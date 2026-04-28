<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $sort = trim($_GET['sort'] ?? '');

    // 🔐 USER ID depuis SESSION (IMPORTANT) ou GET (fallback)
    $user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? null;

    if (!$user_id) {
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

    /* =========================
       USER DATA
    ========================== */
    $userAllergies = [];
    try {
        $stmt = $pdo->prepare("SELECT nom_allergie FROM allergies WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $userAllergies = array_map('trim', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Exception $e) {
        // Table allergies might not exist, ignore
    }

    $userPreferences = [];
    try {
        $stmt = $pdo->prepare("SELECT type_preference FROM preferences_alimentaires WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $userPreferences = array_filter(array_map('trim', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    } catch (Exception $e) {
        // Table preferences_alimentaires might not exist, ignore
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
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    foreach ($products as &$p) {

        $p['forbidden'] = false;
        $p['check'] = false;
        $p['allowed'] = true;
        $p['recommended'] = false;

        // 🔴 ALLERGIES FILTER
        if ($stmtAllergen) {
            $stmtAllergen->execute([$p['id']]);
            $allergenes = $stmtAllergen->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allergenes as $a) {
                if (in_array($a['allergene'], $userAllergies, true)) {

                    if (($a['niveau'] ?? '') === 'interdit') {
                        $p['forbidden'] = true;
                        $p['allowed'] = false;
                    }

                    if (($a['niveau'] ?? '') === 'a_verifier') {
                        $p['check'] = true;
                    }
                }
            }
        }

        // 🟢 PREFERENCES FILTER (AI)
        if (!empty($userPreferences)) {

            if ($stmtProductPrefs) {
                $stmtProductPrefs->execute([$p['id']]);
                $productPrefs = $stmtProductPrefs->fetchAll(PDO::FETCH_ASSOC);

                foreach ($productPrefs as $prefRow) {
                    if (
                        in_array(trim($prefRow['preference']), $userPreferences, true)
                        && (int)$prefRow['compatible'] === 1
                    ) {
                        $p['recommended'] = true;
                        break;
                    }
                }
            }

            // fallback AI text matching
            if (!$p['recommended']) {
                $text = mb_strtolower($p['nom'] . ' ' . $p['categorie'] . ' ' . $p['description']);

                foreach ($userPreferences as $pref) {
                    if ($pref !== '' && mb_stripos($text, mb_strtolower($pref)) !== false) {
                        $p['recommended'] = true;
                        break;
                    }
                }
            }
        }
    }
    unset($p);

    /* =========================
       SORTING AI RESULT
    ========================== */
    usort($products, function ($a, $b) {

        if ($a['forbidden'] !== $b['forbidden']) {
            return $a['forbidden'] ? 1 : -1;
        }

        if ($a['recommended'] !== $b['recommended']) {
            return $a['recommended'] ? -1 : 1;
        }

        return 0;
    });

    echo json_encode($products, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'error' => true,
        'message' => 'Erreur serveur',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}