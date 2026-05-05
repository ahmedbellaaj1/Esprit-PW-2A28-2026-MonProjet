<?php
require_once "../../controller/EvenementController.php";

$controller = new EvenementController();

// ==================== RÉCUPÉRATION ET VALIDATION DES FILTRES ====================

$filters = [
    'keyword' => isset($_GET['keyword']) ? trim($_GET['keyword']) : '',
    'type' => isset($_GET['type']) ? trim($_GET['type']) : 'all',
    'lieu' => isset($_GET['lieu']) ? trim($_GET['lieu']) : '',
    'organisateur_id' => isset($_GET['organisateur_id']) ? trim($_GET['organisateur_id']) : 'all',
    'date_debut' => isset($_GET['date_debut']) ? trim($_GET['date_debut']) : '',
    'date_fin' => isset($_GET['date_fin']) ? trim($_GET['date_fin']) : '',
    'statut' => isset($_GET['statut']) ? trim($_GET['statut']) : 'all',
    'tri' => isset($_GET['tri']) ? trim($_GET['tri']) : 'date_asc'
];

// Validation des dates
if (!empty($filters['date_debut']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_debut'])) {
    $filters['date_debut'] = '';
}
if (!empty($filters['date_fin']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_fin'])) {
    $filters['date_fin'] = '';
}

// Validation du statut
$validStatuts = ['all', 'upcoming', 'past', 'today'];
if (!in_array($filters['statut'], $validStatuts)) {
    $filters['statut'] = 'all';
}

// Validation du tri
$validTris = ['date_asc', 'date_desc', 'titre_asc', 'titre_desc', 'lieu_asc', 'type_asc', 'organisateur_asc'];
if (!in_array($filters['tri'], $validTris)) {
    $filters['tri'] = 'date_asc';
}

// Récupération des données pour les filtres
$events = $controller->searchAdvanced($filters);
$allTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
$allLieus = $controller->getAllLieus();
$allOrganisateurs = $controller->getAllOrganisateurs();

// Comptage des résultats
$resultsCount = count($events);

// Libellés pour le tri
$triLabels = [
    'date_asc' => '📅 Date (plus proche → plus loin)',
    'date_desc' => '📅 Date (plus loin → plus proche)',
    'titre_asc' => '🔤 Titre (A → Z)',
    'titre_desc' => '🔤 Titre (Z → A)',
    'lieu_asc' => '📍 Lieu (A → Z)',
    'type_asc' => '🏷️ Type (A → Z)',
    'organisateur_asc' => '👥 Organisateur (A → Z)'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche avancée - GreenBite</title>
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
        .navbar-links { display: flex; gap: 2rem; list-style: none; }
        .navbar-links a { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem; font-weight: 500; }
        .navbar-links a:hover, .navbar-links a.active { color: white; border-bottom: 2px solid white; }
        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.25); }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .page-header p {
            color: #64748b;
        }
        
        /* Filtres Section */
        .filters-section {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .filters-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .filter-group input,
        .filter-group select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.1);
        }
        .filter-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: #0f766e;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 9999px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #0c5f58;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 9999px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        /* Results Header */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .results-count {
            color: #64748b;
            font-size: 0.9rem;
        }
        .results-count strong {
            color: #0f766e;
            font-size: 1.2rem;
        }
        .sort-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 9999px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
        }
        
        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(15,118,110,0.15);
        }
        .event-img {
            height: 140px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
        }
        .event-body {
            padding: 1.25rem;
        }
        .event-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .event-info {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .event-tags {
            margin: 0.75rem 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .tag-type { background: #dcfce7; color: #166534; }
        .tag-lieu { background: #dbeafe; color: #1e40af; }
        .tag-organisateur { background: #fef3c7; color: #92400e; }
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
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-upcoming { background: #dcfce7; color: #166534; }
        .status-today { background: #fef3c7; color: #92400e; }
        .status-past { background: #f1f5f9; color: #64748b; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 20px;
        }
        .empty-state .emoji { font-size: 4rem; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 1.3rem; margin-bottom: 0.5rem; }
        .empty-state p { color: #64748b; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; height: auto; padding: 1rem; gap: 0.5rem; }
            .navbar-links { flex-wrap: wrap; justify-content: center; gap: 1rem; }
            .main-container { padding: 1rem; }
            .filters-grid { grid-template-columns: 1fr; }
            .results-header { flex-direction: column; align-items: flex-start; }
            .events-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">Green<span>Bite</span></a>
    <ul class="navbar-links">
        <li><a href="listEvenements.php">Événements</a></li>
        <li><a href="recherche-avancee.php" class="active">Recherche avancée</a></li>
    </ul>
    <div class="navbar-right">
        <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1>🔍 Recherche avancée</h1>
        <p>Affinez votre recherche avec des filtres multiples</p>
    </div>
    
    <!-- Formulaire de filtres - AUCUN attribut HTML5 de validation -->
    <form method="GET" action="recherche-avancee.php" class="filters-section">
        <div class="filters-title">🎯 Filtres de recherche</div>
        
        <div class="filters-grid">
            <!-- Mot-clé -->
            <div class="filter-group">
                <label>🔎 Mot-clé</label>
                <input type="text" name="keyword" placeholder="Titre, description, lieu..." value="<?= htmlspecialchars($filters['keyword']) ?>">
            </div>
            
            <!-- Type -->
            <div class="filter-group">
                <label>🏷️ Type</label>
                <select name="type">
                    <option value="all" <?= $filters['type'] == 'all' ? 'selected' : '' ?>>Tous les types</option>
                    <?php foreach ($allTypes as $t): ?>
                        <option value="<?= $t ?>" <?= $filters['type'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Lieu -->
            <div class="filter-group">
                <label>📍 Lieu</label>
                <select name="lieu">
                    <option value="" <?= empty($filters['lieu']) ? 'selected' : '' ?>>Tous les lieux</option>
                    <?php foreach ($allLieus as $l): ?>
                        <option value="<?= htmlspecialchars($l['lieu']) ?>" <?= $filters['lieu'] == $l['lieu'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['lieu']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Organisateur -->
            <div class="filter-group">
                <label>👥 Organisateur</label>
                <select name="organisateur_id">
                    <option value="all" <?= $filters['organisateur_id'] == 'all' ? 'selected' : '' ?>>Tous les organisateurs</option>
                    <?php foreach ($allOrganisateurs as $org): ?>
                        <option value="<?= $org['id'] ?>" <?= $filters['organisateur_id'] == $org['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($org['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Date début -->
            <div class="filter-group">
                <label>📅 Date début</label>
                <input type="text" name="date_debut" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($filters['date_debut']) ?>">
            </div>
            
            <!-- Date fin -->
            <div class="filter-group">
                <label>📅 Date fin</label>
                <input type="text" name="date_fin" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($filters['date_fin']) ?>">
            </div>
            
            <!-- Statut -->
            <div class="filter-group">
                <label>⏰ Statut</label>
                <select name="statut">
                    <option value="all" <?= $filters['statut'] == 'all' ? 'selected' : '' ?>>Tous</option>
                    <option value="upcoming" <?= $filters['statut'] == 'upcoming' ? 'selected' : '' ?>>À venir</option>
                    <option value="today" <?= $filters['statut'] == 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                    <option value="past" <?= $filters['statut'] == 'past' ? 'selected' : '' ?>>Passés</option>
                </select>
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn-primary">🔍 Appliquer les filtres</button>
            <a href="recherche-avancee.php" class="btn-secondary">🗑️ Réinitialiser</a>
        </div>
    </form>
    
    <!-- Résultats -->
    <div class="results-header">
        <div class="results-count">
            <strong><?= $resultsCount ?></strong> résultat(s) trouvé(s)
        </div>
        
        <!-- Tri personnalisé -->
        <form method="GET" action="recherche-avancee.php" id="sortForm">
            <?php foreach ($filters as $key => $value): ?>
                <?php if ($key != 'tri' && !empty($value)): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <select name="tri" class="sort-select" onchange="document.getElementById('sortForm').submit()">
                <?php foreach ($triLabels as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $filters['tri'] == $value ? 'selected' : '' ?>>
                        Trier par : <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <!-- Grille des événements -->
    <div class="events-grid">
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <div class="emoji">🔍</div>
                <h3>Aucun résultat trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
                <a href="recherche-avancee.php" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Réinitialiser les filtres</a>
            </div>
        <?php else: ?>
            <?php foreach($events as $event): 
                $eventDate = $event['date_event'];
                $today = date('Y-m-d');
                if ($eventDate == $today) {
                    $statusClass = 'status-today';
                    $statusText = "Aujourd'hui";
                } elseif ($eventDate >= $today) {
                    $statusClass = 'status-upcoming';
                    $statusText = 'À venir';
                } else {
                    $statusClass = 'status-past';
                    $statusText = 'Passé';
                }
                
                $typeIcon = match($event['type']) {
                    'Atelier' => '🧑‍🍳',
                    'Conférence' => '🎤',
                    'Festival' => '🎉',
                    default => '📌'
                };
            ?>
                <div class="event-card" onclick="window.location.href='showEvenement.php?id=<?= $event['id'] ?>'">
                    <div class="event-img">
                        <?= $typeIcon ?>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                    <div class="event-body">
                        <div class="event-title"><?= htmlspecialchars($event['titre']) ?></div>
                        <div class="event-info">📍 <?= htmlspecialchars($event['lieu']) ?></div>
                        <div class="event-info">📅 <?= date('d/m/Y', strtotime($event['date_event'])) ?></div>
                        <div class="event-tags">
                            <span class="tag tag-type">🏷️ <?= $event['type'] ?></span>
                            <span class="tag tag-organisateur">👥 <?= htmlspecialchars($event['organisateur_nom'] ?? 'Non défini') ?></span>
                        </div>
                        <a href="showEvenement.php?id=<?= $event['id'] ?>" class="btn-detail">Voir détail →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>