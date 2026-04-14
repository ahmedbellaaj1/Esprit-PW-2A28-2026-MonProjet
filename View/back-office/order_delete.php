<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders.php');
}

$id = (int) ($_POST['id'] ?? 0);
$controller = new OrderController();
$controller->delete($id);

redirect('orders.php?ok=1');
