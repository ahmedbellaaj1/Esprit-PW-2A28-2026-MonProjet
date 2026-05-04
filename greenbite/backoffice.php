<?php
require 'connexion.php';

// Traitement des actions (Ajout, Modification, Suppression) - même logique que précédemment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter préférence
    if (isset($_POST['ajouter_preference']) && !empty($_POST['nouvelle_preference'])) {
        $nom = trim($_POST['nouvelle_preference']);
        $pdo->prepare("INSERT INTO preferences (nom) VALUES (?)")->execute([$nom]);
    }
    // Ajouter allergie
    if (isset($_POST['ajouter_allergie']) && !empty($_POST['nouvelle_allergie'])) {
        $nom = trim($_POST['nouvelle_allergie']);
        $pdo->prepare("INSERT INTO allergies (nom) VALUES (?)")->execute([$nom]);
    }
}

// Suppression
if (isset($_GET['supp_pref'])) {
    $pdo->prepare("DELETE FROM preferences WHERE id = ?")->execute([(int)$_GET['supp_pref']]);
}
if (isset($_GET['supp_all'])) {
    $pdo->prepare("DELETE FROM allergies WHERE id = ?")->execute([(int)$_GET['supp_all']]);
}

// Récupération des listes
$preferences = $pdo->query("SELECT * FROM preferences ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$allergies    = $pdo->query("SELECT * FROM allergies ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Greenbite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --teal: #0f766e; }
        body { background-color: #f8f9fa; }
        .header { background-color: var(--teal); color: white; }
        .sidebar { background-color: var(--teal); color: white; min-height: 100vh; }
        .sidebar .nav-link { color: white; padding: 12px 20px; }
        .sidebar .nav-link.active { background-color: #0a5c56; }
        .btn-teal { background-color: var(--teal); color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .section-title { font-size: 1.35rem; font-weight: 600; color: #333; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-4">
            <h4 class="text-white mb-4">GreenBite</h4>
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link"><i class="bi bi-house-door"></i> Tableau de bord</a></li>
                <li><a href="backoffice.php" class="nav-link active"><i class="bi bi-gear"></i> Administration</a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-box-seam"></i> Produits</a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-star"></i> Évaluations</a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-people"></i> Utilisateurs</a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-book"></i> Recettes</a></li>
            </ul>
        </div>

        <!-- Contenu -->
        <div class="col-md-9 col-lg-10 p-0">
            <div class="header p-3 d-flex justify-content-between align-items-center">
                <h5>Back Office Greenbite</h5>
                <div class="d-flex gap-3">
                    <span class="badge bg-light text-dark px-3 py-2">Admin</span>
                    <div class="rounded-circle bg-white text-teal fw-bold px-3 py-1">GB</div>
                </div>
            </div>

            <div class="p-5">
                <p class="text-muted mb-4">Ajoutez ici vos pages de gestion et vos liens administratifs.</p>

                <!-- Préférences Alimentaires -->
                <h5 class="section-title mb-3">✅ Gestion des Préférences Alimentaires</h5>
                <div class="input-group mb-4" style="max-width: 420px;">
                    <input type="text" id="new_pref" class="form-control" placeholder="Nouvelle préférence">
                    <button class="btn btn-teal" onclick="addPref()">Ajouter</button>
                </div>

                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Préférence</th><th width="140"></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($preferences as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nom']) ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-teal me-2">Modifier</a>
                                <a href="?supp_pref=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <hr class="my-5">

                <!-- Allergies -->
                <h5 class="section-title mb-3">⚠️ Gestion des Allergies</h5>
                <div class="input-group mb-4" style="max-width: 420px;">
                    <input type="text" id="new_all" class="form-control" placeholder="Nouvelle allergie">
                    <button class="btn btn-teal" onclick="addAllergy()">Ajouter</button>
                </div>

                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Allergie</th><th width="140"></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($allergies as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['nom']) ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-teal me-2">Modifier</a>
                                <a href="?supp_all=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function addPref() {
    let val = document.getElementById('new_pref').value.trim();
    if(val) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="nouvelle_preference" value="${val}"><input type="hidden" name="ajouter_preference" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}
function addAllergy() {
    let val = document.getElementById('new_all').value.trim();
    if(val) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="nouvelle_allergie" value="${val}"><input type="hidden" name="ajouter_allergie" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>