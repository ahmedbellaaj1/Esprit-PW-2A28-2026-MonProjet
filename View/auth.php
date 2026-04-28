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
$formState = consumeFormState();
$activeTab = $formState['tab'] ?? 'login-panel';
$fieldErrors = $formState['errors'] ?? [];
$oldInput = $formState['old'] ?? [];
$resetToken = $_GET['token'] ?? null;

// If a token is provided in URL, show the reset password form
if ($resetToken !== null && $resetToken !== '') {
    $activeTab = 'reset-password-panel';
}

function field_error(array $errors, string $name): string
{
    return isset($errors[$name]) ? '<div class="alert error field-error">' . htmlspecialchars((string) $errors[$name]) . '</div>' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - GreenBite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-card card" aria-label="Page de connexion et creation de compte">
            <aside class="auth-side">
                <div>
                    <img src="../uploads/logo.png" alt="GreenBite Logo" class="auth-side-logo">
                    <h1>GreenBite</h1>
                    <p>Connexion et inscription utilisateur </p>
                </div>
                
            </aside>

            <div class="auth-content">
                <?php if ($flash): ?>
                    <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <div class="auth-tabs" role="tablist" aria-label="Choix du formulaire">
                    <button type="button" class="auth-tab <?= $activeTab === 'login-panel' ? 'active' : '' ?>" data-target="login-panel" role="tab" aria-controls="login-panel" aria-selected="<?= $activeTab === 'login-panel' ? 'true' : 'false' ?>">Login</button>
                    <button type="button" class="auth-tab <?= $activeTab === 'register-panel' ? 'active' : '' ?>" data-target="register-panel" role="tab" aria-controls="register-panel" aria-selected="<?= $activeTab === 'register-panel' ? 'true' : 'false' ?>">Creer un compte</button>
                </div>

                <section id="login-panel" class="auth-panel <?= $activeTab === 'login-panel' ? 'active' : '' ?>" aria-label="Formulaire de connexion" role="tabpanel">
                    <div class="auth-panel-header">
                        <img src="../uploads/logo.png" alt="GreenBite Logo" class="auth-panel-logo">
                    </div>
                    <h2 class="auth-title">Connexion</h2>
                    <p class="auth-subtitle">Connectez-vous avec votre email et mot de passe.</p>

                    <form action="../Controller/auth.php" method="post" autocomplete="on" novalidate>
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input id="login-email" name="email" type="email" placeholder="nom@domaine.com" value="<?= htmlspecialchars((string) ($oldInput['email'] ?? '')) ?>" required>
                            <?= field_error($fieldErrors, 'email') ?>
                        </div>

                        <div class="form-group">
                            <label for="login-password">Mot de passe</label>
                            <input id="login-password" name="mot_de_passe" type="password" placeholder="********" required>
                            <?= field_error($fieldErrors, 'mot_de_passe') ?>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Se connecter</button>
                            <button type="button" class="forgot-password-link" id="forgotPasswordBtn" aria-label="Acceder au formulaire de reinitialisation du mot de passe">Mot de passe oublie ?</button>
                        </div>

                        <div class="auth-divider">
                            <span>-------- Ou ---------</span>
                        </div>

                        <?php
                            $baseUrl = getBaseUrl();
                            $redirectUri = $baseUrl . '/projetwebnova/Controller/google-callback.php';
                            
                            error_log('[Google Auth] Base URL: ' . $baseUrl);
                            error_log('[Google Auth] Redirect URI: ' . $redirectUri);
                            
                            $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                                'client_id' => getGoogleClientId(),
                                'redirect_uri' => $redirectUri,
                                'response_type' => 'code',
                                'scope' => 'email profile',
                                'access_type' => 'offline',
                            ]);
                            
                            error_log('[Google Auth] Full URL: ' . $googleAuthUrl);
                        ?>
                        <div class="google-login-container">
                            <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="google-login-btn" title="Continuer avec Google" aria-label="Se connecter avec Google">
                                <i class="fab fa-google"></i>
                                Continuer avec Google
                            </a>
                        </div>
                    </form>
                </section>

                <section id="register-panel" class="auth-panel <?= $activeTab === 'register-panel' ? 'active' : '' ?>" aria-label="Formulaire de creation de compte" role="tabpanel">
                    <div class="auth-panel-header">
                        <img src="../uploads/logo.png" alt="GreenBite Logo" class="auth-panel-logo">
                    </div>
                    <h2 class="auth-title">Creer un compte</h2>
                    <p class="auth-subtitle">Remplissez les champs pour vous inscrire.</p>

                    <form action="../Controller/auth.php" method="post" enctype="multipart/form-data" autocomplete="on" novalidate>
                        <input type="hidden" name="action" value="register">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="reg-nom">Nom</label>
                                <input id="reg-nom" name="nom" type="text" placeholder="Nom" value="<?= htmlspecialchars((string) ($oldInput['nom'] ?? '')) ?>" required>
                                <?= field_error($fieldErrors, 'nom') ?>
                            </div>

                            <div class="form-group">
                                <label for="reg-prenom">Prenom</label>
                                <input id="reg-prenom" name="prenom" type="text" placeholder="Prenom" value="<?= htmlspecialchars((string) ($oldInput['prenom'] ?? '')) ?>" required>
                                <?= field_error($fieldErrors, 'prenom') ?>
                            </div>

                            <div class="form-group full">
                                <label for="reg-email">Email</label>
                                <input id="reg-email" name="email" type="email" placeholder="nom@domaine.com" value="<?= htmlspecialchars((string) ($oldInput['email'] ?? '')) ?>" required>
                                <?= field_error($fieldErrors, 'email') ?>
                            </div>

                            <div class="form-group">
                                <label for="reg-password">Mot de passe</label>
                                <input id="reg-password" name="mot_de_passe" type="password" placeholder="********" required>
                                <?= field_error($fieldErrors, 'mot_de_passe') ?>
                            </div>

                            <div class="form-group">
                                <label for="reg-photo">Photo</label>
                                <input id="reg-photo" name="photo" type="file" accept="image/*">
                                <?= field_error($fieldErrors, 'photo') ?>

                                <div class="profile-photo-preview is-hidden auth-photo-preview" id="registerPhotoPreview">
                                    <button type="button" class="profile-photo-remove" id="removeRegisterPhoto" aria-label="Retirer la photo">x</button>
                                    <img src="" alt="Apercu de la photo" class="profile-photo-image" id="registerPhotoImage">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reg-role">Role</label>
                                <select id="reg-role" name="role" required>
                                    <option value="user" <?= ($oldInput['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="moderateur" <?= ($oldInput['role'] ?? '') === 'moderateur' ? 'selected' : '' ?>>Moderateur</option>
                                    <option value="admin" <?= ($oldInput['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <?= field_error($fieldErrors, 'role') ?>
                            </div>

                            <div class="form-group">
                                <label for="reg-statut">Statut</label>
                                <select id="reg-statut" name="statut" required>
                                    <option value="actif" <?= ($oldInput['statut'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif" <?= ($oldInput['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                    <option value="suspendu" <?= ($oldInput['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                                </select>
                                <?= field_error($fieldErrors, 'statut') ?>
                            </div>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Creer le compte</button>
                        </div>
                    </form>
                </section>

                <section id="forgot-password-panel" class="auth-panel <?= $activeTab === 'forgot-password-panel' ? 'active' : '' ?>" aria-label="Formulaire de reinitialisation du mot de passe" role="tabpanel">
                    <h2 class="auth-title">Mot de passe oublie</h2>
                    <p class="auth-subtitle">Entrez votre email pour recevoir un lien de reinitialisation.</p>

                    <form action="../Controller/auth.php" method="post" autocomplete="on" novalidate>
                        <input type="hidden" name="action" value="request-password-reset">
                        <div class="form-group">
                            <label for="forgot-email">Email</label>
                            <input id="forgot-email" name="email" type="email" placeholder="nom@domaine.com" value="<?= htmlspecialchars((string) ($oldInput['email'] ?? '')) ?>" required>
                            <?= field_error($fieldErrors, 'email') ?>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Envoyer le lien</button>
                            <button type="button" class="forgot-password-back" id="backToLoginBtn" aria-label="Retour a la connexion">Retour</button>
                        </div>
                    </form>
                </section>

                <section id="reset-password-panel" class="auth-panel <?= $activeTab === 'reset-password-panel' ? 'active' : '' ?>" aria-label="Formulaire de reinitialisation du mot de passe" role="tabpanel">
                    <h2 class="auth-title">Reinitialiser votre mot de passe</h2>
                    <p class="auth-subtitle">Entrez votre nouveau mot de passe ci-dessous.</p>

                    <form action="../Controller/auth.php" method="post" autocomplete="on" novalidate>
                        <input type="hidden" name="action" value="reset-password">
                        <input type="hidden" name="token" value="<?= htmlspecialchars((string) $resetToken) ?>">
                        <div class="form-group">
                            <label for="reset-password">Nouveau mot de passe</label>
                            <input id="reset-password" name="mot_de_passe" type="password" placeholder="********" required>
                            <?= field_error($fieldErrors, 'mot_de_passe') ?>
                        </div>

                        <div class="form-group">
                            <label for="reset-password-confirm">Confirmer le mot de passe</label>
                            <input id="reset-password-confirm" name="mot_de_passe_confirm" type="password" placeholder="********" required>
                            <?= field_error($fieldErrors, 'mot_de_passe_confirm') ?>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="primary-btn">Reinitialiser le mot de passe</button>
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
            var forgotPasswordBtn = document.getElementById('forgotPasswordBtn');
            var backToLoginBtn = document.getElementById('backToLoginBtn');

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

            if (forgotPasswordBtn) {
                forgotPasswordBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    activate('forgot-password-panel');
                });
            }

            if (backToLoginBtn) {
                backToLoginBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    activate('login-panel');
                });
            }

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

            activate(<?= json_encode($activeTab) ?>);
        })();
    </script>
</body>
</html>
