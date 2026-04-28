<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$product_id = $_GET['id'] ?? 0;

// produit
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode([]);
    exit;
}

// ingrédients (optionnel)
$stmt = $pdo->prepare("SELECT nom FROM ingredients WHERE produit_id = ?");
$stmt->execute([$product_id]);
$product['ingredients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// nutrition (optionnel)
$stmt = $pdo->prepare("SELECT * FROM nutrition WHERE produit_id = ?");
$stmt->execute([$product_id]);
$product['nutrition'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// statut simple
$product['recommended'] = ($product['calories'] < 200);
$product['allowed'] = !$product['recommended'];
$product['check'] = false;
$product['forbidden'] = false;

echo json_encode($product);