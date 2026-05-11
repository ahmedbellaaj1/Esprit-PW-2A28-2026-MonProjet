<?php
/**
 * Green-Bite Front-Office - Participer à un événement
 * Requiert authentification via requireAuth() de bootstrap.php
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
requireAuth(); // Redirige vers login si non connecté

require_once __DIR__ . '/../../../Controller/EvenementController.php';
require_once __DIR__ . '/../../../Controller/ParticipationController.php';

$eventController = new EvenementController();
$participationController = new ParticipationController();

$currentUser = getCurrentUser();
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) { header('Location: listEvenements.php'); exit(); }

$event = $eventController->getEvenementById($eventId);
if (!$event) { header('Location: listEvenements.php'); exit(); }

$error = '';
$success = '';
$qrToken = '';
$participationId = 0;
$isRegistered = $participationController->isUserRegistered($eventId, $currentUser['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isRegistered) {
    require_once __DIR__ . '/../../../Model/Participation.php';
    $participation = new Participation($eventId, $currentUser['id']);
    $result = $participationController->addParticipation($participation);
    if ($result['success']) {
        $success        = $result['message'];
        $participationId= $result['id'];
        $qrToken        = $result['qr_token'] ?? '';
        $isRegistered   = true;
    } else {
        $error = $result['message'];
    }
}

$placesRestantes = $participationController->checkRemainingCapacity($eventId);
$capaciteMax = $event['capacite_max'] ?? 0;
$complet = ($capaciteMax > 0 && $placesRestantes <= 0);

$userFullName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));

// URL QR code
$qrCodeUrl = '';
if (!empty($qrToken) && $participationId > 0) {
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $qrContent = $baseUrl . "/Green-Bite/View/front-office/evenements/valider-presence.php?token=" . $qrToken . "&id=" . $participationId;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($qrContent);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participer - <?= htmlspecialchars($event['titre']) ?> - GreenBite</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-participer-wrap { max-width: 650px; margin: 2rem auto; padding: 0 1rem; }
        .ev-card-box { background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .ev-card-box h1 { font-size: 1.8rem; color: #0f172a; margin-bottom: 0.5rem; }
        .ev-card-box h2 { font-size: 1.2rem; color: #0f766e; margin-bottom: 1.5rem; }
        .ev-event-info { background: #f8fafc; padding: 1rem; border-radius: 16px; margin: 1rem 0; }
        .ev-user-info-box { background: #e0f2fe; padding: 1rem; border-radius: 16px; margin: 1rem 0; }
        .ev-qr-section { text-align: center; margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 16px; }
        .ev-qr-section img { width: 180px; height: 180px; margin: 10px 0; border: 3px solid #0f766e; border-radius: 12px; }
        .ev-btn-submit { width: 100%; background: #0f766e; color: white; padding: 0.875rem; border: none; border-radius: 9999px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; font-family: 'Inter', sans-serif; }
        .ev-btn-submit:hover { background: #0c5f58; transform: translateY(-2px); }
        .ev-btn-download { display: inline-block; background: #14b8a6; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.8rem; margin-top: 10px; }
        .ev-error { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #dc2626; }
        .ev-success { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #16a34a; }
        .ev-action-links { display: flex; justify-content: center; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .ev-back-link { display: inline-block; margin-top: 1rem; color: #0f766e; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-participer-wrap">
    <div class="ev-card-box">
        <h1>📝 Participer à l'événement</h1>
        <h2><?= htmlspecialchars($event['titre']) ?></h2>

        <div class="ev-event-info">
            <p>📅 <?= date('d/m/Y', strtotime($event['date_event'])) ?> &nbsp;|&nbsp; 📍 <?= htmlspecialchars($event['lieu']) ?></p>
            <?php if ($capaciteMax > 0): ?>
                <p>🎟️ Places disponibles : <strong><?= max(0, $placesRestantes) ?> / <?= $capaciteMax ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="ev-user-info-box">
            <p>Vous êtes connecté(e) en tant que :</p>
            <p><strong><?= htmlspecialchars($userFullName) ?></strong> (<?= htmlspecialchars($currentUser['email'] ?? '') ?>)</p>
        </div>

        <?php if ($error): ?>
            <div class="ev-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="ev-success">
                <strong>✅ <?= htmlspecialchars($success) ?></strong>
                <br><br>Un email de confirmation avec votre billet électronique vous a été envoyé.
            </div>
            <?php if ($qrCodeUrl): ?>
            <div class="ev-qr-section">
                <h3>🎟️ Votre billet électronique</h3>
                <img src="<?= $qrCodeUrl ?>" alt="QR Code - Billet de participation" id="qrCodeImage">
                <p>Présentez ce QR code à l'entrée de l'événement</p>
                <a href="<?= $qrCodeUrl ?>" download="billet_<?= $participationId ?>.png" class="ev-btn-download">📥 Télécharger mon billet</a>
            </div>
            <?php endif; ?>
            <div class="ev-action-links">
                <a href="showEvenement.php?id=<?= $eventId ?>" class="ev-back-link">← Retour à l'événement</a>
                <a href="/Green-Bite/View/front-office/evenements/mes-participations.php" class="ev-back-link">📋 Voir mes participations →</a>
            </div>

        <?php elseif ($isRegistered): ?>
            <div class="ev-success">
                ✅ Vous êtes déjà inscrit(e) à cet événement !<br><br>
                <a href="/Green-Bite/View/front-office/evenements/mes-participations.php" style="color: #166534;">📋 Voir mes participations →</a>
            </div>
            <a href="showEvenement.php?id=<?= $eventId ?>" class="ev-back-link">← Retour à l'événement</a>

        <?php elseif ($complet): ?>
            <div class="ev-error">❌ Désolé, cet événement est complet !</div>
            <a href="showEvenement.php?id=<?= $eventId ?>" class="ev-back-link">← Retour à l'événement</a>

        <?php else: ?>
            <form method="POST">
                <button type="submit" class="ev-btn-submit">✅ Confirmer ma participation</button>
            </form>
            <a href="showEvenement.php?id=<?= $eventId ?>" class="ev-back-link">← Annuler et retourner</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
