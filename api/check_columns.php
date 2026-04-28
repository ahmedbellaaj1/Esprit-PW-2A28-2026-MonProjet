<?php
include '../config/db.php';
try {
    $stmt = $pdo->query("DESCRIBE produits");
    echo "Columns of 'produits':\n";
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
