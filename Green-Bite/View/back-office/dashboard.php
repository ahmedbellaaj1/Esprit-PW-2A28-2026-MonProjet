<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';
require_once __DIR__ . '/../../Controller/OrderController.php';
require_once __DIR__ . '/../../Controller/EvenementController.php';
require_once __DIR__ . '/../../Controller/ParticipationController.php';

$productController = new ProductController();
$orderController = new OrderController();
$eventController = new EvenementController();
$participationController = new ParticipationController();

$productMetrics = $productController->metrics();
$orderMetrics = $orderController->metrics();

// Statistiques événements
$eventStats = $eventController->getStats();
$totalEvents    = (int)($eventStats['total']       ?? 0);
$upcomingEvents = (int)($eventStats['upcoming']    ?? 0);
$totalParticipations = $participationController->getTotalParticipations();
$totalOrganisateurs = (int)($eventStats['organisateurs'] ?? 0);
$nextEvents = $eventController->getNextEvents(5);

$totalProducts = $productMetrics['total'];
$activeProducts = $productMetrics['active'];
$totalOrders = $orderMetrics['total'];
$pendingOrders = $orderMetrics['pending'];

$latestProducts = $productController->latest(5);
$latestOrders = $orderController->latest(5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="dashboard-layout">
<?php $activePage = 'dashboard'; include __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <div class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-title">Dashboard Administration</div>
            <div class="header-right">
                <span class="header-badge">🟢 En ligne</span>
                <div class="admin-avatar">AB</div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Tableau de bord</h1>
                <p>Suivi global des produits, commandes et événements communautaires.</p>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon icon-teal">🛒</div>
                    <div class="metric-value"><?= $activeProducts ?></div>
                    <div class="metric-label">Produits actifs</div>
                    <div class="metric-trend trend-up">↑ total: <?= $totalProducts ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-blue">📦</div>
                    <div class="metric-value"><?= $totalOrders ?></div>
                    <div class="metric-label">Commandes totales</div>
                    <div class="metric-trend trend-up">↑ Activite continue</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-amber">⏳</div>
                    <div class="metric-value"><?= $pendingOrders ?></div>
                    <div class="metric-label">En attente</div>
                    <div class="metric-trend trend-down">A traiter rapidement</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-red">📈</div>
                    <div class="metric-value"><?= count($latestProducts) ?></div>
                    <div class="metric-label">Nouveaux produits visibles</div>
                    <div class="metric-trend trend-up">Derniers ajouts</div>
                </div>
            </div>

            <!-- ===== Section Événements ===== -->
            <div class="page-header" style="margin-top:2rem;margin-bottom:1rem;">
                <h2 style="font-size:1.4rem;font-weight:700;color:#0f172a;">📅 Événements Communautaires</h2>
            </div>
            <div class="metrics-grid" style="margin-bottom:1.5rem;">
                <div class="metric-card">
                    <div class="metric-icon icon-teal">📅</div>
                    <div class="metric-value"><?= $totalEvents ?></div>
                    <div class="metric-label">Total événements</div>
                    <div class="metric-trend trend-up">↑ <?= $upcomingEvents ?> à venir</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-blue">👥</div>
                    <div class="metric-value"><?= $totalParticipations ?></div>
                    <div class="metric-label">Participations</div>
                    <div class="metric-trend trend-up">↑ Inscriptions actives</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-amber">🏢</div>
                    <div class="metric-value"><?= $totalOrganisateurs ?></div>
                    <div class="metric-label">Organisateurs</div>
                    <div class="metric-trend trend-up">↑ Partenaires actifs</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon icon-red">⏰</div>
                    <div class="metric-value"><?= $upcomingEvents ?></div>
                    <div class="metric-label">À venir</div>
                    <div class="metric-trend trend-up">Prochainement</div>
                </div>
            </div>

            <div class="panels-row">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Derniers produits</div>
                        <a class="panel-action" href="products.php">Voir tout</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>ID</th><th>Nom</th><th>Marque</th><th>Prix</th><th>Statut</th></tr></thead>
                            <tbody>
                            <?php foreach ($latestProducts as $p): ?>
                                <tr>
                                    <td><?= (int) $p['id_produit'] ?></td>
                                    <td><?= h($p['nom']) ?></td>
                                    <td><?= h($p['marque']) ?></td>
                                    <td><?= number_format((float) $p['prix'], 2, ',', ' ') ?> DT</td>
                                    <td>
                                        <span class="badge <?= $p['statut'] === 'actif' ? 'badge-green' : ($p['statut'] === 'attente' ? 'badge-amber' : 'badge-red') ?>">
                                            <?= h($p['statut']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Dernieres commandes</div>
                        <a class="panel-action" href="orders.php">Voir tout</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>ID</th><th>Produit</th><th>User</th><th>Qte</th><th>Total</th><th>Livraison</th><th>Date souhaitée</th><th>Statut</th></tr></thead>
                            <tbody>
                            <?php foreach ($latestOrders as $o): ?>
                                <tr>
                                    <td><?= (int) $o['id_commande'] ?></td>
                                    <td>
                                        <div style="font-size:0.9rem;">
                                            <strong><?= h((string) ($o['produit_nom'] ?? 'Produit supprimé')) ?></strong><br>
                                            <span style="color:#64748b;font-size:0.85rem;"><?= h((string) ($o['produit_marque'] ?? '')) ?></span>
                                        </div>
                                    </td>
                                    <td><?= (int) $o['id_utilisateur'] ?></td>
                                    <td><?= (int) $o['quantite'] ?></td>
                                    <td><?= number_format((float) $o['prix_total'], 2, ',', ' ') ?> DT</td>
                                    <td><?= h((string) ($o['mode_livraison'] ?? 'standard')) ?></td>
                                    <td><?= h((string) ($o['date_livraison_souhaitee'] ?? '')) ?></td>
                                    <td>
                                        <span class="badge <?= in_array($o['statut'], ['confirmee', 'livree'], true) ? 'badge-green' : ($o['statut'] === 'annulee' ? 'badge-red' : 'badge-amber') ?>">
                                            <?= h($o['statut']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel prochains événements -->
            <div class="panels-row" style="margin-top:1.5rem;">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">⏰ Prochains Événements</div>
                        <a class="panel-action" href="/Green-Bite/View/back-office/evenements.php">Gérer</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Type</th><th>Organisateur</th></tr></thead>
                            <tbody>
                            <?php if (empty($nextEvents)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:2rem;color:#64748b;">📭 Aucun événement à venir.<br><a href="/Green-Bite/View/back-office/evenements_add.php" style="color:#0f766e;">➕ Ajouter un événement</a></td></tr>
                            <?php else: ?>
                                <?php foreach ($nextEvents as $ev): ?>
                                <tr>
                                    <td><strong><?= h($ev['titre']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($ev['date_event'])) ?></td>
                                    <td><?= h($ev['lieu']) ?></td>
                                    <td><span class="badge badge-green"><?= h($ev['type']) ?></span></td>
                                    <td><?= h($ev['organisateur_nom'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
