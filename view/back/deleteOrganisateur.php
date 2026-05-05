<?php
session_start();
require_once "../../controller/OrganisateurController.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

// Initialisation du contrôleur
$controller = new OrganisateurController();

// 1. Validation et récupération de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validation PHP de l'ID
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['message'] = "ID d'organisateur invalide. Veuillez réessayer.";
    header('Location: organisateurs.php');
    exit();
}

// 2. Vérifier si l'organisateur existe dans la base de données
$organisateur = $controller->getOrganisateurById($id);

if (!$organisateur) {
    $_SESSION['message'] = "Organisateur non trouvé. Il a peut-être déjà été supprimé.";
    header('Location: organisateurs.php');
    exit();
}

// 3. Validation supplémentaire des données de l'organisateur
$orgNom = isset($organisateur['nom']) ? trim(htmlspecialchars($organisateur['nom'], ENT_QUOTES, 'UTF-8')) : 'Sans nom';
$orgEmail = isset($organisateur['email']) ? htmlspecialchars($organisateur['email'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
$orgTelephone = isset($organisateur['telephone']) ? htmlspecialchars($organisateur['telephone'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
$orgAdresse = isset($organisateur['adresse']) && !empty($organisateur['adresse']) 
    ? htmlspecialchars($organisateur['adresse'], ENT_QUOTES, 'UTF-8') 
    : null;
$orgSiteWeb = isset($organisateur['site_web']) && !empty($organisateur['site_web']) 
    ? htmlspecialchars($organisateur['site_web'], ENT_QUOTES, 'UTF-8') 
    : null;

// 4. Vérifier le nombre d'événements associés
$eventCount = $controller->getEventCountByOrganisateur($id);
$eventCount = is_numeric($eventCount) ? (int)$eventCount : 0;

// 5. Traitement de la confirmation de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation du token CSRF basique
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
        $_SESSION['message'] = "Confirmation de suppression invalide.";
        header('Location: organisateurs.php');
        exit();
    }
    
    // Vérification supplémentaire que l'ID est toujours valide
    $verifiedId = filter_var($id, FILTER_VALIDATE_INT);
    if (!$verifiedId || $verifiedId <= 0) {
        $_SESSION['message'] = "ID d'organisateur invalide lors de la suppression.";
        header('Location: organisateurs.php');
        exit();
    }
    
    // Vérifier à nouveau le nombre d'événements (au cas où)
    $checkEventCount = $controller->getEventCountByOrganisateur($verifiedId);
    if ($checkEventCount > 0) {
        $_SESSION['message'] = "Impossible de supprimer : cet organisateur est toujours associé à $checkEventCount événement(s).";
        header('Location: organisateurs.php');
        exit();
    }
    
    // Tentative de suppression
    $result = $controller->deleteOrganisateur($verifiedId);
    
    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $result['message'];
    }
    header('Location: organisateurs.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmer la suppression - GreenBite Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .confirm-container {
            max-width: 580px;
            width: 100%;
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 50px rgba(15, 118, 110, 0.2);
            overflow: hidden;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .confirm-header {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            padding: 2rem;
            color: white;
        }
        .confirm-header .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .confirm-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
        }
        .confirm-header p {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .confirm-body {
            padding: 2rem;
        }
        .confirm-body > p {
            color: #334155;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .organisateur-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1.5rem;
            border-radius: 20px;
            margin: 1.25rem 0;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        .organisateur-info h3 {
            color: #0f172a;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 700;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .organisateur-info p {
            color: #475569;
            font-size: 0.9rem;
            margin: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .organisateur-info .info-icon {
            font-size: 1.1rem;
            min-width: 28px;
        }
        .event-stats {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 16px;
            margin: 1rem 0;
            font-size: 0.9rem;
            border-left: 4px solid #f59e0b;
            text-align: left;
        }
        .danger-warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 16px;
            margin: 1rem 0;
            font-size: 0.9rem;
            border-left: 4px solid #dc2626;
            text-align: left;
        }
        .danger-warning a {
            color: #991b1b;
            font-weight: 600;
            text-decoration: underline;
        }
        .danger-warning a:hover {
            color: #7f1d1d;
        }
        .confirm-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            flex: 1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        .btn-danger:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            flex: 1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-cancel:hover {
            background: #e2e8f0;
            color: #475569;
            transform: translateY(-2px);
        }
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .confirm-header { padding: 1.5rem; }
            .confirm-header .icon { font-size: 3rem; }
            .confirm-header h1 { font-size: 1.3rem; }
            .confirm-body { padding: 1.5rem; }
            .confirm-actions { flex-direction: column; }
            .organisateur-info p { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-header">
            <div class="icon">⚠️</div>
            <h1>Confirmer la suppression</h1>
            <p>Cette action est définitive</p>
        </div>
        
        <div class="confirm-body">
            <p>Êtes-vous sûr de vouloir supprimer cet organisateur ?</p>
            
            <div class="organisateur-info">
                <h3>
                    <span>👤</span>
                    <?= $orgNom ?>
                </h3>
                <p>
                    <span class="info-icon">📧</span>
                    <span><?= $orgEmail ?></span>
                </p>
                <p>
                    <span class="info-icon">📞</span>
                    <span><?= $orgTelephone ?></span>
                </p>
                <?php if ($orgAdresse): ?>
                    <p>
                        <span class="info-icon">📍</span>
                        <span><?= $orgAdresse ?></span>
                    </p>
                <?php endif; ?>
                <?php if ($orgSiteWeb): ?>
                    <p>
                        <span class="info-icon">🌐</span>
                        <span><?= $orgSiteWeb ?></span>
                    </p>
                <?php endif; ?>
                <div class="event-stats">
                    📊 <?= $eventCount ?> événement(s) organisé(s)
                </div>
            </div>
            
            <?php if ($eventCount > 0): ?>
                <div class="danger-warning">
                    <strong>⚠️ Impossible de supprimer</strong><br>
                    Cet organisateur est associé à <strong><?= $eventCount ?> événement(s)</strong>.
                    Vous ne pouvez pas le supprimer tant que ces événements existent.
                    <br><br>
                    <a href="organisateurs.php">← Retour à la liste des organisateurs</a>
                </div>
            <?php else: ?>
                <div class="warning">
                    <strong>⚠️ Attention :</strong> Cette action est irréversible ! 
                    L'organisateur sera définitivement supprimé de la base de données.
                </div>
                
                <!-- Formulaire sans aucun attribut HTML5 de validation -->
                <form method="POST" class="confirm-actions">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="btn-danger">
                        🗑️ Oui, supprimer définitivement
                    </button>
                    <a href="organisateurs.php" class="btn-cancel">
                        ❌ Non, annuler
                    </a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>