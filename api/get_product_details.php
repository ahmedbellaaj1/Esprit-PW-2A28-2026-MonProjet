<?php
session_start();
include __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Product.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$product_id = $_GET['id'] ?? 0;

// produit
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$product_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode([]);
    exit;
}

$p = new Product($row['nom'], $row['categorie'], $row['description'], $row['calories'], $row['prix']);
$p->setId($row['id']);

// ingrédients (optionnel)
$stmt = $pdo->prepare("SELECT nom FROM ingredients WHERE produit_id = ?");
$stmt->execute([$product_id]);
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// nutrition (optionnel)
$stmt = $pdo->prepare("SELECT * FROM nutrition WHERE produit_id = ?");
$stmt->execute([$product_id]);
$nutrition = $stmt->fetchAll(PDO::FETCH_ASSOC);

// statut simple
$p->recommended = ($p->getCalories() < 200);
$p->allowed = !$p->recommended;

// On convertit en tableau pour ajouter les champs dynamiques non présents dans la classe si besoin,
// ou on utilise jsonSerialize et on merge.
$data = $p->jsonSerialize();
$data['ingredients'] = $ingredients;
$data['nutrition'] = $nutrition;

echo json_encode($data);