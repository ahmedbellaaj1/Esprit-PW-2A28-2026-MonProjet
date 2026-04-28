<?php
session_start();
require_once "../../controller/OrganisateurController.php";
require_once "../../model/Organisateur.php";

$controller = new OrganisateurController();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['message'] = "ID d'organisateur invalide";
    header('Location: organisateurs.php');
    exit();
}

$organisateurData = $controller->getOrganisateurById($id);
if (!$organisateurData) {
    $_SESSION['message'] = "Organisateur non trouvé";
    header('Location: organisateurs.php');
    exit();
}

$error = '';
$formData = [
    'nom' => $organisateurData['nom'],
    'email' => $organisateurData['email'],
    'telephone' => $organisateurData['telephone'],
    'adresse' => $organisateurData['adresse'] ?? '',
    'site_web' => $organisateurData['site_web'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $formData = [
        'nom' => trim($_POST['nom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'site_web' => trim($_POST['site_web'] ?? '')
    ];
    
    // Validation PHP uniquement
    $errors = [];
    
    // Validation du nom
    if (empty($formData['nom'])) {
        $errors['nom'] = "Le nom de l'organisateur est obligatoire";
    } elseif (strlen($formData['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
    } elseif (strlen($formData['nom']) > 100) {
        $errors['nom'] = "Le nom ne peut pas dépasser 100 caractères";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $formData['nom'])) {
        $errors['nom'] = "Le nom ne peut contenir que des lettres, espaces et tirets";
    }
    
    // Validation de l'email
    if (empty($formData['email'])) {
        $errors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    } elseif (strlen($formData['email']) > 150) {
        $errors['email'] = "L'email ne peut pas dépasser 150 caractères";
    }
    
    // Validation du téléphone
    if (empty($formData['telephone'])) {
        $errors['telephone'] = "Le numéro de téléphone est obligatoire";
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $formData['telephone'])) {
        $errors['telephone'] = "Format de téléphone invalide (ex: 71 123 456)";
    }
    
    // Validation de l'adresse (optionnelle)
    if (!empty($formData['adresse']) && strlen($formData['adresse']) < 5) {
        $errors['adresse'] = "L'adresse doit contenir au moins 5 caractères";
    }
    
    // Validation du site web (optionnel)
    if (!empty($formData['site_web']) && !filter_var($formData['site_web'], FILTER_VALIDATE_URL)) {
        $errors['site_web'] = "Format d'URL invalide (ex: https://exemple.com)";
    }
    
    // Si pas d'erreurs, on modifie
    if (empty($errors)) {
        try {
            $organisateur = new Organisateur(
                $formData['nom'],
                $formData['email'],
                $formData['telephone'],
                $formData['adresse'],
                $formData['site_web']
            );
            
            $result = $controller->updateOrganisateur($organisateur, $id);
            
            if ($result['success']) {
                $_SESSION['message'] = $result['message'];
                header('Location: organisateurs.php');
                exit();
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'organisateur - GreenBite Admin</title>
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
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
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
            <h1>✏️ Modifier l'organisateur</h1>
            <p>Modifiez les informations de l'organisateur</p>
        </div>
        
        <div class="form-body">
            <?php if ($error): ?>
                <div class="error-message">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Nom de l'organisateur <span class="required">*</span></label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($formData['nom']) ?>"
                           class="<?= isset($errors['nom']) ? 'field-error' : '' ?>">
                    <small>2 à 100 caractères (lettres, espaces et tirets uniquement)</small>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>"
                           class="<?= isset($errors['email']) ? 'field-error' : '' ?>">
                    <small>Format email valide (ex: contact@exemple.com)</small>
                </div>

                <div class="form-group">
                    <label>Téléphone <span class="required">*</span></label>
                    <input type="tel" name="telephone" value="<?= htmlspecialchars($formData['telephone']) ?>"
                           class="<?= isset($errors['telephone']) ? 'field-error' : '' ?>">
                    <small>Format: 71 123 456 ou 71234567</small>
                </div>

                <div class="form-group">
                    <label>Adresse</label>
                    <textarea name="adresse" class="<?= isset($errors['adresse']) ? 'field-error' : '' ?>"><?= htmlspecialchars($formData['adresse']) ?></textarea>
                    <small>Optionnel - Minimum 5 caractères</small>
                </div>

                <div class="form-group">
                    <label>Site web</label>
                    <input type="url" name="site_web" value="<?= htmlspecialchars($formData['site_web']) ?>"
                           class="<?= isset($errors['site_web']) ? 'field-error' : '' ?>">
                    <small>Optionnel - Format: https://exemple.com</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">💾 Enregistrer les modifications</button>
                    <a href="organisateurs.php" class="btn-cancel">❌ Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>