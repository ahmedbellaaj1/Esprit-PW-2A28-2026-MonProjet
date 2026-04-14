<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$controller = new ProductController();
$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$product = [
    'nom' => '',
    'marque' => '',
    'code_barre' => '',
    'categorie' => '',
    'prix' => '',
    'calories' => '',
    'proteines' => '',
    'glucides' => '',
    'lipides' => '',
    'nutriscore' => 'C',
    'image' => '',
    'statut' => 'actif',
];

if ($isEdit) {
    $row = $controller->find($id);
    if (!$row) {
        redirect('products.php');
    }
    $product = $row;
}

$error = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->save($_POST, $isEdit ? $id : null);
    if (!$result['ok']) {
        $error = $result['error'];
        $errors = $result['errors'] ?? [];
        $product = array_merge($product, $result['data']);
    } else {
        redirect('products.php?ok=1');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Produit</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand" style="margin-bottom:14px;">Admin GreenBite</div>
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="products.php">Gestion Produits</a>
        <a href="orders.php">Gestion Commandes</a>
        <a href="../front-office/index.php">Voir Front Office</a>
    </aside>

    <main class="main">
        <h1 class="page-title" style="margin-top:0;"><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un produit</h1>
        <p class="subtitle">Tous les attributs de la classe Produit sont disponibles.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card">
            <div class="row">
                <div>
                    <label>Nom</label>
                    <input name="nom" value="<?= h($product['nom']) ?>" required minlength="2" maxlength="150">
                    <?php if (isset($errors['nom'])): ?><small style="color:#b91c1c;"><?= h($errors['nom']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Marque</label>
                    <input name="marque" value="<?= h($product['marque']) ?>" required minlength="2" maxlength="120">
                    <?php if (isset($errors['marque'])): ?><small style="color:#b91c1c;"><?= h($errors['marque']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Code barre</label>
                    <input name="code_barre" value="<?= h($product['code_barre']) ?>" pattern="[0-9]{8,20}" maxlength="20" title="8 a 20 chiffres">
                    <?php if (isset($errors['code_barre'])): ?><small style="color:#b91c1c;"><?= h($errors['code_barre']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Categorie</label>
                    <input name="categorie" value="<?= h($product['categorie']) ?>" maxlength="120">
                    <?php if (isset($errors['categorie'])): ?><small style="color:#b91c1c;"><?= h($errors['categorie']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Prix</label>
                    <input type="number" step="0.01" min="0" max="100000" name="prix" value="<?= h((string) $product['prix']) ?>">
                    <?php if (isset($errors['prix'])): ?><small style="color:#b91c1c;"><?= h($errors['prix']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Calories</label>
                    <input type="number" step="0.01" min="0" max="5000" name="calories" value="<?= h((string) $product['calories']) ?>">
                    <?php if (isset($errors['calories'])): ?><small style="color:#b91c1c;"><?= h($errors['calories']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Proteines</label>
                    <input type="number" step="0.01" min="0" max="5000" name="proteines" value="<?= h((string) $product['proteines']) ?>">
                    <?php if (isset($errors['proteines'])): ?><small style="color:#b91c1c;"><?= h($errors['proteines']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Glucides</label>
                    <input type="number" step="0.01" min="0" max="5000" name="glucides" value="<?= h((string) $product['glucides']) ?>">
                    <?php if (isset($errors['glucides'])): ?><small style="color:#b91c1c;"><?= h($errors['glucides']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Lipides</label>
                    <input type="number" step="0.01" min="0" max="5000" name="lipides" value="<?= h((string) $product['lipides']) ?>">
                    <?php if (isset($errors['lipides'])): ?><small style="color:#b91c1c;"><?= h($errors['lipides']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Nutriscore</label>
                    <select name="nutriscore">
                        <?php foreach (['A', 'B', 'C', 'D', 'E'] as $n): ?>
                            <option value="<?= $n ?>" <?= $product['nutriscore'] === $n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['nutriscore'])): ?><small style="color:#b91c1c;"><?= h($errors['nutriscore']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Image (URL)</label>
                    <input type="url" name="image" value="<?= h($product['image']) ?>" placeholder="https://...">
                    <?php if (isset($errors['image'])): ?><small style="color:#b91c1c;"><?= h($errors['image']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Statut</label>
                    <select name="statut">
                        <?php foreach (['actif', 'inactif', 'attente'] as $s): ?>
                            <option value="<?= $s ?>" <?= $product['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['statut'])): ?><small style="color:#b91c1c;"><?= h($errors['statut']) ?></small><?php endif; ?>
                </div>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
                <button class="btn" type="submit">Enregistrer</button>
                <a class="btn secondary" href="products.php">Annuler</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
