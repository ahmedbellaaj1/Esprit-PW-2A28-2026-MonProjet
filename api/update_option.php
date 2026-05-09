<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$oldName = $data['id'] ?? '';
$newName = trim($data['name'] ?? '');

if (!$type || !$oldName || !$newName) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Modifier dans la table système
    $stmtSys = $pdo->prepare("UPDATE options_systeme SET nom = ? WHERE type = ? AND nom = ?");
    $stmtSys->execute([$newName, $type, $oldName]);

    // Modifier chez les utilisateurs
    if ($type === 'preference') {
        $stmtUser = $pdo->prepare("UPDATE preferences_alimentaires SET type_preference = ? WHERE type_preference = ?");
    } else {
        $stmtUser = $pdo->prepare("UPDATE allergies SET nom_allergie = ? WHERE nom_allergie = ?");
    }
    $stmtUser->execute([$newName, $oldName]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
