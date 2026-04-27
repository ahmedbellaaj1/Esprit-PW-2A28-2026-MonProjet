<?php
/**
 * Setup and Migrations File
 * Run this file once to ensure all database tables and columns are created
 * 
 * Access: http://localhost/WEB/setup.php
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connection();
    
    // Get database name from PDO
    $dbName = DB_NAME;
    
    echo "<h1>GreenBite Setup & Migrations</h1>";
    echo "<hr>";
    
    // Migration 1: Add quantite_disponible column to produits table
    echo "<h2>Migration 1: Add quantite_disponible to produits</h2>";
    try {
        $pdo->exec("ALTER TABLE produits ADD COLUMN IF NOT EXISTS quantite_disponible INT NOT NULL DEFAULT 0 AFTER image;");
        echo "<p style='color:green;'>✓ Column quantite_disponible added successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange;'>⚠ " . $e->getMessage() . "</p>";
    }
    
    // Migration 2: Add payment columns to commandes table
    echo "<h2>Migration 2: Add payment columns to commandes</h2>";
    try {
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS methode_paiement ENUM('cash', 'carte') NOT NULL DEFAULT 'cash' AFTER adresse_livraison;");
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS numero_carte VARCHAR(20) DEFAULT NULL AFTER methode_paiement;");
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS nom_titulaire VARCHAR(100) DEFAULT NULL AFTER numero_carte;");
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS date_expiration VARCHAR(5) DEFAULT NULL AFTER nom_titulaire;");
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS cvv VARCHAR(3) DEFAULT NULL AFTER date_expiration;");
        echo "<p style='color:green;'>✓ Payment columns added successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange;'>⚠ " . $e->getMessage() . "</p>";
    }
    
    // Migration 3: Add delivery mode and date to commandes table
    echo "<h2>Migration 3: Add delivery mode to commandes</h2>";
    try {
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS mode_livraison ENUM('standard', 'express') NOT NULL DEFAULT 'standard' AFTER statut;");
        $pdo->exec("ALTER TABLE commandes ADD COLUMN IF NOT EXISTS date_livraison_souhaitee DATE DEFAULT NULL AFTER mode_livraison;");
        echo "<p style='color:green;'>✓ Delivery mode columns added successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange;'>⚠ " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>Database Status</h2>";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES FROM " . $dbName)->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Tables in database:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check produits columns
    echo "<h3>Columns in 'produits' table:</h3>";
    $columns = $pdo->query("DESCRIBE produits")->fetchAll();
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li><strong>" . $col['Field'] . "</strong> (" . $col['Type'] . ")</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p style='color:green; font-weight:bold;'>✓ Setup completed successfully!</p>";
    echo "<p><a href='View/back-office/dashboard.php'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        h1 { color: #0f766e; }
        h2 { color: #14b8a6; margin-top: 20px; }
        hr { border: 1px solid #e2e8f0; }
        a { color: #0f766e; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<!-- Content is generated above by PHP -->
</body>
</html>
