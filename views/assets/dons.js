/* ================================================================
   assets/dons.js - VERSION COMPLETE (admin + front + top partenaires)
   ================================================================ */

// Configuration du chemin - CORRIGÉ
const APP_BASE = '/greenbite_mvc';
const API_DONS = `${APP_BASE}/models/Don.php`;
const API_PARTENAIRES = `${APP_BASE}/models/Partenaire.php`;

console.log('APP_BASE:', APP_BASE);
console.log('API_DONS:', API_DONS);
console.log('API_PARTENAIRES:', API_PARTENAIRES);

let editingDonId = null;
let editingPartId = null;
let pendingDeleteDonId = null;
let frontDonFilter = '';

/* ================================================================
   UTILITAIRES (gardiens)
   ================================================================ */
function statutBadge(s) {
    const styles = {
        disponible: 'background:#dcfce7;color:#166534;',
        réservé: 'background:#fef9c3;color:#854d0e;',
        récupéré: 'background:#ccfbf1;color:#0f766e;',
        périmé: 'background:#fee2e2;color:#991b1b;'
    };
    return `<span style="display:inline-flex;padding:3px 10px;border-radius:9999px;font-size:0.78rem;font-weight:600;${styles[s] || ''}">${s}</span>`;
}

function typeBadge(t) {
    const styles = {
        association: 'background:#ccfbf1;color:#0f766e;',
        restaurant: 'background:#fef9c3;color:#854d0e;',
        épicerie: 'background:#dcfce7;color:#166534;'
    };
    const icons = { association: '🤝', restaurant: '🍽', épicerie: '🛒' };
    return `<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:0.78rem;font-weight:600;${styles[t] || ''}">${icons[t] || ''} ${t}</span>`;
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

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

/* ================================================================
   STATS ADMIN
   ================================================================ */
async function loadDonStats() {
    try {
        const res = await fetch(`${API_DONS}?action=stats`);
        const json = await res.json();
        if (!json.success) return;
        const s = json.data;
        const el = document.getElementById('don-stats');
        if (!el) return;
        el.innerHTML = `
            <div class="metric-card"><div class="metric-icon icon-teal">🎁</div><div class="metric-value">${s.total}</div><div class="metric-label">Total dons</div></div>
            <div class="metric-card"><div class="metric-icon icon-blue">✅</div><div class="metric-value" style="color:#16a34a;">${s.disponible}</div><div class="metric-label">Disponibles</div></div>
            <div class="metric-card"><div class="metric-icon icon-amber">🔒</div><div class="metric-value" style="color:#ca8a04;">${s['réservé']}</div><div class="metric-label">Réservés</div></div>
            <div class="metric-card"><div class="metric-icon icon-red">📦</div><div class="metric-value" style="color:#0f766e;">${s['récupéré']}</div><div class="metric-label">Récupérés</div></div>
            <div class="metric-card"><div class="metric-icon icon-red">⚠️</div><div class="metric-value" style="color:#991b1b;">${s['périmé'] || 0}</div><div class="metric-label">Périmés</div></div>`;
    } catch (e) {
        console.error(e);
    }
}

/* ================================================================
   LISTE DES DONS ADMIN
   ================================================================ */
async function loadDons() {
    const search = document.getElementById('don-search')?.value || '';
    const statut = document.getElementById('don-filter-statut')?.value || '';
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (statut) params.append('statut', statut);

    try {
        const res = await fetch(`${API_DONS}?${params}`);
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
            <td><div style="font-weight:600;font-size:0.875rem;">${resumeProduits(d.produits)}</div><div style="font-size:0.75rem;color:#94a3b8;">${(d.produits || []).length} produit(s)</div></td>
            <td>${totalQte(d.produits)} unités</td>
            <td>${minDate(d.produits)}</td>
            <td>${statutBadge(d.statut)}</td>
            <td>${d.partenaire_nom || '—'}</td>
            <td>${(d.date_publication || '').split(' ')[0]}</td>
            <td><button class="btn-icon btn-edit" onclick='openDonModal(${JSON.stringify(d)})'>✏️</button><button class="btn-icon btn-del" onclick="askDeleteDon(${d.id_don})">🗑️</button></td>
        </tr>`).join('');
}

/* ================================================================
   MODAL DON ADMIN
   ================================================================ */
async function openDonModal(donData = null) {
    editingDonId = donData ? donData.id_don : null;
    hideAlert('don-alert');

    try {
        const res = await fetch(API_PARTENAIRES);
        const json = await res.json();
        const sel = document.getElementById('don-partenaire');
        sel.innerHTML = '<option value="">— Aucun —</option>' + (json.data || []).map(p => `<option value="${p.id_partenaire}">${escapeHtml(p.nom)}</option>`).join('');
        if (donData) sel.value = donData.id_partenaire || '';
        sel.onchange = function () {
            if (this.value && document.getElementById('don-statut').value === 'disponible') {
                document.getElementById('don-statut').value = 'réservé';
            }
        };
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
        renderProduitLines([{ nom_produit: '', quantite: '', date_peremption: '' }]);
    }
    document.getElementById('donModal').classList.add('open');
}

function closeDonModal() {
    document.getElementById('donModal').classList.remove('open');
}

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
        <input type="text" class="pl-nom" placeholder="Nom du produit *" value="${escapeHtml(data.nom_produit || '')}" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <input type="number" class="pl-qty" placeholder="Qté *" value="${data.quantite || ''}" min="1" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <input type="date" class="pl-date" value="${data.date_peremption || ''}" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <button type="button" onclick="removeProduitLine(this)" style="background:#fef2f2;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:#dc2626;">✕</button>`;
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
        const nom = l.querySelector('.pl-nom').value.trim();
        const qty = parseInt(l.querySelector('.pl-qty').value);
        const date = l.querySelector('.pl-date').value;
        if (!nom || !qty || !date) {
            valid = false;
            return;
        }
        produits.push({ nom_produit: nom, quantite: qty, date_peremption: date });
    });
    return valid ? produits : null;
}

async function saveDon() {
    const produits = collectProduits();
    if (!produits || !produits.length) {
        showAlert('don-alert', 'Remplissez tous les champs produits.');
        return;
    }

    const body = {
        statut: document.getElementById('don-statut').value,
        id_partenaire: document.getElementById('don-partenaire').value || null,
        id_user: 1,
        produits
    };

    try {
        let res;
        if (editingDonId) {
            res = await fetch(`${API_DONS}?id=${editingDonId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        } else {
            res = await fetch(API_DONS, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        }
        const json = await res.json();
        if (!json.success) {
            showAlert('don-alert', json.error || 'Erreur');
            return;
        }
        toast(editingDonId ? '✅ Don modifié !' : '✅ Don publié !');
        closeDonModal();
        loadDons();
        loadDonStats();
    } catch (e) {
        showAlert('don-alert', 'Impossible de contacter le serveur.');
    }
}

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
        const res = await fetch(`${API_DONS}?id=${pendingDeleteDonId}`, { method: 'DELETE' });
        const json = await res.json();
        if (!json.success) {
            toast('❌ ' + (json.error || 'Erreur'));
            return;
        }
        toast('🗑️ Don supprimé.');
        closeConfirmDon();
        loadDons();
        loadDonStats();
    } catch (e) {
        toast('❌ Erreur serveur.');
    }
}

async function deleteDon() {
    if (!editingDonId) return;
    if (!confirm('Supprimer ce don ?')) return;
    try {
        const res = await fetch(`${API_DONS}?id=${editingDonId}`, { method: 'DELETE' });
        const json = await res.json();
        if (!json.success) {
            showAlert('don-alert', json.error || 'Erreur');
            return;
        }
        toast('🗑️ Don supprimé.');
        closeDonModal();
        loadDons();
        loadDonStats();
    } catch (e) {
        showAlert('don-alert', 'Erreur serveur.');
    }
}

/* ================================================================
   LISTE DES PARTENAIRES ADMIN
   ================================================================ */
async function loadPartenaires() {
    const search = document.getElementById('part-search')?.value || '';
    const type = document.getElementById('part-filter-type')?.value || '';
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (type) params.append('type', type);

    try {
        const res = await fetch(`${API_PARTENAIRES}?${params}`);
        const json = await res.json();
        renderPartenairesTable(json.success ? json.data : []);
    } catch (e) {
        renderPartenairesTable([]);
    }
}

function renderPartenairesTable(parts) {
    const tbody = document.getElementById('part-tbody');
    if (!tbody) return;
    if (!parts.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">Aucun partenaire trouvé.</td></tr>`;
        return;
    }
    tbody.innerHTML = parts.map(p => `
        <tr>
            <td style="font-weight:600;">${escapeHtml(p.nom)}</td>
            <td>${typeBadge(p.type)}</td>
            <td>${escapeHtml(p.adresse || '—')}</td>
            <td>${escapeHtml(p.telephone || '—')}</td>
            <td>${escapeHtml(p.email || '—')}</td>
            <td style="text-align:center;">${p.nb_dons || 0}</td>
            <td><button class="btn-icon btn-edit" onclick='openPartModal(${JSON.stringify(p)})'>✏️</button><button class="btn-icon btn-del" onclick="deletePartenaire(${p.id_partenaire})">🗑️</button></td>
        </tr>`).join('');
}

function openPartModal(partData = null) {
    editingPartId = partData ? partData.id_partenaire : null;
    hideAlert('part-alert');

    if (partData) {
        document.getElementById('part-modal-title').textContent = 'Modifier le partenaire';
        document.getElementById('part-nom').value = partData.nom;
        document.getElementById('part-type').value = partData.type;
        document.getElementById('part-adresse').value = partData.adresse || '';
        document.getElementById('part-tel').value = partData.telephone || '';
        document.getElementById('part-email').value = partData.email || '';
        document.getElementById('part-btn-delete').style.display = '';
    } else {
        document.getElementById('part-modal-title').textContent = 'Nouveau partenaire';
        ['part-nom', 'part-adresse', 'part-tel', 'part-email'].forEach(id => { document.getElementById(id).value = ''; });
        document.getElementById('part-type').value = 'association';
        document.getElementById('part-btn-delete').style.display = 'none';
    }
    document.getElementById('partModal').classList.add('open');
}

function closePartModal() {
    document.getElementById('partModal').classList.remove('open');
}

async function savePartenaire() {
    const nom = document.getElementById('part-nom').value.trim();
    if (!nom) {
        showAlert('part-alert', 'Le nom est obligatoire.');
        return;
    }

    const body = {
        nom: nom,
        type: document.getElementById('part-type').value,
        adresse: document.getElementById('part-adresse').value.trim(),
        telephone: document.getElementById('part-tel').value.trim(),
        email: document.getElementById('part-email').value.trim()
    };

    try {
        let res;
        if (editingPartId) {
            res = await fetch(`${API_PARTENAIRES}?id=${editingPartId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        } else {
            res = await fetch(API_PARTENAIRES, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        }
        const json = await res.json();
        if (!json.success) {
            showAlert('part-alert', json.error || 'Erreur');
            return;
        }
        toast(editingPartId ? '✅ Partenaire modifié !' : '✅ Partenaire ajouté !');
        closePartModal();
        loadPartenaires();
        loadTopPartenaires();
    } catch (e) {
        showAlert('part-alert', 'Impossible de contacter le serveur.');
    }
}

async function deletePartenaire(id) {
    const targetId = id ?? editingPartId;
    if (!targetId) {
        toast('❌ Partenaire introuvable.');
        return;
    }
    if (!confirm('Supprimer ce partenaire ?')) return;
    try {
        const res = await fetch(`${API_PARTENAIRES}?id=${targetId}`, { method: 'DELETE' });
        const json = await res.json();
        if (!json.success) {
            toast('❌ ' + (json.error || 'Erreur'));
            return;
        }
        toast('🗑️ Partenaire supprimé.');
        if (editingPartId === targetId) closePartModal();
        loadPartenaires();
        loadTopPartenaires();
    } catch (e) {
        toast('❌ Erreur serveur.');
    }
}

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
   FRONT OFFICE
   ================================================================ */
async function loadFrontDons() {
    const search = document.getElementById('front-don-search')?.value || '';
    const params = new URLSearchParams();
    if (frontDonFilter) params.append('statut', frontDonFilter);
    if (search) params.append('search', search);

    try {
        const res = await fetch(`${API_DONS}?${params}`);
        const json = await res.json();
        renderFrontDons(json.success ? json.data : []);
        renderFrontStats(json.success ? json.data : []);
    } catch (e) {
        renderFrontDons([]);
    }
}

function renderFrontStats(dons) {
    const el = document.getElementById('front-don-stats');
    if (!el) return;
    const total = dons.length;
    const dispo = dons.filter(d => d.statut === 'disponible').length;
    const rec = dons.filter(d => d.statut === 'récupéré').length;
    el.innerHTML = `
        <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">🎁</div><div class="metric-value">${total}</div><div class="metric-label">Total</div></div>
        <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">✅</div><div class="metric-value" style="color:#16a34a;">${dispo}</div><div class="metric-label">Disponibles</div></div>
        <div class="metric-card" style="text-align:center;"><div style="font-size:2rem;">📦</div><div class="metric-value" style="color:#0f766e;">${rec}</div><div class="metric-label">Récupérés</div></div>`;
}

function renderFrontDons(dons) {
    const grid = document.getElementById('front-dons-grid');
    const count = document.getElementById('front-don-count');
    if (!grid) return;
    if (count) count.textContent = `(${dons.length} don${dons.length > 1 ? 's' : ''})`;

    if (!dons.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#64748b;">Aucun don trouvé.</div>`;
        return;
    }

    const icons = { association: '🤝', restaurant: '🍽', épicerie: '🛒' };
    grid.innerHTML = dons.map(d => {
        const produitsHtml = (d.produits || []).slice(0, 3).map(p => `<span style="display:inline-block;background:#f0fdf4;color:#166534;border-radius:9999px;padding:2px 10px;font-size:0.75rem;margin:2px;">${escapeHtml(p.nom_produit)} ×${p.quantite}</span>`).join('') + ((d.produits || []).length > 3 ? `<span style="font-size:0.75rem;color:#94a3b8;"> +${d.produits.length - 3}</span>` : '');
        const canReserve = d.statut === 'disponible';
        const bgColor = d.statut === 'disponible' ? '#f0fdf4' : d.statut === 'réservé' ? '#fefce8' : '#f0fdfa';

        return `
            <div class="product-card card" style="cursor:default;">
                <div class="product-img" style="background:${bgColor};"><span style="font-size:2.5rem;">🎁</span><div style="position:absolute;top:10px;right:10px;">${statutBadge(d.statut)}</div></div>
                <div class="product-body">
                    <div class="product-brand">Don #${d.id_don} — ${(d.date_publication || '').split(' ')[0]}</div>
                    <div style="margin:6px 0;flex-wrap:wrap;display:flex;gap:2px;">${produitsHtml}</div>
                    <div style="font-size:0.82rem;color:#64748b;margin:4px 0;">🗓 Péremption min. : <strong>${minDate(d.produits)}</strong></div>
                    <div style="font-size:0.82rem;color:#64748b;margin:4px 0;">📦 Quantité totale : <strong>${totalQte(d.produits)} unités</strong></div>
                    ${d.partenaire_nom ? `<div style="font-size:0.82rem;color:#0f766e;margin:4px 0;">${icons[d.partenaire_type] || ''} ${escapeHtml(d.partenaire_nom)}</div>` : ''}
                    <div style="margin-top:10px;display:flex;gap:8px;">
                        <button onclick="openFrontDonDetail(${JSON.stringify(d).replace(/"/g, '&quot;')})" style="flex:1;background:#f1f5f9;border:none;border-radius:9999px;padding:7px;font-size:0.8rem;cursor:pointer;">Voir détails</button>
                        ${canReserve ? `<button onclick="reserverDon(${d.id_don})" style="flex:1;background:#0f766e;color:white;border:none;border-radius:9999px;padding:7px;font-size:0.8rem;font-weight:600;cursor:pointer;">Réserver</button>` : ''}
                    </div>
                </div>
            </div>`;
    }).join('');
}

function setFrontDonFilter(btn, val) {
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    frontDonFilter = val;
    loadFrontDons();
}

async function reserverDon(id) {
    try {
        const res = await fetch(`${API_DONS}?action=statut&id=${id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ statut: 'réservé' }) });
        const json = await res.json();
        if (!json.success) {
            toast('❌ Erreur', 'toast');
            return;
        }
        toast('✅ Don réservé avec succès !', 'toast');
        loadFrontDons();
        loadTopPartenaires();
    } catch (e) {
        toast('❌ Erreur serveur', 'toast');
    }
}

function openFrontDonDetail(d) {
    const icons = { association: '🤝', restaurant: '🍽', épicerie: '🛒' };
    document.getElementById('don-detail-content').innerHTML = `
        <div style="margin-bottom:1rem;"><div style="font-size:1.5rem;font-weight:700;margin-bottom:4px;">🎁 Don #${d.id_don}</div><div>${statutBadge(d.statut)}</div></div>
        <div style="font-size:0.85rem;font-weight:600;margin-bottom:8px;">Produits inclus</div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:1.5rem;">${(d.produits || []).map(p => `<div style="display:flex;justify-content:space-between;background:#f8fafc;border-radius:10px;padding:10px 14px;"><div><div style="font-weight:600;">${escapeHtml(p.nom_produit)}</div><div style="font-size:0.78rem;color:#64748b;">Péremption : ${p.date_peremption}</div></div><div style="font-weight:600;color:#0f766e;">${p.quantite} unités</div></div>`).join('')}</div>
        ${d.partenaire_nom ? `<div style="font-size:0.85rem;font-weight:600;margin-bottom:8px;">Partenaire bénéficiaire</div><div style="background:#f0fdfa;border-radius:10px;padding:12px;"><div style="font-weight:600;">${icons[d.partenaire_type] || ''} ${escapeHtml(d.partenaire_nom)}</div></div>` : ''}
        <div style="margin-top:1.5rem;font-size:0.82rem;color:#94a3b8;">Publié le ${(d.date_publication || '').split(' ')[0]}</div>`;
    document.getElementById('donDetailModal').classList.add('open');
}

function openFrontCreateDonModal() {
    hideAlert('front-don-create-alert');
    frontRenderProduitLines([{ nom_produit: '', quantite: '', date_peremption: '' }]);
    const modal = document.getElementById('frontDonCreateModal');
    if (modal) modal.classList.add('open');
}

function closeFrontCreateDonModal() {
    const modal = document.getElementById('frontDonCreateModal');
    if (modal) modal.classList.remove('open');
}

function frontRenderProduitLines(produits) {
    const container = document.getElementById('front-produits-lines');
    if (!container) return;
    container.innerHTML = '';
    produits.forEach(p => frontAddProduitLine(p));
}

function frontAddProduitLine(data = {}) {
    const container = document.getElementById('front-produits-lines');
    if (!container) return;
    const div = document.createElement('div');
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:center;';
    div.innerHTML = `
        <input type="text" class="fpl-nom" placeholder="Nom du produit *" value="${escapeHtml(data.nom_produit || '')}" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <input type="number" class="fpl-qty" placeholder="Qté *" value="${data.quantite || ''}" min="1" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <input type="date" class="fpl-date" value="${data.date_peremption || ''}" style="padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:10px;">
        <button type="button" onclick="frontRemoveProduitLine(this)" style="background:#fef2f2;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:#dc2626;">✕</button>`;
    container.appendChild(div);
}

function frontRemoveProduitLine(btn) {
    const container = document.getElementById('front-produits-lines');
    if (container && container.children.length > 1) btn.parentElement.remove();
    else showAlert('front-don-create-alert', 'Un don doit contenir au moins un produit.');
}

function frontCollectProduits() {
    const lines = document.querySelectorAll('#front-produits-lines > div');
    const produits = [];
    let valid = true;
    lines.forEach(line => {
        const nom = line.querySelector('.fpl-nom').value.trim();
        const qty = parseInt(line.querySelector('.fpl-qty').value, 10);
        const date = line.querySelector('.fpl-date').value;
        if (!nom || !qty || !date) {
            valid = false;
            return;
        }
        produits.push({ nom_produit: nom, quantite: qty, date_peremption: date });
    });
    return valid ? produits : null;
}

async function submitFrontDon() {
    const produits = frontCollectProduits();
    if (!produits || !produits.length) {
        showAlert('front-don-create-alert', 'Remplissez tous les champs produits.');
        return;
    }

    const body = { statut: 'disponible', id_user: 1, id_partenaire: null, produits };

    try {
        const res = await fetch(API_DONS, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        const json = await res.json();
        if (!json.success) {
            showAlert('front-don-create-alert', json.error || 'Erreur');
            return;
        }
        toast('✅ Don publié avec succès !', 'toast');
        closeFrontCreateDonModal();
        loadFrontDons();
        loadTopPartenaires();
    } catch (e) {
        showAlert('front-don-create-alert', 'Impossible de contacter le serveur.');
    }
}

/* ================================================================
   TOP PARTENAIRES (NOUVEAU)
   ================================================================ */
async function loadTopPartenaires() {
    const container = document.getElementById('top-partenaires-container');
    if (!container) return;

    try {
        const res = await fetch(`${API_PARTENAIRES}?action=top&limit=5`);
        const json = await res.json();

        if (!json.success || !json.data || !json.data.length) {
            container.innerHTML = '<div style="text-align:center;padding:1rem;color:#94a3b8;">Aucun partenaire</div>';
            return;
        }
        renderTopPartenaires(json.data);
    } catch (e) {
        container.innerHTML = '<div style="text-align:center;padding:1rem;color:#ef4444;">Erreur</div>';
    }
}

function renderTopPartenaires(partenaires) {
    const container = document.getElementById('top-partenaires-container');
    if (!container) return;

    const icons = { association: '🤝', restaurant: '🍽️', épicerie: '🛒' };
    container.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:1rem;">${partenaires.map((p, index) => {
        let medal = index === 0 ? '🥇 ' : index === 1 ? '🥈 ' : index === 2 ? '🥉 ' : '';
        return `<div class="product-card" style="cursor:default;padding:1rem;"><div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><div style="font-size:2rem;">${icons[p.type] || '🏢'}</div><div style="flex:1;"><div style="font-size:1rem;font-weight:700;">${medal}${escapeHtml(p.nom)}</div><div style="font-size:0.75rem;color:#64748b;">${p.type}</div></div><div style="background:#0f766e;color:white;border-radius:9999px;padding:4px 12px;">${p.nb_dons} don${p.nb_dons > 1 ? 's' : ''}</div></div>${p.adresse ? `<div style="font-size:0.75rem;color:#64748b;">📍 ${escapeHtml(p.adresse)}</div>` : ''}${p.telephone ? `<div style="font-size:0.75rem;color:#64748b;">📞 ${escapeHtml(p.telephone)}</div>` : ''}</div>`;
    }).join('')}</div>`;
}

/* ================================================================
   INITIALISATION
   ================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    // Mode admin
    if (document.getElementById('don-tbody')) {
        loadDons();
        loadDonStats();
    }

    // Mode front
    if (document.getElementById('front-dons-grid')) {
        loadFrontDons();
        loadTopPartenaires();  // ← AJOUT ESSENTIEL
    }

    // Fermeture des modals
    ['donModal', 'partModal', 'confirmDonModal', 'donDetailModal', 'frontDonCreateModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
    });
});

// Exports globaux pour les onclick
window.setFrontDonFilter = setFrontDonFilter;
window.openFrontDonDetail = openFrontDonDetail;
window.reserverDon = reserverDon;
window.openFrontCreateDonModal = openFrontCreateDonModal;
window.closeFrontCreateDonModal = closeFrontCreateDonModal;
window.submitFrontDon = submitFrontDon;
window.frontAddProduitLine = frontAddProduitLine;
window.openDonModal = openDonModal;
window.closeDonModal = closeDonModal;
window.saveDon = saveDon;
window.deleteDon = deleteDon;
window.askDeleteDon = askDeleteDon;
window.confirmDeleteDon = confirmDeleteDon;
window.closeConfirmDon = closeConfirmDon;
window.addProduitLine = addProduitLine;
window.removeProduitLine = removeProduitLine;
window.openPartModal = openPartModal;
window.closePartModal = closePartModal;
window.savePartenaire = savePartenaire;
window.deletePartenaire = deletePartenaire;
window.switchDonTab = switchDonTab;
window.loadPartenaires = loadPartenaires;
window.loadTopPartenaires = loadTopPartenaires;