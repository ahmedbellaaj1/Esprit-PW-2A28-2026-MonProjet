<?php
/**
 * API ChatBot - Traiter les messages
 * POST /api/chatbot/message.php
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../Controller/ChatBotController.php';
    
    $controller = new ChatBotController();
    $response = $controller->handleMessage();
    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    error_log("ChatBot Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
