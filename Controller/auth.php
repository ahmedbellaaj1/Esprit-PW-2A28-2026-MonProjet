<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/../Model/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projetwebnova/View/auth.php');
}

$action = $_POST['action'] ?? '';
error_log('[AUTH.PHP] ============ NEW REQUEST ============');
error_log('[AUTH.PHP] REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('[AUTH.PHP] Action value: "' . $action . '"');
error_log('[AUTH.PHP] All POST keys: ' . json_encode(array_keys($_POST)));
error_log('[AUTH.PHP] Full POST: ' . json_encode($_POST));
$userRepository = new UserRepository();

function redirectWithFormState(string $tab, array $errors, array $oldInput = []): void
{
    setFormState($tab, $errors, $oldInput);
    redirect('/projetwebnova/View/auth.php');
}

try {
    if ($action === 'register') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $statut = $_POST['statut'] ?? 'actif';
        $errors = [];
        $oldInput = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'role' => $role,
            'statut' => $statut,
        ];

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            $errors['nom'] = $nom === '' ? 'Le nom est obligatoire.' : null;
            $errors['prenom'] = $prenom === '' ? 'Le prenom est obligatoire.' : null;
            $errors['email'] = $email === '' ? 'L email est obligatoire.' : null;
            $errors['mot_de_passe'] = $password === '' ? 'Le mot de passe est obligatoire.' : null;
            $errors = array_filter($errors, static fn ($value) => $value !== null);

            setFlash('error', 'Veuillez corriger les champs signales.');
            redirectWithFormState('register-panel', $errors, $oldInput);
        }

        if (!isValidPersonName($nom)) {
            $errors['nom'] = 'Le nom doit contenir uniquement des lettres, espaces ou tirets.';
        }

        if (!isValidPersonName($prenom)) {
            $errors['prenom'] = 'Le prenom doit contenir uniquement des lettres, espaces ou tirets.';
        }

        if (!isValidEmailAddress($email)) {
            $errors['email'] = 'Adresse email invalide. Le caractere @ est obligatoire.';
        }

        if ($userRepository->findByEmail($email) !== null) {
            $emailError = 'Cet email est deja utilise.';
            setFlash('error', $emailError);
            redirectWithFormState('register-panel', ['email' => $emailError], $oldInput);
        }

        if ($password === '') {
            $errors['mot_de_passe'] = 'Le mot de passe est obligatoire.';
        }

        if ($errors !== []) {
            setFlash('error', 'Veuillez corriger les champs signales.');
            redirectWithFormState('register-panel', $errors, $oldInput);
        }

        try {
            $photoName = storeUploadedUserPhoto($_FILES['photo'] ?? []);
        } catch (Throwable $photoError) {
            setFlash('error', $photoError->getMessage());
            redirectWithFormState('register-panel', ['photo' => $photoError->getMessage()], $oldInput);
        }

        // Generate verification token
        $verificationToken = generatePasswordResetToken();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Save verification data
        saveEmailVerification($email, $verificationToken, $nom, $prenom, $hashedPassword, $photoName, $role, $statut);

        // Send verification email
        $emailSent = sendEmailVerificationEmail($email, $verificationToken);

        if (!$emailSent) {
            setFlash('error', 'Erreur lors de l envoi de l email de confirmation. Veuillez reessayer.');
            redirectWithFormState('register-panel', [], $oldInput);
        }

        setFlash('success', 'Un email de confirmation a ete envoye a ' . htmlspecialchars($email) . '. Veuillez confirmer votre email pour activer votre compte.');
        redirect('/projetwebnova/View/auth.php');
    }

    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');

        // Debug logging
        error_log('[Login DEBUG] Action: ' . $action . ', Email: ' . $email . ', Password length: ' . strlen($password));
        error_log('[Login DEBUG] POST data: ' . json_encode($_POST));

        if ($email === '' || $password === '') {
            error_log('[Login] Empty fields detected');
            $errors = [
                'email' => $email === '' ? 'L email est obligatoire.' : null,
                'mot_de_passe' => $password === '' ? 'Le mot de passe est obligatoire.' : null,
            ];
            $errors = array_filter($errors, static fn ($value) => $value !== null);
            
            setFlash('error', 'Email et mot de passe sont obligatoires.');
            setFormState('login-panel', $errors, ['email' => $email]);
            redirect('/projetwebnova/View/auth.php');
        }

        $user = $userRepository->findByEmail($email);

        if ($user === null || !password_verify($password, $user->getMotDePasse())) {
            setFlash('error', 'Identifiants invalides.');
            setFormState('login-panel', [
                'email' => 'Identifiants invalides.',
                'mot_de_passe' => 'Identifiants invalides.',
            ], ['email' => $email]);
            redirect('/projetwebnova/View/auth.php');
        }

        if ($user->getStatut() !== 'actif') {
            setFlash('error', 'Votre compte est inactif ou suspendu.');
            setFormState('login-panel', [
                'email' => 'Votre compte est inactif ou suspendu.',
                'mot_de_passe' => 'Votre compte est inactif ou suspendu.',
            ], ['email' => $email]);
            redirect('/projetwebnova/View/auth.php');
        }

        $_SESSION['user'] = [
            'id' => (int) $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
        ];

        setFlash('success', 'Connexion reussie.');

        if ($user->getRole() === 'admin') {
            redirect('/projetwebnova/View/back-office/users.php');
        }

        redirect('/projetwebnova/View/front-office/profile.php');
    }

    if ($action === 'logout') {
        unset($_SESSION['user']);
        setFlash('success', 'Vous etes deconnecte.');
        redirect('/projetwebnova/View/auth.php');
    }

    if ($action === 'request-password-reset') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'L email est obligatoire.';
        } elseif (!isValidEmailAddress($email)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        if ($errors !== []) {
            setFlash('error', 'Veuillez corriger les champs signales.');
            redirectWithFormState('forgot-password-panel', $errors, ['email' => $email]);
        }

        $user = $userRepository->findByEmail($email);

        if ($user === null) {
            setFlash('error', 'Aucun compte n existe avec cette adresse email.');
            redirectWithFormState('forgot-password-panel', [], ['email' => $email]);
        }

        $resetToken = generatePasswordResetToken();
        savePasswordResetToken($email, $resetToken);

        $emailSent = sendPasswordResetEmail($email, $resetToken);

        if (!$emailSent) {
            setFlash('error', 'Erreur lors de l envoi de l email. Veuillez reessayer.');
            redirectWithFormState('forgot-password-panel', [], ['email' => $email]);
        }

        setFlash('success', 'Un email de reinitialisation a ete envoye a votre adresse email.');
        redirect('/projetwebnova/View/auth.php');
    }

    if ($action === 'reset-password') {
        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $passwordConfirm = (string) ($_POST['mot_de_passe_confirm'] ?? '');
        $errors = [];

        if ($token === '') {
            throw new RuntimeException('Token invalide ou absent.');
        }

        $email = validatePasswordResetToken($token);

        if ($email === null) {
            setFlash('error', 'Le lien de reinitialisation est invalide ou a expire.');
            redirect('/projetwebnova/View/auth.php');
        }

        if ($password === '' || $passwordConfirm === '') {
            $errors['mot_de_passe'] = $password === '' ? 'Le mot de passe est obligatoire.' : null;
            $errors['mot_de_passe_confirm'] = $passwordConfirm === '' ? 'La confirmation est obligatoire.' : null;
            $errors = array_filter($errors, static fn ($value) => $value !== null);

            setFlash('error', 'Veuillez corriger les champs signales.');
            setFormState('reset-password-panel', $errors);
            redirect('/projetwebnova/View/auth.php?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            $errors['mot_de_passe_confirm'] = 'Les mots de passe ne correspondent pas.';
            setFlash('error', 'Les mots de passe ne correspondent pas.');
            setFormState('reset-password-panel', $errors);
            redirect('/projetwebnova/View/auth.php?token=' . urlencode($token));
        }

        $user = $userRepository->findByEmail($email);

        if ($user === null) {
            throw new RuntimeException('Utilisateur non trouve.');
        }

        $user->setMotDePasse(password_hash($password, PASSWORD_DEFAULT));
        $userRepository->updateProfile($user);

        deletePasswordResetToken($token);

        setFlash('success', 'Votre mot de passe a ete reinitialise avec succes. Veuillez vous connecter.');
        redirect('/projetwebnova/View/auth.php');
    }

    setFlash('error', 'Action non supportee.');
    redirect('/projetwebnova/View/auth.php');
} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/auth.php');
}
