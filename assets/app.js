
const PRODUITS = [
  {
    id: 1, emoji: "🥛", nom: "Yaourt Nature Bio", marque: "Danone Bio",
    categorie: "Produits laitiers", nutriscore: "A", calories: 62,
    proteines: 4.5, glucides: 8.2, lipides: 1.8, fibres: 0,
    code: "3017620422003", statut: "actif",
    tags: ["bio", "local"],
    evaluations: [
      { user: "Rayen S.", note: 5, commentaire: "Excellent goût, très frais !", date: "2025-06-10", recommande: true },
      { user: "Imen B.", note: 4, commentaire: "Bon produit, un peu cher.", date: "2025-06-08", recommande: true }
    ]
  },
  {
    id: 2, emoji: "🌾", nom: "Granola Avoine & Miel", marque: "NaturaCrunch",
    categorie: "Céréales & Pains", nutriscore: "B", calories: 420,
    proteines: 9.2, glucides: 62, lipides: 14, fibres: 6.5,
    code: "5000159484695", statut: "actif",
    tags: ["bio", "vegan"],
    evaluations: [
      { user: "Sami K.", note: 5, commentaire: "Parfait pour le matin !", date: "2025-06-12", recommande: true }
    ]
  },
  {
    id: 3, emoji: "🥤", nom: "Lait d'Avoine Original", marque: "Oatly",
    categorie: "Boissons", nutriscore: "B", calories: 47,
    proteines: 1.0, glucides: 6.7, lipides: 1.5, fibres: 0.8,
    code: "7394376616618", statut: "actif",
    tags: ["vegan", "gluten"],
    evaluations: [
      { user: "Ahmed I.", note: 4, commentaire: "Très bonne alternative végétale.", date: "2025-06-09", recommande: true },
      { user: "Nour M.", note: 3, commentaire: "Goût correct mais prix élevé.", date: "2025-06-07", recommande: false }
    ]
  },
  {
    id: 4, emoji: "🍫", nom: "Chocolat Noir 85%", marque: "Lindt",
    categorie: "Snacks & Biscuits", nutriscore: "C", calories: 598,
    proteines: 12, glucides: 14, lipides: 52, fibres: 10.5,
    code: "4000539402106", statut: "actif",
    tags: ["local"],
    evaluations: []
  },
  {
    id: 5, emoji: "🫙", nom: "Pois Chiches Bio en Conserve", marque: "Jardin Bio",
    categorie: "Conserves", nutriscore: "A", calories: 119,
    proteines: 7.2, glucides: 18, lipides: 2.1, fibres: 5.4,
    code: "3329770001123", statut: "actif",
    tags: ["bio", "vegan", "gluten"],
    evaluations: [
      { user: "Soheib H.", note: 5, commentaire: "Pratique et délicieux !", date: "2025-06-11", recommande: true }
    ]
  },
  {
    id: 6, emoji: "🍞", nom: "Pain Complet Sans Gluten", marque: "Schär",
    categorie: "Céréales & Pains", nutriscore: "B", calories: 249,
    proteines: 4.8, glucides: 44, lipides: 5.5, fibres: 3.2,
    code: "8008698012192", statut: "attente",
    tags: ["gluten"],
    evaluations: []
  },
  {
    id: 7, emoji: "🧃", nom: "Jus de Carotte & Gingembre", marque: "Innocent",
    categorie: "Boissons", nutriscore: "C", calories: 49,
    proteines: 0.8, glucides: 10, lipides: 0.2, fibres: 0.5,
    code: "5038862209090", statut: "actif",
    tags: ["local", "bio"],
    evaluations: [
      { user: "Amen A.", note: 4, commentaire: "Très rafraîchissant !", date: "2025-06-05", recommande: true }
    ]
  },
  {
    id: 8, emoji: "🫚", nom: "Huile d'Olive Extra Vierge", marque: "Rivière d'Or",
    categorie: "Épicerie", nutriscore: "B", calories: 824,
    proteines: 0, glucides: 0, lipides: 91.6, fibres: 0,
    code: "3300543310028", statut: "inactif",
    tags: ["local", "bio"],
    evaluations: []
  }
];

const SIGNALEMENTS = [
  { signaleur: "Karim O.", evaluation: "Sami K. sur Granola", motif: "Contenu inapproprié", date: "2025-06-13", statut: "En attente" },
  { signaleur: "Lina M.", evaluation: "Nour M. sur Lait d'Avoine", motif: "Fausse information", date: "2025-06-11", statut: "Traité" }
];

/* ===================================================================
   UTILITAIRES
   =================================================================== */

function getNutriClass(score) {
  const map = { A: "ns-a", B: "ns-b", C: "ns-c", D: "ns-d", E: "ns-e" };
  return map[score] || "ns-c";
}

function getNutriDesc(score) {
  const map = {
    A: "Qualité nutritionnelle excellente",
    B: "Bonne qualité nutritionnelle",
    C: "Qualité nutritionnelle moyenne",
    D: "Qualité nutritionnelle médiocre",
    E: "Mauvaise qualité nutritionnelle"
  };
  return map[score] || "";
}

function renderStars(note, max = 5) {
  let s = "";
  for (let i = 1; i <= max; i++) {
    s += `<span class="star-icon">${i <= note ? "⭐" : "☆"}</span>`;
  }
  return s;
}

function avgNote(evaluations) {
  if (!evaluations.length) return 0;
  return (evaluations.reduce((a, e) => a + e.note, 0) / evaluations.length).toFixed(1);
}

function showToast(id, msg) {
  const t = document.getElementById(id);
  if (!t) return;
  t.textContent = msg;
  t.classList.add("show");
  setTimeout(() => t.classList.remove("show"), 3000);
}

/* ===================================================================
   FRONT OFFICE
   =================================================================== */

let currentFilter = "all";
let currentProductId = null;
let selectedStars = 0;

function renderProducts(data) {
  const grid = document.getElementById("productsGrid");
  const label = document.getElementById("countLabel");
  if (!grid) return;

  const filtered = data.filter(p => {
    if (currentFilter === "all") return true;
    if (["a","b","c","d","e"].includes(currentFilter))
      return p.nutriscore.toLowerCase() === currentFilter;
    return p.tags.includes(currentFilter);
  });

  if (label) label.textContent = `(${filtered.length} produits)`;

  grid.innerHTML = filtered.map(p => {
    const avg = avgNote(p.evaluations);
    const stars = Array.from({length:5},(_,i)=>
      `<span class="star-icon">${i < Math.round(avg) ? "⭐" : "☆"}</span>`).join("");
    const tags = p.tags.map(t => {
      const map = {bio:"tag-bio",vegan:"tag-vegan",local:"tag-local",gluten:"tag-gluten"};
      const label = {bio:"🌱 Bio",vegan:"🥗 Vegan",local:"📍 Local",gluten:"🌾 S. Gluten"};
      return `<span class="tag ${map[t]}">${label[t]}</span>`;
    }).join("");

    return `
    <div class="product-card card" onclick="openProduct(${p.id})">
      <div class="product-img">
        ${p.emoji}
        <div class="nutriscore ${getNutriClass(p.nutriscore)}">${p.nutriscore}</div>
      </div>
      <div class="product-body">
        <div class="product-brand">${p.marque}</div>
        <div class="product-name">${p.nom}</div>
        <div class="product-cal">${p.calories} kcal / 100g</div>
        <div class="stars">
          ${stars}
          <span class="stars-count">${avg > 0 ? avg + " ("+p.evaluations.length+")" : "Aucun avis"}</span>
        </div>
        <div class="product-tags">${tags}</div>
      </div>
    </div>`;
  }).join("");
}

function setFilter(btn, val) {
  document.querySelectorAll(".filter-pill").forEach(b => b.classList.remove("active"));
  btn.classList.add("active");
  currentFilter = val;
  renderProducts(PRODUITS);
}

function searchProducts() {
  const q = document.getElementById("searchInput")?.value.toLowerCase() || "";
  const filtered = PRODUITS.filter(p =>
    p.nom.toLowerCase().includes(q) || p.marque.toLowerCase().includes(q)
  );
  currentFilter = "all";
  document.querySelectorAll(".filter-pill").forEach(b => b.classList.remove("active"));
  document.querySelector(".filter-pill")?.classList.add("active");

  const grid = document.getElementById("productsGrid");
  if (!grid) return;
  if (!filtered.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#64748b;">
      Aucun produit trouvé pour "<strong>${q}</strong>"</div>`;
    return;
  }
  const tempFilter = currentFilter;
  currentFilter = "all";
  renderProducts(filtered);
  currentFilter = tempFilter;
}

// Écoute Enter dans la barre de recherche
document.addEventListener("DOMContentLoaded", () => {
  const si = document.getElementById("searchInput");
  if (si) si.addEventListener("keydown", e => { if (e.key === "Enter") searchProducts(); });
  renderProducts(PRODUITS);
  renderAdminTables();
  renderCharts();
});

function openProduct(id) {
  const p = PRODUITS.find(x => x.id === id);
  if (!p) return;
  currentProductId = id;
  selectedStars = 0;

  document.getElementById("modalEmoji").textContent = p.emoji;
  document.getElementById("modalBrand").textContent = p.marque;
  document.getElementById("modalName").textContent = p.nom;

  const avg = avgNote(p.evaluations);
  document.getElementById("modalStars").innerHTML =
    renderStars(Math.round(avg)) +
    `<span class="stars-count" style="margin-left:6px;">${avg > 0 ? avg+" / 5" : "Aucun avis"}</span>`;

  const tagMap = {bio:"tag-bio",vegan:"tag-vegan",local:"tag-local",gluten:"tag-gluten"};
  const tagLabel = {bio:"🌱 Bio",vegan:"🥗 Vegan",local:"📍 Local",gluten:"🌾 S. Gluten"};
  document.getElementById("modalTags").innerHTML =
    p.tags.map(t => `<span class="tag ${tagMap[t]}">${tagLabel[t]}</span>`).join("");

  document.getElementById("nutriGrid").innerHTML = `
    <div class="nutri-item"><label>Calories</label><span class="value">${p.calories}</span> <span class="unit">kcal</span></div>
    <div class="nutri-item"><label>Protéines</label><span class="value">${p.proteines}</span> <span class="unit">g</span></div>
    <div class="nutri-item"><label>Glucides</label><span class="value">${p.glucides}</span> <span class="unit">g</span></div>
    <div class="nutri-item"><label>Lipides</label><span class="value">${p.lipides}</span> <span class="unit">g</span></div>
  `;

  const ns = document.getElementById("modalNutriscore");
  ns.textContent = p.nutriscore;
  ns.className = `nutriscore ${getNutriClass(p.nutriscore)}`;
  document.getElementById("nutriDesc").textContent = getNutriDesc(p.nutriscore);

  renderReviews(p.evaluations);
  resetStars();

  document.getElementById("productModal").classList.add("open");
  document.body.style.overflow = "hidden";
}

function renderReviews(evaluations) {
  const list = document.getElementById("reviewsList");
  if (!evaluations.length) {
    list.innerHTML = `<p style="color:#94a3b8;font-size:0.88rem;font-style:italic;">Soyez le premier à évaluer ce produit !</p>`;
    return;
  }
  list.innerHTML = evaluations.map(e => `
    <div style="padding:0.85rem;background:#f8fafc;border-radius:12px;margin-bottom:0.75rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
        <div style="display:flex;align-items:center;gap:0.6rem;">
          <div style="width:30px;height:30px;border-radius:50%;background:#ccfbf1;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:#0f766e;">
            ${e.user.split(" ").map(w=>w[0]).join("")}
          </div>
          <strong style="font-size:0.875rem;color:#0f172a;">${e.user}</strong>
        </div>
        <span style="font-size:0.75rem;color:#94a3b8;">${e.date}</span>
      </div>
      <div style="display:flex;gap:2px;margin-bottom:0.3rem;">${renderStars(e.note)}</div>
      <p style="font-size:0.85rem;color:#374151;margin:0;">${e.commentaire}</p>
      ${e.recommande ? '<div style="font-size:0.75rem;color:#16a34a;margin-top:0.3rem;">✅ Recommande ce produit</div>' : ''}
    </div>
  `).join("");
}

function setStars(val) {
  selectedStars = val;
  const btns = document.querySelectorAll("#starRating .star-btn");
  btns.forEach((b, i) => { b.textContent = i < val ? "⭐" : "☆"; });
}

function resetStars() {
  selectedStars = 0;
  document.querySelectorAll("#starRating .star-btn").forEach(b => b.textContent = "☆");
  const ta = document.getElementById("reviewText");
  if (ta) ta.value = "";
}

function submitReview() {
  if (!selectedStars) { showToast("toast", "⚠️ Veuillez choisir une note !"); return; }
  const text = document.getElementById("reviewText").value.trim();
  if (!text) { showToast("toast", "⚠️ Veuillez écrire un commentaire !"); return; }

  const p = PRODUITS.find(x => x.id === currentProductId);
  p.evaluations.unshift({
    user: "Vous", note: selectedStars, commentaire: text,
    date: new Date().toISOString().split("T")[0], recommande: selectedStars >= 4
  });
  renderReviews(p.evaluations);
  const avg = avgNote(p.evaluations);
  document.getElementById("modalStars").innerHTML =
    renderStars(Math.round(avg)) +
    `<span class="stars-count" style="margin-left:6px;">${avg} / 5</span>`;
  resetStars();
  showToast("toast", "✅ Votre avis a été publié !");
}

function closeModal() {
  document.getElementById("productModal").classList.remove("open");
  document.body.style.overflow = "";
}

/* ===================================================================
   DASHBOARD ADMIN
   =================================================================== */

let deleteTargetId = null;
let editMode = false;

function renderAdminTables() {
  renderProduits();
  renderEvaluations();
  renderSignalements();
}

function renderProduits(data) {
  const tbody = document.getElementById("produitsTbody");
  if (!tbody) return;

  const q = (document.getElementById("searchProduit")?.value || "").toLowerCase();
  const sc = document.getElementById("filterScore")?.value || "";

  const list = (data || PRODUITS).filter(p => {
    const matchQ = !q || p.nom.toLowerCase().includes(q) || p.marque.toLowerCase().includes(q);
    const matchSc = !sc || p.nutriscore === sc;
    return matchQ && matchSc;
  });

  const statusMap = {
    actif:   '<span class="badge badge-green">Actif</span>',
    inactif: '<span class="badge badge-gray">Inactif</span>',
    attente: '<span class="badge badge-amber">En attente</span>'
  };

  tbody.innerHTML = list.map(p => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <span style="font-size:1.5rem;">${p.emoji}</span>
          <div>
            <div style="font-weight:600;color:#0f172a;font-size:0.875rem;">${p.nom}</div>
            <div style="font-size:0.75rem;color:#94a3b8;">${p.code}</div>
          </div>
        </div>
      </td>
      <td style="font-size:0.875rem;color:#374151;">${p.marque}</td>
      <td style="font-size:0.875rem;color:#374151;">${p.categorie}</td>
      <td><span class="ns-badge ${getNutriClass(p.nutriscore)}">${p.nutriscore}</span></td>
      <td style="font-size:0.875rem;color:#374151;">${p.calories} kcal</td>
      <td style="font-size:0.875rem;">
        <div style="display:flex;align-items:center;gap:4px;">
          ${renderStars(Math.round(avgNote(p.evaluations)))}
          <span style="color:#94a3b8;font-size:0.75rem;">(${p.evaluations.length})</span>
        </div>
      </td>
      <td>${statusMap[p.statut] || ""}</td>
      <td>
        <button class="btn-icon btn-edit" title="Modifier" onclick="editProduct(${p.id})">✏️</button>
        <button class="btn-icon btn-del" title="Supprimer" onclick="askDelete(${p.id})">🗑️</button>
      </td>
    </tr>
  `).join("");
}

function renderEvaluations() {
  const tbody = document.getElementById("evalTbody");
  if (!tbody) return;

  const allEvals = [];
  PRODUITS.forEach(p => {
    p.evaluations.forEach(e => allEvals.push({ ...e, produit: p.nom }));
  });

  const q = (document.getElementById("searchEval")?.value || "").toLowerCase();
  const list = allEvals.filter(e =>
    !q || e.user.toLowerCase().includes(q) || e.produit.toLowerCase().includes(q)
  );

  tbody.innerHTML = list.map(e => `
    <tr>
      <td style="font-size:0.875rem;font-weight:500;color:#0f172a;">${e.user}</td>
      <td style="font-size:0.875rem;color:#374151;">${e.produit}</td>
      <td><div style="display:flex;gap:1px;">${renderStars(e.note)}</div></td>
      <td style="font-size:0.82rem;color:#374151;max-width:200px;">${e.commentaire}</td>
      <td style="font-size:0.82rem;color:#94a3b8;">${e.date}</td>
      <td>${e.recommande
        ? '<span class="badge badge-green">Oui</span>'
        : '<span class="badge badge-red">Non</span>'}</td>
      <td>
        <button class="btn-icon btn-del" title="Supprimer" onclick="showToast('adminToast','🗑️ Évaluation supprimée')">🗑️</button>
        <button class="btn-icon" style="color:#ea580c;" title="Signaler" onclick="showToast('adminToast','🚨 Évaluation signalée')">🚨</button>
      </td>
    </tr>
  `).join("");
}

function renderSignalements() {
  const tbody = document.getElementById("signalTbody");
  if (!tbody) return;
  const statusMap = {
    "En attente": '<span class="badge badge-amber">En attente</span>',
    "Traité":     '<span class="badge badge-green">Traité</span>'
  };
  tbody.innerHTML = SIGNALEMENTS.map(s => `
    <tr>
      <td style="font-size:0.875rem;font-weight:500;color:#0f172a;">${s.signaleur}</td>
      <td style="font-size:0.875rem;color:#374151;">${s.evaluation}</td>
      <td style="font-size:0.875rem;color:#374151;">${s.motif}</td>
      <td style="font-size:0.82rem;color:#94a3b8;">${s.date}</td>
      <td>${statusMap[s.statut] || ""}</td>
      <td>
        <button class="btn-icon btn-edit" onclick="showToast('adminToast','✅ Signalement traité')">✅</button>
        <button class="btn-icon btn-del" onclick="showToast('adminToast','🗑️ Signalement supprimé')">🗑️</button>
      </td>
    </tr>
  `).join("");
}

function filterTable(type) {
  if (type === "produits") renderProduits();
  if (type === "eval") renderEvaluations();
}

function switchTab(btn, tab) {
  document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
  btn.classList.add("active");
  ["produits","evaluations","signalements"].forEach(t => {
    const el = document.getElementById("tab-"+t);
    if (el) el.style.display = t === tab ? "block" : "none";
  });
}

function openFormModal(mode = "add") {
  editMode = mode === "edit";
  document.getElementById("formModalTitle").textContent =
    editMode ? "Modifier le produit" : "Ajouter un produit";
  document.getElementById("formModal").classList.add("open");
}

function editProduct(id) {
  const p = PRODUITS.find(x => x.id === id);
  if (!p) return;
  document.getElementById("f_nom").value = p.nom;
  document.getElementById("f_marque").value = p.marque;
  document.getElementById("f_categorie").value = p.categorie;
  document.getElementById("f_nutriscore").value = p.nutriscore;
  document.getElementById("f_calories").value = p.calories;
  document.getElementById("f_code").value = p.code;
  document.getElementById("f_proteines").value = p.proteines;
  document.getElementById("f_glucides").value = p.glucides;
  document.getElementById("f_lipides").value = p.lipides;
  document.getElementById("f_statut").value = p.statut;
  document.getElementById("tag_bio").checked = p.tags.includes("bio");
  document.getElementById("tag_vegan").checked = p.tags.includes("vegan");
  document.getElementById("tag_local").checked = p.tags.includes("local");
  document.getElementById("tag_gluten").checked = p.tags.includes("gluten");
  openFormModal("edit");
}

function closeFormModal() {
  document.getElementById("formModal").classList.remove("open");
}

function saveProduct() {
  const nom = document.getElementById("f_nom").value.trim();
  if (!nom) { showToast("adminToast", "⚠️ Le nom est obligatoire."); return; }
  showToast("adminToast", editMode ? "✅ Produit modifié avec succès !" : "✅ Produit ajouté avec succès !");
  closeFormModal();
}

function askDelete(id) {
  deleteTargetId = id;
  document.getElementById("confirmModal").classList.add("open");
}

function confirmDelete() {
  const idx = PRODUITS.findIndex(x => x.id === deleteTargetId);
  if (idx !== -1) PRODUITS.splice(idx, 1);
  closeConfirm();
  renderProduits();
  showToast("adminToast", "🗑️ Produit supprimé.");
}

function closeConfirm() {
  document.getElementById("confirmModal").classList.remove("open");
  deleteTargetId = null;
}

/* ===================================================================
   CHARTS (Dashboard uniquement)
   =================================================================== */

function renderCharts() {
  const evalCanvas = document.getElementById("chartEval");
  const nutriCanvas = document.getElementById("chartNutri");
  if (!evalCanvas || !nutriCanvas) return;

  // Évaluations par semaine
  new Chart(evalCanvas, {
    type: "bar",
    data: {
      labels: ["Sem 1", "Sem 2", "Sem 3", "Sem 4", "Sem 5", "Sem 6"],
      datasets: [{
        label: "Évaluations",
        data: [18, 32, 27, 45, 38, 52],
        backgroundColor: "rgba(20, 184, 166, 0.7)",
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: "rgba(0,0,0,0.04)" }, beginAtZero: true }
      }
    }
  });

  // Répartition Nutriscore
  const counts = { A: 0, B: 0, C: 0, D: 0, E: 0 };
  PRODUITS.forEach(p => { if (counts[p.nutriscore] !== undefined) counts[p.nutriscore]++; });

  new Chart(nutriCanvas, {
    type: "doughnut",
    data: {
      labels: ["A", "B", "C", "D", "E"],
      datasets: [{
        data: Object.values(counts),
        backgroundColor: ["#16a34a","#65a30d","#ca8a04","#ea580c","#dc2626"],
        borderWidth: 0,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "right",
          labels: { font: { family: "Inter", size: 12 }, padding: 16 }
        }
      },
      cutout: "65%"
    }
  });
}