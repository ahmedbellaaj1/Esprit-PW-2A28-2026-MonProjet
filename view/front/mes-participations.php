<?php
// view/front/mes-participations.php
session_start();
require_once "../../controller/ParticipationController.php";
require_once "../../controller/EvenementController.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$participationController = new ParticipationController();
$eventController = new EvenementController();

$participations = $participationController->getParticipationsByUser($user['id']);

// Nom complet de l'utilisateur
$userFullName = '';
if (isset($user['prenom']) && isset($user['nom'])) {
    $userFullName = trim($user['prenom'] . ' ' . $user['nom']);
} elseif (isset($user['nom'])) {
    $userFullName = $user['nom'];
} else {
    $userFullName = 'Utilisateur';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes participations - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .navbar {
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-radius: 16px;
        }
        .navbar-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-logo span { color: #ccfbf1; }
        .navbar-logo img {
            height: 35px;
            width: 35px;
            border-radius: 8px;
            object-fit: cover;
        }
        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .nav-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-name {
            color: white;
            font-size: 0.9rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 { font-size: 1.8rem; color: #0f172a; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #0f172a; }
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
        
        .btn {
            background: #0f766e;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #0c5f58;
            transform: translateY(-2px);
        }
        .btn-print {
            background: #14b8a6;
            margin-left: 0.5rem;
        }
        .btn-print:hover {
            background: #0f766e;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        .empty-state .emoji {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            font-size: 1.2rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .navbar { flex-direction: column; height: auto; gap: 0.5rem; padding: 1rem; }
            .container { padding: 1rem; overflow-x: auto; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
            .actions { flex-direction: column; }
            .btn { text-align: center; margin: 0.25rem 0; }
            .btn-print { margin-left: 0; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">
        <img src="../assets/images/logo.png" alt="GreenBite">
        <span>Green<span>Bite</span></span>
    </a>
    <div class="user-info">
        <span class="user-name">👤 <?= htmlspecialchars($userFullName) ?></span>
        <a href="logout.php" class="nav-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">🚪 Déconnexion</a>
        <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
            <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h1>📋 Mes participations</h1>
    <p>Bienvenue <?= htmlspecialchars($userFullName) ?> ! Voici vos inscriptions aux événements.</p>
    
    <?php if (empty($participations)): ?>
        <div class="empty-state">
            <div class="emoji">📭</div>
            <h3>Aucune participation</h3>
            <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
            <a href="listEvenements.php" class="btn" style="margin-top: 1rem; display: inline-block;">→ Découvrir les événements</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Événement</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Statut</th>
                    <th>Date inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($participations as $p): 
                    $statusClass = match($p['statut']) {
                        'present' => 'status-present',
                        'inscrit' => 'status-inscrit',
                        'annule' => 'status-annule',
                        default => 'status-en_attente'
                    };
                    $statusLabel = match($p['statut']) {
                        'present' => '✅ Présent',
                        'inscrit' => '📝 Inscrit',
                        'annule' => '❌ Annulé',
                        default => '⏳ En attente'
                    };
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['event_titre']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($p['date_event'])) ?></td>
                    <td><?= htmlspecialchars($p['lieu']) ?></td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
                    <td class="actions">
                        <a href="showEvenement.php?id=<?= $p['evenement_id'] ?>" class="btn">📋 Voir</a>
                        <a href="imprimer-recu.php?id=<?= $p['id'] ?>" class="btn btn-print" target="_blank">🖨️ Imprimer le récépissé</a>
                     </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #64748b;">
            💡 Cliquez sur "Imprimer le récépissé" pour obtenir un justificatif de votre participation.
        </p>
    <?php endif; ?>
</div>

</body>
</html>