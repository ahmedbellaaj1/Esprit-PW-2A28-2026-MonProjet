<?php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../controller/ParticipationController.php";
require_once "../../model/Participation.php";

// ==================== VÉRIFICATION CONNEXION ====================

// Vérifier si l'utilisateur est connecté (module user)
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    // Redirection vers la page de connexion locale
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$eventController = new EvenementController();
$participationController = new ParticipationController();

// ==================== VALIDATION DE L'ÉVÉNEMENT ====================

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    $_SESSION['error'] = "ID d'événement invalide";
    header('Location: listEvenements.php');
    exit();
}

$event = $eventController->getEvenementById($eventId);

if (!$event) {
    $_SESSION['error'] = "Événement non trouvé";
    header('Location: listEvenements.php');
    exit();
}

// ==================== TRAITEMENT DE L'INSCRIPTION ====================

$error = '';
$success = '';
$qrToken = '';
$participationId = 0;
$isRegistered = $participationController->isUserRegistered($eventId, $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isRegistered) {
    $participation = new Participation($eventId, $user['id']);
    $result = $participationController->addParticipation($participation);
    
    if ($result['success']) {
        $success = $result['message'];
        $participationId = $result['id'];
        $qrToken = $result['qr_token'] ?? '';
        $isRegistered = true;
    } else {
        $error = $result['message'];
    }
}

// ==================== CALCUL DES PLACES ====================

$placesRestantes = $participationController->checkRemainingCapacity($eventId);
$capaciteMax = $event['capacite_max'] ?? 0;
$complet = ($capaciteMax > 0 && $placesRestantes <= 0);

// Nom complet de l'utilisateur
$userFullName = '';
if (isset($user['prenom']) && isset($user['nom'])) {
    $userFullName = trim($user['prenom'] . ' ' . $user['nom']);
} elseif (isset($user['nom'])) {
    $userFullName = $user['nom'];
} else {
    $userFullName = 'Utilisateur';
}

// Générer l'URL du QR code si disponible
$qrCodeUrl = '';
if (!empty($qrToken) && $participationId > 0) {
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $baseUrl = rtrim($baseUrl, '/');
    $qrContent = $baseUrl . "/projetwebnova1/view/front/valider-presence.php?token=" . $qrToken . "&id=" . $participationId;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($qrContent);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participer - <?= htmlspecialchars($event['titre']) ?> - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .navbar {
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-radius: 16px;
        }
        .navbar-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-logo span { color: #ccfbf1; }
        .navbar-logo img {
            height: 35px;
            width: 35px;
            border-radius: 8px;
            object-fit: cover;
        }
        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .nav-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-name {
            color: white;
            font-size: 0.9rem;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 { font-size: 1.8rem; color: #0f172a; margin-bottom: 0.5rem; }
        h2 { font-size: 1.2rem; color: #0f766e; margin-bottom: 1rem; }
        .event-info {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 16px;
            margin: 1.5rem 0;
        }
        .user-connected-info {
            background: #e0f2fe;
            padding: 1rem;
            border-radius: 16px;
            margin: 1rem 0;
        }
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
        }
        .qr-section img {
            width: 180px;
            height: 180px;
            margin: 10px 0;
            border: 3px solid #0f766e;
            border-radius: 12px;
        }
        .btn-submit {
            width: 100%;
            background: #0f766e;
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover { background: #0c5f58; transform: translateY(-2px); }
        .btn-download {
            display: inline-block;
            background: #14b8a6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            margin-top: 10px;
        }
        .btn-download:hover { background: #0f766e; }
        .error-message { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #dc2626; }
        .success-message { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #16a34a; }
        .back-link { display: inline-block; margin-top: 1rem; color: #0f766e; text-decoration: none; }
        .action-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .container { padding: 1.5rem; }
            .navbar { flex-direction: column; height: auto; gap: 0.5rem; padding: 1rem; }
            .qr-section img { width: 140px; height: 140px; }
            .action-links { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">
        <img src="../assets/images/logo.png" alt="GreenBite">
        <span>Green<span>Bite</span></span>
    </a>
    <div class="user-info">
        <span class="user-name">👤 <?= htmlspecialchars($userFullName) ?></span>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php" class="nav-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">🚪 Déconnexion</a>
        <?php endif; ?>
        <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
            <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h1>📝 Participer à l'événement</h1>
    <h2><?= htmlspecialchars($event['titre']) ?></h2>
    
    <div class="event-info">
        <p>📅 <?= date('d/m/Y', strtotime($event['date_event'])) ?> | 📍 <?= htmlspecialchars($event['lieu']) ?></p>
        <?php if ($capaciteMax > 0): ?>
            <p>🎟️ Places disponibles : <strong><?= max(0, $placesRestantes) ?> / <?= $capaciteMax ?></strong></p>
        <?php endif; ?>
    </div>
    
    <div class="user-connected-info">
        <p>Vous êtes connecté en tant que :</p>
        <p><strong><?= htmlspecialchars($userFullName) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
    </div>
    
    <?php if ($error): ?>
        <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message">
            <strong>✅ <?= htmlspecialchars($success) ?></strong>
            <br><br>
            Un email de confirmation avec votre billet électronique vous a été envoyé.
        </div>
        
        <?php if ($qrCodeUrl): ?>
        <div class="qr-section">
            <h3>🎟️ Votre billet électronique</h3>
            <img src="<?= $qrCodeUrl ?>" alt="QR Code" id="qrCodeImage">
            <p>Présentez ce QR code à l'entrée de l'événement</p>
            <div>
                <a href="<?= $qrCodeUrl ?>" download="billet_<?= $participationId ?>.png" class="btn-download">📥 Télécharger mon billet</a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-links">
            <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Retour à l'événement</a>
            <a href="mes-participations.php" class="back-link">📋 Voir mes participations →</a>
        </div>
        
    <?php elseif ($isRegistered): ?>
        <div class="success-message">
            ✅ Vous êtes déjà inscrit à cet événement !
            <br><br>
            <a href="mes-participations.php" style="color: #166534;">📋 Voir mes participations →</a>
        </div>
        <div class="action-links">
            <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Retour à l'événement</a>
        </div>
        
    <?php elseif ($complet): ?>
        <div class="error-message">❌ Désolé, cet événement est complet !</div>
        <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Retour à l'événement</a>
        
    <?php else: ?>
        <form method="POST">
            <button type="submit" class="btn-submit">✅ Confirmer ma participation</button>
        </form>
        <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Annuler et retourner</a>
    <?php endif; ?>
</div>

</body>
</html>