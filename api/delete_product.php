<?php
session_start();
include "../config/db.php";

header('Content-Type: application/json');

require_once '../controller/ProductController.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$response = ProductController::deleteProduct($pdo, $id);
echo json_encode($response);
