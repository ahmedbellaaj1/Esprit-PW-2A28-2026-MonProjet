<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/../Model/User.php';

$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if ($code === null) {
    setFlash('error', 'Erreur lors de la connexion avec Google.');
    redirect('/projetwebnova/View/auth.php');
}

try {
    // Exchange authorization code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $baseUrl = getBaseUrl();
    
    $postData = [
        'code' => $code,
        'client_id' => getGoogleClientId(),
        'client_secret' => getGoogleClientSecret(),
        'redirect_uri' => $baseUrl . '/projetwebnova/Controller/google-callback.php',
        'grant_type' => 'authorization_code',
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || $response === false) {
        throw new RuntimeException('Impossible d obtenir le token Google.');
    }
    
    $tokenData = json_decode($response, true);
    
    if (!isset($tokenData['access_token'])) {
        throw new RuntimeException('Pas de token d acces recu de Google.');
    }
    
    $accessToken = $tokenData['access_token'];
    
    // Get user info from Google
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    $ch = curl_init($userInfoUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || $response === false) {
        throw new RuntimeException('Impossible d obtenir les infos utilisateur de Google.');
    }
    
    $googleUser = json_decode($response, true);
    
    if (!isset($googleUser['email'])) {
        throw new RuntimeException('Email non recu de Google.');
    }
    
    $email = strtolower((string) $googleUser['email']);
    $firstName = (string) ($googleUser['given_name'] ?? 'User');
    $lastName = (string) ($googleUser['family_name'] ?? '');
    $picture = (string) ($googleUser['picture'] ?? null);
    
    $userRepository = new UserRepository();
    $user = $userRepository->findByEmail($email);
    
    // If user doesn't exist, create one
    if ($user === null) {
        $newUser = new User(
            null,
            $lastName ?: 'Utilisateur',
            $firstName,
            $email,
            password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            null,
            'user',
            'actif'
        );
        
        $newUserId = $userRepository->create($newUser);
        $user = $userRepository->findById($newUserId);
        
        if ($user === null) {
            throw new RuntimeException('Impossible de creer le compte utilisateur.');
        }
    }
    
    // Check if account is active
    if ($user->getStatut() !== 'actif') {
        setFlash('error', 'Votre compte est inactif ou suspendu.');
        redirect('/projetwebnova/View/auth.php');
    }
    
    // Login the user
    $_SESSION['user'] = [
        'id' => (int) $user->getId(),
        'nom' => $user->getNom(),
        'prenom' => $user->getPrenom(),
        'email' => $user->getEmail(),
        'role' => $user->getRole(),
    ];
    
    setFlash('success', 'Connexion reussie avec Google!');
    
    if ($user->getRole() === 'admin') {
        redirect('/projetwebnova/View/back-office/users.php');
    }
    
    redirect('/projetwebnova/View/front-office/profile.php');
    
} catch (Throwable $e) {
    setFlash('error', 'Erreur lors de la connexion: ' . $e->getMessage());
    redirect('/projetwebnova/View/auth.php');
}
