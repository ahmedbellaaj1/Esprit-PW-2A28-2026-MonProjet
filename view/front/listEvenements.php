<?php
require_once "../../controller/EvenementController.php";

// ==================== VALIDATIONS PHP UNIQUEMENT ====================

$controller = new EvenementController();

// Récupération et validation des paramètres GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

// Validation du paramètre search
if (!empty($search)) {
    // Protection contre les injections
    $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
    $events = $controller->searchEvents($search);
} 
// Validation du paramètre type
elseif (!empty($type) && $type != 'all') {
    $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
    if (in_array($type, $validTypes)) {
        $events = $controller->getEventsByType($type);
    } else {
        // Type invalide, on redirige vers la liste par défaut
        $events = $controller->getUpcomingEvents();
    }
} 
// Aucun filtre, on affiche les événements à venir
else {
    $events = $controller->getUpcomingEvents();
}

// Récupération de tous les événements pour les statistiques (optionnel)
$allEvents = $controller->listEvenements();

// Validation des événements récupérés
if (!is_array($events)) {
    $events = [];
}
if (!is_array($allEvents)) {
    $allEvents = [];
}

// Comptage pour l'affichage
$eventsCount = count($events);
$totalEventsCount = count($allEvents);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(15, 118, 110, 0.25);
        }

        .navbar-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .navbar-logo span {
            color: #ccfbf1;
        }

        .navbar-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .navbar-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 0;
        }

        .navbar-links a:hover, .navbar-links a.active {
            color: white;
            border-bottom: 2px solid white;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 60%, #5eead4 100%);
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Form - PAS d'attributs HTML5 */
        .search-wrapper {
            display: flex;
            max-width: 500px;
            margin: 1.5rem auto 0;
            background: white;
            border-radius: 9999px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .search-wrapper input {
            flex: 1;
            border: none;
            padding: 0.9rem 1.5rem;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            outline: none;
        }

        .search-wrapper button {
            background: #0f766e;
            color: white;
            border: none;
            padding: 0.9rem 1.8rem;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            transition: background 0.3s;
        }

        .search-wrapper button:hover {
            background: #0c5f58;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section-heading {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .section-heading::before {
            content: '';
            width: 4px;
            height: 28px;
            background: #14b8a6;
            border-radius: 2px;
        }

        .results-count {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: normal;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .filter-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            border: 2px solid #14b8a6;
            background: white;
            color: #0f766e;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-block;
        }

        .filter-btn:hover, .filter-btn.active {
            background: #0f766e;
            color: white;
            border-color: #0f766e;
            transform: translateY(-2px);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px rgba(15, 118, 110, 0.15);
        }

        .product-img {
            height: 140px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
        }

        .product-body {
            padding: 1.25rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .product-lieu, .product-date {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .product-tags {
            margin: 0.75rem 0;
        }

        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .tag-Atelier { background: #dcfce7; color: #166534; }
        .tag-Conférence { background: #dbeafe; color: #1e40af; }
        .tag-Festival { background: #fef3c7; color: #92400e; }
        .tag-Autre { background: #f3e8ff; color: #6b21a5; }

        .btn-detail {
            display: inline-block;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: #0f766e;
            color: white;
            text-decoration: none;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-detail:hover {
            background: #0c5f58;
            transform: translateX(5px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 20px;
        }

        .empty-state .emoji {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                height: auto;
                padding: 1rem;
                gap: 0.5rem;
            }
            
            .navbar-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            .hero-section h1 {
                font-size: 1.8rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .section-heading {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">Green<span>Bite</span></a>
    <ul class="navbar-links">
        <li><a href="listEvenements.php" class="active">Événements</a></li>
        <!-- NOUVEAU LIEN VERS LA RECHERCHE AVANCÉE -->
        <li><a href="recherche-avancee.php">🔍 Recherche avancée</a></li>
    </ul>
    <div class="navbar-right">
        <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
    </div>
</nav>

<div class="hero-section">
    <h1>🌱 Découvrez les événements</h1>
    <p>Participez à des activités écologiques et communautaires</p>
    
    <!-- 
        ATTENTION : AUCUN ATTRIBUT HTML5 DE VALIDATION N'EST UTILISÉ !
        - PAS de "required"
        - PAS de "minlength"
        - PAS de "maxlength"
        - PAS de "pattern"
        Toute la validation est faite en PHP côté serveur !
    -->
    <form method="GET" action="listEvenements.php" class="search-wrapper">
        <input type="text" name="search" placeholder="🔍 Rechercher un événement..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>
</div>

<!-- Main Content -->
<div class="main-container">
    
    <div class="section-heading">
        <span>📅 Événements disponibles</span>
        <span class="results-count"><?= $eventsCount ?> événement(s) trouvé(s)</span>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <a href="listEvenements.php" class="filter-btn <?= empty($type) && empty($search) ? 'active' : '' ?>">Tous</a>
        <a href="listEvenements.php?type=Atelier" class="filter-btn <?= $type == 'Atelier' ? 'active' : '' ?>">🧑‍🍳 Ateliers</a>
        <a href="listEvenements.php?type=Conférence" class="filter-btn <?= $type == 'Conférence' ? 'active' : '' ?>">🎤 Conférences</a>
        <a href="listEvenements.php?type=Festival" class="filter-btn <?= $type == 'Festival' ? 'active' : '' ?>">🎉 Festivals</a>
        <a href="listEvenements.php?type=Autre" class="filter-btn <?= $type == 'Autre' ? 'active' : '' ?>">📌 Autres</a>
        <!-- Lien vers la recherche avancée dans la barre de filtres -->
        <a href="recherche-avancee.php" class="filter-btn">🔍 Filtres avancés</a>
    </div>

    <!-- Events Grid -->
    <div class="products-grid">
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <div class="emoji">📭</div>
                <h3>Aucun événement trouvé</h3>
                <?php if (!empty($search)): ?>
                    <p>Aucun résultat pour "<?= htmlspecialchars($search) ?>"</p>
                    <a href="listEvenements.php" style="color: #0f766e; display: inline-block; margin-top: 1rem;">
                        🔄 Voir tous les événements
                    </a>
                <?php else: ?>
                    <p>Revenez plus tard pour découvrir nos prochains événements !</p>
                    <a href="recherche-avancee.php" style="color: #0f766e; display: inline-block; margin-top: 1rem;">
                        🔍 Utiliser la recherche avancée
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach($events as $event): 
                // Validation des données de l'événement
                $eventId = isset($event['id']) ? (int)$event['id'] : 0;
                $eventTitre = isset($event['titre']) ? htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') : 'Sans titre';
                $eventLieu = isset($event['lieu']) ? htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') : 'Lieu non spécifié';
                $eventDate = isset($event['date_event']) ? $event['date_event'] : '';
                $eventType = isset($event['type']) ? $event['type'] : 'Autre';
                
                // Formatage de la date
                $formattedDate = '';
                if (!empty($eventDate)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
                    if ($dateObj && $dateObj->format('Y-m-d') === $eventDate) {
                        $formattedDate = $dateObj->format('d/m/Y');
                    } else {
                        $formattedDate = $eventDate;
                    }
                }
                
                // Icône selon le type
                $typeIcon = match($eventType) {
                    'Atelier' => '🧑‍🍳',
                    'Conférence' => '🎤',
                    'Festival' => '🎉',
                    default => '📌'
                };
            ?>
                <div class="product-card" onclick="window.location.href='showEvenement.php?id=<?= $eventId ?>'">
                    <div class="product-img">
                        <?= $typeIcon ?>
                    </div>
                    <div class="product-body">
                        <div class="product-name"><?= $eventTitre ?></div>
                        <div class="product-lieu">📍 <?= $eventLieu ?></div>
                        <div class="product-date">📆 <?= $formattedDate ?></div>
                        <div class="product-tags">
                            <span class="tag tag-<?= $eventType ?>"><?= $eventType ?></span>
                        </div>
                        <a href="showEvenement.php?id=<?= $eventId ?>" class="btn-detail">Voir détail →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>