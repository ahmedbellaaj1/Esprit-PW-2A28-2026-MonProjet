<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/ReviewController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) $body = $_POST;

$action  = trim((string)($body['action']  ?? ''));
$id_avis = filter_var($body['id_avis'] ?? null, FILTER_VALIDATE_INT);

if (!$id_avis || $id_avis <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'ID avis invalide']);
    exit;
}

try {
    $controller = new ReviewController();

    $result = match($action) {
        'approve' => $controller->approveReview($id_avis),
        'reject'  => $controller->rejectReview($id_avis),
        'delete'  => $controller->deleteReview($id_avis),
        default   => ['success' => false, 'message' => 'Action inconnue']
    };

    echo json_encode(['ok' => $result['success'], 'message' => $result['message']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
