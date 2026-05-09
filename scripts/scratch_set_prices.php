<?php
include __DIR__ . '/api/../config/db.php';
try {
    $tableName = 'produits';

    $stmt = $pdo->query("SELECT id FROM $tableName WHERE prix IS NULL OR prix = 0");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($products as $p) {
        $randomPrice = mt_rand(300, 1500) / 100;
        $updateStmt = $pdo->prepare("UPDATE $tableName SET prix = ? WHERE id = ?");
        $updateStmt->execute([$randomPrice, $p['id']]);
        $count++;
    }

    echo "Successfully updated $count products with random DT prices.\n";
} catch (PDOException $e) {
    try {
        $tableName = 'Produits'; // Try uppercase P
        $stmt = $pdo->query("SELECT id FROM $tableName WHERE prix IS NULL OR prix = 0");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($products as $p) {
            $randomPrice = mt_rand(300, 1500) / 100;
            $updateStmt = $pdo->prepare("UPDATE $tableName SET prix = ? WHERE id = ?");
            $updateStmt->execute([$randomPrice, $p['id']]);
            $count++;
        }

        echo "Successfully updated $count products with random DT prices (table Produits).\n";
    } catch (PDOException $e2) {
        echo "Error: " . $e2->getMessage() . "\n";
    }
}
