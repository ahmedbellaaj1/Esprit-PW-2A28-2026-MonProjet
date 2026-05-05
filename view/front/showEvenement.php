<?php
require_once "../../controller/EvenementController.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

$controller = new EvenementController();

// 1. Validation et récupération de l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validation PHP de l'ID
if ($id <= 0) {
    header('Location: listEvenements.php');
    exit();
}

// 2. Récupération de l'événement
$event = $controller->getEvenementById($id);

// Vérification si l'événement existe
if (!$event || !is_array($event)) {
    header('Location: listEvenements.php');
    exit();
}

// 3. Validation et nettoyage des données de l'événement
$eventId = isset($event['id']) ? (int)$event['id'] : 0;
$eventTitre = isset($event['titre']) ? htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') : 'Sans titre';
$eventDescription = isset($event['description']) ? nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')) : 'Aucune description disponible.';
$eventDate = isset($event['date_event']) ? $event['date_event'] : '';
$eventLieu = isset($event['lieu']) ? htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') : 'Lieu non spécifié';
$eventType = isset($event['type']) ? htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8') : 'Autre';

// 4. Données de l'organisateur (jointure)
$organisateurNom = isset($event['organisateur_nom']) ? htmlspecialchars($event['organisateur_nom'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
$organisateurEmail = isset($event['organisateur_email']) ? htmlspecialchars($event['organisateur_email'], ENT_QUOTES, 'UTF-8') : '';
$organisateurTelephone = isset($event['organisateur_telephone']) ? htmlspecialchars($event['organisateur_telephone'], ENT_QUOTES, 'UTF-8') : '';
$organisateurAdresse = isset($event['organisateur_adresse']) ? htmlspecialchars($event['organisateur_adresse'], ENT_QUOTES, 'UTF-8') : '';
$organisateurSiteWeb = isset($event['organisateur_site_web']) ? htmlspecialchars($event['organisateur_site_web'], ENT_QUOTES, 'UTF-8') : '';

// 5. Formatage de la date
$formattedDate = '';
$formattedDateLong = '';
$isPast = false;
$isToday = false;
$daysRemaining = null;

if (!empty($eventDate)) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($dateObj && $dateObj->format('Y-m-d') === $eventDate) {
        $formattedDate = $dateObj->format('d/m/Y');
        
        // Formatage long avec jour de la semaine
        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $formattedDateLong = $jours[(int)$dateObj->format('w')] . ' ' . $dateObj->format('d') . ' ' . $mois[(int)$dateObj->format('n')-1] . ' ' . $dateObj->format('Y');
        
        // Statut de l'événement
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $isPast = ($dateObj < $today);
        $isToday = ($dateObj == $today);
        
        if (!$isPast && !$isToday) {
            $diff = $today->diff($dateObj);
            $daysRemaining = (int)$diff->days;
        }
    }
}

// 6. Icône et classe CSS selon le type
$typeIcon = match($eventType) {
    'Atelier' => '🧑‍🍳',
    'Conférence' => '🎤',
    'Festival' => '🎉',
    default => '📌'
};

$typeClass = match($eventType) {
    'Atelier' => 'type-Atelier',
    'Conférence' => 'type-Conférence',
    'Festival' => 'type-Festival',
    default => 'type-Autre'
};

// 7. Statut de l'événement
$statusText = '';
$statusIcon = '';
$statusClass = '';

if ($isToday) {
    $statusText = "Aujourd'hui";
    $statusIcon = "🔴";
    $statusClass = "status-today";
} elseif ($isPast) {
    $statusText = "Passé";
    $statusIcon = "✅";
    $statusClass = "status-past";
} else {
    $statusText = "À venir";
    $statusIcon = "📅";
    $statusClass = "status-upcoming";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $eventTitre ?> - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(15,118,110,0.25);
        }
        .navbar-logo { font-size: 1.6rem; font-weight: 700; color: white; text-decoration: none; }
        .navbar-logo span { color: #ccfbf1; }
        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }
        
        /* Main Container */
        .show-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Header */
        .show-header {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            padding: 2rem;
            color: white;
        }
        .show-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .show-header .type-badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            margin-top: 0.5rem;
        }
        
        /* Content */
        .show-content { padding: 2rem; }
        
        /* Info Cards */
        .show-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        .info-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .info-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .info-card .label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .info-card .value { font-size: 1rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem; }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .status-upcoming { background: #dcfce7; color: #166534; }
        .status-today { background: #fef3c7; color: #92400e; }
        .status-past { background: #f1f5f9; color: #64748b; }
        
        /* Description */
        .show-description {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 20px;
            margin: 1.5rem 0;
        }
        .show-description h3 { 
            font-size: 1.2rem; 
            margin-bottom: 1rem; 
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .show-description p { line-height: 1.7; color: #334155; }
        
        /* Organisateur Section */
        .organisateur-section {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            padding: 1.5rem;
            border-radius: 20px;
            margin: 1.5rem 0;
        }
        .organisateur-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #0f766e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .organisateur-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .organisateur-info p {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #334155;
            font-size: 0.9rem;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }
        
        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            color: #0f766e;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-link:hover { gap: 0.75rem; text-decoration: underline; }
        
        /* Type Badges */
        .type-Atelier { background: #dcfce7; color: #166534; }
        .type-Conférence { background: #dbeafe; color: #1e40af; }
        .type-Festival { background: #fef3c7; color: #92400e; }
        .type-Autre { background: #f3e8ff; color: #6b21a5; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; }
            .show-container { margin: 1rem; }
            .show-header { padding: 1.5rem; }
            .show-header h1 { font-size: 1.5rem; }
            .show-content { padding: 1.5rem; }
            .show-info { grid-template-columns: 1fr; }
            .organisateur-info { flex-direction: column; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">Green<span>Bite</span></a>
    <div class="navbar-right">
        <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
    </div>
</nav>

<div class="show-container">
    <div class="show-header">
        <h1><?= $eventTitre ?></h1>
        <div>
            <span class="type-badge <?= $typeClass ?>"><?= $typeIcon ?> <?= $eventType ?></span>
            <span class="status-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= $statusText ?></span>
            <?php if ($daysRemaining !== null && $daysRemaining > 0): ?>
                <span class="status-badge status-upcoming" style="margin-left: 0.5rem;">
                    ⏰ Dans <?= $daysRemaining ?> jour<?= $daysRemaining > 1 ? 's' : '' ?>
                </span>
            <?php elseif ($daysRemaining === 0): ?>
                <span class="status-badge status-today" style="margin-left: 0.5rem;">
                    🔴 C'est aujourd'hui !
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="show-content">
        <div class="show-info">
            <div class="info-card">
                <div class="icon">📅</div>
                <div class="label">Date</div>
                <div class="value"><?= $formattedDate ?></div>
                <?php if ($formattedDateLong): ?>
                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.25rem;"><?= $formattedDateLong ?></div>
                <?php endif; ?>
            </div>
            <div class="info-card">
                <div class="icon">📍</div>
                <div class="label">Lieu</div>
                <div class="value"><?= $eventLieu ?></div>
            </div>
            <div class="info-card">
                <div class="icon">🏷️</div>
                <div class="label">Type</div>
                <div class="value"><?= $eventType ?></div>
            </div>
        </div>
        
        <div class="show-description">
            <h3>📖 Description</h3>
            <p><?= $eventDescription ?></p>
        </div>
        
        <?php if ($organisateurNom !== 'Non spécifié'): ?>
        <div class="organisateur-section">
            <h3>👥 Organisateur</h3>
            <div class="organisateur-info">
                <p><strong><?= $organisateurNom ?></strong></p>
                <?php if ($organisateurEmail): ?>
                    <p>📧 <?= $organisateurEmail ?></p>
                <?php endif; ?>
                <?php if ($organisateurTelephone): ?>
                    <p>📞 <?= $organisateurTelephone ?></p>
                <?php endif; ?>
                <?php if ($organisateurSiteWeb): ?>
                    <p>🌐 <a href="<?= $organisateurSiteWeb ?>" target="_blank" style="color: #0f766e;"><?= $organisateurSiteWeb ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="listEvenements.php" class="back-link">← Retour aux événements</a>
    </div>
</div>

</body>
</html>