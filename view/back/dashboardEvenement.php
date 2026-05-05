<?php
session_start();
require_once "../../controller/EvenementController.php";

$controller = new EvenementController();
$events = $controller->listEvenements();
$stats = $controller->getStats();

// Validation PHP du message de session
$message = '';
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = trim(htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'));
    unset($_SESSION['message']);
}

// Validation des données statistiques
$totalEvents = isset($stats['total']) ? (int)$stats['total'] : 0;
$upcomingEvents = isset($stats['upcoming']) ? (int)$stats['upcoming'] : 0;
$pastEvents = $totalEvents - $upcomingEvents;
$typesCount = isset($stats['byType']) ? count($stats['byType']) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GreenBite Admin</title>
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
        .sidebar-logo { padding: 2rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1.5rem; }
        .sidebar-logo h2 { font-size: 1.5rem; font-weight: 700; color: white; }
        .sidebar-logo span { color: #99f6e4; }
        .sidebar-logo p { font-size: 0.75rem; opacity: 0.7; margin-top: 0.5rem; }
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
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(15,118,110,0.15); }
        .stat-icon {
            width: 48px;
            height: 48px;
            background: #ccfbf1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-value { font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem; }
        .stat-label { color: #64748b; font-size: 0.85rem; }
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn-add {
            background: #0f766e;
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-add:hover { background: #0c5f58; transform: scale(1.02); }
        
        /* Boutons d'export */
        .btn-export {
            background: #14b8a6;
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn-export:hover {
            background: #0f766e;
            transform: scale(1.02);
        }
        
        /* Dropdown menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: white;
            min-width: 220px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 12px;
            z-index: 1;
            overflow: hidden;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            padding: 12px 16px;
            display: block;
            text-decoration: none;
            color: #334155;
            font-size: 0.85rem;
            transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }
        .dropdown-content a:hover {
            background: #f8fafc;
            color: #0f766e;
        }
        .dropdown-content a:last-child {
            border-bottom: none;
        }
        
        /* PAS d'attributs HTML5 de validation sur la recherche */
        .search-box {
            padding: 0.75rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 9999px;
            width: 300px;
            font-family: 'Inter', sans-serif;
        }
        .search-box:focus { outline: none; border-color: #14b8a6; }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        thead { background: #f8fafc; }
        th { padding: 1rem 1.25rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.85rem; }
        td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tbody tr:hover { background: #f8fafc; }
        
        /* Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-upcoming { background: #dcfce7; color: #166534; }
        .status-past { background: #f1f5f9; color: #64748b; }
        .status-today { background: #fef3c7; color: #92400e; }
        .type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .type-Atelier { background: #dcfce7; color: #166534; }
        .type-Conférence { background: #dbeafe; color: #1e40af; }
        .type-Festival { background: #fef3c7; color: #92400e; }
        .type-Autre { background: #f3e8ff; color: #6b21a5; }
        
        /* Action buttons */
        .actions { display: flex; gap: 0.5rem; }
        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-edit { background: #dbeafe; color: #1e40af; }
        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .btn-delete:hover { background: #fecaca; }
        
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 200;
            background: #0f766e;
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        .empty-state .emoji { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 1.2rem; color: #0f172a; margin-bottom: 0.5rem; }
        .empty-state p { color: #64748b; }
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .mobile-menu-btn { display: flex; align-items: center; justify-content: center; }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1rem; margin-top: 4rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-bar { flex-direction: column; }
            .search-box { width: 100%; }
            .dropdown-content { right: 0; left: auto; }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <h2>Green<span>Bite</span></h2>
                <p>Administration</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboardEvenement.php" class="sidebar-link active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="stats.php" class="sidebar-link">
                    <span class="icon">📈</span>
                    <span>Statistiques</span>
                </a>
                <!-- NOUVEAU LIEN VERS LA RECHERCHE AVANCÉE -->
                <a href="../front/recherche-avancee.php" class="sidebar-link">
                    <span class="icon">🔍</span>
                    <span>Recherche avancée</span>
                </a>
                <a href="addEvenement.php" class="sidebar-link">
                    <span class="icon">➕</span>
                    <span>Ajouter un événement</span>
                </a>
                <a href="organisateurs.php" class="sidebar-link">
                    <span class="icon">👥</span>
                    <span>Organisateurs</span>
                </a>
                <a href="../front/listEvenements.php" class="sidebar-link">
                    <span class="icon">🌍</span>
                    <span>Voir le site</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard Administrateur</h1>
                <p>Gérez les événements GreenBite</p>
            </div>

            <?php if ($message): ?>
                <div class="alert">
                    ✅ <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Cartes de statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?= $totalEvents ?></div>
                    <div class="stat-label">Total événements</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value"><?= $upcomingEvents ?></div>
                    <div class="stat-label">Événements à venir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= $pastEvents ?></div>
                    <div class="stat-label">Événements passés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏷️</div>
                    <div class="stat-value"><?= $typesCount ?></div>
                    <div class="stat-label">Types d'événements</div>
                </div>
            </div>

            <!-- Barre d'actions avec boutons d'export -->
            <div class="action-bar">
                <a href="addEvenement.php" class="btn-add">➕ Ajouter un événement</a>
                
                <!-- Menu déroulant d'export -->
                <div class="dropdown">
                    <button class="btn-export">📥 Exporter</button>
                    <div class="dropdown-content">
                        <a href="../../controller/export_dispatcher.php?action=export_events_csv">
                            📄 CSV - Événements
                        </a>
                        <a href="../../controller/export_dispatcher.php?action=export_events_excel">
                            📊 Excel - Événements
                        </a>
                        <a href="../../controller/export_dispatcher.php?action=export_stats_html">
                            📈 Rapport statistiques
                        </a>
                    </div>
                </div>
                
                <!-- Champ de recherche sans attributs HTML5 de validation -->
                <input type="text" id="searchInput" class="search-box" placeholder="🔍 Rechercher un événement...">
            </div>

            <!-- Tableau des événements -->
            <div class="table-container">
                <table id="eventsTable">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Type</th>
                            <th>Organisateur</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="emoji">📭</div>
                                    <h3>Aucun événement trouvé</h3>
                                    <p>Cliquez sur "Ajouter un événement" pour commencer</p>
                                    <a href="addEvenement.php" style="color:#0f766e; display:inline-block; margin-top:0.5rem;">➕ Ajouter votre premier événement</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($events as $event): 
                                // Validation PHP de la date
                                $eventDate = isset($event['date_event']) ? $event['date_event'] : '';
                                $today = date('Y-m-d');
                                $isUpcoming = ($eventDate >= $today);
                                $isToday = ($eventDate == $today);
                                
                                // Déterminer le statut
                                if ($isToday) {
                                    $statusClass = 'status-today';
                                    $statusIcon = '🔴';
                                    $statusText = "Aujourd'hui";
                                } elseif ($isUpcoming) {
                                    $statusClass = 'status-upcoming';
                                    $statusIcon = '📅';
                                    $statusText = 'À venir';
                                } else {
                                    $statusClass = 'status-past';
                                    $statusIcon = '✅';
                                    $statusText = 'Passé';
                                }
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($event['titre']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($event['date_event'])) ?></td>
                                <td><?= htmlspecialchars($event['lieu']) ?></td>
                                <td><span class="type-badge type-<?= htmlspecialchars($event['type']) ?>"><?= htmlspecialchars($event['type']) ?></span></td>
                                <td><?= htmlspecialchars($event['organisateur_nom'] ?? 'Non défini') ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= $statusText ?></span></td>
                                <td class="actions">
                                    <a href="editEvenement.php?id=<?= (int)$event['id'] ?>" class="btn-edit">✏️ Modifier</a>
                                    <a href="deleteEvenement.php?id=<?= (int)$event['id'] ?>" class="btn-delete" onclick="return confirm('⚠️ Supprimer cet événement ? Cette action est irréversible.')">🗑️ Supprimer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Recherche côté client (aucune validation HTML5)
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        
        // Fermer la sidebar sur clic externe (mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Recherche en temps réel (JavaScript pur, pas de validation HTML5)
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                let searchValue = this.value.toLowerCase();
                let rows = document.querySelectorAll('#eventsTable tbody tr');
                
                rows.forEach(row => {
                    // Vérifier si la ligne n'est pas la ligne "vide"
                    if (row.querySelector('.empty-state')) {
                        return;
                    }
                    let text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>