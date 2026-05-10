<?php
// view/front/inscription.php
session_start();
require_once "../../controller/EvenementController.php";
require_once "../../controller/ParticipationController.php";
require_once "../../model/Participation.php";

$eventController = new EvenementController();
$participationController = new ParticipationController();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    header('Location: listEvenements.php');
    exit();
}

$event = $eventController->getEvenementById($eventId);

if (!$event) {
    header('Location: listEvenements.php');
    exit();
}

$error = '';
$success = '';
$formData = ['nom' => '', 'email' => '', 'telephone' => ''];

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']);

// Si connecté, pré-remplir les champs
if ($isLoggedIn) {
    $user = $_SESSION['user'];
    $formData = [
        'nom' => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
        'email' => $user['email'] ?? '',
        'telephone' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nom' => trim($_POST['nom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? '')
    ];
    
    $errors = [];
    
    // Validation du nom
    if (empty($formData['nom'])) {
        $errors['nom'] = "Le nom est obligatoire";
    } elseif (strlen($formData['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
    }
    
    // Validation de l'email
    if (empty($formData['email'])) {
        $errors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    }
    
    // Validation du téléphone
    if (!empty($formData['telephone']) && !preg_match('/^[0-9+\-\s]{8,20}$/', $formData['telephone'])) {
        $errors['telephone'] = "Format de téléphone invalide";
    }
    
    if (empty($errors)) {
        try {
            // Vérifier si l'utilisateur existe déjà dans la table users
            // Si non, on le crée temporairement
            $userRepoPaths = [
                __DIR__ . "/../../ModuleUser/Controller/UserRepository.php",
                __DIR__ . "/../../Controller/UserRepository.php",
                __DIR__ . "/../ModuleUser/Controller/UserRepository.php"
            ];
            
            $userRepoFound = false;
            foreach ($userRepoPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $userRepoFound = true;
                    break;
                }
            }
            
            $userId = null;
            
            if ($userRepoFound) {
                $userRepo = new UserRepository();
                $existingUser = $userRepo->findByEmail($formData['email']);
                
                if ($existingUser) {
                    $userId = $existingUser->getId();
                } else {
                    // Créer un utilisateur temporaire
                    $sql = "INSERT INTO users (nom, prenom, email, role, statut) 
                            VALUES (:nom, '', :email, 'participant', 'actif')";
                    $db = config::getConnexion();
                    $query = $db->prepare($sql);
                    $query->execute([
                        'nom' => $formData['nom'],
                        'email' => $formData['email']
                    ]);
                    $userId = $db->lastInsertId();
                }
            } else {
                // Fallback: utiliser l'email comme identifiant
                $userId = null;
            }
            
            // Créer la participation
            $participation = new Participation($eventId, $userId);
            $result = $participationController->addParticipation($participation);
            
            if ($result['success']) {
                $success = $result['message'];
                $formData = ['nom' => '', 'email' => '', 'telephone' => ''];
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = '<ul style="margin:0; padding-left:1.5rem;">';
        foreach ($errors as $e) {
            $error .= '<li>' . htmlspecialchars($e) . '</li>';
        }
        $error .= '</ul>';
    }
}

$inscrits = $participationController->countParticipantsByEvent($eventId);
$capaciteMax = $event['capacite_max'] ?? 0;
$placesRestantes = $capaciteMax - $inscrits;
$complet = ($capaciteMax > 0 && $placesRestantes <= 0);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= htmlspecialchars($event['titre']) ?> - GreenBite</title>
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
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 { font-size: 1.8rem; color: #0f172a; margin-bottom: 0.5rem; }
        .event-info {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 16px;
            margin: 1.5rem 0;
        }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #0f172a; }
        label .required { color: #dc2626; }
        input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #14b8a6; }
        .btn-submit {
            width: 100%;
            background: #0f766e;
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-submit:hover { background: #0c5f58; }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
        .error-message { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #dc2626; }
        .success-message { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #16a34a; }
        .back-link { display: inline-block; margin-top: 1rem; color: #0f766e; text-decoration: none; }
        small { display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.7rem; }
        
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .container { padding: 1.5rem; }
            .navbar { flex-direction: column; height: auto; gap: 0.5rem; padding: 1rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">
        <img src="../assets/images/logo.png" alt="GreenBite">
        <span>Green<span>Bite</span></span>
    </a>
    <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
</nav>

<div class="container">
    <h1>📝 Inscription à l'événement</h1>
    
    <div class="event-info">
        <p><strong><?= htmlspecialchars($event['titre']) ?></strong></p>
        <p>📅 <?= date('d/m/Y', strtotime($event['date_event'])) ?> | 📍 <?= htmlspecialchars($event['lieu']) ?></p>
        <?php if ($capaciteMax > 0): ?>
            <p>🎟️ Places disponibles : <strong><?= max(0, $placesRestantes) ?> / <?= $capaciteMax ?></strong></p>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
        <div class="error-message">❌ <?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message">
            ✅ <?= htmlspecialchars($success) ?>
            <br><br>
            <?php if ($isLoggedIn): ?>
                <a href="mes-participations.php" style="color: #166534;">📋 Voir mes participations →</a>
            <?php else: ?>
                <a href="showEvenement.php?id=<?= $eventId ?>" style="color: #166534;">← Retour à l'événement</a>
            <?php endif; ?>
        </div>
    <?php elseif ($complet): ?>
        <div class="error-message">❌ Désolé, cet événement est complet !</div>
        <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Retour à l'événement</a>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label>Nom complet <span class="required">*</span></label>
                <input type="text" name="nom" value="<?= htmlspecialchars($formData['nom']) ?>" placeholder="Votre nom et prénom">
                <small>2 à 100 caractères</small>
            </div>
            
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="text" name="email" value="<?= htmlspecialchars($formData['email']) ?>" placeholder="exemple@email.com">
                <small>Un email de confirmation vous sera envoyé</small>
            </div>
            
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($formData['telephone']) ?>" placeholder="71 123 456">
                <small>Optionnel - Pour vous contacter en cas d'urgence</small>
            </div>
            
            <button type="submit" class="btn-submit">✅ Confirmer mon inscription</button>
        </form>
        <a href="showEvenement.php?id=<?= $eventId ?>" class="back-link">← Annuler et retourner</a>
    <?php endif; ?>
</div>

</body>
</html>