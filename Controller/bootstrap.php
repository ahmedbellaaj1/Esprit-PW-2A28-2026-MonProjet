<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function getBaseUrl(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    return $protocol . '://' . $host;
}

function getEmailBaseUrl(): string
{
    // Email links should always use the public ngrok URL so they work on any device
    return 'https://valuables-baguette-sandbar.ngrok-free.dev';
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

function setFormState(string $tab, array $errors = [], array $oldInput = []): void
{
    $_SESSION['form_state'] = [
        'tab' => $tab,
        'errors' => $errors,
        'old' => $oldInput,
    ];
}

function consumeFormState(): array
{
    if (!isset($_SESSION['form_state']) || !is_array($_SESSION['form_state'])) {
        return [
            'tab' => 'login-panel',
            'errors' => [],
            'old' => [],
        ];
    }

    $formState = $_SESSION['form_state'];
    unset($_SESSION['form_state']);

    return [
        'tab' => (string) ($formState['tab'] ?? 'login-panel'),
        'errors' => is_array($formState['errors'] ?? null) ? $formState['errors'] : [],
        'old' => is_array($formState['old'] ?? null) ? $formState['old'] : [],
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

function isValidPersonName(string $value): bool
{
    $normalized = trim($value);

    if ($normalized === '') {
        return false;
    }

    // Letters only, with optional spaces or hyphens.
    return (bool) preg_match('/^[\p{L}]+(?:[\s\-][\p{L}]+)*$/u', $normalized);
}

function isValidEmailAddress(string $email): bool
{
    $normalized = strtolower(trim($email));

    if ($normalized === '' || strpos($normalized, '@') === false) {
        return false;
    }

    return filter_var($normalized, FILTER_VALIDATE_EMAIL) !== false;
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

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMimeType = $finfo !== false ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo !== false) {
        finfo_close($finfo);
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!array_key_exists($detectedMimeType, $allowedMimeTypes)) {
        throw new RuntimeException('Type de fichier non supporte. Utilisez uniquement des images png, jpeg, gif ou webp.');
    }

    if (@getimagesize($tmpName) === false) {
        throw new RuntimeException('Le fichier envoye n est pas une image valide.');
    }

    $originalName = (string) ($file['name'] ?? '');
    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        $extension = $allowedMimeTypes[$detectedMimeType];
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

function generatePasswordResetToken(): string
{
    return bin2hex(random_bytes(32));
}

function savePasswordResetToken(string $email, string $token, int $expirationHours = 24): void
{
    $pdo = getPdo();
    $expiresAt = new DateTime("+{$expirationHours} hours");
    
    // Delete old tokens for this email
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = :email');
    $stmt->execute(['email' => $email]);
    
    // Save new token
    $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
    $stmt->execute([
        'email' => $email,
        'token' => $token,
        'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
    ]);
}

function validatePasswordResetToken(string $token): ?string
{
    $pdo = getPdo();
    
    $stmt = $pdo->prepare(
        'SELECT email FROM password_resets 
         WHERE token = :token AND expires_at > NOW() 
         LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $result = $stmt->fetch();
    
    return $result ? (string) $result['email'] : null;
}

function deletePasswordResetToken(string $token): void
{
    $pdo = getPdo();
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = :token');
    $stmt->execute(['token' => $token]);
}

function saveEmailVerification(string $email, string $token, string $nom, string $prenom, string $motDePasse, ?string $photo, string $role = 'user', string $statut = 'actif', int $expirationHours = 24): void
{
    $pdo = getPdo();
    $expiresAt = new DateTime("+{$expirationHours} hours");
    
    // Delete old verifications for this email
    $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE email = :email');
    $stmt->execute(['email' => $email]);
    
    // Save new verification
    $stmt = $pdo->prepare(
        'INSERT INTO email_verifications (email, token, nom, prenom, mot_de_passe, photo, role, statut, expires_at) 
         VALUES (:email, :token, :nom, :prenom, :mot_de_passe, :photo, :role, :statut, :expires_at)'
    );
    $stmt->execute([
        'email' => $email,
        'token' => $token,
        'nom' => $nom,
        'prenom' => $prenom,
        'mot_de_passe' => $motDePasse,
        'photo' => $photo,
        'role' => $role,
        'statut' => $statut,
        'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
    ]);
}

function validateEmailVerificationToken(string $token): ?array
{
    $pdo = getPdo();
    
    $stmt = $pdo->prepare(
        'SELECT email, nom, prenom, mot_de_passe, photo, role, statut FROM email_verifications 
         WHERE token = :token AND expires_at > NOW() 
         LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $result = $stmt->fetch();
    
    return $result ? $result : null;
}

function deleteEmailVerificationToken(string $token): void
{
    $pdo = getPdo();
    $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE token = :token');
    $stmt->execute(['token' => $token]);
}

function sendPasswordResetEmail(string $email, string $resetToken): bool
{
    $baseUrl = getEmailBaseUrl();
    $resetUrl = $baseUrl . '/projetwebnova/View/auth.php?token=' . urlencode($resetToken);
    
    $gmailAddress = 'rayenrourou1919@gmail.com';
    $gmailAppPassword = 'ndvsxsdzlkgzfxui';
    
    $to = $email;
    $subject = 'Reinitialisation de votre mot de passe - GreenBite';
    
    $message = "Bonjour,\n\n";
    $message .= "Vous avez demande une reinitialisation de votre mot de passe.\n\n";
    $message .= "Cliquez sur le lien ci-dessous pour reinitialiser votre mot de passe :\n";
    $message .= $resetUrl . "\n\n";
    $message .= "Ce lien expirera dans 24 heures.\n\n";
    $message .= "Si vous n'avez pas demande cette reinitialisation, veuillez ignorer ce message.\n\n";
    $message .= "Cordialement,\n";
    $message .= "L'equipe AppNova";
    
    $headers = "From: AppNova <" . $gmailAddress . ">\r\n";
    $headers .= "Reply-To: AppNova <" . $gmailAddress . ">\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    $logDir = __DIR__ . '/../uploads';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/email_debug.log';
    
    try {
        error_log('[' . date('Y-m-d H:i:s') . '] Tentative d envoi email a: ' . $to, 3, $logFile);
        error_log('[' . date('Y-m-d H:i:s') . '] URL de reinitialisation: ' . $resetUrl, 3, $logFile);
        
        $smtp = fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 30);
        
        if (!$smtp) {
            $error = "Connexion SMTP echouee: [$errno] $errstr";
            error_log('[' . date('Y-m-d H:i:s') . '] ' . $error, 3, $logFile);
            return false;
        }
        
        error_log('[' . date('Y-m-d H:i:s') . '] Connexion SMTP etablie', 3, $logFile);
        
        // Helper function to read full SMTP response (handles multi-line)
        $readResponse = function($handle) use ($logFile) {
            $response = '';
            while ($line = fgets($handle)) {
                $response .= $line;
                if (substr($line, 3, 1) !== '-') {
                    break;
                }
            }
            return $response;
        };
        
        // Read SMTP greeting
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] GREETING: ' . trim($response), 3, $logFile);
        
        // Send EHLO
        fwrite($smtp, "EHLO localhost\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] EHLO Response: ' . trim($response), 3, $logFile);
        
        // AUTH LOGIN
        fwrite($smtp, "AUTH LOGIN\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] AUTH LOGIN Response: ' . trim($response), 3, $logFile);
        
        if (strpos($response, '334') === false) {
            error_log('[' . date('Y-m-d H:i:s') . '] AUTH LOGIN failed - expected 334', 3, $logFile);
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            return false;
        }
        
        // Send username (base64 encoded)
        fwrite($smtp, base64_encode($gmailAddress) . "\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] Username Response: ' . trim($response), 3, $logFile);
        
        if (strpos($response, '334') === false) {
            error_log('[' . date('Y-m-d H:i:s') . '] Username response failed - expected 334', 3, $logFile);
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            return false;
        }
        
        // Send password (base64 encoded)
        fwrite($smtp, base64_encode($gmailAppPassword) . "\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] Password Response: ' . trim($response), 3, $logFile);
        
        if (strpos($response, '235') === false) {
            error_log('[' . date('Y-m-d H:i:s') . '] Authentication failed - expected 235, got: ' . trim($response), 3, $logFile);
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            return false;
        }
        
        error_log('[' . date('Y-m-d H:i:s') . '] Authentication successful', 3, $logFile);
        
        // MAIL FROM
        fwrite($smtp, "MAIL FROM: <" . $gmailAddress . ">\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] MAIL FROM Response: ' . trim($response), 3, $logFile);
        
        // RCPT TO
        fwrite($smtp, "RCPT TO: <" . $to . ">\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] RCPT TO Response: ' . trim($response), 3, $logFile);
        
        // DATA
        fwrite($smtp, "DATA\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] DATA Response: ' . trim($response), 3, $logFile);
        
        // Send headers and body
        $fullMessage = "To: " . $to . "\r\n";
        $fullMessage .= "Subject: " . $subject . "\r\n";
        $fullMessage .= $headers . "\r\n";
        $fullMessage .= $message;
        
        fwrite($smtp, $fullMessage . "\r\n.\r\n");
        $response = $readResponse($smtp);
        error_log('[' . date('Y-m-d H:i:s') . '] Message Response: ' . trim($response), 3, $logFile);
        
        // QUIT
        fwrite($smtp, "QUIT\r\n");
        fclose($smtp);
        
        error_log('[' . date('Y-m-d H:i:s') . '] Email envoye avec succes a: ' . $to, 3, $logFile);
        return true;
    } catch (Throwable $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Exception: ' . $e->getMessage(), 3, $logFile);
        return false;
    }
}

function sendEmailVerificationEmail(string $email, string $verificationToken): bool
{
    $baseUrl = getEmailBaseUrl();
    $verificationUrl = $baseUrl . '/projetwebnova/Controller/verify-email.php?token=' . urlencode($verificationToken);
    
    $gmailAddress = 'rayenrourou1919@gmail.com';
    $gmailAppPassword = 'ndvsxsdzlkgzfxui';
    
    $to = $email;
    $subject = 'Confirmez votre email - AppNova';
    
    $message = "Bonjour,\n\n";
    $message .= "Merci de vous etre inscrit sur AppNova!\n\n";
    $message .= "Cliquez sur le lien ci-dessous pour confirmer votre adresse email et activer votre compte :\n";
    $message .= $verificationUrl . "\n\n";
    $message .= "Ce lien expirera dans 24 heures.\n\n";
    $message .= "Si vous n'avez pas cree ce compte, veuillez ignorer ce message.\n\n";
    $message .= "Cordialement,\n";
    $message .= "L'equipe AppNova";
    
    $headers = "From: AppNova <" . $gmailAddress . ">\r\n";
    $headers .= "Reply-To: AppNova <" . $gmailAddress . ">\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    $logDir = __DIR__ . '/../uploads';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/email_debug.log';
    
    try {
        error_log('[' . date('Y-m-d H:i:s') . '] Tentative d envoi email de verification a: ' . $to, 3, $logFile);
        error_log('[' . date('Y-m-d H:i:s') . '] URL de verification: ' . $verificationUrl, 3, $logFile);
        
        $smtp = fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 30);
        
        if (!$smtp) {
            $error = "Connexion SMTP echouee: [$errno] $errstr";
            error_log('[' . date('Y-m-d H:i:s') . '] ' . $error, 3, $logFile);
            return false;
        }
        
        $readResponse = function($handle) use ($logFile) {
            $response = '';
            while ($line = fgets($handle)) {
                $response .= $line;
                if (substr($line, 3, 1) !== '-') {
                    break;
                }
            }
            return $response;
        };
        
        $response = $readResponse($smtp);
        fwrite($smtp, "EHLO localhost\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, "AUTH LOGIN\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, base64_encode($gmailAddress) . "\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, base64_encode($gmailAppPassword) . "\r\n");
        $response = $readResponse($smtp);
        
        if (strpos($response, '235') === false) {
            error_log('[' . date('Y-m-d H:i:s') . '] Authentication failed for verification email', 3, $logFile);
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            return false;
        }
        
        fwrite($smtp, "MAIL FROM: <" . $gmailAddress . ">\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, "RCPT TO: <" . $to . ">\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, "DATA\r\n");
        $response = $readResponse($smtp);
        
        $fullMessage = "To: " . $to . "\r\n";
        $fullMessage .= "Subject: " . $subject . "\r\n";
        $fullMessage .= $headers . "\r\n";
        $fullMessage .= $message;
        
        fwrite($smtp, $fullMessage . "\r\n.\r\n");
        $response = $readResponse($smtp);
        
        fwrite($smtp, "QUIT\r\n");
        fclose($smtp);
        
        error_log('[' . date('Y-m-d H:i:s') . '] Email de verification envoye avec succes a: ' . $to, 3, $logFile);
        return true;
    } catch (Throwable $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Exception (verification): ' . $e->getMessage(), 3, $logFile);
        return false;
    }
}
