<?php
include '../config/db.php';
try {
    // Get last user
    $user = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("No users found.");
    }
    $user_id = $user['id'];
    echo "Last User: " . $user['nom'] . " (ID: $user_id)\n";
    echo "Preferences in 'users' table: " . $user['preferences'] . "\n";
    echo "Allergies in 'users' table: " . $user['allergies'] . "\n";

    // Get preferences from preferences_alimentaires
    $prefs = $pdo->prepare("SELECT type_preference, poids, age, calories FROM preferences_alimentaires WHERE id_user = ?");
    $prefs->execute([$user_id]);
    echo "Data in 'preferences_alimentaires' table:\n";
    print_r($prefs->fetchAll(PDO::FETCH_ASSOC));

    // Get allergies from allergies
    $alls = $pdo->prepare("SELECT nom_allergie FROM allergies WHERE id_user = ?");
    $alls->execute([$user_id]);
    echo "Allergies in 'allergies' table:\n";
    print_r($alls->fetchAll(PDO::FETCH_COLUMN));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
