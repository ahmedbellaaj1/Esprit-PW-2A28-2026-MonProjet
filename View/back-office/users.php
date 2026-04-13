<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Controller/bootstrap.php';
require_once __DIR__ . '/../../Model/User.php';

requireAdmin();

$userModel = new User();
$users = $userModel->getAll();
$flash = getFlash();
$sessionUser = $_SESSION['user'];
$initials = strtoupper(substr((string) $sessionUser['prenom'], 0, 1) . substr((string) $sessionUser['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Back Office</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-logo">Green<span>Bite</span></div>
            <div class="sidebar-role">Administration</div>
            <nav class="sidebar-nav">
                <a class="sidebar-link" href="#"><span class="icon">📊</span> Vue d ensemble</a>
                <a class="sidebar-link" href="#"><span class="icon">🛒</span> Produits</a>
                <a class="sidebar-link" href="#"><span class="icon">⭐</span> Evaluations</a>
                <a class="sidebar-link active" href="/projetwebnova/View/back-office/users.php"><span class="icon">👥</span> Utilisateurs</a>
                <a class="sidebar-link" href="#"><span class="icon">🍽️</span> Recettes</a>
                <a class="sidebar-link" href="#"><span class="icon">🎁</span> Dons</a>
                <a class="sidebar-link" href="#"><span class="icon">📍</span> Magasins</a>
                <a class="sidebar-link" href="#"><span class="icon">📈</span> Rapports</a>
            </nav>
            <div class="sidebar-bottom">
                <a class="sidebar-link" href="#"><span class="icon">⚙️</span> Parametres</a>
                <form action="../../Controller/auth.php" method="post" class="sidebar-link sidebar-logout-form">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="sidebar-link-btn"><span class="icon">🚪</span> Deconnexion</button>
                </form>
            </div>
        </aside>

        <div class="dashboard-main">
            <header class="dashboard-header">
                <div class="header-title">Administration des utilisateurs</div>
                <div class="header-right">
                    <span class="header-badge">Role: <?= htmlspecialchars((string) $sessionUser['role']) ?></span>
                    <div class="admin-avatar"><?= htmlspecialchars($initials !== '' ? $initials : 'AD') ?></div>
                </div>
            </header>

            <div class="page-content users-page-content">
                <section class="users-card card" aria-label="Tableau des utilisateurs">
                    <div class="page-header users-page-header">
                        <h1>CRUD utilisateurs</h1>
                        <p>Creation, lecture, mise a jour et suppression des utilisateurs.</p>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
                    <?php endif; ?>

                    <div class="table-container">
                        <div class="table-toolbar">
                            <div class="table-search">
                                <span>🔍</span>
                                <input type="text" id="userSearch" placeholder="Rechercher par nom, prenom ou email...">
                            </div>
                            <button type="button" class="btn-add" id="openAddUserModal">+ Ajouter utilisateur</button>
                        </div>

                        <div class="table-wrapper">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prenom</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Statut</th>
                                        <th>Inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <?php foreach ($users as $user): ?>
                                        <tr
                                            data-id="<?= (int) $user['id'] ?>"
                                            data-nom="<?= htmlspecialchars($user['nom']) ?>"
                                            data-prenom="<?= htmlspecialchars($user['prenom']) ?>"
                                            data-email="<?= htmlspecialchars($user['email']) ?>"
                                            data-role="<?= htmlspecialchars($user['role']) ?>"
                                            data-statut="<?= htmlspecialchars($user['statut']) ?>"
                                        >
                                            <td><?= (int) $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['nom']) ?></td>
                                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['role']) ?></td>
                                            <td><?= htmlspecialchars($user['statut']) ?></td>
                                            <td><?= htmlspecialchars((string) $user['date_inscription']) ?></td>
                                            <td class="actions-cell">
                                                <button type="button" class="table-btn save-btn open-update-btn">Update</button>
                                                <button type="button" class="table-btn delete-btn open-delete-btn">Delete</button>
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

    <div class="form-modal-overlay" id="addUserModal">
        <div class="form-modal">
            <button class="modal-close" type="button" data-close-modal="addUserModal">x</button>
            <h2>Ajouter un utilisateur</h2>
            <form action="../../Controller/user.php" method="post">
                <input type="hidden" name="action" value="create_user">
                <div class="form-row">
                    <div class="form-group">
                        <label for="addNom">Nom</label>
                        <input id="addNom" type="text" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="addPrenom">Prenom</label>
                        <input id="addPrenom" type="text" name="prenom" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addEmail">Email</label>
                        <input id="addEmail" type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="addPassword">Mot de passe</label>
                        <input id="addPassword" type="password" name="mot_de_passe" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addRole">Role</label>
                        <select id="addRole" name="role" required>
                            <option value="user">User</option>
                            <option value="moderateur">Moderateur</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="addStatut">Statut</label>
                        <select id="addStatut" name="statut" required>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" data-close-modal="addUserModal">Annuler</button>
                    <button type="submit" class="primary-btn" style="padding:10px 24px;">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="form-modal-overlay" id="updateUserModal">
        <div class="form-modal">
            <button class="modal-close" type="button" data-close-modal="updateUserModal">x</button>
            <h2>Modifier utilisateur</h2>
            <form action="../../Controller/user.php" method="post" id="updateUserForm">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" id="updateId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="updateNom">Nom</label>
                        <input id="updateNom" type="text" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="updatePrenom">Prenom</label>
                        <input id="updatePrenom" type="text" name="prenom" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="updateEmail">Email</label>
                        <input id="updateEmail" type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="updateRole">Role</label>
                        <select id="updateRole" name="role" required>
                            <option value="user">User</option>
                            <option value="moderateur">Moderateur</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="updateStatut">Statut</label>
                        <select id="updateStatut" name="statut" required>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" data-close-modal="updateUserModal">Annuler</button>
                    <button type="submit" class="primary-btn" style="padding:10px 24px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="form-modal-overlay" id="confirmDeleteModal">
        <div class="confirm-modal">
            <div style="font-size:2.4rem;margin-bottom:0.7rem;">!</div>
            <h3>Supprimer cet utilisateur ?</h3>
            <p>Cette action est irreversible.</p>
            <form action="../../Controller/user.php" method="post" id="deleteUserForm">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="id" id="deleteId">
                <div style="display:flex;gap:0.75rem;justify-content:center;">
                    <button type="button" class="btn-cancel" data-close-modal="confirmDeleteModal">Annuler</button>
                    <button type="submit" class="btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var searchInput = document.getElementById('userSearch');
            var rows = document.querySelectorAll('#usersTableBody tr');
            var addUserModal = document.getElementById('addUserModal');
            var updateUserModal = document.getElementById('updateUserModal');
            var confirmDeleteModal = document.getElementById('confirmDeleteModal');
            var openAddBtn = document.getElementById('openAddUserModal');
            var closeModalBtns = document.querySelectorAll('[data-close-modal]');
            var openUpdateBtns = document.querySelectorAll('.open-update-btn');
            var openDeleteBtns = document.querySelectorAll('.open-delete-btn');
            var updateId = document.getElementById('updateId');
            var updateNom = document.getElementById('updateNom');
            var updatePrenom = document.getElementById('updatePrenom');
            var updateEmail = document.getElementById('updateEmail');
            var updateRole = document.getElementById('updateRole');
            var updateStatut = document.getElementById('updateStatut');
            var deleteId = document.getElementById('deleteId');

            function openModal(modal) {
                if (modal) {
                    modal.classList.add('open');
                }
            }

            function closeModal(modal) {
                if (modal) {
                    modal.classList.remove('open');
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var query = searchInput.value.trim().toLowerCase();
                    rows.forEach(function (row) {
                        var text = row.textContent.toLowerCase();
                        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
                    });
                });
            }

            if (openAddBtn) {
                openAddBtn.addEventListener('click', function () {
                    openModal(addUserModal);
                });
            }

            closeModalBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var modalId = btn.getAttribute('data-close-modal');
                    closeModal(document.getElementById(modalId));
                });
            });

            [addUserModal, updateUserModal, confirmDeleteModal].forEach(function (modal) {
                if (!modal) {
                    return;
                }

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            openUpdateBtns.forEach(function (button) {
                button.addEventListener('click', function () {
                    var row = button.closest('tr');
                    if (!row) {
                        return;
                    }

                    updateId.value = row.getAttribute('data-id') || '';
                    updateNom.value = row.getAttribute('data-nom') || '';
                    updatePrenom.value = row.getAttribute('data-prenom') || '';
                    updateEmail.value = row.getAttribute('data-email') || '';
                    updateRole.value = row.getAttribute('data-role') || 'user';
                    updateStatut.value = row.getAttribute('data-statut') || 'actif';

                    openModal(updateUserModal);
                });
            });

            openDeleteBtns.forEach(function (button) {
                button.addEventListener('click', function () {
                    var row = button.closest('tr');
                    if (!row) {
                        return;
                    }

                    deleteId.value = row.getAttribute('data-id') || '';
                    openModal(confirmDeleteModal);
                });
            });
        })();
    </script>
</body>
</html>
