<?php
/**
 * API pour ajouter un avis
 * POST /api/reviews/add.php
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/ReviewController.php';

try {
    $controller = new ReviewController();
    $response = $controller->addReview();
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
