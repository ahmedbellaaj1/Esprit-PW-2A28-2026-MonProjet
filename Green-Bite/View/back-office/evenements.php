<?php
/**
 * Green-Bite Back-Office - Gestion des Événements
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/EvenementController.php';
require_once __DIR__ . '/../../Controller/OrganisateurController.php';

$controller           = new EvenementController();
$organisateurController = new OrganisateurController();

$events = $controller->listEvenements();
$stats  = $controller->getStats();

$message = '';
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = trim(htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'));
    unset($_SESSION['message']);
}

$totalEvents    = isset($stats['total'])    ? (int)$stats['total']    : 0;
$upcomingEvents = isset($stats['upcoming']) ? (int)$stats['upcoming'] : 0;
$pastEvents     = $totalEvents - $upcomingEvents;
$typesCount     = isset($stats['byType'])   ? count($stats['byType']) : 0;
$organisateurs  = (int)($stats['organisateurs'] ?? 0);

$activePage = 'evenements';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="dashboard-layout">
    <?php $activePage = 'evenements'; include __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <div class="dashboard-main"><div class="page-content">
        <div class="page-header">
            <h1>📅 Gestion des Événements</h1>
            <p>Créez, modifiez et gérez les événements GreenBite</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?= $message ?></div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:2rem;">
            <?php foreach([
                ['📅','Total événements',$totalEvents],
                ['⏰','À venir',$upcomingEvents],
                ['✅','Passés',$pastEvents],
                ['🏷️','Types',$typesCount],
            ] as [$icon, $label, $val]): ?>
            <div class="stat-card" style="background:white;border-radius:20px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.05);transition:all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform=''">
                <div style="width:48px;height:48px;background:#ccfbf1;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;"><?= $icon ?></div>
                <div style="font-size:2rem;font-weight:700;color:#0f172a;margin-bottom:.25rem;"><?= $val ?></div>
                <div style="color:#64748b;font-size:.85rem;"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Action Bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <a href="/Green-Bite/View/back-office/evenements_add.php" class="primary-btn">➕ Ajouter un événement</a>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher un événement..." style="padding:.75rem 1.25rem;border:2px solid #e2e8f0;border-radius:9999px;width:300px;font-family:'Inter',sans-serif;outline:none;" onfocus="this.style.borderColor='#14b8a6'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <!-- Tableau des événements -->
        <div class="table-container">
            <table id="eventsTable">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Type</th>
                        <th>Organisateur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:3rem;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
                                <h3 style="color:#0f172a;margin-bottom:.5rem;">Aucun événement</h3>
                                <p style="color:#64748b;">Cliquez sur "Ajouter un événement" pour commencer</p>
                                <a href="/Green-Bite/View/back-office/evenements_add.php" style="color:#0f766e;display:inline-block;margin-top:.5rem;">➕ Ajouter votre premier événement</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($events as $event):
                            $eventDate = $event['date_event'] ?? '';
                            $today     = date('Y-m-d');
                            $isToday   = ($eventDate == $today);
                            $isUp      = ($eventDate >= $today);
                            if ($isToday) { $sc='status-today'; $si='🔴'; $st="Aujourd'hui"; }
                            elseif ($isUp) { $sc='status-upcoming'; $si='📅'; $st='À venir'; }
                            else { $sc='status-past'; $si='✅'; $st='Passé'; }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($event['titre']) ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($event['date_event'])) ?></td>
                            <td><?= htmlspecialchars($event['lieu']) ?></td>
                            <td><span style="display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:600;background:<?= match($event['type']){'Atelier'=>'#dcfce7','Conférence'=>'#dbeafe','Festival'=>'#fef3c7',default=>'#f3e8ff'} ?>;color:<?= match($event['type']){'Atelier'=>'#166534','Conférence'=>'#1e40af','Festival'=>'#92400e',default=>'#6b21a5'} ?>;"><?= $event['type'] ?></span></td>
                            <td><?= htmlspecialchars($event['organisateur_nom'] ?? 'Non défini') ?></td>
                            <td><span style="display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:600;background:<?= $sc=='status-upcoming'?'#dcfce7':($sc=='status-today'?'#fef3c7':'#f1f5f9') ?>;color:<?= $sc=='status-upcoming'?'#166534':($sc=='status-today'?'#92400e':'#64748b') ?>;"><?= $si ?> <?= $st ?></span></td>
                            <td>
                                <div style="display:flex;gap:.5rem;">
                                    <a href="/Green-Bite/View/back-office/evenements_edit.php?id=<?= (int)$event['id'] ?>" style="padding:.5rem 1rem;border-radius:8px;background:#dbeafe;color:#1e40af;text-decoration:none;font-size:.8rem;font-weight:500;">✏️ Modifier</a>
                                    <a href="/Green-Bite/View/back-office/evenements_delete.php?id=<?= (int)$event['id'] ?>" style="padding:.5rem 1rem;border-radius:8px;background:#fee2e2;color:#991b1b;text-decoration:none;font-size:.8rem;font-weight:500;" onclick="return confirm('⚠️ Supprimer cet événement ?')">🗑️ Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>
</div>
<script>
const si = document.getElementById('searchInput');
if (si) {
    si.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('#eventsTable tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none';
        });
    });
}
</script>
</body>
</html>
