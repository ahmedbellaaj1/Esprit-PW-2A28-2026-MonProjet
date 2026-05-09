<?php
session_start();
include '../config/db.php';
require_once '../controller/AdminController.php';

header('Content-Type: application/json');

try {
    $stats = AdminController::getStats($pdo);
    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
