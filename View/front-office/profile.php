<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Controller/bootstrap.php';
require_once __DIR__ . '/../../Model/User.php';

requireAuth();

$userModel = new User();
$currentUser = $userModel->findById((int) $_SESSION['user']['id']);

if ($currentUser === null) {
    unset($_SESSION['user']);
    setFlash('error', 'Utilisateur introuvable.');
    redirect('/projetwebnova/View/auth.php');
}

$flash = getFlash();
$initials = strtoupper(substr((string) $currentUser['prenom'], 0, 1) . substr((string) $currentUser['nom'], 0, 1));
$photoUrl = null;

if (!empty($currentUser['photo'])) {
    $photoFile = (string) $currentUser['photo'];
    $photoPath = __DIR__ . '/../../uploads/users/' . $photoFile;

    if (is_file($photoPath)) {
        $photoUrl = '/projetwebnova/uploads/users/' . rawurlencode($photoFile);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - GreenBit</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav class="navbar">
        <a class="navbar-logo" href="/projetwebnova/View/front-office/profile.php">Green<span>Bite</span></a>
        <ul class="navbar-links">
            <li><a href="#">Accueil</a></li>
            <li><a href="#">Recettes</a></li>
            <li><a href="#">Produits</a></li>
            <li><a href="#">Dons</a></li>
            <li><a href="#" class="active">Mon profil</a></li>
        </ul>
        <div class="navbar-right">
            <button class="primary-btn nav-quick-btn" type="button">Scanner un produit</button>
            <div class="nav-avatar"><?= htmlspecialchars($initials !== '' ? $initials : 'GB') ?></div>
            <form action="../../Controller/auth.php" method="post" class="navbar-logout-form">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="primary-btn nav-logout-btn">Deconnexion</button>
            </form>
        </div>
    </nav>

    <main class="profile-page">
        <section class="profile-card card" aria-label="Page profil utilisateur connecte">
            <header class="dashboard-header profile-header rounded-3xl">
                <div>
                    <h1>Mon Profil</h1>
                    <p>Consultez et modifiez les informations de votre compte.</p>
                </div>
                <span class="profile-status">Compte <?= htmlspecialchars($currentUser['statut']) ?></span>
            </header>

            <?php if ($flash): ?>
                <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <form action="../../Controller/user.php" method="post" enctype="multipart/form-data" autocomplete="on" class="profile-form">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="profile-id">ID</label>
                        <input id="profile-id" name="id" type="number" value="<?= (int) $currentUser['id'] ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="profile-role">Role</label>
                        <input id="profile-role" name="role" type="text" value="<?= htmlspecialchars($currentUser['role']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="profile-nom">Nom</label>
                        <input id="profile-nom" name="nom" type="text" value="<?= htmlspecialchars($currentUser['nom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="profile-prenom">Prenom</label>
                        <input id="profile-prenom" name="prenom" type="text" value="<?= htmlspecialchars($currentUser['prenom']) ?>" required>
                    </div>

                    <div class="form-group full">
                        <label for="profile-email">Email</label>
                        <input id="profile-email" name="email" type="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="profile-password">Nouveau mot de passe (optionnel)</label>
                        <input id="profile-password" name="mot_de_passe" type="password" placeholder="Laisser vide pour conserver l actuel">
                    </div>

                    <div class="form-group">
                        <label for="profile-photo">Nouvelle photo (optionnel)</label>
                        <input id="profile-photo" name="photo" type="file" accept="image/*">

                        <div class="profile-photo-preview <?= $photoUrl === null ? 'is-hidden' : '' ?>" id="profilePhotoPreview">
                            <button type="button" class="profile-photo-remove" id="removePhotoPreview" aria-label="Retirer la photo">x</button>
                            <img
                                src="<?= htmlspecialchars($photoUrl ?? '') ?>"
                                alt="Photo de profil de <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>"
                                class="profile-photo-image"
                                id="profilePhotoImage"
                            >
                        </div>
                    </div>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="primary-btn">Mettre a jour</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        (function () {
            var navLinks = document.querySelectorAll('.navbar-links a');
            var photoInput = document.getElementById('profile-photo');
            var photoPreview = document.getElementById('profilePhotoPreview');
            var photoImage = document.getElementById('profilePhotoImage');
            var removePreviewBtn = document.getElementById('removePhotoPreview');
            var originalPhotoSrc = photoImage ? photoImage.getAttribute('src') : '';
            var objectUrl = null;

            navLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    navLinks.forEach(function (item) { item.classList.remove('active'); });
                    link.classList.add('active');
                });
            });

            function showPreview(src) {
                if (!photoPreview || !photoImage) {
                    return;
                }

                photoImage.src = src;
                photoPreview.classList.remove('is-hidden');
            }

            function hidePreview() {
                if (!photoPreview || !photoImage) {
                    return;
                }

                photoImage.removeAttribute('src');
                photoPreview.classList.add('is-hidden');
            }

            function clearTemporaryPreview() {
                if (objectUrl !== null) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
            }

            if (photoInput) {
                photoInput.addEventListener('change', function () {
                    clearTemporaryPreview();

                    if (photoInput.files && photoInput.files[0]) {
                        objectUrl = URL.createObjectURL(photoInput.files[0]);
                        showPreview(objectUrl);
                        return;
                    }

                    if (originalPhotoSrc) {
                        showPreview(originalPhotoSrc);
                    } else {
                        hidePreview();
                    }
                });
            }

            if (removePreviewBtn) {
                removePreviewBtn.addEventListener('click', function () {
                    if (photoInput) {
                        photoInput.value = '';
                    }

                    clearTemporaryPreview();

                    if (originalPhotoSrc) {
                        showPreview(originalPhotoSrc);
                    } else {
                        hidePreview();
                    }
                });
            }
        })();
    </script>
</body>
</html>
