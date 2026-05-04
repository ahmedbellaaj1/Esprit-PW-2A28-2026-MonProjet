<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../Controller/RecetteController.php';
require_once __DIR__ . '/../../Controller/IngredientController.php';

$controller = new RecetteController();
$ingredientController = new IngredientController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scrollY = filter_var($_POST['scroll_y'] ?? null, FILTER_VALIDATE_INT);
    if ($scrollY !== false && $scrollY !== null && $scrollY >= 0) {
        $_SESSION['recette_scroll_y_once'] = $scrollY;
    }
    $action = (string) ($_POST['action'] ?? '');
    if (str_starts_with($action, 'ingredient_')) {
        $_POST['action'] = substr($action, strlen('ingredient_'));
        $result = $ingredientController->handlePost();
    } else {
        $result = $controller->handleAdminPost();
    }
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
$ingredients = $ingredientController->allIngredientsWithUser();
$selectedRecetteId = (int) ($_GET['id_recette'] ?? ($recipes[0]['id_recette'] ?? 0));
$linkedIngredients = $selectedRecetteId >= 0 ? $ingredientController->ingredientsByRecette($selectedRecetteId) : [];
$scrollYOnLoad = (int) ($_SESSION['recette_scroll_y_once'] ?? 0);
unset($_SESSION['recette_scroll_y_once']);

require __DIR__ . '/recettes_admin.php';
