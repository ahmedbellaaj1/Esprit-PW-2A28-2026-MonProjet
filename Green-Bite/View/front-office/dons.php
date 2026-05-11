<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBite — Dons alimentaires</title>
    <meta name="description" content="Consultez et réservez des dons alimentaires publiés par la communauté GreenBite.">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); padding: 3rem 2rem; text-align: center; }
        .hero-section h1 { font-size: 2.2rem; color: #0f172a; margin-bottom: .75rem; }
        .hero-section p { color: #475569; font-size: 1.05rem; margin-bottom: 1.5rem; }
        .search-wrapper { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; max-width: 600px; margin: 0 auto; }
        .search-wrapper input { flex: 1; min-width: 220px; padding: .7rem 1rem; border: 1.5px solid #d1fae5; border-radius: 9999px; font-size: .95rem; }
        .search-wrapper button { padding: .7rem 1.4rem; border: none; border-radius: 9999px; cursor: pointer; font-weight: 600; font-size: .9rem; }
        .search-wrapper button:first-of-type { background: #16a34a; color: #fff; }
        .search-wrapper button:last-of-type { background: #0f766e; color: #fff; }
        .filter-bar { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .filter-pill { border: 1.5px solid #e2e8f0; background: #fff; border-radius: 9999px; padding: .45rem 1.1rem; font-size: .85rem; cursor: pointer; font-weight: 500; transition: all .2s; }
        .filter-pill.active, .filter-pill:hover { background: #16a34a; color: #fff; border-color: #16a34a; }
        .section-heading { font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #fff; border-radius: 18px; padding: 2rem; max-width: 520px; width: 90%; max-height: 85vh; overflow-y: auto; position: relative; }
        .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b; }
        .form-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1100; align-items: center; justify-content: center; }
        .form-modal-overlay.open { display: flex; }
        .form-modal { background: #fff; border-radius: 18px; padding: 2rem; max-width: 620px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; }
        .form-alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: .75rem 1rem; font-size: .88rem; margin-bottom: 1rem; }
        .form-label-section { font-weight: 600; font-size: .9rem; color: #0f172a; display: block; margin-bottom: .4rem; }
        .form-actions { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }
        .btn-cancel { background: #f1f5f9; border: none; border-radius: 9999px; padding: .65rem 1.4rem; cursor: pointer; font-weight: 500; }
        .toast { position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(1rem); background: #0f172a; color: #fff; padding: .75rem 1.5rem; border-radius: 9999px; font-size: .9rem; font-weight: 500; opacity: 0; transition: all .35s; z-index: 9999; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        #front-don-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 2rem; }
        .metric-card { background: #fff; border-radius: 14px; padding: 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .metric-value { font-size: 1.8rem; font-weight: 700; }
        .metric-label { font-size: .8rem; color: #64748b; margin-top: .25rem; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<section class="hero-section">
    <h1>🎁 Dons alimentaires disponibles</h1>
    <p>Consultez les dons publiés, réservez ceux dont vous avez besoin ou publiez vos propres dons.</p>
    <div class="search-wrapper">
        <input type="text" id="front-don-search" placeholder="Rechercher un produit (ex: pain, légumes...)">
        <button onclick="loadFrontDons()">Rechercher</button>
        <button onclick="openFrontCreateDonModal()">+ Publier un don</button>
    </div>
</section>

<div class="main-container">
    <div class="filter-bar">
        <button class="filter-pill active" onclick="setFrontDonFilter(this,'')">Tous</button>
        <button class="filter-pill" onclick="setFrontDonFilter(this,'disponible')">✅ Disponible</button>
        <button class="filter-pill" onclick="setFrontDonFilter(this,'réservé')">🔒 Réservé</button>
        <button class="filter-pill" onclick="setFrontDonFilter(this,'récupéré')">📦 Récupéré</button>
    </div>

    <div id="front-don-stats"></div>

    <div class="section-heading" style="margin-top:2rem;margin-bottom:1rem;">🏆 Meilleurs partenaires</div>
    <div id="top-partenaires-container" style="margin-bottom:2rem;">
        <div style="text-align:center;padding:1rem;color:#94a3b8;">Chargement...</div>
    </div>

    <div class="section-heading">
        Dons publiés
        <span id="front-don-count" style="font-size:.85rem;font-weight:400;color:#64748b;"></span>
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

<!-- MODAL CRÉATION DON (FRONT) -->
<div class="form-modal-overlay" id="frontDonCreateModal">
    <div class="form-modal" style="max-width:620px;">
        <button class="modal-close" onclick="closeFrontCreateDonModal()">✕</button>
        <h2>Publier un nouveau don</h2>
        <div id="front-don-create-alert" class="form-alert" style="display:none;"></div>
        <div id="front-image-section" style="margin-bottom:1.25rem;">
            <label class="form-label-section">📸 Photo du don <span style="font-size:.78rem;color:#64748b;font-weight:400;">(vérification alimentaire par IA)</span></label>
            <div id="front-dropzone" onclick="document.getElementById('front-image-input').click()" ondragover="event.preventDefault();this.style.borderColor='#0f766e';" ondragleave="this.style.borderColor='#14b8a6';" ondrop="handleFrontImageDrop(event)" style="margin-top:8px;border:2px dashed #14b8a6;border-radius:14px;padding:20px;text-align:center;cursor:pointer;background:#f0fdfa;transition:all .2s;">
                <div id="front-dropzone-content">
                    <div style="font-size:2rem;">📁</div>
                    <div style="font-size:.85rem;color:#0f766e;font-weight:600;margin-top:4px;">Cliquez ou glissez une image ici</div>
                    <div style="font-size:.75rem;color:#64748b;margin-top:2px;">JPEG, PNG, WebP — max 5 Mo</div>
                </div>
                <img id="front-image-preview" src="" alt="" style="display:none;max-height:140px;border-radius:10px;margin-top:8px;">
            </div>
            <input type="file" id="front-image-input" accept="image/*" style="display:none;" onchange="handleFrontImageSelect(this.files[0])">
            <div id="front-ai-badge" style="display:none;margin-top:10px;padding:10px 14px;border-radius:12px;font-size:.85rem;font-weight:600;"></div>
        </div>
        <div style="margin-bottom:1rem;">
            <label class="form-label-section">Produits du don *</label>
            <div id="front-produits-lines" style="margin-top:.5rem;display:flex;flex-direction:column;gap:8px;"></div>
            <button type="button" onclick="frontAddProduitLine()" style="margin-top:8px;background:none;border:1.5px dashed #14b8a6;color:#0f766e;border-radius:10px;padding:7px 16px;font-size:.82rem;font-weight:600;cursor:pointer;width:100%;">+ Ajouter un produit</button>
        </div>
        <div class="form-actions">
            <button class="btn-cancel" onclick="closeFrontCreateDonModal()">Annuler</button>
            <button class="primary-btn" id="front-btn-publier" style="padding:10px 24px;" onclick="submitFrontDon()">Publier</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
<script src="/Green-Bite/View/assets/dons.js"></script>
<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>
</html>
