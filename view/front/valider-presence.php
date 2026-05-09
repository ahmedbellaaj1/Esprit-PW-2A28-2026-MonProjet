<?php
// view/front/valider-presence.php
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
    __DIR__ . "/../../model/UserRepository.php"
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

$participationController = new ParticipationController();
$eventController = new EvenementController();
$mailController = new MailController();
$userRepo = new UserRepository();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($token) || $id <= 0) {
    die("❌ QR Code invalide");
}

// Récupérer la participation
$participation = $participationController->getParticipationById($id);

if (!$participation) {
    die("❌ Participation non trouvée");
}

// Vérifier le token
if ($participation['code_qr'] !== $token) {
    die("❌ QR Code invalide");
}

// Vérifier si déjà validé
if ($participation['statut'] === 'present') {
    $alreadyValidated = true;
    $validationSuccess = true;
} else {
    $alreadyValidated = false;
    
    // Mettre à jour le statut
    $result = $participationController->updateParticipationStatut($id, 'present');
    
    if ($result['success']) {
        // Récupérer les informations pour l'email de récépissé
        $user = $userRepo->findById($participation['user_id']);
        $event = $eventController->getEvenementById($participation['evenement_id']);
        
        if ($user && $event) {
            $userArray = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ];
            
            // Envoyer l'email de récépissé
            $mailController->sendReceiptEmail($userArray, $event, $participation);
        }
        
        $validationSuccess = true;
    } else {
        $validationSuccess = false;
    }
}

// Récupérer l'événement
$event = $eventController->getEvenementById($participation['evenement_id']);
$user = $userRepo->findById($participation['user_id']);
$userFullName = '';
if ($user) {
    $userFullName = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? ''));
}

// Calculer la date de validation
$validationDate = '';
if ($participation['date_validation']) {
    $validationDate = date('d/m/Y à H:i', strtotime($participation['date_validation']));
} elseif ($validationSuccess && !$alreadyValidated) {
    $validationDate = date('d/m/Y à H:i');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation QR Code - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 550px;
            width: 100%;
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .header {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header .icon { font-size: 64px; margin-bottom: 10px; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px; }
        .event-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 16px;
            margin: 20px 0;
        }
        .event-info h3 {
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        .event-info p {
            margin: 8px 0;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .success-box {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 16px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #16a34a;
        }
        .warning-box {
            background: #fef3c7;
            color: #92400e;
            padding: 15px;
            border-radius: 16px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .receipt {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            padding: 20px;
            border-radius: 16px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #bbf7d0;
        }
        .receipt h3 {
            color: #0f766e;
            margin-bottom: 15px;
        }
        .receipt-number {
            font-size: 28px;
            font-weight: 700;
            color: #0f766e;
            font-family: monospace;
            letter-spacing: 2px;
            background: white;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 12px;
            margin: 10px 0;
        }
        .receipt hr {
            margin: 15px 0;
            border: none;
            border-top: 1px dashed #bbf7d0;
        }
        .btn {
            display: inline-block;
            background: #0f766e;
            color: white;
            padding: 12px 24px;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn:hover {
            background: #0c5f58;
            transform: translateY(-2px);
        }
        .btn-print {
            background: #f59e0b;
        }
        .btn-print:hover {
            background: #d97706;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .footer {
            background: #f8fafc;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        @media print {
            .header, .action-buttons, .footer, .btn, .no-print {
                display: none !important;
            }
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .receipt {
                break-inside: avoid;
            }
        }
        @media (max-width: 640px) {
            .header { padding: 20px; }
            .header .icon { font-size: 48px; }
            .header h1 { font-size: 24px; }
            .content { padding: 20px; }
            .receipt-number { font-size: 20px; }
            .action-buttons { flex-direction: column; align-items: center; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Validation QR Code</h1>
            <p>GreenBite - Événement éco-responsable</p>
        </div>
        
        <div class="content">
            <?php if ($alreadyValidated): ?>
                <div class="warning-box">
                    <strong>⚠️ Déjà validé</strong><br>
                    Votre présence a déjà été enregistrée pour cet événement.
                </div>
            <?php elseif ($validationSuccess): ?>
                <div class="success-box">
                    <strong>✅ Présence validée !</strong><br>
                    Bienvenue à l'événement ! Un email de confirmation vous a été envoyé.
                </div>
            <?php else: ?>
                <div class="warning-box">
                    <strong>❌ Erreur</strong><br>
                    Une erreur est survenue lors de la validation.
                </div>
            <?php endif; ?>
            
            <div class="event-info">
                <h3>📌 <?= htmlspecialchars($event['titre'] ?? 'Événement') ?></h3>
                <p>📅 <strong>Date :</strong> <?= isset($event['date_event']) ? date('d/m/Y', strtotime($event['date_event'])) : 'Date non spécifiée' ?></p>
                <p>📍 <strong>Lieu :</strong> <?= htmlspecialchars($event['lieu'] ?? 'Lieu non spécifié') ?></p>
                <p>👤 <strong>Participant :</strong> <?= htmlspecialchars($userFullName) ?></p>
                <p>📧 <strong>Email :</strong> <?= htmlspecialchars($user?->getEmail() ?? '') ?></p>
            </div>
            
            <!-- Récépissé -->
            <div class="receipt">
                <h3>🎟️ RÉCÉPISSÉ DE PARTICIPATION</h3>
                <div class="receipt-number">#<?= str_pad($participation['id'], 8, '0', STR_PAD_LEFT) ?></div>
                <p>Délivré le <?= date('d/m/Y à H:i') ?></p>
                <hr>
                <p><strong><?= htmlspecialchars($userFullName) ?></strong></p>
                <p><?= htmlspecialchars($user?->getEmail() ?? '') ?></p>
                <p><strong><?= htmlspecialchars($event['titre'] ?? '') ?></strong></p>
                <p>📅 <?= isset($event['date_event']) ? date('d/m/Y', strtotime($event['date_event'])) : '' ?></p>
                <p>📍 <?= htmlspecialchars($event['lieu'] ?? '') ?></p>
                <p>Statut : <span style="color: #0f766e; font-weight: bold;">✅ Présent(e)</span></p>
                <?php if ($validationDate): ?>
                    <p>Validé le : <?= $validationDate ?></p>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <?php if ($validationSuccess && !$alreadyValidated): ?>
                    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer le récépissé</button>
                <?php endif; ?>
                <a href="showEvenement.php?id=<?= $participation['evenement_id'] ?>" class="btn">📋 Voir l'événement</a>
                <a href="listEvenements.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>
        
        <div class="footer">
            <p>GreenBite - Plateforme événementielle éco-responsable</p>
            <p>Ce document fait office de justificatif de présence.</p>
        </div>
    </div>
    
    <script>
        // Impression automatique après validation (optionnel - décommentez pour activer)
        // <?php if ($validationSuccess && !$alreadyValidated): ?>
        // window.onload = function() { setTimeout(function() { window.print(); }, 500); }
        // <?php endif; ?>
    </script>
</body>
</html>