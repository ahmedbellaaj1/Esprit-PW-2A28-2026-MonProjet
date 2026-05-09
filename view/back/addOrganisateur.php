<?php
session_start();
require_once "../../controller/OrganisateurController.php";
require_once "../../model/Organisateur.php";

$error = '';
$success = '';
$formData = ['nom' => '', 'email' => '', 'telephone' => '', 'adresse' => '', 'site_web' => ''];
$errors = [];

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
    
    // 1. Validation du nom
    if (empty($formData['nom'])) {
        $errors['nom'] = "Le nom de l'organisateur est obligatoire";
    } elseif (strlen($formData['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
    } elseif (strlen($formData['nom']) > 100) {
        $errors['nom'] = "Le nom ne peut pas dépasser 100 caractères";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/', $formData['nom'])) {
        $errors['nom'] = "Le nom ne peut contenir que des lettres, espaces, tirets, points et apostrophes";
    }
    
    // 2. Validation de l'email
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
    
    // 3. Validation du téléphone
    if (empty($formData['telephone'])) {
        $errors['telephone'] = "Le numéro de téléphone est obligatoire";
    } elseif (strlen($formData['telephone']) < 8) {
        $errors['telephone'] = "Le numéro de téléphone doit contenir au moins 8 chiffres";
    } elseif (strlen($formData['telephone']) > 20) {
        $errors['telephone'] = "Le numéro de téléphone ne peut pas dépasser 20 caractères";
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $formData['telephone'])) {
        $errors['telephone'] = "Format de téléphone invalide. Exemples: 71 123 456, 71234567, +21671234567";
    } else {
        $digitsOnly = preg_replace('/[^0-9]/', '', $formData['telephone']);
        if (strlen($digitsOnly) < 8) {
            $errors['telephone'] = "Le numéro de téléphone doit contenir au moins 8 chiffres";
        }
    }
    
    // 4. Validation de l'adresse (optionnelle)
    if (!empty($formData['adresse'])) {
        if (strlen($formData['adresse']) < 5) {
            $errors['adresse'] = "L'adresse doit contenir au moins 5 caractères";
        }
        if (strlen($formData['adresse']) > 500) {
            $errors['adresse'] = "L'adresse ne peut pas dépasser 500 caractères";
        }
    }
    
    // 5. Validation du site web (optionnel)
    if (!empty($formData['site_web'])) {
        if (strlen($formData['site_web']) > 200) {
            $errors['site_web'] = "L'URL du site web ne peut pas dépasser 200 caractères";
        } else {
            $urlToValidate = $formData['site_web'];
            if (!preg_match('/^https?:\/\//', $urlToValidate)) {
                $urlToValidate = 'https://' . $urlToValidate;
            }
            if (!filter_var($urlToValidate, FILTER_VALIDATE_URL)) {
                $errors['site_web'] = "Format d'URL invalide. Exemples: https://exemple.com, http://exemple.com";
            } else {
                $parsedUrl = parse_url($urlToValidate);
                if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
                    $errors['site_web'] = "L'URL doit contenir un nom de domaine valide";
                }
            }
        }
    }
    
    // ==================== TRAITEMENT SI PAS D'ERREURS ====================
    if (empty($errors)) {
        try {
            $organisateur = new Organisateur(
                $formData['nom'],
                $formData['email'],
                $formData['telephone'],
                $formData['adresse'],
                $formData['site_web']
            );
            
            $controller = new OrganisateurController();
            $result = $controller->addOrganisateur($organisateur);
            
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
    <title>Ajouter un organisateur - GreenBite Admin</title>
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
                <a href="addEvenement.php" class="sidebar-link">
                    <span class="icon">➕</span>
                    <span>Ajouter un événement</span>
                </a>
                <a href="addOrganisateur.php" class="sidebar-link active">
                    <span class="icon">👥</span>
                    <span>Ajouter organisateur</span>
                </a>
                <a href="../front/listEvenements.php" class="sidebar-link">
                    <span class="icon">🌍</span>
                    <span>Voir le site</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Ajouter un organisateur</h1>
                <p>Créez un nouvel organisateur pour les événements GreenBite</p>
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
                            <button type="submit" class="btn-submit">✅ Ajouter l'organisateur</button>
                            <a href="organisateurs.php" class="btn-cancel">❌ Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>