-- =============================================================================
-- MIGRATION: Ajouter le système d'Avis et Notations
-- =============================================================================

USE greenbite;

-- Créer la table avis
CREATE TABLE IF NOT EXISTS avis (
    id_avis INT AUTO_INCREMENT PRIMARY KEY,
    id_produit INT NOT NULL,
    id_utilisateur INT NOT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    titre VARCHAR(150) NOT NULL,
    texte TEXT NOT NULL,
    statut ENUM('en-attente', 'approuve', 'rejet') DEFAULT 'en-attente',
    nombre_utilites INT DEFAULT 0,
    date_avis DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_avis_produit FOREIGN KEY (id_produit) REFERENCES produits(id_produit) ON DELETE CASCADE,
    INDEX idx_avis_produit (id_produit),
    INDEX idx_avis_utilisateur (id_utilisateur),
    INDEX idx_avis_statut (statut),
    INDEX idx_avis_date (date_avis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer la table pour les photos d'avis (optionnel)
CREATE TABLE IF NOT EXISTS avis_photos (
    id_photo INT AUTO_INCREMENT PRIMARY KEY,
    id_avis INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_avis_photo FOREIGN KEY (id_avis) REFERENCES avis(id_avis) ON DELETE CASCADE,
    INDEX idx_avis_photos (id_avis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter colonne moyenne_note à la table produits
ALTER TABLE produits ADD COLUMN IF NOT EXISTS moyenne_note DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE produits ADD COLUMN IF NOT EXISTS nombre_avis INT DEFAULT 0;

-- Insérer quelques avis de démonstration
INSERT INTO avis (id_produit, id_utilisateur, note, titre, texte, statut, date_avis) VALUES
(1, 1, 5, 'Excellent produit !', 'Vraiment excellent yaourt, très frais et bon goût. Je recommande vivement !', 'approuve', NOW()),
(1, 2, 4, 'Très bon', 'Bon produit, emballage un peu abîmé à la livraison mais contenu parfait.', 'approuve', NOW()),
(1, 3, 5, 'Meilleur yaourt du marché', 'Texture crémeuse, saveur naturelle. Produit de qualité supérieure.', 'approuve', NOW()),
(7, 1, 4, 'Eau minérale de qualité', 'Bonne eau, bien emballée. Prix correct pour la qualité.', 'approuve', NOW()),
(7, 2, 5, 'Parfait', 'Livraison rapide, produit conforme à la description.', 'approuve', NOW());

-- Mettre à jour les moyennes de notes
UPDATE produits p SET 
    nombre_avis = (SELECT COUNT(*) FROM avis WHERE id_produit = p.id_produit AND statut = 'approuve'),
    moyenne_note = (SELECT ROUND(AVG(note), 2) FROM avis WHERE id_produit = p.id_produit AND statut = 'approuve');

-- =============================================================================
-- Résumé de la migration
-- =============================================================================
-- Tables créées: avis, avis_photos
-- Colonnes ajoutées: moyenne_note, nombre_avis
-- Avis de démonstration: 5 avis
-- =============================================================================
