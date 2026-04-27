<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$errors = [];
$old = [
    'id_utilisateur' => trim((string) ($_POST['id_utilisateur'] ?? '')),
    'adresse_livraison' => trim((string) ($_POST['adresse_livraison'] ?? '')),
    'mode_livraison' => trim((string) ($_POST['mode_livraison'] ?? 'standard')),
    'date_livraison_souhaitee' => trim((string) ($_POST['date_livraison_souhaitee'] ?? '')),
    'methode_paiement' => trim((string) ($_POST['methode_paiement'] ?? 'cash')),
    'numero_carte' => trim((string) ($_POST['numero_carte'] ?? '')),
    'nom_titulaire' => trim((string) ($_POST['nom_titulaire'] ?? '')),
    'date_expiration' => trim((string) ($_POST['date_expiration'] ?? '')),
    'cvv' => trim((string) ($_POST['cvv'] ?? '')),
];

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les produits du panier depuis les données POST
    $cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];
    
    // Valider les données
    if (empty($old['id_utilisateur'])) {
        $errors['id_utilisateur'] = 'ID utilisateur requis';
    }
    if (empty($old['adresse_livraison'])) {
        $errors['adresse_livraison'] = 'Adresse de livraison requise';
    }
    if (empty($old['date_livraison_souhaitee'])) {
        $errors['date_livraison_souhaitee'] = 'Date de livraison requise';
    }
    if ($old['methode_paiement'] === 'carte') {
        if (empty($old['numero_carte'])) {
            $errors['numero_carte'] = 'Numéro de carte requis';
        }
        if (empty($old['nom_titulaire'])) {
            $errors['nom_titulaire'] = 'Nom du titulaire requis';
        }
        if (empty($old['date_expiration'])) {
            $errors['date_expiration'] = 'Date d\'expiration requise';
        }
        if (empty($old['cvv'])) {
            $errors['cvv'] = 'CVV requis';
        }
    }
    if (empty($cart_items)) {
        $errors['cart'] = 'Votre panier est vide';
    }

    // Vérifier la disponibilité du stock
    if (empty($errors) && !empty($cart_items)) {
        $productController = new ProductController();
        foreach ($cart_items as $item) {
            $product = $productController->find((int) $item['id_produit']);
            if (!$product) {
                $errors['cart'] = "Produit ID {$item['id_produit']} introuvable";
                break;
            }
            if ((int) $item['quantite'] > (int) ($product['quantite_disponible'] ?? 0)) {
                $errors['stock'] = "Stock insuffisant pour {$product['nom']}. Disponible: {$product['quantite_disponible']}, Demandé: {$item['quantite']}";
                break;
            }
        }
    }

    // Si pas d'erreurs, créer les commandes
    if (empty($errors) && !empty($cart_items)) {
        require_once __DIR__ . '/../../Controller/OrderController.php';
        require_once __DIR__ . '/../../Model/Order.php';

        $controller = new OrderController();
        $productController = new ProductController();
        $all_ok = true;

        foreach ($cart_items as $item) {
            $order_data = [
                'id_produit' => (int) $item['id_produit'],
                'id_utilisateur' => (int) $old['id_utilisateur'],
                'quantite' => (int) $item['quantite'],
                'prix_total' => (float) $item['prix'] * (int) $item['quantite'],
                'adresse_livraison' => $old['adresse_livraison'],
                'mode_livraison' => $old['mode_livraison'],
                'date_livraison_souhaitee' => $old['date_livraison_souhaitee'],
                'methode_paiement' => $old['methode_paiement'],
                'numero_carte' => $old['numero_carte'] ?? '',
                'nom_titulaire' => $old['nom_titulaire'] ?? '',
                'date_expiration' => $old['date_expiration'] ?? '',
                'cvv' => $old['cvv'] ?? '',
            ];

            $result = $controller->save($order_data);
            if (!$result['ok']) {
                $all_ok = false;
                break;
            }
            
            // Réduire la quantité disponible du produit
            $productController->decreaseQuantity((int) $item['id_produit'], (int) $item['quantite']);
        }

        if ($all_ok) {
            redirect('index.php?order=success');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passer la commande - GreenBite</title>
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
    <div class="section-heading">Passer votre commande</div>

    <?php if (!empty($errors)): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:1rem;margin-bottom:1.5rem;color:#991b1b;">
            <strong>Erreur:</strong>
            <ul style="margin:0.5rem 0 0 1.5rem;">
                <?php foreach ($errors as $field => $message): ?>
                    <li><?= h($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="cart-container">
        <div class="cart-items-wrapper">
            <h3 style="margin-top:0;">Articles à commander</h3>
            <div id="checkoutItems">
                <!-- Sera rempli par JavaScript -->
            </div>

            <form method="post" action="checkout.php" id="checkoutForm">
                <input type="hidden" id="cartItemsInput" name="cart_items" value="">

                <div style="margin-top:2rem;padding:1.5rem;background:#f0fdf4;border-radius:8px;border-left:4px solid #16a34a;">
                    <h3 style="margin-top:0;color:#15803d;">Informations de livraison</h3>

                    <div class="form-row">
                        <div>
                            <label for="id_utilisateur">ID utilisateur</label>
                            <input id="id_utilisateur" type="text" name="id_utilisateur" value="<?= h($old['id_utilisateur']) ?>" required>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">
                        <label for="adresse_livraison">Adresse de livraison</label>
                        <textarea id="adresse_livraison" name="adresse_livraison" rows="4" required><?= h($old['adresse_livraison']) ?></textarea>
                    </div>

                    <div style="margin-top:1rem;">
                        <label for="mode_livraison">Mode de livraison 🚚</label>
                        <select id="mode_livraison" name="mode_livraison">
                            <option value="standard" <?= $old['mode_livraison'] === 'standard' ? 'selected' : '' ?>>Standard (5-7 jours)</option>
                            <option value="express" <?= $old['mode_livraison'] === 'express' ? 'selected' : '' ?>>Express (2-3 jours)</option>
                        </select>
                    </div>

                    <div style="margin-top:1rem;">
                        <label for="date_livraison_souhaitee">Date de livraison souhaitée 📅</label>
                        <input id="date_livraison_souhaitee" type="date" name="date_livraison_souhaitee" value="<?= h($old['date_livraison_souhaitee']) ?>" required>
                    </div>
                </div>

                <div style="margin-top:2rem;padding:1.5rem;background:#fef3f2;border-radius:8px;border-left:4px solid #ea580c;">
                    <h3 style="margin-top:0;color:#b42318;">Méthode de paiement 💳</h3>

                    <div style="margin-top:1rem;">
                        <label for="methode_paiement">Choisir une méthode de paiement</label>
                        <select id="methode_paiement" name="methode_paiement" onchange="togglePaymentForm()">
                            <option value="cash" <?= $old['methode_paiement'] === 'cash' ? 'selected' : '' ?>>Paiement à la livraison (Cash)</option>
                            <option value="carte" <?= $old['methode_paiement'] === 'carte' ? 'selected' : '' ?>>Paiement par carte bancaire</option>
                        </select>
                    </div>

                    <!-- Formulaire de paiement par carte (caché par défaut) -->
                    <div id="payment-form" style="display:none; margin-top:15px; padding:15px; background:white; border-radius:8px; border:1px solid #e2e8f0;">
                        <h4 style="margin-top:0; color:#0f172a;">Informations de paiement</h4>
                        
                        <div style="margin-bottom:10px;">
                            <label for="numero_carte">Numéro de carte bancaire</label>
                            <input id="numero_carte" type="text" name="numero_carte" value="<?= h($old['numero_carte']) ?>" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="nom_titulaire">Nom du titulaire</label>
                            <input id="nom_titulaire" type="text" name="nom_titulaire" value="<?= h($old['nom_titulaire']) ?>" placeholder="Jean Dupont">
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="date_expiration">Date d'expiration</label>
                                <input id="date_expiration" type="text" name="date_expiration" value="<?= h($old['date_expiration']) ?>" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div>
                                <label for="cvv">CVV</label>
                                <input id="cvv" type="text" name="cvv" value="<?= h($old['cvv']) ?>" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <small style="color:#64748b; display:block; margin-top:8px;">Vos données de carte sont sécurisées et chiffrées.</small>
                    </div>
                </div>

                <button class="primary-btn" type="submit" style="margin-top:2rem; width:100%; padding:1rem; font-size:1rem;">✓ Confirmer la commande</button>
            </form>
        </div>

        <div class="cart-summary">
            <div class="cart-summary-title">Résumé total</div>
            <div id="summaryItems">
                <!-- Sera rempli par JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Afficher les articles du panier
function renderCheckout() {
    const items = cart.getItems();
    const checkoutItems = document.getElementById('checkoutItems');
    const summaryItems = document.getElementById('summaryItems');

    if (items.length === 0) {
        checkoutItems.innerHTML = '<p style="color:#64748b;">Votre panier est vide. <a href="cart.php">Retour au panier</a></p>';
        return;
    }

    // Remplir les articles
    const itemsHTML = items.map(item => `
        <div style="padding:1rem;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:1rem;display:flex;gap:1rem;">
            <img src="${h(item.image)}" alt="${h(item.nom)}" style="width:80px;height:80px;object-fit:cover;border-radius:4px;">
            <div style="flex:1;">
                <div style="font-weight:600;color:#0f172a;">${h(item.nom)}</div>
                <div style="font-size:0.85rem;color:#64748b;margin-top:2px;">${h(item.marque)}</div>
                <div style="margin-top:0.5rem;color:#0f766e;font-weight:600;">${formatPrice(item.prix)} DT × ${item.quantite} = ${formatPrice(item.prix * item.quantite)} DT</div>
            </div>
        </div>
    `).join('');

    checkoutItems.innerHTML = itemsHTML;

    // Remplir le résumé
    const summaryHTML = `
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
    `;

    summaryItems.innerHTML = summaryHTML;

    // Mettre à jour le champ caché avec les articles du panier
    document.getElementById('cartItemsInput').value = JSON.stringify(items);
}

// Afficher/masquer le formulaire de paiement
function togglePaymentForm() {
    const methodePaiement = document.getElementById('methode_paiement').value;
    const paymentForm = document.getElementById('payment-form');
    
    if (methodePaiement === 'carte') {
        paymentForm.style.display = 'block';
        document.getElementById('numero_carte').required = true;
        document.getElementById('nom_titulaire').required = true;
        document.getElementById('date_expiration').required = true;
        document.getElementById('cvv').required = true;
    } else {
        paymentForm.style.display = 'none';
        document.getElementById('numero_carte').required = false;
        document.getElementById('nom_titulaire').required = false;
        document.getElementById('date_expiration').required = false;
        document.getElementById('cvv').required = false;
    }
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

// Formatage des champs de carte
document.addEventListener('DOMContentLoaded', () => {
    renderCheckout();
    togglePaymentForm();
    updateCartBadge();

    // Formater le numéro de carte
    const carteInput = document.getElementById('numero_carte');
    if (carteInput) {
        carteInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });
    }

    // Formater la date d'expiration
    const dateExpInput = document.getElementById('date_expiration');
    if (dateExpInput) {
        dateExpInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
});
</script>

<script src="../assets/cart.js"></script>
</body>
</html>
