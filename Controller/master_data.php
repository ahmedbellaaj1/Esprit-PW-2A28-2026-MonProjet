<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/PreferenceRepository.php';
require_once __DIR__ . '/AllergyRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Green-Bite/View/back-office/preferences.php');
}

requireAdmin();

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? ''; // 'preference' or 'allergy'

try {
    if ($type === 'preference') {
        $repo = new PreferenceRepository();
    } else {
        $repo = new AllergyRepository();
    }

    if ($action === 'create') {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($nom === '') throw new Exception('Le nom est obligatoire.');
        $repo->create($nom, $description);
        setFlash('success', 'Élément ajouté.');
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($id <= 0 || $nom === '') throw new Exception('Données invalides.');
        $repo->update($id, $nom, $description);
        setFlash('success', 'Élément mis à jour.');
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID invalide.');
        $repo->delete($id);
        setFlash('success', 'Élément supprimé.');
    }

    redirect('/Green-Bite/View/back-office/preferences.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur: ' . $e->getMessage());
    redirect('/Green-Bite/View/back-office/preferences.php');
}
