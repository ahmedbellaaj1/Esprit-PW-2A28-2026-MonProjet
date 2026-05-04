/* global window, document */

function $(id) {
  return document.getElementById(id);
}

function showToast(msg) {
  const t = $('adminToast');
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

function filterRecettesTable() {
  const q = ($('searchRecette')?.value || '').trim().toLowerCase();
  document.querySelectorAll('#recettesTbody tr.recette-row').forEach((tr) => {
    const nom = (tr.dataset.nom || '').toLowerCase();
    const desc = (tr.dataset.desc || '').toLowerCase();
    const match = q === '' || nom.includes(q) || desc.includes(q);
    tr.style.display = match ? '' : 'none';
  });
}

function openRecipeModal(mode, id) {
  const modal = $('formModal');
  const title = $('formModalTitle');
  const action = $('recette_post_action');
  const fid = $('f_id_recette');
  const form = $('recetteForm');
  if (!modal || !form) return;

  clearFieldErrors();
  if (mode === 'create') {
    title.textContent = 'Ajouter une recette';
    action.value = 'create';
    fid.value = '';
    form.reset();
  } else if (mode === 'edit' && id != null) {
    const list = window.__RECIPES || [];
    const r = list.find((x) => String(x.id_recette) === String(id));
    if (!r) return;
    title.textContent = 'Modifier la recette';
    action.value = 'update';
    fid.value = String(r.id_recette);
    $('f_nom').value = r.nom || '';
    $('f_calories').value = r.calories ?? '';
    $('f_duree_prep').value = (r.duree_prep || '').toString().slice(0, 5);
    $('f_description').value = r.description || '';

    // Pre-fill ingredient rows from existing recipe ingredients
    const box = $('ingredientRows');
    if (box) {
      box.innerHTML = '';
      const ings = (window.__INGREDIENTS_BY_RECIPE || {})[String(r.id_recette)] || [];
      if (ings.length === 0) {
        box.insertAdjacentHTML('beforeend', ingredientRowTemplate());
      } else {
        ings.forEach((ing) => {
          box.insertAdjacentHTML('beforeend', ingredientRowTemplate());
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

function closeRecipeModal() {
  $('formModal')?.classList.remove('open');
}

function ingredientRowTemplate() {
  return `
    <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;">
      <input type="text" name="ingredient_nom[]" maxlength="50" placeholder="Nom ingrédient"/>
      <select name="ingredient_bio[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <select name="ingredient_local[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <select name="ingredient_saisonnier[]"><option value="non">Non</option><option value="oui">Oui</option></select>
      <input type="text" name="ingredient_quantite[]" value="1" placeholder="Qté"/>
      <select name="ingredient_unite[]"><option value="piece">Piece</option><option value="kg">KG</option><option value="litre">Litre</option></select>
      <button type="button" class="btn-icon btn-del" onclick="removeIngredientRow(this)">−</button>
    </div>
  `;
}

function addIngredientRow() {
  const box = $('ingredientRows');
  if (!box) return;
  box.insertAdjacentHTML('beforeend', ingredientRowTemplate());
}

function removeIngredientRow(btn) {
  const box = $('ingredientRows');
  if (!box) return;
  if (box.querySelectorAll('.ingredient-row').length <= 1) return;
  btn.closest('.ingredient-row')?.remove();
}

function setSelectValue(select, value) {
  if (!select || value === undefined || value === null) return;
  const opt = select.querySelector(`option[value="${value}"]`);
  if (opt) opt.selected = true;
}

function clearFieldErrors() {
  ['nom', 'calories', 'duree_prep', 'description'].forEach((k) => {
    const el = $('err_' + k);
    if (el) el.textContent = '';
    const inp = $('f_' + k);
    if (inp) inp.classList.remove('input-error');
  });
  const ingNom = $('err_ingredient_nom');
  const ingQte = $('err_ingredient_quantite');
  if (ingNom) ingNom.textContent = '';
  if (ingQte) ingQte.textContent = '';
}

function validateRecipeFormClient() {
  clearFieldErrors();
  let ok = true;
  const nom = ($('f_nom')?.value || '').trim();
  const cal = ($('f_calories')?.value || '').trim();
  const desc = ($('f_description')?.value || '').trim();
  const dureePrep = ($('f_duree_prep')?.value || '').trim();

  if (!nom) {
    setErr('nom', 'Le nom est obligatoire.');
    ok = false;
  } else if (nom.length > 150) {
    setErr('nom', 'Maximum 150 caractères.');
    ok = false;
  } else if (/\d/u.test(nom) && !/[A-Za-zÀ-ÖØ-öø-ÿ]/u.test(nom)) {
    setErr('nom', 'Si le nom contient des chiffres, il doit aussi contenir au moins une lettre.');
    ok = false;
  }

  if (cal === '') {
    setErr('calories', 'Les calories sont obligatoires.');
    ok = false;
  } else if (Number.isNaN(Number(cal))) {
    setErr('calories', 'Nombre invalide.');
    ok = false;
  } else if (Number(cal) < 0) {
    setErr('calories', 'Les calories ne peuvent pas être négatives.');
    ok = false;
  }

  if (!desc) {
    setErr('description', 'La description est obligatoire.');
    ok = false;
  }
  if (!dureePrep) {
    setErr('duree_prep', 'La durée de préparation est obligatoire.');
    ok = false;
  } else if (timeToMinutes(dureePrep) < 1) {
    setErr('duree_prep', 'La durée minimale est 00:01.');
    ok = false;
  }
  document.querySelectorAll('#ingredientRows .ingredient-row').forEach((row) => {
    const nom = (row.querySelector('input[name="ingredient_nom[]"]')?.value || '').trim();
    const qte = (row.querySelector('input[name="ingredient_quantite[]"]')?.value || '').trim().replace(',', '.');
    if (!nom && !qte) return;
    if (!nom || nom.length > 50 || !/^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]+$/u.test(nom)) {
      const e = $('err_ingredient_nom');
      if (e) e.textContent = 'Nom ingrédient: lettres uniquement';
      ok = false;
    }
    if (qte === '' || Number.isNaN(Number(qte)) || Number(qte) <= 0) {
      const e = $('err_ingredient_quantite');
      if (e) e.textContent = 'Quantité ingrédient: nombre > 0 (ex: 0,5 KG ou 2 L).';
      ok = false;
    }
  });
  return ok;
}

function setErr(field, msg) {
  const e = $('err_' + field);
  if (e) e.textContent = msg;
  const inp = $('f_' + field);
  if (inp) inp.classList.add('input-error');
}

document.addEventListener('DOMContentLoaded', () => {
  const scrollY = Number(window.__RECETTE_SCROLL_Y || 0);
  if (Number.isFinite(scrollY) && scrollY > 0) {
    window.scrollTo({ top: scrollY, behavior: 'auto' });
  }

  if (window.__RECETTE_FLASH) {
    showToast(window.__RECETTE_FLASH);
  }

  const errs = window.__RECETTE_FORM_ERRORS || {};
  const old = window.__RECETTE_OLD || {};
  if (Object.keys(errs).length || Object.keys(old).length) {
    const isUpdate = old.action === 'update' || (old.id_recette && String(old.id_recette) !== '');
    if (isUpdate && old.id_recette) {
      openRecipeModal('edit', old.id_recette);
    } else {
      openRecipeModal('create');
    }
    Object.keys(errs).forEach((k) => {
      if (['nom', 'calories', 'duree_prep', 'description'].includes(k)) {
        setErr(k, errs[k]);
      }
    });
  }

  $('recetteForm')?.addEventListener('submit', (ev) => {
    if (!validateRecipeFormClient()) {
      ev.preventDefault();
    }
  });

  $('formModal')?.addEventListener('click', (ev) => {
    if (ev.target === $('formModal')) closeRecipeModal();
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
