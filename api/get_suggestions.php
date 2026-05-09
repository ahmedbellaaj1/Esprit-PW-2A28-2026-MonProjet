<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    echo json_encode([]);
    exit;
}

try {
    // 1. Récupérer les allergies de l'utilisateur
    $stmtAll = $pdo->prepare("SELECT nom_allergie FROM allergies WHERE id_user = ?");
    $stmtAll->execute([$user_id]);
    $userAllergies = array_map('mb_strtolower', $stmtAll->fetchAll(PDO::FETCH_COLUMN));

    // 2. Récupérer les préférences de l'utilisateur
    $stmtPref = $pdo->prepare("SELECT type_preference FROM preferences_alimentaires WHERE id_user = ?");
    $stmtPref->execute([$user_id]);
    $userPrefs = array_filter(array_map('mb_strtolower', $stmtPref->fetchAll(PDO::FETCH_COLUMN)));

    // 3. Récupérer tous les produits
    require_once '../model/ProductModel.php';
    require_once '../model/Product.php';
    $products = ProductModel::getAll($pdo);

    foreach ($products as $p) {
        $productText = mb_strtolower($p->getNom() . ' ' . $p->getCategorie() . ' ' . ($p->getDescription() ?? ''));

        // 🔴 Vérification des allergies (AI simple par mot-clé)
        foreach ($userAllergies as $allergy) {
            if (!empty($allergy) && mb_stripos($productText, $allergy) !== false) {
                $p->forbidden = true;
                $p->allowed = false;
                break;
            }
        }

        // 🟢 Vérification des préférences (AI simple par mot-clé)
        if (!$p->forbidden) {
            foreach ($userPrefs as $pref) {
                if (!empty($pref) && mb_stripos($productText, $pref) !== false) {
                    $p->recommended = true;
                    break;
                }
            }
        }
    }

    // 4. Tri : Recommandés d'abord, Interdits à la fin
    usort($products, function($a, $b) {
        if ($a->forbidden !== $b->forbidden) return $a->forbidden ? 1 : -1;
        if ($a->recommended !== $b->recommended) return $a->recommended ? -1 : 1;
        return 0;
    });

    // Retourner les 10 meilleures suggestions
    echo json_encode(array_slice($products, 0, 10));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}