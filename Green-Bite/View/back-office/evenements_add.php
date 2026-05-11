<?php
/**
 * Green-Bite Back-Office - Ajouter un Événement
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/EvenementController.php';
require_once __DIR__ . '/../../Model/Evenement.php';

$controller  = new EvenementController();
$organisateurs = $controller->getAllOrganisateurs();
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre          = trim($_POST['titre'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $date_event     = trim($_POST['date_event'] ?? '');
    $lieu           = trim($_POST['lieu'] ?? '');
    $type           = trim($_POST['type'] ?? '');
    $organisateur_id= (int)($_POST['organisateur_id'] ?? 0);

    try {
        $event = new Evenement();
        $event->setTitre($titre);
        $event->setDescription($description);
        $event->setDate($date_event);
        $event->setLieu($lieu);
        $event->setType($type);
        $event->setOrganisateurId($organisateur_id);

        $result = $controller->addEvenement($event);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
            header('Location: /Green-Bite/View/back-office/evenements.php');
            exit();
        } else {
            $errors[] = $result['message'];
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
$activePage = 'evenements';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Événement - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-form-card { background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; }
        .ev-field { margin-bottom: 1.25rem; }
        .ev-field label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .ev-field input, .ev-field textarea, .ev-field select {
            width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif; transition: border-color 0.2s; background: white;
        }
        .ev-field input:focus, .ev-field textarea:focus, .ev-field select:focus { outline: none; border-color: #14b8a6; }
        .ev-field textarea { min-height: 120px; resize: vertical; }
        .ev-error-list { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #dc2626; }
        .ev-form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
    <div class="dashboard-main"><div class="page-content">
        <div class="page-header">
            <h1>➕ Ajouter un Événement</h1>
            <p>Créez un nouvel événement communautaire</p>
        </div>

        <div class="ev-form-card">
            <?php if (!empty($errors)): ?>
                <div class="ev-error-list">
                    <strong>❌ Erreurs :</strong><br>
                    <?php foreach ($errors as $e): ?><span>• <?= htmlspecialchars($e) ?></span><br><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="ev-field">
                    <label for="titre">📌 Titre *</label>
                    <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" placeholder="Ex: Atelier cuisine végane">
                </div>
                <div class="ev-field">
                    <label for="description">📝 Description *</label>
                    <textarea id="description" name="description" placeholder="Décrivez l'événement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="ev-field">
                        <label for="date_event">📅 Date *</label>
                        <input type="date" id="date_event" name="date_event" value="<?= htmlspecialchars($_POST['date_event'] ?? '') ?>">
                    </div>
                    <div class="ev-field">
                        <label for="lieu">📍 Lieu *</label>
                        <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" placeholder="Ex: Tunis">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="ev-field">
                        <label for="type">🏷️ Type *</label>
                        <select id="type" name="type">
                            <option value="">Sélectionnez un type</option>
                            <?php foreach(['Atelier','Conférence','Festival','Autre'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($_POST['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ev-field">
                        <label for="organisateur_id">👥 Organisateur *</label>
                        <select id="organisateur_id" name="organisateur_id">
                            <option value="">Sélectionnez un organisateur</option>
                            <?php foreach($organisateurs as $org): ?>
                                <option value="<?= $org['id'] ?>" <?= ($_POST['organisateur_id'] ?? 0) == $org['id'] ? 'selected' : '' ?>><?= htmlspecialchars($org['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ev-form-actions">
                    <button type="submit" class="primary-btn">💾 Enregistrer l'événement</button>
                    <a href="/Green-Bite/View/back-office/evenements.php" class="secondary-btn">← Annuler</a>
                </div>
            </form>
        </div>
    </div></div>\n</div>
</body>
</html>
