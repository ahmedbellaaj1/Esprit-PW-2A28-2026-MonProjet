<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Model/Commande.php';

$commandeModel = new Commande();
$commandes = $commandeModel->getAll();


$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
if ($flash) unset($_SESSION['flash']);

$statutLabels = [
    'en_attente' => 'En attente',
    'confirmee'  => 'Confirmée',
    'expediee'   => 'Expédiée',
    'livree'     => 'Livrée',
    'annulee'    => 'Annulée',
];

$statutBadges = [
    'en_attente' => 'badge-amber',
    'confirmee'  => 'badge-blue',
    'expediee'   => 'badge-green',
    'livree'     => 'badge-green',
    'annulee'    => 'badge-red',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes - Back Office GreenBite</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">Green<span>Bite</span></div>
        <div class="sidebar-role">Administration</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/projetwebnova/View/back-office/produits.php">🛒 Produits</a>
            <a class="sidebar-link active" href="/projetwebnova/View/back-office/commandes.php">📦 Commandes</a>
            <a class="sidebar-link" href="#">👥 Utilisateurs</a>
        </nav>
        <div class="sidebar-bottom">
            <button onclick="alert('Déconnexion simulée')" class="sidebar-link-btn">🚪 Déconnexion</button>
        </div>
    </aside>

    <div class="dashboard-main">

        <header class="dashboard-header">
            <div class="header-title">Gestion des Commandes</div>
            <div class="header-right">
                <span class="header-badge">Role: Admin</span>
                <div class="admin-avatar">AD</div>
            </div>
        </header>

        <div class="page-content">

            <section class="users-card card">

                <div class="page-header">
                    <h1>CRUD Commandes</h1>
                    <p>Consultez et gérez les commandes des clients.</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert <?= htmlspecialchars($flash['type'] ?? 'info') ?>">
                        <?= htmlspecialchars($flash['message'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="table-search">
                            <span>🔍</span>
                            <input type="text" id="commandeSearch" placeholder="Rechercher par produit, utilisateur...">
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Produit</th>
                                    <th>Utilisateur</th>
                                    <th>Quantité</th>
                                    <th>Prix total</th>
                                    <th>Adresse</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="commandesTableBody">
                                <?php foreach ($commandes as $c): ?>
                                    <tr data-id="<?= (int)$c['id'] ?>" data-statut="<?= htmlspecialchars($c['statut']) ?>">
                                        <td>#<?= (int)$c['id'] ?></td>
                                        <td><?= htmlspecialchars($c['produit_nom'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($c['utilisateur_prenom'] . ' ' . $c['utilisateur_nom'] ?? 'User #' . $c['id_utilisateur']) ?></td>
                                        <td><?= (int)$c['quantite'] ?></td>
                                        <td><strong><?= number_format((float)$c['prix_total'], 2) ?> DT</strong></td>
                                        <td style="max-width:180px; font-size:0.85rem;"><?= htmlspecialchars($c['adresse_livraison'] ?? '—') ?></td>
                                        <td style="font-size:0.82rem;"><?= htmlspecialchars(substr($c['date_commande'] ?? '', 0, 10)) ?></td>
                                        <td>
                                            <span class="badge <?= $statutBadges[$c['statut']] ?? 'badge-gray' ?>">
                                                <?= $statutLabels[$c['statut']] ?? $c['statut'] ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <button type="button" class="table-btn save-btn open-update-commande-btn">Statut</button>
                                            <button type="button" class="table-btn delete-btn open-delete-commande-btn">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </div>
    </div>
</div>



<script>
</script>
</body>
</html>