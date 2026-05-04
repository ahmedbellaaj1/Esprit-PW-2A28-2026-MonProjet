<?php
/**
 * API ChatBot - Récupérer la conversation
 * GET /api/chatbot/conversation.php?id_conversation=1
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/ChatBotController.php';

try {
    $id_conversation = filter_var($_GET['id_conversation'] ?? null, FILTER_VALIDATE_INT);

    if (!$id_conversation) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID conversation invalide'
        ]);
        exit;
    }

    $controller = new ChatBotController();
    $response = $controller->getConversation($id_conversation);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
