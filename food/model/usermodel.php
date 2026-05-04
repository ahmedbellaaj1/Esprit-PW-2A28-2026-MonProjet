<?php
class UserModel {
public static function createUser($pdo, $data) {
$sql = "INSERT INTO users(age, weight, calories) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$data['age'], $data['weight'], $data['calories']]);
return $pdo->lastInsertId();
}
public static function addAllergy($pdo, $user_id, $allergy_id) {
$stmt = $pdo->prepare("INSERT INTO user_allergies VALUES (?, ?)");
return $stmt->execute([$user_id, $allergy_id]);
}
public static function addPreference($pdo, $user_id, $pref_id) {
$stmt = $pdo->prepare("INSERT INTO user_preferences VALUES (?, ?)");
return $stmt->execute([$user_id, $pref_id]);
}
}
?>