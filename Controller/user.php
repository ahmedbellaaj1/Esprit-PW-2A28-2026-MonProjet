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
    if ($action === 'update_profile') {
        requireAuth();

        $currentUserId = (int) $_SESSION['user']['id'];
        $currentUser = $userModel->findById($currentUserId);

        if ($currentUser === null) {
            unset($_SESSION['user']);
            setFlash('error', 'Session invalide, reconnectez-vous.');
            redirect('/projetwebnova/View/auth.php');
        }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $newPassword = (string) ($_POST['mot_de_passe'] ?? '');

        if ($nom === '' || $prenom === '' || $email === '') {
            setFlash('error', 'Nom, prenom et email sont obligatoires.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Adresse email invalide.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        $otherUser = $userModel->findByEmail($email);
        if ($otherUser !== null && (int) $otherUser['id'] !== $currentUserId) {
            setFlash('error', 'Cet email est deja utilise par un autre compte.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        $photoName = $currentUser['photo'] ?? null;
        $newPhoto = storeUploadedUserPhoto($_FILES['photo'] ?? []);
        if ($newPhoto !== null) {
            $photoName = $newPhoto;
        }

        $passwordHash = $currentUser['mot_de_passe'];
        if ($newPassword !== '') {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $userModel->updateProfile($currentUserId, [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'photo' => $photoName,
            'mot_de_passe' => $passwordHash,
        ]);

        $updatedUser = $userModel->findById($currentUserId);

        $_SESSION['user'] = [
            'id' => (int) $updatedUser['id'],
            'nom' => $updatedUser['nom'],
            'prenom' => $updatedUser['prenom'],
            'email' => $updatedUser['email'],
            'role' => $updatedUser['role'],
        ];

        setFlash('success', 'Profil mis a jour.');
        redirect('/projetwebnova/View/front-office/profile.php');
    }

    if ($action === 'create_user') {
        requireAdmin();

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $statut = $_POST['statut'] ?? 'actif';

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            setFlash('error', 'Les champs de creation utilisateur sont incomplets.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        if ($userModel->findByEmail($email) !== null) {
            setFlash('error', 'Email deja utilise.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $userModel->create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'statut' => $statut,
        ]);

        setFlash('success', 'Utilisateur ajoute.');
        redirect('/projetwebnova/View/back-office/users.php');
    }

    if ($action === 'update_user') {
        requireAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $role = $_POST['role'] ?? 'user';
        $statut = $_POST['statut'] ?? 'actif';

        if ($id <= 0 || $nom === '' || $prenom === '' || $email === '') {
            setFlash('error', 'Donnees invalides pour la mise a jour.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $otherUser = $userModel->findByEmail($email);
        if ($otherUser !== null && (int) $otherUser['id'] !== $id) {
            setFlash('error', 'Email deja utilise par un autre utilisateur.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $userModel->updateByAdmin($id, [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'role' => $role,
            'statut' => $statut,
        ]);

        setFlash('success', 'Utilisateur mis a jour.');
        redirect('/projetwebnova/View/back-office/users.php');
    }

    if ($action === 'delete_user') {
        requireAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlash('error', 'ID utilisateur invalide.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        if ($id === (int) $_SESSION['user']['id']) {
            setFlash('error', 'Suppression de votre propre compte admin interdite.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $userModel->delete($id);

        setFlash('success', 'Utilisateur supprime.');
        redirect('/projetwebnova/View/back-office/users.php');
    }

    setFlash('error', 'Action non reconnue.');
    redirect('/projetwebnova/View/back-office/users.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/back-office/users.php');
}
