<?php
session_start();
require_once "../../controller/EvenementController.php";

$controller = new EvenementController();
$events = $controller->listEvenements();
$stats = $controller->getStats();

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
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
                <a href="addEvenement.php" class="sidebar-link">
                    <span class="icon">➕</span>
                    <span>Ajouter un événement</span>
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
                <div class="alert">✅ <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Total événements</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value"><?= $stats['upcoming'] ?? 0 ?></div>
                    <div class="stat-label">Événements à venir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= ($stats['total'] ?? 0) - ($stats['upcoming'] ?? 0) ?></div>
                    <div class="stat-label">Événements passés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏷️</div>
                    <div class="stat-value"><?= count($stats['byType'] ?? []) ?></div>
                    <div class="stat-label">Types d'événements</div>
                </div>
            </div>

            <div class="action-bar">
                <a href="addEvenement.php" class="btn-add">➕ Ajouter un événement</a>
                <input type="text" id="searchInput" class="search-box" placeholder="🔍 Rechercher un événement...">
            </div>

            <div class="table-container">
                <table id="eventsTable">
                    <thead>
                        <tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Type</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:3rem;">📭 Aucun événement trouvé<br><a href="addEvenement.php" style="color:#0f766e;">➕ Ajouter votre premier événement</a></td></tr>
                        <?php else: ?>
                            <?php foreach($events as $event): 
                                $isUpcoming = $event['date_event'] >= date('Y-m-d');
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($event['titre']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($event['date_event'])) ?></td>
                                <td><?= htmlspecialchars($event['lieu']) ?></td>
                                <td><span class="type-badge type-<?= $event['type'] ?>"><?= $event['type'] ?></span></td>
                                <td><span class="status-badge <?= $isUpcoming ? 'status-upcoming' : 'status-past' ?>"><?= $isUpcoming ? '📅 À venir' : '✅ Passé' ?></span></td>
                                <td class="actions">
                                    <a href="editEvenement.php?id=<?= $event['id'] ?>" class="btn-edit">✏️ Modifier</a>
                                    <a href="deleteEvenement.php?id=<?= $event['id'] ?>" class="btn-delete" onclick="return confirm('⚠️ Supprimer cet événement ?')">🗑️ Supprimer</a>
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
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchValue = this.value.toLowerCase();
            let rows = document.querySelectorAll('#eventsTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>