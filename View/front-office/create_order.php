<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}


$controller = new OrderController();
$result = $controller->createFromFront($_POST);

if (!$result['ok']) {
    $idProduit = (int) ($_POST['id_produit'] ?? 0);
    $params = ['id' => $idProduit];
    $errors = $result['errors'] ?? [];
    foreach ($errors as $field => $message) {
        $params['err_' . $field] = (string) $message;
    }
    $params['old_id_utilisateur'] = (string) ($_POST['id_utilisateur'] ?? '');
    $params['old_quantite'] = (string) ($_POST['quantite'] ?? '1');
    $params['old_statut'] = (string) ($_POST['statut'] ?? 'en-cours');
    $params['old_adresse_livraison'] = (string) ($_POST['adresse_livraison'] ?? '');
    redirect('product.php?' . http_build_query($params));
}

redirect('index.php?order=success');
