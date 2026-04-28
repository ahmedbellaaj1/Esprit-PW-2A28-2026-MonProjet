<?php
$host = "127.0.0.1";
$port = "3306"; // très important
$dbname = "greenbite";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion OK"; // TEST
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}