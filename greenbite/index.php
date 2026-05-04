<?php
require 'connexion.php';

// Récupérer les préférences et allergies du profil (simulation pour l'instant)
$stmt = $pdo->query("SELECT nom FROM preferences LIMIT 5");
$preferences = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT nom FROM allergies LIMIT 5");
$allergies = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenbite - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --teal: #0f766e;
        }
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .header {
            background-color: var(--teal);
            color: white;
        }
        .sidebar {
            background-color: var(--teal);
            color: white;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            border-radius: 0 30px 30px 0;
        }
        .sidebar .nav-link.active {
            background-color: #0a5c56;
            font-weight: 500;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .btn-teal {
            background-color: var(--teal);
            color: white;
            border-radius: 30px;
            padding: 10px 25px;
        }
        .btn-teal:hover {
            background-color: #0a5c56;
        }
        .tag {
            background-color: #e6f0ef;
            color: var(--teal);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.95rem;
            display: inline-block;
            margin: 4px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-4">
            <div class="d-flex align-items-center mb-5">
                <img src="https://via.placeholder.com/40x40/0f766e/ffffff?text=🌱" alt="Logo" class="me-2">
                <h4 class="mb-0">Greenbite</h4>
            </div>
            <p class="text-white-50 mb-1">AMEN ALLAH BANI</p>
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="backoffice.php" class="nav-link"><i class="bi bi-gear-fill"></i> Back Office</a></li>
            </ul>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10 p-0">
            <!-- Header -->
            <div class="header p-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tableau de bord</h5>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light rounded-pill px-3">Préférences</button>
                    <div class="rounded-circle bg-white text-teal fw-bold d-flex align-items-center justify-content-center" style="width:38px;height:38px;">A</div>
                </div>
            </div>

            <div class="p-5">
                <h2>Tableau de bord</h2>
                <p class="text-muted">Affichez et mettez à jour les préférences et allergies du profil.</p>

                <div class="card p-4 mt-4">
                    <h5 class="mb-4"><i class="bi bi-heart-fill text-danger"></i> Préférences & Allergies</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Préférences Alimentaires</h6>
                            <div class="mt-3">
                                <?php foreach($preferences as $pref): ?>
                                    <span class="tag"><?= htmlspecialchars($pref) ?></span>
                                <?php endforeach; ?>
                                <?php if(empty($preferences)): ?>
                                    <p class="text-muted">Aucune préférence enregistrée</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Allergies</h6>
                            <div class="mt-3">
                                <?php foreach($allergies as $all): ?>
                                    <span class="tag text-danger border border-danger"><?= htmlspecialchars($all) ?></span>
                                <?php endforeach; ?>
                                <?php if(empty($allergies)): ?>
                                    <p class="text-muted">Aucune allergie déclarée</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button onclick="alert('Fonctionnalité Enregistrer en cours de développement')" class="btn btn-teal">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>