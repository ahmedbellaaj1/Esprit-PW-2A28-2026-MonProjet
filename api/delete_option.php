<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$name = $data['id'] ?? '';

if (!$type || !$name) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Supprimer de la table système
    $stmtSys = $pdo->prepare("DELETE FROM options_systeme WHERE type = ? AND nom = ?");
    $stmtSys->execute([$type, $name]);

    // Supprimer des utilisateurs
    if ($type === 'preference') {
        $stmtUser = $pdo->prepare("DELETE FROM preferences_alimentaires WHERE type_preference = ?");
    } else {
        $stmtUser = $pdo->prepare("DELETE FROM allergies WHERE nom_allergie = ?");
    }
    $stmtUser->execute([$name]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
