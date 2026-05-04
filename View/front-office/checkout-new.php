<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💳 Paiement Sécurisé - GreenBite</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.navbar {
    background: white;
    padding: 15px 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    border-radius: 8px;
}

.navbar a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.payment-section {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.payment-title {
    font-size: 1.8em;
    color: #333;
    margin-bottom: 10px;
}

.payment-subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 0.9em;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 0.95em;
}

input, select, textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1em;
    font-family: inherit;
    transition: all 0.3s;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

#card-element {
    border: 2px solid #e0e0e0;
    padding: 12px 15px;
    border-radius: 8px;
    background: white;
    font-size: 1em;
}

#card-element.complete {
    border-color: #28a745;
}

#card-errors {
    color: #dc3545;
    margin: 10px 0;
    font-size: 0.9em;
    display: none;
}

.payment-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin: 30px 0;
}

.payment-method {
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s;
}

.payment-method:hover {
    border-color: #667eea;
    background: #f9f9f9;
}

.payment-method.active {
    border-color: #667eea;
    background: #f0f4ff;
}

.payment-method input {
    display: none;
}

.payment-method label {
    margin: 0;
    cursor: pointer;
    font-size: 1.1em;
}

.stripe-section {
    display: none;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    margin: 20px 0;
}

.stripe-section.active {
    display: block;
}

.stripe-section h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.1em;
}

.security-badge {
    background: #f0f4ff;
    border-left: 4px solid #667eea;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    color: #667eea;
    font-size: 0.9em;
}

.security-badge strong {
    color: #333;
}

button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.05em;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 20px;
}

button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.order-summary {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.summary-title {
    font-size: 1.5em;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-item-name {
    flex: 1;
}

.summary-item-qty {
    color: #999;
    font-size: 0.9em;
    margin: 0 15px;
}

.summary-item-price {
    font-weight: 600;
    color: #667eea;
    min-width: 80px;
    text-align: right;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 2px solid #667eea;
    font-size: 1.3em;
    font-weight: bold;
    color: #333;
}

.summary-total-price {
    color: #28a745;
}

.loading {
    display: none;
    text-align: center;
    color: #667eea;
}

.loading.active {
    display: block;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-message {
    display: none;
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.error-message.active {
    display: block;
}

.success-message {
    display: none;
    background: #d4edda;
    border: 1px solid #28a745;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.success-message.active {
    display: block;
}

@media (max-width: 768px) {
    .container {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php">← Retour à la boutique</a>
</div>

<div class="container">
    <!-- FORMULAIRE DE PAIEMENT -->
    <div class="payment-section">
        <h1 class="payment-title">💳 Paiement Sécurisé</h1>
        <p class="payment-subtitle">Complétez votre commande en toute sécurité</p>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage">✅ Paiement réussi! Redirection...</div>

        <form id="paymentForm">
            <!-- INFORMATIONS PERSONNELLES -->
            <h3 style="color: #333; margin-bottom: 20px; margin-top: 30px;">👤 Vos informations</h3>
            
            <div class="form-group">
                <label for="userId">ID Utilisateur</label>
                <input type="text" id="userId" name="userId" value="22" required>
            </div>

            <div class="form-group">
                <label for="address">Adresse de livraison</label>
                <textarea id="address" name="address" rows="3" placeholder="Entrez votre adresse complète" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="deliveryMode">Mode de livraison</label>
                    <select id="deliveryMode" name="deliveryMode" required>
                        <option value="standard">📦 Standard (5-7 jours)</option>
                        <option value="express">🚚 Express (2-3 jours)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deliveryDate">Date souhaitée</label>
                    <input type="date" id="deliveryDate" name="deliveryDate" required>
                </div>
            </div>

            <!-- MÉTHODE DE PAIEMENT -->
            <h3 style="color: #333; margin-bottom: 20px; margin-top: 30px;">💰 Méthode de paiement</h3>
            
            <div class="payment-methods">
                <div class="payment-method active">
                    <input type="radio" id="paymentStripe" name="paymentMethod" value="stripe" checked onchange="togglePaymentMethod()">
                    <label for="paymentStripe">💳 Carte Bancaire</label>
                </div>
                <div class="payment-method">
                    <input type="radio" id="paymentCash" name="paymentMethod" value="cash" onchange="togglePaymentMethod()">
                    <label for="paymentCash">💵 À la livraison</label>
                </div>
            </div>

            <!-- FORMULAIRE STRIPE -->
            <div class="stripe-section active" id="stripeSection">
                <h3>🔒 Informations de votre carte</h3>
                <div id="card-element"></div>
                <div id="card-errors"></div>

                <div class="security-badge">
                    <strong>✅ Paiement 100% sécurisé</strong><br>
                    Vos données bancaires sont chiffrées et traitées directement par Stripe.
                    Nous ne stockons jamais vos informations de carte.
                </div>

                <div style="background: #fef2f2; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <strong style="color: #991b1b;">🧪 Carte de test (Mode développement)</strong>
                    <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #7c2d12;">
                        Numéro: <code>4242 4242 4242 4242</code> | 
                        Date: <code>12/25</code> | 
                        CVC: <code>123</code>
                    </p>
                </div>
            </div>

            <!-- BOUTON DE CONFIRMATION -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Traitement du paiement...</p>
            </div>

            <button type="submit" id="submitBtn">✓ Confirmer la commande</button>
        </form>
    </div>

    <!-- RÉSUMÉ DE COMMANDE -->
    <div class="order-summary">
        <h2 class="summary-title">📋 Résumé</h2>

        <div id="summaryItems">
            <div class="summary-item">
                <div class="summary-item-name">Salade Verte Bio</div>
                <div class="summary-item-qty">×1</div>
                <div class="summary-item-price">5.99 DT</div>
            </div>
        </div>

        <div class="summary-total">
            <span>Total:</span>
            <span class="summary-total-price" id="totalPrice">5.99 DT</span>
        </div>

        <div style="background: #f0f4ff; padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 0.9em; color: #666;">
            <strong style="color: #333;">📦 Livraison estimée</strong>
            <p style="margin-top: 8px;">Selon le mode de livraison sélectionné</p>
        </div>
    </div>
</div>

<script src="../assets/cart.js"></script>
<script>
// ========== INITIALISATION STRIPE ==========
let stripe = null;
let elements = null;
let cardElement = null;

// Initialiser Stripe au chargement
window.addEventListener('load', async function() {
    try {
        // Charger la configuration Stripe
        const configResponse = await fetch('../api/payment/stripe.php?action=check_config');
        const config = await configResponse.json();

        if (!config.configured) {
            showError('❌ Paiement non configuré. Contattez le support.');
            return;
        }

        // Initialiser Stripe avec la clé publique
        stripe = Stripe(config.public_key);
        elements = stripe.elements();
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#333',
                    '::placeholder': {
                        color: '#ccc'
                    }
                },
                invalid: {
                    color: '#dc3545'
                }
            }
        });

        cardElement.mount('#card-element');

        // Écouteur pour les changements de carte
        cardElement.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
                displayError.style.display = 'block';
                cardElement.classList.remove('complete');
            } else {
                displayError.style.display = 'none';
                if (event.complete) {
                    cardElement.classList.add('complete');
                }
            }
        });

        // Charger le panier
        updateOrderSummary();

    } catch (error) {
        showError('Erreur lors du chargement: ' + error.message);
    }
});

// ========== GESTION DU FORMULAIRE ==========

function togglePaymentMethod() {
    const stripeSection = document.getElementById('stripeSection');
    const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

    if (selectedMethod === 'stripe') {
        stripeSection.classList.add('active');
    } else {
        stripeSection.classList.remove('active');
    }
}

function updateOrderSummary() {
    const cart = new Cart();
    const items = cart.getItems();
    const summaryItems = document.getElementById('summaryItems');
    const totalPrice = document.getElementById('totalPrice');

    if (items.length === 0) {
        summaryItems.innerHTML = '<p style="color: #999;">Votre panier est vide</p>';
        totalPrice.textContent = '0 DT';
        return;
    }

    const itemsHTML = items.map(item => `
        <div class="summary-item">
            <div class="summary-item-name">${item.nom}</div>
            <div class="summary-item-qty">×${item.quantite}</div>
            <div class="summary-item-price">${(item.prix * item.quantite).toFixed(2)} DT</div>
        </div>
    `).join('');

    summaryItems.innerHTML = itemsHTML;
    const total = items.reduce((sum, item) => sum + (item.prix * item.quantite), 0);
    totalPrice.textContent = total.toFixed(2) + ' DT';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.add('active');
    document.getElementById('loading').classList.remove('active');
    document.getElementById('submitBtn').disabled = false;
}

function showSuccess() {
    const successDiv = document.getElementById('successMessage');
    successDiv.classList.add('active');
    document.getElementById('loading').classList.add('active');
    setTimeout(() => {
        window.location.href = 'index.php?order=success';
    }, 2000);
}

// ========== GESTION DU PAIEMENT ==========

document.getElementById('paymentForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const userId = document.getElementById('userId').value;
    const address = document.getElementById('address').value;
    const deliveryMode = document.getElementById('deliveryMode').value;
    const deliveryDate = document.getElementById('deliveryDate').value;

    // Valider les champs
    if (!userId || !address || !deliveryDate) {
        showError('❌ Veuillez remplir tous les champs');
        return;
    }

    // Vérifier le panier
    const cart = new Cart();
    if (cart.getItems().length === 0) {
        showError('❌ Votre panier est vide');
        return;
    }

    document.getElementById('submitBtn').disabled = true;
    document.getElementById('loading').classList.add('active');
    document.getElementById('errorMessage').classList.remove('active');

    try {
        if (paymentMethod === 'stripe') {
            // PAIEMENT STRIPE
            await handleStripePayment(userId, address, deliveryMode, deliveryDate);
        } else {
            // PAIEMENT À LA LIVRAISON
            await handleCashPayment(userId, address, deliveryMode, deliveryDate);
        }
    } catch (error) {
        showError('❌ ' + error.message);
        document.getElementById('submitBtn').disabled = false;
    }
});

async function handleStripePayment(userId, address, deliveryMode, deliveryDate) {
    const cart = new Cart();
    const items = cart.getItems();
    const total = items.reduce((sum, item) => sum + (item.prix * item.quantite), 0);

    // Créer Payment Intent
    const piResponse = await fetch('../api/payment/stripe.php?action=create_payment_intent', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            amount: total,
            email: userId + '@greenbite.local',
            description: `Commande GreenBite - ${items.length} article(s)`
        })
    });

    const piData = await piResponse.json();
    if (!piData.ok) {
        throw new Error(piData.error || 'Erreur lors de la création du paiement');
    }

    // Confirmer le paiement
    const { paymentIntent, error } = await stripe.confirmCardPayment(piData.client_secret, {
        payment_method: {
            card: cardElement,
            billing_details: {email: userId + '@greenbite.local'}
        }
    });

    if (error) {
        throw new Error(error.message);
    }

    if (paymentIntent.status === 'succeeded') {
        await createOrder(userId, address, deliveryMode, deliveryDate, 'stripe', paymentIntent.id);
        showSuccess();
    } else {
        throw new Error('Paiement non confirmé');
    }
}

async function handleCashPayment(userId, address, deliveryMode, deliveryDate) {
    await createOrder(userId, address, deliveryMode, deliveryDate, 'cash', null);
    showSuccess();
}

async function createOrder(userId, address, deliveryMode, deliveryDate, method, paymentId) {
    const cart = new Cart();
    const items = cart.getItems();

    for (const item of items) {
        const total = item.prix * item.quantite;
        
        const response = await fetch('../api/checkout/create_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id_produit: item.id_produit,
                id_utilisateur: userId,
                quantite: item.quantite,
                prix_total: total,
                adresse_livraison: address,
                mode_livraison: deliveryMode,
                date_livraison_souhaitee: deliveryDate,
                methode_paiement: method,
                stripe_payment_intent_id: paymentId
            })
        });

        const result = await response.json();
        if (!result.ok) {
            throw new Error(result.error || 'Erreur lors de la création de la commande');
        }
    }

    // Vider le panier
    cart.clear();
}
</script>

</body>
</html>
