<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function requireAuth(): void
{
    if (!isset($_SESSION['user'])) {
        setFlash('error', 'Veuillez vous connecter.');
        redirect('/projetwebnova/View/auth.php');
    }
}

function requireAdmin(): void
{
    requireAuth();

    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        setFlash('error', 'Acces reserve a un administrateur.');
        redirect('/projetwebnova/View/front-office/profile.php');
    }
}

function storeUploadedUserPhoto(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier depasse la taille maximale autorisee par le serveur.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier depasse la taille maximale autorisee par le formulaire.',
            UPLOAD_ERR_PARTIAL => 'Le fichier a ete telecharge partiellement.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d ecrire le fichier sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a bloque le telechargement.',
        ];

        $message = $messages[$file['error']] ?? 'Erreur inconnue pendant le telechargement de la photo.';
        throw new RuntimeException($message);
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Le fichier telecharge est invalide.');
    }

    $originalName = (string) ($file['name'] ?? '');
    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Format de photo non supporte. Utilisez jpg, jpeg, png, gif ou webp.');
    }

    $uploadDir = __DIR__ . '/../uploads/users';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Impossible de creer le dossier de destination des photos.');
    }

    $photoName = 'user_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . '/' . $photoName;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Le fichier n a pas pu etre enregistre sur le serveur.');
    }

    return $photoName;
}
