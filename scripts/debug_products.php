<?php
include 'config/db.php';
$stmt = $pdo->query("SELECT nom, categorie, description FROM produits");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $r) {
    echo $r['nom'] . " | " . $r['categorie'] . "\n";
}
?>
