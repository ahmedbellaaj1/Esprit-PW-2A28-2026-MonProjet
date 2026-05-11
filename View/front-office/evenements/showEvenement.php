<?php
/**
 * Green-Bite Front-Office - Détail d'un Événement
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../../Controller/EvenementController.php';
require_once __DIR__ . '/../../../Controller/ParticipationController.php';

$controller = new EvenementController();
$participationController = new ParticipationController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: listEvenements.php'); exit(); }

$event = $controller->getEvenementById($id);
if (!$event || !is_array($event)) { header('Location: listEvenements.php'); exit(); }

// Préparer les données
$eventId          = (int)$event['id'];
$eventTitre       = htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8');
$eventDescription = nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'));
$eventDate        = $event['date_event'] ?? '';
$eventLieu        = htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8');
$eventType        = htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8');
$capaciteMax      = (int)($event['capacite_max'] ?? 0);
$organisateurNom  = htmlspecialchars($event['organisateur_nom'] ?? 'Non spécifié', ENT_QUOTES, 'UTF-8');
$organisateurEmail= htmlspecialchars($event['organisateur_email'] ?? '', ENT_QUOTES, 'UTF-8');
$organisateurTel  = htmlspecialchars($event['organisateur_telephone'] ?? '', ENT_QUOTES, 'UTF-8');
$organisateurSite = htmlspecialchars($event['organisateur_site_web'] ?? '', ENT_QUOTES, 'UTF-8');

$formattedDate = $formattedDateLong = '';
$isPast = $isToday = false;
$daysRemaining = null;

if (!empty($eventDate)) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('d/m/Y');
        $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        $formattedDateLong = $jours[(int)$dateObj->format('w')] . ' ' . $dateObj->format('d') . ' ' . $mois[(int)$dateObj->format('n')-1] . ' ' . $dateObj->format('Y');
        $today = new DateTime(); $today->setTime(0,0,0);
        $isPast = ($dateObj < $today);
        $isToday = ($dateObj == $today);
        if (!$isPast && !$isToday) { $diff = $today->diff($dateObj); $daysRemaining = (int)$diff->days; }
    }
}

$typeIcon = match($eventType) { 'Atelier'=>'🧑‍🍳','Conférence'=>'🎤','Festival'=>'🎉', default=>'📌' };
$typeClass = 'ev-type-' . $eventType;

if ($isToday) { $statusText = "Aujourd'hui"; $statusIcon = "🔴"; $statusClass = "ev-status-today"; }
elseif ($isPast) { $statusText = "Passé"; $statusIcon = "✅"; $statusClass = "ev-status-past"; }
else { $statusText = "À venir"; $statusIcon = "📅"; $statusClass = "ev-status-upcoming"; }

$inscrits = $participationController->countParticipantsByEvent($eventId);
$placesRestantes = $capaciteMax - $inscrits;
$complet = ($capaciteMax > 0 && $placesRestantes <= 0);

$currentUser = getCurrentUser();
$isRegistered = $currentUser ? $participationController->isUserRegistered($eventId, $currentUser['id']) : false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $eventTitre ?> - GreenBite</title>
    <meta name="description" content="<?= $eventTitre ?> le <?= $formattedDate ?> à <?= $eventLieu ?>">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-show-container { max-width: 1000px; margin: 2rem auto; background: white; border-radius: 28px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.1); }
        .ev-show-header { background: linear-gradient(135deg, #0f766e, #14b8a6); padding: 2rem; color: white; }
        .ev-show-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .ev-badge { display: inline-block; padding: 0.35rem 1rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(255,255,255,0.2); margin: 0.25rem; }
        .ev-status-upcoming { background: #dcfce7; color: #166534; }
        .ev-status-today { background: #fef3c7; color: #92400e; }
        .ev-status-past { background: #f1f5f9; color: #64748b; }
        .ev-show-content { padding: 2rem; }
        .ev-show-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .ev-info-card { background: #f8fafc; padding: 1.25rem; border-radius: 16px; text-align: center; border: 1px solid #e2e8f0; transition: all 0.3s; }
        .ev-info-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .ev-info-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .ev-info-card .label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; }
        .ev-info-card .value { font-size: 1rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem; }
        .ev-description { background: #f8fafc; padding: 1.5rem; border-radius: 20px; margin: 1.5rem 0; }
        .ev-description h3 { font-size: 1.2rem; margin-bottom: 1rem; color: #0f172a; }
        .ev-organisateur { background: linear-gradient(135deg, #f0fdf4, #dcfce7); padding: 1.5rem; border-radius: 20px; margin: 1.5rem 0; }
        .ev-organisateur h3 { font-size: 1.1rem; margin-bottom: 1rem; color: #0f766e; }
        .ev-org-info { display: flex; flex-wrap: wrap; gap: 1rem; }
        .ev-org-info p { display: flex; align-items: center; gap: 0.5rem; color: #334155; font-size: 0.9rem; background: white; padding: 0.5rem 1rem; border-radius: 12px; }
        .ev-inscription { text-align: center; margin: 1.5rem 0; padding: 1.5rem; background: #f0fdf4; border-radius: 20px; }
        .ev-btn-participer { display: inline-block; background: #0f766e; color: white; padding: 0.875rem 2rem; border-radius: 9999px; text-decoration: none; font-weight: 600; font-size: 1rem; transition: all 0.3s; }
        .ev-btn-participer:hover { background: #0c5f58; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15,118,110,0.3); }
        .ev-places-info { margin-top: 0.5rem; font-size: 0.8rem; color: #64748b; }
        .ev-back-link { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: #0f766e; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .ev-back-link:hover { gap: 0.75rem; text-decoration: underline; }
        @media (max-width: 768px) {
            .ev-show-container { margin: 1rem; }
            .ev-show-header h1 { font-size: 1.5rem; }
            .ev-show-content { padding: 1.5rem; }
            .ev-show-info { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-show-container">
    <div class="ev-show-header">
        <h1><?= $eventTitre ?></h1>
        <div>
            <span class="ev-badge"><?= $typeIcon ?> <?= $eventType ?></span>
            <span class="ev-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= $statusText ?></span>
            <?php if ($daysRemaining !== null && $daysRemaining > 0): ?>
                <span class="ev-badge ev-status-upcoming">⏰ Dans <?= $daysRemaining ?> jour<?= $daysRemaining > 1 ? 's' : '' ?></span>
            <?php elseif ($daysRemaining === 0): ?>
                <span class="ev-badge ev-status-today">🔴 C'est aujourd'hui !</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="ev-show-content">
        <div class="ev-show-info">
            <div class="ev-info-card">
                <div class="icon">📅</div>
                <div class="label">Date</div>
                <div class="value"><?= $formattedDate ?></div>
                <?php if ($formattedDateLong): ?><div style="font-size:.7rem;color:#64748b;margin-top:.25rem;"><?= $formattedDateLong ?></div><?php endif; ?>
            </div>
            <div class="ev-info-card">
                <div class="icon">📍</div>
                <div class="label">Lieu</div>
                <div class="value"><?= $eventLieu ?></div>
            </div>
            <div class="ev-info-card">
                <div class="icon">🏷️</div>
                <div class="label">Type</div>
                <div class="value"><?= $eventType ?></div>
            </div>
            <?php if ($capaciteMax > 0): ?>
            <div class="ev-info-card">
                <div class="icon">🎟️</div>
                <div class="label">Places restantes</div>
                <div class="value"><?= max(0, $placesRestantes) ?> / <?= $capaciteMax ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="ev-description">
            <h3>📖 Description</h3>
            <p style="line-height:1.7;color:#334155;"><?= $eventDescription ?></p>
        </div>

        <?php if ($organisateurNom !== 'Non spécifié'): ?>
        <div class="ev-organisateur">
            <h3>👥 Organisateur</h3>
            <div class="ev-org-info">
                <p><strong><?= $organisateurNom ?></strong></p>
                <?php if ($organisateurEmail): ?><p>📧 <?= $organisateurEmail ?></p><?php endif; ?>
                <?php if ($organisateurTel): ?><p>📞 <?= $organisateurTel ?></p><?php endif; ?>
                <?php if ($organisateurSite): ?><p>🌐 <a href="<?= $organisateurSite ?>" target="_blank" style="color:#0f766e;"><?= $organisateurSite ?></a></p><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="ev-inscription">
            <?php if ($isPast): ?>
                <p style="color:#64748b;">📅 Cet événement est déjà passé</p>
            <?php elseif ($complet): ?>
                <p style="color:#dc2626;font-weight:600;">❌ Cet événement est complet</p>
            <?php elseif ($isRegistered): ?>
                <p style="color:#166534;font-weight:600;">✅ Vous êtes déjà inscrit à cet événement</p>
                <a href="/Green-Bite/View/front-office/evenements/mes-participations.php" class="ev-btn-participer" style="margin-top:1rem;display:inline-block;">📋 Voir mes participations</a>
            <?php elseif (!isLoggedIn()): ?>
                <p style="margin-bottom:1rem;">Connectez-vous pour vous inscrire à cet événement</p>
                <a href="/Green-Bite/View/auth.php" class="ev-btn-participer">🔑 Se connecter pour participer</a>
            <?php else: ?>
                <a href="/Green-Bite/View/front-office/evenements/participer.php?id=<?= $eventId ?>" class="ev-btn-participer">📝 Participer à cet événement</a>
                <?php if ($capaciteMax > 0): ?>
                    <p class="ev-places-info">🎟️ <?= $placesRestantes ?> places restantes sur <?= $capaciteMax ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" class="ev-back-link">← Retour aux événements</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/chatbot_widget.php'; ?>
</body>
</html>
