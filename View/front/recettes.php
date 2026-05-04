<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../Controller/RecetteController.php';
require_once __DIR__ . '/../../Controller/IngredientController.php';
require_once __DIR__ . '/../../Controller/FavorisController.php';

$controller = new RecetteController();
$ingredientController = new IngredientController();
$favorisController = new FavorisController();

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
        $result = $controller->handleFrontPost();
    }
    if ($result === 'redirect') {
        header('Location: ' . basename(__FILE__));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $scrollY = filter_var($_GET['scroll_y'] ?? null, FILTER_VALIDATE_INT);
    if ($scrollY !== false && $scrollY !== null && $scrollY >= 0) {
        $_SESSION['recette_scroll_y_once'] = $scrollY;
    }
}

// Handle favoris action
if (isset($_GET['action']) && $_GET['action'] === 'toggle_favori') {
    $recetteId = (int) ($_GET['id_recette'] ?? 0);
    $idUser = (int) ($_SESSION['user_id'] ?? 1);
    if ($recetteId > 0) {
        if (!isset($_SESSION['recette_favoris'])) {
            $_SESSION['recette_favoris'] = [];
        }
        $key = array_search($recetteId, $_SESSION['recette_favoris'], true);
        if ($key !== false) {
            // Supprimer du favoris
            unset($_SESSION['recette_favoris'][$key]);
            $favorisController->removeFavorite($recetteId, $idUser);
        } else {
            // Ajouter au favoris
            $_SESSION['recette_favoris'][] = $recetteId;
            $favorisController->addFavorite($recetteId, $idUser);
        }
        $_SESSION['recette_favoris'] = array_values($_SESSION['recette_favoris']);
    }
    header('Location: ' . basename(__FILE__));
    exit;
}

$sortBy = $_GET['sort_by'] ?? 'date_creation';
$sortOrder = $_GET['sort_order'] ?? 'DESC';
$recipes = $controller->allRecipes($sortBy, $sortOrder);
$flash = $_SESSION['recette_flash'] ?? null;
unset($_SESSION['recette_flash']);
$formErrors = $_SESSION['recette_form_errors'] ?? [];
unset($_SESSION['recette_form_errors']);
$old = $_SESSION['recette_form_old'] ?? [];
unset($_SESSION['recette_form_old']);

// Initialize favoris session if needed
$idUser = (int) ($_SESSION['user_id'] ?? 1);
if (!isset($_SESSION['recette_favoris'])) {
    // Charger les favoris depuis la base de données
    $favoritesFromDb = $favorisController->getFavoritesByUser($idUser);
    $_SESSION['recette_favoris'] = array_map(function ($fav) {
        return (int) $fav['id_recette'];
    }, $favoritesFromDb);
} else {
    // Recharger depuis la DB pour avoir les données à jour
    $favoritesFromDb = $favorisController->getFavoritesByUser($idUser);
    $favoritesIds = array_map(function ($fav) {
        return (int) $fav['id_recette'];
    }, $favoritesFromDb);
    $_SESSION['recette_favoris'] = $favoritesIds;
}

$formAction = $_SERVER['SCRIPT_NAME'] ?? ('/' . basename(__FILE__));
$ingredients = $ingredientController->allIngredientsWithUser();
$selectedRecetteId = (int) ($_GET['id_recette'] ?? ($recipes[0]['id_recette'] ?? 0));
$linkedIngredients = $selectedRecetteId >= 0 ? $ingredientController->ingredientsByRecette($selectedRecetteId) : [];
$scrollYOnLoad = (int) ($_SESSION['recette_scroll_y_once'] ?? 0);
unset($_SESSION['recette_scroll_y_once']);

// Add favoris status to recipes
$recipesWithFavoris = array_map(function ($r) {
    $r['is_favori'] = in_array((int) $r['id_recette'], $_SESSION['recette_favoris'], true) ? 1 : 0;
    return $r;
}, $recipes);

require __DIR__ . '/recettes_public.php';
