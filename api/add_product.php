<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $nom = trim($_POST['nom'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $calories = intval($_POST['calories'] ?? 0);
    $prix = floatval($_POST['prix'] ?? 0.00);

    if (empty($nom) || empty($categorie)) {
        echo json_encode(['error' => true, 'message' => 'Le nom et la catégorie sont obligatoires.']);
        exit;
    }

    // Vérifier si la table s'appelle 'produits' ou 'Produits'
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmtCheck->execute(['produits']);
    $tableName = ((int)$stmtCheck->fetchColumn() > 0) ? 'produits' : 'Produits';

    $stmt = $pdo->prepare("INSERT INTO $tableName (nom, categorie, description, calories, prix) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $categorie, $description, $calories, $prix]);

    echo json_encode(['error' => false, 'message' => 'Produit ajouté avec succès !']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Erreur serveur', 'details' => $e->getMessage()]);
}
