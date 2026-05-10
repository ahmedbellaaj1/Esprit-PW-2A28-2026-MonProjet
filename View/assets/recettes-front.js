/* global window, document */

function $(id) {
  return document.getElementById(id);
}

let currentDetail = null;
let calBand = 'all';
let timeBand = 'all';
let favorisFilter = false;

function showToastFront(msg) {
  const t = $('toast');
  if (!t || !msg) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

function timeToMinutes(v) {
  if (!v || !/^\d{2}:\d{2}$/.test(v)) return 0;
  const [h, m] = v.split(':').map((n) => Number(n));
  return h * 60 + m;
}

function recipeById(id) {
  const list = window.__FRONT_RECIPES || [];
  return list.find((x) => String(x.id_recette) === String(id));
}

function openDetailModal(recipe) {
  currentDetail = recipe;
  $('detailName').textContent = recipe.nom || '';
  $('detailMeta').textContent = `${recipe.calories ?? ''} kcal · ${(recipe.date_creation || '').toString().slice(0, 10)}`;
  const prep = (recipe.duree_prep || '').toString().slice(0, 5);
  $('detailMeta').textContent += prep ? ` · Préparation ${prep}` : '';
  $('detailDesc').textContent = recipe.description || '';
  
  // Vérifier si c'est une recette admin (id_user NULL ou undefined)
  const isAdmin = recipe.id_user === null || recipe.id_user === undefined;
  const currentUserId = window.__FRONT_USER_ID || 1;
  const canModify = !isAdmin && ((recipe.id_user || 0) === currentUserId);
  
  // Afficher/masquer les boutons Modifier et Supprimer
  const editBtn = document.querySelector('#detailModal button[onclick="fromDetailToEdit()"]');
  const deleteBtn = document.querySelector('#detailModal button[onclick="fromDetailToDelete()"]');
  
  if (editBtn) editBtn.style.display = canModify ? 'block' : 'none';
  if (deleteBtn) deleteBtn.style.display = canModify ? 'block' : 'none';
  
  // Ajouter un badge si c'est une recette admin
  const detailHeader = document.querySelector('.modal-header');
  const adminBadge = detailHeader?.querySelector('.admin-badge');
  if (isAdmin) {
    if (!adminBadge) {
      const badge = document.createElement('span');
      badge.className = 'admin-badge';
      badge.textContent = '👨‍💼 Recette du chef';
      badge.style.cssText = 'position:absolute;top:8px;right:8px;background:#f59e0b;color:white;padding:4px 12px;border-radius:9999px;font-size:0.75rem;font-weight:600;';
      detailHeader?.appendChild(badge);
    }
  } else if (adminBadge) {
    adminBadge.remove();
  }
  
  $('detailModal')?.classList.add('open');
}

function closeDetailModal() {
  $('detailModal')?.classList.remove('open');
}

function fromDetailToEdit() {
  if (!currentDetail) return;
  
  // Vérifier les droits
  const isAdmin = currentDetail.id_user === null || currentDetail.id_user === undefined;
  const currentUserId = window.__FRONT_USER_ID || 1;
  const canModify = !isAdmin && ((currentDetail.id_user || 0) === currentUserId);
  
  if (!canModify) {
    showToastFront('Vous n\'êtes pas autorisé à modifier cette recette');
    return;
  }
  
  closeDetailModal();
  openFrontModal('edit', currentDetail.id_recette);
}

function fromDetailToDelete() {
  if (!currentDetail) return;
  
  // Vérifier les droits
  const isAdmin = currentDetail.id_user === null || currentDetail.id_user === undefined;
  const currentUserId = window.__FRONT_USER_ID || 1;
  const canModify = !isAdmin && ((currentDetail.id_user || 0) === currentUserId);
  
  if (!canModify) {
    showToastFront('Vous n\'êtes pas autorisé à supprimer cette recette');
    return;
  }
  
  const id = currentDetail.id_recette;
  const name = currentDetail.nom || '';
  closeDetailModal();
  openFrontDeleteModal(id, name);
}

function openFrontModal(mode, id) {
  const modal = $('frontFormModal');
  const title = $('frontFormTitle');
  const action = $('front_form_action');
  const fid = $('front_f_id');
  const form = $('frontRecetteForm');
  if (!modal || !form) return;

  clearFrontErrors();
  if (mode === 'create') {
    title.textContent = 'Proposer une recette';
    action.value = 'create';
    fid.value = '';
    form.reset();
  } else if (mode === 'edit' && id != null) {
    const r = recipeById(id);
    if (!r) return;
    title.textContent = 'Modifier la recette';
    action.value = 'update';
    fid.value = String(r.id_recette);
    $('front_f_nom').value = r.nom || '';
    $('front_f_calories').value = r.calories ?? '';
    $('front_f_duree_prep').value = (r.duree_prep || '').toString().slice(0, 5);
    $('front_f_description').value = r.description || '';

    // Pre-fill ingredient rows from existing recipe ingredients
    const box = $('frontIngredientRows');
    if (box) {
      box.innerHTML = '';
      const ings = (window.__FRONT_INGREDIENTS_BY_RECIPE || {})[String(r.id_recette)] || [];
      if (ings.length === 0) {
        box.insertAdjacentHTML('beforeend', frontIngredientRowTemplate());
      } else {
        ings.forEach((ing) => {
          box.insertAdjacentHTML('beforeend', frontIngredientRowTemplate());
          const row = box.lastElementChild;
          row.querySelector('input[name="ingredient_nom[]"]').value = ing.nom || '';
          row.querySelector('input[name="ingredient_quantite[]"]').value = ing.quantite || '1';
          setSelectValue(row.querySelector('select[name="ingredient_bio[]"]'), ing.bio);
          setSelectValue(row.querySelector('select[name="ingredient_local[]"]'), ing.local);
          setSelectValue(row.querySelector('select[name="ingredient_saisonnier[]"]'), ing.saisonnier);
          setSelectValue(row.querySelector('select[name="ingredient_unite[]"]'), ing.unite);
        });
      }
    }
  }
  modal.classList.add('open');
}

function closeFrontFormModal() {
  $('frontFormModal')?.classList.remove('open');
}

function frontIngredientRowTemplate() {
  return `
    <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;">
      <input type="text" name="ingredient_nom[]" maxlength="50" placeholder="Nom ingrédient"/>
      <select name="ingredient_bio[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <select name="ingredient_local[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <select name="ingredient_saisonnier[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <input type="text" name="ingredient_quantite[]" value="1" placeholder="Qté"/>
      <select name="ingredient_unite[]"><option value="piece">Piece</option><option value="kg">KG</option><option value="litre">Litre</option></select>
      <button type="button" class="btn-icon btn-del" onclick="removeFrontIngredientRow(this)">−</button>
    </div>
  `;
}

function addFrontIngredientRow() {
  const box = $('frontIngredientRows');
  if (!box) return;
  box.insertAdjacentHTML('beforeend', frontIngredientRowTemplate());
}

function removeFrontIngredientRow(btn) {
  const box = $('frontIngredientRows');
  if (!box) return;
  if (box.querySelectorAll('.ingredient-row').length <= 1) return;
  btn.closest('.ingredient-row')?.remove();
}

function setSelectValue(select, value) {
  if (!select || value === undefined || value === null) return;
  const opt = select.querySelector(`option[value="${value}"]`);
  if (opt) opt.selected = true;
}

function openFrontDeleteModal(id, name) {
  $('front_delete_id').value = String(id);
  const p = $('frontConfirmText');
  if (p) p.textContent = `La recette « ${name} » sera supprimée.`;
  $('frontConfirmModal')?.classList.add('open');
}

function closeFrontDeleteModal() {
  $('frontConfirmModal')?.classList.remove('open');
}

function clearFrontErrors() {
  ['nom', 'calories', 'duree_prep', 'description'].forEach((k) => {
    const el = $('front_err_' + k);
    if (el) el.textContent = '';
    const inp = $('front_f_' + k);
    if (inp) inp.classList.remove('input-error');
  });
  const ingNom = $('front_err_ingredient_nom');
  const ingQte = $('front_err_ingredient_quantite');
  if (ingNom) ingNom.textContent = '';
  if (ingQte) ingQte.textContent = '';
}

function validateFrontForm() {
  clearFrontErrors();
  let ok = true;
  const nom = ($('front_f_nom')?.value || '').trim();
  const cal = ($('front_f_calories')?.value || '').trim();
  const dureePrep = ($('front_f_duree_prep')?.value || '').trim();
  const desc = ($('front_f_description')?.value || '').trim();

  if (!nom) {
    setFrontErr('nom', 'Le nom est obligatoire.');
    ok = false;
  } else if (nom.length > 150) {
    setFrontErr('nom', 'Maximum 150 caractères.');
    ok = false;
  } else if (/\d/u.test(nom) && !/[A-Za-zÀ-ÖØ-öø-ÿ]/u.test(nom)) {
    setFrontErr('nom', 'Si le nom contient des chiffres, il doit aussi contenir au moins une lettre.');
    ok = false;
  }
  if (cal === '') {
    setFrontErr('calories', 'Les calories sont obligatoires.');
    ok = false;
  } else if (Number.isNaN(Number(cal))) {
    setFrontErr('calories', 'Nombre invalide.');
    ok = false;
  } else if (Number(cal) < 0) {
    setFrontErr('calories', 'Valeur négative interdite.');
    ok = false;
  }
  if (!desc) {
    setFrontErr('description', 'La description est obligatoire.');
    ok = false;
  }
  if (!dureePrep) {
    setFrontErr('duree_prep', 'La durée de préparation est obligatoire.');
    ok = false;
  } else if (timeToMinutes(dureePrep) < 1) {
    setFrontErr('duree_prep', 'La durée minimale est 00:01.');
    ok = false;
  }
  document.querySelectorAll('#frontIngredientRows .ingredient-row').forEach((row) => {
    const nom = (row.querySelector('input[name="ingredient_nom[]"]')?.value || '').trim();
    const qte = (row.querySelector('input[name="ingredient_quantite[]"]')?.value || '').trim().replace(',', '.');
    if (!nom && !qte) return;
    if (!nom || nom.length > 50 || !/^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]+$/u.test(nom)) {
      const e = $('front_err_ingredient_nom');
      if (e) e.textContent = 'Nom ingrédient: lettres uniquement';
      ok = false;
    }
    if (qte === '' || Number.isNaN(Number(qte)) || Number(qte) <= 0) {
      const e = $('front_err_ingredient_quantite');
      if (e) e.textContent = 'Quantité ingrédient: nombre > 0 (ex: 0,5 KG ou 2L).';
      ok = false;
    }
  });
  return ok;
}

function setFrontErr(field, msg) {
  const e = $('front_err_' + field);
  if (e) e.textContent = msg;
  const inp = $('front_f_' + field);
  if (inp) inp.classList.add('input-error');
}

function filterRecettesFront() {
  const q = ($('searchInput')?.value || '').trim().toLowerCase();
  document.querySelectorAll('.recette-card').forEach((card) => {
    const nom = (card.dataset.nom || '').toLowerCase();
    const desc = (card.dataset.desc || '').toLowerCase();
    const band = card.dataset.band || '';
    const duree = card.dataset.duree || '00:00:00';
    const isFavori = (card.dataset.favori || '0') === '1';
    const textOk = q === '' || nom.includes(q) || desc.includes(q);
    const calOk = calBand === 'all' || band === calBand;
    const favorisOk = !favorisFilter || isFavori;
    
    let timeOk = true;
    if (timeBand !== 'all') {
      const minutes = timeToMinutes(duree.slice(0, 5));
      if (timeBand === '10') timeOk = minutes <= 10;
      else if (timeBand === '20') timeOk = minutes <= 20;
      else if (timeBand === '30') timeOk = minutes <= 30;
    }

    card.style.display = textOk && calOk && favorisOk && timeOk ? '' : 'none';
  });
}

function setCalFilter(btn, band) {
  document.querySelectorAll('.filter-pill[data-cal]').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  calBand = band;
  filterRecettesFront();
}

function setFavorisFilter(btn) {
  favorisFilter = !favorisFilter;
  if (favorisFilter) {
    btn.classList.add('active');
  } else {
    btn.classList.remove('active');
  }
  filterRecettesFront();
}

function setTimeFilter(btn, band) {
  document.querySelectorAll('.filter-pill[data-time]').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  timeBand = band;
  filterRecettesFront();
}

// Chatbot functionality
function toggleChatbot() {
  const container = $('chatbot-container');
  const icon = $('chatbot-toggle-icon');
  if (container.style.transform === 'translateY(calc(100% - 50px))') {
    container.style.transform = 'translateY(0)';
    icon.textContent = '▼';
  } else {
    container.style.transform = 'translateY(calc(100% - 50px))';
    icon.textContent = '▲';
  }
}

function addChatMessage(text, isBot = false) {
  const messages = $('chatbot-messages');
  const div = document.createElement('div');
  div.style.padding = '10px 14px';
  div.style.borderRadius = '12px';
  div.style.maxWidth = '85%';
  div.style.fontSize = '0.9rem';
  div.style.lineHeight = '1.4';
  
  if (isBot) {
    div.style.background = '#e2e8f0';
    div.style.borderBottomLeftRadius = '2px';
    div.style.alignSelf = 'flex-start';
    div.innerHTML = text; // Permet d'injecter du HTML généré par le bot
  } else {
    div.style.background = '#0d9488';
    div.style.color = 'white';
    div.style.borderBottomRightRadius = '2px';
    div.style.alignSelf = 'flex-end';
    div.textContent = text;
  }
  
  messages.appendChild(div);
  messages.scrollTop = messages.scrollHeight;
}

async function sendChatMessage() {
  const input = $('chatbot-input');
  const msg = input.value.trim();
  if (!msg) return;
  
  addChatMessage(msg, false);
  input.value = '';
  
  try {
    const formData = new FormData();
    formData.append('message', msg);
    
    // Check if we are in front or back directory to set relative path
    const isFront = window.location.pathname.includes('/front/');
    const apiUrl = isFront ? '../../api/chatbot_api.php' : '../api/chatbot_api.php';
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    if (data.status === 'success') {
      addChatMessage(data.reply, true);
    } else {
      addChatMessage("Désolé, une erreur est survenue.", true);
    }
  } catch (error) {
    addChatMessage("Désolé, je n'arrive pas à joindre le serveur.", true);
  }
}

function submitRecetteSelectForm() {
  const form = $('recetteSelectForm');
  if (!form) return;
  const scrollInput = form.querySelector('input[name="scroll_y"]');
  if (scrollInput) {
    scrollInput.value = String(Math.max(0, Math.floor(window.scrollY || 0)));
  }
  form.submit();
}

document.addEventListener('DOMContentLoaded', () => {
  const scrollY = Number(window.__FRONT_RECETTE_SCROLL_Y || 0);
  if (Number.isFinite(scrollY) && scrollY > 0) {
    window.scrollTo({ top: scrollY, behavior: 'auto' });
  }

  document.querySelectorAll('.recette-card').forEach((card) => {
    card.addEventListener('click', () => {
      const id = card.dataset.id;
      const r = recipeById(id);
      if (r) openDetailModal(r);
    });
  });

  $('searchInput')?.addEventListener('input', filterRecettesFront);

  if (window.__FRONT_RECETTE_FLASH) {
    showToastFront(window.__FRONT_RECETTE_FLASH);
  }

  const errs = window.__FRONT_RECETTE_ERRORS || {};
  const old = window.__FRONT_RECETTE_OLD || {};
  if (Object.keys(errs).length || Object.keys(old).length) {
    const isUpdate = old.action === 'update' || (old.id_recette && String(old.id_recette) !== '');
    if (isUpdate && old.id_recette) {
      openFrontModal('edit', old.id_recette);
    } else {
      openFrontModal('create');
    }
    Object.keys(errs).forEach((k) => {
      if (['nom', 'calories', 'duree_prep', 'description'].includes(k)) {
        setFrontErr(k, errs[k]);
      }
    });
  }

  $('frontRecetteForm')?.addEventListener('submit', (ev) => {
    if (!validateFrontForm()) ev.preventDefault();
  });

  $('detailModal')?.addEventListener('click', (ev) => {
    if (ev.target === $('detailModal')) closeDetailModal();
  });
  $('frontFormModal')?.addEventListener('click', (ev) => {
    if (ev.target === $('frontFormModal')) closeFrontFormModal();
  });
  $('frontConfirmModal')?.addEventListener('click', (ev) => {
    if (ev.target === $('frontConfirmModal')) closeFrontDeleteModal();
  });

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      let input = form.querySelector('input[name="scroll_y"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'scroll_y';
        form.appendChild(input);
      }
      input.value = String(Math.max(0, Math.floor(window.scrollY || 0)));
    });
  });
});
