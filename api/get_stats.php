<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

try {
    // Nombre total d'utilisateurs
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmtUsers->fetchColumn();

    // Nom de la table produits (gestion casse)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmtCheck->execute(['produits']);
    $productsTable = ((int)$stmtCheck->fetchColumn() > 0) ? 'produits' : 'Produits';

    // Nombre total de produits
    $stmtProducts = $pdo->query("SELECT COUNT(*) FROM $productsTable");
    $totalProducts = $stmtProducts->fetchColumn();

    // Nombre de catégories uniques
    $stmtCategories = $pdo->query("SELECT COUNT(DISTINCT categorie) FROM $productsTable");
    $totalCategories = $stmtCategories->fetchColumn();

    // Moyenne calorique
    $stmtCalories = $pdo->query("SELECT AVG(calories) FROM $productsTable");
    $avgCalories = round($stmtCalories->fetchColumn(), 0);

    echo json_encode([
        'total_users' => $totalUsers,
        'total_products' => $totalProducts,
        'total_categories' => $totalCategories,
        'avg_calories' => $avgCalories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
