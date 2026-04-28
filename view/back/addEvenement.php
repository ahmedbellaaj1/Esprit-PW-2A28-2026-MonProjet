<?php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../model/Evenement.php";

$error = '';
$success = '';
$formData = ['titre' => '', 'description' => '', 'date' => '', 'lieu' => '', 'type' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $formData = [
        'titre' => trim($_POST['titre'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'lieu' => trim($_POST['lieu'] ?? ''),
        'type' => trim($_POST['type'] ?? '')
    ];
    
    // Validation PHP uniquement
    $errors = [];
    
    // Validation du titre
    if (empty($formData['titre'])) {
        $errors['titre'] = "Le titre est obligatoire";
    } elseif (strlen($formData['titre']) < 3) {
        $errors['titre'] = "Le titre doit contenir au moins 3 caractères";
    } elseif (strlen($formData['titre']) > 100) {
        $errors['titre'] = "Le titre ne peut pas dépasser 100 caractères";
    }
    
    // Validation de la description
    if (empty($formData['description'])) {
        $errors['description'] = "La description est obligatoire";
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = "La description doit contenir au moins 10 caractères";
    }
    
    // Validation de la date
    if (empty($formData['date'])) {
        $errors['date'] = "La date est obligatoire";
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $formData['date']);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $formData['date']) {
            $errors['date'] = "Format de date invalide";
        } elseif ($dateObj < new DateTime()) {
            $errors['date'] = "La date ne peut pas être dans le passé";
        }
    }
    
    // Validation du lieu
    if (empty($formData['lieu'])) {
        $errors['lieu'] = "Le lieu est obligatoire";
    } elseif (strlen($formData['lieu']) < 2) {
        $errors['lieu'] = "Le lieu doit contenir au moins 2 caractères";
    }
    
    // Validation du type
    $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
    if (empty($formData['type'])) {
        $errors['type'] = "Le type est obligatoire";
    } elseif (!in_array($formData['type'], $validTypes)) {
        $errors['type'] = "Type d'événement invalide";
    }
    
    // Si pas d'erreurs, on ajoute
    if (empty($errors)) {
        try {
            $event = new Evenement(
                $formData['titre'],
                $formData['description'],
                $formData['date'],
                $formData['lieu'],
                $formData['type']
            );
            
            $controller = new EvenementController();
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
        // Afficher les erreurs
        $error = implode('<br>', $errors);
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
            padding: 2rem;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
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
            body { padding: 1rem; }
            .form-header { padding: 1.5rem; }
            .form-header h1 { font-size: 1.4rem; }
            .form-body { padding: 1.5rem; }
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>➕ Ajouter un événement</h1>
            <p>Créez un nouvel événement pour la communauté GreenBite</p>
        </div>
        
        <div class="form-body">
            <?php if ($error): ?>
                <div class="error-message">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Titre de l'événement <span class="required">*</span></label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($formData['titre']) ?>"
                           class="<?= isset($errors['titre']) ? 'field-error' : '' ?>">
                    <small>3 à 100 caractères</small>
                </div>

                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" class="<?= isset($errors['description']) ? 'field-error' : '' ?>"><?= htmlspecialchars($formData['description']) ?></textarea>
                    <small>Minimum 10 caractères</small>
                </div>

                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="date" name="date" value="<?= htmlspecialchars($formData['date']) ?>"
                           class="<?= isset($errors['date']) ? 'field-error' : '' ?>">
                    <small>La date ne peut pas être dans le passé</small>
                </div>

                <div class="form-group">
                    <label>Lieu <span class="required">*</span></label>
                    <input type="text" name="lieu" value="<?= htmlspecialchars($formData['lieu']) ?>"
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

                <div class="form-actions">
                    <button type="submit" class="btn-submit">✅ Ajouter l'événement</button>
                    <a href="dashboardEvenement.php" class="btn-cancel">❌ Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>