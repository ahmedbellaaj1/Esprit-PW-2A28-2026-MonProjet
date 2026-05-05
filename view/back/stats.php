<?php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../controller/OrganisateurController.php";

$eventController = new EvenementController();
$organisateurController = new OrganisateurController();

// Récupération des données
$events = $eventController->listEvenements();
$organisateurs = $organisateurController->listOrganisateurs();

// ==================== CALCUL DES STATISTIQUES AVANCÉES ====================

$stats = [];

// 1. Statistiques générales
$stats['total_events'] = is_array($events) ? count($events) : 0;
$stats['total_organisateurs'] = is_array($organisateurs) ? count($organisateurs) : 0;

// 2. Événements par mois
$eventsByMonth = [];
$eventsByType = [];
$eventsByLieu = [];
$upcomingCount = 0;
$pastCount = 0;
$todayCount = 0;
$currentMonthEvents = 0;
$nextMonthEvents = 0;

$currentMonth = date('Y-m');
$nextMonth = date('Y-m', strtotime('+1 month'));

foreach ($events as $event) {
    // Par mois
    $month = date('Y-m', strtotime($event['date_event']));
    if (!isset($eventsByMonth[$month])) {
        $eventsByMonth[$month] = 0;
    }
    $eventsByMonth[$month]++;
    
    // Comptage mois actuel et prochain
    if ($month == $currentMonth) $currentMonthEvents++;
    if ($month == $nextMonth) $nextMonthEvents++;
    
    // Par type
    $type = $event['type'];
    if (!isset($eventsByType[$type])) {
        $eventsByType[$type] = 0;
    }
    $eventsByType[$type]++;
    
    // Par lieu
    $lieu = $event['lieu'];
    if (!isset($eventsByLieu[$lieu])) {
        $eventsByLieu[$lieu] = 0;
    }
    $eventsByLieu[$lieu]++;
    
    // Statut (à venir, passé, aujourd'hui)
    if ($event['date_event'] == date('Y-m-d')) {
        $todayCount++;
    } elseif ($event['date_event'] >= date('Y-m-d')) {
        $upcomingCount++;
    } else {
        $pastCount++;
    }
}

// 3. Tendance mensuelle
$previousMonth = date('Y-m', strtotime('-1 month'));
$trend = isset($eventsByMonth[$currentMonth]) && isset($eventsByMonth[$previousMonth]) 
    ? $eventsByMonth[$currentMonth] - $eventsByMonth[$previousMonth] 
    : (isset($eventsByMonth[$currentMonth]) ? $eventsByMonth[$currentMonth] : 0);

// 4. Taux d'occupation
$occupationRate = $stats['total_events'] > 0 ? round(($upcomingCount / $stats['total_events']) * 100) : 0;

// 5. Organisateur le plus actif
$topOrganisateur = null;
$maxEvents = 0;
foreach ($organisateurs as $org) {
    $count = $eventController->countEventsByOrganisateur($org['id']);
    if ($count > $maxEvents) {
        $maxEvents = $count;
        $topOrganisateur = $org;
    }
}

// 6. Type d'événement le plus populaire
$topType = '';
$maxType = 0;
foreach ($eventsByType as $type => $count) {
    if ($count > $maxType) {
        $maxType = $count;
        $topType = $type;
    }
}

// 7. Lieu le plus populaire
$topLieu = '';
$maxLieu = 0;
foreach ($eventsByLieu as $lieu => $count) {
    if ($count > $maxLieu) {
        $maxLieu = $count;
        $topLieu = $lieu;
    }
}

// 8. Prochains événements (5 prochains)
$nextEvents = $eventController->getNextEvents(5);
if (!is_array($nextEvents)) $nextEvents = [];

// 9. Évolution mensuelle (pourcentage)
$monthlyEvolution = 0;
if (isset($eventsByMonth[$previousMonth]) && $eventsByMonth[$previousMonth] > 0) {
    $monthlyEvolution = round((($currentMonthEvents - $eventsByMonth[$previousMonth]) / $eventsByMonth[$previousMonth]) * 100);
} elseif ($currentMonthEvents > 0) {
    $monthlyEvolution = 100;
}

// 10. Score de santé de la plateforme (0-100)
$healthScore = 0;
if ($stats['total_organisateurs'] > 0) {
    $healthScore = min(100, round(
        ($stats['total_events'] * 0.4) + 
        ($upcomingCount * 0.3) + 
        ($stats['total_organisateurs'] * 0.3)
    ));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - GreenBite Admin</title>
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
        .trend-up { color: #16a34a; }
        .trend-down { color: #dc2626; }
        
        /* Health Score */
        .health-card {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .health-score {
            font-size: 3rem;
            font-weight: 800;
        }
        
        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .chart-card h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #0f172a;
            border-left: 4px solid #14b8a6;
            padding-left: 0.75rem;
        }
        .chart-bar {
            margin-bottom: 1rem;
        }
        .chart-bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        .chart-bar-fill {
            background: #14b8a6;
            height: 8px;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        /* Top Lists */
        .top-list {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .top-list h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #0f172a;
            border-left: 4px solid #f59e0b;
            padding-left: 0.75rem;
        }
        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .top-item:last-child { border-bottom: none; }
        .top-rank {
            font-weight: 700;
            color: #0f766e;
            width: 30px;
        }
        .top-name { flex: 1; }
        .top-count {
            background: #e2e8f0;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .insight-text {
            background: #f0fdfa;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1rem;
            border-left: 4px solid #14b8a6;
        }
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>Green<span>Bite</span></h2>
                <p>Administration</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboardEvenement.php" class="sidebar-link">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="stats.php" class="sidebar-link active">
                    <span class="icon">📈</span>
                    <span>Statistiques</span>
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
                <h1>📈 Statistiques avancées</h1>
                <p>Analyse détaillée de votre plateforme événementielle</p>
            </div>

            <!-- Health Score -->
            <div class="health-card">
                <div style="font-size: 0.85rem; opacity: 0.8;">Santé de la plateforme</div>
                <div class="health-score"><?= $healthScore ?>%</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">
                    <?php if ($healthScore >= 80): ?>
                        🎉 Excellente santé !
                    <?php elseif ($healthScore >= 50): ?>
                        👍 Bonne dynamique
                    <?php else: ?>
                        📈 À développer
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?= $stats['total_events'] ?></div>
                    <div class="stat-label">Total événements</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value"><?= $upcomingCount ?></div>
                    <div class="stat-label">À venir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= $pastCount ?></div>
                    <div class="stat-label">Passés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?= $stats['total_organisateurs'] ?></div>
                    <div class="stat-label">Organisateurs</div>
                </div>
            </div>

            <!-- Tendance mensuelle -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value">
                        <?= $currentMonthEvents ?>
                        <span style="font-size: 0.9rem;">
                            <?php if ($trend > 0): ?>
                                <span class="trend-up">▲ +<?= $trend ?></span>
                            <?php elseif ($trend < 0): ?>
                                <span class="trend-down">▼ <?= $trend ?></span>
                            <?php else: ?>
                                <span class="trend-up">◼ stable</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="stat-label">Événements ce mois-ci</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value"><?= $nextMonthEvents ?></div>
                    <div class="stat-label">Événements mois prochain</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value"><?= $occupationRate ?>%</div>
                    <div class="stat-label">Taux d'occupation</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📉</div>
                    <div class="stat-value">
                        <?= $monthlyEvolution >= 0 ? '+' : '' ?><?= $monthlyEvolution ?>%
                    </div>
                    <div class="stat-label">Évolution mensuelle</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-row">
                <!-- Événements par type -->
                <div class="chart-card">
                    <h3>🎯 Événements par type</h3>
                    <?php
                    $maxTypeCount = max($eventsByType) ?: 1;
                    foreach ($eventsByType as $type => $count):
                        $percentage = round(($count / $maxTypeCount) * 100);
                    ?>
                        <div class="chart-bar">
                            <div class="chart-bar-label">
                                <span>
                                    <?= $type == 'Atelier' ? '🧑‍🍳' : ($type == 'Conférence' ? '🎤' : ($type == 'Festival' ? '🎉' : '📌')) ?>
                                    <?= $type ?>
                                </span>
                                <span><?= $count ?> événement(s)</span>
                            </div>
                            <div class="chart-bar-fill" style="width: <?= $percentage ?>%; background: <?= $type == 'Atelier' ? '#166534' : ($type == 'Conférence' ? '#1e40af' : ($type == 'Festival' ? '#92400e' : '#6b21a5')) ?>;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Événements par lieu -->
                <div class="chart-card">
                    <h3>📍 Top lieux</h3>
                    <?php
                    $maxLieuCount = max($eventsByLieu) ?: 1;
                    $lieuCount = 0;
                    foreach ($eventsByLieu as $lieu => $count):
                        if ($lieuCount++ >= 5) break;
                        $percentage = round(($count / $maxLieuCount) * 100);
                    ?>
                        <div class="chart-bar">
                            <div class="chart-bar-label">
                                <span>📍 <?= htmlspecialchars($lieu) ?></span>
                                <span><?= $count ?> événement(s)</span>
                            </div>
                            <div class="chart-bar-fill" style="width: <?= $percentage ?>%; background: #f59e0b;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Lists -->
            <div class="charts-row">
                <!-- Top organisateur -->
                <div class="top-list">
                    <h3>🏆 Organisateur le plus actif</h3>
                    <?php if ($topOrganisateur): ?>
                        <div class="top-item">
                            <span class="top-rank">🏅</span>
                            <span class="top-name"><strong><?= htmlspecialchars($topOrganisateur['nom']) ?></strong></span>
                            <span class="top-count"><?= $maxEvents ?> événement(s)</span>
                        </div>
                        <div class="top-item">
                            <span class="top-rank">📧</span>
                            <span class="top-name"><?= htmlspecialchars($topOrganisateur['email']) ?></span>
                        </div>
                        <div class="top-item">
                            <span class="top-rank">📞</span>
                            <span class="top-name"><?= htmlspecialchars($topOrganisateur['telephone']) ?></span>
                        </div>
                    <?php else: ?>
                        <p style="color: #64748b; text-align: center;">Aucun organisateur enregistré</p>
                    <?php endif; ?>
                </div>

                <!-- Top type et lieu -->
                <div class="top-list">
                    <h3>🏆 Palmarès</h3>
                    <div class="top-item">
                        <span class="top-rank">🥇</span>
                        <span class="top-name">Type le plus populaire</span>
                        <span class="top-count">
                            <?= $topType ?: 'Aucun' ?>
                            <?php if ($topType): ?>(<?= $maxType ?> évts)<?php endif; ?>
                        </span>
                    </div>
                    <div class="top-item">
                        <span class="top-rank">🥈</span>
                        <span class="top-name">Lieu le plus populaire</span>
                        <span class="top-count">
                            <?= $topLieu ?: 'Aucun' ?>
                            <?php if ($topLieu): ?>(<?= $maxLieu ?> évts)<?php endif; ?>
                        </span>
                    </div>
                    <div class="top-item">
                        <span class="top-rank">🥉</span>
                        <span class="top-name">Événements aujourd'hui</span>
                        <span class="top-count"><?= $todayCount ?> événement(s)</span>
                    </div>
                </div>
            </div>

            <!-- Prochains événements -->
            <div class="top-list">
                <h3>⏰ Prochains événements</h3>
                <?php if (empty($nextEvents)): ?>
                    <p style="color: #64748b; text-align: center;">Aucun événement à venir</p>
                <?php else: ?>
                    <?php foreach ($nextEvents as $index => $event): ?>
                        <div class="top-item">
                            <span class="top-rank"><?= $index + 1 ?>.</span>
                            <span class="top-name">
                                <strong><?= htmlspecialchars($event['titre']) ?></strong>
                                <span style="display: block; font-size: 0.7rem; color: #64748b;">
                                    📍 <?= htmlspecialchars($event['lieu']) ?> | 
                                    📅 <?= date('d/m/Y', strtotime($event['date_event'])) ?>
                                </span>
                            </span>
                            <a href="../front/showEvenement.php?id=<?= $event['id'] ?>" style="color: #0f766e; text-decoration: none;">Voir →</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Insights -->
            <div class="insight-text">
                <strong>💡 Analyse métier</strong><br>
                <?php if ($upcomingCount > $pastCount): ?>
                    📈 Dynamique positive : plus d'événements à venir que d'événements passés.
                <?php elseif ($upcomingCount < $pastCount): ?>
                    📉 Attention : le nombre d'événements à venir est inférieur aux événements passés.
                <?php else: ?>
                    📊 Équilibre entre événements passés et à venir.
                <?php endif; ?>
                
                <?php if ($topType): ?>
                    <br>🎯 Le type d'événement le plus populaire est <strong><?= $topType ?></strong>.
                <?php endif; ?>
                
                <?php if ($topOrganisateur && $maxEvents > 0): ?>
                    <br>🏆 Félicitations à <strong><?= htmlspecialchars($topOrganisateur['nom']) ?></strong> pour ses <?= $maxEvents ?> événement(s) !
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>