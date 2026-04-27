<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

$q = trim($_GET['q'] ?? '');
$idUser = trim($_GET['id_user'] ?? '');
$status = trim($_GET['status'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$sort = trim($_GET['sort'] ?? 'date');
$order = trim($_GET['order'] ?? 'DESC');

$controller = new OrderController();
$orders = $controller->list([
    'q' => $q,
    'id_user' => $idUser,
    'status' => $status,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
]);

// Tri côté PHP
if ($sort) {
    usort($orders, function($a, $b) use ($sort, $order) {
        $aVal = $a[$sort] ?? '';
        $bVal = $b[$sort] ?? '';
        
        // Tri numérique
        if ($sort === 'id_commande' || $sort === 'id_utilisateur' || $sort === 'quantite' || $sort === 'prix_total') {
            $aVal = (float) $aVal;
            $bVal = (float) $bVal;
            $result = $aVal <=> $bVal;
        } elseif ($sort === 'date_commande' || $sort === 'date_livraison_souhaitee') {
            // Tri par date
            $aVal = strtotime($aVal ?: '1970-01-01');
            $bVal = strtotime($bVal ?: '1970-01-01');
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
    <title>Back Office - Commandes</title>
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
                <form method="get" style="margin-bottom:1.5rem;">
                    <div style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem;">
                        <div class="table-search" style="flex:1;min-width:250px;">
                            <span>🔍</span>
                            <input type="text" name="q" value="<?= h($q) ?>" placeholder="Chercher par nom produit ou ID commande...">
                            <input type="hidden" name="sort" value="<?= h($sort) ?>">
                            <input type="hidden" name="order" value="<?= h($order) ?>">
                        </div>
                        <div style="min-width:130px;">
                            <label style="display:block;font-size:0.85rem;color:#64748b;margin-bottom:0.25rem;font-weight:500;">ID User 👤</label>
                            <input type="number" name="id_user" value="<?= h($idUser) ?>" min="1" placeholder="Ex: 22" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:6px;font-size:0.9rem;">
                        </div>
                        <div style="min-width:160px;">
                            <label style="display:block;font-size:0.85rem;color:#64748b;margin-bottom:0.25rem;font-weight:500;">Statut</label>
                            <select name="status" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:6px;font-size:0.9rem;cursor:pointer;">
                                <option value="">-- Tous --</option>
                                <option value="en-cours" <?= $status === 'en-cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="en-preparation" <?= $status === 'en-preparation' ? 'selected' : '' ?>>En préparation</option>
                                <option value="confirmee" <?= $status === 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                                <option value="livree" <?= $status === 'livree' ? 'selected' : '' ?>>Livrée</option>
                                <option value="annulee" <?= $status === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem;">
                        <div style="flex:1;min-width:140px;">
                            <label style="display:block;font-size:0.85rem;color:#64748b;margin-bottom:0.25rem;font-weight:500;">Date depuis</label>
                            <input type="date" name="date_from" value="<?= h($dateFrom) ?>" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:6px;font-size:0.9rem;">
                        </div>
                        <div style="flex:1;min-width:140px;">
                            <label style="display:block;font-size:0.85rem;color:#64748b;margin-bottom:0.25rem;font-weight:500;">Date jusqu'à</label>
                            <input type="date" name="date_to" value="<?= h($dateTo) ?>" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:6px;font-size:0.9rem;">
                        </div>
                        <button class="btn-add" type="submit">🔎 Rechercher</button>
                        <a class="btn-cancel" href="orders.php">↻ Réinitialiser</a>
                    </div>
                </form>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <div style="font-size:0.95rem;color:#64748b;">
                        <strong><?= count($orders) ?></strong> commande<?= count($orders) !== 1 ? 's' : '' ?> 
                        <?php if ($q || $status || $dateFrom || $dateTo): ?>
                            trouvée<?= count($orders) !== 1 ? 's' : '' ?> 📊
                        <?php endif; ?>
                    </div>
                    <a class="btn-add" href="order_form.php">➕ Ajouter commande</a>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align:center;width:60px;cursor:pointer;user-select:none;" onclick="sortOrders('id_commande')">
                                    🆔 
                                    <?php if ($sort === 'id_commande'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="cursor:pointer;user-select:none;" onclick="sortOrders('produit_nom')">
                                    📦 Produit
                                    <?php if ($sort === 'produit_nom'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:center;width:70px;cursor:pointer;user-select:none;" onclick="sortOrders('id_utilisateur')">
                                    👤 User
                                    <?php if ($sort === 'id_utilisateur'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:center;width:60px;cursor:pointer;user-select:none;" onclick="sortOrders('quantite')">
                                    📊 Qté
                                    <?php if ($sort === 'quantite'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:right;width:100px;cursor:pointer;user-select:none;" onclick="sortOrders('prix_total')">
                                    💰 Montant
                                    <?php if ($sort === 'prix_total'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:center;width:110px;cursor:pointer;user-select:none;" onclick="sortOrders('date_commande')">
                                    📅 Date cmd
                                    <?php if ($sort === 'date_commande'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:center;width:90px;cursor:pointer;user-select:none;" onclick="sortOrders('statut')">
                                    ✓ Statut
                                    <?php if ($sort === 'statut'): ?>
                                        <span style="color:#16a34a;"><?= $order === 'ASC' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </th>
                                <th style="text-align:center;width:110px;">🚚 Livraison</th>
                                <th style="text-align:center;width:100px;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center;padding:2rem;color:#64748b;">
                                    <div style="font-size:1.2rem;margin-bottom:0.5rem;">🔍 Aucune commande trouvée</div>
                                    <small>Ajustez vos critères de recherche ou <a href="order_form.php" style="color:#16a34a;">créez une nouvelle commande</a>.</small>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="font-weight:600;color:#0f172a;text-align:center;">#<?= (int) $o['id_commande'] ?></td>
                                    <td>
                                        <div style="font-weight:600;color:#0f172a;font-size:0.95rem;">
                                            <?= h((string) ($o['produit_nom'] ?? '❌ Supprimé')) ?>
                                        </div>
                                        <div style="color:#64748b;font-size:0.85rem;">
                                            <?= h((string) ($o['produit_marque'] ?? 'N/A')) ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center;font-weight:500;"><?= (int) $o['id_utilisateur'] ?></td>
                                    <td style="text-align:center;font-weight:600;color:#16a34a;"><?= (int) $o['quantite'] ?> ✓</td>
                                    <td style="text-align:right;font-weight:600;color:#0f172a;font-size:0.95rem;">
                                        <?= number_format((float) $o['prix_total'], 2, ',', ' ') ?> <span style="font-size:0.85rem;">DT</span>
                                    </td>
                                    <td style="text-align:center;font-size:0.85rem;color:#64748b;">
                                        <?php 
                                            $date = new DateTime($o['date_commande']);
                                            echo $date->format('d/m/Y');
                                        ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge <?= match($o['statut']) {
                                            'confirmee', 'livree' => 'badge-green',
                                            'annulee' => 'badge-red',
                                            'en-preparation' => 'badge-blue',
                                            default => 'badge-amber'
                                        } ?>" style="font-size:0.8rem;padding:0.35rem 0.6rem;display:inline-block;">
                                            <?= match($o['statut']) {
                                                'en-cours' => '⏳ En cours',
                                                'en-preparation' => '📦 Prép.',
                                                'confirmee' => '✅ Confirmée',
                                                'livree' => '🚚 Livrée',
                                                'annulee' => '❌ Annulée',
                                                default => h($o['statut'])
                                            } ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;font-size:0.85rem;">
                                        <div><?= h((string) ($o['mode_livraison'] ?? 'standard')) ?></div>
                                        <div style="color:#64748b;">
                                            <?php 
                                                if (!empty($o['date_livraison_souhaitee'])) {
                                                    $delivDate = new DateTime($o['date_livraison_souhaitee']);
                                                    echo $delivDate->format('d/m/Y');
                                                } else {
                                                    echo '--';
                                                }
                                            ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <div style="display:flex;gap:0.35rem;justify-content:center;">
                                            <a class="table-btn save-btn" href="order_form.php?id=<?= (int) $o['id_commande'] ?>" title="Modifier">✏️</a>
                                            <form action="order_delete.php" method="post" onsubmit="return confirm('Confirmer la suppression ?');" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= (int) $o['id_commande'] ?>">
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
        </div>
    </div>
</div>

<script>
function sortOrders(column) {
    const params = new URLSearchParams(window.location.search);
    const newOrder = (params.get('sort') === column && params.get('order') === 'ASC') ? 'DESC' : 'ASC';
    params.set('sort', column);
    params.set('order', newOrder);
    window.location.search = params.toString();
}
</script>
</body>
</html>
