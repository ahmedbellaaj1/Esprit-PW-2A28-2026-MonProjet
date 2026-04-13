<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../Model/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projetwebnova/View/auth.php');
}

$action = $_POST['action'] ?? '';
$userModel = new User();

try {
    if ($action === 'register') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $statut = $_POST['statut'] ?? 'actif';

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            setFlash('error', 'Tous les champs obligatoires doivent etre remplis.');
            redirect('/projetwebnova/View/auth.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Adresse email invalide.');
            redirect('/projetwebnova/View/auth.php');
        }

        if ($userModel->findByEmail($email) !== null) {
            setFlash('error', 'Cet email est deja utilise.');
            redirect('/projetwebnova/View/auth.php');
        }

        $photoName = storeUploadedUserPhoto($_FILES['photo'] ?? []);

        $newUserId = $userModel->create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
            'photo' => $photoName,
            'role' => $role,
            'statut' => $statut,
        ]);

        $user = $userModel->findById($newUserId);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        setFlash('success', 'Compte cree avec succes.');
        redirect('/projetwebnova/View/front-office/profile.php');
    }

    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');

        if ($email === '' || $password === '') {
            setFlash('error', 'Email et mot de passe sont obligatoires.');
            redirect('/projetwebnova/View/auth.php');
        }

        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['mot_de_passe'])) {
            setFlash('error', 'Identifiants invalides.');
            redirect('/projetwebnova/View/auth.php');
        }

        if (($user['statut'] ?? 'actif') !== 'actif') {
            setFlash('error', 'Votre compte est inactif ou suspendu.');
            redirect('/projetwebnova/View/auth.php');
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        setFlash('success', 'Connexion reussie.');

        if ($user['role'] === 'admin') {
            redirect('/projetwebnova/View/back-office/users.php');
        }

        redirect('/projetwebnova/View/front-office/profile.php');
    }

    if ($action === 'logout') {
        unset($_SESSION['user']);
        setFlash('success', 'Vous etes deconnecte.');
        redirect('/projetwebnova/View/auth.php');
    }

    setFlash('error', 'Action non supportee.');
    redirect('/projetwebnova/View/auth.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/auth.php');
}
