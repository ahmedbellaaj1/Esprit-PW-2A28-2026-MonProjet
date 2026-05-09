<?php
session_start();
include '../config/db.php';
require_once '../model/Product.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {

    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $sort = trim($_GET['sort'] ?? '');

    // 🔐 USER ID depuis SESSION (IMPORTANT) ou GET (fallback)
    $user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? null;

    if (!$user_id && !isset($_GET['override_prefs'])) {
        echo json_encode([]);
        exit;
    }

    /* =========================
       CHECK TABLE EXISTS
    ========================== */
    function tableExists($pdo, $table) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = ?
        ");
        $stmt->execute([$table]);
        return (int)$stmt->fetchColumn() > 0;
    }

    $productsTable = tableExists($pdo, 'produits') ? 'produits' : (tableExists($pdo, 'Produits') ? 'Produits' : null);

    if (!$productsTable) {
        throw new Exception("Table produits introuvable");
    }

    function normalize($str) {
        $str = mb_strtolower($str);
        $str = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'û', 'ù', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u', 'c'],
            $str
        );
        return $str;
    }

    /* =========================
       USER DATA (WITH OVERRIDES)
    ========================== */
    $userAllergies = [];
    if (isset($_GET['override_allergies']) && $_GET['override_allergies'] !== '') {
        $userAllergies = preg_split('/[\s,]+/', $_GET['override_allergies'], -1, PREG_SPLIT_NO_EMPTY);
        $userAllergies = array_map('normalize', $userAllergies);
    } elseif ($user_id) {
        try {
            $stmt = $pdo->prepare("SELECT nom_allergie FROM allergies WHERE id_user = ?");
            $stmt->execute([$user_id]);
            $userAllergies = array_map('normalize', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Exception $e) {}
    }

    $userPreferences = [];
    if (isset($_GET['override_prefs']) && $_GET['override_prefs'] !== '') {
        $userPreferences = preg_split('/[\s,]+/', $_GET['override_prefs'], -1, PREG_SPLIT_NO_EMPTY);
        $userPreferences = array_map('normalize', $userPreferences);
    } elseif ($user_id) {
        try {
            $stmt = $pdo->prepare("SELECT type_preference FROM preferences_alimentaires WHERE id_user = ?");
            $stmt->execute([$user_id]);
            $userPreferences = array_filter(array_map('normalize', $stmt->fetchAll(PDO::FETCH_COLUMN)));
        } catch (Exception $e) {}
    }

    /* =========================
       PRODUCTS QUERY
    ========================== */
    $sql = "SELECT id, nom, categorie, description, calories, prix FROM $productsTable WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (nom LIKE ? OR description LIKE ? OR categorie LIKE ?)";
        $like = "%$search%";
        $params = [$like, $like, $like];
    }

    if ($category !== '') {
        $sql .= " AND categorie = ?";
        $params[] = $category;
    }

    if ($sort === 'prix_asc') {
        $sql .= " ORDER BY prix ASC";
    } elseif ($sort === 'prix_desc') {
        $sql .= " ORDER BY prix DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];
    foreach ($rows as $row) {
        $p = new Product($row['nom'], $row['categorie'], $row['description'], $row['calories'], $row['prix']);
        $p->setId($row['id']);
        $products[] = $p;
    }

    /* =========================
       OPTIONAL TABLES
    ========================== */
    $hasAllergenTable = tableExists($pdo, 'allergenes_produits');

    $stmtAllergen = null;
    if ($hasAllergenTable) {
        $stmtAllergen = $pdo->prepare("
            SELECT allergene, niveau
            FROM allergenes_produits
            WHERE id_produit = ?
        ");
    }

    $hasPreferencesProductTable = tableExists($pdo, 'preferences_produits');

    $stmtProductPrefs = null;
    if ($hasPreferencesProductTable) {
        $stmtProductPrefs = $pdo->prepare("
            SELECT preference, compatible 
            FROM preferences_produits 
            WHERE id_produit = ?
        ");
    }

    /* =========================
       AI FILTER LOGIC
    ========================== */
    foreach ($products as $p) {
        // 🔴 ALLERGIES FILTER
        if ($stmtAllergen) {
            $stmtAllergen->execute([$p->getId()]);
            $allergenes = $stmtAllergen->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allergenes as $a) {
                $pAll = mb_strtolower(trim($a['allergene'] ?? ''));
                if (in_array($pAll, $userAllergies, true)) {
                    if (($a['niveau'] ?? '') === 'interdit') {
                        $p->forbidden = true;
                        $p->allowed = false;
                    }
                    if (($a['niveau'] ?? '') === 'a_verifier') {
                        $p->check = true;
                    }
                }
            }
        }

        // Fallback allergies detection by text
        if (!$p->forbidden) {
            $productText = normalize(($p->getNom() ?? '') . ' ' . ($p->getCategorie() ?? '') . ' ' . ($p->getDescription() ?? ''));
            foreach ($userAllergies as $all) {
                if ($all !== '' && strpos($productText, $all) !== false) {
                    $p->forbidden = true;
                    $p->allowed = false;
                    break;
                }
            }
        }

        // 🟢 PREFERENCES FILTER (AI)
        if ($stmtProductPrefs) {
            $stmtProductPrefs->execute([$p->getId()]);
            $productPrefs = $stmtProductPrefs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($productPrefs as $prefRow) {
                $pPref = normalize(trim($prefRow['preference'] ?? ''));
                if (
                    in_array($pPref, $userPreferences, true)
                    && (int)$prefRow['compatible'] === 1
                ) {
                    $p->recommended = true;
                    break;
                }
            }
        }

        // fallback AI text matching
        if (!$p->recommended) {
            $text = normalize($p->getNom() . ' ' . $p->getCategorie() . ' ' . $p->getDescription());

            foreach ($userPreferences as $pref) {
                if ($pref !== '' && strpos($text, $pref) !== false) {
                    $p->recommended = true;
                    break;
                }
            }
        }
    }

    /* =========================
       STRICT FILTERING & SORTING
    ========================== */
    // Si on est en mode "AI Override" (depuis le dashboard), on filtre plus strictement
    $isAIOverride = isset($_GET['override_prefs']) || isset($_GET['override_allergies']);

    $products = array_filter($products, function($p) use ($isAIOverride, $userPreferences) {
        // 1. Toujours supprimer ce qui est INTERDIT (allergies)
        if ($p->forbidden) return false;
        
        // 2. Si l'utilisateur a saisi des préférences manuellement (Mode IA),
        // on ne montre QUE les produits qui correspondent (Recommandés)
        if ($isAIOverride && !empty($userPreferences)) {
            return $p->recommended;
        }
        
        return true;
    });

    usort($products, function ($a, $b) {
        if ($a->recommended !== $b->recommended) {
            return $a->recommended ? -1 : 1;
        }
        return 0;
    });

    echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'error' => true,
        'message' => 'Erreur serveur',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}