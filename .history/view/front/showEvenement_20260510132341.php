<?php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../controller/ParticipationController.php";
require_once "../../controller/AvisController.php";
require_once "../../controller/FavorisController.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

$controller = new EvenementController();
$participationController = new ParticipationController();
$avisController = new AvisController();
$favorisController = new FavorisController();

// Récupération de l'utilisateur connecté
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']);
$userId = null;
$userName = '';
$userRole = '';

if ($isLoggedIn) {
    $userId = $_SESSION['user']['id'];
    $userName = trim(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? ''));
    $userRole = $_SESSION['user']['role'] ?? 'user';
}

// 1. Validation et récupération de l'ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: listEvenements.php');
    exit();
}

// 2. Récupération de l'événement
$event = $controller->getEvenementById($id);

if (!$event || !is_array($event)) {
    header('Location: listEvenements.php');
    exit();
}

// 3. Traitement des formulaires (avis et favoris)
$avisError = '';
$avisSuccess = '';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_avis') {
        $note = (int)($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        $errors = [];
        if ($note < 1 || $note > 5) {
            $errors['note'] = "La note doit être comprise entre 1 et 5";
        }
        if (empty($commentaire)) {
            $errors['commentaire'] = "Le commentaire est obligatoire";
        } elseif (strlen($commentaire) < 5) {
            $errors['commentaire'] = "Le commentaire doit contenir au moins 5 caractères";
        }
        
        if (empty($errors)) {
            $result = $avisController->addOrUpdateAvis($userId, $id, $note, $commentaire);
            if ($result['success']) {
                $avisSuccess = $result['message'];
            } else {
                $avisError = $result['message'];
            }
        } else {
            $avisError = implode('<br>', $errors);
        }
    } elseif ($action === 'add_favori') {
        $result = $favorisController->addFavori($userId, $id);
        if (!$result['success']) {
            $avisError = $result['message'];
        }
    } elseif ($action === 'remove_favori') {
        $result = $favorisController->removeFavori($userId, $id);
        if (!$result['success']) {
            $avisError = $result['message'];
        }
    }
}

// 4. Validation et nettoyage des données de l'événement
$eventId = isset($event['id']) ? (int)$event['id'] : 0;
$eventTitre = isset($event['titre']) ? htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') : 'Sans titre';
$eventDescription = isset($event['description']) ? nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')) : 'Aucune description disponible.';
$eventDate = isset($event['date_event']) ? $event['date_event'] : '';
$eventLieu = isset($event['lieu']) ? htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') : 'Lieu non spécifié';
$eventType = isset($event['type']) ? htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8') : 'Autre';
$capaciteMax = isset($event['capacite_max']) ? (int)$event['capacite_max'] : 0;

// 5. Données de l'organisateur (jointure)
$organisateurNom = isset($event['organisateur_nom']) ? htmlspecialchars($event['organisateur_nom'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
$organisateurEmail = isset($event['organisateur_email']) ? htmlspecialchars($event['organisateur_email'], ENT_QUOTES, 'UTF-8') : '';
$organisateurSiteWeb = isset($event['organisateur_site_web']) ? htmlspecialchars($event['organisateur_site_web'], ENT_QUOTES, 'UTF-8') : '';

// 6. Récupération des avis et favoris
$avisList = $avisController->getAvisByEvent($eventId);
$noteStats = $avisController->getAverageNote($eventId);
$userAvis = $avisController->getUserAvis($userId, $eventId);
$isFavori = $isLoggedIn ? $favorisController->isFavori($userId, $eventId) : false;
$favorisCount = $favorisController->countFavorisByEvent($eventId);

// 7. Formatage de la date
$formattedDate = '';
$formattedDateLong = '';
$isPast = false;
$isToday = false;
$daysRemaining = null;

if (!empty($eventDate)) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($dateObj && $dateObj->format('Y-m-d') === $eventDate) {
        $formattedDate = $dateObj->format('d/m/Y');
        
        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $formattedDateLong = $jours[(int)$dateObj->format('w')] . ' ' . $dateObj->format('d') . ' ' . $mois[(int)$dateObj->format('n')-1] . ' ' . $dateObj->format('Y');
        
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

// 8. Icône et classe CSS
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

// 9. Statut de l'événement
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

// 10. Vérifier les places disponibles
$inscrits = $participationController->countParticipantsByEvent($eventId);
$placesRestantes = $capaciteMax - $inscrits;
$complet = ($capaciteMax > 0 && $placesRestantes <= 0);
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
        .navbar-logo { font-size: 1.6rem; font-weight: 700; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .navbar-logo span { color: #ccfbf1; }
        .navbar-logo img { height: 35px; width: 35px; border-radius: 8px; object-fit: cover; }
        .navbar-links { display: flex; gap: 2rem; list-style: none; }
        .navbar-links a { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem; font-weight: 500; }
        .navbar-links a:hover, .navbar-links a.active { color: white; border-bottom: 2px solid white; }
        .nav-btn { background: rgba(255,255,255,0.15); color: white; padding: 0.5rem 1.2rem; border-radius: 9999px; text-decoration: none; font-size: 0.85rem; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-name { color: white; font-size: 0.9rem; }
        
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
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .favori-btn {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .favori-btn:hover { transform: scale(1.1); }
        
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
        .show-description h3 { font-size: 1.2rem; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; }
        .show-description p { line-height: 1.7; color: #334155; }
        
        /* Organisateur Section */
        .organisateur-section {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            padding: 1.5rem;
            border-radius: 20px;
            margin: 1.5rem 0;
        }
        .organisateur-section h3 { font-size: 1.1rem; margin-bottom: 1rem; color: #0f766e; display: flex; align-items: center; gap: 0.5rem; }
        .organisateur-info { display: flex; flex-wrap: wrap; gap: 1rem; }
        .organisateur-info p { display: flex; align-items: center; gap: 0.5rem; color: #334155; font-size: 0.9rem; background: white; padding: 0.5rem 1rem; border-radius: 12px; }
        
        /* Avis Section */
        .avis-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 20px;
        }
        .avis-section h3 { font-size: 1.2rem; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; }
        .rating-summary {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }
        .rating-average {
            text-align: center;
            background: #0f766e;
            color: white;
            padding: 1rem;
            border-radius: 16px;
            min-width: 100px;
        }
        .rating-average .big-number { font-size: 2rem; font-weight: 700; }
        .stars { display: flex; gap: 4px; margin-top: 5px; }
        .star { font-size: 1.2rem; }
        .star.gold { color: #f59e0b; }
        
        .form-avis textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            resize: vertical;
            min-height: 100px;
        }
        .star-rating { display: flex; gap: 5px; margin: 10px 0; }
        .star-btn {
            font-size: 1.8rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
            color: #cbd5e1;
        }
        .star-btn:hover, .star-btn.active { color: #f59e0b; transform: scale(1.1); }
        .avis-card {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
        }
        .avis-author { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
        .avis-author .name { font-weight: 600; color: #0f172a; }
        .avis-author .date { font-size: 0.7rem; color: #64748b; }
        .avis-comment { color: #334155; margin-top: 0.5rem; }
        .user-avis { background: #f0fdf4; border-left: 4px solid #16a34a; }
        
        /* Inscription Button */
        .inscription-section {
            text-align: center;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f0fdf4;
            border-radius: 20px;
        }
        .btn-inscription {
            display: inline-block;
            background: #0f766e;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn-inscription:hover { background: #0c5f58; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15,118,110,0.3); }
        .places-info { margin-top: 0.5rem; font-size: 0.8rem; color: #64748b; }
        
        /* Back Link */
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: #0f766e; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .back-link:hover { gap: 0.75rem; text-decoration: underline; }
        
        .error-message { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 12px; margin-bottom: 1rem; }
        .success-message { background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 12px; margin-bottom: 1rem; }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; height: auto; padding: 1rem; gap: 0.5rem; }
            .show-container { margin: 1rem; }
            .show-header { padding: 1.5rem; }
            .show-header h1 { font-size: 1.5rem; }
            .show-content { padding: 1.5rem; }
            .show-info { grid-template-columns: 1fr; }
            .rating-summary { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">
        <img src="../assets/images/logo.png" alt="GreenBite">
        <span>Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="listEvenements.php">Événements</a></li>
        <li><a href="recherche-avancee.php">Recherche avancée</a></li>
        <li><a href="calendrier.php">Calendrier</a></li>
    </ul>
    <div class="user-info">
        <?php if ($isLoggedIn): ?>
            <span class="user-name">👤 <?= htmlspecialchars($userName) ?></span>
            <a href="logout.php" class="nav-btn">🚪 Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="nav-btn">🔑 Connexion</a>
        <?php endif; ?>
        <?php if ($userRole === 'admin'): ?>
            <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="show-container">
    <div class="show-header">
        <div class="header-top">
            <h1><?= $eventTitre ?></h1>
            <?php if ($isLoggedIn): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="<?= $isFavori ? 'remove_favori' : 'add_favori' ?>">
                    <button type="submit" class="favori-btn" title="<?= $isFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                        <?= $isFavori ? '❤️' : '🤍' ?>
                    </button>
                </form>
            <?php else: ?>
                <span class="favori-btn" title="Connectez-vous pour ajouter aux favoris">🤍</span>
            <?php endif; ?>
        </div>
        <div>
            <span class="type-badge <?= $typeClass ?>"><?= $typeIcon ?> <?= $eventType ?></span>
            <span class="status-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= $statusText ?></span>
            <span style="margin-left: 0.5rem; font-size: 0.8rem;">⭐ <?= $noteStats['moyenne'] ?> (<?= $noteStats['total'] ?> avis) | ❤️ <?= $favorisCount ?></span>
            <?php if ($daysRemaining !== null && $daysRemaining > 0): ?>
                <span class="status-badge status-upcoming" style="margin-left: 0.5rem;">⏰ Dans <?= $daysRemaining ?> jour<?= $daysRemaining > 1 ? 's' : '' ?></span>
            <?php elseif ($daysRemaining === 0): ?>
                <span class="status-badge status-today" style="margin-left: 0.5rem;">🔴 C'est aujourd'hui !</span>
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
                <?php if ($organisateurSiteWeb): ?>
                    <p>🌐 <a href="<?= $organisateurSiteWeb ?>" target="_blank" style="color: #0f766e;"><?= $organisateurSiteWeb ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Avis -->
        <div class="avis-section">
            <h3>⭐ Avis et témoignages</h3>
            
            <div class="rating-summary">
                <div class="rating-average">
                    <div class="big-number"><?= $noteStats['moyenne'] ?></div>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= round($noteStats['moyenne']) ? 'gold' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <div style="font-size: 0.7rem;"><?= $noteStats['total'] ?> avis</div>
                </div>
                <div style="flex: 1;">
                    <p>Partagez votre expérience sur cet événement !</p>
                </div>
            </div>
            
            <?php if ($avisError): ?>
                <div class="error-message">❌ <?= $avisError ?></div>
            <?php endif; ?>
            
            <?php if ($avisSuccess): ?>
                <div class="success-message">✅ <?= $avisSuccess ?></div>
            <?php endif; ?>
            
            <!-- Formulaire d'avis (pour utilisateurs connectés) -->
            <?php if ($isLoggedIn): ?>
                <form method="POST" class="form-avis">
                    <input type="hidden" name="action" value="add_avis">
                    <div class="star-rating" id="starRating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="star-btn" data-note="<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="note" id="noteValue" value="<?= $userAvis['note'] ?? 5 ?>">
                    <textarea name="commentaire" placeholder="Partagez votre expérience..."><?= htmlspecialchars($userAvis['commentaire'] ?? '') ?></textarea>
                    <button type="submit" class="btn-inscription" style="margin-top: 1rem; padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <?= $userAvis ? 'Modifier mon avis' : 'Publier mon avis' ?>
                    </button>
                </form>
            <?php else: ?>
                <p style="text-align: center; padding: 1rem;"><a href="login.php">Connectez-vous</a> pour laisser un avis.</p>
            <?php endif; ?>
            
            <!-- Liste des avis -->
            <div style="margin-top: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">📝 Témoignages</h4>
                <?php if (empty($avisList)): ?>
                    <p style="color: #64748b; text-align: center;">Soyez le premier à laisser un avis !</p>
                <?php else: ?>
                    <?php foreach ($avisList as $avis): 
                        $isUserAvis = ($isLoggedIn && $userId == $avis['user_id']);
                    ?>
                        <div class="avis-card <?= $isUserAvis ? 'user-avis' : '' ?>">
                            <div class="avis-author">
                                <span class="name"><?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']) ?></span>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $avis['note'] ? 'gold' : '' ?>" style="font-size: 0.8rem;">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="date">le <?= date('d/m/Y', strtotime($avis['date_creation'])) ?></span>
                                <?php if ($isUserAvis): ?>
                                    <span style="background: #dcfce7; color: #166534; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.6rem;">Votre avis</span>
                                <?php endif; ?>
                            </div>
                            <div class="avis-comment"><?= nl2br(htmlspecialchars($avis['commentaire'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Section Inscription -->
        <div class="inscription-section">
            <?php if ($isPast): ?>
                <p style="color: #64748b;">📅 Cet événement est déjà passé</p>
            <?php elseif ($complet): ?>
                <p style="color: #dc2626;">❌ Désolé, cet événement est complet !</p>
                <p class="places-info"><?= $capaciteMax ?> places maximum</p>
            <?php elseif ($isLoggedIn): ?>
                <a href="participer.php?id=<?= $eventId ?>" class="btn-inscription">📝 Participer à cet événement</a>
                <?php if ($capaciteMax > 0): ?>
                    <p class="places-info">🎟️ <?= $placesRestantes ?> places restantes sur <?= $capaciteMax ?></p>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn-inscription">🔑 Connectez-vous pour participer</a>
            <?php endif; ?>
        </div>
        
        <a href="listEvenements.php" class="back-link">← Retour aux événements</a>
    </div>
</div>

<script>
    // Gestion des étoiles pour la note
    const starBtns = document.querySelectorAll('.star-btn');
    const noteInput = document.getElementById('noteValue');
    
    starBtns.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const note = parseInt(btn.dataset.note);
            noteInput.value = note;
            
            starBtns.forEach((star, i) => {
                if (i < note) {
                    star.classList.add('active');
                    star.style.color = '#f59e0b';
                } else {
                    star.classList.remove('active');
                    star.style.color = '#cbd5e1';
                }
            });
        });
    });
    
    // Initialiser les étoiles avec la note existante
    const currentNote = parseInt(noteInput.value);
    if (currentNote) {
        starBtns.forEach((star, i) => {
            if (i < currentNote) {
                star.classList.add('active');
                star.style.color = '#f59e0b';
            }
        });
    }
</script>

</body>
</html>