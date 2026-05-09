<?php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../model/Evenement.php";

$controller = new EvenementController();
$organisateurs = $controller->getAllOrganisateurs();

$error = '';
$success = '';
$formData = ['titre' => '', 'description' => '', 'date' => '', 'lieu' => '', 'type' => '', 'organisateur_id' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $formData = [
        'titre' => trim($_POST['titre'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'lieu' => trim($_POST['lieu'] ?? ''),
        'type' => trim($_POST['type'] ?? ''),
        'organisateur_id' => trim($_POST['organisateur_id'] ?? '')
    ];
    
    // ==================== VALIDATIONS PHP UNIQUEMENT ====================
    
    // 1. Validation du titre
    if (empty($formData['titre'])) {
        $errors['titre'] = "Le titre est obligatoire";
    } elseif (strlen($formData['titre']) < 3) {
        $errors['titre'] = "Le titre doit contenir au moins 3 caractères";
    } elseif (strlen($formData['titre']) > 100) {
        $errors['titre'] = "Le titre ne peut pas dépasser 100 caractères";
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-\'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]+$/', $formData['titre'])) {
        $errors['titre'] = "Le titre contient des caractères non autorisés";
    }
    
    // 2. Validation de la description
    if (empty($formData['description'])) {
        $errors['description'] = "La description est obligatoire";
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = "La description doit contenir au moins 10 caractères";
    } elseif (strlen($formData['description']) > 5000) {
        $errors['description'] = "La description ne peut pas dépasser 5000 caractères";
    }
    
    // 3. Validation de la date
    if (empty($formData['date'])) {
        $errors['date'] = "La date est obligatoire";
    } else {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['date'])) {
            $errors['date'] = "Format de date invalide. Utilisez le format AAAA-MM-JJ (ex: 2025-12-31)";
        } else {
            $dateObj = DateTime::createFromFormat('Y-m-d', $formData['date']);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $formData['date']) {
                $errors['date'] = "Date invalide. Vérifiez que le jour et le mois sont corrects";
            } else {
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                if ($dateObj < $today) {
                    $errors['date'] = "La date ne peut pas être dans le passé. Choisissez une date à partir d'aujourd'hui";
                }
                $maxDate = (new DateTime())->modify('+5 years');
                if ($dateObj > $maxDate) {
                    $errors['date'] = "La date ne peut pas dépasser 5 ans dans le futur";
                }
            }
        }
    }
    
    // 4. Validation du lieu
    if (empty($formData['lieu'])) {
        $errors['lieu'] = "Le lieu est obligatoire";
    } elseif (strlen($formData['lieu']) < 2) {
        $errors['lieu'] = "Le lieu doit contenir au moins 2 caractères";
    } elseif (strlen($formData['lieu']) > 100) {
        $errors['lieu'] = "Le lieu ne peut pas dépasser 100 caractères";
    }
    
    // 5. Validation du type
    $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
    if (empty($formData['type'])) {
        $errors['type'] = "Le type est obligatoire";
    } elseif (!in_array($formData['type'], $validTypes)) {
        $errors['type'] = "Type d'événement invalide. Choisissez parmi: " . implode(', ', $validTypes);
    }
    
    // 6. Validation de l'organisateur
    if (empty($formData['organisateur_id'])) {
        $errors['organisateur_id'] = "L'organisateur est obligatoire";
    } elseif (!is_numeric($formData['organisateur_id']) || $formData['organisateur_id'] <= 0) {
        $errors['organisateur_id'] = "Veuillez sélectionner un organisateur valide";
    } else {
        $organisateurExists = false;
        foreach ($organisateurs as $org) {
            if ($org['id'] == $formData['organisateur_id']) {
                $organisateurExists = true;
                break;
            }
        }
        if (!$organisateurExists) {
            $errors['organisateur_id'] = "L'organisateur sélectionné n'existe pas";
        }
    }
    
    // ==================== TRAITEMENT SI PAS D'ERREURS ====================
    if (empty($errors)) {
        try {
            $event = new Evenement(
                $formData['titre'],
                $formData['description'],
                $formData['date'],
                $formData['lieu'],
                $formData['type'],
                $formData['organisateur_id']
            );
            
            $result = $controller->addEvenement($event);
            
            if ($result['success']) {
                $_SESSION['message'] = $result['message'];
                header('Location: dashboardEvenement.php');
                exit();
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = '<ul style="margin:0; padding-left:1.5rem;">';
        foreach ($errors as $field => $message) {
            $error .= '<li><strong>' . htmlspecialchars($field) . '</strong>: ' . htmlspecialchars($message) . '</li>';
        }
        $error .= '</ul>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un événement - GreenBite Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
        }
        .dashboard-container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f766e 0%, #0c5f58 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        
        .sidebar-logo {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        
        .sidebar-logo-text h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            margin: 0;
            line-height: 1.2;
        }
        
        .sidebar-logo-text span {
            color: #99f6e4;
        }
        
        .sidebar-logo-text p {
            font-size: 0.7rem;
            opacity: 0.7;
            margin: 0;
            margin-top: 2px;
        }
        
        .sidebar-logo-img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
        }
        
        .sidebar-nav { padding: 0 1rem; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        .sidebar-link .icon { font-size: 1.2rem; width: 28px; }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; }
        .page-header p { color: #64748b; font-size: 0.95rem; }
        
        /* Alert */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Form Container */
        .form-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 118, 110, 0.15);
            overflow: hidden;
        }
        .form-header {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            padding: 2rem;
            color: white;
        }
        .form-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .form-header p { opacity: 0.9; font-size: 0.95rem; }
        .form-body { padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .form-group label .required { color: #dc2626; margin-left: 0.25rem; }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .form-group small {
            display: block;
            margin-top: 0.4rem;
            font-size: 0.75rem;
            color: #64748b;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc2626;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .btn-submit {
            background: #0f766e;
            color: white;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-submit:hover {
            background: #0c5f58;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        }
        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-cancel:hover {
            background: #e2e8f0;
            color: #475569;
            transform: translateY(-2px);
        }
        .field-error {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; margin-top: 4rem; }
            .form-header { padding: 1.5rem; }
            .form-header h1 { font-size: 1.4rem; }
            .form-body { padding: 1.5rem; }
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-wrapper">
                    <div class="sidebar-logo-text">
                        <h2>Green<span>Bite</span></h2>
                        <p>Administration</p>
                    </div>
                    <img src="../../assets/images/logo.png" alt="GreenBite" class="sidebar-logo-img">
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboardEvenement.php" class="sidebar-link">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="stats.php" class="sidebar-link">
                    <span class="icon">📈</span>
                    <span>Statistiques</span>
                </a>
                <a href="participants.php" class="sidebar-link">
                    <span class="icon">👥</span>
                    <span>Participants</span>
                </a>
                <a href="organisateurs.php" class="sidebar-link">
                    <span class="icon">👥</span>
                    <span>Organisateurs</span>
                </a>
                <a href="addEvenement.php" class="sidebar-link active">
                    <span class="icon">➕</span>
                    <span>Ajouter un événement</span>
                </a>
                <a href="../front/listEvenements.php" class="sidebar-link">
                    <span class="icon">🌍</span>
                    <span>Voir le site</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Ajouter un événement</h1>
                <p>Créez un nouvel événement pour la communauté GreenBite</p>
            </div>

            <?php if ($error): ?>
                <div class="alert">
                    <strong>❌ Erreurs de validation :</strong><br>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="form-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Titre de l'événement <span class="required">*</span></label>
                            <input type="text" name="titre" 
                                   value="<?= htmlspecialchars($formData['titre']) ?>"
                                   class="<?= isset($errors['titre']) ? 'field-error' : '' ?>">
                            <small>3 à 100 caractères (lettres, chiffres, espaces, tirets, apostrophes)</small>
                        </div>

                        <div class="form-group">
                            <label>Description <span class="required">*</span></label>
                            <textarea name="description" 
                                      class="<?= isset($errors['description']) ? 'field-error' : '' ?>"><?= htmlspecialchars($formData['description']) ?></textarea>
                            <small>Minimum 10 caractères, maximum 5000 caractères</small>
                        </div>

                        <div class="form-group">
                            <label>Date <span class="required">*</span></label>
                            <input type="text" name="date" 
                                   value="<?= htmlspecialchars($formData['date']) ?>" 
                                   placeholder="AAAA-MM-JJ"
                                   class="<?= isset($errors['date']) ? 'field-error' : '' ?>">
                            <small>Format: AAAA-MM-JJ (ex: 2026-12-15) - La date ne peut pas être dans le passé</small>
                        </div>

                        <div class="form-group">
                            <label>Lieu <span class="required">*</span></label>
                            <input type="text" name="lieu" 
                                   value="<?= htmlspecialchars($formData['lieu']) ?>"
                                   class="<?= isset($errors['lieu']) ? 'field-error' : '' ?>">
                            <small>Ex: Tunis, Sfax, Sousse, Hammamet...</small>
                        </div>

                        <div class="form-group">
                            <label>Type d'événement <span class="required">*</span></label>
                            <select name="type" class="<?= isset($errors['type']) ? 'field-error' : '' ?>">
                                <option value="">Sélectionnez un type</option>
                                <option value="Atelier" <?= $formData['type'] == 'Atelier' ? 'selected' : '' ?>>🧑‍🍳 Atelier</option>
                                <option value="Conférence" <?= $formData['type'] == 'Conférence' ? 'selected' : '' ?>>🎤 Conférence</option>
                                <option value="Festival" <?= $formData['type'] == 'Festival' ? 'selected' : '' ?>>🎉 Festival</option>
                                <option value="Autre" <?= $formData['type'] == 'Autre' ? 'selected' : '' ?>>📌 Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Organisateur <span class="required">*</span></label>
                            <select name="organisateur_id" class="<?= isset($errors['organisateur_id']) ? 'field-error' : '' ?>">
                                <option value="">Sélectionnez un organisateur</option>
                                <?php foreach ($organisateurs as $org): ?>
                                    <option value="<?= $org['id'] ?>" <?= $formData['organisateur_id'] == $org['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($org['nom']) ?> (<?= htmlspecialchars($org['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Sélectionnez l'organisateur de cet événement</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">✅ Ajouter l'événement</button>
                            <a href="dashboardEvenement.php" class="btn-cancel">❌ Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>