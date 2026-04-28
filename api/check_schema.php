<?php
include '../config/db.php';
$tables = ['users', 'preferences_alimentaires', 'preference_options', 'allergies', 'allergy_options'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "Columns of '$table':\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "\n";
    } catch (Exception $e) {
        echo "Error on $table: " . $e->getMessage() . "\n";
    }
}
