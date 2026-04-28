<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;

// suggestions simples (low calories)
$stmt = $pdo->prepare("SELECT * FROM produits WHERE calories < 300 LIMIT 6");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as &$product) {
    $product['recommended'] = true;
    $product['allowed'] = true;
    $product['check'] = false;
    $product['forbidden'] = false;
}

echo json_encode($products);