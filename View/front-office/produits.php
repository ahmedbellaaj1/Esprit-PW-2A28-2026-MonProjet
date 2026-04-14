<?php
declare(strict_types=1);

// ====================== CONNEXION & MODÈLE ======================
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Model/Produit.php';

$produitModel = new Produit();
$produits = $produitModel->getAllDisponibles();

// ====================== DONNÉES D'AFFICHAGE ======================
$nsDesc = [
    'A' => 'Excellent choix nutritionnel',
    'B' => 'Bon choix nutritionnel',
    'C' => 'Qualite nutritionnelle moyenne',
    'D' => 'Qualite nutritionnelle mediocre',
    'E' => 'A consommer avec moderation',
];

$emojis = [
    'Produits laitiers'  => '🥛',
    'Cereales & Pains'   => '🍞',
    'Boissons'           => '🧃',
    'Fruits & Legumes'   => '🥦',
    'Snacks & Biscuits'  => '🍪',
    'Conserves'          => '🥫',
    'Epicerie'           => '🫙',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits - GreenBite</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <nav class="navbar">
        <a class="navbar-logo" href="#">Green<span>Bite</span></a>
        <ul class="navbar-links">
            <li><a href="#">Accueil</a></li>
            <li><a href="#">Recettes</a></li>
            <li><a href="/projetwebnova/View/front-office/produits.php" class="active">Produits</a></li>
            <li><a href="#">Dons</a></li>
            <li><a href="#">Mon profil</a></li>
        </ul>
        <div class="navbar-right">
            <div class="nav-avatar">GB</div>
            <button onclick="alert('Déconnexion non implémentée pour le moment')" 
                    class="primary-btn nav-logout-btn">Déconnexion</button>
        </div>
    </nav>

    <section class="hero-section">
        <h1>🌿 Produits alimentaires sains</h1>
        <p>Découvrez nos produits, consultez leurs valeurs nutritionnelles et passez votre commande.</p>
        <div class="search-wrapper">
            <input type="text" id="searchInput" placeholder="Rechercher un produit (ex: yaourt bio, lait...)"/>
            <button type="button" onclick="searchProducts()">Rechercher</button>
        </div>
    </section>

    <div class="main-container">

        <div class="section-heading">
            Produits disponibles
            <span id="countLabel" style="font-size:0.85rem;font-weight:400;color:#64748b;margin-left:0.5rem;">
                (<?= count($produits) ?> produits)
            </span>
        </div>

        <div class="products-grid" id="produitsGrid">
            <?php foreach ($produits as $p):
                $emoji = $emojis[$p['categorie'] ?? ''] ?? '🌿';
                $ns    = $p['nutriscore'] ?? 'C';
            ?>
                <div class="product-card"
                     data-ns="<?= htmlspecialchars($ns) ?>"
                     data-nom="<?= htmlspecialchars(strtolower((string)$p['nom'])) ?>"
                     onclick="openCommandeModal(
                        <?= (int)$p['id'] ?>,
                        '<?= addslashes(htmlspecialchars((string)$p['nom'])) ?>',
                        '<?= addslashes(htmlspecialchars((string)($p['marque'] ?? ''))) ?>',
                        <?= (float)$p['prix'] ?>,
                        '<?= htmlspecialchars($ns) ?>',
                        '<?= addslashes(htmlspecialchars((string)($p['categorie'] ?? ''))) ?>',
                        <?= $p['calories']  !== null ? (float)$p['calories']  : 'null' ?>,
                        <?= $p['proteines'] !== null ? (float)$p['proteines'] : 'null' ?>,
                        <?= $p['glucides']  !== null ? (float)$p['glucides']  : 'null' ?>,
                        <?= $p['lipides']   !== null ? (float)$p['lipides']   : 'null' ?>
                     )"
                >
                    <div class="product-img">
                        <span style="font-size:3.5rem;"><?= $emoji ?></span>
                        <div class="nutriscore ns-<?= strtolower($ns) ?>"><?= htmlspecialchars($ns) ?></div>
                    </div>
                    <div class="product-body">
                        <div class="product-brand"><?= htmlspecialchars((string)($p['marque'] ?? '')) ?></div>
                        <div class="product-name"><?= htmlspecialchars((string)$p['nom']) ?></div>
                        <div class="product-cal">
                            <?= $p['calories'] !== null ? htmlspecialchars((string)$p['calories']) . ' kcal / 100g' : '—' ?>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;">
                            <span class="badge badge-green" style="font-size:0.72rem;">Disponible</span>
                            <strong style="color:#0f766e;"><?= number_format((float)$p['prix'], 2) ?> DT</strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- MODAL COMMANDE -->
    <div class="modal-overlay" id="commandeModal">
        <div class="modal-box">
            <button class="modal-close" type="button" onclick="closeCommandeModal()">×</button>

            <div class="modal-header">
                <div class="modal-emoji" id="modalEmoji"></div>
                <div class="modal-title">
                    <div class="brand" id="modalBrand"></div>
                    <h2 id="modalNom"></h2>
                    <div id="modalNsTag"></div>
                </div>
            </div>

            <div class="section-heading" style="font-size:0.95rem;margin-bottom:0.75rem;">Valeurs nutritionnelles (100g)</div>
            <div class="nutri-grid" id="modalNutriGrid"></div>

            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f8fafc;border-radius:14px;">
                <div id="modalNs" class="nutriscore" style="position:static;width:52px;height:52px;font-size:1.4rem;"></div>
                <div>
                    <div style="font-weight:600;color:#0f172a;font-size:0.95rem;">Nutri-Score</div>
                    <div id="modalNsDesc" style="font-size:0.82rem;color:#64748b;"></div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f0fdf4;border-radius:14px;">
                <div style="font-size:1.6rem;font-weight:700;color:#0f766e;" id="modalPrix"></div>
                <span class="badge badge-green">Disponible</span>
            </div>

            <div class="review-form">
                <h3>🛒 Passer une commande</h3>
                <form action="../../Controller/commande.php" method="post">
                    <input type="hidden" name="action" value="create_commande">
                    <input type="hidden" name="id_produit" id="formIdProduit">

                    <div class="form-group">
                        <label>Quantité</label>
                        <input type="number" name="quantite" id="formQuantite" min="1" value="1" oninput="updateTotal()">
                    </div>

                    <div class="form-group">
                        <label>Adresse de livraison *</label>
                        <input type="text" name="adresse_livraison" id="formAdresse" 
                               placeholder="ex: 12 rue de la Paix, Tunis" required>
                    </div>

                    <div style="margin:1rem 0; font-size:0.95rem;">
                        Total estimé : <strong id="formTotal" style="color:#0f766e;">0.00 DT</strong>
                    </div>

                    <button type="submit" class="primary-btn">Commander</button>
                </form>
            </div>
        </div>
    </div>

    
</body>
</html>