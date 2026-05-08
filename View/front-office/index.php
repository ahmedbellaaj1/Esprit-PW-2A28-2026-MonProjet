<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$q = trim($_GET['q'] ?? '');
$categorie = trim($_GET['categorie'] ?? '');
$nutriscore = trim($_GET['nutriscore'] ?? '');
$prixMin = trim($_GET['prix_min'] ?? '');
$prixMax = trim($_GET['prix_max'] ?? '');
$sort = trim($_GET['sort'] ?? 'recent');

$controller = new ProductController();
$products = $controller->list([
    'q' => $q,
    'categorie' => $categorie,
    'nutriscore' => $nutriscore,
    'prix_min' => $prixMin,
    'prix_max' => $prixMax,
    'sort' => $sort,
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
    <a class="navbar-logo" href="index.php">
        <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="navbar-logo-img">
        <span class="navbar-logo-text">Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="index.php" class="active">Produits</a></li>
        <li><a href="#">Dons</a></li>
        <li><a href="#">Événements</a></li>
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

<section class="hero-section">
    <!-- Carousel de phrases inspirantes -->
    <div class="tagline-carousel">
        <div class="tagline-track">
            <div class="tagline-slide">
                <h1>🥬 Découvrez vos produits GreenBite</h1>
                <p>Une alimentation saine commence par des choix intelligents</p>
            </div>
            <div class="tagline-slide">
                <h1>🌱 Vivez Sainement</h1>
                <p>Chaque produit est sélectionné pour votre bien-être</p>
            </div>
            <div class="tagline-slide">
                <h1>💚 Nutrition Équilibrée</h1>
                <p>Découvrez le Nutriscore de chaque aliment</p>
            </div>
            <div class="tagline-slide">
                <h1>🎯 Qualité & Transparence</h1>
                <p>Tous les détails nutritionnels pour vos choix</p>
            </div>
            <div class="tagline-slide">
                <h1>✨ Mangez Mieux, Vivez Mieux</h1>
                <p>Explorez notre sélection premium de produits sains</p>
            </div>
        </div>
        
        <!-- Indicateurs de position -->
        <div class="tagline-indicators">
            <span class="indicator active" onclick="goToSlide(0)"></span>
            <span class="indicator" onclick="goToSlide(1)"></span>
            <span class="indicator" onclick="goToSlide(2)"></span>
            <span class="indicator" onclick="goToSlide(3)"></span>
            <span class="indicator" onclick="goToSlide(4)"></span>
        </div>
    </div>

    <div style="margin-top: 2rem; display: flex; gap: 0.75rem; align-items: stretch; justify-content: center; flex-wrap: wrap; max-width: 900px; margin-left: auto; margin-right: auto;">
        <div class="search-wrapper" style="flex: 1; min-width: 250px; max-width: none; margin: 0;">
            <input type="text" id="searchInput" value="<?= h($q) ?>" placeholder="🔍 Rechercher par nom, marque ou code barre...">
            <button type="button" onclick="searchProducts()">Rechercher</button>
        </div>
        <a href="barcode-scanner.php" class="scanner-btn">
            <span class="scanner-icon">📷</span>
            <span class="scanner-text">Scanner</span>
        </a>
    </div>
</section>

<style>
    .tagline-carousel {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecf0f1 100%);
        border-radius: 12px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
    }

    .tagline-track {
        display: flex;
        animation: slideCarousel 20s infinite linear;
        gap: 0;
    }

    .tagline-slide {
        min-width: 100%;
        flex: 0 0 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 0 2rem;
        transition: all 0.5s ease;
    }

    .tagline-slide h1 {
        font-size: 2.5rem;
        color: #0f172a;
        margin: 0 0 1rem 0;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .tagline-slide p {
        font-size: 1.1rem;
        color: #475569;
        margin: 0;
        max-width: 600px;
        font-weight: 500;
    }

    @keyframes slideCarousel {
        0% {
            transform: translateX(0);
        }
        20% {
            transform: translateX(0);
        }
        25% {
            transform: translateX(-100%);
        }
        45% {
            transform: translateX(-100%);
        }
        50% {
            transform: translateX(-200%);
        }
        70% {
            transform: translateX(-200%);
        }
        75% {
            transform: translateX(-300%);
        }
        95% {
            transform: translateX(-300%);
        }
        100% {
            transform: translateX(-400%);
        }
    }

    .tagline-indicators {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-bottom: 0.5rem;
    }

    .indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.3);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .indicator.active {
        background: #16a34a;
        width: 28px;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.4);
    }

    .indicator:hover {
        background: #16a34a;
        transform: scale(1.1);
    }

    @media (max-width: 768px) {
        .tagline-carousel {
            padding: 2rem 1rem;
        }

        .tagline-slide h1 {
            font-size: 1.8rem;
        }

        .tagline-slide p {
            font-size: 0.95rem;
        }
    }
</style>

<div class="main-container">
    <form id="filterForm" method="get" action="index.php" style="display:none;">
        <input type="hidden" name="q" id="f_q" value="<?= h($q) ?>">
        <input type="hidden" name="categorie" id="f_categorie" value="<?= h($categorie) ?>">
        <input type="hidden" name="nutriscore" id="f_nutriscore" value="<?= h($nutriscore) ?>">
        <input type="hidden" name="prix_min" id="f_prix_min" value="<?= h($prixMin) ?>">
        <input type="hidden" name="prix_max" id="f_prix_max" value="<?= h($prixMax) ?>">
        <input type="hidden" name="sort" id="f_sort" value="<?= h($sort) ?>">
    </form>

    <?php if (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
        <div class="alert success">✅ Commande enregistrée avec succès!</div>
    <?php endif; ?>

    <div style="display:flex;gap:2rem;max-width:1400px;margin:0 auto;">
        <!-- SIDEBAR FILTERS -->
        <aside style="flex-basis:280px;min-width:280px;">
            <div style="background:#ffffff;border-radius:12px;padding:1.5rem;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
                    <h3 style="margin:0;font-size:1.1rem;color:#0f172a;">🎯 Filtres</h3>
                    <?php if ($categorie || $nutriscore || $prixMin || $prixMax || $q): ?>
                        <a href="index.php" style="font-size:0.85rem;color:#16a34a;text-decoration:none;font-weight:500;">↻ Réinit</a>
                    <?php endif; ?>
                </div>

                <!-- Search -->
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;color:#0f172a;margin-bottom:0.5rem;">Recherche</label>
                    <input type="text" id="sideSearchInput" value="<?= h($q) ?>" placeholder="Nom, marque..." style="width:100%;padding:0.6rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.9rem;" onkeyup="handleSideSearch(event)">
                </div>

                <!-- Prix Input -->
                <div style="margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f1f5f9;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;color:#0f172a;margin-bottom:1rem;">💰 Plage de prix (DT)</label>
                    
                    <!-- Input Fields -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem;">
                        <div>
                            <label style="display:block;font-size:0.75rem;color:#64748b;text-transform:uppercase;font-weight:600;margin-bottom:0.4rem;">Min</label>
                            <input type="number" id="priceMinInput" min="0" max="500" value="<?= h($prixMin) ?: '0' ?>" placeholder="0" style="width:100%;padding:0.7rem;border:2px solid #e2e8f0;border-radius:8px;font-size:0.95rem;font-weight:600;color:#0f172a;transition:border-color 0.2s ease;">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.75rem;color:#64748b;text-transform:uppercase;font-weight:600;margin-bottom:0.4rem;">Max</label>
                            <input type="number" id="priceMaxInput" min="0" max="500" value="<?= h($prixMax) ?: '500' ?>" placeholder="500" style="width:100%;padding:0.7rem;border:2px solid #e2e8f0;border-radius:8px;font-size:0.95rem;font-weight:600;color:#0f172a;transition:border-color 0.2s ease;">
                        </div>
                    </div>

                    <style>
                        #priceMinInput:focus,
                        #priceMaxInput:focus {
                            outline: none;
                            border-color: #16a34a;
                            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
                        }
                    </style>

                    <button class="primary-btn" onclick="applyPriceFilter()" style="width:100%;padding:0.7rem;font-size:0.9rem;">✓ Appliquer le filtre</button>
                </div>

                <!-- Catégories -->
                <div style="margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f1f5f9;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;color:#0f172a;margin-bottom:0.7rem;">📂 Catégories</label>
                    <div style="display:flex;flex-direction:column;gap:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                            <input type="radio" name="category" value="" <?= $categorie === '' ? 'checked' : '' ?> onchange="applyCategoryFilter('')">
                            <span>Tous</span>
                        </label>
                        <?php foreach ($categories as $cat): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                                <input type="radio" name="category" value="<?= h((string) $cat) ?>" <?= $categorie === $cat ? 'checked' : '' ?> onchange="applyCategoryFilter('<?= h((string) $cat) ?>')">
                                <span><?= h((string) $cat) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Nutriscore -->
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;color:#0f172a;margin-bottom:0.7rem;">⭐ Nutriscore</label>
                    <div style="display:flex;flex-direction:column;gap:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                            <input type="radio" name="nutri" value="" <?= $nutriscore === '' ? 'checked' : '' ?> onchange="applyNutriFilter('')">
                            <span>Tous</span>
                        </label>
                        <?php foreach (['A' => 'Excellent', 'B' => 'Bon', 'C' => 'Acceptable', 'D' => 'Médiocre'] as $score => $label): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                                <input type="radio" name="nutri" value="<?= $score ?>" <?= $nutriscore === $score ? 'checked' : '' ?> onchange="applyNutriFilter('<?= $score ?>')">
                                <span>
                                    <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:<?= match($score) {
                                        'A' => '#16a34a',
                                        'B' => '#84cc16',
                                        'C' => '#eab308',
                                        'D' => '#f97316',
                                        default => '#ccc'
                                    } ?>;color:white;text-align:center;line-height:24px;font-weight:600;font-size:0.8rem;">
                                        <?= $score ?>
                                    </span>
                                    <?= $label ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <section style="flex:1;">
            <!-- Sort & Results -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
                <div style="font-size:0.95rem;color:#0f172a;font-weight:500;">
                    📊 <strong><?= count($products) ?></strong> produit<?= count($products) !== 1 ? 's' : '' ?>
                    <?php if ($q || $categorie || $nutriscore || $prixMin || $prixMax): ?>
                        trouvé<?= count($products) !== 1 ? 's' : '' ?>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <label style="font-size:0.9rem;color:#64748b;font-weight:500;">Trier par:</label>
                    <select id="sortSelect" onchange="applySort(this.value)" style="padding:0.6rem 0.8rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.9rem;cursor:pointer;background:white;">
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>🆕 Récents</option>
                        <option value="prix_asc" <?= $sort === 'prix_asc' ? 'selected' : '' ?>>💰 Prix croissant</option>
                        <option value="prix_desc" <?= $sort === 'prix_desc' ? 'selected' : '' ?>>💸 Prix décroissant</option>
                        <option value="nom" <?= $sort === 'nom' ? 'selected' : '' ?>>🔤 Alphabétique</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                <?php if (empty($products)): ?>
                    <div style="grid-column:1/-1;text-align:center;padding:3rem 1rem;color:#64748b;">
                        <div style="font-size:2rem;margin-bottom:0.5rem;">🔍</div>
                        <div style="font-size:1.1rem;font-weight:500;margin-bottom:0.3rem;">Aucun produit trouvé</div>
                        <small>Essayez avec d'autres critères de filtrage</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php $score = strtolower((string) $product['nutriscore']); ?>
                        <article class="product-card">
                            <a href="product.php?id=<?= (int) $product['id_produit'] ?>" style="display:block;color:inherit;text-decoration:none;">
                                <div class="product-img">
                                    <img src="<?= h($product['image'] ?: 'https://via.placeholder.com/400x300?text=Produit') ?>" alt="Image produit" style="width:100%;height:100%;object-fit:cover;">
                                    <span class="nutriscore ns-<?= h($score) ?>" title="<?= match($score) {
                                        'a' => 'Excellente qualité nutritionnelle',
                                        'b' => 'Bonne qualité nutritionnelle',
                                        'c' => 'Qualité nutritionnelle acceptable',
                                        'd' => 'Qualité nutritionnelle médiocre',
                                        'e' => 'Mauvaise qualité nutritionnelle',
                                        default => 'Non évalué'
                                    } ?>"><?= h((string) $product['nutriscore']) ?></span>
                                </div>
                                <div class="product-body">
                                    <div class="product-brand" style="color:#64748b;font-size:0.8rem;text-transform:uppercase;"><?= h((string) $product['marque']) ?></div>
                                    <div class="product-name" style="font-weight:600;color:#0f172a;margin:0.5rem 0;"><?= h((string) $product['nom']) ?></div>
                                    <div class="product-cal" style="font-size:0.85rem;color:#64748b;"><?= (int) $product['calories'] ?> kcal / 100g</div>
                                    <div class="product-tags" style="margin:0.7rem 0;display:flex;gap:0.4rem;flex-wrap:wrap;">
                                        <span class="tag tag-local"><?= h((string) $product['categorie']) ?></span>
                                        <span class="tag <?= $product['statut'] === 'actif' ? 'tag-bio' : ($product['statut'] === 'attente' ? 'tag-gluten' : 'tag-local') ?>">
                                            <?= h((string) $product['statut']) ?>
                                        </span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:0.7rem;border-top:1px solid #f1f5f9;">
                                        <strong style="color:#16a34a;font-size:1.1rem;"><?= number_format((float) $product['prix'], 2, ',', ' ') ?> DT</strong>
                                        <span class="primary-btn" style="padding:7px 12px;font-size:0.78rem;">👉 Voir</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
function handleSideSearch(event) {
    if (event.key === 'Enter' || event.type === 'change') {
        searchProducts();
    }
}

function searchProducts() {
    document.getElementById('f_q').value = document.getElementById('sideSearchInput').value.trim();
    document.getElementById('filterForm').submit();
}

function applyPriceFilter() {
    const minPrice = document.getElementById('priceMinInput').value;
    const maxPrice = document.getElementById('priceMaxInput').value;
    
    document.getElementById('f_prix_min').value = minPrice;
    document.getElementById('f_prix_max').value = maxPrice;
    document.getElementById('f_sort').value = document.getElementById('sortSelect').value;
    document.getElementById('filterForm').submit();
}

function applyCategoryFilter(category) {
    document.getElementById('f_categorie').value = category;
    document.getElementById('f_nutriscore').value = '';
    document.getElementById('f_sort').value = document.getElementById('sortSelect').value;
    document.getElementById('filterForm').submit();
}

function applyNutriFilter(nutri) {
    document.getElementById('f_nutriscore').value = nutri;
    document.getElementById('f_categorie').value = '';
    document.getElementById('f_sort').value = document.getElementById('sortSelect').value;
    document.getElementById('filterForm').submit();
}

function applySort(sortValue) {
    document.getElementById('f_sort').value = sortValue;
    document.getElementById('filterForm').submit();
}

function goToSlide(slideIndex) {
    const track = document.querySelector('.tagline-track');
    const indicators = document.querySelectorAll('.indicator');
    
    // Retirer l'animation lors du clic manuel
    track.style.animationPlayState = 'paused';
    
    // Calculer le translateX
    const offset = slideIndex * -100;
    track.style.transform = `translateX(${offset}%)`;
    
    // Mettre à jour les indicateurs
    indicators.forEach((indicator, index) => {
        if (index === slideIndex) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
    
    // Reprendre l'animation après 3 secondes
    setTimeout(() => {
        track.style.animationPlayState = 'running';
    }, 3000);
}

document.getElementById('searchInput').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        document.getElementById('sideSearchInput').value = this.value;
        searchProducts();
    }
});
</script>

<script src="../assets/cart.js"></script>

<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>

</body>
</html>
