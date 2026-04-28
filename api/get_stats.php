<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

try {
    // 1. Nombre total de préférences alimentaires
    $stmtPref = $pdo->query("SELECT COUNT(*) FROM preferences_alimentaires WHERE type_preference IS NOT NULL AND type_preference != ''");
    $totalPrefs = $stmtPref->fetchColumn();
    
    // 2. Moyenne des calories visées (depuis preferences_alimentaires)
    $stmtCal = $pdo->query("SELECT AVG(calories) FROM preferences_alimentaires WHERE calories > 0");
    $avgCalories = round($stmtCal->fetchColumn() ?? 0, 0);

    // 3. Moyenne d'âge
    $stmtAge = $pdo->query("SELECT AVG(age) FROM preferences_alimentaires WHERE age > 0");
    $avgAge = round($stmtAge->fetchColumn() ?? 0, 1);

    // 4. Poids moyen (utilisé comme indicateur de poids idéal/moyen)
    $stmtPoids = $pdo->query("SELECT AVG(poids) FROM preferences_alimentaires WHERE poids > 0");
    $avgPoids = round($stmtPoids->fetchColumn() ?? 0, 1);

    echo json_encode([
        'total_preferences' => $totalPrefs,
        'avg_calories' => $avgCalories,
        'avg_age' => $avgAge,
        'avg_poids' => $avgPoids
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
