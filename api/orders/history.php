<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Model/Order.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Récupérer l'ID utilisateur depuis les paramètres GET
    $id_user = filter_var($_GET['id_user'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$id_user) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'ID utilisateur requis et valide'
        ]);
        exit;
    }

    $controller = new OrderController();
    
    // Récupérer les commandes de l'utilisateur
    $orders = $controller->list(['id_user' => (string) $id_user]);
    
    if (empty($orders)) {
        echo json_encode([
            'ok' => true,
            'data' => [],
            'message' => 'Aucune commande trouvée'
        ]);
        exit;
    }

    // Formater les résultats
    $formattedOrders = array_map(function ($order) {
        return [
            'id_commande' => (int) $order['id_commande'],
            'id_produit' => (int) $order['id_produit'],
            'produit_nom' => $order['produit_nom'],
            'produit_marque' => $order['produit_marque'],
            'quantite' => (int) $order['quantite'],
            'prix_total' => (float) $order['prix_total'],
            'date_commande' => $order['date_commande'],
            'statut' => $order['statut'],
            'mode_livraison' => $order['mode_livraison'],
            'adresse_livraison' => $order['adresse_livraison'],
            'date_livraison_souhaitee' => $order['date_livraison_souhaitee']
        ];
    }, $orders);

    echo json_encode([
        'ok' => true,
        'data' => $formattedOrders,
        'count' => count($formattedOrders),
        'total_amount' => array_sum(array_column($formattedOrders, 'prix_total'))
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
