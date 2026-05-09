<?php
session_start();
include '../config/db.php';
require_once '../controller/ChatController.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$response = ChatController::handleMessage($pdo, $message, $user_id);

echo json_encode($response);
