<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/HealthRepository.php';
require_once __DIR__ . '/../Model/Health.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Green-Bite/View/auth.php');
}

requireAuth();

$action = $_POST['action'] ?? '';
$healthRepository = new HealthRepository();

try {
    if ($action === 'update_health') {
        $idUser = (int) $_SESSION['user']['id'];
        $preference = trim($_POST['preference_alimentaire'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');
        $poids = $_POST['poids'] !== '' ? (float) $_POST['poids'] : null;
        $age = $_POST['age'] !== '' ? (int) $_POST['age'] : null;
        $taille = $_POST['taille'] !== '' ? (float) $_POST['taille'] : null;
        $sexe = trim($_POST['sexe'] ?? '');

        $health = new Health($idUser, $preference, $allergies, $poids, $age, $taille, $sexe);
        $healthRepository->save($health);

        setFlash('success', 'Informations santé mises à jour.');
        redirect('/Green-Bite/View/front-office/profile.php');
    }

    setFlash('error', 'Action non reconnue.');
    redirect('/Green-Bite/View/front-office/profile.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/Green-Bite/View/front-office/profile.php');
}
