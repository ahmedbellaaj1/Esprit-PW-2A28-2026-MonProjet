<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$id = (int) ($_GET['id'] ?? 0);

$controller = new ProductController();
$product = $controller->find($id);

$fieldErrors = [
    'id_utilisateur' => trim((string) ($_GET['err_id_utilisateur'] ?? '')),
    'quantite' => trim((string) ($_GET['err_quantite'] ?? '')),
    'statut' => trim((string) ($_GET['err_statut'] ?? '')),
    'adresse_livraison' => trim((string) ($_GET['err_adresse_livraison'] ?? '')),
];
$old = [
    'id_utilisateur' => trim((string) ($_GET['old_id_utilisateur'] ?? '')),
    'quantite' => trim((string) ($_GET['old_quantite'] ?? '1')),
    'statut' => trim((string) ($_GET['old_statut'] ?? 'en-cours')),
    'adresse_livraison' => trim((string) ($_GET['old_adresse_livraison'] ?? '')),
];

if (!$product) {
    http_response_code(404);
    echo 'Produit introuvable';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produit - <?= h($product['nom']) ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <a class="navbar-logo" href="index.php">Green<span>Bite</span></a>
    <ul class="navbar-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#" class="active">Produits</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="#">Dons</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="index.php">Catalogue</a>
        <div class="nav-avatar">AB</div>
    </div>

</nav>

<div class="main-container" style="padding-top:1.5rem;">
    <div class="section-heading">Details produit</div>

    <div class="table-container">
        <div class="modal-header">
            <div class="modal-emoji" style="overflow:hidden;padding:0;">
                <img src="<?= h($product['image'] ?: 'https://via.placeholder.com/400x400?text=Produit') ?>" alt="Image" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="modal-title">
                <div class="brand"><?= h($product['marque']) ?> - <?= h($product['categorie']) ?></div>
                <h2 style="margin-top:0;"><?= h($product['nom']) ?></h2>
                <div class="product-tags">
                    <span class="tag tag-local">Code: <?= h((string) $product['code_barre']) ?></span>
                    <span class="tag tag-bio">Statut: <?= h((string) $product['statut']) ?></span>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f8fafc;border-radius:14px;">
            <div class="nutriscore ns-<?= strtolower((string) $product['nutriscore']) ?>" style="position:static;width:52px;height:52px;font-size:1.4rem;">
                <?= h((string) $product['nutriscore']) ?>
            </div>
            <div>
                <div style="font-weight:600;color:#0f172a;font-size:0.95rem;">Prix: <?= number_format((float) $product['prix'], 2, ',', ' ') ?> DT</div>
                <div id="nutriDesc" style="font-size:0.82rem;color:#64748b;">Date ajout: <?= h((string) $product['date_ajout']) ?></div>
            </div>
        </div>

        <div class="section-heading" style="font-size:0.95rem;margin-bottom:0.75rem;">Valeurs nutritionnelles (100g)</div>
        <div class="nutri-grid" id="nutriGrid">
            <div class="nutri-item"><label>Calories</label><div class="value"><?= (int) $product['calories'] ?> <span class="unit">kcal</span></div></div>
            <div class="nutri-item"><label>Proteines</label><div class="value"><?= h((string) $product['proteines']) ?> <span class="unit">g</span></div></div>
            <div class="nutri-item"><label>Glucides</label><div class="value"><?= h((string) $product['glucides']) ?> <span class="unit">g</span></div></div>
            <div class="nutri-item"><label>Lipides</label><div class="value"><?= h((string) $product['lipides']) ?> <span class="unit">g</span></div></div>
        </div>

        <div class="review-form">
            <h3>Commander ce produit</h3>
            <form method="post" action="create_order.php">
                <input type="hidden" name="id_produit" value="<?= (int) $product['id_produit'] ?>">
                <input type="hidden" name="prix_unitaire" value="<?= h((string) $product['prix']) ?>">

                <div class="form-row">
                    <div>
                        <label for="id_utilisateur">ID utilisateur</label>
                        <input id="id_utilisateur" type="number" min="1" max="999999999" name="id_utilisateur" value="<?= h($old['id_utilisateur']) ?>" required>
                        <?php if ($fieldErrors['id_utilisateur'] !== ''): ?><small style="color:#b91c1c;"><?= h($fieldErrors['id_utilisateur']) ?></small><?php endif; ?>
                    </div>
                    <div>
                        <label for="quantite">Quantite</label>
                        <input id="quantite" type="number" min="1" max="1000" value="<?= h($old['quantite']) ?>" name="quantite" required>
                        <?php if ($fieldErrors['quantite'] !== ''): ?><small style="color:#b91c1c;"><?= h($fieldErrors['quantite']) ?></small><?php endif; ?>
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label for="adresse_livraison">Adresse de livraison</label>
                    <textarea id="adresse_livraison" name="adresse_livraison" rows="4" required minlength="10" maxlength="255"><?= h($old['adresse_livraison']) ?></textarea>
                    <?php if ($fieldErrors['adresse_livraison'] !== ''): ?><small style="color:#b91c1c;"><?= h($fieldErrors['adresse_livraison']) ?></small><?php endif; ?>
                </div>

                <div style="margin-top:10px;">
                    <label for="statut">Statut commande</label>
                    <select id="statut" name="statut">
                        <option value="en-cours" <?= $old['statut'] === 'en-cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="confirmee" <?= $old['statut'] === 'confirmee' ? 'selected' : '' ?>>Confirmee</option>
                        <option value="annulee" <?= $old['statut'] === 'annulee' ? 'selected' : '' ?>>Annulee</option>
                    </select>
                    <?php if ($fieldErrors['statut'] !== ''): ?><small style="color:#b91c1c;"><?= h($fieldErrors['statut']) ?></small><?php endif; ?>
                </div>

                <button class="primary-btn" type="submit" style="margin-top:14px; width:100%;">Valider la commande</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
