<?php
/**
 * API ChatBot - Nouvelle conversation
 * POST /api/chatbot/start.php
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/ChatBotController.php';

try {
    $controller = new ChatBotController();
    $response = $controller->startNewConversation();
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
