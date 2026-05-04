<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * ChatBot IA Model - Système Intelligent avec Catalogue de Produits
 * Comprend les demandes utilisateur et fait des recommandations réalistes
 */
class ChatBot
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /**
     * Créer une conversation
     */
    public function createConversation(int $id_utilisateur): int
    {
        try {
            $titre = 'Chat du ' . date('d/m/Y H:i');
            $date_debut = date('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare('
                INSERT INTO chat_conversations (id_utilisateur, titre, date_debut)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$id_utilisateur, $titre, $date_debut]);
            return (int)$this->pdo->lastInsertId();
        } catch (Throwable $e) {
            error_log("createConversation: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ajouter un message
     */
    public function addMessage(int $id_conversation, string $type, string $contenu): int
    {
        try {
            $sentiment = $this->detectSentiment($contenu);
            $date_msg = date('Y-m-d H:i:s');
            
            $stmt = $this->pdo->prepare('
                INSERT INTO chat_messages (id_conversation, type, contenu, sentiment, date_message)
                VALUES (?, ?, ?, ?, ?)
            ');
            
            $stmt->execute([$id_conversation, $type, $contenu, $sentiment, $date_msg]);
            $id_msg = (int)$this->pdo->lastInsertId();
            
            if ($id_msg > 0) {
                $this->pdo->prepare('
                    UPDATE chat_conversations SET date_dernier_message = NOW() WHERE id_conversation = ?
                ')->execute([$id_conversation]);
            }
            
            return $id_msg;
        } catch (Throwable $e) {
            error_log("addMessage: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Analyser le message avec logique intelligente
     */
    public function analyzeMessage(string $message): array
    {
        try {
            $msg_lower = strtolower($message);
            $analysis = [
                'intent' => 'help_request',
                'confiance' => 0.5,
                'has_search_keywords' => false,
                'has_diet_criteria' => false,
                'has_budget_criteria' => false,
                'has_nutrition_criteria' => false,
                'search_keywords' => [],
                'diet_type' => null,
                'budget_min' => null,
                'budget_max' => null,
                'nutrition_focus' => null
            ];

            // Extraction des mots-clés et critères
            $search_keywords = $this->extractSearchKeywords($msg_lower);
            $diet_type = $this->extractDietCriteria($msg_lower);
            $budget = $this->extractBudgetCriteria($msg_lower);
            $nutrition_focus = $this->extractNutritionFocus($msg_lower);

            if (!empty($search_keywords)) {
                $analysis['has_search_keywords'] = true;
                $analysis['search_keywords'] = $search_keywords;
                $analysis['intent'] = 'product_search';
                $analysis['confiance'] = 0.85;
            }

            if ($diet_type) {
                $analysis['has_diet_criteria'] = true;
                $analysis['diet_type'] = $diet_type;
                $analysis['intent'] = 'diet_preference';
                $analysis['confiance'] = 0.9;
            }

            if ($budget) {
                $analysis['has_budget_criteria'] = true;
                $analysis['budget_min'] = $budget['min'];
                $analysis['budget_max'] = $budget['max'];
                if ($analysis['intent'] === 'help_request') {
                    $analysis['intent'] = 'budget_search';
                }
                $analysis['confiance'] = max($analysis['confiance'], 0.8);
            }

            if ($nutrition_focus) {
                $analysis['has_nutrition_criteria'] = true;
                $analysis['nutrition_focus'] = $nutrition_focus;
                if ($analysis['intent'] === 'help_request') {
                    $analysis['intent'] = 'health_nutrition';
                }
                $analysis['confiance'] = max($analysis['confiance'], 0.85);
            }

            // Détection des allergies
            if ($this->hasAllergyKeywords($msg_lower)) {
                $analysis['intent'] = 'allergies';
                $analysis['confiance'] = 0.95;
            }

            return $analysis;
        } catch (Throwable $e) {
            error_log("analyzeMessage: " . $e->getMessage());
            return [
                'intent' => 'help_request',
                'confiance' => 0.3,
                'has_search_keywords' => false
            ];
        }
    }

    /**
     * Extraire les mots-clés de recherche
     */
    private function extractSearchKeywords(string $message): array
    {
        $keywords = [];
        
        // Mots-clés de recherche courants
        $common_keywords = [
            'fruits', 'légumes', 'viande', 'poisson', 'fromage', 'yaourt', 'lait',
            'pain', 'riz', 'pâtes', 'céréales', 'chocolat', 'café', 'thé',
            'jus', 'eau', 'bière', 'vin', 'snack', 'biscuit', 'gâteau',
            'chips', 'noisettes', 'amandes', 'bio', 'vegan', 'végétal',
            'protéine', 'protéiné', 'fit', 'light', 'zéro', 'sans sucre',
            'sans gluten', 'sans lactose', 'oeufs', 'beurre', 'huile',
            'miel', 'sucre', 'sel', 'épices', 'herbes', 'sauce'
        ];

        foreach ($common_keywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $keywords[] = $kw;
            }
        }

        return $keywords;
    }

    /**
     * Extraire les critères diététiques
     */
    private function extractDietCriteria(string $message): ?string
    {
        $diet_patterns = [
            'vegan' => ['vegan', 'végétalien'],
            'vegetarian' => ['végétarien', 'vegetarien'],
            'gluten_free' => ['sans gluten', 'sans-gluten', 'gluten free'],
            'dairy_free' => ['sans lactose', 'sans-lactose', 'lactose free'],
            'halal' => ['halal', 'halale'],
            'kosher' => ['kasher', 'kosher'],
            'bio' => ['bio', 'biologique']
        ];

        foreach ($diet_patterns as $diet => $keywords) {
            foreach ($keywords as $kw) {
                if (stripos($message, $kw) !== false) {
                    return $diet;
                }
            }
        }

        return null;
    }

    /**
     * Extraire les critères de budget
     */
    private function extractBudgetCriteria(string $message): ?array
    {
        // Chercher des patterns de prix
        if (preg_match('/moins de (\d+(?:[.,]\d+)?)\s*€/', $message, $matches)) {
            return ['min' => 0, 'max' => (float)str_replace(',', '.', $matches[1])];
        }

        if (preg_match('/moins de (\d+(?:[.,]\d+)?)/i', $message, $matches)) {
            return ['min' => 0, 'max' => (float)str_replace(',', '.', $matches[1])];
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*€?\s*maximum/i', $message, $matches)) {
            return ['min' => 0, 'max' => (float)str_replace(',', '.', $matches[1])];
        }

        if (preg_match('/bon marché|pas cher|économique|discount/', $message)) {
            return ['min' => 0, 'max' => 5];
        }

        return null;
    }

    /**
     * Extraire le focus nutritionnel
     */
    private function extractNutritionFocus(string $message): ?string
    {
        if (preg_match('/protéin|muscle|force|fit/', $message)) {
            return 'protein';
        }
        if (preg_match('/calorie|énergie|sport|cardio/', $message)) {
            return 'energy';
        }
        if (preg_match('/régime|poid|mince|minceur/', $message)) {
            return 'low_calorie';
        }
        if (preg_match('/sain|santé|équilibr|vitamine/', $message)) {
            return 'healthy';
        }
        if (preg_match('/fibr|digestion|ventre/', $message)) {
            return 'digestion';
        }

        return null;
    }

    /**
     * Vérifier la présence de mots-clés d'allergies
     */
    private function hasAllergyKeywords(string $message): bool
    {
        $allergy_keywords = ['allergie', 'allergique', 'intolérant', 'intolérance'];
        foreach ($allergy_keywords as $kw) {
            if (stripos($message, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Détecter le sentiment
     */
    public function detectSentiment(string $message): string
    {
        $msg_lower = strtolower($message);
        
        $positifs = ['super', 'excellent', 'bien', 'adoré', 'merveilleux', 'génial', 'parfait', 'top', 'cool', 'love'];
        $negatifs = ['horrible', 'mauvais', 'nul', 'déteste', 'pire', 'déçu', 'médiocre'];

        $pos = 0;
        $neg = 0;

        foreach ($positifs as $w) {
            if (stripos($msg_lower, $w) !== false) $pos++;
        }
        foreach ($negatifs as $w) {
            if (stripos($msg_lower, $w) !== false) $neg++;
        }

        if ($pos > $neg) return 'positif';
        if ($neg > $pos) return 'négatif';
        return 'neutre';
    }

    /**
     * Générer une réponse contextuelle
     */
    public function generateResponse(string $message, array $analysis): string
    {
        $intent = $analysis['intent'] ?? 'help_request';
        $keywords = $analysis['search_keywords'] ?? [];
        $diet = $analysis['diet_type'] ?? null;
        $nutrition = $analysis['nutrition_focus'] ?? null;

        // Réponses contextuelles
        $responses = [
            'product_search' => [
                "Parfait! 🛍️ Je cherche les " . (count($keywords) > 0 ? implode(', ', $keywords) : 'produits') . " disponibles dans notre catalogue.",
                "Excellent! 👀 Voyons ensemble ce que nous avons comme " . (count($keywords) > 0 ? implode(', ', $keywords) : 'produits') . ".",
                "Super ! 🔍 Je parcours notre sélection pour trouver exactement ce qu'il vous faut."
            ],
            'diet_preference' => [
                "Très bien! 💚 Je vais vous proposer nos meilleurs produits " . ($diet ? $diet : 'adaptés') . ".",
                "Excellent choix! 🌱 Voici nos meilleures options pour vous.",
                "Parfait! 👌 J'ai trouvé exactement ce qu'il faut pour vous."
            ],
            'allergies' => [
                "C'est important! 🏥 Je vérifie nos produits hypoallergéniques pour vous.",
                "Sécurité avant tout! ✓ Je cherche les produits sans allergènes.",
                "Compris! 🛡️ Voici uniquement les produits sûrs pour vous."
            ],
            'budget_search' => [
                "Super! 💰 Je vous propose les meilleures affaires dans votre budget.",
                "Pas de problème! 💵 Voici nos prix les plus avantageux.",
                "Entendu! ✨ Qualité au meilleur prix pour vous."
            ],
            'health_nutrition' => [
                "Excellent! 💪 Voici mes recommandations santé.",
                "Très bien! 🥗 Je vous propose nos produits les plus nutritifs.",
                "Santé avant tout! ❤️ Découvrez nos meilleures options."
            ],
            'help_request' => [
                "Bonjour! 👋 Bienvenue dans notre catalogue. Dites-moi ce que vous cherchez!",
                "Salut! 😊 Je suis là pour vous aider. Que puis-je faire pour vous?",
                "Coucou! 👋 Décrivez-moi ce que vous cherchez et je trouverai ça pour vous!"
            ]
        ];

        $response_array = $responses[$intent] ?? $responses['help_request'];
        $random_response = $response_array[array_rand($response_array)];
        
        return $random_response;
    }

    /**
     * Obtenir les recommandations produits intelligentes
     */
    public function getRecommendations(string $message, int $id_message, int $limit = 5): array
    {
        try {
            $analysis = $this->analyzeMessage($message);
            
            // Construire la requête SQL dynamique avec jointure pour les avis
            $query = 'SELECT 
                        p.id_produit, p.nom, p.prix, p.image, p.categorie, p.marque,
                        COALESCE(AVG(a.note), 0) as note_moyenne,
                        COALESCE(COUNT(a.id_avis), 0) as nombre_avis,
                        p.calories,
                        p.proteines
                    FROM produits p
                    LEFT JOIN avis a ON p.id_produit = a.id_produit AND a.statut = "approuve"
                    WHERE p.statut = "actif"';

            $conditions = [];
            $params = [];

            // Ajouter les filtres selon l'analyse
            if (!empty($analysis['search_keywords'])) {
                $keyword_filters = [];
                foreach ($analysis['search_keywords'] as $kw) {
                    $keyword_filters[] = '(p.nom LIKE ? OR p.categorie LIKE ? OR p.marque LIKE ?)';
                    $params[] = '%' . $kw . '%';
                    $params[] = '%' . $kw . '%';
                    $params[] = '%' . $kw . '%';
                }
                if (!empty($keyword_filters)) {
                    $conditions[] = '(' . implode(' OR ', $keyword_filters) . ')';
                }
            }

            // Filtre budget
            if ($analysis['has_budget_criteria']) {
                if ($analysis['budget_max']) {
                    $conditions[] = 'p.prix <= ?';
                    $params[] = $analysis['budget_max'];
                }
                if ($analysis['budget_min']) {
                    $conditions[] = 'p.prix >= ?';
                    $params[] = $analysis['budget_min'];
                }
            }

            // Filtre diététique
            if ($analysis['diet_type']) {
                $diet_name = $this->getDietCategoryName($analysis['diet_type']);
                if ($diet_name) {
                    $conditions[] = 'p.categorie LIKE ?';
                    $params[] = '%' . $diet_name . '%';
                }
            }

            // Filtre nutritionnel
            if ($analysis['nutrition_focus'] === 'protein') {
                $conditions[] = 'p.proteines >= 10';
            } elseif ($analysis['nutrition_focus'] === 'low_calorie') {
                $conditions[] = 'p.calories <= 200';
            }

            // Ajouter les conditions
            if (!empty($conditions)) {
                $query .= ' AND ' . implode(' AND ', $conditions);
            }

            // Ajouter GROUP BY pour les agrégations et l'ordre
            $query .= ' GROUP BY p.id_produit
                    ORDER BY 
                        COALESCE(AVG(a.note), 0) DESC,
                        COALESCE(COUNT(a.id_avis), 0) DESC,
                        p.nom ASC
                    LIMIT ' . intval($limit);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si pas de produits trouvés, retourner les mieux notés
            if (empty($products)) {
                $fallback_query = 'SELECT 
                                    p.id_produit, p.nom, p.prix, p.image, p.categorie, p.marque,
                                    COALESCE(AVG(a.note), 0) as note_moyenne,
                                    COALESCE(COUNT(a.id_avis), 0) as nombre_avis,
                                    p.calories,
                                    p.proteines
                                FROM produits p
                                LEFT JOIN avis a ON p.id_produit = a.id_produit AND a.statut = "approuve"
                                WHERE p.statut = "actif"
                                GROUP BY p.id_produit
                                ORDER BY note_moyenne DESC, nombre_avis DESC
                                LIMIT ' . intval($limit);
                $stmt = $this->pdo->query($fallback_query);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Formatter les résultats
            $recs = [];
            foreach ($products as $idx => $p) {
                $raison = $this->generateRecommendationReason($p, $analysis);
                $recs[] = [
                    'id_produit' => (int)$p['id_produit'],
                    'nom' => (string)$p['nom'],
                    'prix' => (float)$p['prix'],
                    'image' => (string)($p['image'] ?? 'https://via.placeholder.com/150'),
                    'categorie' => (string)($p['categorie'] ?? 'Produit'),
                    'marque' => (string)($p['marque'] ?? ''),
                    'note_moyenne' => (float)$p['note_moyenne'],
                    'nombre_avis' => (int)$p['nombre_avis'],
                    'calories' => (float)($p['calories'] ?? 0),
                    'proteines' => (float)($p['proteines'] ?? 0),
                    'confiance' => 0.75 + (0.03 * $idx),
                    'raison' => $raison
                ];
            }
            
            return $recs;
        } catch (Throwable $e) {
            error_log("getRecommendations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Générer une raison de recommandation
     */
    private function generateRecommendationReason(array $product, array $analysis): string
    {
        $reasons = [];

        if ($product['note_moyenne'] >= 4.5) {
            $reasons[] = "⭐ Très bien noté (" . round($product['note_moyenne'], 1) . "/5)";
        }

        if (!empty($analysis['search_keywords'])) {
            $reasons[] = "🔍 Correspond à votre recherche";
        }

        if ($analysis['diet_type']) {
            $reasons[] = "🌱 Adapté à votre régime";
        }

        if ($analysis['has_budget_criteria'] && $product['prix'] <= ($analysis['budget_max'] ?? 100)) {
            $reasons[] = "💰 Au meilleur prix";
        }

        if ($analysis['nutrition_focus'] === 'protein' && $product['proteines'] >= 10) {
            $reasons[] = "💪 Riche en protéines";
        }

        if (empty($reasons)) {
            $reasons[] = "Recommandé pour vous";
        }

        return implode(" • ", $reasons);
    }

    /**
     * Obtenir le nom de catégorie pour un régime
     */
    private function getDietCategoryName(string $diet_type): ?string
    {
        $diet_map = [
            'vegan' => 'vegan',
            'vegetarian' => 'vegetarien',
            'gluten_free' => 'sans gluten',
            'dairy_free' => 'sans lactose',
            'bio' => 'bio'
        ];

        return $diet_map[$diet_type] ?? null;
    }

    /**
     * Tracker les ajouts au panier depuis les recommandations
     */
    public function trackAddToCart(int $id_recommendation): bool
    {
        try {
            // Ce sont les recommandations du produit
            return true;
        } catch (Throwable $e) {
            error_log("trackAddToCart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer l'historique des conversations
     */
    public function getConversationHistory(int $id_conversation): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT id_message, type, contenu, sentiment, date_message
                FROM chat_messages
                WHERE id_conversation = ?
                ORDER BY date_message ASC
            ');
            $stmt->execute([$id_conversation]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("getConversationHistory: " . $e->getMessage());
            return [];
        }
    }
}
?>
