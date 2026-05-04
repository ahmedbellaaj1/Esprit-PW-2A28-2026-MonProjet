<?php
// views/dons/index.php  — Vue admin : liste des dons
$pageTitle = 'Gestion des dons';
$extraJs   = '/' . basename(dirname(__DIR__, 2)) . '/views/assets/dons.js';
require_once __DIR__ . '/../layout_header.php';
?>

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="dashboard-main">

    <!-- HEADER -->
    <header class="dashboard-header">
      <div class="header-title">🎁 Gestion des dons alimentaires</div>
      <div class="header-right">
        <span class="header-badge">🟢 En ligne</span>
        <div class="admin-avatar">AB</div>
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

      <!-- ── TAB DONS ── -->
      <div id="don-tab-liste">
        <div class="table-container">
          <div class="table-toolbar">
            <div class="table-search">
              <span>🔍</span>
              <input type="text" id="don-search" placeholder="Rechercher par produit..." oninput="loadDons()"/>
            </div>
            <div style="display:flex;gap:0.75rem;align-items:center;">
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

      <!-- ── TAB PARTENAIRES ── -->
      <div id="don-tab-partenaires" style="display:none;">
        <div class="table-container">
          <div class="table-toolbar">
            <div class="table-search">
              <span>🔍</span>
              <input type="text" id="part-search" placeholder="Rechercher un partenaire..." oninput="loadPartenaires()"/>
            </div>
            <div style="display:flex;gap:0.75rem;align-items:center;">
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

    <div style="margin-bottom:1rem;">
      <label class="form-label-section">Produits du don *</label>
      <div id="produits-lines" style="margin-top:0.5rem;display:flex;flex-direction:column;gap:8px;"></div>
      <button type="button" onclick="addProduitLine()"
              style="margin-top:8px;background:none;border:1.5px dashed #14b8a6;color:#0f766e;border-radius:10px;padding:7px 16px;font-size:0.82rem;font-weight:600;cursor:pointer;width:100%;">
        + Ajouter un produit
      </button>
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
      <button class="primary-btn" style="padding:10px 24px;" onclick="saveDon()">Enregistrer</button>
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
      <div class="form-group">
        <label>Nom *</label>
        <input type="text" id="part-nom" placeholder="Nom de l'organisation"/>
      </div>
      <div class="form-group">
        <label>Type *</label>
        <select id="part-type" class="form-input-select">
          <option value="association">Association</option>
          <option value="restaurant">Restaurant</option>
          <option value="épicerie">Épicerie</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Adresse</label>
      <input type="text" id="part-adresse" placeholder="Adresse complète"/>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Téléphone</label>
        <input type="text" id="part-tel" placeholder="+216..."/>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="part-email" placeholder="contact@..."/>
      </div>
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
    <div style="font-size:2.5rem;margin-bottom:0.75rem;">🗑️</div>
    <h3>Supprimer ce don ?</h3>
    <p>Cette action est irréversible. Tous les produits associés seront supprimés.</p>
    <div style="display:flex;gap:0.75rem;justify-content:center;">
      <button class="btn-cancel" onclick="closeConfirmDon()">Annuler</button>
      <button class="btn-danger" onclick="confirmDeleteDon()">Supprimer</button>
    </div>
  </div>
</div>

<div class="toast" id="adminToast"></div>
<?php require_once __DIR__ . '/../layout_footer.php'; ?>