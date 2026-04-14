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
    $('f_description').value = r.description || '';
  }
  modal.classList.add('open');
}

function closeRecipeModal() {
  $('formModal')?.classList.remove('open');
}

function clearFieldErrors() {
  ['nom', 'calories', 'description'].forEach((k) => {
    const el = $('err_' + k);
    if (el) el.textContent = '';
    const inp = $('f_' + k);
    if (inp) inp.classList.remove('input-error');
  });
}

function validateRecipeFormClient() {
  clearFieldErrors();
  let ok = true;
  const nom = ($('f_nom')?.value || '').trim();
  const cal = ($('f_calories')?.value || '').trim();
  const desc = ($('f_description')?.value || '').trim();

  if (!nom) {
    setErr('nom', 'Le nom est obligatoire.');
    ok = false;
  } else if (nom.length > 150) {
    setErr('nom', 'Maximum 150 caractères.');
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
  return ok;
}

function setErr(field, msg) {
  const e = $('err_' + field);
  if (e) e.textContent = msg;
  const inp = $('f_' + field);
  if (inp) inp.classList.add('input-error');
}

document.addEventListener('DOMContentLoaded', () => {
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
      if (['nom', 'calories', 'description'].includes(k)) {
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
});
