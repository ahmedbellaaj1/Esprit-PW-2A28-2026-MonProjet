<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$q = trim($_GET['q'] ?? '');
$sort = trim($_GET['sort'] ?? '');
$order = trim($_GET['order'] ?? 'ASC');

$controller = new ProductController();
$products = $controller->list(['q' => $q]);

// Tri côté PHP
if ($sort) {
    usort($products, function($a, $b) use ($sort, $order) {
        $aVal = $a[$sort] ?? '';
        $bVal = $b[$sort] ?? '';
        
        // Tri numérique
        if ($sort === 'id_produit' || $sort === 'prix' || $sort === 'quantite_disponible') {
            $aVal = (float) $aVal;
            $bVal = (float) $bVal;
            $result = $aVal <=> $bVal;
        } else {
            // Tri alphabétique
            $result = strcasecmp($aVal, $bVal);
        }
        
        return $order === 'ASC' ? $result : -$result;
    });
}
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
        <div class="sidebar-logo">
            <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="sidebar-logo-img">
            <span>Green<span>Bite</span></span>
        </div>
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
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
                    <form method="get" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;flex:1;">
                        <div class="table-search" style="max-width:430px;">
                            <span>🔍</span>
                            <input id="q" type="text" name="q" value="<?= h($q) ?>" placeholder="Rechercher nom, marque ou catégorie...">
                            <input type="hidden" name="sort" value="<?= h($sort) ?>">
                            <input type="hidden" name="order" value="<?= h($order) ?>">
                        </div>
                        <button class="btn-add" type="submit">Rechercher</button>
                        <a class="btn-cancel" href="products.php">↻ Réinit</a>
                    </form>
                    <a class="btn-add" href="product_form.php">➕ Ajouter produit</a>
                </div>

                <div style="font-size:0.9rem;color:#64748b;margin-bottom:1rem;text-align:right;">
                    📊 <strong><?= count($products) ?></strong> produit<?= count($products) !== 1 ? 's' : '' ?>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="cursor:pointer;user-select:none;" onclick="sortBy('id_produit')">
                                    🆔 ID 
                                    <?php if ($sort === 'id_produit'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="cursor:pointer;user-select:none;" onclick="sortBy('nom')">
                                    📝 Nom 
                                    <?php if ($sort === 'nom'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="cursor:pointer;user-select:none;" onclick="sortBy('marque')">
                                    🏷️ Marque 
                                    <?php if ($sort === 'marque'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th>📂 Catégorie</th>
                                <th style="cursor:pointer;user-select:none;" onclick="sortBy('prix')">
                                    💰 Prix 
                                    <?php if ($sort === 'prix'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="cursor:pointer;user-select:none;" onclick="sortBy('quantite_disponible')">
                                    📦 Qté 
                                    <?php if ($sort === 'quantite_disponible'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th>⭐ Nutri</th>
                                <th>✓ Statut</th>
                                <th style="text-align:center;width:140px;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center;padding:2rem;color:#64748b;">
                                    🔍 Aucun produit trouvé
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="font-weight:600;color:#0f172a;">#<?= (int) $p['id_produit'] ?></td>
                                    <td style="font-weight:600;color:#0f172a;"><?= h($p['nom']) ?></td>
                                    <td style="color:#64748b;"><?= h($p['marque']) ?></td>
                                    <td><span class="badge badge-blue"><?= h($p['categorie']) ?></span></td>
                                    <td style="font-weight:600;color:#16a34a;"><?= number_format((float) $p['prix'], 2, ',', ' ') ?> DT</td>
                                    <td style="text-align:center;font-weight:500;">
                                        <?php $qty = (int) ($p['quantite_disponible'] ?? 0); ?>
                                        <span style="background:<?= $qty === 0 ? '#fee2e2' : ($qty < 5 ? '#fef3f2' : '#f0fdf4') ?>;padding:0.4rem 0.8rem;border-radius:6px;font-size:0.9rem;">
                                            <?= $qty ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="nutriscore ns-<?= strtolower((string) $p['nutriscore']) ?>" style="display:inline-block;width:32px;height:32px;border-radius:50%;line-height:32px;text-align:center;font-weight:600;color:white;">
                                            <?= h($p['nutriscore']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['statut'] === 'actif' ? 'badge-green' : ($p['statut'] === 'attente' ? 'badge-amber' : 'badge-red') ?>" style="font-size:0.85rem;">
                                            <?= match($p['statut']) {
                                                'actif' => '✅ Actif',
                                                'attente' => '⏳ Attente',
                                                'inactif' => '❌ Inactif',
                                                default => h($p['statut'])
                                            } ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <div style="display:flex;gap:0.35rem;justify-content:center;">
                                            <a class="table-btn save-btn" href="product_form.php?id=<?= (int) $p['id_produit'] ?>" title="Modifier">✏️</a>
                                            <form action="product_delete.php" method="post" onsubmit="return confirm('Confirmer la suppression ?');" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= (int) $p['id_produit'] ?>">
                                                <button class="table-btn delete-btn" type="submit" title="Supprimer">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                function sortBy(column) {
                    const params = new URLSearchParams(window.location.search);
                    const newOrder = (params.get('sort') === column && params.get('order') === 'ASC') ? 'DESC' : 'ASC';
                    params.set('sort', column);
                    params.set('order', newOrder);
                    window.location.search = params.toString();
                }
            </script>
        </div>
    </div>
</div>
</body>
</html>
