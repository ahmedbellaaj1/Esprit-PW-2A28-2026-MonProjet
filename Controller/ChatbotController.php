<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class ChatbotController
{
    /**
     * Search recipes based on time limit, calorie limit, optional keywords, and excluded keywords (allergies).
     */
    public function searchRecipes(?int $maxMinutes, ?int $maxCalories, array $keywords, array $excludedKeywords = []): array
    {
        $pdo = getPdo();
        
        $sql = "SELECT r.id_recette, r.nom, r.duree_prep, r.calories 
                FROM recette r ";
                
        $joins = " LEFT JOIN ingredient i ON r.id_recette = i.id_recette ";
        $whereConditions = [];
        $params = [];
        
        if (!empty($keywords)) {
            $keywordConditions = [];
            foreach ($keywords as $index => $kw) {
                $paramName = "kw" . $index;
                $keywordConditions[] = "(r.nom LIKE :$paramName OR r.description LIKE :$paramName OR i.nom LIKE :$paramName)";
                $params[$paramName] = "%" . $kw . "%";
            }
            $whereConditions[] = "(" . implode(" AND ", $keywordConditions) . ")";
        }
        
        // Exclude allergies
        if (!empty($excludedKeywords)) {
            foreach ($excludedKeywords as $index => $ex) {
                $paramName = "ex" . $index;
                // Exclude recipes where the name, description, or any ingredient matches the allergen
                $whereConditions[] = "(r.nom NOT LIKE :$paramName AND r.description NOT LIKE :$paramName AND (i.nom IS NULL OR i.nom NOT LIKE :$paramName))";
                $params[$paramName] = "%" . $ex . "%";
            }
        }
        
        if ($maxMinutes !== null) {
            $hours = floor($maxMinutes / 60);
            $mins = $maxMinutes % 60;
            $timeString = sprintf('%02d:%02d:00', $hours, $mins);
            $whereConditions[] = "r.duree_prep <= :maxTime";
            $params['maxTime'] = $timeString;
        }
        
        if ($maxCalories !== null) {
            $whereConditions[] = "r.calories <= :maxCalories";
            $params['maxCalories'] = $maxCalories;
        }
        
        $whereClause = "";
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= $joins . $whereClause . " GROUP BY r.id_recette ORDER BY r.duree_prep ASC LIMIT 5";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Process a user message and return a response
     */
    public function processMessage(string $message): string
    {
        $message = strtolower(trim($message));
        
        // 5. Analyse d'une recette
        if (str_contains($message, 'analyse') || str_word_count($message) > 25) {
            return "📊 **Analyse de votre recette** :<br>
                    🔥 Calories estimées : ~450 kcal<br>
                    💚 Niveau santé : Bon<br>
                    💡 **Suggestion** : Pour rendre cette recette plus durable, essayez d'utiliser des ingrédients de saison et remplacez une partie des graisses par de la compote de pommes !";
        }

        // 6. Substitution intelligente
        if (preg_match('/(je n\'ai pas de|je nai pas de|sans|remplacer|par quoi remplacer)\s+(le |la |les |d\'|de |l\')?([a-zàâçéèêëîïôûùüÿñæœ]+)/i', $message, $matches)) {
            $ingredientToReplace = $matches[3];
            $subs = [
                'sucre' => "du miel, du sirop d'érable ou de la stevia",
                'beurre' => "de la compote de pommes (sans sucre ajouté), de la purée d'amande ou de l'huile d'olive",
                'oeuf' => "des graines de chia/lin gonflées dans l'eau, ou une demi-banane écrasée",
                'oeufs' => "des graines de chia/lin gonflées dans l'eau, ou une banane écrasée",
                'lait' => "un lait végétal (amande, avoine, soja)",
                'farine' => "de la farine sans gluten (sarrasin, maïs) ou de la poudre d'amande",
                'crème' => "de la crème de soja ou du yaourt nature",
                'creme' => "de la crème de soja ou du yaourt nature"
            ];
            
            foreach ($subs as $key => $sub) {
                if (str_contains($ingredientToReplace, $key)) {
                    return "🔄 **Astuce substitution** : Tu n'as pas de {$key} ? Tu peux utiliser **{$sub}** à la place !";
                }
            }
        }

        // 1. Identify time constraints
        $maxMinutes = null;
        if (str_contains($message, 'rapide')) {
            $maxMinutes = 20;
        }
        if (preg_match('/(\d+)\s*min/i', $message, $matches)) {
            $maxMinutes = (int) $matches[1];
        }
        
        // 1.5 Identify calorie / weight loss constraints
        $maxCalories = null;
        $dietKeywords = ['perte', 'poids', 'mincir', 'maigrir', 'régime', 'regime', 'healthy', 'léger', 'légère', 'leger', 'legere', 'diet'];
        foreach ($dietKeywords as $dk) {
            if (str_contains($message, $dk)) {
                $maxCalories = 400; 
                break;
            }
        }
        
        // 4. Détection Allergies
        $excludedKeywords = [];
        if (preg_match('/(allergique|allergie|intolérant|sans)\s+(au |à la |aux |le |la |les )?([a-zàâçéèêëîïôûùüÿñæœ]+)/i', $message, $matches)) {
            $allergen = $matches[3];
            // Normalize common allergens
            if (str_contains($allergen, 'gluten')) $excludedKeywords[] = 'gluten';
            else if (str_contains($allergen, 'lait') || str_contains($allergen, 'lactose')) { $excludedKeywords[] = 'lait'; $excludedKeywords[] = 'beurre'; $excludedKeywords[] = 'crème'; }
            else if (str_contains($allergen, 'arachide') || str_contains($allergen, 'cacahuète')) { $excludedKeywords[] = 'arachide'; $excludedKeywords[] = 'cacahuète'; }
            else $excludedKeywords[] = $allergen;
            
            // Remove allergy phrase from message so it doesn't become a required keyword
            $message = str_replace($matches[0], '', $message);
        }
        
        // Coach Nutrition (Sport)
        $isSport = preg_match('/(sport|musculation|muscle|énergie)/i', $message);
        if ($isSport) {
            // For sports, we don't restrict calories, we might even want more energy
            $maxCalories = null;
        }
        
        // 2. Identify keywords (Chatbot Frigo)
        // Add "j'ai", "reste", "frigo" to stop words so we just get the ingredients
        $stopWords = ['je', 'veux', 'une', 'recette', 'avec', 'de', 'la', 'le', 'les', 'des', 'un', 'pour', 'en', 'moins', 'et', 'ou', 'qui', 'soit', 'fait', 'donne-moi', 'cherche', 'qu', 'est', 'ce', 'que', 'tu', 'connais', 'quoi', 'manger', 'faire', 'comment', 'as-tu', 'avez-vous', 'salut', 'bonjour', 'bonsoir', 'coucou', 'hey', 'hello', 'ça', 'va', 'merci', 's\'il', 'te', 'plait', 'plaît', 'propose', 'moi', 'trouve', 'j\'ai', 'jai', 'reste', 'frigo', 'dans', 'mon'];
        
        $allWordsToIgnore = array_merge($stopWords, $dietKeywords);
        
        $cleanMessage = preg_replace('/(\d+)\s*minutes?/i', '', $message);
        $cleanMessage = preg_replace('/(sport|musculation|muscle|énergie)/i', '', $cleanMessage);
        $cleanMessage = str_replace(['rapide', '?', '!', '.', ','], ' ', $cleanMessage);
        
        $words = str_word_count($cleanMessage, 1, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿ0123456789');
        $keywords = [];
        foreach ($words as $word) {
            if (!in_array($word, $allWordsToIgnore) && strlen($word) > 2) {
                $keywords[] = $word;
            }
        }
        
        // 2.5 Handle small talk 
        if ($maxMinutes === null && $maxCalories === null && empty($keywords) && empty($excludedKeywords) && !$isSport) {
            if (str_contains($message, 'bonjour') || str_contains($message, 'salut') || str_contains($message, 'coucou')) {
                return "Bonjour ! Je suis l'Assistant GreenBite. Je suis aussi Coach Nutrition et un as des Substitutions ! Dites-moi ce qu'il y a dans votre frigo, ou si vous êtes allergique à quelque chose.";
            }
            if (str_contains($message, 'merci')) {
                return "Avec grand plaisir ! Bon appétit ! 🍽️";
            }
            if (str_contains($message, 'qui') || str_contains($message, 'connais') || str_contains($message, 'quoi')) {
                return "Je suis un super-assistant ! Je peux analyser des recettes, vous aider pour le sport, remplacer des ingrédients manquants, ou gérer vos allergies !";
            }
            return "Je n'ai pas bien compris... 😅 Demandez-moi une recette avec ce que vous avez au frigo, ou dites-moi ce que vous voulez remplacer !";
        }
        
        // 3. Query the database
        $recipes = $this->searchRecipes($maxMinutes, $maxCalories, $keywords, $excludedKeywords);
        
        // 4. Format the response
        if (empty($recipes)) {
            $response = "Mince, je n'ai trouvé aucune recette ";
            if (!empty($keywords)) $response .= "contenant <strong>" . htmlspecialchars(implode(', ', $keywords)) . "</strong> ";
            if (!empty($excludedKeywords)) $response .= "sans <strong>" . htmlspecialchars(implode(', ', $excludedKeywords)) . "</strong> ";
            if ($maxCalories !== null) $response .= "faible en calories ";
            if ($maxMinutes !== null) $response .= "en moins de <strong>" . $maxMinutes . " minutes</strong>.";
            return $response . " 😕 Essayez avec d'autres critères !";
        }
        
        $reply = "";
        if ($isSport) {
            $reply .= "🏋️‍♂️ **Coach Nutrition** : Pour le sport, privilégiez les protéines et les glucides complexes ! Voici des recettes énergétiques idéales :<br>";
        } else if (!empty($excludedKeywords)) {
            $reply .= "⚠️ **Allergies prises en compte** : Voici des recettes sûres sans " . htmlspecialchars(implode(', ', $excludedKeywords)) . " :<br>";
        } else {
            $reply .= "Voici ce que j'ai trouvé pour vous :<br>";
        }
        
        $reply .= "<ul style='margin-top: 8px; padding-left: 20px;'>";
        foreach ($recipes as $r) {
            $timeFormatted = substr($r['duree_prep'], 0, 5);
            $reply .= "<li style='margin-bottom: 4px;'><strong>" . htmlspecialchars($r['nom']) . "</strong> (" . $timeFormatted . " - " . htmlspecialchars((string)$r['calories']) . " kcal)</li>";
        }
        $reply .= "</ul>";
        
        return $reply;
    }
}
