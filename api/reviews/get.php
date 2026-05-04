<?php
/**
 * API pour récupérer les avis d'un produit
 * GET /api/reviews/get.php?id_produit=1
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/ReviewController.php';

try {
    $id_produit = filter_var($_GET['id_produit'] ?? null, FILTER_VALIDATE_INT);

    if (!$id_produit) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID produit invalide'
        ]);
        exit;
    }

    $controller = new ReviewController();
    $response = $controller->getProductReviews($id_produit);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
