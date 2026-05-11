<?php
/**
 * Green-Bite Front-Office - Liste des Événements
 * Utilise bootstrap.php et navbar.php de Green-Bite
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../../Controller/EvenementController.php';

$controller = new EvenementController();

// Récupération et validation des paramètres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type   = isset($_GET['type'])   ? trim($_GET['type'])   : '';

if (!empty($search)) {
    $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
    $events = $controller->searchEvents($search);
} elseif (!empty($type) && $type != 'all') {
    $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
    $events = in_array($type, $validTypes) ? $controller->getEventsByType($type) : $controller->getUpcomingEvents();
} else {
    $events = $controller->getUpcomingEvents();
}

if (!is_array($events)) $events = [];
$eventsCount = count($events);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements Communautaires - GreenBite</title>
    <meta name="description" content="Découvrez et participez aux événements écologiques et communautaires de GreenBite : ateliers, conférences, festivals.">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-hero {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 60%, #5eead4 100%);
            padding: 4rem 2rem 3rem;
            text-align: center;
            color: white;
        }
        .ev-hero h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
        .ev-hero p { font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto; }
        .ev-search-wrapper {
            display: flex; max-width: 500px; margin: 1.5rem auto 0;
            background: white; border-radius: 9999px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .ev-search-wrapper input {
            flex: 1; border: none; padding: 0.9rem 1.5rem; font-size: 0.95rem;
            font-family: 'Inter', sans-serif; outline: none;
        }
        .ev-search-wrapper button {
            background: #0f766e; color: white; border: none; padding: 0.9rem 1.8rem;
            cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: background 0.3s;
        }
        .ev-search-wrapper button:hover { background: #0c5f58; }
        .ev-main { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .ev-section-heading {
            font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 0.75rem; justify-content: space-between; flex-wrap: wrap;
        }
        .ev-results-count { font-size: 0.85rem; color: #64748b; font-weight: normal; }
        .ev-filter-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .ev-filter-btn {
            padding: 0.6rem 1.5rem; border-radius: 9999px; border: 2px solid #14b8a6;
            background: white; color: #0f766e; font-size: 0.85rem; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif;
            text-decoration: none; display: inline-block;
        }
        .ev-filter-btn:hover, .ev-filter-btn.active {
            background: #0f766e; color: white; border-color: #0f766e; transform: translateY(-2px);
        }
        .ev-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem; margin-bottom: 2rem;
        }
        .ev-card {
            background: white; border-radius: 20px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;
        }
        .ev-card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(15,118,110,0.15); }
        .ev-card-img {
            height: 140px; background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex; align-items: center; justify-content: center; font-size: 3rem;
        }
        .ev-card-body { padding: 1.25rem; }
        .ev-card-name { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; }
        .ev-card-meta { font-size: 0.85rem; color: #64748b; margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.4rem; }
        .ev-tag {
            display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600; margin: 0.5rem 0;
        }
        .ev-tag-Atelier { background: #dcfce7; color: #166534; }
        .ev-tag-Conférence { background: #dbeafe; color: #1e40af; }
        .ev-tag-Festival { background: #fef3c7; color: #92400e; }
        .ev-tag-Autre { background: #f3e8ff; color: #6b21a5; }
        .ev-btn-detail {
            display: inline-block; margin-top: 0.75rem; padding: 0.5rem 1rem;
            background: #0f766e; color: white; text-decoration: none; border-radius: 9999px;
            font-size: 0.8rem; font-weight: 500; transition: all 0.3s ease;
        }
        .ev-btn-detail:hover { background: #0c5f58; transform: translateX(5px); }
        .ev-empty {
            text-align: center; padding: 4rem; background: white; border-radius: 20px;
            grid-column: 1/-1;
        }
        .ev-empty .emoji { font-size: 4rem; margin-bottom: 1rem; }
        .ev-empty h3 { font-size: 1.3rem; margin-bottom: 0.5rem; }
        .ev-empty p { color: #64748b; }
        @media (max-width: 768px) {
            .ev-hero h1 { font-size: 1.8rem; }
            .ev-main { padding: 1rem; }
            .ev-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-hero">
    <h1>🌱 Événements Communautaires</h1>
    <p>Participez à des activités écologiques et communautaires GreenBite</p>
    <form method="GET" action="/Green-Bite/View/front-office/evenements/listEvenements.php" class="ev-search-wrapper">
        <input type="text" name="search" placeholder="🔍 Rechercher un événement..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>
</div>

<div class="ev-main">
    <div class="ev-section-heading">
        <span>📅 Événements disponibles</span>
        <span class="ev-results-count"><?= $eventsCount ?> événement(s) trouvé(s)</span>
    </div>

    <div class="ev-filter-bar">
        <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" class="ev-filter-btn <?= empty($type) && empty($search) ? 'active' : '' ?>">Tous</a>
        <a href="?type=Atelier"    class="ev-filter-btn <?= $type == 'Atelier'    ? 'active' : '' ?>">🧑‍🍳 Ateliers</a>
        <a href="?type=Conférence" class="ev-filter-btn <?= $type == 'Conférence' ? 'active' : '' ?>">🎤 Conférences</a>
        <a href="?type=Festival"   class="ev-filter-btn <?= $type == 'Festival'   ? 'active' : '' ?>">🎉 Festivals</a>
        <a href="?type=Autre"      class="ev-filter-btn <?= $type == 'Autre'      ? 'active' : '' ?>">📌 Autres</a>
        <a href="/Green-Bite/View/front-office/evenements/recherche-avancee.php" class="ev-filter-btn">🔍 Recherche avancée</a>
        <?php if (isLoggedIn()): ?>
        <a href="/Green-Bite/View/front-office/evenements/mes-participations.php" class="ev-filter-btn">📋 Mes participations</a>
        <?php endif; ?>
    </div>

    <div class="ev-grid">
        <?php if (empty($events)): ?>
            <div class="ev-empty">
                <div class="emoji">📭</div>
                <h3>Aucun événement trouvé</h3>
                <?php if (!empty($search)): ?>
                    <p>Aucun résultat pour "<?= htmlspecialchars($search) ?>"</p>
                    <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" style="color:#0f766e;display:inline-block;margin-top:1rem;">🔄 Voir tous les événements</a>
                <?php else: ?>
                    <p>Revenez plus tard pour découvrir nos prochains événements !</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach($events as $event):
                $eventId   = isset($event['id'])         ? (int)$event['id'] : 0;
                $eventTitre= isset($event['titre'])      ? htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') : 'Sans titre';
                $eventLieu = isset($event['lieu'])       ? htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') : '';
                $eventDate = isset($event['date_event']) ? $event['date_event'] : '';
                $eventType = isset($event['type'])       ? $event['type'] : 'Autre';
                $formattedDate = '';
                if (!empty($eventDate)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
                    if ($dateObj) $formattedDate = $dateObj->format('d/m/Y');
                }
                $typeIcon = match($eventType) {
                    'Atelier'    => '🧑‍🍳',
                    'Conférence' => '🎤',
                    'Festival'   => '🎉',
                    default      => '📌'
                };
            ?>
                <div class="ev-card" onclick="window.location.href='showEvenement.php?id=<?= $eventId ?>'">
                    <div class="ev-card-img"><?= $typeIcon ?></div>
                    <div class="ev-card-body">
                        <div class="ev-card-name"><?= $eventTitre ?></div>
                        <div class="ev-card-meta">📍 <?= $eventLieu ?></div>
                        <div class="ev-card-meta">📆 <?= $formattedDate ?></div>
                        <div><span class="ev-tag ev-tag-<?= $eventType ?>"><?= $eventType ?></span></div>
                        <a href="showEvenement.php?id=<?= $eventId ?>" class="ev-btn-detail">Voir détail →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/chatbot_widget.php'; ?>
</body>
</html>
