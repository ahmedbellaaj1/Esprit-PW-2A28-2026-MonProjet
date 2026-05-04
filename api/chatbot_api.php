<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../Controller/ChatbotController.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    
    if (empty(trim($message))) {
        echo json_encode(['status' => 'error', 'reply' => 'Veuillez entrer un message.']);
        exit;
    }
    
    $bot = new ChatbotController();
    $reply = $bot->processMessage($message);
    
    echo json_encode(['status' => 'success', 'reply' => $reply]);
    exit;
}

echo json_encode(['status' => 'error', 'reply' => 'Requête invalide.']);
