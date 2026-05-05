<?php
session_start();
require_once "../../controller/EvenementController.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

// Initialisation du contrôleur
$controller = new EvenementController();

// 1. Validation et récupération de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validation PHP de l'ID
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['message'] = "ID d'événement invalide. Veuillez réessayer.";
    header('Location: dashboardEvenement.php');
    exit();
}

// 2. Vérifier si l'événement existe dans la base de données
$event = $controller->getEvenementById($id);

if (!$event) {
    $_SESSION['message'] = "Événement non trouvé. Il a peut-être déjà été supprimé.";
    header('Location: dashboardEvenement.php');
    exit();
}

// 3. Validation supplémentaire des données de l'événement
$eventTitre = isset($event['titre']) ? trim(htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8')) : 'Sans titre';
$eventDate = isset($event['date_event']) ? $event['date_event'] : '';
$eventLieu = isset($event['lieu']) ? htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
$eventType = isset($event['type']) ? htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';

// 4. Traitement de la confirmation de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation du token CSRF basique
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
        $_SESSION['message'] = "Confirmation de suppression invalide.";
        header('Location: dashboardEvenement.php');
        exit();
    }
    
    // Vérification supplémentaire que l'ID est toujours valide
    $verifiedId = filter_var($id, FILTER_VALIDATE_INT);
    if (!$verifiedId || $verifiedId <= 0) {
        $_SESSION['message'] = "ID d'événement invalide lors de la suppression.";
        header('Location: dashboardEvenement.php');
        exit();
    }
    
    // Tentative de suppression
    $result = $controller->deleteEvenement($verifiedId);
    
    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $result['message'];
    }
    header('Location: dashboardEvenement.php');
    exit();
}

// 5. Formatage de la date pour l'affichage
$formattedDate = '';
if (!empty($eventDate)) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($dateObj && $dateObj->format('Y-m-d') === $eventDate) {
        $formattedDate = $dateObj->format('d/m/Y');
    } else {
        $formattedDate = $eventDate;
    }
}

// 6. Vérifier si l'événement est passé ou à venir (information supplémentaire)
$isPast = false;
if (!empty($eventDate)) {
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $eventDateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($eventDateObj && $eventDateObj < $today) {
        $isPast = true;
    }
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
            max-width: 550px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 118, 110, 0.15);
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
            font-size: 1.5rem;
            font-weight: 700;
        }
        .confirm-body {
            padding: 2rem;
        }
        .confirm-body p {
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .event-info {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 16px;
            margin: 1.25rem 0;
            text-align: left;
            border-left: 4px solid #dc2626;
        }
        .event-info h3 {
            color: #0f172a;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        .event-info p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .event-info .event-icon {
            font-size: 1rem;
            min-width: 24px;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 0.75rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .danger-warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-size: 0.85rem;
            font-weight: 500;
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
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .confirm-actions { flex-direction: column; }
            .confirm-header { padding: 1.5rem; }
            .confirm-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-header">
            <div class="icon">⚠️</div>
            <h1>Confirmer la suppression</h1>
        </div>
        
        <div class="confirm-body">
            <p>Êtes-vous sûr de vouloir supprimer définitivement cet événement ?</p>
            
            <div class="event-info">
                <h3>📌 <?= $eventTitre ?></h3>
                <p>
                    <span class="event-icon">📅</span>
                    <span>Date : <?= $formattedDate ?></span>
                    <?php if ($isPast): ?>
                        <span style="color: #f59e0b; margin-left: 0.5rem;">(Événement passé)</span>
                    <?php endif; ?>
                </p>
                <p>
                    <span class="event-icon">📍</span>
                    <span>Lieu : <?= $eventLieu ?></span>
                </p>
                <p>
                    <span class="event-icon">🏷️</span>
                    <span>Type : <?= $eventType ?></span>
                </p>
            </div>
            
            <?php if ($isPast): ?>
                <div class="danger-warning">
                    ⚠️ Cet événement est déjà passé. Sa suppression n'affectera pas le planning à venir.
                </div>
            <?php else: ?>
                <div class="warning">
                    ⚠️ Attention : Cette action est irréversible et supprimera définitivement l'événement.
                </div>
            <?php endif; ?>
            
            <div class="confirm-actions">
                <!-- Formulaire sans attributs HTML5 de validation -->
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="btn-danger">
                        🗑️ Oui, supprimer définitivement
                    </button>
                </form>
                <a href="dashboardEvenement.php" class="btn-cancel">
                    ❌ Non, annuler
                </a>
            </div>
        </div>
    </div>
</body>
</html>