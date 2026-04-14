<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$id = (int) ($_POST['id'] ?? 0);
$controller = new ProductController();
$controller->delete($id);

redirect('products.php?ok=1');
