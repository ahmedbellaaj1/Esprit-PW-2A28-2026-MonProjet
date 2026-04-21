/* global window, document */

function $(id) {
  return document.getElementById(id);
}

let currentDetail = null;
let calBand = 'all';

function showToastFront(msg) {
  const t = $('toast');
  if (!t || !msg) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

function recipeById(id) {
  const list = window.__FRONT_RECIPES || [];
  return list.find((x) => String(x.id_recette) === String(id));
}

function openDetailModal(recipe) {
  currentDetail = recipe;
  $('detailName').textContent = recipe.nom || '';
  $('detailMeta').textContent = `${recipe.calories ?? ''} kcal · ${(recipe.date_creation || '').toString().slice(0, 10)}`;
  $('detailDesc').textContent = recipe.description || '';
  $('detailModal')?.classList.add('open');
}

function closeDetailModal() {
  $('detailModal')?.classList.remove('open');
}

function fromDetailToEdit() {
  if (!currentDetail) return;
  closeDetailModal();
  openFrontModal('edit', currentDetail.id_recette);
}

function fromDetailToDelete() {
  if (!currentDetail) return;
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
    $('front_f_description').value = r.description || '';
  }
  modal.classList.add('open');
}

function closeFrontFormModal() {
  $('frontFormModal')?.classList.remove('open');
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
  ['nom', 'calories', 'description'].forEach((k) => {
    const el = $('front_err_' + k);
    if (el) el.textContent = '';
    const inp = $('front_f_' + k);
    if (inp) inp.classList.remove('input-error');
  });
}

function validateFrontForm() {
  clearFrontErrors();
  let ok = true;
  const nom = ($('front_f_nom')?.value || '').trim();
  const cal = ($('front_f_calories')?.value || '').trim();
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
    const textOk = q === '' || nom.includes(q) || desc.includes(q);
    const calOk = calBand === 'all' || band === calBand;
    card.style.display = textOk && calOk ? '' : 'none';
  });
}

function setCalFilter(btn, band) {
  document.querySelectorAll('.filter-pill').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  calBand = band;
  filterRecettesFront();
}

document.addEventListener('DOMContentLoaded', () => {
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
      if (['nom', 'calories', 'description'].includes(k)) {
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
});
