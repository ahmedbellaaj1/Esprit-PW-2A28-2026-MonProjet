<?php
// view/front/imprimer-recu.php
session_start();
require_once "../../controller/ParticipationController.php";
require_once "../../controller/EvenementController.php";
require_once "../../controller/MailController.php";

// ==================== RECHERCHE FLEXIBLE DE USERREPOSITORY ====================
$userRepoPaths = [
    __DIR__ . "/../../ModuleUser/Controller/UserRepository.php",
    __DIR__ . "/../../Controller/UserRepository.php",
    __DIR__ . "/../ModuleUser/Controller/UserRepository.php",
    __DIR__ . "/../Controller/UserRepository.php",
    __DIR__ . "/../../model/UserRepository.php",
    __DIR__ . "/../model/UserRepository.php"
];

$userRepoFound = false;
foreach ($userRepoPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $userRepoFound = true;
        break;
    }
}

// Définir une classe User par défaut si le module user n'est pas trouvé
if (!$userRepoFound) {
    if (!class_exists('User')) {
        class User {
            private $id, $nom, $prenom, $email, $role;
            
            public function __construct($id, $nom, $prenom, $email, $role = 'user') {
                $this->id = $id;
                $this->nom = $nom;
                $this->prenom = $prenom;
                $this->email = $email;
                $this->role = $role;
            }
            
            public function getId() { return $this->id; }
            public function getNom() { return $this->nom; }
            public function getPrenom() { return $this->prenom; }
            public function getEmail() { return $this->email; }
            public function getRole() { return $this->role; }
        }
    }
    
    if (!class_exists('UserRepository')) {
        class UserRepository {
            private $db;
            
            public function __construct() {
                require_once __DIR__ . "/../../config/database.php";
                $this->db = config::getConnexion();
            }
            
            public function findById($id) {
                try {
                    $sql = "SELECT id, nom, prenom, email, role FROM users WHERE id = :id";
                    $query = $this->db->prepare($sql);
                    $query->execute(['id' => $id]);
                    $row = $query->fetch();
                    if ($row) {
                        return new User($row['id'], $row['nom'], $row['prenom'], $row['email'], $row['role'] ?? 'user');
                    }
                    return null;
                } catch (Exception $e) {
                    error_log("UserRepository findById error: " . $e->getMessage());
                    return null;
                }
            }
        }
    }
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$participationController = new ParticipationController();
$eventController = new EvenementController();
$userRepo = new UserRepository();

$participationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($participationId <= 0) {
    header('Location: mes-participations.php');
    exit();
}

// Récupérer la participation
$participation = $participationController->getParticipationById($participationId);

if (!$participation || $participation['user_id'] != $user['id']) {
    header('Location: mes-participations.php');
    exit();
}

// Récupérer l'événement et les informations utilisateur
$event = $eventController->getEvenementById($participation['evenement_id']);
$userFromDb = $userRepo->findById($participation['user_id']);
$userFullName = '';
if ($userFromDb) {
    $userFullName = trim(($userFromDb->getPrenom() ?? '') . ' ' . ($userFromDb->getNom() ?? ''));
} else {
    $userFullName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
}

$eventDate = isset($event['date_event']) ? date('d/m/Y', strtotime($event['date_event'])) : 'Date non spécifiée';
$qrToken = $participation['code_qr'] ?? '';

// Générer l'URL du QR code
$qrCodeUrl = '';
if (!empty($qrToken)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = rtrim($protocol . $host, '/');
    $qrContent = $baseUrl . "/projetwebnova1/view/front/valider-presence.php?token=" . $qrToken . "&id=" . $participationId;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($qrContent);
}

$statusLabel = match($participation['statut']) {
    'present' => '✅ Présent(e)',
    'inscrit' => '📝 Inscrit(e)',
    'annule' => '❌ Annulé',
    default => '⏳ En attente'
};
$statusColor = match($participation['statut']) {
    'present' => '#166534',
    'inscrit' => '#1e40af',
    'annule' => '#991b1b',
    default => '#92400e'
};
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé de participation - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e6f7f5;
            padding: 2rem;
        }
        
        /* Style pour l'impression */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .qr-code {
                break-inside: avoid;
            }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        /* En-tête */
        .receipt-header {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .receipt-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .receipt-header p {
            opacity: 0.9;
        }
        
        /* Corps */
        .receipt-body {
            padding: 2rem;
        }
        
        /* Titre du récépissé */
        .receipt-title {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .receipt-title h2 {
            font-size: 1.5rem;
            color: #0f172a;
        }
        .receipt-number {
            font-size: 1rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
        
        /* Grille d'informations */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
        }
        .info-card .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
        }
        .info-card .value {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            margin-top: 0.25rem;
        }
        
        /* Section QR Code */
        .qr-section {
            text-align: center;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 16px;
        }
        .qr-section img {
            margin: 1rem 0;
            border: 2px solid #0f766e;
            border-radius: 12px;
        }
        
        /* Statut */
        .status-box {
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            background: #f0fdf4;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        
        /* Pied de page */
        .receipt-footer {
            background: #f8fafc;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Boutons d'action */
        .action-buttons {
            text-align: center;
            margin-top: 1.5rem;
            padding: 1rem;
        }
        .btn {
            display: inline-block;
            background: #0f766e;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 0.5rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0c5f58;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .receipt-body { padding: 1.5rem; }
            .info-grid { grid-template-columns: 1fr; gap: 0.75rem; }
            .btn { display: block; margin: 0.5rem auto; width: 200px; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h1>🌱 GreenBite</h1>
        <p>Plateforme événementielle éco-responsable</p>
    </div>
    
    <div class="receipt-body">
        <div class="receipt-title">
            <h2>🎟️ RÉCÉPISSÉ DE PARTICIPATION</h2>
            <div class="receipt-number">N° <?= str_pad($participation['id'], 8, '0', STR_PAD_LEFT) ?></div>
            <div>Délivré le <?= date('d/m/Y à H:i') ?></div>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <div class="label">👤 Participant</div>
                <div class="value"><?= htmlspecialchars($userFullName) ?></div>
            </div>
            <div class="info-card">
                <div class="label">📧 Email</div>
                <div class="value"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div class="info-card">
                <div class="label">📅 Événement</div>
                <div class="value"><?= htmlspecialchars($event['titre'] ?? 'Événement') ?></div>
            </div>
            <div class="info-card">
                <div class="label">📍 Lieu</div>
                <div class="value"><?= htmlspecialchars($event['lieu'] ?? 'Non spécifié') ?></div>
            </div>
            <div class="info-card">
                <div class="label">📆 Date</div>
                <div class="value"><?= $eventDate ?></div>
            </div>
            <div class="info-card">
                <div class="label">🏷️ Type</div>
                <div class="value"><?= htmlspecialchars($event['type'] ?? 'Non spécifié') ?></div>
            </div>
            <div class="info-card">
                <div class="label">📝 Date d'inscription</div>
                <div class="value"><?= date('d/m/Y H:i', strtotime($participation['date_inscription'])) ?></div>
            </div>
            <div class="info-card">
                <div class="label">🔑 Code unique</div>
                <div class="value"><?= substr($participation['code_qr'] ?? '', 0, 15) ?>...</div>
            </div>
        </div>
        
        <?php if ($qrCodeUrl): ?>
        <div class="qr-section">
            <h3>🎟️ Votre QR code d'accès</h3>
            <img src="<?= $qrCodeUrl ?>" alt="QR Code">
            <p>Présentez ce QR code à l'entrée de l'événement</p>
        </div>
        <?php endif; ?>
        
        <div class="status-box">
            <span class="status-badge" style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>;">
                <?= $statusLabel ?>
            </span>
        </div>
        
        <?php if ($participation['statut'] === 'present' && $participation['date_validation']): ?>
        <div class="info-card" style="text-align: center;">
            <div class="label">✅ Présence validée le</div>
            <div class="value"><?= date('d/m/Y à H:i', strtotime($participation['date_validation'])) ?></div>
        </div>
        <?php endif; ?>
        
        <div class="receipt-footer">
            <p>Ce document fait office de justificatif de participation.</p>
            <p>GreenBite - Pour un monde plus vert 🌱</p>
        </div>
    </div>
</div>

<div class="action-buttons no-print">
    <button onclick="window.print()" class="btn">🖨️ Imprimer / Enregistrer en PDF</button>
    <a href="mes-participations.php" class="btn btn-secondary">← Retour à mes participations</a>
    <a href="showEvenement.php?id=<?= $participation['evenement_id'] ?>" class="btn btn-secondary">📋 Voir l'événement</a>
</div>

<script>
    // Auto-print (optionnel - décommentez pour imprimer automatiquement)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>