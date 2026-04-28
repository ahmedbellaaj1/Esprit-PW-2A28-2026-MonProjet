<?php
session_start();

// 🔒 Vérifier si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Greenbite Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="view/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="navbar-logo" href="index.html">Greenbite</a>

    <div class="navbar-links">
        <a href="index.html">Accueil</a>
        <a class="active" href="#">Dashboard</a>
        <a href="view/backoffice.php">Back Office</a>
    </div>

    <div class="navbar-right">
        <div class="nav-avatar">U</div>
    </div>
</nav>

<!-- CONTENU -->
<main class="page-content">

    <div class="page-header">
        <h1>Dashboard Greenbite</h1>
        <p>Recherchez des aliments adaptés à votre profil.</p>
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

<!-- ================= JS ================= -->
<script>

// 🔄 Charger au démarrage
document.addEventListener('DOMContentLoaded', () => {
    loadGrid('', '');
});

// 🔍 Charger produits et suggestions dynamiquement
function loadGrid(query = '', category = '', sort = '') {
    const isSearch = query !== '' || category !== '' || sort !== '';
    const title = document.getElementById('section-title');
    const grid = document.getElementById('main-grid');
    
    title.textContent = isSearch ? "Résultats de recherche" : "Suggestions pour vous";
    grid.innerHTML = '<p style="text-align: center; padding: 40px; color: #64748b;">Chargement...</p>';
    
    // Anti-cache
    const cacheBuster = Date.now();
    
    // Chemin corrigé (sans ../) car on est à la racine
    const url = isSearch 
        ? `api/get_products.php?search=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&t=${cacheBuster}`
        : `api/get_suggestions.php?t=${cacheBuster}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
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
function scanBarcode() {
    alert("Scan en développement");
}

// fermer modal si clic dehors
window.onclick = function(e) {
    const modal = document.getElementById('product-modal');
    if (e.target == modal) modal.style.display = "none";
}

</script>

</body>
</html>