<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - GreenBite</title>
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
        <li><a href="#">Recettes</a></li>
        <li><a href="index.php">Produits</a></li>
        <li><a href="#">Dons</a></li>
        <li><a href="#">Magasins</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="../back-office/dashboard.php">Dashboard Admin</a>
        <a href="cart.php" class="cart-icon" title="Voir le panier">
            🛒
            <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<div class="main-container" style="padding-top:1.5rem;">
    <div class="section-heading">Votre panier 🛒</div>

    <div id="cartContent">
        <!-- Sera rempli par JavaScript -->
    </div>
</div>

<script>
// Afficher le panier
function renderCart() {
    const cartContent = document.getElementById('cartContent');
    const items = cart.getItems();

    if (items.length === 0) {
        cartContent.innerHTML = `
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <p>Votre panier est vide</p>
                <a href="index.php" class="primary-btn" style="display:inline-block;margin-top:1rem;">Continuer les achats</a>
            </div>
        `;
        return;
    }

    const cartHTML = `
        <div class="cart-container">
            <div class="cart-items-wrapper">
                ${items.map(item => `
                    <div class="cart-item">
                        <img src="${h(item.image)}" alt="${h(item.nom)}" class="cart-item-image">
                        <div class="cart-item-details">
                            <div>
                                <div class="cart-item-name">${h(item.nom)}</div>
                                <div class="cart-item-brand">${h(item.marque)}</div>
                                <div class="cart-item-price">${formatPrice(item.prix)} DT</div>
                            </div>
                            <div class="cart-item-controls">
                                <div class="cart-item-quantity">
                                    <button type="button" onclick="updateQuantity(${item.id_produit}, ${item.quantite - 1})">−</button>
                                    <input type="number" value="${item.quantite}" readonly>
                                    <button type="button" onclick="updateQuantity(${item.id_produit}, ${item.quantite + 1})">+</button>
                                </div>
                                <button class="cart-item-remove" type="button" onclick="removeFromCart(${item.id_produit})">Retirer</button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>

            <div class="cart-summary">
                <div class="cart-summary-title">Résumé de la commande</div>
                
                ${items.map(item => `
                    <div class="cart-summary-row">
                        <span>${h(item.nom)} × ${item.quantite}</span>
                        <span>${formatPrice(item.prix * item.quantite)} DT</span>
                    </div>
                `).join('')}

                <div class="cart-summary-row total">
                    <span>Total</span>
                    <span>${formatPrice(cart.getTotal())} DT</span>
                </div>

                <button type="button" class="primary-btn checkout-btn" onclick="goToCheckout()">Passer la commande</button>
                <a href="index.php" class="primary-btn" style="display:block;text-align:center;margin-top:0.5rem;background:rgba(15,118,110,0.1);color:#0f766e;text-decoration:none;">Continuer les achats</a>
            </div>
        </div>
    `;

    cartContent.innerHTML = cartHTML;
}

// Mettre à jour la quantité
function updateQuantity(productId, quantity) {
    cart.updateQuantity(productId, quantity);
    renderCart();
}

// Retirer du panier
function removeFromCart(productId) {
    cart.removeItem(productId);
    renderCart();
}

// Aller au checkout
function goToCheckout() {
    // Rediriger vers la page de checkout
    window.location.href = 'checkout.php';
}

// Fonction pour formater les prix
function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace('.', ',');
}

// Fonction pour échapper HTML
function h(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Afficher le panier au chargement
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    updateCartBadge();
});
</script>

<script src="../assets/cart.js"></script>
</body>
</html>
