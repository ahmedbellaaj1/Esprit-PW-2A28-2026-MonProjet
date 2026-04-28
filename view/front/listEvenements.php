<?php
require_once "../../controller/EvenementController.php";
$controller = new EvenementController();

// Récupération et validation des paramètres GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

$events = [];

if (!empty($search)) {
    $events = $controller->searchEvents($search);
} elseif (!empty($type) && $type != 'all') {
    $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
    if (in_array($type, $validTypes)) {
        $events = $controller->getEventsByType($type);
    } else {
        $events = $controller->getUpcomingEvents();
    }
} else {
    $events = $controller->getUpcomingEvents();
}

$allEvents = $controller->listEvenements();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - GreenBite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">Green<span>Bite</span></a>
    <ul class="navbar-links">
        <li><a href="listEvenements.php">Événements</a></li>
    </ul>
    <div class="navbar-right">
        <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
    </div>
</nav>

<div class="hero-section">
    <h1>🌱 Découvrez les événements</h1>
    <p>Participez à des activités écologiques et communautaires</p>
    
    <form method="GET" action="listEvenements.php" class="search-wrapper">
        <input type="text" name="search" placeholder="🔍 Rechercher un événement..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Recher