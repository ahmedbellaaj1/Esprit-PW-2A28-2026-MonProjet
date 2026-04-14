<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$q = trim($_GET['q'] ?? '');
$categorie = trim($_GET['categorie'] ?? '');
$nutriscore = trim($_GET['nutriscore'] ?? '');

$controller = new ProductController();
$products = $controller->list([
    'q' => $q,
    'categorie' => $categorie,
    'nutriscore' => $nutriscore,
]);
$categories = $controller->categories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBite - Produits et Evaluation Nutritionnelle</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <a class="navbar-logo" href="index.php">Green<span>Bite</span></a>
    <ul class="navbar-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="#" class="active">Produits</a></li>
        <li><a href="#">Dons</a></li>
        <li><a href="#">Magasins</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="../back-office/dashboard.php">Dashboard Admin</a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<section class="hero-section">
    <h1>Decouvrez vos produits alimentaires</h1>
    <p>Recherchez, filtrez et cliquez sur un produit pour voir ses informations et commander.</p>
    <div class="search-wrapper">
        <input type="text" id="searchInput" value="<?= h($q) ?>" placeholder="Rechercher un produit, marque ou code barre...">
        <button type="button" onclick="searchProducts()">Rechercher</button>
    </div>
</section>

<div class="main-container">
    <form id="filterForm" method="get" action="index.php" style="display:none;">
        <input type="hidden" name="q" id="f_q" value="<?= h($q) ?>">
        <input type="hidden" name="categorie" id="f_categorie" value="<?= h($categorie) ?>">
        <input type="hidden" name="nutriscore" id="f_nutriscore" value="<?= h($nutriscore) ?>">
    </form>

    <?php if (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
        <div class="alert success">Commande enregistree avec succes.</div>
    <?php endif; ?>

    <div class="filter-bar">
        <button class="filter-pill active" type="button" onclick="clearFilters(this)">Tous</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-pill" type="button" onclick="setCategoryFilter(this, '<?= h((string) $cat) ?>')"><?= h((string) $cat) ?></button>
        <?php endforeach; ?>
        <button class="filter-pill" type="button" onclick="setNutriFilter(this, 'A')">Nutriscore A</button>
        <button class="filter-pill" type="button" onclick="setNutriFilter(this, 'B')">Nutriscore B</button>
        <button class="filter-pill" type="button" onclick="setNutriFilter(this, 'C')">Nutriscore C</button>
        <button class="filter-pill" type="button" onclick="setNutriFilter(this, 'D')">Nutriscore D</button>
        <button class="filter-pill" type="button" onclick="setNutriFilter(this, 'E')">Nutriscore E</button>
    </div>

    <div class="section-heading">
        Produits disponibles
        <span id="countLabel" style="font-size:0.85rem;font-weight:400;color:#64748b;margin-left:0.5rem;">
            (<?= count($products) ?> resultat<?= count($products) > 1 ? 's' : '' ?>)
        </span>
    </div>

    <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $product): ?>
            <?php $score = strtolower((string) $product['nutriscore']); ?>
            <article class="product-card">
                <a href="product.php?id=<?= (int) $product['id_produit'] ?>" style="display:block;color:inherit;text-decoration:none;">
                    <div class="product-img">
                        <img src="<?= h($product['image'] ?: 'https://via.placeholder.com/400x300?text=Produit') ?>" alt="Image produit" style="width:100%;height:100%;object-fit:cover;">
                        <span class="nutriscore ns-<?= h($score) ?>"><?= h((string) $product['nutriscore']) ?></span>
                    </div>
                    <div class="product-body">
                        <div class="product-brand"><?= h((string) $product['marque']) ?></div>
                        <div class="product-name"><?= h((string) $product['nom']) ?></div>
                        <div class="product-cal"><?= (int) $product['calories'] ?> kcal / 100g</div>
                        <div class="product-tags" style="margin-bottom:0.7rem;">
                            <span class="tag tag-local"><?= h((string) $product['categorie']) ?></span>
                            <span class="tag <?= $product['statut'] === 'actif' ? 'tag-bio' : ($product['statut'] === 'attente' ? 'tag-gluten' : 'tag-local') ?>">
                                <?= h((string) $product['statut']) ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <strong style="color:#0f172a;"><?= number_format((float) $product['prix'], 2, ',', ' ') ?> DT</strong>
                            <span class="primary-btn" style="padding:7px 12px;font-size:0.78rem;">Voir</span>
                        </div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!$products): ?>
        <div class="table-container">Aucun produit trouve avec ces criteres.</div>
    <?php endif; ?>
</div>

<script>
function submitFilters() {
    document.getElementById('filterForm').submit();
}

function clearPillState() {
    document.querySelectorAll('.filter-pill').forEach(function(el) {
        el.classList.remove('active');
    });
}

function searchProducts() {
    document.getElementById('f_q').value = document.getElementById('searchInput').value.trim();
    submitFilters();
}

function clearFilters(btn) {
    clearPillState();
    btn.classList.add('active');
    document.getElementById('f_q').value = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('f_categorie').value = '';
    document.getElementById('f_nutriscore').value = '';
    submitFilters();
}

function setCategoryFilter(btn, category) {
    clearPillState();
    btn.classList.add('active');
    document.getElementById('f_categorie').value = category;
    document.getElementById('f_nutriscore').value = '';
    submitFilters();
}

function setNutriFilter(btn, nutri) {
    clearPillState();
    btn.classList.add('active');
    document.getElementById('f_nutriscore').value = nutri;
    document.getElementById('f_categorie').value = '';
    submitFilters();
}

document.getElementById('searchInput').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchProducts();
    }
});
</script>
</body>
</html>
