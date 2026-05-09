<?php
include __DIR__ . '/api/../config/db.php';
try {
    $pdo->exec("ALTER TABLE produits ADD COLUMN prix DECIMAL(10,2) DEFAULT 0.00");
    echo "Column 'prix' added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("ALTER TABLE Produits ADD COLUMN prix DECIMAL(10,2) DEFAULT 0.00");
    echo "Column 'prix' added successfully to Produits.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
