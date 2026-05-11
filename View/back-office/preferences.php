<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Controller/bootstrap.php';
require_once __DIR__ . '/../../Controller/PreferenceRepository.php';
require_once __DIR__ . '/../../Controller/AllergyRepository.php';

requireAdmin();

$preferenceRepo = new PreferenceRepository();
$allergyRepo = new AllergyRepository();

$preferences = $preferenceRepo->getAll();
$allergies = $allergyRepo->getAll();

$flash = getFlash();
$sessionUser = $_SESSION['user'];
$initials = strtoupper(substr((string) $sessionUser['prenom'], 0, 1) . substr((string) $sessionUser['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préférences & Allergies - Back Office</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; }
        .tab-btn { padding: 0.75rem 1.5rem; cursor: pointer; border: none; background: none; font-weight: 600; color: #64748b; transition: all 0.3s; }
        .tab-btn.active { color: #10b981; border-bottom: 3px solid #10b981; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
<?php $activePage = 'preferences'; include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <header class="dashboard-header">
                <div class="header-title">Gestion des Préférences & Allergies</div>
                <div class="header-right">
                    <span class="header-badge">Admin</span>
                    <div class="admin-avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
            </header>

            <div class="page-content">
                <?php if ($flash): ?>
                    <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <!-- Statistiques rapides -->
                <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="stat-card card" style="padding: 1.5rem; border-left: 4px solid #10b981;">
                        <div style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL PRÉFÉRENCES</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1e293b;"><?= count($preferences) ?></div>
                    </div>
                    <div class="stat-card card" style="padding: 1.5rem; border-left: 4px solid #f59e0b;">
                        <div style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL ALLERGIES</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1e293b;"><?= count($allergies) ?></div>
                    </div>
                    <div class="stat-card card" style="padding: 1.5rem; border-left: 4px solid #3b82f6;">
                        <div style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TENDANCE IA DU MOMENT</div>
                        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-top: 0.5rem;">
                            <?php
                                // Petite logique pour trouver le mot-clé le plus présent dans les messages récents
                                try {
                                    $stmt = getPdo()->query("SELECT contenu FROM chat_messages WHERE type='utilisateur' ORDER BY id_message DESC LIMIT 100");
                                    $msgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    $all_text = strtolower(implode(' ', $msgs));
                                    $trends = [];
                                    foreach ($preferences as $p) {
                                        $count = substr_count($all_text, strtolower($p->getNom()));
                                        if ($count > 0) $trends[$p->getNom()] = $count;
                                    }
                                    arsort($trends);
                                    echo !empty($trends) ? key($trends) : 'Aucune donnée';
                                } catch (Exception $e) { echo 'N/A'; }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Barre de recherche globale -->
                <div class="table-toolbar" style="margin-bottom: 2rem; background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div class="table-search" style="width: 100%; max-width: 500px;">
                        <span>🔍</span>
                        <input type="text" id="globalSearch" placeholder="Rechercher une préférence ou une allergie..." style="width: 100%; border: none; outline: none; padding: 0.5rem;">
                    </div>
                </div>

                <div class="tabs">
                    <button class="tab-btn active" onclick="showTab('tab-preferences')">🥗 Préférences Alimentaires</button>
                    <button class="tab-btn" onclick="showTab('tab-allergies')">⚠️ Allergies</button>
                </div>

                <!-- Section PREFERENCES -->
                <section id="tab-preferences" class="tab-content active card">
                    <div class="page-header">
                        <h2>Préférences Alimentaires</h2>
                        <button class="btn-add" onclick="openAddModal('preference')">+ Ajouter une préférence</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preferences as $p): ?>
                                    <tr>
                                        <td><?= $p->getId() ?></td>
                                        <td><strong><?= htmlspecialchars($p->getNom()) ?></strong></td>
                                        <td><?= htmlspecialchars($p->getDescription() ?? '-') ?></td>
                                        <td class="actions-cell">
                                            <button class="table-btn save-btn" onclick="openUpdateModal('preference', <?= $p->getId() ?>, '<?= addslashes($p->getNom()) ?>', '<?= addslashes($p->getDescription() ?? '') ?>')">Modifier</button>
                                            <button class="table-btn delete-btn" onclick="openDeleteModal('preference', <?= $p->getId() ?>)">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Section ALLERGIES -->
                <section id="tab-allergies" class="tab-content card">
                    <div class="page-header">
                        <h2>Types d'Allergies</h2>
                        <button class="btn-add" onclick="openAddModal('allergy')">+ Ajouter une allergie</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allergies as $a): ?>
                                    <tr>
                                        <td><?= $a->getId() ?></td>
                                        <td><strong><?= htmlspecialchars($a->getNom()) ?></strong></td>
                                        <td><?= htmlspecialchars($a->getDescription() ?? '-') ?></td>
                                        <td class="actions-cell">
                                            <button class="table-btn save-btn" onclick="openUpdateModal('allergy', <?= $a->getId() ?>, '<?= addslashes($a->getNom()) ?>', '<?= addslashes($a->getDescription() ?? '') ?>')">Modifier</button>
                                            <button class="table-btn delete-btn" onclick="openDeleteModal('allergy', <?= $a->getId() ?>)">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Modals (CRUD) -->
    <div class="form-modal-overlay" id="crudModal">
        <div class="form-modal">
            <button class="modal-close" onclick="closeModal()">x</button>
            <h2 id="modalTitle">Ajouter</h2>
            <form action="../../Controller/master_data.php" method="post">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="type" id="formType" value="">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="formNom" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="formDescription" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="primary-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="form-modal-overlay" id="deleteModal">
        <div class="confirm-modal">
            <h3>Supprimer cet élément ?</h3>
            <p>Cette action est irréversible.</p>
            <form action="../../Controller/master_data.php" method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" id="deleteType">
                <input type="hidden" name="id" id="deleteId">
                <div style="display:flex;gap:0.75rem;justify-content:center;margin-top:1.5rem;">
                    <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
                    <button type="submit" class="btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function openAddModal(type) {
            document.getElementById('modalTitle').innerText = "Ajouter " + (type === 'preference' ? 'une préférence' : 'une allergie');
            document.getElementById('formAction').value = 'create';
            document.getElementById('formType').value = type;
            document.getElementById('formId').value = '';
            document.getElementById('formNom').value = '';
            document.getElementById('formDescription').value = '';
            document.getElementById('crudModal').classList.add('open');
        }

        function openUpdateModal(type, id, nom, desc) {
            document.getElementById('modalTitle').innerText = "Modifier " + (type === 'preference' ? 'la préférence' : "l'allergie");
            document.getElementById('formAction').value = 'update';
            document.getElementById('formType').value = type;
            document.getElementById('formId').value = id;
            document.getElementById('formNom').value = nom;
            document.getElementById('formDescription').value = desc;
            document.getElementById('crudModal').classList.add('open');
        }

        function closeModal() { document.getElementById('crudModal').classList.remove('open'); }

        function openDeleteModal(type, id) {
            document.getElementById('deleteType').value = type;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.add('open');
        }

        function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('open'); }

        // Recherche en temps réel
        document.getElementById('globalSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.users-table tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
