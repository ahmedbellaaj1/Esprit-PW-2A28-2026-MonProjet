<?php
// Tu peux ajouter ici des vérifications (session, login, etc.)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Greenbite Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../view/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
<nav class="navbar">
    <a class="navbar-logo" href="../index.php">Greenbite</a>
    <div class="navbar-links">
        <a href="../index.php">Accueil</a>
        <a class="active" href="../greenbite/index.php">Dashboard</a>
        <a href="../view/backoffice.php">Back Office</a>
    </div>
    <div class="navbar-right">
        <div class="nav-avatar">A</div>
    </div>
</nav>

<main class="page-content">
    <div class="page-header">
        <h1>Dashboard Greenbite</h1>
        <p>Recherchez des aliments et produits adaptés à vos préférences.</p>
    </div>

    <!-- Barre de recherche -->
    <div class="search-section">
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Rechercher un aliment ou produit..." class="search-input">
            <button class="search-btn" onclick="searchProducts()" title="Rechercher"><i class="fas fa-search"></i></button>
            <button class="scan-btn" id="sort-btn" onclick="toggleSort()" title="Trier par prix (Croissant)"><i class="fas fa-sort-amount-down"></i></button>
            <button class="scan-btn" onclick="scanBarcode()" title="Scanner un code-barres"><i class="fas fa-qrcode"></i></button>
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

    <!-- Modal pour détails produit -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="product-details"></div>
        </div>
    </div>
</main>

<!-- SCRIPT JS -->
<script>
const userId = getQueryParam('user_id', 1);

function getQueryParam(name, defaultValue) {
    const url = new URL(window.location.href);
    return parseInt(url.searchParams.get(name) || defaultValue, 10);
}

function initPage() {
    loadGrid('', '');
}

function closeModal() {
    document.getElementById('product-modal').style.display = 'none';
}

window.onload = initPage;

function loadGrid(query = '', category = '', sort = '') {
    const isSearch = query !== '' || category !== '' || sort !== '';
    const title = document.getElementById('section-title');
    const grid = document.getElementById('main-grid');
    
    title.textContent = isSearch ? "Résultats de recherche" : "Suggestions pour vous";
    grid.innerHTML = '<p style="text-align: center; padding: 40px; color: #64748b;">Chargement...</p>';
    
    const cacheBuster = Date.now();
    const url = isSearch 
        ? `../api/get_products.php?search=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&user_id=${userId}&t=${cacheBuster}`
        : `../api/get_suggestions.php?user_id=${userId}&t=${cacheBuster}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                grid.innerHTML = '<p style="text-align: center; padding: 40px; color: #64748b;">Aucun produit trouvé pour votre recherche.</p>';
                return;
            }
            grid.innerHTML = data.map(product => `
                <div class="product-card" onclick="showProductDetails(${product.id})">
                    <div class="product-image">
                        <i class="fas ${isSearch ? 'fa-utensils' : 'fa-star'}"></i>
                    </div>
                    <div class="product-info">
                        <div class="product-name" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>${product.nom}</span>
                            ${product.prix && parseFloat(product.prix) > 0 ? `<span style="color:#0f766e; font-weight:bold; font-size:0.9em;">${parseFloat(product.prix).toFixed(2)} DT</span>` : ''}
                        </div>
                        <div class="product-category">${product.categorie}</div>
                        <div style="font-size: 0.85em; color: #64748b; margin-top: 5px; margin-bottom: 8px;">
                            <i class="fas fa-fire" style="color: #ef4444;"></i> ${product.calories || 0} kcal
                        </div>
                        <div class="product-status">
                            ${getStatusBadges(product)}
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Erreur chargement:', error);
            grid.innerHTML = '<p style="text-align: center; padding: 40px; color: red;">Erreur de connexion avec le serveur.</p>';
        });
}

let currentSort = '';

function searchProducts() {
    const query = document.getElementById('search-input').value;
    const category = document.getElementById('category-filter').value;
    loadGrid(query, category, currentSort);
}

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

function getStatusBadges(product) {
    let badges = '';
    if (product.recommended) badges += '<span class="status-badge status-recommended">⭐ Recommandé</span>';
    if (product.allowed) badges += '<span class="status-badge status-allowed">✓ Autorisé</span>';
    if (product.check) badges += '<span class="status-badge status-check">⚠ À vérifier</span>';
    if (product.forbidden) badges += '<span class="status-badge status-forbidden">✗ Interdit</span>';
    return badges;
}

function showProductDetails(productId) {
    fetch(`../api/get_product_details.php?id=${productId}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('product-details').innerHTML = `
                <div class="product-detail-header">
                    <h2>${data.nom}</h2>
                    <p>${data.description || 'Aucune description disponible.'}</p>
                </div>
                <div class="product-detail-body">
                    <h3>Ingrédients</h3>
                    <div class="ingredient-list">
                        ${data.ingredients && data.ingredients.length > 0 
                            ? data.ingredients.map(ing => `<span class="ingredient-tag">${ing.nom || ing}</span>`).join('')
                            : '<p>Aucun ingrédient listé.</p>'
                        }
                    </div>
                    <h3>Statut selon votre profil</h3>
                    <div class="product-status" style="margin-top: 16px;">
                        ${getStatusBadges(data)}
                    </div>
                    ${data.nutrition && data.nutrition.length > 0 ? `
                        <h3>Valeurs nutritionnelles</h3>
                        <table class="nutrition-table">
                            <thead>
                                <tr>
                                    <th>Nutriment</th>
                                    <th>Valeur</th>
                                    <th>Unité</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.nutrition.map(nut => `
                                    <tr>
                                        <td>${nut.nutrient}</td>
                                        <td>${nut.valeur}</td>
                                        <td>${nut.unite}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    ` : ''}
                </div>
            `;
            document.getElementById('product-modal').style.display = 'block';
        })
        .catch(error => console.error('Erreur chargement détails:', error));
}

// Fermer la modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('product-modal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}