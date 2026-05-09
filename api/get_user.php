<?php
include "../config/db.php";
require_once __DIR__ . '/../model/UserModel.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['error' => true, 'message' => 'Email requis']);
    exit;
}

try {
    $user = UserModel::getByEmail($pdo, $email);

    if ($user) {
        // JsonSerializable handles the conversion
        echo json_encode($user);
    } else {
        echo json_encode(['error' => true, 'message' => 'Utilisateur non trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
