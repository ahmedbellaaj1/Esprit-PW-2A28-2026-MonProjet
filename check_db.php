<?php
require_once __DIR__ . '/config/database.php';
$pdo = getPdo();
$stmt = $pdo->query("SHOW COLUMNS FROM user_health");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($columns);
