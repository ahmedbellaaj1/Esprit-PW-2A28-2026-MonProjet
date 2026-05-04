<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Controller/OrderController.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }

    // Accept both JSON body and form-data
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = $_POST;
    }

    $id_produit              = filter_var($body['id_produit']              ?? null, FILTER_VALIDATE_INT);
    $id_utilisateur          = filter_var($body['id_utilisateur']          ?? null, FILTER_VALIDATE_INT);
    $quantite                = filter_var($body['quantite']                ?? null, FILTER_VALIDATE_INT);
    $prix_total              = filter_var($body['prix_total']              ?? null, FILTER_VALIDATE_FLOAT);
    $adresse_livraison       = trim((string) ($body['adresse_livraison']       ?? ''));
    $mode_livraison          = trim((string) ($body['mode_livraison']          ?? 'standard'));
    $date_livraison_souhaitee= trim((string) ($body['date_livraison_souhaitee']?? ''));
    $methode_paiement        = trim((string) ($body['methode_paiement']        ?? 'cash'));
    $stripe_payment_intent_id= trim((string) ($body['stripe_payment_intent_id']?? ''));

    if (!$id_produit || !$id_utilisateur || !$quantite || $quantite <= 0 || !$prix_total || $prix_total <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Données invalides (id_produit, id_utilisateur, quantite, prix_total requis)']);
        exit;
    }
    if (empty($adresse_livraison)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Adresse de livraison requise']);
        exit;
    }

    $productController = new ProductController();
    $product = $productController->find($id_produit);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Produit non trouvé']);
        exit;
    }
    if ((int)$quantite > (int)($product['quantite_disponible'] ?? 0)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Stock insuffisant (disponible : ' . $product['quantite_disponible'] . ')']);
        exit;
    }

    $statut = ($methode_paiement === 'carte') ? 'confirmee' : 'en-cours';

    $orderController = new OrderController();
    $result = $orderController->save([
        'id_produit'               => $id_produit,
        'id_utilisateur'           => $id_utilisateur,
        'quantite'                 => $quantite,
        'prix_total'               => $prix_total,
        'adresse_livraison'        => $adresse_livraison,
        'mode_livraison'           => $mode_livraison,
        'date_livraison_souhaitee' => $date_livraison_souhaitee,
        'methode_paiement'         => $methode_paiement,
        'stripe_payment_intent_id' => $stripe_payment_intent_id,
        'statut'                   => $statut,
    ]);

    if (!$result['ok']) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Erreur lors de la création de la commande']);
        exit;
    }

    // Decrease stock
    $pdo = Database::connection();
    $pdo->prepare('UPDATE produits SET quantite_disponible = quantite_disponible - ? WHERE id_produit = ?')
        ->execute([$quantite, $id_produit]);

    echo json_encode(['ok' => true, 'message' => 'Commande créée avec succès', 'order_id' => $result['id'] ?? null]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
