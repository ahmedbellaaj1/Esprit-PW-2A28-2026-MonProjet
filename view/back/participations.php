<?php
session_start();
require_once "../../controller/ParticipationController.php";
require_once "../../controller/EvenementController.php";

// Vérifier si l'utilisateur est administrateur (module user)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../front/listEvenements.php');
    exit();
}

$participationController = new ParticipationController();
$eventController = new EvenementController();

$events = $eventController->listEvenements();
$eventFilter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$statutFilter = isset($_GET['statut']) ? $_GET['statut'] : '';

if ($eventFilter > 0) {
    $participations = $participationController->getParticipantsByEvent($eventFilter);
} else {
    $participations = $participationController->getAllParticipations();
}

// Filtrage par statut
if (!empty($statutFilter) && $statutFilter !== 'all') {
    $participations = array_filter($participations, function($p) use ($statutFilter) {
        return $p['statut'] === $statutFilter;
    });
}

// Statistiques
$totalParticipations = count($participations);
$totalInscrits = count(array_filter($participations, fn($p) => $p['statut'] === 'inscrit'));
$totalPresents = count(array_filter($participations, fn($p) => $p['statut'] === 'present'));
$totalAnnules = count(array_filter($participations, fn($p) => $p['statut'] === 'annule'));

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
    <title>Participations - GreenBite Admin</title>
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
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .sidebar-logo {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        
        .sidebar-logo-text h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            margin: 0;
            line-height: 1.2;
        }
        
        .sidebar-logo-text span {
            color: #99f6e4;
        }
        
        .sidebar-logo-text p {
            font-size: 0.7rem;
            opacity: 0.7;
            margin: 0;
            margin-top: 2px;
        }
        
        .sidebar-logo-img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
        }
        
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
        .stat-value { font-size: 2rem; font-weight: 700; color: #0f172a; }
        .stat-label { color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; }
        
        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            min-width: 250px;
            background: white;
        }
        .filter-select:focus {
            outline: none;
            border-color: #14b8a6;
        }
        .btn-filter {
            background: #0f766e;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-filter:hover {
            background: #0c5f58;
            transform: translateY(-2px);
        }
        .btn-reset {
            background: #64748b;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            background: #475569;
            transform: translateY(-2px);
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        thead { background: #f8fafc; }
        th { padding: 1rem 1.25rem; text-align: left; font-weight: 600; color: #0f172a; font-size: 0.85rem; }
        td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tbody tr:hover { background: #f8fafc; }
        
        /* Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-inscrit { background: #dbeafe; color: #1e40af; }
        .status-present { background: #dcfce7; color: #166534; }
        .status-annule { background: #fee2e2; color: #991b1b; }
        .status-en_attente { background: #fef3c7; color: #92400e; }
        
        /* Action Buttons */
        .actions { display: flex; gap: 0.5rem; }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        .btn-edit { background: #dbeafe; color: #1e40af; }
        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .btn-delete:hover { background: #fecaca; }
        
        /* Export button */
        .btn-export {
            background: #14b8a6;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-export:hover {
            background: #0f766e;
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 3rem; }
        .empty-state .emoji { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 1.2rem; color: #0f172a; margin-bottom: 0.5rem; }
        .empty-state p { color: #64748b; }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; margin-top: 4rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .filter-bar { flex-direction: column; }
            .filter-select { width: 100%; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-wrapper">
                    <div class="sidebar-logo-text">
                        <h2>Green<span>Bite</span></h2>
                        <p>Administration</p>
                    </div>
                    <img src="../../assets/images/logo.png" alt="GreenBite" class="sidebar-logo-img">
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboardEvenement.php" class="sidebar-link">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="stats.php" class="sidebar-link">
                    <span class="icon">📈</span>
                    <span>Statistiques</span>
                </a>
                <a href="participations.php" class="sidebar-link active">
                    <span class="icon">👥</span>
                    <span>Participations</span>
                </a>
                <a href="organisateurs.php" class="sidebar-link">
                    <span class="icon">👥</span>
                    <span>Organisateurs</span>
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
                <h1>👥 Gestion des participations</h1>
                <p>Gérez les inscriptions des utilisateurs aux événements</p>
            </div>

            <?php if ($message): ?>
                <div class="alert">
                    ✅ <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?= $totalParticipations ?></div>
                    <div class="stat-label">Total inscriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?= $totalInscrits ?></div>
                    <div class="stat-label">Inscrits</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= $totalPresents ?></div>
                    <div class="stat-label">Présents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value"><?= $totalAnnules ?></div>
                    <div class="stat-label">Annulés</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="filter-bar">
                <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                    <select name="event_id" class="filter-select">
                        <option value="0">📅 Tous les événements</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['id'] ?>" <?= $eventFilter == $event['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['titre']) ?> - <?= date('d/m/Y', strtotime($event['date_event'])) ?> (<?= htmlspecialchars($event['lieu']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="statut" class="filter-select">
                        <option value="all">📊 Tous les statuts</option>
                        <option value="inscrit" <?= $statutFilter == 'inscrit' ? 'selected' : '' ?>>✅ Inscrits</option>
                        <option value="present" <?= $statutFilter == 'present' ? 'selected' : '' ?>>🎉 Présents</option>
                        <option value="en_attente" <?= $statutFilter == 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                        <option value="annule" <?= $statutFilter == 'annule' ? 'selected' : '' ?>>❌ Annulés</option>
                    </select>
                    
                    <button type="submit" class="btn-filter">🔍 Filtrer</button>
                    
                    <?php if ($eventFilter > 0 || !empty($statutFilter)): ?>
                        <a href="participations.php" class="btn-reset">🗑️ Réinitialiser</a>
                    <?php endif; ?>
                    
                    <?php if ($eventFilter > 0): ?>
                        <a href="../../controller/export_dispatcher.php?action=export_participations&event_id=<?= $eventFilter ?>" class="btn-export">
                            📥 Exporter CSV
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tableau des participations -->
            <div class="table-container">
                <table id="participationsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Événement</th>
                            <th>Date événement</th>
                            <th>Date inscription</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        <tr>
                    </thead>
                    <tbody>
                        <?php if (empty($participations)): ?>
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <div class="emoji">📭</div>
                                    <h3>Aucune participation</h3>
                                    <p>Aucune inscription trouvée pour ces critères</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($participations as $p): 
                                $statusClass = match($p['statut']) {
                                    'inscrit' => 'status-inscrit',
                                    'present' => 'status-present',
                                    'annule' => 'status-annule',
                                    default => 'status-en_attente'
                                };
                                $statusLabel = match($p['statut']) {
                                    'inscrit' => '✅ Inscrit',
                                    'present' => '🎉 Présent',
                                    'annule' => '❌ Annulé',
                                    'en_attente' => '⏳ En attente',
                                    default => $p['statut']
                                };
                                
                                $userFullName = '';
                                if (isset($p['prenom']) && isset($p['nom'])) {
                                    $userFullName = trim($p['prenom'] . ' ' . $p['nom']);
                                } elseif (isset($p['user_nom'])) {
                                    $userFullName = $p['user_nom'];
                                } elseif (isset($p['nom'])) {
                                    $userFullName = $p['nom'];
                                } else {
                                    $userFullName = 'N/A';
                                }
                                
                                $userEmail = $p['user_email'] ?? $p['email'] ?? 'N/A';
                            ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($userFullName) ?></strong></td>
                                <td><?= htmlspecialchars($userEmail) ?></td>
                                <td><?= htmlspecialchars($p['event_titre'] ?? 'N/A') ?></td>
                                <td><?= isset($p['date_event']) ? date('d/m/Y', strtotime($p['date_event'])) : '-' ?></td>
                                <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td class="actions">
                                    <a href="editParticipation.php?id=<?= $p['id'] ?>" class="btn-sm btn-edit" title="Modifier">✏️</a>
                                    <a href="deleteParticipation.php?id=<?= $p['id'] ?>" class="btn-sm btn-delete" title="Supprimer" onclick="return confirm('⚠️ Supprimer cette participation ? Cette action est irréversible.')">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>