<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/../Model/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projetwebnova/View/auth.php');
}

$action = $_POST['action'] ?? '';
$userRepository = new UserRepository();

try {
    if ($action === 'update_profile') {
        requireAuth();

        $currentUserId = (int) $_SESSION['user']['id'];
        $currentUser = $userRepository->findById($currentUserId);

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

        if (!isValidPersonName($nom) || !isValidPersonName($prenom)) {
            setFlash('error', 'Nom et prenom doivent contenir uniquement des lettres.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        if (!isValidEmailAddress($email)) {
            setFlash('error', 'Adresse email invalide.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        $otherUser = $userRepository->findByEmail($email);
        if ($otherUser !== null && (int) $otherUser->getId() !== $currentUserId) {
            setFlash('error', 'Cet email est deja utilise par un autre compte.');
            redirect('/projetwebnova/View/front-office/profile.php');
        }

        $photoName = $currentUser->getPhoto();
        $newPhoto = storeUploadedUserPhoto($_FILES['photo'] ?? []);
        if ($newPhoto !== null) {
            $photoName = $newPhoto;
        }

        $passwordHash = $currentUser->getMotDePasse();
        if ($newPassword !== '') {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $updatedProfile = new User(
            $currentUserId,
            $nom,
            $prenom,
            $email,
            $passwordHash,
            $photoName,
            $currentUser->getRole(),
            $currentUser->getStatut(),
            $currentUser->getDateInscription()
        );

        $userRepository->updateProfile($updatedProfile);

        $updatedUser = $userRepository->findById($currentUserId);

        if ($updatedUser === null) {
            throw new RuntimeException('Impossible de charger le profil mis a jour.');
        }

        $_SESSION['user'] = [
            'id' => (int) $updatedUser->getId(),
            'nom' => $updatedUser->getNom(),
            'prenom' => $updatedUser->getPrenom(),
            'email' => $updatedUser->getEmail(),
            'role' => $updatedUser->getRole(),
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

        if (!isValidPersonName($nom) || !isValidPersonName($prenom)) {
            setFlash('error', 'Nom et prenom doivent contenir uniquement des lettres.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        if (!isValidEmailAddress($email)) {
            setFlash('error', 'Adresse email invalide. Le caractere @ est obligatoire.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        if ($userRepository->findByEmail($email) !== null) {
            setFlash('error', 'Email deja utilise.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $newUser = new User(
            null,
            $nom,
            $prenom,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            null,
            $role,
            $statut
        );

        $userRepository->create($newUser);

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

        if (!isValidPersonName($nom) || !isValidPersonName($prenom)) {
            setFlash('error', 'Nom et prenom doivent contenir uniquement des lettres.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        if (!isValidEmailAddress($email)) {
            setFlash('error', 'Adresse email invalide. Le caractere @ est obligatoire.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $otherUser = $userRepository->findByEmail($email);
        if ($otherUser !== null && (int) $otherUser->getId() !== $id) {
            setFlash('error', 'Email deja utilise par un autre utilisateur.');
            redirect('/projetwebnova/View/back-office/users.php');
        }

        $adminUpdatedUser = new User(
            $id,
            $nom,
            $prenom,
            $email,
            '',
            null,
            $role,
            $statut
        );

        $userRepository->updateByAdmin($adminUpdatedUser);

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

        $userRepository->delete($id);

        setFlash('success', 'Utilisateur supprime.');
        redirect('/projetwebnova/View/back-office/users.php');
    }

    setFlash('error', 'Action non reconnue.');
    redirect('/projetwebnova/View/back-office/users.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/back-office/users.php');
}
