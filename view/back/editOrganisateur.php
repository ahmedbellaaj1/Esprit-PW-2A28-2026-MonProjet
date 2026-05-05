<?php
session_start();
require_once "../../controller/OrganisateurController.php";
require_once "../../model/Organisateur.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

$controller = new OrganisateurController();

// 1. Validation et récupération de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    $_SESSION['message'] = "ID d'organisateur invalide. Veuillez réessayer.";
    header('Location: organisateurs.php');
    exit();
}

// 2. Vérifier si l'organisateur existe
$organisateurData = $controller->getOrganisateurById($id);
if (!$organisateurData) {
    $_SESSION['message'] = "Organisateur non trouvé. Il a peut-être déjà été supprimé.";
    header('Location: organisateurs.php');
    exit();
}

// 3. Initialisation des données du formulaire
$error = '';
$formData = [
    'nom' => isset($organisateurData['nom']) ? $organisateurData['nom'] : '',
    'email' => isset($organisateurData['email']) ? $organisateurData['email'] : '',
    'telephone' => isset($organisateurData['telephone']) ? $organisateurData['telephone'] : '',
    'adresse' => isset($organisateurData['adresse']) ? $organisateurData['adresse'] : '',
    'site_web' => isset($organisateurData['site_web']) ? $organisateurData['site_web'] : ''
];
$errors = [];

// 4. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $formData = [
        'nom' => trim($_POST['nom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'site_web' => trim($_POST['site_web'] ?? '')
    ];
    
    // ==================== VALIDATIONS PHP UNIQUEMENT ====================
    
    // 4.1 Validation du nom
    if (empty($formData['nom'])) {
        $errors['nom'] = "Le nom de l'organisateur est obligatoire";
    } elseif (strlen($formData['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
    } elseif (strlen($formData['nom']) > 100) {
        $errors['nom'] = "Le nom ne peut pas dépasser 100 caractères";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/', $formData['nom'])) {
        $errors['nom'] = "Le nom ne peut contenir que des lettres, espaces, tirets, points et apostrophes";
    }
    
    // 4.2 Validation de l'email
    if (empty($formData['email'])) {
        $errors['email'] = "L'email est obligatoire";
    } elseif (strlen($formData['email']) > 150) {
        $errors['email'] = "L'email ne peut pas dépasser 150 caractères";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide. Exemple: nom@domaine.com";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $formData['email'])) {
        $errors['email'] = "Format d'email invalide. Vérifiez la présence du @ et du domaine";
    } elseif (preg_match('/[<>\(\)\[\]\\\;,]/', $formData['email'])) {
        $errors['email'] = "L'email contient des caractères non autorisés";
    }
    
    // 4.3 Validation du téléphone
    if (empty($formData['telephone'])) {
        $errors['telephone'] = "Le numéro de téléphone est obligatoire";
    } elseif (strlen($formData['telephone']) < 8) {
        $errors['telephone'] = "Le numéro de téléphone doit contenir au moins 8 chiffres";
    } elseif (strlen($formData['telephone']) > 20) {
        $errors['telephone'] = "Le numéro de téléphone ne peut pas dépasser 20 caractères";
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $formData['telephone'])) {
        $errors['telephone'] = "Format de téléphone invalide. Exemples: 71 123 456, 71234567, +21671234567";
    } else {
        // Vérification qu'il y a au moins 8 chiffres
        $digitsOnly = preg_replace('/[^0-9]/', '', $formData['telephone']);
        if (strlen($digitsOnly) < 8) {
            $errors['telephone'] = "Le numéro de téléphone doit contenir au moins 8 chiffres";
        }
    }
    
    // 4.4 Validation de l'adresse (optionnelle)
    if (!empty($formData['adresse'])) {
        if (strlen($formData['adresse']) < 5) {
            $errors['adresse'] = "L'adresse doit contenir au moins 5 caractères";
        }
        if (strlen($formData['adresse']) > 500) {
            $errors['adresse'] = "L'adresse ne peut pas dépasser 500 caractères";
        }
    }
    
    // 4.5 Validation du site web (optionnel)
    if (!empty($formData['site_web'])) {
        if (strlen($formData['site_web']) > 200) {
            $errors['site_web'] = "L'URL du site web ne peut pas dépasser 200 caractères";
        } else {
            // Ajouter http:// si absent pour validation
            $urlToValidate = $formData['site_web'];
            if (!preg_match('/^https?:\/\//', $urlToValidate)) {
                $urlToValidate = 'https://' . $urlToValidate;
            }
            if (!filter_var($urlToValidate, FILTER_VALIDATE_URL)) {
                $errors['site_web'] = "Format d'URL invalide. Exemples: https://exemple.com, http://exemple.com";
            } else {
                // Vérification supplémentaire du domaine
                $parsedUrl = parse_url($urlToValidate);
                if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
                    $errors['site_web'] = "L'URL doit contenir un nom de domaine valide";
                }
            }
        }
    }
    
    // 4.6 Vérification de l'unicité de l'email (si modifié)
    if (empty($errors) && $formData['email'] !== $organisateurData['email']) {
        if ($controller->emailExists($formData['email'], $id)) {
            $errors['email'] = "Cet email est déjà utilisé par un autre organisateur";
        }
    }
    
    // 4.7 Si pas d'erreurs, mise à jour
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
        // Construction du message d'erreur HTML
        $error = '<ul style="margin:0; padding-left:1.5rem;">';
        foreach ($errors as $field => $message) {
            $error .= '<li><strong>' . htmlspecialchars($field) . '</strong>: ' . htmlspecialchars($message) . '</li>';
        }
        $error .= '</ul>';
    }
}

// 5. Vérifier si l'organisateur a des événements associés
$eventCount = $controller->getEventCountByOrganisateur($id);
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
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        /* PAS d'attributs HTML5 de validation ! */
        .form-group input,
        .form-group textarea {
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
        .info-banner {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .event-stats {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
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
            .info-banner { flex-direction: column; align-items: flex-start; }
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
                    <strong>❌ Erreurs de validation :</strong><br>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="info-banner">
                <span>ℹ️</span> Tous les champs marqués d'un <span class="required">*</span> sont obligatoires.
                <?php if ($eventCount > 0): ?>
                    <span class="event-stats">📊 <?= $eventCount ?> événement(s) associé(s)</span>
                <?php endif; ?>
            </div>

            <!-- 
                ATTENTION : AUCUN ATTRIBUT HTML5 DE VALIDATION N'EST UTILISÉ !
                - PAS de "required"
                - PAS de "minlength"
                - PAS de "maxlength"
                - PAS de "pattern"
                - PAS de "type="email"" (type="text" est utilisé)
                - PAS de "type="url"" (type="text" est utilisé)
                - PAS de "type="tel"" (type="text" est utilisé)
                Toute la validation est faite en PHP côté serveur !
            -->
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nom de l'organisateur <span class="required">*</span></label>
                    <input type="text" name="nom" 
                           value="<?= htmlspecialchars($formData['nom']) ?>"
                           class="<?= isset($errors['nom']) ? 'field-error' : '' ?>">
                    <small>2 à 100 caractères (lettres, espaces, tirets, points et apostrophes uniquement)</small>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="text" name="email" 
                           value="<?= htmlspecialchars($formData['email']) ?>"
                           class="<?= isset($errors['email']) ? 'field-error' : '' ?>">
                    <small>Format email valide (ex: contact@exemple.com)</small>
                </div>

                <div class="form-group">
                    <label>Téléphone <span class="required">*</span></label>
                    <input type="text" name="telephone" 
                           value="<?= htmlspecialchars($formData['telephone']) ?>"
                           class="<?= isset($errors['telephone']) ? 'field-error' : '' ?>">
                    <small>Format: 71 123 456, 71234567 ou +21671234567 (8 chiffres minimum)</small>
                </div>

                <div class="form-group">
                    <label>Adresse</label>
                    <textarea name="adresse" 
                              class="<?= isset($errors['adresse']) ? 'field-error' : '' ?>"><?= htmlspecialchars($formData['adresse']) ?></textarea>
                    <small>Optionnel - Minimum 5 caractères, maximum 500 caractères</small>
                </div>

                <div class="form-group">
                    <label>Site web</label>
                    <input type="text" name="site_web" 
                           value="<?= htmlspecialchars($formData['site_web']) ?>"
                           class="<?= isset($errors['site_web']) ? 'field-error' : '' ?>">
                    <small>Optionnel - Format: https://exemple.com (http:// ou https://)</small>
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