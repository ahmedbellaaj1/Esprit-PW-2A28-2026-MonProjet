<?php
/**
 * Green-Bite Back-Office - Supprimer un Événement
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/EvenementController.php';

$controller = new EvenementController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) { header('Location: /Green-Bite/View/back-office/evenements.php'); exit(); }

$result = $controller->deleteEvenement($id);
$_SESSION['message'] = $result['message'];
header('Location: /Green-Bite/View/back-office/evenements.php');
exit();
