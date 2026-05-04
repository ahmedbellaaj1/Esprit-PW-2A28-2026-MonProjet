<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande – GreenBite</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
    <style>
        .checkout-page { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 4rem; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 2rem; }
        @media(max-width:900px){ .checkout-grid{ grid-template-columns:1fr; } }

        .co-card {
            background: white; border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            padding: 2rem; margin-bottom: 1.5rem;
        }
        .co-card h2 {
            font-size: 1.05rem; font-weight: 700; color: #0f172a;
            margin: 0 0 1.25rem; display: flex; align-items: center; gap: .5rem;
        }

        /* Steps */
        .steps { display: flex; gap: 0; margin-bottom: 2rem; }
        .step {
            flex: 1; text-align: center; padding: .75rem .5rem;
            font-size: .82rem; font-weight: 600; color: #94a3b8;
            border-bottom: 3px solid #e2e8f0; transition: all .3s;
        }
        .step.active { color: #16a34a; border-bottom-color: #16a34a; }
        .step.done   { color: #64748b; border-bottom-color: #94a3b8; }

        /* Form */
        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block; font-size: .85rem; font-weight: 600;
            color: #374151; margin-bottom: .4rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: .7rem .9rem;
            border: 1.5px solid #e2e8f0; border-radius: 9px;
            font-size: .95rem; font-family: inherit;
            transition: border-color .2s; background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none; border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,.1);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media(max-width:600px){ .form-row{ grid-template-columns:1fr; } }

        /* Payment methods */
        .pay-methods { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: 1.25rem; }
        .pay-method {
            border: 2px solid #e2e8f0; border-radius: 12px;
            padding: 1rem; cursor: pointer; text-align: center;
            transition: all .2s; background: white;
        }
        .pay-method:hover { border-color: #16a34a; background: #f0fdf4; }
        .pay-method.selected { border-color: #16a34a; background: #f0fdf4; }
        .pay-method input[type=radio] { display: none; }
        .pay-method .pm-icon { font-size: 1.8rem; display: block; margin-bottom: .3rem; }
        .pay-method .pm-label { font-weight: 600; color: #0f172a; font-size: .9rem; }
        .pay-method .pm-sub { font-size: .75rem; color: #64748b; }

        /* Card fields (simulated) */
        .card-fields { display: none; }
        .card-fields.show { display: block; }
        .card-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Order summary sidebar */
        .summary-sticky { position: sticky; top: 100px; }
        .summary-item {
            display: flex; justify-content: space-between;
            align-items: center; padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9; gap: .5rem;
        }
        .summary-item:last-child { border-bottom: none; }
        .si-img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
        .si-name { font-weight: 600; color: #0f172a; font-size: .88rem; flex: 1; }
        .si-qty  { color: #64748b; font-size: .8rem; }
        .si-price{ font-weight: 700; color: #16a34a; font-size: .9rem; white-space: nowrap; }
        .summary-total {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1rem 0 0; font-size: 1.15rem; font-weight: 800;
            color: #0f172a; border-top: 2px solid #e2e8f0; margin-top: .5rem;
        }
        .summary-total span:last-child { color: #16a34a; }

        /* Submit btn */
        .btn-order {
            width: 100%; padding: .9rem;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white; border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: all .25s; margin-top: 1.25rem;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-order:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(22,163,74,.3); }
        .btn-order:disabled { opacity: .6; cursor: not-allowed; }

        /* Alerts */
        .alert { padding: .9rem 1.2rem; border-radius: 10px; margin-bottom: 1rem; font-weight: 500; display: none; }
        .alert.show { display: flex; align-items: center; gap: .6rem; }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; }

        /* Security badge */
        .secure-badge {
            display: flex; align-items: center; gap: .6rem;
            background: #f0fdf4; border: 1px solid #86efac;
            border-radius: 9px; padding: .75rem 1rem;
            font-size: .82rem; color: #15803d; margin-top: 1rem;
        }

        /* Empty cart */
        .empty-checkout {
            text-align: center; padding: 4rem 2rem;
            color: #64748b;
        }
        .empty-checkout .ec-icon { font-size: 3rem; margin-bottom: 1rem; }

        /* Spinner */
        .spinner-sm {
            width: 18px; height: 18px;
            border: 3px solid rgba(255,255,255,.3);
            border-top-color: white; border-radius: 50%;
            animation: spin .7s linear infinite; display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Success overlay */
        .success-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.7); z-index: 999;
            align-items: center; justify-content: center;
        }
        .success-overlay.show { display: flex; }
        .success-box {
            background: white; border-radius: 20px; padding: 3rem 2.5rem;
            text-align: center; max-width: 420px; width: 90%;
            animation: popIn .4s ease;
        }
        @keyframes popIn { from{transform:scale(.8);opacity:0} to{transform:scale(1);opacity:1} }
        .success-box .sb-icon { font-size: 4rem; margin-bottom: 1rem; }
        .success-box h2 { color: #0f172a; margin: 0 0 .5rem; font-size: 1.5rem; }
        .success-box p  { color: #64748b; margin: 0 0 1.5rem; }
        .test-card-box {
            background: #f8fafc; border-radius: 10px; padding: 1rem;
            font-size: .82rem; color: #475569; margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }
        .test-card-box code { background: #e2e8f0; padding: .1rem .4rem; border-radius: 4px; }
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
        <li><a href="index.php">Produits</a></li>
        <li><a href="cart.php">🛒 Panier</a></li>
        <li><a href="order-history.php">📋 Historique</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="../back-office/dashboard.php">Dashboard Admin</a>
        <a href="cart.php" class="cart-icon">🛒
            <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<!-- Success overlay -->
<div class="success-overlay" id="successOverlay">
    <div class="success-box">
        <div class="sb-icon">✅</div>
        <h2>Commande confirmée !</h2>
        <p id="successMsg">Votre commande a été enregistrée avec succès.</p>
        <a href="index.php" class="primary-btn" style="display:inline-block;margin-top:.5rem;">🛍 Continuer les achats</a>
        <br>
        <a href="order-history.php" style="display:inline-block;margin-top:.75rem;color:#16a34a;font-size:.9rem;">📋 Voir l'historique</a>
    </div>
</div>

<div class="main-container">
<div class="checkout-page">

    <div class="section-heading">Finaliser la commande 🛒</div>

    <!-- Steps -->
    <div class="steps">
        <div class="step done">1 · Panier</div>
        <div class="step active">2 · Livraison & Paiement</div>
        <div class="step">3 · Confirmation</div>
    </div>

    <div id="emptyState" class="empty-checkout" style="display:none;">
        <div class="ec-icon">🛒</div>
        <p style="font-size:1.1rem;font-weight:600;color:#0f172a;">Votre panier est vide</p>
        <a href="index.php" class="primary-btn" style="display:inline-block;margin-top:1rem;">Parcourir les produits</a>
    </div>

    <div class="checkout-grid" id="checkoutGrid">
        <!-- LEFT COLUMN -->
        <div>
            <!-- Alert -->
            <div class="alert alert-error" id="alertError">
                <span>⚠️</span><span id="alertErrorText"></span>
            </div>

            <!-- Infos livraison -->
            <div class="co-card">
                <h2>📦 Informations de livraison</h2>

                <div class="form-group">
                    <label for="userId">ID Utilisateur *</label>
                    <input type="number" id="userId" min="1" placeholder="Ex : 1" required>
                </div>

                <div class="form-group">
                    <label for="address">Adresse de livraison *</label>
                    <textarea id="address" rows="2" placeholder="Rue, ville, code postal…" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="deliveryMode">Mode de livraison</label>
                        <select id="deliveryMode">
                            <option value="standard">📦 Standard (5-7 jours)</option>
                            <option value="express">🚀 Express (2-3 jours)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="deliveryDate">Date souhaitée *</label>
                        <input type="date" id="deliveryDate" required>
                    </div>
                </div>
            </div>

            <!-- Méthode de paiement -->
            <div class="co-card">
                <h2>💳 Méthode de paiement</h2>

                <div class="pay-methods">
                    <div class="pay-method selected" id="pm-cash" onclick="selectPayment('cash')">
                        <input type="radio" name="pm" value="cash" checked>
                        <span class="pm-icon">💵</span>
                        <span class="pm-label">À la livraison</span>
                        <span class="pm-sub">Payez en cash</span>
                    </div>
                    <div class="pay-method" id="pm-carte" onclick="selectPayment('carte')">
                        <input type="radio" name="pm" value="carte">
                        <span class="pm-icon">💳</span>
                        <span class="pm-label">Carte bancaire</span>
                        <span class="pm-sub">Paiement simulé</span>
                    </div>
                </div>

                <!-- Carte fields (simulated – no real Stripe key needed) -->
                <div class="card-fields" id="cardFields">
                    <div class="form-group">
                        <label>Numéro de carte</label>
                        <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19"
                               oninput="formatCard(this)">
                    </div>
                    <div class="card-field-row">
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <input type="text" id="cardExpiry" placeholder="MM/AA" maxlength="5"
                                   oninput="formatExpiry(this)">
                        </div>
                        <div class="form-group">
                            <label>CVC</label>
                            <input type="text" id="cardCvc" placeholder="123" maxlength="3">
                        </div>
                    </div>
                    <div class="test-card-box">
                        🧪 <strong>Test :</strong>
                        Carte <code>4242 4242 4242 4242</code> &nbsp;|&nbsp;
                        Exp <code>12/26</code> &nbsp;|&nbsp;
                        CVC <code>123</code>
                    </div>
                </div>

                <div class="secure-badge">
                    🔒 <span><strong>Paiement sécurisé</strong> — Vos données ne sont jamais stockées</span>
                </div>
            </div>

            <!-- Submit -->
            <button class="btn-order" id="submitBtn" onclick="placeOrder()">
                <span class="spinner-sm" id="spinner"></span>
                <span id="submitLabel">✅ Confirmer la commande</span>
            </button>
        </div>

        <!-- RIGHT COLUMN – Summary -->
        <div class="summary-sticky">
            <div class="co-card">
                <h2>📋 Résumé de la commande</h2>
                <div id="summaryItems"></div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="summaryTotal">0,00 DT</span>
                </div>
                <a href="cart.php" style="display:block;text-align:center;margin-top:1rem;font-size:.85rem;color:#16a34a;">← Modifier le panier</a>
            </div>
        </div>
    </div>

</div>
</div>

<script src="../assets/cart.js"></script>
<script>
// ── Set min date to tomorrow ─────────────────────────────────────────────────
(function() {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    document.getElementById('deliveryDate').min = d.toISOString().split('T')[0];
    document.getElementById('deliveryDate').value = d.toISOString().split('T')[0];
})();

// ── Load cart into summary ────────────────────────────────────────────────────
function renderSummary() {
    const items = cart.getItems();
    const container = document.getElementById('summaryItems');
    const totalEl   = document.getElementById('summaryTotal');
    const grid      = document.getElementById('checkoutGrid');
    const empty     = document.getElementById('emptyState');

    if (items.length === 0) {
        grid.style.display  = 'none';
        empty.style.display = 'block';
        document.getElementById('submitBtn').style.display = 'none';
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="summary-item">
            <img class="si-img" src="${hh(item.image)}" alt="${hh(item.nom)}"
                 onerror="this.src='https://placehold.co/44x44?text=?'">
            <div style="flex:1;min-width:0;">
                <div class="si-name">${hh(item.nom)}</div>
                <div class="si-qty">${hh(item.marque)} · ×${item.quantite}</div>
            </div>
            <div class="si-price">${fmt(item.prix * item.quantite)} DT</div>
        </div>
    `).join('');

    totalEl.textContent = fmt(cart.getTotal()) + ' DT';
}

// ── Payment method selection ──────────────────────────────────────────────────
function selectPayment(method) {
    document.querySelectorAll('.pay-method').forEach(el => el.classList.remove('selected'));
    document.getElementById('pm-' + method).classList.add('selected');
    document.querySelector('#pm-' + method + ' input').checked = true;
    const cf = document.getElementById('cardFields');
    cf.classList.toggle('show', method === 'carte');
}

// ── Card formatting ───────────────────────────────────────────────────────────
function formatCard(el) {
    let v = el.value.replace(/\D/g,'').substring(0,16);
    el.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function formatExpiry(el) {
    let v = el.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
    el.value = v;
}

// ── Validate card fields ──────────────────────────────────────────────────────
function validateCard() {
    const num = document.getElementById('cardNumber').value.replace(/\s/g,'');
    const exp = document.getElementById('cardExpiry').value;
    const cvc = document.getElementById('cardCvc').value;
    if (num.length < 16) return 'Numéro de carte invalide (16 chiffres requis).';
    if (!/^\d{2}\/\d{2}$/.test(exp)) return 'Date d\'expiration invalide (MM/AA).';
    if (cvc.length < 3) return 'CVC invalide (3 chiffres requis).';
    return null;
}

// ── Place order ───────────────────────────────────────────────────────────────
async function placeOrder() {
    hideError();

    const userId   = document.getElementById('userId').value.trim();
    const address  = document.getElementById('address').value.trim();
    const mode     = document.getElementById('deliveryMode').value;
    const date     = document.getElementById('deliveryDate').value;
    const method   = document.querySelector('input[name="pm"]:checked').value;

    // Validations
    if (!userId || isNaN(userId) || parseInt(userId) < 1) return showError('ID utilisateur invalide.');
    if (!address) return showError('Adresse de livraison requise.');
    if (!date)    return showError('Date de livraison requise.');

    if (method === 'carte') {
        const cardErr = validateCard();
        if (cardErr) return showError(cardErr);
    }

    const items = cart.getItems();
    if (items.length === 0) return showError('Votre panier est vide.');

    // Show loading
    setLoading(true);

    let allOk = true;
    let errorMsg = '';

    for (const item of items) {
        const total = parseFloat((item.prix * item.quantite).toFixed(2));
        try {
            const res  = await fetch('../../api/checkout/create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_produit:               item.id_produit,
                    id_utilisateur:           parseInt(userId),
                    quantite:                 item.quantite,
                    prix_total:               total,
                    adresse_livraison:        address,
                    mode_livraison:           mode,
                    date_livraison_souhaitee: date,
                    methode_paiement:         method,
                    stripe_payment_intent_id: '',
                })
            });
            const data = await res.json();
            if (!data.ok) { allOk = false; errorMsg = data.error || 'Erreur inconnue'; break; }
        } catch(e) {
            allOk = false; errorMsg = 'Erreur réseau : ' + e.message; break;
        }
    }

    setLoading(false);

    if (allOk) {
        cart.clear();
        updateCartBadge();
        const label = method === 'carte' ? 'Paiement par carte confirmé.' : 'Paiement à la livraison enregistré.';
        document.getElementById('successMsg').textContent = label + ' Merci pour votre commande !';
        document.getElementById('successOverlay').classList.add('show');
    } else {
        showError('Erreur : ' + errorMsg);
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function setLoading(on) {
    document.getElementById('submitBtn').disabled = on;
    document.getElementById('spinner').style.display = on ? 'block' : 'none';
    document.getElementById('submitLabel').textContent = on ? 'Traitement…' : '✅ Confirmer la commande';
}
function showError(msg) {
    document.getElementById('alertErrorText').textContent = msg;
    document.getElementById('alertError').classList.add('show');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function hideError() { document.getElementById('alertError').classList.remove('show'); }
function fmt(n) { return parseFloat(n).toFixed(2).replace('.', ','); }
function hh(s) {
    const d = document.createElement('div');
    d.textContent = String(s || '');
    return d.innerHTML;
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    renderSummary();
    updateCartBadge();
});
</script>

<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>
</html>
