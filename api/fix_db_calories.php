<?php
include '../config/db.php';
try {
    // Determine the table name (case sensitivity)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmtCheck->execute(['produits']);
    $tableName = ((int)$stmtCheck->fetchColumn() > 0) ? 'produits' : 'Produits';

    // Check if calories column already exists
    $stmtCols = $pdo->query("DESCRIBE $tableName");
    $columns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('calories', $columns)) {
        $pdo->exec("ALTER TABLE $tableName ADD COLUMN calories INT DEFAULT 0 AFTER description");
        echo "Column 'calories' added successfully to '$tableName'.\n";
    } else {
        echo "Column 'calories' already exists in '$tableName'.\n";
    }

    // Set some default calories for existing products
    $stmt = $pdo->query("UPDATE $tableName SET calories = FLOOR(RAND() * (800 - 50 + 1) + 50) WHERE calories = 0 OR calories IS NULL");
    echo "Default calories set for products.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
