<?php
/**
 * Green-Bite Back-Office - Gestion des Participants
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/ParticipationController.php';

$controller    = new ParticipationController();
$participations= $controller->getAllParticipations();
$stats         = $controller->getStats();

$message = '';
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = trim(htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'));
    unset($_SESSION['message']);
}

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_statut') {
        $partId = (int)($_POST['participation_id'] ?? 0);
        $statut = trim($_POST['statut'] ?? '');
        $result = $controller->updateParticipationStatut($partId, $statut);
        $_SESSION['message'] = $result['message'];
        header('Location: /Green-Bite/View/back-office/participants.php');
        exit();
    }
    if ($_POST['action'] === 'delete') {
        $partId = (int)($_POST['participation_id'] ?? 0);
        $result = $controller->deleteParticipation($partId);
        $_SESSION['message'] = $result['message'];
        header('Location: /Green-Bite/View/back-office/participants.php');
        exit();
    }
}

$totalParticipations = (int)($stats['total'] ?? 0);
$byStatut = [];
foreach (($stats['byStatut'] ?? []) as $row) { $byStatut[$row['statut']] = $row['count']; }
$activePage = 'participants';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .stat-badge { display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:600; }
        .stat-inscrit { background:#dcfce7;color:#166534; }
        .stat-present { background:#dbeafe;color:#1e40af; }
        .stat-annule { background:#fee2e2;color:#991b1b; }
        .stat-en_attente { background:#fef3c7;color:#92400e; }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
    <div class="dashboard-main"><div class="page-content">
        <div class="page-header">
            <h1>👥 Gestion des Participants</h1>
            <p>Consultez et gérez les inscriptions aux événements</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?= $message ?></div>
        <?php endif; ?>

        <!-- Statistiques rapides -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:2rem;">
            <?php foreach([
                ['👥','Total participations',$totalParticipations,'#ccfbf1'],
                ['✅','Inscrits',$byStatut['inscrit'] ?? 0,'#dcfce7'],
                ['🎉','Présents',$byStatut['present'] ?? 0,'#dbeafe'],
                ['❌','Annulés',$byStatut['annule'] ?? 0,'#fee2e2'],
            ] as [$icon,$label,$val,$bg]): ?>
            <div style="background:white;border-radius:20px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div style="width:48px;height:48px;background:<?= $bg ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;"><?= $icon ?></div>
                <div style="font-size:2rem;font-weight:700;color:#0f172a;margin-bottom:.25rem;"><?= $val ?></div>
                <div style="color:#64748b;font-size:.85rem;"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Barre de recherche -->
        <div style="margin-bottom:1.5rem;">
            <input type="text" id="searchInput" placeholder="🔍 Rechercher un participant, événement..." style="padding:.75rem 1.25rem;border:2px solid #e2e8f0;border-radius:9999px;width:350px;font-family:'Inter',sans-serif;outline:none;" onfocus="this.style.borderColor='#14b8a6'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <table id="participantsTable">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Email</th>
                        <th>Événement</th>
                        <th>Date événement</th>
                        <th>Inscrit le</th>
                        <th>Statut</th>
                        <th>QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participations)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:3rem;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
                                <h3 style="color:#0f172a;margin-bottom:.5rem;">Aucun participant</h3>
                                <p style="color:#64748b;">Les inscriptions apparaîtront ici</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($participations as $p):
                            $nom = trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? ''));
                            $statutLabels = ['inscrit'=>'✅ Inscrit','present'=>'🎉 Présent','annule'=>'❌ Annulé','en_attente'=>'⏳ En attente'];
                            $statutColors = [
                                'inscrit'   => 'background:#dcfce7;color:#166534',
                                'present'   => 'background:#dbeafe;color:#1e40af',
                                'annule'    => 'background:#fee2e2;color:#991b1b',
                                'en_attente'=> 'background:#fef3c7;color:#92400e'
                            ];
                            $sc = $statutColors[$p['statut']] ?? '';
                            $qrUrl = '';
                            if (!empty($p['code_qr'])) {
                                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                                $qrContent = $baseUrl . "/Green-Bite/View/front-office/evenements/valider-presence.php?token=" . $p['code_qr'] . "&id=" . $p['id'];
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=" . urlencode($qrContent);
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($nom ?: 'Utilisateur') ?></strong></td>
                            <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($p['event_titre'] ?? '') ?></td>
                            <td><?= !empty($p['date_event']) ? date('d/m/Y', strtotime($p['date_event'])) : '-' ?></td>
                            <td><?= !empty($p['date_inscription']) ? date('d/m/Y', strtotime($p['date_inscription'])) : '-' ?></td>
                            <td>
                                <span style="display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:600;<?= $sc ?>">
                                    <?= $statutLabels[$p['statut']] ?? $p['statut'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($qrUrl): ?>
                                    <img src="<?= $qrUrl ?>" alt="QR" style="width:50px;height:50px;border-radius:4px;border:1px solid #e2e8f0;" title="QR Code de présence">
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:.8rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                    <?php if ($p['statut'] !== 'present'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="action" value="update_statut">
                                        <input type="hidden" name="participation_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="statut" value="present">
                                        <button type="submit" style="padding:.4rem .8rem;border-radius:8px;background:#dbeafe;color:#1e40af;border:none;font-size:.75rem;font-weight:500;cursor:pointer;font-family:'Inter',sans-serif;">✅ Valider</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer cette participation ?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="participation_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" style="padding:.4rem .8rem;border-radius:8px;background:#fee2e2;color:#991b1b;border:none;font-size:.75rem;font-weight:500;cursor:pointer;font-family:'Inter',sans-serif;">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>\n</div>
<script>
const si = document.getElementById('searchInput');
if (si) {
    si.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('#participantsTable tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none';
        });
    });
}
</script>
</body>
</html>
