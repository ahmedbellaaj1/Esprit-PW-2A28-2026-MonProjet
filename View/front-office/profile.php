<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Controller/bootstrap.php';
require_once __DIR__ . '/../../Controller/UserRepository.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../Controller/HealthRepository.php';
require_once __DIR__ . '/../../Controller/PreferenceRepository.php';
require_once __DIR__ . '/../../Controller/AllergyRepository.php';
=======
>>>>>>> e2c825fb4e8f094eb8c5d8bde41073ee13565fcd

requireAuth();

$userRepository = new UserRepository();
$profileUser = $userRepository->findById((int) $_SESSION['user']['id']);

if ($profileUser === null) {
    unset($_SESSION['user']);
    setFlash('error', 'Utilisateur introuvable.');
    redirect('/Green-Bite/View/auth.php');
}

$flash = getFlash();
$isAdmin = $profileUser->getRole() === 'admin';
$photoUrl = null;

if (!empty($profileUser->getPhoto())) {
    $photoFile = (string) $profileUser->getPhoto();
    $photoPath = __DIR__ . '/../../uploads/users/' . $photoFile;

    if (is_file($photoPath)) {
        $photoUrl = '/Green-Bite/uploads/users/' . rawurlencode($photoFile);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - GreenBite</title>
    <link rel="stylesheet" href="../style.css">
<<<<<<< HEAD
    <!-- PDF Export Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
=======
>>>>>>> e2c825fb4e8f094eb8c5d8bde41073ee13565fcd
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="profile-page">
        <section class="profile-card card" aria-label="Page profil utilisateur connecte">
            <header class="dashboard-header profile-header rounded-3xl">
                <div>
                    <h1>Mon Profil</h1>
                    <p>Consultez et modifiez les informations de votre compte.</p>
                </div>
                <span class="profile-status">Compte <?= htmlspecialchars($profileUser->getStatut()) ?></span>
            </header>

            <?php if ($flash): ?>
                <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

<<<<<<< HEAD
            <!-- Section Informations de compte - Masquée à la demande de l'utilisateur -->
            <details class="profile-account-details" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <summary style="padding: 1rem; background: #f8fafc; cursor: pointer; font-weight: 600; color: #64748b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>⚙️ Informations du compte (Email, Nom, Photo...)</span>
                    <span style="font-size: 0.8rem; font-weight: normal;">Cliquez pour modifier</span>
                </summary>
                <div style="padding: 1.5rem; border-top: 1px solid #e2e8f0;">
                    <form action="../../Controller/user.php" method="post" enctype="multipart/form-data" autocomplete="on" class="profile-form" style="padding: 0;" novalidate>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-grid">
                            <?php if ($isAdmin): ?>
                                <div class="form-group">
                                    <label for="profile-id">ID</label>
                                    <input id="profile-id" name="id" type="number" value="<?= (int) $profileUser->getId() ?>" readonly>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="profile-role">Role</label>
                                <input id="profile-role" name="role" type="text" value="<?= htmlspecialchars($profileUser->getRole()) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="profile-nom">Nom</label>
                                <input id="profile-nom" name="nom" type="text" value="<?= htmlspecialchars($profileUser->getNom()) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="profile-prenom">Prenom</label>
                                <input id="profile-prenom" name="prenom" type="text" value="<?= htmlspecialchars($profileUser->getPrenom()) ?>" required>
                            </div>

                            <div class="form-group full">
                                <label for="profile-email">Email</label>
                                <input id="profile-email" name="email" type="email" value="<?= htmlspecialchars($profileUser->getEmail()) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="profile-photo">Nouvelle photo (optionnel)</label>
                                <input id="profile-photo" name="photo" type="file" accept="image/*">

                                <div class="profile-photo-preview <?= $photoUrl === null ? 'is-hidden' : '' ?>" id="profilePhotoPreview">
                                    <button type="button" class="profile-photo-remove" id="removePhotoPreview" aria-label="Retirer la photo">x</button>
                                    <img
                                        src="<?= htmlspecialchars($photoUrl ?? '') ?>"
                                            alt="Photo de profil de <?= htmlspecialchars($profileUser->getPrenom() . ' ' . $profileUser->getNom()) ?>"
                                        class="profile-photo-image"
                                        id="profilePhotoImage"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="auth-actions" style="margin-top: 1rem;">
                            <button type="submit" class="primary-btn">Mettre a jour le profil</button>
                        </div>
                    </form>
                </div>
            </details>

            <?php
            $healthRepository = new HealthRepository();
            $userHealth = $healthRepository->findByUserId((int) $profileUser->getId()) ?? new Health((int) $profileUser->getId());

            // Charger les listes officielles
            $preferenceRepo = new PreferenceRepository();
            $allergyRepo = new AllergyRepository();
            $allPrefs = $preferenceRepo->getAll();
            $allAllergies = $allergyRepo->getAll();

            // Récupérer les conseils personnalisés
            $prefAdvice = $userHealth->getPreferenceAlimentaire() ? $healthRepository->getAdvice('preference', $userHealth->getPreferenceAlimentaire()) : null;
            $allergyAdvice = $userHealth->getAllergies() ? $healthRepository->getAdvice('allergy', $userHealth->getAllergies()) : null;
            ?>

            <style>
                .health-section-modern {
                    background: #ffffff;
                    border-radius: 24px;
                    padding: 2.5rem;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                    margin-top: 3rem;
                    border: 1px solid #f1f5f9;
                }

                .health-search-container {
                    margin-bottom: 2.5rem;
                    max-width: 600px;
                }

                .health-search-wrapper {
                    display: flex;
                    align-items: center;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 16px;
                    padding: 0.5rem 1.25rem;
                    transition: all 0.3s ease;
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
                }

                .health-search-wrapper:focus-within {
                    border-color: #10b981;
                    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
                    background: white;
                }

                .health-search-wrapper input {
                    border: none !important;
                    background: transparent !important;
                    padding: 0.75rem !important;
                    font-size: 1rem !important;
                    width: 100%;
                    outline: none !important;
                    box-shadow: none !important;
                    color: #1e293b;
                }

                .health-search-icon {
                    color: #94a3b8;
                    font-size: 1.2rem;
                    margin-right: 0.5rem;
                }

                .health-grid-modern {
                    display: grid;
                    grid-template-columns: 1.2fr 0.8fr;
                    gap: 3.5rem;
                }

                .form-group-modern {
                    margin-bottom: 1.75rem;
                }

                .form-group-modern label {
                    display: flex;
                    align-items: center;
                    gap: 0.6rem;
                    margin-bottom: 0.6rem;
                    font-weight: 700;
                    color: #334155;
                    font-size: 0.95rem;
                }

                .modern-select, .modern-input {
                    width: 100%;
                    padding: 0.9rem 1.1rem;
                    border: 1.5px solid #e2e8f0;
                    border-radius: 14px;
                    font-size: 1rem;
                    color: #1e293b;
                    background: #f8fafc;
                    transition: all 0.2s ease;
                    font-family: inherit;
                    -webkit-appearance: none;
                    appearance: none;
                }

                .modern-select {
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: right 1rem center;
                    background-size: 1.25rem;
                    padding-right: 2.5rem;
                }

                .modern-select:focus, .modern-input:focus {
                    border-color: #10b981;
                    background: white;
                    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
                    outline: none;
                }

                .advice-panel-modern {
                    display: flex;
                    flex-direction: column;
                    gap: 1.5rem;
                }

                .advice-card-modern {
                    border-radius: 24px;
                    padding: 1.75rem;
                    position: relative;
                    overflow: hidden;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                }

                .advice-card-modern:hover {
                    transform: translateY(-6px);
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                }

                .advice-card-modern.preference {
                    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                    border: 1px solid #bbf7d0;
                }

                .advice-card-modern.allergy {
                    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                    border: 1px solid #fecaca;
                }

                .advice-card-modern h4 {
                    margin: 0 0 0.85rem 0;
                    font-size: 1.15rem;
                    font-weight: 800;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                }

                .advice-card-modern.preference h4 { color: #166534; }
                .advice-card-modern.allergy h4 { color: #991b1b; }

                .advice-card-modern p {
                    margin: 0;
                    font-size: 0.95rem;
                    line-height: 1.7;
                    color: #374151;
                }

                .advice-empty {
                    padding: 2.5rem;
                    background: #f8fafc;
                    border: 2px dashed #e2e8f0;
                    border-radius: 24px;
                    text-align: center;
                    color: #64748b;
                    font-style: italic;
                }

                @media (max-width: 968px) {
                    .health-grid-modern {
                        grid-template-columns: 1fr;
                        gap: 2.5rem;
                    }
                }
            </style>

            <div id="health-section" class="health-section-modern">
                <header style="margin-bottom: 2.5rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <span style="background: #dcfce7; padding: 0.5rem; border-radius: 12px;">🥗</span> Mes Informations Santé
                    </h2>
                    <p style="color: #64748b; font-size: 0.95rem;">Personnalisez votre expérience GreenBite selon vos besoins nutritionnels.</p>
                </header>

                <div class="health-search-container">
                    <div class="health-search-wrapper">
                        <span class="health-search-icon">🔍</span>
                        <input type="text" id="healthSearchInput" placeholder="Rechercher une préférence ou allergie (ex: végétarien, keto...)" onkeyup="if(event.key === 'Enter') triggerHealthSearch()">
                        <button type="button" class="primary-btn" onclick="triggerHealthSearch()" style="padding: 0.6rem 1.5rem; font-size: 0.9rem; margin-left: 0.5rem; border-radius: 12px;">Rechercher</button>
                    </div>
                    
                    <div id="healthSearchResults" style="display: none; margin-top: 1.5rem; padding: 1.5rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 20px;">
                        <h4 style="margin-top: 0; margin-bottom: 1rem; color: #0369a1; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>✨</span> Résultats pour votre recherche :
                        </h4>
                        <div id="resultsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                            <!-- Les résultats seront injectés ici -->
                        </div>
                        <button type="button" onclick="this.parentElement.style.display='none'" style="margin-top: 1rem; background: transparent; border: none; color: #0369a1; cursor: pointer; font-size: 0.85rem; font-weight: 600; text-decoration: underline;">Fermer les résultats</button>
                    </div>
                </div>

                <div class="health-grid-modern">
                    <form action="../../Controller/health.php" method="post" class="profile-form" style="padding: 0;">
                        <input type="hidden" name="action" value="update_health">
                        
                        <div class="form-group-modern">
                            <label for="profile-preference"><span>🥦</span> Préférence alimentaire</label>
                            <select id="profile-preference" name="preference_alimentaire" class="modern-select">
                                <option value="" <?= empty($userHealth->getPreferenceAlimentaire()) ? 'selected' : '' ?>>Aucune préférence</option>
                                <?php foreach ($allPrefs as $p): ?>
                                    <option value="<?= htmlspecialchars($p->getNom()) ?>" <?= $userHealth->getPreferenceAlimentaire() === $p->getNom() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p->getNom()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-modern">
                            <label for="profile-allergies"><span>⚠️</span> Type d'allergie principale</label>
                            <select id="profile-allergies" name="allergies" class="modern-select">
                                <option value="" <?= empty($userHealth->getAllergies()) ? 'selected' : '' ?>>Aucune allergie</option>
                                <?php foreach ($allAllergies as $a): ?>
                                    <option value="<?= htmlspecialchars($a->getNom()) ?>" <?= $userHealth->getAllergies() === $a->getNom() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a->getNom()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group-modern">
                                <label for="profile-poids"><span>⚖️</span> Poids (kg)</label>
                                <input id="profile-poids" name="poids" type="number" step="0.1" class="modern-input" value="<?= $userHealth->getPoids() ?>" placeholder="ex: 70.5" oninput="calculateNutrition()">
                            </div>

                            <div class="form-group-modern">
                                <label for="profile-taille"><span>📏</span> Taille (cm)</label>
                                <input id="profile-taille" name="taille" type="number" class="modern-input" value="<?= $userHealth->getTaille() ?>" placeholder="ex: 175" oninput="calculateNutrition()">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group-modern">
                                <label for="profile-age"><span>🎂</span> Âge</label>
                                <input id="profile-age" name="age" type="number" class="modern-input" value="<?= $userHealth->getAge() ?>" placeholder="ex: 25" oninput="calculateNutrition()">
                            </div>

                            <div class="form-group-modern">
                                <label for="profile-sexe"><span>🚻</span> Sexe</label>
                                <select id="profile-sexe" name="sexe" class="modern-select" onchange="calculateNutrition()">
                                    <option value="" <?= empty($userHealth->getSexe()) ? 'selected' : '' ?>>Non précisé</option>
                                    <option value="M" <?= $userHealth->getSexe() === 'M' ? 'selected' : '' ?>>Homme</option>
                                    <option value="F" <?= $userHealth->getSexe() === 'F' ? 'selected' : '' ?>>Femme</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="primary-btn" style="background: #10b981; border: none; width: 100%; padding: 1rem; border-radius: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                                Enregistrer mon profil santé
                            </button>
                        </div>
                    </form>

                    <div class="advice-panel-modern" id="advicePanel">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0; color: #1e293b; font-size: 1.25rem; font-weight: 800; display: flex; align-items: center; gap: 0.6rem;">
                                <span>💡</span> Conseils & Bilan
                            </h3>
                            <button type="button" onclick="exportToPDF()" class="secondary-btn" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; color: #475569; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
                                <span>📄</span> Exporter PDF
                            </button>
                        </div>

                        <!-- Card Bilan Nutritionnel -->
                        <div id="nutritionBilanCard" class="advice-card-modern" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; margin-bottom: 1.5rem;">
                            <h4 style="color: #1e40af;">📊 Bilan Nutritionnel</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                <div style="text-align: center; padding: 1rem; background: rgba(255,255,255,0.5); border-radius: 16px;">
                                    <div style="font-size: 0.75rem; color: #60a5fa; font-weight: 700; text-transform: uppercase;">Votre IMC</div>
                                    <div id="imcValue" style="font-size: 1.5rem; font-weight: 800; color: #1e40af;">--</div>
                                    <div id="imcStatus" style="font-size: 0.75rem; font-weight: 600; margin-top: 0.2rem;">--</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: rgba(255,255,255,0.5); border-radius: 16px;">
                                    <div style="font-size: 0.75rem; color: #60a5fa; font-weight: 700; text-transform: uppercase;">Besoin Journalier</div>
                                    <div id="bmrValue" style="font-size: 1.5rem; font-weight: 800; color: #1e40af;">--</div>
                                    <div style="font-size: 0.75rem; font-weight: 600; margin-top: 0.2rem;">kcal / jour</div>
                                </div>
                            </div>
                            <p style="margin-top: 1rem; font-size: 0.85rem; color: #3b82f6; font-style: italic;">Calculé selon la formule de Harris-Benedict.</p>
                        </div>
                        
                        <div id="adviceEmptyState" class="advice-empty" style="<?= ($prefAdvice || $allergyAdvice) ? 'display: none;' : '' ?>">
                            <p>Sélectionnez une préférence ou une allergie pour recevoir des conseils personnalisés.</p>
                        </div>

                        <div id="prefAdviceCard" class="advice-card-modern preference" style="<?= !$prefAdvice ? 'display: none;' : '' ?>">
                            <h4 id="prefAdviceTitle">🥗 Régime <?= htmlspecialchars($userHealth->getPreferenceAlimentaire() ?? '') ?></h4>
                            <p id="prefAdviceText"><?= htmlspecialchars($prefAdvice ?? '') ?></p>
                        </div>

                        <div id="allergyAdviceCard" class="advice-card-modern allergy" style="<?= !$allergyAdvice ? 'display: none;' : '' ?>">
                            <h4 id="allergyAdviceTitle">🚫 Allergie : <?= htmlspecialchars($userHealth->getAllergies() ?? '') ?></h4>
                            <p id="allergyAdviceText"><?= htmlspecialchars($allergyAdvice ?? '') ?></p>
                            <p style="margin-top: 1rem; font-size: 0.8rem; opacity: 0.8; font-weight: 600;">L'IA de GreenBite filtrera automatiquement les produits pour vous.</p>
                        </div>
                    </div>
                </div>
            </div>

=======
            <form action="../../Controller/user.php" method="post" enctype="multipart/form-data" autocomplete="on" class="profile-form" novalidate>
                <input type="hidden" name="action" value="update_profile">

                <div class="form-grid">
                    <?php if ($isAdmin): ?>
                        <div class="form-group">
                            <label for="profile-id">ID</label>
                            <input id="profile-id" name="id" type="number" value="<?= (int) $profileUser->getId() ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="profile-role">Role</label>
                        <input id="profile-role" name="role" type="text" value="<?= htmlspecialchars($profileUser->getRole()) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="profile-nom">Nom</label>
                        <input id="profile-nom" name="nom" type="text" value="<?= htmlspecialchars($profileUser->getNom()) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="profile-prenom">Prenom</label>
                        <input id="profile-prenom" name="prenom" type="text" value="<?= htmlspecialchars($profileUser->getPrenom()) ?>" required>
                    </div>

                    <div class="form-group full">
                        <label for="profile-email">Email</label>
                        <input id="profile-email" name="email" type="email" value="<?= htmlspecialchars($profileUser->getEmail()) ?>" required>
                    </div>

                    <div class="form-group">

                        <label for="profile-photo">Nouvelle photo (optionnel)</label>
                        <input id="profile-photo" name="photo" type="file" accept="image/*">

                        <div class="profile-photo-preview <?= $photoUrl === null ? 'is-hidden' : '' ?>" id="profilePhotoPreview">
                            <button type="button" class="profile-photo-remove" id="removePhotoPreview" aria-label="Retirer la photo">x</button>
                            <img
                                src="<?= htmlspecialchars($photoUrl ?? '') ?>"
                                    alt="Photo de profil de <?= htmlspecialchars($profileUser->getPrenom() . ' ' . $profileUser->getNom()) ?>"
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
>>>>>>> e2c825fb4e8f094eb8c5d8bde41073ee13565fcd
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
<<<<<<< HEAD

            // Health Section Filtering
            window.filterHealthFields = function() {
                const query = document.getElementById('healthSearchInput').value.toLowerCase();
                
                // Filter Preference Select
                const prefSelect = document.getElementById('profile-preference');
                if (prefSelect) {
                    Array.from(prefSelect.options).forEach(option => {
                        if (option.value === "") return; 
                        const text = option.text.toLowerCase();
                        option.style.display = text.includes(query) ? "" : "none";
                    });
                }

                // Filter Allergy Select
                const allergySelect = document.getElementById('profile-allergies');
                if (allergySelect) {
                    Array.from(allergySelect.options).forEach(option => {
                        if (option.value === "") return;
                        const text = option.text.toLowerCase();
                        option.style.display = text.includes(query) ? "" : "none";
                    });
                }
            };

            // Dynamic Advice Update
            const prefAdviceData = <?= json_encode(array_reduce($allPrefs, function($acc, $p) { $acc[$p->getNom()] = $p->getDescription(); return $acc; }, [])) ?>;
            const allergyAdviceData = <?= json_encode(array_reduce($allAllergies, function($acc, $a) { $acc[$a->getNom()] = $a->getDescription(); return $acc; }, [])) ?>;

            function updateAdviceDisplay() {
                const selectedPref = document.getElementById('profile-preference').value;
                const selectedAllergy = document.getElementById('profile-allergies').value;

                const prefCard = document.getElementById('prefAdviceCard');
                const allergyCard = document.getElementById('allergyAdviceCard');
                const emptyState = document.getElementById('adviceEmptyState');

                let hasAdvice = false;

                if (selectedPref && prefAdviceData[selectedPref]) {
                    document.getElementById('prefAdviceTitle').textContent = '🥗 Régime ' + selectedPref;
                    document.getElementById('prefAdviceText').textContent = prefAdviceData[selectedPref];
                    prefCard.style.display = 'block';
                    hasAdvice = true;
                } else {
                    prefCard.style.display = 'none';
                }

                if (selectedAllergy && allergyAdviceData[selectedAllergy]) {
                    document.getElementById('allergyAdviceTitle').textContent = '🚫 Allergie : ' + selectedAllergy;
                    document.getElementById('allergyAdviceText').textContent = allergyAdviceData[selectedAllergy];
                    allergyCard.style.display = 'block';
                    hasAdvice = true;
                } else {
                    allergyCard.style.display = 'none';
                }

                emptyState.style.display = hasAdvice ? 'none' : 'block';
            }

            // Search and Display Examples/Compatible diets
            window.triggerHealthSearch = function() {
                const query = document.getElementById('healthSearchInput').value.trim().toLowerCase();
                const resultsContainer = document.getElementById('healthSearchResults');
                const resultsGrid = document.getElementById('resultsGrid');
                
                if (!query) {
                    resultsContainer.style.display = 'none';
                    return;
                }

                resultsGrid.innerHTML = '';
                let found = false;

                // Search in Preferences
                Object.entries(prefAdviceData).forEach(([name, desc]) => {
                    if (name.toLowerCase().includes(query) || desc.toLowerCase().includes(query)) {
                        addResultCard(name, desc, 'preference');
                        found = true;
                    }
                });

                // Search in Allergies
                Object.entries(allergyAdviceData).forEach(([name, desc]) => {
                    if (name.toLowerCase().includes(query) || desc.toLowerCase().includes(query)) {
                        addResultCard(name, desc, 'allergy');
                        found = true;
                    }
                });

                if (found) {
                    resultsContainer.style.display = 'block';
                    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    resultsGrid.innerHTML = '<p style="color: #64748b; grid-column: 1/-1;">Aucun régime ou allergie correspondant trouvé. Essayez un autre mot-clé.</p>';
                    resultsContainer.style.display = 'block';
                }
            };

            function addResultCard(name, desc, type) {
                const grid = document.getElementById('resultsGrid');
                const card = document.createElement('div');
                card.style.cssText = 'background: white; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;';
                card.onmouseover = () => { card.style.borderColor = '#10b981'; card.style.transform = 'translateY(-2px)'; };
                card.onmouseout = () => { card.style.borderColor = '#e2e8f0'; card.style.transform = 'translateY(0)'; };
                
                const emoji = type === 'preference' ? '🥗' : '🚫';
                card.innerHTML = `
                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>${emoji}</span> ${name}
                    </div>
                    <div style="font-size: 0.8rem; color: #64748b; line-height: 1.4;">${desc.substring(0, 60)}${desc.length > 60 ? '...' : ''}</div>
                    <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #10b981; font-weight: 700;">+ Sélectionner</div>
                `;
                
                card.onclick = () => {
                    const selectId = type === 'preference' ? 'profile-preference' : 'profile-allergies';
                    const select = document.getElementById(selectId);
                    select.value = name;
                    updateAdviceDisplay();
                    document.getElementById('healthSearchResults').style.display = 'none';
                    select.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    select.style.borderColor = '#10b981';
                    setTimeout(() => select.style.borderColor = '', 2000);
                };
                
                grid.appendChild(card);
            }

            document.getElementById('profile-preference').addEventListener('change', updateAdviceDisplay);
            document.getElementById('profile-allergies').addEventListener('change', updateAdviceDisplay);

            // Nutritional Calculations (IMC & BMR)
            window.calculateNutrition = function() {
                const weight = parseFloat(document.getElementById('profile-poids').value);
                const height = parseFloat(document.getElementById('profile-taille').value);
                const age = parseInt(document.getElementById('profile-age').value);
                const gender = document.getElementById('profile-sexe').value;

                const imcValueEl = document.getElementById('imcValue');
                const imcStatusEl = document.getElementById('imcStatus');
                const bmrValueEl = document.getElementById('bmrValue');

                // IMC Calculation
                if (weight > 0 && height > 0) {
                    const imc = weight / ((height / 100) ** 2);
                    imcValueEl.textContent = imc.toFixed(1);
                    
                    let status = "";
                    let color = "";
                    if (imc < 18.5) { status = "Insuffisance pondérale"; color = "#60a5fa"; }
                    else if (imc < 25) { status = "Poids normal"; color = "#10b981"; }
                    else if (imc < 30) { status = "Surpoids"; color = "#f59e0b"; }
                    else { status = "Obésité"; color = "#ef4444"; }
                    
                    imcStatusEl.textContent = status;
                    imcStatusEl.style.color = color;
                } else {
                    imcValueEl.textContent = "--";
                    imcStatusEl.textContent = "--";
                }

                // BMR Calculation (Harris-Benedict)
                if (weight > 0 && height > 0 && age > 0 && gender) {
                    let bmr = 0;
                    if (gender === 'M') {
                        bmr = 88.362 + (13.397 * weight) + (4.799 * height) - (5.677 * age);
                    } else {
                        bmr = 447.593 + (9.247 * weight) + (3.098 * height) - (4.330 * age);
                    }
                    bmrValueEl.textContent = Math.round(bmr);
                } else {
                    bmrValueEl.textContent = "--";
                }
            };

            // PDF Export Function
            window.exportToPDF = function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                const userName = "<?= htmlspecialchars($profileUser->getPrenom() . ' ' . $profileUser->getNom()) ?>";
                const date = new Date().toLocaleDateString('fr-FR');

                // Style & Branding
                doc.setFillColor(16, 185, 129); // GreenBite primary color
                doc.rect(0, 0, 210, 40, 'F');
                
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(24);
                doc.text("GreenBite - Rapport Santé", 20, 25);
                
                doc.setTextColor(51, 65, 85);
                doc.setFontSize(12);
                doc.text(`Utilisateur: ${userName}`, 20, 55);
                doc.text(`Date du rapport: ${date}`, 20, 62);
                
                doc.setDrawColor(226, 232, 240);
                doc.line(20, 68, 190, 68);

                // Section 1: Profil Physique
                doc.setFontSize(16);
                doc.setTextColor(15, 23, 42);
                doc.text("1. Profil Physique", 20, 80);
                
                const weight = document.getElementById('profile-poids').value || "--";
                const height = document.getElementById('profile-taille').value || "--";
                const age = document.getElementById('profile-age').value || "--";
                const gender = document.getElementById('profile-sexe').options[document.getElementById('profile-sexe').selectedIndex].text;
                
                doc.setFontSize(11);
                doc.text(`• Poids: ${weight} kg`, 25, 90);
                doc.text(`• Taille: ${height} cm`, 25, 97);
                doc.text(`• Âge: ${age} ans`, 25, 104);
                doc.text(`• Sexe: ${gender}`, 25, 111);

                // Section 2: Bilan Nutritionnel
                doc.setFontSize(16);
                doc.text("2. Bilan Nutritionnel", 20, 125);
                
                const imc = document.getElementById('imcValue').textContent;
                const imcStatus = document.getElementById('imcStatus').textContent;
                const bmr = document.getElementById('bmrValue').textContent;
                
                doc.setFontSize(11);
                doc.text(`• Indice de Masse Corporelle (IMC): ${imc} (${imcStatus})`, 25, 135);
                doc.text(`• Besoin Calorique Journalier (BMR): ${bmr} kcal / jour`, 25, 142);

                // Section 3: Recommandations
                doc.setFontSize(16);
                doc.text("3. Recommandations Personnalisées", 20, 157);
                
                const pref = document.getElementById('profile-preference').value || "Aucune";
                const allergy = document.getElementById('profile-allergies').value || "Aucune";
                
                doc.setFontSize(11);
                doc.text(`• Régime suivi: ${pref}`, 25, 167);
                doc.text(`• Allergie déclarée: ${allergy}`, 25, 174);
                
                // Advice Text (Multilined)
                const prefAdvice = document.getElementById('prefAdviceText').textContent;
                if (prefAdvice && prefAdvice !== "") {
                    doc.setFontSize(10);
                    doc.setTextColor(100, 116, 139);
                    const splitAdvice = doc.splitTextToSize(`Conseil Régime: ${prefAdvice}`, 160);
                    doc.text(splitAdvice, 25, 184);
                }

                // Footer
                doc.setFontSize(9);
                doc.setTextColor(148, 163, 184);
                doc.text("Ce rapport est généré par l'IA de GreenBite pour votre information personnelle.", 20, 280);
                doc.text("https://greenbite.com", 160, 280);

                // Download
                doc.save(`Rapport_Sante_GreenBite_${userName.replace(' ', '_')}.pdf`);
            };

            // Initial calculation
            calculateNutrition();
        })();
    </script>
    <script src="../assets/cart.js"></script>
    <?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>
=======
        })();
    </script>
>>>>>>> e2c825fb4e8f094eb8c5d8bde41073ee13565fcd
</body>
</html>
