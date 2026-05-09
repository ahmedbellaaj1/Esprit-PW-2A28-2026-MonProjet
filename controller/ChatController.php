<?php
require_once __DIR__ . "/../model/ProductModel.php";
require_once __DIR__ . "/../model/Product.php";

class ChatController {
    public static function handleMessage($pdo, $message, $user_id = 0) {
        $messageLower = mb_strtolower(trim($message));
        if (empty($messageLower)) {
            return ['reply' => 'Bonjour ! Comment puis-je vous aider aujourd\'hui ?'];
        }

        require_once __DIR__ . "/../model/UserModel.php";

        $userAllergies = [];
        $userPrefs = [];

        if ($user_id) {
            $user = UserModel::getById($pdo, $user_id);
            if ($user) {
                $details = UserModel::getProfileDetails($pdo, $user_id);
                $user->setPreferences($details['preferences']);
                $user->setAllergies($details['allergies']);

                $userAllergies = $user->getAllergies();
                $userPrefs = $user->getPreferences();
            }
        }

        try {
            if (strpos($messageLower, 'salut') !== false || strpos($messageLower, 'bonjour') !== false) {
                return ['reply' => "Bonjour ! Je suis l'assistant Greenbite 🍏. Je peux vous aider à explorer notre catalogue, comparer les calories ou trouver des suggestions personnalisées."];
            } 
            
            if (strpos($messageLower, 'calorie') !== false) {
                preg_match('/(\d+)/', $messageLower, $matches);
                $limit = isset($matches[1]) ? intval($matches[1]) : 500;
                $isGreater = (strpos($messageLower, 'plus') !== false || strpos($messageLower, 'supérieur') !== false || strpos($messageLower, '>') !== false);
                $opLabel = $isGreater ? "plus de" : "moins de";
                
                $products = ProductModel::getAll($pdo);
                $filtered = array_filter($products, function($p) use ($limit, $isGreater) {
                    // UTILISER LE GETTER ICI
                    return $isGreater ? ($p->getCalories() > $limit) : ($p->getCalories() <= $limit);
                });
                $filtered = array_slice($filtered, 0, 5); 
                
                if (empty($filtered)) {
                    return ['reply' => "Désolé, je n'ai trouvé aucun produit avec $opLabel $limit calories dans notre base actuelle."];
                } else {
                    $reply = "D'après vos critères, voici les produits avec **$opLabel $limit calories** :<br><ul>";
                    foreach ($filtered as $p) {
                        // UTILISER LE GETTER ICI
                        $reply .= "<li>✨ <b>{$p->getNom()}</b> — <span style='color:#0f766e;'>{$p->getCalories()} kcal</span></li>";
                    }
                    $reply .= "</ul>";
                    return ['reply' => $reply];
                }
            } 
            
            if (strpos($messageLower, 'produit') !== false || strpos($messageLower, 'manger') !== false || strpos($messageLower, 'suggestion') !== false) {
                $products = ProductModel::getAll($pdo);
                
                foreach ($products as $p) {
                    $score = 0;
                    // UTILISER LE GETTER ICI
                    $text = mb_strtolower($p->getNom() . ' ' . $p->getCategorie());
                    
                    foreach ($userAllergies as $all) {
                        if (!empty($all) && strpos($text, $all) !== false) {
                            $score = -100; break;
                        }
                    }
                    
                    if ($score >= 0) {
                        foreach ($userPrefs as $pref) {
                            if (!empty($pref) && strpos($text, $pref) !== false) {
                                $score += 10;
                            }
                        }
                    }
                    // On peut pas facilement stocker le score dans l'objet Product sans modifier la classe, 
                    // on va utiliser un tableau temporaire pour le tri.
                    $scoredProducts[] = ['product' => $p, 'score' => $score];
                }
                
                usort($scoredProducts, function($a, $b) { return $b['score'] - $a['score']; });
                $top = array_slice(array_filter($scoredProducts, function($item){ return $item['score'] >= 0; }), 0, 3);
                
                if (empty($top)) {
                    $reply = "Désolé, je n'ai pas trouvé de produits correspondant parfaitement à votre profil sans allergènes. Voici quand même quelques options sûres :";
                    $top = array_slice(array_filter($scoredProducts, function($item){ return $item['score'] >= 0; }), 0, 2);
                } else {
                    $reply = "En fonction de votre profil (préférences: " . implode(', ', $userPrefs) . "), voici mes meilleures suggestions :<br><ul>";
                }

                foreach ($top as $item) {
                    $p = $item['product'];
                    // UTILISER LE GETTER ICI
                    $reply .= "<li>🥗 <b>{$p->getNom()}</b> — <small>{$p->getCategorie()}</small></li>";
                }
                $reply .= "</ul>";
                return ['reply' => $reply];
            }

            if (strpos($messageLower, 'aide') !== false || strpos($messageLower, 'quoi') !== false) {
                return ['reply' => "Je suis là pour vous accompagner ! Vous pouvez me demander par exemple :<br>💡 *'Montre moi des produits'*<br>🔥 *'Produits avec plus de 400 calories'*<br>👋 *'Bonjour'*"];
            }

            return ['reply' => "Je ne suis pas sûr de comprendre. Tapez 'Aide' pour voir ce que je peux faire !"];

        } catch (Exception $e) {
            return ['reply' => "Désolé, j'ai rencontré une erreur technique.", 'error' => $e->getMessage()];
        }
    }
}
