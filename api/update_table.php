<?php
include '../config/db.php';
try {
    $sql = "ALTER TABLE preferences_alimentaires 
            ADD COLUMN poids DECIMAL(5,2) NULL,
            ADD COLUMN age INT(11) NULL,
            ADD COLUMN calories INT(11) NULL";
    $pdo->exec($sql);
    echo "Table 'preferences_alimentaires' updated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
