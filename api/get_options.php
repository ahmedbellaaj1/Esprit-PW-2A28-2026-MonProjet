<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Récupérer les préférences (utilisateurs + système)
    $stmtPref = $pdo->query("
        SELECT nom FROM options_systeme WHERE type = 'preference'
        UNION
        SELECT DISTINCT type_preference FROM preferences_alimentaires WHERE type_preference != ''
    ");
    $preferences = $stmtPref->fetchAll(PDO::FETCH_COLUMN);

    // 2. Récupérer les allergies (utilisateurs + système)
    $stmtAll = $pdo->query("
        SELECT nom FROM options_systeme WHERE type = 'allergy'
        UNION
        SELECT DISTINCT nom_allergie FROM allergies WHERE nom_allergie != ''
    ");
    $allergies = $stmtAll->fetchAll(PDO::FETCH_COLUMN);

    $res = [
        'preferences' => array_map(function($p) { return ['id' => $p, 'name' => $p]; }, $preferences),
        'allergies' => array_map(function($a) { return ['id' => $a, 'name' => $a]; }, $allergies)
    ];

    echo json_encode($res);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
