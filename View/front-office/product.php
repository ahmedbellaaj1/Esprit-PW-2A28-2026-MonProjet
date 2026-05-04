<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';
require_once __DIR__ . '/../../Controller/ReviewController.php';

$id = (int) ($_GET['id'] ?? 0);

$controller = new ProductController();
$product    = $controller->find($id);

if (!$product) {
    http_response_code(404);
    echo 'Produit introuvable';
    exit;
}

$reviewController = new ReviewController();
$reviewData  = $reviewController->getProductReviews($id);
$reviews     = $reviewData['avis'];
$stats       = $reviewData['stats'];
$dist        = $reviewData['distribution'];
$avgNote     = round((float)$stats['moyenne_note'], 1);
$nbAvis      = (int)$stats['nombre_avis'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($product['nom']) ?> - GreenBite</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
    <style>
        .rating-summary {
            display: flex; gap: 2rem; align-items: center;
            background: white; border-radius: 16px;
            padding: 1.5rem; margin-bottom: 1.5rem;
            box-shadow: 0 1px 8px rgba(0,0,0,.06); flex-wrap: wrap;
        }
        .avg-block { text-align: center; flex-shrink: 0; }
        .avg-num   { font-size: 3.5rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .avg-stars { color: #f59e0b; font-size: 1.4rem; letter-spacing: 2px; margin: .3rem 0; }
        .avg-count { font-size: .82rem; color: #64748b; }

        .dist-bars { flex:1; min-width:180px; display:flex; flex-direction:column; gap:.35rem; }
        .dist-row  { display:flex; align-items:center; gap:.6rem; font-size:.82rem; }
        .dist-label{ width:12px; text-align:right; color:#64748b; font-weight:700; }
        .dist-bar-bg { flex:1; background:#f1f5f9; border-radius:999px; height:8px; overflow:hidden; }
        .dist-bar-fill { height:100%; background:#f59e0b; border-radius:999px; }
        .dist-cnt  { width:24px; color:#94a3b8; font-size:.75rem; }

        .reviews-list { display:grid; gap:1rem; margin-top:1.5rem; }
        .review-item {
            background:white; border-radius:14px; padding:1.25rem 1.5rem;
            box-shadow:0 1px 6px rgba(0,0,0,.05); border:1px solid #f1f5f9;
        }
        .ri-top  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.5rem; }
        .ri-title{ font-weight:700; color:#0f172a; font-size:.97rem; }
        .ri-stars{ color:#f59e0b; font-size:.95rem; }
        .ri-meta { font-size:.78rem; color:#94a3b8; margin-bottom:.6rem; }
        .ri-text { font-size:.88rem; color:#475569; line-height:1.65; }

        .review-form-box {
            background:white; border-radius:16px; padding:1.75rem;
            box-shadow:0 1px 8px rgba(0,0,0,.06); margin-top:1.5rem;
            border:1.5px solid #e2e8f0;
        }
        .review-form-box h3 { font-size:1.05rem; margin:0 0 1.25rem; color:#0f172a; }

        .star-picker { display:flex; gap:.35rem; margin-bottom:1rem; }
        .star-btn {
            font-size:2rem; background:none; border:none; cursor:pointer;
            color:#cbd5e1; transition:color .15s, transform .15s; padding:0; line-height:1;
        }
        .star-btn.lit { color:#f59e0b; }
        .star-btn:hover { transform:scale(1.2); }

        .rf-group { margin-bottom:1rem; }
        .rf-group label { display:block; font-size:.85rem; font-weight:600; color:#374151; margin-bottom:.4rem; }
        .rf-group input, .rf-group textarea {
            width:100%; padding:.7rem .9rem;
            border:1.5px solid #e2e8f0; border-radius:9px;
            font-size:.93rem; font-family:inherit; transition:border-color .2s;
        }
        .rf-group input:focus, .rf-group textarea:focus {
            outline:none; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.1);
        }
        .rf-char { font-size:.75rem; color:#94a3b8; text-align:right; margin-top:.25rem; }

        .btn-submit-review {
            background:linear-gradient(135deg,#16a34a,#15803d);
            color:white; border:none; border-radius:10px;
            padding:.8rem 1.75rem; font-size:.95rem; font-weight:700;
            cursor:pointer; transition:all .25s; display:inline-flex; align-items:center; gap:.5rem;
        }
        .btn-submit-review:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 20px rgba(22,163,74,.3); }
        .btn-submit-review:disabled { opacity:.6; cursor:not-allowed; }

        .rf-alert { padding:.8rem 1.1rem; border-radius:9px; font-size:.88rem; font-weight:500; display:none; margin-bottom:1rem; }
        .rf-alert.show { display:flex; align-items:center; gap:.5rem; }
        .rf-ok  { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
        .rf-err { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

        .no-reviews { text-align:center; padding:2.5rem; color:#94a3b8; background:#f8fafc; border-radius:12px; }
        @keyframes spin { to { transform:rotate(360deg); } }
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
        <li><a href="index.php" class="active">Produits</a></li>
        <li><a href="order-history.php">📋 Historique</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="index.php">Catalogue</a>
        <a href="cart.php" class="cart-icon" title="Voir le panier">
            🛒 <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<div class="main-container" style="padding-top:1.5rem;">
    <div class="section-heading">Détails produit</div>

    <div class="table-container">
        <div class="modal-header">
            <div class="modal-emoji" style="overflow:hidden;padding:0;">
                <img src="<?= h($product['image'] ?: 'https://placehold.co/400x400?text=Produit') ?>"
                     alt="Image" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="modal-title">
                <div class="brand"><?= h($product['marque']) ?> - <?= h($product['categorie']) ?></div>
                <h2 style="margin-top:0;"><?= h($product['nom']) ?></h2>
                <div class="product-tags">
                    <span class="tag tag-local">Code: <?= h((string)$product['code_barre']) ?></span>
                    <span class="tag tag-bio">Statut: <?= h((string)$product['statut']) ?></span>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f8fafc;border-radius:14px;">
            <div class="nutriscore ns-<?= strtolower((string)$product['nutriscore']) ?>" style="position:static;width:52px;height:52px;font-size:1.4rem;">
                <?= h((string)$product['nutriscore']) ?>
            </div>
            <div>
                <div style="font-weight:600;color:#0f172a;font-size:.95rem;">Prix: <?= number_format((float)$product['prix'], 2, ',', ' ') ?> DT</div>
                <div style="font-size:.82rem;color:#64748b;">Date ajout: <?= h((string)$product['date_ajout']) ?></div>
            </div>
        </div>

        <div class="section-heading" style="font-size:.95rem;margin-bottom:.75rem;">Valeurs nutritionnelles (100g)</div>
        <div class="nutri-grid">
            <div class="nutri-item"><label>Calories</label><div class="value"><?= (int)$product['calories'] ?> <span class="unit">kcal</span></div></div>
            <div class="nutri-item"><label>Protéines</label><div class="value"><?= h((string)$product['proteines']) ?> <span class="unit">g</span></div></div>
            <div class="nutri-item"><label>Glucides</label><div class="value"><?= h((string)$product['glucides']) ?> <span class="unit">g</span></div></div>
            <div class="nutri-item"><label>Lipides</label><div class="value"><?= h((string)$product['lipides']) ?> <span class="unit">g</span></div></div>
        </div>

        <!-- Add to cart -->
        <div class="review-form" style="margin-top:1.5rem;">
            <h3>Commander ce produit</h3>
            <div style="display:flex;gap:1rem;align-items:flex-end;">
                <div style="flex:1;">
                    <label for="cart_quantite">Quantité <span style="color:#64748b;font-size:.9rem;">(Dispo: <?= (int)$product['quantite_disponible'] ?>)</span></label>
                    <input id="cart_quantite" type="number" value="1" min="1" max="<?= (int)$product['quantite_disponible'] ?>" step="1" style="width:100%;"
                           oninput="validateQuantityInput(this, <?= (int)$product['quantite_disponible'] ?>)">
                </div>
                <button type="button" class="primary-btn"
                    onclick="addProductToCart(<?= (int)$product['id_produit'] ?>, '<?= h((string)$product['nom']) ?>', '<?= h((string)$product['marque']) ?>', <?= (float)$product['prix'] ?>, '<?= h($product['image'] ?: 'https://placehold.co/400x400?text=Produit') ?>', <?= (int)$product['quantite_disponible'] ?>)">
                    🛒 Ajouter au panier
                </button>
            </div>
            <div id="cartMessage" style="margin-top:1rem;padding:.75rem;border-radius:8px;display:none;"></div>
        </div>
    </div>

    <!-- ════ REVIEWS SECTION ════ -->
    <div style="margin-top:2.5rem;">
        <div class="section-heading">⭐ Avis clients</div>

        <!-- Rating summary -->
        <div class="rating-summary">
            <div class="avg-block">
                <div class="avg-num"><?= $nbAvis > 0 ? number_format($avgNote, 1) : '-' ?></div>
                <div class="avg-stars">
                    <?php
                    if ($nbAvis > 0) {
                        $full = (int)floor($avgNote);
                        echo str_repeat('★', $full) . str_repeat('☆', 5 - $full);
                    } else {
                        echo '☆☆☆☆☆';
                    }
                    ?>
                </div>
                <div class="avg-count"><?= $nbAvis ?> avis</div>
            </div>
            <div class="dist-bars">
                <?php
                $total = array_sum($dist) ?: 1;
                for ($s = 5; $s >= 1; $s--):
                    $pct = (int)round(($dist[$s] / $total) * 100);
                ?>
                <div class="dist-row">
                    <span class="dist-label"><?= $s ?></span>
                    <span style="color:#f59e0b;font-size:.75rem;">★</span>
                    <div class="dist-bar-bg">
                        <div class="dist-bar-fill" style="width:<?= $pct ?>%"></div>
                    </div>
                    <span class="dist-cnt"><?= $dist[$s] ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Reviews list -->
        <?php if (empty($reviews)): ?>
            <div class="no-reviews">
                <div style="font-size:2.5rem;margin-bottom:.75rem;">💬</div>
                <p style="font-weight:600;color:#475569;font-size:1rem;">Aucun avis approuvé pour ce produit</p>
                <p style="font-size:.88rem;margin-top:.3rem;">Soyez le premier à partager votre expérience !</p>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $r):
                    $rNote  = (int)$r['note'];
                    $rStars = str_repeat('★', $rNote) . str_repeat('☆', 5 - $rNote);
                    $rDate  = (new DateTime($r['date_avis']))->format('d/m/Y');
                ?>
                <div class="review-item">
                    <div class="ri-top">
                        <div class="ri-title"><?= h($r['titre']) ?></div>
                        <div class="ri-stars"><?= $rStars ?></div>
                    </div>
                    <div class="ri-meta">👤 Utilisateur #<?= (int)$r['id_utilisateur'] ?> &nbsp;·&nbsp; 🕐 <?= $rDate ?></div>
                    <div class="ri-text"><?= h($r['texte']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Submit review form -->
        <div class="review-form-box">
            <h3>✍️ Laisser un avis</h3>

            <div class="rf-alert rf-ok" id="rfOk">✅ Avis soumis ! Il sera visible après validation par notre équipe. Merci !</div>
            <div class="rf-alert rf-err" id="rfErr"></div>

            <div class="rf-group">
                <label>ID Utilisateur *</label>
                <input type="number" id="rfUserId" min="1" placeholder="Ex : 1">
            </div>

            <div class="rf-group">
                <label>Votre note *</label>
                <div class="star-picker" id="starPicker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button class="star-btn" type="button" data-val="<?= $i ?>"
                            onmouseover="hoverStars(<?= $i ?>)" onmouseout="resetStars()"
                            onclick="selectStar(<?= $i ?>)">★</button>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="rf-group">
                <label>Titre de l'avis * <span style="font-weight:400;color:#94a3b8;">(5-150 car.)</span></label>
                <input type="text" id="rfTitre" maxlength="150" placeholder="Résumez votre expérience…"
                       oninput="document.getElementById('rfTitreChar').textContent=this.value.length+'/150'">
                <div class="rf-char" id="rfTitreChar">0/150</div>
            </div>

            <div class="rf-group">
                <label>Commentaire * <span style="font-weight:400;color:#94a3b8;">(10-1000 car.)</span></label>
                <textarea id="rfTexte" rows="4" maxlength="1000" placeholder="Partagez votre expérience avec ce produit…"
                          oninput="document.getElementById('rfTexteChar').textContent=this.value.length+'/1000'"></textarea>
                <div class="rf-char" id="rfTexteChar">0/1000</div>
            </div>

            <button class="btn-submit-review" id="rfSubmit" onclick="submitReview()">
                <span id="rfSpinner" style="display:none;width:16px;height:16px;border:3px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;"></span>
                <span id="rfLabel">📨 Soumettre l'avis</span>
            </button>
            <p style="font-size:.78rem;color:#94a3b8;margin-top:.75rem;">
                🔍 Les avis sont modérés avant publication pour garantir leur authenticité.
            </p>
        </div>
    </div>
</div>

<script src="../assets/cart.js"></script>
<script>
const PRODUCT_ID = <?= (int)$product['id_produit'] ?>;
let selectedStar = 0;

function hoverStars(n) {
    document.querySelectorAll('.star-btn').forEach((b, i) => b.classList.toggle('lit', i < n));
}
function resetStars() {
    document.querySelectorAll('.star-btn').forEach((b, i) => b.classList.toggle('lit', i < selectedStar));
}
function selectStar(n) {
    selectedStar = n;
    resetStars();
}

async function submitReview() {
    const userId = parseInt(document.getElementById('rfUserId').value);
    const titre  = document.getElementById('rfTitre').value.trim();
    const texte  = document.getElementById('rfTexte').value.trim();

    document.getElementById('rfOk').classList.remove('show');
    document.getElementById('rfErr').classList.remove('show');

    if (!userId || userId < 1)  return showRfErr('ID utilisateur invalide.');
    if (!selectedStar)          return showRfErr('Sélectionnez une note de 1 à 5 étoiles.');
    if (titre.length < 5)       return showRfErr('Le titre doit faire au moins 5 caractères.');
    if (texte.length < 10)      return showRfErr('Le commentaire doit faire au moins 10 caractères.');

    setRfLoading(true);
    try {
        const res  = await fetch('../../api/reviews/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_produit: PRODUCT_ID, id_utilisateur: userId, note: selectedStar, titre, texte })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('rfOk').classList.add('show');
            document.getElementById('rfUserId').value = '';
            document.getElementById('rfTitre').value  = '';
            document.getElementById('rfTexte').value  = '';
            document.getElementById('rfTitreChar').textContent = '0/150';
            document.getElementById('rfTexteChar').textContent = '0/1000';
            selectedStar = 0; resetStars();
        } else {
            showRfErr(data.message || 'Erreur lors de la soumission.');
        }
    } catch(e) {
        showRfErr('Erreur réseau : ' + e.message);
    } finally {
        setRfLoading(false);
    }
}

function showRfErr(msg) {
    const el = document.getElementById('rfErr');
    el.textContent = '⚠️ ' + msg;
    el.classList.add('show');
}
function setRfLoading(on) {
    document.getElementById('rfSubmit').disabled = on;
    document.getElementById('rfSpinner').style.display = on ? 'inline-block' : 'none';
    document.getElementById('rfLabel').textContent = on ? 'Envoi…' : "📨 Soumettre l'avis";
}

document.addEventListener('DOMContentLoaded', () => updateCartBadge());
</script>

<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>
</html>
