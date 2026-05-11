<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../../Controller/OrganisateurController.php';
require_once __DIR__ . '/../../Model/Organisateur.php';

$controller = new OrganisateurController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /Green-Bite/View/back-office/organisateurs_ev.php'); exit(); }
$orgData = $controller->getOrganisateurById($id);
if (!$orgData) { header('Location: /Green-Bite/View/back-office/organisateurs_ev.php'); exit(); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $org = new Organisateur(
            trim($_POST['nom'] ?? ''), trim($_POST['email'] ?? ''),
            trim($_POST['telephone'] ?? ''), trim($_POST['adresse'] ?? ''), trim($_POST['site_web'] ?? '')
        );
        $result = $controller->updateOrganisateur($org, $id);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
            header('Location: /Green-Bite/View/back-office/organisateurs_ev.php');
            exit();
        } else { $errors[] = $result['message']; }
    } catch (Exception $e) { $errors[] = $e->getMessage(); }
}
$formData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $orgData;
$activePage = 'organisateurs';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Organisateur - GreenBite Admin</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-form-card { background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; }
        .ev-field { margin-bottom: 1.25rem; }
        .ev-field label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .ev-field input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.9rem; font-family: 'Inter', sans-serif; transition: border-color 0.2s; }
        .ev-field input:focus { outline: none; border-color: #14b8a6; }
        .ev-error-list { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #dc2626; }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
    <div class="dashboard-main"><div class="page-content">
        <div class="page-header">
            <h1>✏️ Modifier l'Organisateur</h1>
            <p><?= htmlspecialchars($orgData['nom']) ?></p>
        </div>
        <div class="ev-form-card">
            <?php if (!empty($errors)): ?>
                <div class="ev-error-list"><?php foreach ($errors as $e): ?>• <?= htmlspecialchars($e) ?><br><?php endforeach; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="ev-field"><label>👤 Nom *</label><input type="text" name="nom" value="<?= htmlspecialchars($formData['nom'] ?? '') ?>"></div>
                <div class="ev-field"><label>📧 Email *</label><input type="text" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>"></div>
                <div class="ev-field"><label>📞 Téléphone *</label><input type="text" name="telephone" value="<?= htmlspecialchars($formData['telephone'] ?? '') ?>"></div>
                <div class="ev-field"><label>📍 Adresse</label><input type="text" name="adresse" value="<?= htmlspecialchars($formData['adresse'] ?? '') ?>"></div>
                <div class="ev-field"><label>🌐 Site Web</label><input type="text" name="site_web" value="<?= htmlspecialchars($formData['site_web'] ?? '') ?>"></div>
                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit" class="primary-btn">💾 Sauvegarder</button>
                    <a href="/Green-Bite/View/back-office/organisateurs_ev.php" class="secondary-btn">← Annuler</a>
                </div>
            </form>
        </div>
    </div></div>\n</div>
</body>
</html>
