<?php
/**
 * Green-Bite - Validation de présence par QR code
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin(); // Seul un admin peut valider la présence via QR
require_once __DIR__ . '/../../../Controller/ParticipationController.php';

$participationController = new ParticipationController();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$id    = isset($_GET['id'])    ? (int)$_GET['id']    : 0;

$participation = null;
$message       = '';
$success       = false;

if (!empty($token) && $id > 0) {
    $participation = $participationController->getParticipationByToken($token);
    if ($participation && (int)$participation['id'] === $id) {
        if ($participation['statut'] === 'present') {
            $message = "✅ Cette participation est déjà validée.";
            $success = true;
        } else {
            $result = $participationController->updateParticipationStatut($id, 'present');
            $success = $result['success'];
            $message = $result['message'];
            if ($success) {
                $participation['statut'] = 'present';
                $participation['date_validation'] = date('Y-m-d H:i:s');
            }
        }
    } else {
        $message = "❌ QR code invalide ou expiré.";
    }
} else {
    $message = "❌ Paramètres manquants.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation de présence - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f0fdfa, #e6f7f5); min-height: 100vh; }
        .ev-validate-wrap { max-width: 600px; margin: 3rem auto; padding: 0 1rem; }
        .ev-validate-card { background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); text-align: center; }
        .ev-validate-icon { font-size: 5rem; margin-bottom: 1rem; }
        .ev-validate-card h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; }
        .ev-validate-msg { padding: 1rem 1.5rem; border-radius: 12px; margin: 1rem 0; font-weight: 600; font-size: 1.1rem; }
        .ev-validate-success { background: #dcfce7; color: #166534; border-left: 4px solid #16a34a; }
        .ev-validate-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }
        .ev-participation-info { background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin: 1.5rem 0; text-align: left; }
        .ev-participation-info p { margin: 0.5rem 0; color: #334155; }
        .ev-btn { display: inline-block; background: #0f766e; color: white; padding: 0.75rem 1.5rem; border-radius: 9999px; text-decoration: none; font-weight: 600; margin: 0.5rem; transition: all 0.3s; }
        .ev-btn:hover { background: #0c5f58; transform: translateY(-2px); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-validate-wrap">
    <div class="ev-validate-card">
        <div class="ev-validate-icon"><?= $success ? '✅' : '❌' ?></div>
        <h1>Validation de présence</h1>
        <div class="ev-validate-msg <?= $success ? 'ev-validate-success' : 'ev-validate-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>

        <?php if ($participation): ?>
        <div class="ev-participation-info">
            <p><strong>👤 Participant :</strong> <?= htmlspecialchars(($participation['prenom'] ?? '') . ' ' . ($participation['nom'] ?? '')) ?></p>
            <p><strong>📧 Email :</strong> <?= htmlspecialchars($participation['email'] ?? '') ?></p>
            <p><strong>📅 Événement :</strong> <?= htmlspecialchars($participation['event_titre'] ?? '') ?></p>
            <p><strong>📍 Lieu :</strong> <?= htmlspecialchars($participation['lieu'] ?? '') ?></p>
            <p><strong>📆 Date :</strong> <?= isset($participation['date_event']) ? date('d/m/Y', strtotime($participation['date_event'])) : '' ?></p>
            <p><strong>🎫 Statut :</strong>
                <?php
                $labels = ['inscrit'=>'✅ Inscrit','present'=>'🎉 Présent','annule'=>'❌ Annulé','en_attente'=>'⏳ En attente'];
                echo $labels[$participation['statut']] ?? $participation['statut'];
                ?>
            </p>
            <?php if (!empty($participation['date_validation'])): ?>
                <p><strong>✅ Validé le :</strong> <?= date('d/m/Y à H:i', strtotime($participation['date_validation'])) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div>
            <a href="/Green-Bite/View/back-office/participants.php" class="ev-btn">👥 Gérer les participants</a>
            <a href="/Green-Bite/View/back-office/evenements.php" class="ev-btn" style="background:#14b8a6;">📅 Événements</a>
        </div>
    </div>
</div>
</body>
</html>
