<?php
// La session est déjà démarrée par le Front Controller (index.php)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Greenbite Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="navbar-logo" href="<?= BASE_URL ?>index.php?page=dashboard">Greenbite</a>

    <div class="navbar-links">
        <a class="active" href="<?= BASE_URL ?>index.php?page=dashboard">Dashboard</a>
        <a href="<?= BASE_URL ?>index.php?page=admin">Back Office</a>
    </div>

    <div class="navbar-right">
        <div class="nav-avatar"><i class="fas fa-user"></i></div>
        <a href="<?= BASE_URL ?>api/logout.php" 
           style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.9rem; font-weight:500; display:flex; align-items:center; gap:6px; padding:8px 16px; border:1px solid rgba(255,255,255,0.4); border-radius:9999px; transition:all 0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.15)'" 
           onmouseout="this.style.background='transparent'"
           title="Se déconnecter">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</nav>

<!-- CONTENU -->
<main class="page-content">

    <!-- 🤖 FILTRE IA -->
    <div class="ai-filter-section" style="margin-bottom: 30px; background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <h3 style="margin-bottom: 15px; color: #0f766e; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-robot"></i> Filtrage Intelligent (IA)
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Saisissez vos besoins en temps réel pour filtrer les produits.</p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Préférences</label>
                <input type="text" id="ai-prefs" placeholder="Ex: Végétarien, Bio, Sans gluten..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 14px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Allergies</label>
                <input type="text" id="ai-allergies" placeholder="Ex: Lactose, Arachides..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 14px;">
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button onclick="applyAIFilter()" style="padding: 12px 30px; background: #0f766e; color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#115e59'" onmouseout="this.style.background='#0f766e'">
                    <i class="fas fa-magic"></i> Lancer l'IA
                </button>
            </div>
        </div>
    </div>

    <!-- 🔍 RECHERCHE -->
    <div class="search-section">
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Rechercher un produit..." class="search-input">
            <button class="search-btn" onclick="searchProducts()" title="Rechercher">
                <i class="fas fa-search"></i>
            </button>
            <button class="scan-btn" id="sort-btn" onclick="toggleSort()" title="Trier par prix (Croissant)">
                <i class="fas fa-sort-amount-down"></i>
            </button>
            <button class="scan-btn" onclick="scanBarcode()" title="Scanner">
                <i class="fas fa-qrcode"></i>
            </button>
        </div>

        <div class="filters">
            <select id="category-filter" class="filter-select" onchange="searchProducts()">
                <option value="">Toutes catégories</option>
                <option value="Salades">Salades</option>
                <option value="Fast Food">Fast Food</option>
                <option value="Produits Laitiers">Produits Laitiers</option>
                <option value="Boulangerie">Boulangerie</option>
                <option value="Boissons">Boissons</option>
                <option value="Plats préparés">Plats préparés</option>
            </select>
        </div>
    </div>

    <!-- Contenu dynamique -->
    <div id="dynamic-content" style="margin-top: 30px;">
        <h2 id="section-title" style="margin-bottom: 24px; color: #1e293b; font-size: 24px;">Suggestions pour vous</h2>
        <div id="main-grid" class="products-grid">
            <!-- Produits chargés dynamiquement -->
        </div>
    </div>

    <!-- 📦 MODAL -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="product-details"></div>
        </div>
    </div>

</main>

<!-- 🤖 CHATBOT -->
<div class="chatbot-bubble" onclick="toggleChat()" title="Besoin d'aide ?">
    <i class="fas fa-comment-dots"></i>
</div>

<div id="chat-window" class="chat-window">
    <div class="chat-header">
        <div class="bot-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chat-header-info">
            <h3>Assistant Greenbite</h3>
            <p>En ligne • Répond instantanément</p>
        </div>
        <div class="chat-close" onclick="toggleChat()">
            <i class="fas fa-times"></i>
        </div>
    </div>
    <div id="chat-messages" class="chat-messages">
        <div class="message message-bot">👋 Bonjour ! Je suis votre assistant Greenbite. Comment puis-je vous aider aujourd'hui ?</div>
    </div>
    <div id="typing-indicator" class="typing">L'assistant écrit...</div>
    <div class="chat-input-area">
        <input type="text" id="chat-input" class="chat-input" placeholder="Votre message..." onkeypress="handleChatKey(event)">
        <button class="chat-send-btn" onclick="sendChatMessage()">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<!-- ================= JS ================= -->
<script>

// 🔄 Charger au démarrage
document.addEventListener('DOMContentLoaded', () => {
    loadGrid('', '');
});

// 🎨 Affichage des produits dans la grille
function renderProducts(data, isSearch = true) {
    const grid = document.getElementById('main-grid');
    if (!Array.isArray(data) || data.length === 0) {
        grid.innerHTML = '<p style="text-align: center; padding: 40px; color: #64748b;">Aucun produit trouvé.</p>';
        return;
    }
    grid.innerHTML = data.map(p => `
        <div class="product-card" onclick="showProductDetails(${p.id})">
            <div class="product-image">
                <i class="fas ${isSearch ? 'fa-utensils' : 'fa-star'}"></i>
            </div>
            <div class="product-info">
                <div class="product-name" style="display:flex; justify-content:space-between; align-items:center;">
                    <span>${p.nom}</span>
                    ${p.prix && parseFloat(p.prix) > 0 ? `<span style="color:#0f766e; font-weight:bold; font-size:0.9em;">${parseFloat(p.prix).toFixed(2)} DT</span>` : ''}
                </div>
                <div class="product-category">${p.categorie}</div>
                <div style="font-size: 0.85em; color: #64748b; margin-top: 5px; margin-bottom: 8px;">
                    <i class="fas fa-fire" style="color: #ef4444;"></i> ${p.calories || 0} kcal
                </div>
                <div class="product-status">
                    ${getBadges(p)}
                </div>
            </div>
        </div>
    `).join('');
}

// 🔍 Charger produits et suggestions dynamiquement
function loadGrid(query = '', category = '', sort = '') {
    const isSearch = query !== '' || category !== '' || sort !== '';
    const title = document.getElementById('section-title');
    const grid = document.getElementById('main-grid');
    
    title.textContent = isSearch ? "Résultats de recherche" : "Suggestions pour vous";
    grid.innerHTML = '<p style="text-align: center; padding: 40px; color: #64748b;">Chargement...</p>';
    
    const cacheBuster = Date.now();
    const url = isSearch 
        ? `api/get_products.php?search=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&t=${cacheBuster}`
        : `api/get_suggestions.php?t=${cacheBuster}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            renderProducts(data, isSearch);
        })
        .catch(err => {
            console.error(err);
            grid.innerHTML = '<p style="text-align: center; padding: 40px; color: red;">Erreur de connexion avec le serveur.</p>';
        });
}

let currentSort = '';

// 🔎 recherche
function searchProducts() {
    const query = document.getElementById('search-input').value;
    const category = document.getElementById('category-filter').value;
    loadGrid(query, category, currentSort);
}

// ↕️ Bouton de tri
function toggleSort() {
    const btn = document.getElementById('sort-btn');
    if (currentSort === '' || currentSort === 'prix_desc') {
        currentSort = 'prix_asc';
        btn.innerHTML = '<i class="fas fa-sort-amount-up"></i>';
        btn.title = "Trier par prix (Décroissant)";
        btn.style.background = "#0f766e";
        btn.style.color = "white";
    } else {
        currentSort = 'prix_desc';
        btn.innerHTML = '<i class="fas fa-sort-amount-down"></i>';
        btn.title = "Trier par prix (Croissant)";
        btn.style.background = "#0f766e";
        btn.style.color = "white";
    }
    searchProducts();
}

// 🏷️ badges
function getBadges(p) {
    let html = '';
    if (p.recommended) html += '<span class="status-badge status-recommended">⭐ Recommandé</span>';
    if (p.allowed) html += '<span class="status-badge status-allowed">✓ OK</span>';
    if (p.check) html += '<span class="status-badge status-check">⚠ Vérifier</span>';
    if (p.forbidden) html += '<span class="status-badge status-forbidden">✗ Interdit</span>';
    return html;
}

// 📦 détails produit
function showProductDetails(id) {
    // Chemin corrigé (sans ../)
    fetch(`api/get_product_details.php?id=${id}`)
        .then(res => res.json())
        .then(p => {
            document.getElementById('product-details').innerHTML = `
                <h2>${p.nom}</h2>
                <p>${p.description || 'Pas de description'}</p>

                <h3>Ingrédients</h3>
                <div>
                    ${p.ingredients?.length 
                        ? p.ingredients.map(i => `<span>${i.nom}</span>`).join(', ')
                        : 'Aucun'}
                </div>

                <h3>Nutrition</h3>
                ${
                    p.nutrition?.length
                    ? `<table>
                        ${p.nutrition.map(n => `
                            <tr>
                                <td>${n.nutrient}</td>
                                <td>${n.valeur}</td>
                                <td>${n.unite}</td>
                            </tr>
                        `).join('')}
                      </table>`
                    : 'Pas de données'
                }

                <div>${getBadges(p)}</div>
            `;
            document.getElementById('product-modal').style.display = 'block';
        });
}

// ❌ fermer modal
function closeModal() {
    document.getElementById('product-modal').style.display = 'none';
}

// 📷 scan (fake)
async function applyAIFilter() {
    const prefs = document.getElementById('ai-prefs').value;
    const allergies = document.getElementById('ai-allergies').value;
    
    if (!prefs && !allergies) {
        alert("Veuillez saisir au moins une préférence ou une allergie.");
        return;
    }

    const grid = document.getElementById('main-grid');
    const title = document.getElementById('section-title');
    
    title.textContent = "Résultats du Filtrage Intelligent (IA)";
    grid.innerHTML = '<div class="loading">L\'IA analyse les produits...</div>';

    try {
        const url = `api/get_products.php?override_prefs=${encodeURIComponent(prefs)}&override_allergies=${encodeURIComponent(allergies)}`;
        console.log("Calling AI Filter:", url);
        
        const response = await fetch(url);
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`Erreur HTTP ${response.status}: ${text}`);
        }
        
        let products;
        const rawText = await response.text();
        try {
            products = JSON.parse(rawText);
        } catch (jsonErr) {
            console.error("JSON Parse Error:", jsonErr, "Raw Response:", rawText);
            throw new Error("Réponse invalide du serveur (Problème PHP ?)");
        }

        console.log("AI Filter Result:", products);
        
        if (products.error) {
            throw new Error(products.message || "Erreur inconnue de l'API");
        }

        if (products.length === 0) {
            grid.innerHTML = '<p class="no-results">Aucun produit trouvé correspondant à ces critères.</p>';
        } else {
            renderProducts(products, true);
            grid.scrollIntoView({ behavior: 'smooth' });
        }
    } catch (err) {
        console.error("AI Filter Error:", err);
        grid.innerHTML = `<p class="no-results">❌ Erreur : ${err.message}</p>`;
    }
}

function scanBarcode() {
    alert("Scan en développement");
}

// ================= CHATBOT LOGIC =================
function toggleChat() {
    const chat = document.getElementById('chat-window');
    chat.classList.toggle('active');
}

function handleChatKey(e) {
    if (e.key === 'Enter') sendChatMessage();
}

function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (!msg) return;

    appendMessage('user', msg);
    input.value = '';

    const typing = document.getElementById('typing-indicator');
    typing.style.display = 'block';

    fetch('api/chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(res => res.json())
    .then(data => {
        typing.style.display = 'none';
        appendMessage('bot', data.reply);
    })
    .catch(err => {
        typing.style.display = 'none';
        appendMessage('bot', "Désolé, je rencontre des difficultés techniques.");
    });
}

function appendMessage(sender, text) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.className = `message message-${sender}`;
    div.innerHTML = text;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

// fermer modal si clic dehors
window.onclick = function(e) {
    const modal = document.getElementById('product-modal');
    if (e.target == modal) modal.style.display = "none";
}

</script>

</body>
</html>