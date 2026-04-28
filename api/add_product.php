<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

require_once '../controller/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

$response = ProductController::addProduct($pdo, $_POST);
if ($response['error']) {
    http_response_code(500);
}
echo json_encode($response);
