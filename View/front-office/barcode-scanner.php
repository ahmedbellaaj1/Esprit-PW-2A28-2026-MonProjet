<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBite – Scanner Code-Barres</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
    <style>
        .scanner-page { max-width: 860px; margin: 0 auto; padding: 2rem 1rem 4rem; }

        .scanner-hero {
            text-align: center;
            padding: 2.5rem 2rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a2f 100%);
            border-radius: 20px;
            margin-bottom: 2rem;
            color: white;
        }
        .scanner-hero h1 { font-size: 2rem; margin: 0 0 .5rem; font-weight: 800; }
        .scanner-hero p  { color: #94a3b8; margin: 0; font-size: 1rem; }

        /* Tabs */
        .scan-tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
        .scan-tab {
            flex: 1; padding: .75rem 1rem; text-align: center;
            border: 2px solid #e2e8f0; border-radius: 10px;
            cursor: pointer; font-weight: 600; font-size: .95rem;
            background: white; color: #64748b; transition: all .2s;
        }
        .scan-tab.active {
            border-color: #16a34a; background: #f0fdf4; color: #16a34a;
        }
        .scan-panel { display: none; }
        .scan-panel.active { display: block; }

        .upload-zone {
            border: 2.5px dashed #16a34a;
            border-radius: 16px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all .3s;
            background: #f0fdf4;
            position: relative;
        }
        .upload-zone:hover, .upload-zone.dragover {
            background: #dcfce7;
            border-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(22,163,74,.15);
        }
        .upload-zone .icon { font-size: 3.5rem; margin-bottom: 1rem; display: block; }
        .upload-zone h2 { margin: 0 0 .5rem; color: #0f172a; font-size: 1.3rem; }
        .upload-zone p  { color: #64748b; margin: 0; font-size: .9rem; }
        #fileInput { display: none; }

        .preview-box {
            display: none;
            margin-top: 1.5rem;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            position: relative;
            background: #f8fafc;
        }
        #previewImg {
            width: 100%; max-height: 360px;
            object-fit: contain; display: block;
        }
        .scan-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(15,23,42,.55); opacity: 0;
            transition: opacity .3s; pointer-events: none;
        }
        .scan-overlay.active { opacity: 1; }
        .scan-line {
            width: 80%; height: 3px;
            background: #16a34a; border-radius: 2px;
            box-shadow: 0 0 16px #16a34a, 0 0 32px rgba(22,163,74,.5);
            animation: scanAnim 1.8s ease-in-out infinite;
        }
        @keyframes scanAnim { 0%,100%{transform:translateY(-70px)} 50%{transform:translateY(70px)} }

        /* Manual input */
        .manual-zone {
            background: white; border: 1.5px solid #e2e8f0;
            border-radius: 16px; padding: 2rem;
        }
        .manual-zone label { display: block; font-weight: 600; color: #0f172a; margin-bottom: .6rem; }
        .manual-input-row { display: flex; gap: .75rem; }
        .manual-input-row input {
            flex: 1; padding: .75rem 1rem;
            border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 1rem; font-family: monospace; letter-spacing: 1px;
            transition: border-color .2s;
        }
        .manual-input-row input:focus { outline: none; border-color: #16a34a; }
        .manual-input-row button {
            padding: .75rem 1.5rem; border-radius: 10px;
            background: #16a34a; color: white; border: none;
            cursor: pointer; font-weight: 700; font-size: .95rem;
            transition: background .2s;
        }
        .manual-input-row button:hover { background: #15803d; }

        /* Status / Error */
        .status-bar {
            display: none; margin-top: 1.5rem;
            padding: 1rem 1.5rem; background: #f0fdf4;
            border-radius: 10px; color: #15803d; font-weight: 500;
            align-items: center; gap: .75rem;
        }
        .spinner {
            width: 20px; height: 20px;
            border: 3px solid #dcfce7; border-top-color: #16a34a;
            border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0;
        }
        @keyframes spin { to{ transform: rotate(360deg) } }

        .error-box {
            display: none; margin-top: 1.5rem;
            padding: 1.2rem 1.5rem; background: #fef2f2;
            border: 1px solid #fecaca; border-radius: 12px;
            color: #dc2626; align-items: center; gap: .75rem; font-weight: 500;
        }

        /* Confirm box — shown after detection so user can verify/fix the code */
        .confirm-box {
            display: none; margin-top: 1.5rem;
            background: #fffbeb; border: 2px solid #f59e0b;
            border-radius: 14px; padding: 1.25rem 1.5rem;
        }
        .confirm-box .cb-title {
            font-weight: 700; color: #92400e; margin-bottom: .75rem;
            display: flex; align-items: center; gap: .5rem; font-size: .95rem;
        }
        .confirm-box .cb-row {
            display: flex; gap: .75rem; align-items: center; flex-wrap: wrap;
        }
        .confirm-box input {
            flex: 1; padding: .65rem 1rem;
            border: 2px solid #f59e0b; border-radius: 9px;
            font-size: 1rem; font-family: monospace; letter-spacing: 1px;
            background: white; min-width: 160px;
        }
        .confirm-box input:focus { outline: none; border-color: #d97706; }
        .cb-btn-confirm {
            padding: .65rem 1.4rem; border-radius: 9px;
            background: #16a34a; color: white; border: none;
            font-weight: 700; cursor: pointer; font-size: .95rem;
            transition: background .2s; white-space: nowrap;
        }
        .cb-btn-confirm:hover { background: #15803d; }
        .cb-hint { font-size: .8rem; color: #92400e; margin-top: .6rem; }

        /* Result card */
        .result-card {
            display: none; background: white;
            border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,.1);
            overflow: hidden; margin-top: 2rem;
            animation: fadeUp .4s ease;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

        .rc-header {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white; padding: 1.2rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .rc-header .found-icon { font-size: 2rem; }
        .rc-header h3 { margin: 0; font-size: 1.1rem; }
        .rc-header small { opacity: .85; font-size: .85rem; }

        .rc-body {
            display: grid; grid-template-columns: 180px 1fr;
        }
        @media(max-width:600px){ .rc-body{ grid-template-columns:1fr; } }

        .rc-img { width: 180px; height: 180px; object-fit: cover; display: block; }
        @media(max-width:600px){ .rc-img{ width:100%; height:220px; } }

        .rc-info { padding: 1.5rem; }
        .rc-name  { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .3rem; }
        .rc-brand { font-size: .85rem; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 1rem; }

        .rc-meta { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .badge { padding: .3rem .75rem; border-radius: 999px; font-size: .78rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-blue  { background: #dbeafe; color: #1d4ed8; }
        .badge-gray  { background: #f1f5f9; color: #475569; }

        .nutri-row { display: grid; grid-template-columns: repeat(4,1fr); gap: .5rem; margin: 1rem 0; }
        .nutri-cell { background: #f8fafc; border-radius: 10px; padding: .6rem; text-align: center; }
        .nutri-cell .nv { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .nutri-cell .nl { font-size: .72rem; color: #64748b; }

        .rc-price { font-size: 1.5rem; font-weight: 800; color: #16a34a; }
        .rc-actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; }

        .ns-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 50%;
            font-weight: 800; font-size: 1.1rem; color: white; flex-shrink: 0;
        }
        .ns-a { background: #16a34a; }
        .ns-b { background: #84cc16; }
        .ns-c { background: #eab308; }
        .ns-d { background: #f97316; }
        .ns-e { background: #ef4444; }

        .reset-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .55rem 1.2rem; border-radius: 8px;
            border: 1.5px solid #e2e8f0; background: white; color: #0f172a;
            cursor: pointer; font-size: .9rem; font-weight: 600; transition: all .2s;
        }
        .reset-btn:hover { background: #f8fafc; border-color: #16a34a; color: #16a34a; }
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
        <li><a href="barcode-scanner.php" class="active">📷 Scanner</a></li>
        <li><a href="order-history.php">📋 Historique</a></li>
    </ul>
    <div class="navbar-right">
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="../back-office/dashboard.php">Dashboard Admin</a>
        <a href="cart.php" class="cart-icon" title="Voir le panier">
            🛒 <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<div class="main-container">
<div class="scanner-page">

    <div class="scanner-hero">
        <span style="font-size:3rem;display:block;margin-bottom:.75rem;">📷</span>
        <h1>Scanner un Code-Barres</h1>
        <p>Importez une photo contenant un code-barres, ou saisissez le code manuellement pour retrouver un produit.</p>
    </div>

    <!-- Tabs -->
    <div class="scan-tabs">
        <div class="scan-tab active" id="tab-image" onclick="switchTab('image')">🖼️ Scanner par image</div>
        <div class="scan-tab" id="tab-manual" onclick="switchTab('manual')">⌨️ Saisie manuelle</div>
    </div>

    <!-- Panel : Image upload -->
    <div class="scan-panel active" id="panel-image">
        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
            <span class="icon">🖼️</span>
            <h2>Importer une image</h2>
            <p>Cliquez pour choisir une photo, ou glissez-déposez une image ici<br>
               <small style="color:#94a3b8;">(JPG, PNG, WEBP — le code-barres doit être visible et net)</small>
            </p>
            <input type="file" id="fileInput" accept="image/*">
        </div>

        <div class="preview-box" id="previewBox">
            <img id="previewImg" src="" alt="Image uploadée" crossorigin="anonymous">
            <div class="scan-overlay" id="scanOverlay">
                <div class="scan-line"></div>
            </div>
        </div>
    </div>

    <!-- Panel : Manual input -->
    <div class="scan-panel" id="panel-manual">
        <div class="manual-zone">
            <label for="manualCode">📝 Entrez le code-barres du produit :</label>
            <div class="manual-input-row">
                <input type="text" id="manualCode" placeholder="Ex: 65833254, 3017620422003…"
                       inputmode="numeric" maxlength="30"
                       onkeydown="if(event.key==='Enter') searchByManual()">
                <button onclick="searchByManual()">🔍 Rechercher</button>
            </div>
            <p style="margin:.75rem 0 0;font-size:.85rem;color:#64748b;">
                💡 Le code-barres se trouve généralement sous les barres verticales sur l'emballage du produit.
            </p>
        </div>
    </div>

    <!-- Status -->
    <div class="status-bar" id="statusBar">
        <div class="spinner"></div>
        <span id="statusText">Analyse en cours…</span>
    </div>

    <!-- Confirmation box — user verifies / corrects the detected code -->
    <div class="confirm-box" id="confirmBox">
        <div class="cb-title">⚠️ Code-barres détecté — Vérifiez et confirmez</div>
        <div class="cb-row">
            <input type="text" id="confirmedCode" placeholder="Code-barres…" inputmode="numeric" maxlength="30"
                   onkeydown="if(event.key==='Enter') confirmAndSearch()">
            <button class="cb-btn-confirm" onclick="confirmAndSearch()">✅ Confirmer et rechercher</button>
        </div>
        <div class="cb-hint">📝 Si le code affiché ne correspond pas à ce que vous voyez sur l'image, corrigez-le avant de confirmer.</div>
    </div>

    <!-- Error -->
    <div class="error-box" id="errorBox">
        <span style="font-size:1.4rem;flex-shrink:0;">⚠️</span>
        <div>
            <span id="errorText">Erreur</span>
            <div id="errorHint" style="font-size:.85rem;color:#b91c1c;margin-top:.25rem;display:none;"></div>
        </div>
    </div>

    <!-- Result Card -->
    <div class="result-card" id="resultCard">
        <div class="rc-header">
            <span class="found-icon">✅</span>
            <div>
                <h3>Produit trouvé !</h3>
                <small id="rc-code-label">Code-barres : —</small>
            </div>
        </div>
        <div class="rc-body">
            <img id="rc-img" class="rc-img" src="" alt="Image produit">
            <div class="rc-info">
                <div class="rc-brand" id="rc-brand"></div>
                <div class="rc-name"  id="rc-name"></div>
                <div class="rc-meta">
                    <span class="badge badge-green" id="rc-categorie"></span>
                    <span class="badge badge-blue"  id="rc-statut"></span>
                    <span class="badge badge-gray"  id="rc-quantite"></span>
                </div>
                <div class="nutri-row">
                    <div class="nutri-cell"><div class="nv" id="rc-cal"></div><div class="nl">Calories</div></div>
                    <div class="nutri-cell"><div class="nv" id="rc-prot"></div><div class="nl">Protéines</div></div>
                    <div class="nutri-cell"><div class="nv" id="rc-gluc"></div><div class="nl">Glucides</div></div>
                    <div class="nutri-cell"><div class="nv" id="rc-lip"></div><div class="nl">Lipides</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.5rem;">
                    <div class="rc-price" id="rc-price"></div>
                    <span class="ns-badge" id="rc-ns"></span>
                </div>
                <div class="rc-actions">
                    <a id="rc-link" href="#" class="primary-btn">👁 Voir le produit</a>
                    <button class="reset-btn" onclick="resetScanner()">↩ Nouveau scan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips -->
    <div style="margin-top:2.5rem;background:#f8fafc;border-radius:14px;padding:1.5rem;">
        <h3 style="margin:0 0 1rem;color:#0f172a;font-size:1rem;">💡 Conseils pour un scan réussi</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
            <div style="display:flex;gap:.6rem;align-items:flex-start;">
                <span style="font-size:1.3rem;">📸</span>
                <div><strong style="display:block;color:#0f172a;font-size:.9rem;">Bonne lumière</strong><span style="color:#64748b;font-size:.85rem;">Évitez les reflets sur l'emballage</span></div>
            </div>
            <div style="display:flex;gap:.6rem;align-items:flex-start;">
                <span style="font-size:1.3rem;">🎯</span>
                <div><strong style="display:block;color:#0f172a;font-size:.9rem;">Code bien cadré</strong><span style="color:#64748b;font-size:.85rem;">Le code-barres doit occuper une bonne partie de l'image</span></div>
            </div>
            <div style="display:flex;gap:.6rem;align-items:flex-start;">
                <span style="font-size:1.3rem;">⌨️</span>
                <div><strong style="display:block;color:#0f172a;font-size:.9rem;">Saisie manuelle</strong><span style="color:#64748b;font-size:.85rem;">Si le scan échoue, utilisez l'onglet saisie manuelle</span></div>
            </div>
        </div>
    </div>

</div>
</div>

<!-- QuaggaJS — bien meilleur pour décoder des images statiques uploadées -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script src="../assets/cart.js"></script>

<script>
// ─── Elements ───────────────────────────────────────────────────────────────
const fileInput    = document.getElementById('fileInput');
const uploadZone   = document.getElementById('uploadZone');
const previewBox   = document.getElementById('previewBox');
const previewImg   = document.getElementById('previewImg');
const scanOverlay  = document.getElementById('scanOverlay');
const statusBar    = document.getElementById('statusBar');
const statusText   = document.getElementById('statusText');
const errorBox     = document.getElementById('errorBox');
const errorText    = document.getElementById('errorText');
const errorHint    = document.getElementById('errorHint');
const resultCard   = document.getElementById('resultCard');
const codeDisplay  = document.getElementById('codeDisplay');
const detectedCode = document.getElementById('detectedCode');

// ─── Tab switching ────────────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.scan-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.scan-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
    resetScanner();
}

// ─── Drag & drop ─────────────────────────────────────────────────────────────
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) processFile(file);
});
fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) processFile(fileInput.files[0]);
});

// ─── Convert file to base64 data URL ─────────────────────────────────────────
function fileToDataURL(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload  = e => resolve(e.target.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

// ─── Process image file ───────────────────────────────────────────────────────
async function processFile(file) {
    resetScanner(false);

    // Show preview
    const objectUrl = URL.createObjectURL(file);
    previewImg.src = objectUrl;
    previewBox.style.display = 'block';
    scanOverlay.classList.add('active');
    showStatus('Analyse du code-barres en cours…');

    // Convert to base64 for Quagga (works with data URLs reliably)
    let dataUrl;
    try {
        dataUrl = await fileToDataURL(file);
    } catch(e) {
        scanOverlay.classList.remove('active');
        hideStatus();
        showError('Impossible de lire le fichier image.');
        return;
    }

    decodeWithQuagga(dataUrl);
}

// ─── Quagga decodeSingle — designed for static image decoding ────────────────
function decodeWithQuagga(src) {
    const readers = [
        'ean_reader',
        'ean_8_reader',
        'code_128_reader',
        'code_39_reader',
        'code_39_vin_reader',
        'upc_reader',
        'upc_e_reader',
        'i2of5_reader',
        'codabar_reader',
    ];

    Quagga.decodeSingle({
        decoder: { readers },
        locate: true,          // try to locate the barcode within the image
        src: src,
        numOfWorkers: 0,       // required for decodeSingle
        inputStream: {
            size: 1920,        // max processing resolution
        },
    }, function(result) {
        scanOverlay.classList.remove('active');
        hideStatus();

        if (result && result.codeResult && result.codeResult.code) {
            const code = result.codeResult.code;
            // Show editable confirm box instead of searching immediately
            showConfirmBox(code);
        } else {
            showError(
                'Aucun code-barres détecté dans cette image.',
                'Astuce : recadrez l\'image pour que le code-barres occupe plus de place, ou utilisez l\'onglet « Saisie manuelle ».'
            );
            console.warn('Quagga: no barcode found', result);
        }
    });
}

// ─── Show confirm box after Quagga detection ─────────────────────────────────
function showConfirmBox(code) {
    hideStatus();
    document.getElementById('confirmedCode').value = code;
    document.getElementById('confirmBox').style.display = 'block';
    document.getElementById('confirmedCode').focus();
    document.getElementById('confirmedCode').select();
}

function confirmAndSearch() {
    const code = document.getElementById('confirmedCode').value.trim();
    if (!code) {
        showError('Le code-barres est vide.');
        return;
    }
    document.getElementById('confirmBox').style.display = 'none';
    showStatus('Recherche du produit pour le code : ' + code + '…');
    searchProduct(code);
}

// ─── Manual code search ───────────────────────────────────────────────────────
function searchByManual() {
    const code = document.getElementById('manualCode').value.trim();
    if (!code) {
        showError('Veuillez entrer un code-barres.');
        return;
    }
    resetScanner(false);
    detectedCode.textContent = code;
    codeDisplay.style.display = 'block';
    showStatus('Recherche du produit…');
    searchProduct(code);
}

// ─── API call ─────────────────────────────────────────────────────────────────
async function searchProduct(code) {
    try {
        const res  = await fetch('../../api/barcode_search.php?code=' + encodeURIComponent(code));
        const data = await res.json();
        hideStatus();

        if (data.ok && data.product) {
            displayResult(data.product, code);
        } else {
            showError(
                data.message || 'Produit introuvable pour ce code-barres.',
                'Le code « ' + code + ' » n\'existe pas dans notre catalogue.'
            );
        }
    } catch (e) {
        hideStatus();
        showError('Erreur de connexion au serveur.', e.message);
    }
}

// ─── Display product result ───────────────────────────────────────────────────
function displayResult(p, code) {
    document.getElementById('rc-code-label').textContent = 'Code-barres : ' + code;

    const imgEl = document.getElementById('rc-img');
    imgEl.src = p.image || 'https://placehold.co/180x180?text=Produit';
    imgEl.onerror = () => { imgEl.src = 'https://placehold.co/180x180?text=Produit'; };

    document.getElementById('rc-brand').textContent     = (p.marque || '').toUpperCase();
    document.getElementById('rc-name').textContent      = p.nom || '';
    document.getElementById('rc-categorie').textContent = '📂 ' + (p.categorie || '');
    document.getElementById('rc-statut').textContent    = '📌 ' + (p.statut || '');
    document.getElementById('rc-quantite').textContent  = '📦 Stock : ' + (p.quantite_disponible || 0);

    document.getElementById('rc-cal').textContent  = parseInt(p.calories  || 0) + ' kcal';
    document.getElementById('rc-prot').textContent = parseFloat(p.proteines || 0).toFixed(1) + 'g';
    document.getElementById('rc-gluc').textContent = parseFloat(p.glucides  || 0).toFixed(1) + 'g';
    document.getElementById('rc-lip').textContent  = parseFloat(p.lipides   || 0).toFixed(1) + 'g';

    document.getElementById('rc-price').textContent = parseFloat(p.prix || 0).toFixed(2).replace('.', ',') + ' DT';

    const ns   = (p.nutriscore || 'C').toUpperCase();
    const nsEl = document.getElementById('rc-ns');
    nsEl.textContent = ns;
    nsEl.className   = 'ns-badge ns-' + ns.toLowerCase();

    document.getElementById('rc-link').href = 'product.php?id=' + p.id_produit;

    resultCard.style.display = 'block';
    resultCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ─── UI helpers ───────────────────────────────────────────────────────────────
function showStatus(msg) {
    statusText.textContent  = msg;
    statusBar.style.display = 'flex';
    errorBox.style.display  = 'none';
}
function hideStatus() { statusBar.style.display = 'none'; }

function showError(msg, hint) {
    errorText.textContent = msg;
    if (hint) {
        errorHint.textContent  = hint;
        errorHint.style.display = 'block';
    } else {
        errorHint.style.display = 'none';
    }
    errorBox.style.display = 'flex';
    hideStatus();
}

function resetScanner(resetFile = true) {
    if (resetFile) {
        fileInput.value = '';
        previewBox.style.display = 'none';
        previewImg.src = '';
    }
    scanOverlay.classList.remove('active');
    resultCard.style.display  = 'none';
    errorBox.style.display    = 'none';
    statusBar.style.display   = 'none';
    errorHint.style.display   = 'none';
    document.getElementById('confirmBox').style.display = 'none';
    document.getElementById('confirmedCode').value = '';
}
</script>

<?php require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>
</html>
