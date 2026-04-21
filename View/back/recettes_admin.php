<?php
declare(strict_types=1);

/** @var array<int, array<string,mixed>> $recipes */
/** @var string|null $flash */
/** @var array<string,string> $formErrors */
/** @var array<string,mixed> $old */
/** @var string $formAction Chemin URL du script qui traite le POST (ex. /projet/View/back/recettes.php) */

$formAction = $formAction ?? 'recettes.php';

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$count = count($recipes);
$avgCal = $count === 0 ? 0 : round(array_sum(array_map(static fn ($r) => (float) $r['calories'], $recipes)) / $count, 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenBite Admin – Recettes durables</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style.css"/>
</head>
<body>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-logo">Green<span>Bite</span></div>
    <div class="sidebar-role">Administration</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="#">
        <span class="icon">📊</span> Vue d'ensemble
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">🛒</span> Produits
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">⭐</span> Évaluations
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">👥</span> Utilisateurs
      </a>
      <a class="sidebar-link active" href="recettes.php">
        <span class="icon">🍽️</span> Recettes
      </a>
      <a class="sidebar-link" href="ingredients.php">
        <span class="icon">🥕</span> Ingrédients
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">🎁</span> Dons
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">📍</span> Magasins
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">📈</span> Rapports
      </a>
    </nav>
    <div class="sidebar-bottom">
      <a class="sidebar-link" href="../front/recettes.php">
        <span class="icon">🌐</span> Voir le front-office
      </a>
      <a class="sidebar-link" href="#">
        <span class="icon">⚙️</span> Paramètres
      </a>
    </div>
  </aside>

  <div class="dashboard-main">
    <header class="dashboard-header">
      <div class="header-title"> Recettes durables</div>
      <div class="header-right">
        <span class="header-badge">🟢 Recette</span>
        <div class="admin-avatar">AD</div>
      </div>
    </header>

    <div class="page-content">
      <div class="page-header">
        <h1>Gestion des recettes</h1>
        <p>Ajoutez, modifiez ou supprimez des recettes – validation côté serveur et formulaire.</p>
      </div>

      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-icon icon-teal">🍽️</div>
          <div class="metric-value"><?= h((string) $count) ?></div>
          <div class="metric-label">Recettes en base</div>
          
        </div>
        <div class="metric-card">
          <div class="metric-icon icon-blue">🔥</div>
          <div class="metric-value"><?= h((string) $avgCal) ?></div>
          <div class="metric-label">Moyenne calories / recette</div>
          <div class="metric-trend trend-up">Champ <code>calories</code></div>
        </div>
      </div>

      <div class="table-container">
        <div class="table-toolbar">
          <div class="table-search">
            <span>🔍</span>
            <input type="text" id="searchRecette" placeholder="Filtrer par nom ou description..." oninput="filterRecettesTable()"/>
          </div>
          <button type="button" class="btn-add" onclick="openRecipeModal('create')">+ Ajouter une recette</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Nom</th>
              <th>Calories</th>
              <th>Description</th>
              <th>Date création</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="recettesTbody">
            <?php if ($count === 0): ?>
              <tr class="recette-row" data-nom="" data-desc="">
                <td colspan="5" style="color:#64748b;">Aucune recette pour le moment. Cliquez sur « Ajouter une recette ».</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recipes as $r): ?>
                <tr class="recette-row"
                    data-id="<?= h((string) $r['id_recette']) ?>"
                    data-nom="<?= h(strtolower((string) $r['nom'])) ?>"
                    data-desc="<?= h(strtolower((string) $r['description'])) ?>">
                  <td><strong><?= h((string) $r['nom']) ?></strong></td>
                  <td><?= h((string) $r['calories']) ?> kcal</td>
                  <td style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h((string) $r['description']) ?></td>
                  <td><?= h((string) $r['date_creation']) ?></td>
                  <td>
                    <button type="button" class="btn-icon btn-edit" title="Modifier" onclick="openRecipeModal('edit', <?= (int) $r['id_recette'] ?>)">✏️</button>
                    <form class="inline-del-form" method="post" action="<?= h($formAction) ?>" style="display:inline;margin:0;padding:0;border:0;vertical-align:middle;" onsubmit="return confirm(<?= json_encode('Supprimer la recette « ' . (string) $r['nom'] . ' » ?', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);">
                      <input type="hidden" name="action" value="delete"/>
                      <input type="hidden" name="delete_id_recette" value="<?= (int) $r['id_recette'] ?>"/>
                      <button type="submit" class="btn-icon btn-del" title="Supprimer">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="form-modal-overlay" id="formModal">
  <div class="form-modal" style="max-width:640px;">
    <button type="button" class="modal-close" onclick="closeRecipeModal()">✕</button>
    <h2 id="formModalTitle">Ajouter une recette</h2>
    <form id="recetteForm" method="post" action="<?= h($formAction) ?>" novalidate>
      <input type="hidden" name="action" id="recette_post_action" value="create"/>
      <input type="hidden" name="id_recette" id="f_id_recette" value=""/>
      <div class="form-group">
        <label for="f_nom">Nom de la recette *</label>
        <input type="text" name="nom" id="f_nom" maxlength="150" required placeholder="ex : Bowl quinoa & légumes de saison" value="<?= h((string) ($old['nom'] ?? '')) ?>"/>
        <div class="field-error" id="err_nom"></div>
      </div>
      <div class="form-group">
        <label for="f_calories">Calories (kcal) *</label>
        <input type="number" name="calories" id="f_calories" min="0" step="1" required placeholder="ex : 420" value="<?= h((string) ($old['calories'] ?? '')) ?>"/>
        <div class="field-error" id="err_calories"></div>
      </div>
      <div class="form-group">
        <label for="f_description">Description *</label>
        <textarea name="description" id="f_description" rows="5" required placeholder="Étapes, astuces zéro déchet, saisonnalité..."><?= h((string) ($old['description'] ?? '')) ?></textarea>
        <div class="field-error" id="err_description"></div>
      </div>
      <?php if (isset($formErrors['id_recette'])): ?><div class="field-error"><?= h($formErrors['id_recette']) ?></div><?php endif; ?>
      <div class="form-actions">
        <button type="button" class="btn-cancel" onclick="closeRecipeModal()">Annuler</button>
        <button type="submit" class="primary-btn" style="padding:10px 24px;">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="adminToast"><?= $flash !== null && $flash !== '' ? h($flash) : '' ?></div>

<script>
  window.__RECIPES = <?= json_encode($recipes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_FLASH = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_FORM_ERRORS = <?= json_encode($formErrors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_OLD = <?= json_encode($old, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="../../assets/js/recettes-admin.js" defer></script>
</body>
</html>
