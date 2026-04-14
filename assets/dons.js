/* ================================================================
   assets/dons.js
   Vue JS — appels vers les Controllers via API REST
   ================================================================ */

const APP_BASE = `/${window.location.pathname.split('/').filter(Boolean)[0] || ''}`;
const API_DONS = `${APP_BASE}/api/dons.php`;
const API_PARTENAIRES = `${APP_BASE}/api/partenaires.php`;

let editingDonId  = null;
let editingPartId = null;
let pendingDeleteDonId = null;
let frontDonFilter = '';

/* ================================================================
   UTILITAIRES
   ================================================================ */
function statutBadge(s) {
  const styles = {
    disponible: 'background:#dcfce7;color:#166534;',
    réservé:    'background:#fef9c3;color:#854d0e;',
    récupéré:   'background:#ccfbf1;color:#0f766e;'
  };
  return `<span style="display:inline-flex;padding:3px 10px;border-radius:9999px;font-size:0.78rem;font-weight:600;${styles[s]||''}">${s}</span>`;
}

function typeBadge(t) {
  const styles = {
    association: 'background:#ccfbf1;color:#0f766e;',
    restaurant:  'background:#fef9c3;color:#854d0e;',
    épicerie:    'background:#dcfce7;color:#166534;'
  };
  const icons = { association:'🤝', restaurant:'🍽', épicerie:'🛒' };
  return `<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:0.78rem;font-weight:600;${styles[t]||''}">${icons[t]||''} ${t}</span>`;
}

function minDate(produits) {
  if (!produits || !produits.length) return '—';
  return produits.map(p => p.date_peremption).sort()[0];
}

function totalQte(produits) {
  if (!produits) return 0;
  return produits.reduce((s, p) => s + parseInt(p.quantite), 0);
}

function resumeProduits(produits) {
  if (!produits || !produits.length) return '—';
  const noms = produits.map(p => p.nom_produit);
  if (noms.length <= 2) return noms.join(', ');
  return noms.slice(0, 2).join(', ') + ` +${noms.length - 2}`;
}

function toast(msg, elId = 'adminToast') {
  const el = document.getElementById(elId) || document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(el._t);
  el._t = setTimeout(() => el.classList.remove('show'), 3500);
}

function showAlert(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.style.display = '';
}
function hideAlert(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'none';
}


/* ================================================================
   STATS — appel Controller GET ?action=stats
   ================================================================ */
async function loadDonStats() {
  try {
    const res  = await fetch(`${API_DONS}?action=stats`);
    const json = await res.json();
    if (!json.success) return;
    const s  = json.data;
    const el = document.getElementById('don-stats');
    if (!el) return;
    el.innerHTML = `
      <div class="metric-card">
        <div class="metric-icon icon-teal">🎁</div>
        <div class="metric-value">${s.total}</div>
        <div class="metric-label">Total dons</div>
      </div>
      <div class="metric-card">
        <div class="metric-icon icon-blue">✅</div>
        <div class="metric-value" style="color:#16a34a;">${s.disponible}</div>
        <div class="metric-label">Disponibles</div>
      </div>
      <div class="metric-card">
        <div class="metric-icon icon-amber">🔒</div>
        <div class="metric-value" style="color:#ca8a04;">${s['réservé']}</div>
        <div class="metric-label">Réservés</div>
      </div>
      <div class="metric-card">
        <div class="metric-icon icon-red">📦</div>
        <div class="metric-value" style="color:#0f766e;">${s['récupéré']}</div>
        <div class="metric-label">Récupérés</div>
        <div class="metric-trend trend-up">Taux : ${s.total ? Math.round(s['récupéré']/s.total*100) : 0}%</div>
      </div>`;
  } catch (e) { console.error(e); }
}


/* ================================================================
   LISTE DES DONS — appel Controller GET
   ================================================================ */
async function loadDons() {
  const search = document.getElementById('don-search')?.value || '';
  const statut = document.getElementById('don-filter-statut')?.value || '';
  const params = new URLSearchParams();
  if (search) params.append('search', search);
  if (statut) params.append('statut', statut);

  try {
    const res  = await fetch(`${API_DONS}?${params}`);
    const json = await res.json();
    renderDonsTable(json.success ? json.data : []);
  } catch (e) {
    renderDonsTable([]);
  }
}

function renderDonsTable(dons) {
  const tbody = document.getElementById('don-tbody');
  if (!tbody) return;
  if (!dons.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Aucun don trouvé.</td></tr>`;
    return;
  }
  tbody.innerHTML = dons.map(d => `
    <tr>
      <td>
        <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">${resumeProduits(d.produits)}</div>
        <div style="font-size:0.75rem;color:#94a3b8;">${(d.produits||[]).length} produit(s)</div>
      </td>
      <td style="font-size:0.875rem;">${totalQte(d.produits)} unités</td>
      <td style="font-size:0.875rem;color:#64748b;">${minDate(d.produits)}</td>
      <td>${statutBadge(d.statut)}</td>
      <td style="font-size:0.875rem;color:#374151;">${d.partenaire_nom || '—'}</td>
      <td style="font-size:0.82rem;color:#94a3b8;">${(d.date_publication||'').split(' ')[0]}</td>
      <td>
        <button class="btn-icon btn-edit" onclick='openDonModal(${JSON.stringify(d)})' title="Modifier">✏️</button>
        <button class="btn-icon btn-del"  onclick="askDeleteDon(${d.id_don})" title="Supprimer">🗑️</button>
      </td>
    </tr>`).join('');
}


/* ================================================================
   MODAL DON — OUVRIR / FERMER
   ================================================================ */
async function openDonModal(donData = null) {
  editingDonId = donData ? donData.id_don : null;
  hideAlert('don-alert');

  // Charger les partenaires dans le select
  try {
    const res  = await fetch(API_PARTENAIRES);
    const json = await res.json();
    const sel  = document.getElementById('don-partenaire');
    sel.innerHTML = '<option value="">— Aucun —</option>' +
      (json.data || []).map(p => `<option value="${p.id_partenaire}">${p.nom}</option>`).join('');
    if (donData) sel.value = donData.id_partenaire || '';
  } catch (e) {}

  if (donData) {
    document.getElementById('don-modal-title').textContent = 'Modifier le don';
    document.getElementById('don-statut').value = donData.statut;
    document.getElementById('don-btn-delete').style.display = '';
    renderProduitLines(donData.produits || []);
  } else {
    document.getElementById('don-modal-title').textContent = 'Nouveau don';
    document.getElementById('don-statut').value = 'disponible';
    document.getElementById('don-btn-delete').style.display = 'none';
    renderProduitLines([{ nom_produit:'', quantite:'', date_peremption:'' }]);
  }

  document.getElementById('donModal').classList.add('open');
}

function closeDonModal() {
  document.getElementById('donModal').classList.remove('open');
}

/* Lignes produits dynamiques */
function renderProduitLines(produits) {
  const c = document.getElementById('produits-lines');
  if (!c) return;
  c.innerHTML = '';
  produits.forEach(p => addProduitLine(p));
}

function addProduitLine(data = {}) {
  const c = document.getElementById('produits-lines');
  if (!c) return;
  const div = document.createElement('div');
  div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:center;';
  div.innerHTML = `
    <input type="text"   class="pl-nom"  placeholder="Nom du produit *" value="${data.nom_produit||''}"
           style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.85rem;font-family:'Inter',sans-serif;"/>
    <input type="number" class="pl-qty"  placeholder="Qté *" value="${data.quantite||''}" min="1"
           style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.85rem;font-family:'Inter',sans-serif;"/>
    <input type="date"   class="pl-date" value="${data.date_peremption||''}"
           style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.85rem;font-family:'Inter',sans-serif;"/>
    <button type="button" onclick="removeProduitLine(this)"
            style="background:#fef2f2;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:#dc2626;font-size:1rem;">✕</button>`;
  c.appendChild(div);
}

function removeProduitLine(btn) {
  const c = document.getElementById('produits-lines');
  if (c && c.children.length > 1) btn.parentElement.remove();
  else showAlert('don-alert', 'Un don doit contenir au moins un produit.');
}

function collectProduits() {
  const lines = document.querySelectorAll('#produits-lines > div');
  const produits = [];
  let valid = true;
  lines.forEach(l => {
    const nom  = l.querySelector('.pl-nom').value.trim();
    const qty  = parseInt(l.querySelector('.pl-qty').value);
    const date = l.querySelector('.pl-date').value;
    if (!nom || !qty || !date) { valid = false; return; }
    produits.push({ nom_produit: nom, quantite: qty, date_peremption: date });
  });
  return valid ? produits : null;
}


/* ================================================================
   SAVE DON — appel Controller POST ou PUT
   ================================================================ */
async function saveDon() {
  const produits = collectProduits();
  if (!produits || !produits.length) {
    showAlert('don-alert', 'Remplissez tous les champs produits (nom, quantité, date).'); return;
  }

  const body = {
    statut:        document.getElementById('don-statut').value,
    id_partenaire: document.getElementById('don-partenaire').value || null,
    id_user:       1,   // ← remplacer par session utilisateur connecté
    produits
  };

  try {
    let res;
    if (editingDonId) {
      // PUT → DonController::update()
      res = await fetch(`${API_DONS}?id=${editingDonId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
    } else {
      // POST → DonController::store()
      res = await fetch(API_DONS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
    }
    const json = await res.json();
    if (!json.success) { showAlert('don-alert', json.error || 'Erreur'); return; }

    toast(editingDonId ? '✅ Don modifié !' : '✅ Don publié !');
    closeDonModal();
    loadDons();
    loadDonStats();
  } catch (e) {
    showAlert('don-alert', 'Impossible de contacter le serveur.');
  }
}


/* ================================================================
   DELETE DON — appel Controller DELETE
   ================================================================ */
function askDeleteDon(id) {
  pendingDeleteDonId = id;
  const m = document.getElementById('confirmDonModal');
  if (m) m.classList.add('open');
}

function closeConfirmDon() {
  pendingDeleteDonId = null;
  const m = document.getElementById('confirmDonModal');
  if (m) m.classList.remove('open');
}

async function confirmDeleteDon() {
  if (!pendingDeleteDonId) return;
  try {
    const res  = await fetch(`${API_DONS}?id=${pendingDeleteDonId}`, { method: 'DELETE' });
    const json = await res.json();
    if (!json.success) { toast('❌ ' + (json.error || 'Erreur')); return; }
    toast('🗑️ Don supprimé.');
    closeConfirmDon();
    loadDons();
    loadDonStats();
  } catch (e) { toast('❌ Erreur serveur.'); }
}

async function deleteDon() {
  if (!editingDonId) return;
  if (!confirm('Supprimer ce don ?')) return;
  try {
    const res  = await fetch(`${API_DONS}?id=${editingDonId}`, { method: 'DELETE' });
    const json = await res.json();
    if (!json.success) { showAlert('don-alert', json.error || 'Erreur'); return; }
    toast('🗑️ Don supprimé.');
    closeDonModal();
    loadDons();
    loadDonStats();
  } catch (e) { showAlert('don-alert', 'Erreur serveur.'); }
}


/* ================================================================
   LISTE DES PARTENAIRES — appel Controller GET
   ================================================================ */
async function loadPartenaires() {
  const search = document.getElementById('part-search')?.value || '';
  const type   = document.getElementById('part-filter-type')?.value || '';
  const params = new URLSearchParams();
  if (search) params.append('search', search);
  if (type)   params.append('type', type);

  try {
    const res  = await fetch(`${API_PARTENAIRES}?${params}`);
    const json = await res.json();
    renderPartenairesTable(json.success ? json.data : []);
  } catch (e) { renderPartenairesTable([]); }
}

function renderPartenairesTable(parts) {
  const tbody = document.getElementById('part-tbody');
  if (!tbody) return;
  if (!parts.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Aucun partenaire trouvé.</td></tr>`;
    return;
  }
  tbody.innerHTML = parts.map(p => `
    <tr>
      <td style="font-weight:600;font-size:0.875rem;color:#0f172a;">${p.nom}</td>
      <td>${typeBadge(p.type)}</td>
      <td style="font-size:0.82rem;color:#374151;">${p.adresse||'—'}</td>
      <td style="font-size:0.82rem;">${p.telephone||'—'}</td>
      <td style="font-size:0.82rem;color:#0f766e;">${p.email||'—'}</td>
      <td style="text-align:center;font-size:0.875rem;">${p.nb_dons||0}</td>
      <td>
        <button class="btn-icon btn-edit" onclick='openPartModal(${JSON.stringify(p)})' title="Modifier">✏️</button>
        <button class="btn-icon btn-del"  onclick="deletePartenaire(${p.id_partenaire})" title="Supprimer">🗑️</button>
      </td>
    </tr>`).join('');
}


/* ================================================================
   MODAL PARTENAIRE — OUVRIR / FERMER
   ================================================================ */
function openPartModal(partData = null) {
  editingPartId = partData ? partData.id_partenaire : null;
  hideAlert('part-alert');

  if (partData) {
    document.getElementById('part-modal-title').textContent = 'Modifier le partenaire';
    document.getElementById('part-nom').value     = partData.nom;
    document.getElementById('part-type').value    = partData.type;
    document.getElementById('part-adresse').value = partData.adresse || '';
    document.getElementById('part-tel').value     = partData.telephone || '';
    document.getElementById('part-email').value   = partData.email || '';
    document.getElementById('part-btn-delete').style.display = '';
  } else {
    document.getElementById('part-modal-title').textContent = 'Nouveau partenaire';
    ['part-nom','part-adresse','part-tel','part-email'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('part-type').value = 'association';
    document.getElementById('part-btn-delete').style.display = 'none';
  }

  document.getElementById('partModal').classList.add('open');
}

function closePartModal() {
  document.getElementById('partModal').classList.remove('open');
}


/* ================================================================
   SAVE PARTENAIRE — appel Controller POST ou PUT
   ================================================================ */
async function savePartenaire() {
  const nom = document.getElementById('part-nom').value.trim();
  if (!nom) { showAlert('part-alert', 'Le nom est obligatoire.'); return; }

  const body = {
    nom,
    type:      document.getElementById('part-type').value,
    adresse:   document.getElementById('part-adresse').value.trim(),
    telephone: document.getElementById('part-tel').value.trim(),
    email:     document.getElementById('part-email').value.trim()
  };

  try {
    let res;
    if (editingPartId) {
      // PUT → PartenaireController::update()
      res = await fetch(`${API_PARTENAIRES}?id=${editingPartId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
    } else {
      // POST → PartenaireController::store()
      res = await fetch(API_PARTENAIRES, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
    }
    const json = await res.json();
    if (!json.success) { showAlert('part-alert', json.error || 'Erreur'); return; }

    toast(editingPartId ? '✅ Partenaire modifié !' : '✅ Partenaire ajouté !');
    closePartModal();
    loadPartenaires();
  } catch (e) {
    showAlert('part-alert', 'Impossible de contacter le serveur.');
  }
}

/* ================================================================
   DELETE PARTENAIRE — appel Controller DELETE
   ================================================================ */
async function deletePartenaire(id) {
  const targetId = id ?? editingPartId;
  if (!targetId) { toast('❌ Partenaire introuvable.'); return; }
  if (!confirm('Supprimer ce partenaire ?')) return;
  try {
    const res  = await fetch(`${API_PARTENAIRES}?id=${targetId}`, { method: 'DELETE' });
    const json = await res.json();
    if (!json.success) { toast('❌ ' + (json.error || 'Erreur')); return; }
    toast('🗑️ Partenaire supprimé.');
    if (editingPartId === targetId) closePartModal();
    loadPartenaires();
  } catch (e) { toast('❌ Erreur serveur.'); }
}


/* ================================================================
   TABS ADMIN
   ================================================================ */
function switchDonTab(btn, tab) {
  document.querySelectorAll('.tabs .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const tabs = { liste: 'don-tab-liste', partenaires: 'don-tab-partenaires' };
  Object.entries(tabs).forEach(([k, id]) => {
    const el = document.getElementById(id);
    if (el) el.style.display = k === tab ? '' : 'none';
  });
  if (tab === 'partenaires') loadPartenaires();
}


/* ================================================================
   FRONT OFFICE — vue publique
   ================================================================ */
frontDonFilter = '';

function setFrontDonFilter(btn, val) {
  document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  frontDonFilter = val;
  loadFrontDons();
}

async function loadFrontDons() {
  const search = document.getElementById('front-don-search')?.value || '';
  const params = new URLSearchParams();
  if (frontDonFilter) params.append('statut', frontDonFilter);
  if (search)         params.append('search', search);

  try {
    const res  = await fetch(`${API_DONS}?${params}`);
    const json = await res.json();
    renderFrontDons(json.success ? json.data : []);
    renderFrontStats(json.success ? json.data : []);
  } catch (e) { renderFrontDons([]); }
}

function renderFrontStats(dons) {
  const el = document.getElementById('front-don-stats');
  if (!el) return;
  const total = dons.length;
  const dispo = dons.filter(d => d.statut === 'disponible').length;
  const rec   = dons.filter(d => d.statut === 'récupéré').length;
  el.innerHTML = `
    <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">🎁</div><div class="metric-value">${total}</div><div class="metric-label">Total</div></div>
    <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">✅</div><div class="metric-value" style="color:#16a34a;">${dispo}</div><div class="metric-label">Disponibles</div></div>
    <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">📦</div><div class="metric-value" style="color:#0f766e;">${rec}</div><div class="metric-label">Récupérés</div></div>`;
}

function renderFrontDons(dons) {
  const grid  = document.getElementById('front-dons-grid');
  const count = document.getElementById('front-don-count');
  if (!grid) return;
  if (count) count.textContent = `(${dons.length} don${dons.length > 1 ? 's' : ''})`;

  if (!dons.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#64748b;">Aucun don trouvé.</div>`;
    return;
  }

  const icons = { association:'🤝', restaurant:'🍽', épicerie:'🛒' };
  grid.innerHTML = dons.map(d => {
    const produitsHtml = (d.produits||[]).slice(0,3).map(p =>
      `<span style="display:inline-block;background:#f0fdf4;color:#166534;border-radius:9999px;padding:2px 10px;font-size:0.75rem;margin:2px;">${p.nom_produit} ×${p.quantite}</span>`
    ).join('') + ((d.produits||[]).length > 3 ? `<span style="font-size:0.75rem;color:#94a3b8;"> +${d.produits.length-3}</span>` : '');

    const canReserve = d.statut === 'disponible';
    const bgColor = d.statut === 'disponible' ? '#f0fdf4' : d.statut === 'réservé' ? '#fefce8' : '#f0fdfa';

    return `
    <div class="product-card card" style="cursor:default;">
      <div class="product-img" style="background:${bgColor};">
        <span style="font-size:2.5rem;">🎁</span>
        <div style="position:absolute;top:10px;right:10px;">${statutBadge(d.statut)}</div>
      </div>
      <div class="product-body">
        <div class="product-brand">Don #${d.id_don} — ${(d.date_publication||'').split(' ')[0]}</div>
        <div style="margin:6px 0;flex-wrap:wrap;display:flex;gap:2px;">${produitsHtml}</div>
        <div style="font-size:0.82rem;color:#64748b;margin:4px 0;">🗓 Péremption min. : <strong>${minDate(d.produits)}</strong></div>
        <div style="font-size:0.82rem;color:#64748b;margin:4px 0;">📦 Quantité totale : <strong>${totalQte(d.produits)} unités</strong></div>
        ${d.partenaire_nom ? `<div style="font-size:0.82rem;color:#0f766e;margin:4px 0;">${icons[d.partenaire_type]||''} ${d.partenaire_nom}</div>` : ''}
        <div style="margin-top:10px;display:flex;gap:8px;">
          <button onclick="openFrontDonDetail(${JSON.stringify(d).replace(/"/g,'&quot;')})"
                  style="flex:1;background:#f1f5f9;border:none;border-radius:9999px;padding:7px;font-size:0.8rem;cursor:pointer;font-family:'Inter',sans-serif;">
            Voir détails
          </button>
          ${canReserve ? `
          <button onclick="reserverDon(${d.id_don})"
                  style="flex:1;background:#0f766e;color:white;border:none;border-radius:9999px;padding:7px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;">
            Réserver
          </button>` : ''}
        </div>
      </div>
    </div>`;
  }).join('');
}

/* Réservation — appel PUT ?action=statut */
async function reserverDon(id) {
  try {
    const res  = await fetch(`${API_DONS}?action=statut&id=${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ statut: 'réservé' })
    });
    const json = await res.json();
    if (!json.success) { toast('❌ Erreur', 'toast'); return; }
    toast('✅ Don réservé avec succès !', 'toast');
    loadFrontDons();
  } catch (e) { toast('❌ Erreur serveur', 'toast'); }
}

function openFrontDonDetail(d) {
  const icons = { association:'🤝', restaurant:'🍽', épicerie:'🛒' };
  document.getElementById('don-detail-content').innerHTML = `
    <div style="margin-bottom:1rem;">
      <div style="font-size:1.5rem;font-weight:700;color:#0f172a;margin-bottom:4px;">🎁 Don #${d.id_don}</div>
      <div>${statutBadge(d.statut)}</div>
    </div>
    <div style="font-size:0.85rem;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Produits inclus</div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:1.5rem;">
      ${(d.produits||[]).map(p => `
        <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:10px;padding:10px 14px;">
          <div>
            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">${p.nom_produit}</div>
            <div style="font-size:0.78rem;color:#64748b;">Péremption : ${p.date_peremption}</div>
          </div>
          <div style="font-size:0.875rem;font-weight:600;color:#0f766e;">${p.quantite} unités</div>
        </div>`).join('')}
    </div>
    ${d.partenaire_nom ? `
    <div style="font-size:0.85rem;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Partenaire bénéficiaire</div>
    <div style="background:#f0fdfa;border-radius:10px;padding:12px 14px;font-size:0.875rem;">
      <div style="font-weight:600;color:#0f172a;">${icons[d.partenaire_type]||''} ${d.partenaire_nom}</div>
    </div>` : ''}
    <div style="margin-top:1.5rem;font-size:0.82rem;color:#94a3b8;">Publié le ${(d.date_publication||'').split(' ')[0]}</div>`;
  document.getElementById('donDetailModal').classList.add('open');
}


/* ================================================================
   INIT
   ================================================================ */
document.addEventListener('DOMContentLoaded', () => {
  // Admin : chargement initial
  if (document.getElementById('don-tbody'))    { loadDons(); loadDonStats(); }
  // Front : chargement initial
  if (document.getElementById('front-dons-grid')) { loadFrontDons(); }

  // Fermer modals sur clic overlay
  ['donModal','partModal','confirmDonModal','donDetailModal'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
  });
});
