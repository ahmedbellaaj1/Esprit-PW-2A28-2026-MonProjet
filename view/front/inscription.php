<?php
session_start();
require_once "../../controller/ParticipantController.php";
require_once "../../controller/EvenementController.php";

$participantController = new ParticipantController();
$eventController = new EvenementController();

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

// Traitement du formulaire
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
            $participant = new Participant(
                $formData['nom'],
                $formData['email'],
                $formData['telephone'],
                $eventId
            );
            
            $result = $participantController->addParticipant($participant);
            
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

// Vérifier les places disponibles
$capaciteMax = $event['capacite_max'] ?? 0;
$inscrits = $participantController->countParticipantsByEvent($eventId);
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
            font-family: 'Inter', system-ui, sans-serif;
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
        .navbar-logo { font-size: 1.6rem; font-weight: 700; color: white; text-decoration: none; }
        .navbar-logo span { color: #ccfbf1; }
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
            transition: all 0.3s;
        }
        .btn-submit:hover { background: #0c5f58; transform: translateY(-2px); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
        .error-message { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
        .success-message { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
        .back-link { display: inline-block; margin-top: 1rem; color: #0f766e; text-decoration: none; }
        small { display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.7rem; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">Green<span>Bite</span></a>
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
            <a href="showEvenement.php?id=<?= $eventId ?>" style="color: #166534;">← Retour à l'événement</a>
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