<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$name = trim($data['name'] ?? '');

if (!$type || !$name) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO options_systeme (type, nom) VALUES (?, ?)");
    $stmt->execute([$type, $name]);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
