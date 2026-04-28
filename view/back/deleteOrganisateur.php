<?php
session_start();
require_once "../../controller/OrganisateurController.php";

$controller = new OrganisateurController();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validation PHP
if (!$id) {
    $_SESSION['message'] = "ID d'organisateur invalide";
    header('Location: organisateurs.php');
    exit();
}

// Vérifier si l'organisateur existe
$organisateur = $controller->getOrganisateurById($id);
if (!$organisateur) {
    $_SESSION['message'] = "Organisateur non trouvé";
    header('Location: organisateurs.php');
    exit();
}

// Vérifier le nombre d'événements associés
$eventCount = $controller->getEventCountByOrganisateur($id);

// Traitement de la confirmation
if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $result = $controller->deleteOrganisateur($id);
    
    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['message'] = "Erreur: " . $result['message'];
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
            max-width: 550px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 118, 110, 0.15);
            overflow: hidden;
            text-align: center;
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
        .organisateur-info {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 16px;
            margin: 1rem 0;
            text-align: left;
        }
        .organisateur-info h3 {
            color: #0f172a;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }
        .organisateur-info p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-size: 0.9rem;
            border-left: 4px solid #f59e0b;
        }
        .danger-warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-size: 0.9rem;
            border-left: 4px solid #dc2626;
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
            display: inline-block;
            text-align: center;
        }
        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }
        .btn-danger:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
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
            display: inline-block;
            text-align: center;
        }
        .btn-cancel:hover {
            background: #e2e8f0;
            color: #475569;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .confirm-actions { flex-direction: column; }
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
            <p>Êtes-vous sûr de vouloir supprimer cet organisateur ?</p>
            
            <div class="organisateur-info">
                <h3><?= htmlspecialchars($organisateur['nom']) ?></h3>
                <p>📧 <?= htmlspecialchars($organisateur['email']) ?></p>
                <p>📞 <?= htmlspecialchars($organisateur['telephone']) ?></p>
                <?php if (!empty($organisateur['adresse'])): ?>
                    <p>📍 <?= htmlspecialchars($organisateur['adresse']) ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($eventCount > 0): ?>
                <div class="danger-warning">
                    ⚠️ Cet organisateur est associé à <strong><?= $eventCount ?> événement(s)</strong>.
                    Vous ne pouvez pas le supprimer tant que ces événements existent.
                    <br><br>
                    <a href="organisateurs.php" style="color: #991b1b; font-weight: 600;">→ Retour à la liste</a>
                </div>
            <?php else: ?>
                <div class="warning">
                    ⚠️ Cette action est irréversible ! L'organisateur sera définitivement supprimé.
                </div>
                
                <div class="confirm-actions">
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn-danger">🗑️ Oui, supprimer</button>
                    </form>
                    <a href="organisateurs.php" class="btn-cancel">❌ Annuler</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>