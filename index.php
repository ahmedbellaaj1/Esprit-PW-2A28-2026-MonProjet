<?php
session_start();

/**
 * FRONT CONTROLLER - ROUTER
 */

// URL de base absolue (utilisée dans toutes les vues)
define('BASE_URL', '/PROJET%202A28/');

$page = $_GET['page'] ?? 'home';

// Définition des routes
switch ($page) {
    case 'dashboard':
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "index.php?page=home");
            exit;
        }
        require_once "view/dashboard/index.php";
        break;

    case 'admin':
        // On pourrait ajouter une vérification de rôle ici
        require_once "view/admin/index.php";
        break;

    case 'home':
    default:
        if (isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }
        require_once "view/home/index.php";
        break;
}
