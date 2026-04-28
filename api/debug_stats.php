<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

try {
    echo "--- Users ---\n";
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
    echo "Users: " . $stmtUsers->fetchColumn() . "\n";

    echo "--- Products ---\n";
    // Try simple query first
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM produits");
        echo "Table 'produits' works: " . $stmt->fetchColumn() . "\n";
        $productsTable = 'produits';
    } catch (Exception $e) {
        echo "Table 'produits' fails: " . $e->getMessage() . "\n";
        $stmt = $pdo->query("SELECT COUNT(*) FROM Produits");
        echo "Table 'Produits' works: " . $stmt->fetchColumn() . "\n";
        $productsTable = 'Produits';
    }

    echo "--- Categories ---\n";
    $stmtCategories = $pdo->query("SELECT COUNT(DISTINCT categorie) FROM $productsTable");
    echo "Categories: " . $stmtCategories->fetchColumn() . "\n";

    echo "--- Calories ---\n";
    $stmtCalories = $pdo->query("SELECT AVG(calories) FROM $productsTable");
    echo "Avg Calories: " . $stmtCalories->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "GLOBAL ERROR: " . $e->getMessage() . "\n";
}
