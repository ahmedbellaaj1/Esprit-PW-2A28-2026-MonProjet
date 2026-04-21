<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

$controller = new OrderController();
$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$order = [
    'id_produit' => '',
    'id_utilisateur' => '',
    'quantite' => 1,
    'prix_total' => '',
    'date_commande' => date('Y-m-d H:i:s'),
    'statut' => 'en-cours',
    'adresse_livraison' => '',
];

if ($isEdit) {
    $row = $controller->find($id);
    if (!$row) {
        redirect('orders.php');
    }
    $order = $row;
}

$error = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->save($_POST, $isEdit ? $id : null);
    if (!$result['ok']) {
        $error = $result['error'];
        $errors = $result['errors'] ?? [];
        $order = array_merge($order, $result['data']);
    } else {
        redirect('orders.php?ok=1');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Commande</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand" style="margin-bottom:14px;">Admin GreenBite</div>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Gestion Produits</a>
        <a class="active" href="orders.php">Gestion Commandes</a>
        <a href="../front-office/index.php">Voir Front Office</a>
    </aside>

    <main class="main">
        <h1 class="page-title" style="margin-top:0;"><?= $isEdit ? 'Modifier' : 'Ajouter' ?> une commande</h1>
        <p class="subtitle">Tous les attributs de la classe Commande sont disponibles.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card">
            <div class="row">
                <div>
                    <label>ID produit</label>
                    <input type="text" name="id_produit" value="<?= h((string) $order['id_produit']) ?>">
                    <?php if (isset($errors['id_produit'])): ?><small style="color:#b91c1c;"><?= h($errors['id_produit']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>ID utilisateur</label>
                    <input type="text" name="id_utilisateur" value="<?= h((string) $order['id_utilisateur']) ?>">
                    <?php if (isset($errors['id_utilisateur'])): ?><small style="color:#b91c1c;"><?= h($errors['id_utilisateur']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Quantite</label>
                    <input type="text" name="quantite" value="<?= h((string) $order['quantite']) ?>">
                    <?php if (isset($errors['quantite'])): ?><small style="color:#b91c1c;"><?= h($errors['quantite']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Prix total</label>
                    <input type="text" name="prix_total" value="<?= h((string) $order['prix_total']) ?>">
                    <?php if (isset($errors['prix_total'])): ?><small style="color:#b91c1c;"><?= h($errors['prix_total']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Date commande (YYYY-MM-DD HH:MM:SS)</label>
                    <input name="date_commande" value="<?= h((string) $order['date_commande']) ?>">
                    <?php if (isset($errors['date_commande'])): ?><small style="color:#b91c1c;"><?= h($errors['date_commande']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Mode de livraison 🚚</label>
                    <select name="mode_livraison">
                        <?php foreach (['standard', 'express'] as $mode): ?>
                            <option value="<?= $mode ?>" <?= $order['mode_livraison'] === $mode ? 'selected' : '' ?>><?= ucfirst($mode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['mode_livraison'])): ?><small style="color:#b91c1c;"><?= h($errors['mode_livraison']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Date livraison souhaitée 📅</label>
                    <input type="date" name="date_livraison_souhaitee" value="<?= h((string) $order['date_livraison_souhaitee']) ?>">
                    <?php if (isset($errors['date_livraison_souhaitee'])): ?><small style="color:#b91c1c;"><?= h($errors['date_livraison_souhaitee']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Statut</label>
                    <select name="statut">
                        <?php foreach (['en-cours', 'en-preparation', 'confirmee', 'livree', 'annulee'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['statut'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('-', ' ', $s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['statut'])): ?><small style="color:#b91c1c;"><?= h($errors['statut']) ?></small><?php endif; ?>
                </div>
            </div>
            <div>
                <label>Adresse livraison</label>
                <textarea name="adresse_livraison" rows="4"><?= h((string) $order['adresse_livraison']) ?></textarea>
                <?php if (isset($errors['adresse_livraison'])): ?><small style="color:#b91c1c;"><?= h($errors['adresse_livraison']) ?></small><?php endif; ?>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
                <button class="btn" type="submit">Enregistrer</button>
                <a class="btn secondary" href="orders.php">Annuler</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
