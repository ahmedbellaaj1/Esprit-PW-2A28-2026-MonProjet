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
    'adresse_livraison' => trim((string) ($_GET['err_adresse_livraison'] ?? '')),
    'mode_livraison' => trim((string) ($_GET['err_mode_livraison'] ?? '')),
    'date_livraison_souhaitee' => trim((string) ($_GET['err_date_livraison_souhaitee'] ?? '')),
    'methode_paiement' => trim((string) ($_GET['err_methode_paiement'] ?? '')),
    'numero_carte' => trim((string) ($_GET['err_numero_carte'] ?? '')),
    'nom_titulaire' => trim((string) ($_GET['err_nom_titulaire'] ?? '')),
    'date_expiration' => trim((string) ($_GET['err_date_expiration'] ?? '')),
    'cvv' => trim((string) ($_GET['err_cvv'] ?? '')),
];
$old = [
    'id_utilisateur' => trim((string) ($_GET['old_id_utilisateur'] ?? '')),
    'quantite' => trim((string) ($_GET['old_quantite'] ?? '1')),
    'adresse_livraison' => trim((string) ($_GET['old_adresse_livraison'] ?? '')),
    'mode_livraison' => trim((string) ($_GET['old_mode_livraison'] ?? 'standard')),
    'date_livraison_souhaitee' => trim((string) ($_GET['old_date_livraison_souhaitee'] ?? '')),
    'methode_paiement' => trim((string) ($_GET['old_methode_paiement'] ?? 'cash')),
    'numero_carte' => trim((string) ($_GET['old_numero_carte'] ?? '')),
    'nom_titulaire' => trim((string) ($_GET['old_nom_titulaire'] ?? '')),
    'date_expiration' => trim((string) ($_GET['old_date_expiration'] ?? '')),
    'cvv' => trim((string) ($_GET['old_cvv'] ?? '')),
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
    <a class="navbar-logo" href="index.php">
        <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="navbar-logo-img">
        <span class="navbar-logo-text">Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#" class="active">Produits</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="#">Dons</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="index.php">Catalogue</a>
        <a href="cart.php" class="cart-icon" title="Voir le panier">
            🛒
            <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
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
            <div id="addToCartForm">
                <div style="display:flex;gap:1rem;align-items:flex-end;">
                    <div style="flex:1;">
                        <label for="cart_quantite">Quantité <span id="quantiteDispoDisplay" style="color:#64748b;font-size:0.9rem;"> (Dispo: <?= (int) $product['quantite_disponible'] ?>)</span></label>
                        <input id="cart_quantite" type="number" value="1" min="1" max="<?= (int) $product['quantite_disponible'] ?>" step="1" style="width:100%;" oninput="validateQuantityInput(this, <?= (int) $product['quantite_disponible'] ?>)">
                    </div>
                    <button type="button" class="primary-btn" onclick="addProductToCart(<?= (int) $product['id_produit'] ?>, '<?= h((string) $product['nom']) ?>', '<?= h((string) $product['marque']) ?>', <?= (float) $product['prix'] ?>, '<?= h($product['image'] ?: 'https://via.placeholder.com/400x400?text=Produit') ?>', <?= (int) $product['quantite_disponible'] ?>)">🛒 Ajouter au panier</button>
                </div>
                <div id="cartMessage" style="margin-top:1rem;padding:0.75rem;border-radius:8px;display:none;">
                </div>
                <div style="margin-top:1.5rem;padding:1rem;background:#f0fdf4;border-radius:8px;border-left:4px solid #16a34a;">
                    <p style="margin:0;color:#15803d;font-size:0.95rem;">💡 <strong>Vous pouvez ajouter plusieurs produits au panier</strong> et passer votre commande complète ensuite. Consultez votre panier pour finaliser votre commande.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/cart.js"></script>
</body>
</html>
