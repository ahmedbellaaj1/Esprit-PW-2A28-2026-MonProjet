<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/OrganisateurController.php';
$controller = new OrganisateurController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /Green-Bite/View/back-office/organisateurs_ev.php'); exit(); }
$result = $controller->deleteOrganisateur($id);
$_SESSION['message'] = $result['message'];
header('Location: /Green-Bite/View/back-office/organisateurs_ev.php');
exit();
