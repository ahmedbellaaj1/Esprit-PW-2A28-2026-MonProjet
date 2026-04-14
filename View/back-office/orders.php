<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

$q = trim($_GET['q'] ?? '');

$controller = new OrderController();
$orders = $controller->list(['q' => $q]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Commandes</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">Green<span>Bite</span></div>
        <div class="sidebar-role">Administration</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link" href="dashboard.php"><span class="icon">📊</span> Vue d'ensemble</a>
            <a class="sidebar-link" href="products.php"><span class="icon">🛒</span> Produits</a>
            <a class="sidebar-link active" href="orders.php"><span class="icon">📦</span> Commandes</a>
            <a class="sidebar-link" href="../front-office/index.php"><span class="icon">🌐</span> Front Office</a>
        </nav>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-title">Gestion des commandes</div>
            <div class="header-right">
                <span class="header-badge">🚚 Suivi livraison</span>
                <div class="admin-avatar">AB</div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Commandes</h1>
                <p>Creation, mise a jour, suppression et suivi des commandes.</p>
            </div>

            <?php if (isset($_GET['ok'])): ?>
                <div class="alert success">Operation commande executee avec succes.</div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-toolbar">
                    <form method="get" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                        <div class="table-search" style="max-width:420px;">
                            <span>🔍</span>
                            <input id="q" type="number" min="1" name="q" value="<?= h($q) ?>" placeholder="ID commande, produit ou utilisateur">
                        </div>
                        <button class="btn-add" type="submit">Rechercher</button>
                    </form>
                    <div style="display:flex;gap:0.75rem;align-items:center;">
                        <a class="btn-cancel" href="orders.php">Reset</a>
                        <a class="btn-add" href="order_form.php">+ Ajouter commande</a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID Produit</th>
                                <th>ID User</th>
                                <th>Quantite</th>
                                <th>Prix total</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Adresse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= (int) $o['id_commande'] ?></td>
                                <td><?= (int) $o['id_produit'] ?></td>
                                <td><?= (int) $o['id_utilisateur'] ?></td>
                                <td><?= (int) $o['quantite'] ?></td>
                                <td><?= number_format((float) $o['prix_total'], 2, ',', ' ') ?> DT</td>
                                <td><?= h((string) $o['date_commande']) ?></td>
                                <td>
                                    <span class="badge <?= in_array($o['statut'], ['confirmee', 'livree'], true) ? 'badge-green' : ($o['statut'] === 'annulee' ? 'badge-red' : 'badge-amber') ?>">
                                        <?= h($o['statut']) ?>
                                    </span>
                                </td>
                                <td><?= h($o['adresse_livraison']) ?></td>
                                <td class="actions-cell">
                                    <a class="table-btn save-btn" href="order_form.php?id=<?= (int) $o['id_commande'] ?>">Modifier</a>
                                    <form action="order_delete.php" method="post" onsubmit="return confirm('Supprimer cette commande ?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $o['id_commande'] ?>">
                                        <button class="table-btn delete-btn" type="submit">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
