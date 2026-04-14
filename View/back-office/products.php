<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$q = trim($_GET['q'] ?? '');

$controller = new ProductController();
$products = $controller->list(['q' => $q]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Produits</title>
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
            <a class="sidebar-link active" href="products.php"><span class="icon">🛒</span> Produits</a>
            <a class="sidebar-link" href="orders.php"><span class="icon">📦</span> Commandes</a>
            <a class="sidebar-link" href="../front-office/index.php"><span class="icon">🌐</span> Front Office</a>
        </nav>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-title">Gestion des produits</div>
            <div class="header-right">
                <span class="header-badge">🧾 CRUD complet</span>
                <div class="admin-avatar">AB</div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Produits</h1>
                <p>Ajout, modification, suppression et consultation des produits.</p>
            </div>

            <?php if (isset($_GET['ok'])): ?>
                <div class="alert success">Operation produit executee avec succes.</div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-toolbar">
                    <form method="get" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                        <div class="table-search" style="max-width:430px;">
                            <span>🔍</span>
                            <input id="q" type="text" name="q" value="<?= h($q) ?>" placeholder="Rechercher nom, marque ou categorie...">
                        </div>
                        <button class="btn-add" type="submit">Rechercher</button>
                    </form>
                    <div style="display:flex;gap:0.75rem;align-items:center;">
                        <a class="btn-cancel" href="products.php">Reset</a>
                        <a class="btn-add" href="product_form.php">+ Ajouter produit</a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Marque</th>
                                <th>Categorie</th>
                                <th>Prix</th>
                                <th>Nutri</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= (int) $p['id_produit'] ?></td>
                                <td><?= h($p['nom']) ?></td>
                                <td><?= h($p['marque']) ?></td>
                                <td><?= h($p['categorie']) ?></td>
                                <td><?= number_format((float) $p['prix'], 2, ',', ' ') ?> DT</td>
                                <td><span class="ns-badge ns-<?= strtolower((string) $p['nutriscore']) ?>"><?= h($p['nutriscore']) ?></span></td>
                                <td>
                                    <span class="badge <?= $p['statut'] === 'actif' ? 'badge-green' : ($p['statut'] === 'attente' ? 'badge-amber' : 'badge-red') ?>">
                                        <?= h($p['statut']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a class="table-btn save-btn" href="product_form.php?id=<?= (int) $p['id_produit'] ?>">Modifier</a>
                                    <form action="product_delete.php" method="post" onsubmit="return confirm('Supprimer ce produit ?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $p['id_produit'] ?>">
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
