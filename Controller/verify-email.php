<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/../Model/User.php';

$token = $_GET['token'] ?? '';
$userRepository = new UserRepository();

if ($token === '') {
    setFlash('error', 'Token de verification invalide.');
    redirect('/projetwebnova/View/auth.php');
}

try {
    // Validate the token and get user data
    $userData = validateEmailVerificationToken($token);

    if ($userData === null) {
        setFlash('error', 'Le lien de verification est invalide ou a expire.');
        redirect('/projetwebnova/View/auth.php');
    }

    // Create the user account
    $newUser = new User(
        null,
        $userData['nom'],
        $userData['prenom'],
        $userData['email'],
        $userData['mot_de_passe'],
        $userData['photo'],
        $userData['role'],
        $userData['statut']
    );

    $newUserId = $userRepository->create($newUser);

    $user = $userRepository->findById($newUserId);

    if ($user === null) {
        throw new RuntimeException('Impossible de charger le nouvel utilisateur cree.');
    }

    // Delete the verification token
    deleteEmailVerificationToken($token);

    // Set flash message and redirect to login
    setFlash('success', 'Votre email a ete confirme avec succes! Vous pouvez maintenant vous connecter.');
    redirect('/projetwebnova/View/auth.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/auth.php');
}
