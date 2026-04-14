<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Model/Produit.php';

$produitModel = new Produit();
$produits     = $produitModel->getAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits – Back Office GreenBite</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">Green<span>Bite</span></div>
        <div class="sidebar-role">Administration</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link active" href="/projetwebnova/View/back-office/produits.php">🛒 Produits</a>
            <a class="sidebar-link" href="/projetwebnova/View/back-office/commandes.php">📦 Commandes</a>
        </nav>
        <div class="sidebar-bottom">
            <a href="/projetwebnova/Controller/logout.php" class="sidebar-link-btn">🚪 Déconnexion</a>
        </div>
    </aside>

    <div class="dashboard-main">

        <header class="dashboard-header">
            <div class="header-title">Gestion des Produits</div>
            <div class="header-right">
                <span class="header-badge">Role: Admin</span>
                <div class="admin-avatar">AD</div>
            </div>
        </header>

        <div class="page-content">
            <section class="users-card card">

                <div class="page-header">
                    <h1>CRUD Produits</h1>
                    <p>Création, lecture, mise à jour et suppression des produits.</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert <?= htmlspecialchars($flash['type']) ?>">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="table-search">
                            <span>🔍</span>
                            <input type="text" id="produitSearch" placeholder="Rechercher par nom, marque, catégorie...">
                        </div>
                        <button type="button" class="btn-add" id="openAddModal">+ Ajouter produit</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Marque</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Nutri-Score</th>
                                    <th>Calories</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="produitsTableBody">
                                <?php foreach ($produits as $p): ?>
                                    <tr
                                        data-id="<?= (int)$p['id_produit'] ?>"
                                        data-nom="<?= htmlspecialchars((string)$p['nom']) ?>"
                                        data-marque="<?= htmlspecialchars((string)($p['marque'] ?? '')) ?>"
                                        data-code="<?= htmlspecialchars((string)($p['code_barre'] ?? '')) ?>"
                                        data-categorie="<?= htmlspecialchars((string)($p['categorie'] ?? '')) ?>"
                                        data-prix="<?= htmlspecialchars((string)$p['prix']) ?>"
                                        data-calories="<?= htmlspecialchars((string)($p['calories'] ?? '')) ?>"
                                        data-proteines="<?= htmlspecialchars((string)($p['proteines'] ?? '')) ?>"
                                        data-glucides="<?= htmlspecialchars((string)($p['glucides'] ?? '')) ?>"
                                        data-lipides="<?= htmlspecialchars((string)($p['lipides'] ?? '')) ?>"
                                        data-nutriscore="<?= htmlspecialchars((string)($p['nutriscore'] ?? 'C')) ?>"
                                        data-statut="<?= htmlspecialchars((string)$p['statut']) ?>"
                                    >
                                        <td><?= (int)$p['id_produit'] ?></td>
                                        <td><?= htmlspecialchars($p['nom']) ?></td>
                                        <td><?= htmlspecialchars($p['marque'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($p['categorie'] ?? '—') ?></td>
                                        <td><strong><?= number_format((float)$p['prix'], 2) ?> DT</strong></td>
                                        <td>
                                            <span class="ns-badge ns-<?= strtolower((string)($p['nutriscore'] ?? 'c')) ?>">
                                                <?= htmlspecialchars((string)($p['nutriscore'] ?? '?')) ?>
                                            </span>
                                        </td>
                                        <td><?= $p['calories'] !== null ? htmlspecialchars((string)$p['calories']) . ' kcal' : '—' ?></td>
                                        <td>
                                            <?php if ($p['statut'] === 'disponible'): ?>
                                                <span class="badge badge-green">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge badge-red">Rupture</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions-cell">
                                            <button type="button" class="table-btn save-btn btn-update">Update</button>
                                            <button type="button" class="table-btn delete-btn btn-delete">Delete</button>
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


<div class="form-modal-overlay" id="addModal">
    <div class="form-modal">
        <button class="modal-close" type="button" data-close="addModal">✕</button>
        <h2>Ajouter un produit</h2>
        <form action="/projetwebnova/Controller/produit.php" method="post">
            <input type="hidden" name="action" value="create_produit">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" placeholder="ex: Yaourt nature bio" required>
                </div>
                <div class="form-group">
                    <label>Marque</label>
                    <input type="text" name="marque" placeholder="ex: Danone">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix (DT) *</label>
                    <input type="number" step="0.01" min="0" name="prix" placeholder="ex: 3.50" required>
                </div>
                <div class="form-group">
                    <label>Code-barre</label>
                    <input type="text" name="code_barre" placeholder="ex: 3017620422001">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="categorie">
                        <option value="">-- Choisir --</option>
                        <option>Produits laitiers</option>
                        <option>Céréales & Pains</option>
                        <option>Boissons</option>
                        <option>Fruits & Légumes</option>
                        <option>Snacks & Biscuits</option>
                        <option>Conserves</option>
                        <option>Épicerie</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nutri-Score</label>
                    <select name="nutriscore">
                        <option value="A">A – Excellent</option>
                        <option value="B">B – Bon</option>
                        <option value="C" selected>C – Moyen</option>
                        <option value="D">D – Médiocre</option>
                        <option value="E">E – Mauvais</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Calories (kcal/100g)</label>
                    <input type="number" step="0.1" name="calories" placeholder="ex: 95">
                </div>
                <div class="form-group">
                    <label>Protéines (g/100g)</label>
                    <input type="number" step="0.1" name="proteines" placeholder="ex: 4.5">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Glucides (g/100g)</label>
                    <input type="number" step="0.1" name="glucides" placeholder="ex: 12">
                </div>
                <div class="form-group">
                    <label>Lipides (g/100g)</label>
                    <input type="number" step="0.1" name="lipides" placeholder="ex: 3.2">
                </div>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="disponible">Disponible</option>
                    <option value="rupture">Rupture de stock</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" data-close="addModal">Annuler</button>
                <button type="submit" class="primary-btn">Ajouter</button>
            </div>
        </form>
    </div>
</div>


<div class="form-modal-overlay" id="updateModal">
    <div class="form-modal">
        <button class="modal-close" type="button" data-close="updateModal">✕</button>
        <h2>Modifier le produit</h2>
        <form action="/projetwebnova/Controller/produit.php" method="post">
            <input type="hidden" name="action" value="update_produit">
            <input type="hidden" name="id_produit" id="uId">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" id="uNom" required>
                </div>
                <div class="form-group">
                    <label>Marque</label>
                    <input type="text" name="marque" id="uMarque">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix (DT) *</label>
                    <input type="number" step="0.01" min="0" name="prix" id="uPrix" required>
                </div>
                <div class="form-group">
                    <label>Code-barre</label>
                    <input type="text" name="code_barre" id="uCode">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="categorie" id="uCategorie">
                        <option value="">-- Choisir --</option>
                        <option>Produits laitiers</option>
                        <option>Céréales & Pains</option>
                        <option>Boissons</option>
                        <option>Fruits & Légumes</option>
                        <option>Snacks & Biscuits</option>
                        <option>Conserves</option>
                        <option>Épicerie</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nutri-Score</label>
                    <select name="nutriscore" id="uNutriscore">
                        <option value="A">A – Excellent</option>
                        <option value="B">B – Bon</option>
                        <option value="C">C – Moyen</option>
                        <option value="D">D – Médiocre</option>
                        <option value="E">E – Mauvais</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Calories</label>
                    <input type="number" step="0.1" name="calories" id="uCalories">
                </div>
                <div class="form-group">
                    <label>Protéines</label>
                    <input type="number" step="0.1" name="proteines" id="uProteines">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Glucides</label>
                    <input type="number" step="0.1" name="glucides" id="uGlucides">
                </div>
                <div class="form-group">
                    <label>Lipides</label>
                    <input type="number" step="0.1" name="lipides" id="uLipides">
                </div>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut" id="uStatut">
                    <option value="disponible">Disponible</option>
                    <option value="rupture">Rupture de stock</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" data-close="updateModal">Annuler</button>
                <button type="submit" class="primary-btn">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


<div class="form-modal-overlay" id="deleteModal">
    <div class="confirm-modal">
        <div style="font-size:2.4rem;margin-bottom:0.7rem;">🗑️</div>
        <h3>Supprimer ce produit ?</h3>
        <p>Cette action est irréversible.</p>
        <form action="/projetwebnova/Controller/produit.php" method="post">
            <input type="hidden" name="action" value="delete_produit">
            <input type="hidden" name="id_produit" id="dId">
            <div style="display:flex;gap:0.75rem;justify-content:center;">
                <button type="button" class="btn-cancel" data-close="deleteModal">Annuler</button>
                <button type="submit" class="btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>

document.getElementById('produitSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#produitsTableBody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});


function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.close));
});
document.querySelectorAll('.form-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('active'); });
});


document.getElementById('openAddModal').addEventListener('click', () => openModal('addModal'));


document.querySelectorAll('.btn-update').forEach(btn => {
    btn.addEventListener('click', function () {
        const row = this.closest('tr');
        document.getElementById('uId').value         = row.dataset.id;
        document.getElementById('uNom').value        = row.dataset.nom;
        document.getElementById('uMarque').value     = row.dataset.marque;
        document.getElementById('uCode').value       = row.dataset.code;
        document.getElementById('uPrix').value       = row.dataset.prix;
        document.getElementById('uCalories').value   = row.dataset.calories;
        document.getElementById('uProteines').value  = row.dataset.proteines;
        document.getElementById('uGlucides').value   = row.dataset.glucides;
        document.getElementById('uLipides').value    = row.dataset.lipides;

        const selCat = document.getElementById('uCategorie');
        [...selCat.options].forEach(o => o.selected = o.value === row.dataset.categorie);

        const selNs = document.getElementById('uNutriscore');
        [...selNs.options].forEach(o => o.selected = o.value === row.dataset.nutriscore);

        const selSt = document.getElementById('uStatut');
        [...selSt.options].forEach(o => o.selected = o.value === row.dataset.statut);

        openModal('updateModal');
    });
});


document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const row = this.closest('tr');
        document.getElementById('dId').value = row.dataset.id;
        openModal('deleteModal');
    });
});
</script>
</body>
</html>