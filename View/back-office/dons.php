<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$activePage = 'dons';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office — Gestion des dons alimentaires</title>
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <style>
        /* ── Tabs ── */
        .tabs { display: flex; gap: .5rem; }
        .tab-btn { background: #f1f5f9; border: none; border-radius: 9999px; padding: .55rem 1.2rem; font-size: .88rem; cursor: pointer; font-weight: 500; transition: all .2s; }
        .tab-btn.active { background: #16a34a; color: #fff; }
        /* ── Table toolbar ── */
        .table-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1rem; }
        .table-search { display: flex; align-items: center; gap: .5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9999px; padding: .45rem 1rem; }
        .table-search input { border: none; background: none; outline: none; font-size: .9rem; width: 180px; }
        .filter-select { border: 1px solid #e2e8f0; border-radius: 9999px; padding: .45rem .9rem; font-size: .85rem; background: #fff; cursor: pointer; }
        .btn-add { background: #16a34a; color: #fff; border: none; border-radius: 9999px; padding: .55rem 1.2rem; font-size: .87rem; cursor: pointer; font-weight: 600; }
        .btn-icon { background: none; border: none; cursor: pointer; padding: 4px 8px; border-radius: 8px; font-size: 1rem; transition: background .2s; }
        .btn-edit:hover { background: #f0fdf4; }
        .btn-del:hover { background: #fef2f2; }
        /* ── Modals ── */
        .form-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1100; align-items: center; justify-content: center; }
        .form-modal-overlay.open { display: flex; }
        .form-modal { background: #fff; border-radius: 18px; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b; }
        .form-alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: .75rem 1rem; font-size: .88rem; margin-bottom: 1rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: .4rem; }
        .form-group label { font-size: .85rem; font-weight: 600; color: #374151; }
        .form-group input, .form-input-select { padding: .6rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: .9rem; width: 100%; box-sizing: border-box; }
        .form-group input:focus, .form-input-select:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.1); }
        .form-label-section { font-weight: 600; font-size: .9rem; color: #0f172a; display: block; margin-bottom: .4rem; }
        .form-actions { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; }
        .btn-cancel { background: #f1f5f9; border: none; border-radius: 9999px; padding: .65rem 1.4rem; cursor: pointer; font-weight: 500; }
        .btn-danger { background: #dc2626; color: #fff; border: none; border-radius: 9999px; padding: .65rem 1.4rem; cursor: pointer; font-weight: 600; }
        /* ── Confirm modal ── */
        .confirm-modal { background: #fff; border-radius: 18px; padding: 2.5rem 2rem; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .confirm-modal h3 { margin: 0 0 .5rem; font-size: 1.15rem; }
        .confirm-modal p { color: #64748b; font-size: .9rem; margin-bottom: 1.5rem; }
        /* ── Toast ── */
        .toast { position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(1rem); background: #0f172a; color: #fff; padding: .75rem 1.5rem; border-radius: 9999px; font-size: .9rem; font-weight: 500; opacity: 0; transition: all .35s; z-index: 9999; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <div class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-title">🎁 Gestion des dons alimentaires</div>
            <div class="header-right">
                <span class="header-badge">🟢 En ligne</span>
                <?php
                $u = getCurrentUser();
                $initials = strtoupper(mb_substr($u['prenom'] ?? '', 0, 1) . mb_substr($u['nom'] ?? '', 0, 1)) ?: 'AD';
                ?>
                <div class="admin-avatar"><?= htmlspecialchars($initials) ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Dons alimentaires</h1>
                <p>Supervisez tous les dons publiés, gérez les statuts et les partenaires bénéficiaires.</p>
            </div>

            <!-- STATISTIQUES -->
            <div class="metrics-grid" id="don-stats"></div>

            <!-- TABS -->
            <div class="tabs" style="margin-top:1.5rem;">
                <button class="tab-btn active" onclick="switchDonTab(this,'liste')">🎁 Dons</button>
                <button class="tab-btn" onclick="switchDonTab(this,'partenaires')">🤝 Partenaires</button>
            </div>

            <!-- TAB DONS -->
            <div id="don-tab-liste">
                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="table-search">
                            <span>🔍</span>
                            <input type="text" id="don-search" placeholder="Rechercher par produit..." oninput="loadDons()">
                        </div>
                        <div style="display:flex;gap:.75rem;align-items:center;">
                            <select id="don-filter-statut" onchange="loadDons()" class="filter-select">
                                <option value="">Tous les statuts</option>
                                <option value="disponible">Disponible</option>
                                <option value="réservé">Réservé</option>
                                <option value="récupéré">Récupéré</option>
                                <option value="périmé">⚠️ Périmé</option>
                            </select>
                            <button class="btn-add" onclick="openDonModal()">+ Nouveau don</button>
                        </div>
                    </div>
                    <table>
                        <thead><tr>
                            <th>Produits</th><th>Qté totale</th><th>Péremption min.</th>
                            <th>Statut</th><th>Partenaire</th><th>Publié le</th><th>Actions</th>
                        </tr></thead>
                        <tbody id="don-tbody">
                            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Chargement…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB PARTENAIRES -->
            <div id="don-tab-partenaires" style="display:none;">
                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="table-search">
                            <span>🔍</span>
                            <input type="text" id="part-search" placeholder="Rechercher un partenaire..." oninput="loadPartenaires()">
                        </div>
                        <div style="display:flex;gap:.75rem;align-items:center;">
                            <select id="part-filter-type" onchange="loadPartenaires()" class="filter-select">
                                <option value="">Tous les types</option>
                                <option value="association">Association</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="épicerie">Épicerie</option>
                            </select>
                            <button class="btn-add" onclick="openPartModal()">+ Nouveau partenaire</button>
                        </div>
                    </div>
                    <table>
                        <thead><tr>
                            <th>Nom</th><th>Type</th><th>Adresse</th>
                            <th>Téléphone</th><th>Email</th><th>Dons liés</th><th>Actions</th>
                        </tr></thead>
                        <tbody id="part-tbody">
                            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Chargement…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /page-content -->
    </div><!-- /dashboard-main -->
</div><!-- /dashboard-layout -->

<!-- ═══ MODAL DON ═══ -->
<div class="form-modal-overlay" id="donModal">
    <div class="form-modal" style="max-width:600px;">
        <button class="modal-close" onclick="closeDonModal()">✕</button>
        <h2 id="don-modal-title">Nouveau don</h2>
        <div id="don-alert" class="form-alert" style="display:none;"></div>
        <div id="don-image-section" style="margin-bottom:1.25rem;">
            <label class="form-label-section">📸 Photo du don <span style="font-size:.78rem;color:#64748b;font-weight:400;">(vérification alimentaire par IA)</span></label>
            <div id="don-dropzone" onclick="document.getElementById('don-image-input').click()" ondragover="event.preventDefault();this.style.borderColor='#0f766e';" ondragleave="this.style.borderColor='#14b8a6';" ondrop="handleDonImageDrop(event)" style="margin-top:8px;border:2px dashed #14b8a6;border-radius:14px;padding:20px;text-align:center;cursor:pointer;background:#f0fdfa;transition:all .2s;">
                <div id="don-dropzone-content">
                    <div style="font-size:2rem;">📁</div>
                    <div style="font-size:.85rem;color:#0f766e;font-weight:600;margin-top:4px;">Cliquez ou glissez une image ici</div>
                    <div style="font-size:.75rem;color:#64748b;margin-top:2px;">JPEG, PNG, WebP — max 5 Mo</div>
                </div>
                <img id="don-image-preview" src="" alt="" style="display:none;max-height:140px;border-radius:10px;margin-top:8px;">
            </div>
            <input type="file" id="don-image-input" accept="image/*" style="display:none;" onchange="handleDonImageSelect(this.files[0])">
            <div id="don-ai-badge" style="display:none;margin-top:10px;padding:10px 14px;border-radius:12px;font-size:.85rem;font-weight:600;"></div>
        </div>
        <div style="margin-bottom:1rem;">
            <label class="form-label-section">Produits du don *</label>
            <div id="produits-lines" style="margin-top:.5rem;display:flex;flex-direction:column;gap:8px;"></div>
            <button type="button" onclick="addProduitLine()" style="margin-top:8px;background:none;border:1.5px dashed #14b8a6;color:#0f766e;border-radius:10px;padding:7px 16px;font-size:.82rem;font-weight:600;cursor:pointer;width:100%;">+ Ajouter un produit</button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Statut</label>
                <select id="don-statut" class="form-input-select">
                    <option value="disponible">Disponible</option>
                    <option value="réservé">Réservé</option>
                    <option value="récupéré">Récupéré</option>
                    <option value="périmé">⚠️ Périmé</option>
                </select>
            </div>
            <div class="form-group">
                <label>Partenaire bénéficiaire</label>
                <select id="don-partenaire" class="form-input-select">
                    <option value="">— Aucun —</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-danger" id="don-btn-delete" style="display:none;margin-right:auto;" onclick="deleteDon()">🗑️ Supprimer</button>
            <button class="btn-cancel" onclick="closeDonModal()">Annuler</button>
            <button class="primary-btn" id="don-btn-save" style="padding:10px 24px;" onclick="saveDon()">Enregistrer</button>
        </div>
    </div>
</div>

<!-- ═══ MODAL PARTENAIRE ═══ -->
<div class="form-modal-overlay" id="partModal">
    <div class="form-modal">
        <button class="modal-close" onclick="closePartModal()">✕</button>
        <h2 id="part-modal-title">Nouveau partenaire</h2>
        <div id="part-alert" class="form-alert" style="display:none;"></div>
        <div class="form-row">
            <div class="form-group"><label>Nom *</label><input type="text" id="part-nom" placeholder="Nom de l'organisation"></div>
            <div class="form-group"><label>Type *</label>
                <select id="part-type" class="form-input-select">
                    <option value="association">Association</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="épicerie">Épicerie</option>
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:1rem;"><label>Adresse</label><input type="text" id="part-adresse" placeholder="Adresse complète"></div>
        <div class="form-row">
            <div class="form-group"><label>Téléphone</label><input type="text" id="part-tel" placeholder="+216..."></div>
            <div class="form-group"><label>Email</label><input type="email" id="part-email" placeholder="contact@..."></div>
        </div>
        <div class="form-actions">
            <button class="btn-danger" id="part-btn-delete" style="display:none;margin-right:auto;" onclick="deletePartenaire()">🗑️ Supprimer</button>
            <button class="btn-cancel" onclick="closePartModal()">Annuler</button>
            <button class="primary-btn" style="padding:10px 24px;" onclick="savePartenaire()">Enregistrer</button>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMATION SUPPRESSION -->
<div class="form-modal-overlay" id="confirmDonModal">
    <div class="confirm-modal">
        <div style="font-size:2.5rem;margin-bottom:.75rem;">🗑️</div>
        <h3>Supprimer ce don ?</h3>
        <p>Cette action est irréversible. Tous les produits associés seront supprimés.</p>
        <div style="display:flex;gap:.75rem;justify-content:center;">
            <button class="btn-cancel" onclick="closeConfirmDon()">Annuler</button>
            <button class="btn-danger" onclick="confirmDeleteDon()">Supprimer</button>
        </div>
    </div>
</div>

<div class="toast" id="adminToast"></div>
<script src="/Green-Bite/View/assets/dons.js"></script>
</body>
</html>
