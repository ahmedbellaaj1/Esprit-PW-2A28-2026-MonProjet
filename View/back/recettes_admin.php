<?php
declare(strict_types=1);

/** @var array<int, array<string,mixed>> $recipes */
/** @var string|null $flash */
/** @var array<string,string> $formErrors */
/** @var array<string,mixed> $old */
/** @var string $formAction Chemin URL du script qui traite le POST (ex. /projet/View/back/recettes.php) */
/** @var array<int, array<string,mixed>> $ingredients */
/** @var int $selectedRecetteId */
/** @var array<int, array<string,mixed>> $linkedIngredients */
/** @var int $scrollYOnLoad */

$formAction = $formAction ?? 'recettes.php';

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$count = count($recipes);
$avgCal = $count === 0 ? 0 : round(array_sum(array_map(static fn ($r) => (float) $r['calories'], $recipes)) / $count, 0);
$ingredientRows = [];
if (isset($old['ingredient_nom']) && is_array($old['ingredient_nom'])) {
    $noms = $old['ingredient_nom'];
    $bios = is_array($old['ingredient_bio'] ?? null) ? $old['ingredient_bio'] : [];
    $locals = is_array($old['ingredient_local'] ?? null) ? $old['ingredient_local'] : [];
    $saisonniers = is_array($old['ingredient_saisonnier'] ?? null) ? $old['ingredient_saisonnier'] : [];
    $quantites = is_array($old['ingredient_quantite'] ?? null) ? $old['ingredient_quantite'] : [];
    $unites = is_array($old['ingredient_unite'] ?? null) ? $old['ingredient_unite'] : [];
    foreach ($noms as $idx => $nomItem) {
        $ingredientRows[] = [
            'nom' => (string) $nomItem,
            'bio' => (string) ($bios[$idx] ?? 'non'),
            'local' => (string) ($locals[$idx] ?? 'non'),
            'saisonnier' => (string) ($saisonniers[$idx] ?? 'non'),
            'quantite' => (string) ($quantites[$idx] ?? '0'),
            'unite' => (string) ($unites[$idx] ?? 'piece'),
        ];
    }
}
if ($ingredientRows === []) {
    $ingredientRows[] = ['nom' => '', 'bio' => 'non', 'local' => 'non', 'saisonnier' => 'non', 'quantite' => '0', 'unite' => 'piece'];
}
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
    <div class="sidebar-logo">
      <img src="../../assets/images/GreenBite.png" alt="GreenBite" class="sidebar-logo-img"/>
      Green<span>Bite</span>
    </div>
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

      <div class="table-container" style="overflow-x:auto;">
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
              <th>Durée prép (HH:MM)</th>
              <th>Description</th>
              <th>Date création</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="recettesTbody">
            <?php if ($count === 0): ?>
              <tr class="recette-row" data-nom="" data-desc="">
                <td colspan="6" style="color:#64748b;">Aucune recette pour le moment. Cliquez sur « Ajouter une recette ».</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recipes as $r): ?>
                <tr class="recette-row"
                    data-id="<?= h((string) $r['id_recette']) ?>"
                    data-nom="<?= h(strtolower((string) $r['nom'])) ?>"
                    data-desc="<?= h(strtolower((string) $r['description'])) ?>"
                    data-duree="<?= h((string) ($r['duree_prep'] ?? '')) ?>">
                  <td><strong><?= h((string) $r['nom']) ?></strong></td>
                  <td><?= h((string) $r['calories']) ?> kcal</td>
                  <td><?= h(substr((string) ($r['duree_prep'] ?? '00:00:00'), 0, 5)) ?></td>
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
        <label for="f_duree_prep">Durée préparation * (heures:minutes)</label>
        <input type="time" name="duree_prep" id="f_duree_prep" min="00:01" required value="<?= h(substr((string) ($old['duree_prep'] ?? ''), 0, 5)) ?>"/>
        <div class="field-error" id="err_duree_prep"></div>
      </div>
      <div class="form-group">
        <label for="f_description">Description *</label>
        <textarea name="description" id="f_description" rows="5" required placeholder="Étapes, astuces zéro déchet, saisonnalité..."><?= h((string) ($old['description'] ?? '')) ?></textarea>
        <div class="field-error" id="err_description"></div>
      </div>
      <div class="form-group">
        <label>Ingrédients de la recette</label>
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;font-size:12px;color:#64748b;margin-bottom:6px;">
          <span>Nom</span><span>Bio</span><span>Local</span><span>Saisonnier</span><span>Quantité</span><span>Unité</span><span></span>
        </div>
        <div id="ingredientRows">
          <?php foreach ($ingredientRows as $idx => $item): ?>
            <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;">
              <input type="text" name="ingredient_nom[]" maxlength="50" placeholder="Nom ingrédient" value="<?= h($item['nom']) ?>"/>
              <select name="ingredient_bio[]">
                <option value="non" <?= $item['bio'] === 'non' ? 'selected' : '' ?>>Non</option>
                <option value="oui" <?= $item['bio'] === 'oui' ? 'selected' : '' ?>>Oui</option>
              </select>
              <select name="ingredient_local[]">
                <option value="non" <?= $item['local'] === 'non' ? 'selected' : '' ?>>Non</option>
                <option value="oui" <?= $item['local'] === 'oui' ? 'selected' : '' ?>>Oui</option>
              </select>
              <select name="ingredient_saisonnier[]">
                <option value="non" <?= $item['saisonnier'] === 'non' ? 'selected' : '' ?>>Non</option>
                <option value="oui" <?= $item['saisonnier'] === 'oui' ? 'selected' : '' ?>>Oui</option>
              </select>
              <input type="text" name="ingredient_quantite[]" value="<?= h($item['quantite']) ?>" placeholder="Qté"/>
              <select name="ingredient_unite[]">
                <option value="piece" <?= $item['unite'] === 'piece' ? 'selected' : '' ?>>Piece</option>
                <option value="kg" <?= $item['unite'] === 'kg' ? 'selected' : '' ?>>KG</option>
                <option value="litre" <?= $item['unite'] === 'litre' ? 'selected' : '' ?>>Litre</option>
              </select>
              <button type="button" class="btn-icon btn-del" onclick="removeIngredientRow(this)">−</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn-add" onclick="addIngredientRow()">+ Ajouter un autre ingrédient</button>
        <div class="field-error" id="err_ingredient_nom"></div>
        <div class="field-error" id="err_ingredient_quantite"></div>
      </div>
      <?php if (isset($formErrors['id_recette'])): ?><div class="field-error"><?= h($formErrors['id_recette']) ?></div><?php endif; ?>
      <div class="form-actions">
        <button type="button" class="btn-cancel" onclick="closeRecipeModal()">Annuler</button>
        <button type="submit" class="primary-btn" style="padding:10px 24px;">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<div class="page-content" style="margin-top:16px;">
  <div class="table-container" style="margin-bottom:16px;overflow-x:auto;">
    <h2 style="margin:0 0 12px 0;">Liste des ingrédients</h2>
    <table>
      <thead><tr><th>ID</th><th>Nom</th><th>Bio</th><th>Local</th><th>Saisonnier</th><th>Quantité</th><th>Unité</th><th>Créateur</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($ingredients === []): ?>
        <tr><td colspan="9">Aucun ingrédient.</td></tr>
      <?php else: foreach ($ingredients as $i): 
        $isAdmin = (($i['id_user'] ?? null) === null);
      ?>
        <tr>
          <td><?= (int) $i['id_ingredient'] ?></td>
          <td><?= h((string) $i['nom']) ?></td>
          <td><?= h((string) $i['bio']) ?></td>
          <td><?= h((string) $i['local']) ?></td>
          <td><?= h((string) $i['saisonnier']) ?></td>
          <td><?= h((string) ($i['quantite'] ?? 0)) ?></td>
          <td><?= h((string) ($i['unite'] ?? 'piece')) ?></td>
          <td><?= $isAdmin ? '👨‍💼 Admin' : '👤 Utilisateur' ?></td>
          <td>
            <details>
              <summary style="cursor:pointer;">Modifier</summary>
              <form method="post" action="<?= h($formAction) ?>" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-top:8px;">
                <input type="hidden" name="action" value="ingredient_update"/>
                <input type="hidden" name="id_ingredient" value="<?= (int) $i['id_ingredient'] ?>"/>
                <input type="text" name="nom" maxlength="50" required value="<?= h((string) $i['nom']) ?>"/>
                <select name="bio"><option value="non" <?= ($i['bio'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['bio'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                <select name="local"><option value="non" <?= ($i['local'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['local'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                <select name="saisonnier"><option value="non" <?= ($i['saisonnier'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['saisonnier'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                <input type="text" name="quantite" required value="<?= h((string) ($i['quantite'] ?? 1)) ?>"/>
                <select name="unite"><option value="piece" <?= ($i['unite'] ?? '') === 'piece' ? 'selected' : '' ?>>Piece</option><option value="kg" <?= ($i['unite'] ?? '') === 'kg' ? 'selected' : '' ?>>KG</option><option value="litre" <?= ($i['unite'] ?? '') === 'litre' ? 'selected' : '' ?>>Litre</option></select>
                <button type="submit" class="btn-add">OK</button>
              </form>
            </details>
            <form method="post" action="<?= h($formAction) ?>" onsubmit="return confirm('Supprimer cet ingrédient ?');" style="margin-top:8px;">
              <input type="hidden" name="action" value="ingredient_delete"/>
              <input type="hidden" name="id_ingredient" value="<?= (int) $i['id_ingredient'] ?>"/>
              <button type="submit" class="btn-icon btn-del">🗑️</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="table-container" style="overflow-x:auto;">
    <h2 style="margin:0 0 12px 0;">Ingrédients par recette</h2>
    <form method="get" action="<?= h($formAction) ?>" style="margin-bottom:12px;">
      <label for="filter_recipe_back">Choisir une recette :</label>
      <select id="filter_recipe_back" name="id_recette" onchange="this.form.submit()">
        <?php foreach ($recipes as $r): ?>
          <option value="<?= (int) $r['id_recette'] ?>" <?= ((int) $r['id_recette'] === $selectedRecetteId) ? 'selected' : '' ?>>
            <?= h((string) $r['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <table>
      <thead><tr><th>ID ingrédient</th><th>Nom</th><th>Bio</th><th>Local</th><th>Saisonnier</th><th>Quantité</th><th>Unité</th></tr></thead>
      <tbody>
      <?php if ($linkedIngredients === []): ?>
        <tr><td colspan="7">Aucune association pour cette recette.</td></tr>
      <?php else: foreach ($linkedIngredients as $li): ?>
        <tr>
          <td><?= (int) $li['id_ingredient'] ?></td>
          <td><?= h((string) $li['nom']) ?></td>
          <td><?= h((string) $li['bio']) ?></td>
          <td><?= h((string) $li['local']) ?></td>
          <td><?= h((string) $li['saisonnier']) ?></td>
          <td><?= h((string) ($li['quantite'] ?? 0)) ?></td>
          <td><?= h((string) ($li['unite'] ?? 'piece')) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
    </div>
  </div>
</div>

<div class="toast" id="adminToast"><?= $flash !== null && $flash !== '' ? h($flash) : '' ?></div>

<script>
  window.__RECIPES = <?= json_encode($recipes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_FLASH = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_FORM_ERRORS = <?= json_encode($formErrors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_OLD = <?= json_encode($old, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  window.__RECETTE_SCROLL_Y = <?= (int) ($scrollYOnLoad ?? 0) ?>;
  <?php
    $ingredientsByRecipeAdmin = [];
    foreach ($ingredients as $ing) {
        $rid = (int) ($ing['id_recette'] ?? 0);
        if ($rid > 0) {
            $ingredientsByRecipeAdmin[$rid][] = [
                'nom'        => (string) ($ing['nom'] ?? ''),
                'bio'        => (string) ($ing['bio'] ?? 'non'),
                'local'      => (string) ($ing['local'] ?? 'non'),
                'saisonnier' => (string) ($ing['saisonnier'] ?? 'non'),
                'quantite'   => (string) ($ing['quantite'] ?? '1'),
                'unite'      => (string) ($ing['unite'] ?? 'piece'),
            ];
        }
    }
  ?>
  window.__INGREDIENTS_BY_RECIPE = <?= json_encode($ingredientsByRecipeAdmin, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="../../assets/js/recettes-admin.js?v=<?= time() ?>" defer></script>
</body>
</html>
