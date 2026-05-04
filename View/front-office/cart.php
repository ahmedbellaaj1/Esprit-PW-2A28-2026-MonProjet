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
    <style>
        /* Styles pour les onglets */
        .cart-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0;
        }

        .tab-button {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.3s ease;
            position: relative;
            bottom: -2px;
        }

        .tab-button:hover {
            color: #16a34a;
            background: rgba(22, 163, 74, 0.05);
        }

        .tab-button.active {
            color: #16a34a;
            border-bottom-color: #16a34a;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Styles pour l'historique */
        .historique-form-section {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .historique-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .status-en-cours {
            background: #fef3c7 !important;
            color: #92400e !important;
        }

        .status-en-preparation {
            background: #dbeafe !important;
            color: #1e40af !important;
        }

        .status-confirmee {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .status-livree {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .status-annulee {
            background: #fee2e2 !important;
            color: #991b1b !important;
        }

        .order-card {
            transition: box-shadow 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .cart-tabs {
                flex-direction: column;
                gap: 0;
            }

            .tab-button {
                border-bottom: 3px solid #e5e7eb;
                width: 100%;
                text-align: left;
                border-bottom-color: #e5e7eb;
            }

            .tab-button.active {
                border-left: 4px solid #16a34a;
                border-bottom: none;
                padding-left: 1.2rem;
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="navbar-logo" href="index.php">
        <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="navbar-logo-img">
        <span class="navbar-logo-text">Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="index.php">Produits</a></li>
        <li><a href="#">Dons</a></li>
        <li><a href="#">Magasins</a></li>
        <li><a href="order-history.php" title="Voir mon historique d'achats">📋 Historique</a></li>
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
    <!-- Onglets -->
    <div class="cart-tabs">
        <button id="tabPanier" class="tab-button active" onclick="switchTab('panier')">
            🛒 Mon Panier
        </button>
        <button id="tabHistorique" class="tab-button" onclick="switchTab('historique')">
         <a href="order-history.php" style="display:inline-block;margin-top:.75rem;color:#16a34a;font-size:.9rem;">📋 Voir l'historique</a>
        </button>
    </div>

    <!-- Contenu du Panier -->
    <div id="cartTab" class="tab-content active">
        <div class="section-heading">Votre panier 🛒</div>
        <div id="cartContent">
            <!-- Sera rempli par JavaScript -->
        </div>
    </div>

    <!-- Contenu de l'Historique -->
    <div id="historiqueTab" class="tab-content">
        <div class="section-heading">Historique d'Achats 📋</div>
        
        <!-- Formulaire pour ID utilisateur -->
        <div class="historique-form-section">
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="historiqueUserId" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Votre ID Utilisateur</label>
                    <input type="text" id="historiqueUserId" placeholder="Entrez votre ID (ex: 1)" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 1rem;">
                </div>
                <button type="button" onclick="loadOrderHistory()" class="primary-btn" style="padding: 0.75rem 1.5rem; cursor: pointer;">
                    Charger l'Historique
                </button>
            </div>
        </div>

        <!-- Contenu de l'historique -->
        <div id="historiqueContent" style="display: none;">
            <!-- Sera rempli par JavaScript -->
        </div>

        <!-- Message initial -->
        <div id="historiqueMessage" class="empty-cart" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <p>Entrez votre ID utilisateur pour voir votre historique d'achats</p>
        </div>
    </div>
</div>

<script src="../assets/cart.js"></script>
<script>

// ========== GESTION DES ONGLETS ==========

function switchTab(tabName) {
    // Masquer tous les onglets
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active');
    });

    // Afficher l'onglet sélectionné
    document.getElementById(tabName + 'Tab').classList.add('active');
    document.getElementById('tab' + (tabName === 'panier' ? 'Panier' : 'Historique')).classList.add('active');
}

// ========== GESTION DE L'HISTORIQUE D'ACHATS ==========

async function loadOrderHistory() {
    const userId = document.getElementById('historiqueUserId').value.trim();

    if (!userId || isNaN(userId)) {
        alert('Veuillez entrer un ID utilisateur valide');
        return;
    }

    try {
        // Afficher le chargement
        document.getElementById('historiqueMessage').style.display = 'flex';
        document.getElementById('historiqueMessage').innerHTML = '<p>Chargement...</p>';
        document.getElementById('historiqueContent').style.display = 'none';

        // Récupérer les données via l'API
        const response = await fetch(`../api/orders/history.php?id_user=${userId}`);
        const data = await response.json();

        if (!data.ok) {
            document.getElementById('historiqueMessage').innerHTML = `
                <div style="font-size: 2rem; margin-bottom: 1rem;">❌</div>
                <p>${data.error || 'Erreur lors du chargement'}</p>
            `;
            return;
        }

        if (data.data.length === 0) {
            document.getElementById('historiqueMessage').innerHTML = `
                <div style="font-size: 3rem; margin-bottom: 1rem;">😔</div>
                <p>Aucune commande trouvée pour votre compte</p>
                <p style="color: #6b7280; margin-top: 0.5rem;">Commencez vos achats dès maintenant!</p>
            `;
            document.getElementById('historiqueContent').style.display = 'none';
            return;
        }

        // Afficher les commandes
        displayOrderHistory(data.data, data.count, data.total_amount);
        document.getElementById('historiqueMessage').style.display = 'none';

    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('historiqueMessage').innerHTML = `
            <div style="font-size: 2rem; margin-bottom: 1rem;">⚠️</div>
            <p>Erreur réseau: ${error.message}</p>
        `;
    }
}

function displayOrderHistory(orders, count, totalAmount) {
    const container = document.getElementById('historiqueContent');

    let html = `
        <div class="historique-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #f3f4f6; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 0.85rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Commandes</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a; margin-top: 0.5rem;">${count}</div>
            </div>
            <div style="background: #f3f4f6; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 0.85rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Montant Total</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a; margin-top: 0.5rem;">${formatPrice(totalAmount)} €</div>
            </div>
        </div>

        <div class="historique-orders" style="display: grid; gap: 1rem;">
    `;

    orders.forEach(order => {
        const date = new Date(order.date_commande);
        const dateStr = date.toLocaleDateString('fr-FR') + ' à ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
        const statusColor = getStatusColor(order.statut);
        const prixUnitaire = order.prix_total / order.quantite;

        html += `
            <div class="order-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; overflow: hidden; transition: box-shadow 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
                    <div>
                        <div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">Commande #${order.id_commande}</div>
                        <div style="color: #6b7280; font-size: 0.9rem; margin-top: 0.25rem;">${dateStr}</div>
                    </div>
                    <span class="status-badge ${statusColor}" style="display: inline-block; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                        ${order.statut}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Produit</div>
                        <div style="color: #1f2937; font-weight: 500;">${h(order.produit_nom)}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Marque</div>
                        <div style="color: #1f2937; font-weight: 500;">${h(order.produit_marque || 'N/A')}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Quantité</div>
                        <div style="color: #1f2937; font-weight: 500;">${order.quantite}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Prix Unitaire</div>
                        <div style="color: #1f2937; font-weight: 500;">${formatPrice(prixUnitaire)} €</div>
                    </div>
                </div>

                <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; font-weight: 600; color: #1f2937;">
                        <span>Prix Total</span>
                        <span>${formatPrice(order.prix_total)} €</span>
                    </div>
                </div>

                <div style="background: #f3f4f6; padding: 1rem; border-radius: 4px;">
                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;">🚚 Livraison</div>
                    <div style="font-size: 0.9rem; color: #1f2937;">
                        <div><strong>Mode:</strong> ${order.mode_livraison === 'standard' ? 'Standard' : 'Express'}</div>
                        ${order.date_livraison_souhaitee ? `<div><strong>Date souhaitée:</strong> ${new Date(order.date_livraison_souhaitee).toLocaleDateString('fr-FR')}</div>` : ''}
                        <div style="margin-top: 0.5rem;"><strong>Adresse:</strong> ${h(order.adresse_livraison.substring(0, 50))}${order.adresse_livraison.length > 50 ? '...' : ''}</div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
    container.style.display = 'block';
}

function getStatusColor(statut) {
    const colors = {
        'en-cours': 'status-en-cours',
        'en-preparation': 'status-en-preparation',
        'confirmee': 'status-confirmee',
        'livree': 'status-livree',
        'annulee': 'status-annulee'
    };
    return colors[statut] || '';
}

// ========== GESTION DU PANIER ==========

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


<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>

</body>
</html>
