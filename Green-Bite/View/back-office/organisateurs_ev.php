<?php
/**
 * Green-Bite Back-Office - Gestion des Organisateurs
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/OrganisateurController.php';

$controller    = new OrganisateurController();
$organisateurs = $controller->listOrganisateurs();
$stats         = $controller->getStats();

$message = '';
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = trim(htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'));
    unset($_SESSION['message']);
}

$organisateursCount = is_array($organisateurs) ? count($organisateurs) : 0;
$activePage = 'organisateurs';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organisateurs - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
    <div class="dashboard-main"><div class="page-content">
        <div class="page-header">
            <h1>🏢 Gestion des Organisateurs</h1>
            <p>Gérez les organisateurs des événements GreenBite</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?= $message ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:2rem;">
            <div style="background:white;border-radius:20px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div style="width:48px;height:48px;background:#ccfbf1;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;">🏢</div>
                <div style="font-size:2rem;font-weight:700;color:#0f172a;margin-bottom:.25rem;"><?= $organisateursCount ?></div>
                <div style="color:#64748b;font-size:.85rem;">Total organisateurs</div>
            </div>
            <div style="background:white;border-radius:20px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div style="width:48px;height:48px;background:#ccfbf1;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;">📧</div>
                <div style="font-size:2rem;font-weight:700;color:#0f172a;margin-bottom:.25rem;"><?= $organisateursCount ?></div>
                <div style="color:#64748b;font-size:.85rem;">Contacts emails</div>
            </div>
            <div style="background:white;border-radius:20px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div style="width:48px;height:48px;background:#ccfbf1;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;">📅</div>
                <div style="font-size:2rem;font-weight:700;color:#0f172a;margin-bottom:.25rem;"><?= $stats['total_events'] ?? 0 ?></div>
                <div style="color:#64748b;font-size:.85rem;">Événements organisés</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <a href="/Green-Bite/View/back-office/organisateurs_ev_add.php" class="primary-btn">➕ Ajouter un organisateur</a>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher..." style="padding:.75rem 1.25rem;border:2px solid #e2e8f0;border-radius:9999px;width:300px;font-family:'Inter',sans-serif;outline:none;" onfocus="this.style.borderColor='#14b8a6'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <div class="table-container">
            <table id="orgsTable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Site Web</th>
                        <th>Événements</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($organisateurs)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:3rem;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
                                <h3>Aucun organisateur</h3>
                                <a href="/Green-Bite/View/back-office/organisateurs_ev_add.php" style="color:#0f766e;display:inline-block;margin-top:.5rem;">➕ Ajouter le premier</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($organisateurs as $org):
                            $eventCount = (int)($org['event_count'] ?? 0);
                            $badgeBg = $eventCount == 0 ? '#f1f5f9' : ($eventCount <= 3 ? '#fef3c7' : '#dcfce7');
                            $badgeColor = $eventCount == 0 ? '#64748b' : ($eventCount <= 3 ? '#92400e' : '#166534');
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($org['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($org['email']) ?></td>
                            <td><?= htmlspecialchars($org['telephone']) ?></td>
                            <td>
                                <?php if (!empty($org['site_web'])): ?>
                                    <a href="<?= htmlspecialchars($org['site_web']) ?>" target="_blank" style="color:#0f766e;text-decoration:none;">🌐 Visiter</a>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:600;background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;"><?= $eventCount ?> événement(s)</span>
                            </td>
                            <td>
                                <div style="display:flex;gap:.5rem;">
                                    <a href="/Green-Bite/View/back-office/organisateurs_ev_edit.php?id=<?= (int)$org['id'] ?>" style="padding:.5rem 1rem;border-radius:8px;background:#dbeafe;color:#1e40af;text-decoration:none;font-size:.8rem;font-weight:500;">✏️ Modifier</a>
                                    <a href="/Green-Bite/View/back-office/organisateurs_ev_delete.php?id=<?= (int)$org['id'] ?>" style="padding:.5rem 1rem;border-radius:8px;background:#fee2e2;color:#991b1b;text-decoration:none;font-size:.8rem;font-weight:500;" onclick="return confirm('Supprimer cet organisateur ?')">🗑️ Supprimer</a>
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
if (si) si.addEventListener('keyup', function() {
    const v = this.value.toLowerCase();
    document.querySelectorAll('#orgsTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none';
    });
});
</script>
</body>
</html>
