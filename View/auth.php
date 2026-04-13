<?php

declare(strict_types=1);

require_once __DIR__ . '/../Controller/bootstrap.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        redirect('/projetwebnova/View/back-office/users.php');
    }
    redirect('/projetwebnova/View/front-office/profile.php');
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - GreenBit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card card" aria-label="Page de connexion et creation de compte">
            <aside class="auth-side">
                <div>
                    <h1>GreenBit</h1>
                    <p>Connexion et inscription utilisateur (PHP + MySQL).</p>
                </div>
                <p>Version front office</p>
            </aside>

            <div class="auth-content">
                <?php if ($flash): ?>
                    <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <div class="auth-tabs" role="tablist" aria-label="Choix du formulaire">
                    <button type="button" class="auth-tab active" data-target="login-panel" role="tab" aria-controls="login-panel" aria-selected="true">Login</button>
                    <button type="button" class="auth-tab" data-target="register-panel" role="tab" aria-controls="register-panel" aria-selected="false">Creer un compte</button>
                </div>

                <section id="login-panel" class="auth-panel active" aria-label="Formulaire de connexion" role="tabpanel">
                    <h2 class="auth-title">Connexion</h2>
                    <p class="auth-subtitle">Connectez-vous avec votre email et mot de passe.</p>

                    <form action="../Controller/auth.php" method="post" autocomplete="on">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input id="login-email" name="email" type="email" placeholder="nom@domaine.com" required>
                        </div>

                        <div class="form-group">
                            <label for="login-password">Mot de passe</label>
                            <input id="login-password" name="mot_de_passe" type="password" placeholder="********" required>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Se connecter</button>
                        </div>
                    </form>
                </section>

                <section id="register-panel" class="auth-panel" aria-label="Formulaire de creation de compte" role="tabpanel">
                    <h2 class="auth-title">Creer un compte</h2>
                    <p class="auth-subtitle">Remplissez les champs pour vous inscrire.</p>

                    <form action="../Controller/auth.php" method="post" enctype="multipart/form-data" autocomplete="on">
                        <input type="hidden" name="action" value="register">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="reg-nom">Nom</label>
                                <input id="reg-nom" name="nom" type="text" placeholder="Nom" required>
                            </div>

                            <div class="form-group">
                                <label for="reg-prenom">Prenom</label>
                                <input id="reg-prenom" name="prenom" type="text" placeholder="Prenom" required>
                            </div>

                            <div class="form-group full">
                                <label for="reg-email">Email</label>
                                <input id="reg-email" name="email" type="email" placeholder="nom@domaine.com" required>
                            </div>

                            <div class="form-group">
                                <label for="reg-password">Mot de passe</label>
                                <input id="reg-password" name="mot_de_passe" type="password" placeholder="********" required>
                            </div>

                            <div class="form-group">
                                <label for="reg-photo">Photo</label>
                                <input id="reg-photo" name="photo" type="file" accept="image/*">

                                <div class="profile-photo-preview is-hidden auth-photo-preview" id="registerPhotoPreview">
                                    <button type="button" class="profile-photo-remove" id="removeRegisterPhoto" aria-label="Retirer la photo">x</button>
                                    <img src="" alt="Apercu de la photo" class="profile-photo-image" id="registerPhotoImage">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reg-role">Role</label>
                                <select id="reg-role" name="role" required>
                                    <option value="user" selected>User</option>
                                    <option value="moderateur">Moderateur</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="reg-statut">Statut</label>
                                <select id="reg-statut" name="statut" required>
                                    <option value="actif" selected>Actif</option>
                                    <option value="inactif">Inactif</option>
                                    <option value="suspendu">Suspendu</option>
                                </select>
                            </div>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Creer le compte</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.auth-tab');
            var panels = document.querySelectorAll('.auth-panel');
            var registerPhotoInput = document.getElementById('reg-photo');
            var registerPhotoPreview = document.getElementById('registerPhotoPreview');
            var registerPhotoImage = document.getElementById('registerPhotoImage');
            var removeRegisterPhoto = document.getElementById('removeRegisterPhoto');
            var registerObjectUrl = null;

            function activate(targetId) {
                tabs.forEach(function (tab) {
                    var isActive = tab.getAttribute('data-target') === targetId;
                    tab.classList.toggle('active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('active', panel.id === targetId);
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    activate(tab.getAttribute('data-target'));
                });
            });

            function clearRegisterPreview() {
                if (registerObjectUrl !== null) {
                    URL.revokeObjectURL(registerObjectUrl);
                    registerObjectUrl = null;
                }

                if (registerPhotoInput) {
                    registerPhotoInput.value = '';
                }

                if (registerPhotoImage) {
                    registerPhotoImage.removeAttribute('src');
                }

                if (registerPhotoPreview) {
                    registerPhotoPreview.classList.add('is-hidden');
                }
            }

            if (registerPhotoInput) {
                registerPhotoInput.addEventListener('change', function () {
                    if (registerObjectUrl !== null) {
                        URL.revokeObjectURL(registerObjectUrl);
                        registerObjectUrl = null;
                    }

                    if (registerPhotoInput.files && registerPhotoInput.files[0]) {
                        registerObjectUrl = URL.createObjectURL(registerPhotoInput.files[0]);
                        if (registerPhotoImage) {
                            registerPhotoImage.src = registerObjectUrl;
                        }
                        if (registerPhotoPreview) {
                            registerPhotoPreview.classList.remove('is-hidden');
                        }
                        return;
                    }

                    clearRegisterPreview();
                });
            }

            if (removeRegisterPhoto) {
                removeRegisterPhoto.addEventListener('click', function () {
                    clearRegisterPreview();
                });
            }

            activate('login-panel');
        })();
    </script>
</body>
</html>
