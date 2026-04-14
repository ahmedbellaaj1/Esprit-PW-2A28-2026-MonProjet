<?php
// views/dons/front.php  — Vue publique : liste des dons disponibles
$baseUrl = '/' . basename(dirname(__DIR__, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenBite — Dons alimentaires</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css"/>
</head>
<body>

  <!-- NAVBAR (identique au projet GreenBite) -->
  <nav class="navbar">
    <a class="navbar-logo" href="../front_office.html">Green<span>Bite</span></a>
    <ul class="navbar-links">
      <li><a href="../front_office.html">Accueil</a></li>
      <li><a href="#">Recettes</a></li>
      <li><a href="../front_office.html">Produits</a></li>
      <li><a href="#" class="active">Dons</a></li>
      <li><a href="#">Magasins</a></li>
    </ul>
    <div class="navbar-right">
      <button class="primary-btn" style="padding:9px 20px;font-size:0.85rem;">Scanner 📷</button>
      <div class="nav-avatar">AB</div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-section">
    <h1>🎁 Dons alimentaires disponibles</h1>
    <p>Consultez les dons publiés et réservez ceux dont vous avez besoin.</p>
    <div class="search-wrapper">
      <input type="text" id="front-don-search" placeholder="Rechercher un produit (ex: pain, légumes...)"/>
      <button onclick="loadFrontDons()">Rechercher</button>
    </div>
  </section>

  <div class="main-container">

    <!-- Filtres -->
    <div class="filter-bar">
      <button class="filter-pill active" onclick="setFrontDonFilter(this,'')">Tous</button>
      <button class="filter-pill" onclick="setFrontDonFilter(this,'disponible')">✅ Disponible</button>
      <button class="filter-pill" onclick="setFrontDonFilter(this,'réservé')">🔒 Réservé</button>
      <button class="filter-pill" onclick="setFrontDonFilter(this,'récupéré')">📦 Récupéré</button>
    </div>

    <!-- Stats rapides -->
    <div id="front-don-stats" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;"></div>

    <!-- Grille des dons -->
    <div class="section-heading">
      Dons publiés
      <span id="front-don-count" style="font-size:0.85rem;font-weight:400;color:#64748b;margin-left:0.5rem;"></span>
    </div>
    <div class="products-grid" id="front-dons-grid">
      <div style="grid-column:1/-1;text-align:center;padding:2rem;color:#94a3b8;">Chargement…</div>
    </div>

  </div>

  <!-- MODAL DÉTAIL DON -->
  <div class="modal-overlay" id="donDetailModal">
    <div class="modal-box">
      <button class="modal-close" onclick="document.getElementById('donDetailModal').classList.remove('open')">✕</button>
      <div id="don-detail-content"></div>
    </div>
  </div>

  <div class="toast" id="toast"></div>
  <script src="<?= $baseUrl ?>/assets/dons.js"></script>
</body>
</html>
