-- =============================================================================
-- MIGRATION: Système de ChatBot IA Intelligent
-- =============================================================================

USE greenbite;

-- Table des conversations
CREATE TABLE IF NOT EXISTS chat_conversations (
    id_conversation INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    titre VARCHAR(200) DEFAULT 'Nouvelle conversation',
    statut ENUM('actif', 'archive', 'supprime') DEFAULT 'actif',
    sentiment_general VARCHAR(20) DEFAULT 'neutral',
    budget_estime DECIMAL(10,2) DEFAULT 0,
    preferences_detectees JSON DEFAULT NULL,
    allergies_detectees JSON DEFAULT NULL,
    regime_detecte VARCHAR(100) DEFAULT NULL,
    date_debut DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_dernier_message DATETIME DEFAULT NULL,
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages
CREATE TABLE IF NOT EXISTS chat_messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_conversation INT NOT NULL,
    type ENUM('utilisateur', 'bot', 'système') DEFAULT 'utilisateur',
    contenu LONGTEXT NOT NULL,
    intent VARCHAR(50) DEFAULT NULL,
    entities JSON DEFAULT NULL,
    sentiment VARCHAR(20) DEFAULT 'neutral',
    confiance DECIMAL(3,2) DEFAULT 0.50,
    date_message DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_conversation FOREIGN KEY (id_conversation) REFERENCES chat_conversations(id_conversation) ON DELETE CASCADE,
    INDEX idx_conversation (id_conversation),
    INDEX idx_type (type),
    INDEX idx_intent (intent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des recommandations
CREATE TABLE IF NOT EXISTS chat_recommendations (
    id_recommendation INT AUTO_INCREMENT PRIMARY KEY,
    id_message INT NOT NULL,
    id_produit INT NOT NULL,
    raison VARCHAR(200) DEFAULT NULL,
    confiance_score DECIMAL(3,2) DEFAULT 0.50,
    position_rank INT DEFAULT 0,
    clique BOOLEAN DEFAULT FALSE,
    ajoute_panier BOOLEAN DEFAULT FALSE,
    acheté BOOLEAN DEFAULT FALSE,
    date_recommendation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recommendation_message FOREIGN KEY (id_message) REFERENCES chat_messages(id_message) ON DELETE CASCADE,
    CONSTRAINT fk_recommendation_produit FOREIGN KEY (id_produit) REFERENCES produits(id_produit) ON DELETE CASCADE,
    INDEX idx_produit (id_produit),
    INDEX idx_ajoute_panier (ajoute_panier),
    INDEX idx_acheté (acheté)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des patterns de langage (pour ML simple)
CREATE TABLE IF NOT EXISTS chat_intent_patterns (
    id_pattern INT AUTO_INCREMENT PRIMARY KEY,
    intent VARCHAR(50) NOT NULL,
    pattern VARCHAR(255) NOT NULL,
    keywords JSON NOT NULL,
    categorie VARCHAR(100) DEFAULT NULL,
    poids INT DEFAULT 1,
    INDEX idx_intent (intent),
    UNIQUE KEY unique_pattern (pattern, intent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table analytics
CREATE TABLE IF NOT EXISTS chat_analytics (
    id_analytics INT AUTO_INCREMENT PRIMARY KEY,
    date_jour DATE NOT NULL,
    nombre_conversations INT DEFAULT 0,
    nombre_messages INT DEFAULT 0,
    nombre_recommendations INT DEFAULT 0,
    taux_conversion DECIMAL(5,2) DEFAULT 0,
    panier_moyen DECIMAL(10,2) DEFAULT 0,
    intent_populaire VARCHAR(50) DEFAULT NULL,
    satisfaction_moyenne DECIMAL(3,2) DEFAULT 0,
    UNIQUE KEY unique_date (date_jour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- INSERTION DES PATTERNS D'INTENT
-- =============================================================================

INSERT INTO chat_intent_patterns (intent, pattern, keywords, categorie, poids) VALUES
-- Recherche de produits
('product_search', 'je cherche', JSON_ARRAY('cherche', 'besoin', 'avoir', 'acheter'), 'search', 2),
('product_search', 'avez-vous', JSON_ARRAY('avez', 'vous', 'existe', 'disponible'), 'search', 2),
('product_search', 'je veux', JSON_ARRAY('veux', 'voudrais', 'aimerais', 'désire'), 'search', 2),

-- Régime alimentaire
('diet_preference', 'vegan', JSON_ARRAY('vegan', 'végétal', 'sans viande'), 'preferences', 3),
('diet_preference', 'keto', JSON_ARRAY('keto', 'cétogène', 'faible carb'), 'preferences', 3),
('diet_preference', 'bio', JSON_ARRAY('bio', 'organique', 'naturel'), 'preferences', 2),
('diet_preference', 'sans gluten', JSON_ARRAY('gluten', 'coeliaque', 'glutenfree'), 'preferences', 3),

-- Allergies
('allergies', 'allergie', JSON_ARRAY('allergie', 'allergique', 'intolérance'), 'health', 3),
('allergies', 'arachides', JSON_ARRAY('arachide', 'cacahuète', 'noix', 'peanut'), 'allergies', 3),
('allergies', 'produits laitiers', JSON_ARRAY('lactose', 'lait', 'dairy'), 'allergies', 3),

-- Budget
('budget_constraint', 'pas cher', JSON_ARRAY('pas cher', 'bon marché', 'économique', 'budget'), 'budget', 2),
('budget_constraint', 'haut de gamme', JSON_ARRAY('haut', 'gamme', 'premium', 'luxe'), 'budget', 2),

-- Santé & Nutrition
('health_nutrition', 'calories', JSON_ARRAY('calorie', 'énergie', 'kcal'), 'health', 2),
('health_nutrition', 'protéines', JSON_ARRAY('protéine', 'protéin', 'muscle'), 'health', 2),
('health_nutrition', 'faible sucre', JSON_ARRAY('sucre', 'diabète', 'glucose'), 'health', 2),

-- Saveur & Préférence
('taste_preference', 'sucré', JSON_ARRAY('sucré', 'sucre', 'miel', 'confiture'), 'preferences', 1),
('taste_preference', 'salé', JSON_ARRAY('salé', 'salé', 'sel', 'savoureux'), 'preferences', 1),
('taste_preference', 'épicé', JSON_ARRAY('épicé', 'piment', 'harissa', 'fort'), 'preferences', 1),

-- Catégorie produit
('category_request', 'fruits légumes', JSON_ARRAY('fruit', 'légume', 'légume', 'frais'), 'category', 2),
('category_request', 'boissons', JSON_ARRAY('boisson', 'jus', 'café', 'thé'), 'category', 2),

-- Aide & Support
('help_request', 'aide', JSON_ARRAY('aide', 'help', 'aide', 'comment'), 'support', 1),
('help_request', 'conseil', JSON_ARRAY('conseil', 'suggestion', 'recommande'), 'support', 1);

-- =============================================================================
-- INDEX POUR PERFORMANCE
-- =============================================================================
CREATE INDEX idx_chat_date ON chat_messages(date_message);
CREATE INDEX idx_conversation_date ON chat_conversations(date_dernier_message);
CREATE INDEX idx_analytics_period ON chat_analytics(date_jour);

-- =============================================================================
-- Résumé
-- =============================================================================
-- Tables créées: 5 (conversations, messages, recommendations, patterns, analytics)
-- Patterns d'intent: 16
-- Prêt pour IA et ML
-- =============================================================================
