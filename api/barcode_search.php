<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$barcode = trim((string) ($_GET['code'] ?? ''));

if ($barcode === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Code-barres manquant.']);
    exit;
}

try {
    $pdo = Database::connection();

    // Recherche exacte par code_barre
    $stmt = $pdo->prepare('SELECT * FROM produits WHERE code_barre = :code LIMIT 1');
    $stmt->execute(['code' => $barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        // Tentative de recherche partielle si code exact non trouvé
        $stmt2 = $pdo->prepare('SELECT * FROM produits WHERE code_barre LIKE :code LIMIT 1');
        $stmt2->execute(['code' => '%' . $barcode . '%']);
        $product = $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    if (!$product) {
        echo json_encode(['ok' => false, 'message' => 'Aucun produit trouvé pour ce code-barres : ' . htmlspecialchars($barcode)]);
        exit;
    }

    echo json_encode(['ok' => true, 'product' => $product]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erreur serveur.']);
}
