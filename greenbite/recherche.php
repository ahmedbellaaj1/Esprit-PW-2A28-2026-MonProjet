<?php
require 'connexion.php';

$results = [];
if (isset($_POST['q']) && $_POST['q'] !== '') {
    $q = '%' . trim($_POST['q']) . '%';
    $stmt = $pdo->prepare("SELECT id, nom, prix FROM produits WHERE nom LIKE ?");
    $stmt->execute([$q]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produits as &$p) {
        $st = $pdo->prepare("SELECT a.nom FROM allergies a JOIN produit_allergies pa ON a.id = pa.allergie_id WHERE pa.produit_id = ?");
        $st->execute([$p['id']]);
        $alls = $st->fetchAll(PDO::FETCH_COLUMN);
        $p['allergies'] = $alls;
    }
    $results = $produits;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Greenbite - Recherche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --teal: #0f766e; }
        .header { background-color: var(--teal); color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="text-teal">Greenbite</h1>
        <p class="lead">Rechercher un produit (prix + allergies)</p>
    </div>

    <form method="POST" class="row justify-content-center">
        <div class="col-md-6">
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="Nom du produit..." required>
                <button class="btn btn-teal">Rechercher</button>
            </div>
        </div>
    </form>

    <?php if (!empty($results)): ?>
        <h4 class="mt-5">Résultats :</h4>
        <?php foreach($results as $p): ?>
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5><?= htmlspecialchars($p['nom']) ?></h5>
                        <?php if (!empty($p['allergies'])): ?>
                            <p class="text-danger mb-0">⚠️ Allergies : <?= implode(', ', array_map('htmlspecialchars', $p['allergies'])) ?></p>
                        <?php else: ?>
                            <p class="text-success mb-0">✅ Sans allergie déclarée</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <span class="fs-4 fw-bold text-teal"><?= number_format($p['prix'], 2) ?> €</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>