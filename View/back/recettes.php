<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../Controller/RecetteController.php';

$controller = new RecetteController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->handleAdminPost();
    if ($result === 'redirect') {
        header('Location: ' . basename(__FILE__));
        exit;
    }
}

$recipes = $controller->allRecipes();
$flash = $_SESSION['recette_flash'] ?? null;
unset($_SESSION['recette_flash']);
$formErrors = $_SESSION['recette_form_errors'] ?? [];
unset($_SESSION['recette_form_errors']);
$old = $_SESSION['recette_form_old'] ?? [];
unset($_SESSION['recette_form_old']);

$formAction = $_SERVER['SCRIPT_NAME'] ?? ('/' . basename(__FILE__));

require __DIR__ . '/recettes_admin.php';
