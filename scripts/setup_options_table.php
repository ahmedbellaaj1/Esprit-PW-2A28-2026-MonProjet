<?php
include 'config/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS options_systeme (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('preference', 'allergy'),
        nom VARCHAR(255) UNIQUE
    )");
    echo "Table options_systeme prête.";
} catch(Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
