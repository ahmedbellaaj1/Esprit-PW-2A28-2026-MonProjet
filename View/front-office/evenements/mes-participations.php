<?php
/**
 * Green-Bite Front-Office - Mes Participations
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
requireAuth();
require_once __DIR__ . '/../../../Controller/ParticipationController.php';
require_once __DIR__ . '/../../../Controller/EvenementController.php';

$currentUser = getCurrentUser();
$participationController = new ParticipationController();
$participations = $participationController->getParticipationsByUser($currentUser['id']);
if (!is_array($participations)) $participations = [];
$userFullName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Participations - GreenBite</title>
    <meta name="description" content="Consultez et gérez vos inscriptions aux événements GreenBite.">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-mp-hero { background: linear-gradient(135deg, #0f766e, #14b8a6); padding: 3rem 2rem 2rem; text-align: center; color: white; }
        .ev-mp-hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .ev-mp-hero p { opacity: 0.9; }
        .ev-mp-main { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .ev-mp-grid { display: grid; gap: 1.5rem; }
        .ev-mp-card { background: white; border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.07); display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
        .ev-mp-event-info h3 { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; }
        .ev-mp-event-info p { font-size: 0.85rem; color: #64748b; margin: 0.25rem 0; }
        .ev-mp-status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-top: 0.5rem; }
        .ev-mp-status-inscrit  { background: #dcfce7; color: #166534; }
        .ev-mp-status-present  { background: #dbeafe; color: #1e40af; }
        .ev-mp-status-annule   { background: #fee2e2; color: #991b1b; }
        .ev-mp-status-en_attente { background: #fef3c7; color: #92400e; }
        .ev-mp-qr { text-align: center; }
        .ev-mp-qr img { width: 120px; height: 120px; border: 2px solid #14b8a6; border-radius: 8px; }
        .ev-mp-actions { display: flex; flex-direction: column; gap: 0.5rem; }
        .ev-mp-btn { padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 500; transition: all 0.2s; display: inline-block; }
        .ev-mp-btn-detail { background: #dbeafe; color: #1e40af; }
        .ev-mp-btn-detail:hover { background: #bfdbfe; }
        .ev-mp-empty { text-align: center; padding: 4rem; background: white; border-radius: 20px; }
        .ev-mp-empty .emoji { font-size: 4rem; margin-bottom: 1rem; }
        .ev-mp-empty h3 { font-size: 1.3rem; margin-bottom: 0.5rem; }
        .ev-mp-empty p { color: #64748b; }
        .ev-mp-btn-ev { display: inline-block; margin-top: 1rem; background: #0f766e; color: white; padding: 0.75rem 1.5rem; border-radius: 9999px; text-decoration: none; font-weight: 600; }
        @media (max-width: 768px) { .ev-mp-card { flex-direction: column; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-mp-hero">
    <h1>📋 Mes Participations</h1>
    <p>Bonjour <?= htmlspecialchars($userFullName) ?>, voici vos inscriptions aux événements</p>
</div>

<div class="ev-mp-main">
    <?php if (empty($participations)): ?>
        <div class="ev-mp-empty">
            <div class="emoji">📭</div>
            <h3>Aucune participation</h3>
            <p>Vous n'êtes inscrit(e) à aucun événement pour l'instant.</p>
            <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" class="ev-mp-btn-ev">📅 Découvrir les événements</a>
        </div>
    <?php else: ?>
        <div class="ev-mp-grid">
            <?php foreach($participations as $p):
                $statutClass = 'ev-mp-status-' . $p['statut'];
                $statutLabels = ['inscrit'=>'✅ Inscrit','present'=>'🎉 Présent','annule'=>'❌ Annulé','en_attente'=>'⏳ En attente'];
                $statutLabel = $statutLabels[$p['statut']] ?? $p['statut'];
                $eventDate = $p['date_event'] ?? '';
                $formattedDate = $eventDate ? date('d/m/Y', strtotime($eventDate)) : '';
                $isPast = $eventDate && $eventDate < date('Y-m-d');

                // Générer le QR code URL si disponible
                $qrCodeUrl = '';
                if (!empty($p['code_qr'])) {
                    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                    $qrContent = $baseUrl . "/Green-Bite/View/front-office/evenements/valider-presence.php?token=" . $p['code_qr'] . "&id=" . $p['id'];
                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($qrContent);
                }
            ?>
                <div class="ev-mp-card">
                    <div class="ev-mp-event-info">
                        <h3><?= htmlspecialchars($p['event_titre'] ?? 'Événement') ?></h3>
                        <p>📅 <?= $formattedDate ?></p>
                        <p>📍 <?= htmlspecialchars($p['lieu'] ?? '') ?></p>
                        <p>🏷️ <?= htmlspecialchars($p['type'] ?? '') ?></p>
                        <p>📝 Inscrit le : <?= date('d/m/Y', strtotime($p['date_inscription'])) ?></p>
                        <?php if (!empty($p['date_validation'])): ?>
                            <p>✅ Validé le : <?= date('d/m/Y', strtotime($p['date_validation'])) ?></p>
                        <?php endif; ?>
                        <div><span class="ev-mp-status-badge <?= $statutClass ?>"><?= $statutLabel ?></span></div>
                    </div>

                    <?php if ($qrCodeUrl && !$isPast): ?>
                    <div class="ev-mp-qr">
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code billet">
                        <p style="font-size:0.7rem;color:#64748b;margin-top:5px;">Votre billet</p>
                    </div>
                    <?php endif; ?>

                    <div class="ev-mp-actions">
                        <a href="showEvenement.php?id=<?= (int)$p['evenement_id'] ?>" class="ev-mp-btn ev-mp-btn-detail">🔍 Voir l'événement</a>
                        <?php if ($qrCodeUrl): ?>
                            <a href="<?= $qrCodeUrl ?>" download="billet_<?= $p['id'] ?>.png" class="ev-mp-btn" style="background:#e0f2fe;color:#0369a1;">📥 Télécharger billet</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" class="ev-mp-btn-ev">📅 Découvrir plus d'événements</a>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/chatbot_widget.php'; ?>
</body>
</html>
