<?php
declare(strict_types=1);

/** @var array<int, array<string,mixed>> $recipes */
/** @var array<int, array<string,mixed>> $recipesWithFavoris */
/** @var string|null $flash */
/** @var array<string,string> $formErrors */
/** @var array<string,mixed> $old */
/** @var string $formAction */
/** @var array<int, array<string,mixed>> $ingredients */
/** @var int $selectedRecetteId */
/** @var array<int, array<string,mixed>> $linkedIngredients */
/** @var int $scrollYOnLoad */

$formAction = $formAction ?? 'recettes.php';
$recipesWithFavoris = $recipesWithFavoris ?? $recipes;

function hf(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$count = count($recipes);
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
  <title>GreenBite – Recettes durables</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style.css"/>
</head>
<body>

  <nav class="navbar">
    <a class="navbar-logo" href="#">
      <img src="../../assets/images/GreenBite.png" alt="GreenBite" class="navbar-logo-img"/>
      Green<span>Bite</span>
    </a>
    <ul class="navbar-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="recettes.php" class="active">Recettes</a></li>
      <li><a href="#">Dons</a></li>
      <li><a href="#">Magasins</a></li>
    </ul>
    <div class="navbar-right">
      <a class="primary-btn" style="padding:9px 20px;font-size:0.85rem;text-decoration:none;display:inline-block;" href="../back/recettes.php">Espace admin</a>
      <div class="nav-avatar">GB</div>
    </div>
  </nav>

  <section class="hero-section">
    <h1>🌿 Recettes durables</h1>
    <p>Consultez, proposez et gérez vos recettes</p>
    <div class="search-wrapper">
      <input type="text" id="searchInput" placeholder="Rechercher une recette (nom ou description)..." />
      <button type="button" onclick="filterRecettesFront()">Rechercher</button>
    </div>
  </section>

  <div class="main-container">
    <div class="filter-bar">
      <button type="button" class="filter-pill active" data-cal="all" onclick="setCalFilter(this,'all')">Toutes</button>
      <button type="button" class="filter-pill" data-cal="low" onclick="setCalFilter(this,'low')">&lt; 400 kcal</button>
      <button type="button" class="filter-pill" data-cal="mid" onclick="setCalFilter(this,'mid')">400 – 700 kcal</button>
      <button type="button" class="filter-pill" data-cal="high" onclick="setCalFilter(this,'high')">&gt; 700 kcal</button>
      <button type="button" class="filter-pill" data-filter="favoris" onclick="setFavorisFilter(this)">❤️ Mes favoris</button>
    </div>

    <div class="section-heading" style="justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <span>Recettes publiées <span id="countLabel" style="font-size:0.85rem;font-weight:400;color:#64748b;margin-left:0.5rem;">(<?= hf((string) $count) ?>)</span></span>
      <button type="button" class="primary-btn" style="padding:10px 22px;font-size:0.85rem;" onclick="openFrontModal('create')">+ Proposer une recette</button>
    </div>

    <div class="products-grid" id="recettesGrid">
      <?php if ($count === 0): ?>
        <p style="color:#64748b;grid-column:1/-1;">Aucune recette. Proposez la première !</p>
      <?php else: ?>
        <?php foreach ($recipesWithFavoris as $r): ?>
          <?php
            $cal = (float) $r['calories'];
            $band = $cal < 400 ? 'low' : ($cal <= 700 ? 'mid' : 'high');
          ?>
          <article class="product-card recette-card"
              data-id="<?= hf((string) $r['id_recette']) ?>"
              data-nom="<?= hf(strtolower((string) $r['nom'])) ?>"
              data-desc="<?= hf(strtolower((string) $r['description'])) ?>"
              data-cal="<?= hf((string) $cal) ?>"
              data-duree="<?= hf((string) ($r['duree_prep'] ?? '')) ?>"
              data-band="<?= hf($band) ?>"
              data-favori="<?= (int) ($r['is_favori'] ?? 0) ?>">
            <div class="product-img" style="background:linear-gradient(135deg,#d1fae5,#6ee7b7);position:relative;">
              <span style="font-size:3rem;">🥗</span>
              <a href="<?= hf($formAction) ?>?action=toggle_favori&amp;id_recette=<?= (int) $r['id_recette'] ?>" 
                 class="favori-btn" 
                 style="position:absolute;top:8px;right:8px;background:white;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.2rem;box-shadow:0 2px 4px rgba(0,0,0,0.1);text-decoration:none;">
                <?= ((int) $r['is_favori'] === 1) ? '❤️' : '🤍' ?>
              </a>
            </div>
            <div class="product-body">
              <div class="product-brand">Recette durable</div>
              <div class="product-name"><?= hf((string) $r['nom']) ?></div>
              <div class="product-cal"><?= hf((string) $r['calories']) ?> kcal · <?= hf(substr((string) $r['date_creation'], 0, 10)) ?></div>
              <div class="product-cal">Préparation : <?= hf(substr((string) ($r['duree_prep'] ?? '00:00:00'), 0, 5)) ?></div>
              <div class="product-tags">
                <span class="tag tag-bio"><?= $band === 'low' ? 'Léger' : ($band === 'mid' ? 'Équilibré' : 'Énergétique') ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="modal-overlay" id="detailModal">
    <div class="modal-box">
      <button type="button" class="modal-close" onclick="closeDetailModal()">✕</button>
      <div class="modal-header">
        <div class="modal-emoji">🍽️</div>
        <div class="modal-title">
          <div class="brand" id="detailMeta"></div>
          <h2 id="detailName"></h2>
        </div>
      </div>
      <div class="section-heading" style="font-size:0.95rem;margin-bottom:0.75rem;">Description</div>
      <p id="detailDesc" style="color:#334155;line-height:1.6;margin-bottom:1.25rem;"></p>
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <button type="button" class="primary-btn" style="padding:10px 18px;font-size:0.85rem;" onclick="fromDetailToEdit()">Modifier</button>
        <button type="button" class="btn-danger" style="padding:10px 18px;font-size:0.85rem;border:none;border-radius:9999px;cursor:pointer;" onclick="fromDetailToDelete()">Supprimer</button>
      </div>
    </div>
  </div>

  <div class="form-modal-overlay" id="frontFormModal">
    <div class="form-modal" style="max-width:640px;">
      <button type="button" class="modal-close" onclick="closeFrontFormModal()">✕</button>
      <h2 id="frontFormTitle">Proposer une recette</h2>
      <form id="frontRecetteForm" method="post" action="<?= hf($formAction) ?>" novalidate>
        <input type="hidden" name="action" id="front_form_action" value="create"/>
        <input type="hidden" name="id_recette" id="front_f_id" value=""/>
        <div class="form-group">
          <label for="front_f_nom">Nom *</label>
          <input type="text" name="nom" id="front_f_nom" maxlength="150" required value="<?= hf((string) ($old['nom'] ?? '')) ?>"/>
          <div class="field-error" id="front_err_nom"></div>
        </div>
        <div class="form-group">
          <label for="front_f_calories">Calories (kcal) *</label>
          <input type="number" name="calories" id="front_f_calories" min="0" step="1" required value="<?= hf((string) ($old['calories'] ?? '')) ?>"/>
          <div class="field-error" id="front_err_calories"></div>
        </div>
        <div class="form-group">
          <label for="front_f_duree_prep">Durée préparation * (heures:minutes)</label>
          <input type="time" name="duree_prep" id="front_f_duree_prep" min="00:01" required value="<?= hf(substr((string) ($old['duree_prep'] ?? ''), 0, 5)) ?>"/>
          <div class="field-error" id="front_err_duree_prep"></div>
        </div>
        <div class="form-group">
          <label>Ingrédients de la recette</label>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;font-size:12px;color:#64748b;margin-bottom:6px;">
            <span>Nom</span><span>Bio</span><span>Local</span><span>Saisonnier</span><span>Quantité</span><span>Unité</span><span></span>
          </div>
          <div id="frontIngredientRows">
            <?php foreach ($ingredientRows as $item): ?>
              <div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;">
                <input type="text" name="ingredient_nom[]" maxlength="50" placeholder="Nom ingrédient" value="<?= hf($item['nom']) ?>"/>
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
                <input type="text" name="ingredient_quantite[]" value="<?= hf($item['quantite']) ?>" placeholder="Qté"/>
                <select name="ingredient_unite[]">
                  <option value="piece" <?= $item['unite'] === 'piece' ? 'selected' : '' ?>>Piece</option>
                  <option value="kg" <?= $item['unite'] === 'kg' ? 'selected' : '' ?>>KG</option>
                  <option value="litre" <?= $item['unite'] === 'litre' ? 'selected' : '' ?>>Litre</option>
                </select>
                <button type="button" class="btn-icon btn-del" onclick="removeFrontIngredientRow(this)">−</button>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn-add" onclick="addFrontIngredientRow()">+ Ajouter un autre ingrédient</button>
          <div class="field-error" id="front_err_ingredient_nom"></div>
          <div class="field-error" id="front_err_ingredient_quantite"></div>
        </div>
        <div class="form-group">
          <label for="front_f_description">Description *</label>
          <textarea name="description" id="front_f_description" rows="5" required><?= hf((string) ($old['description'] ?? '')) ?></textarea>
          <div class="field-error" id="front_err_description"></div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeFrontFormModal()">Annuler</button>
          <button type="submit" class="primary-btn" style="padding:10px 24px;">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <div class="main-container">
      <div class="table-container" style="margin-bottom:16px;overflow-x:auto;">
      <div class="section-heading">Liste des ingrédients</div>
      <table>
        <thead><tr><th>ID</th><th>Nom</th><th>Bio</th><th>Local</th><th>Saisonnier</th><th>Quantité</th><th>Unité</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if ($ingredients === []): ?><tr><td colspan="8">Aucun ingrédient disponible.</td></tr>
        <?php else: foreach ($ingredients as $i): ?>
          <tr>
            <td><?= (int) $i['id_ingredient'] ?></td>
            <td><?= hf((string) $i['nom']) ?></td>
            <td><?= hf((string) $i['bio']) ?></td>
            <td><?= hf((string) $i['local']) ?></td>
            <td><?= hf((string) $i['saisonnier']) ?></td>
            <td><?= hf((string) ($i['quantite'] ?? 0)) ?></td>
            <td><?= hf((string) ($i['unite'] ?? 'piece')) ?></td>
            <td>
              <details>
                <summary style="cursor:pointer;">Modifier</summary>
                <form method="post" action="<?= hf($formAction) ?>" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-top:8px;">
                  <input type="hidden" name="action" value="ingredient_update"/>
                  <input type="hidden" name="id_ingredient" value="<?= (int) $i['id_ingredient'] ?>"/>
                  <input type="text" name="nom" maxlength="50" required value="<?= hf((string) $i['nom']) ?>"/>
                  <select name="bio"><option value="non" <?= ($i['bio'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['bio'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                  <select name="local"><option value="non" <?= ($i['local'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['local'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                  <select name="saisonnier"><option value="non" <?= ($i['saisonnier'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option><option value="oui" <?= ($i['saisonnier'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option></select>
                  <input type="text" name="quantite" required value="<?= hf((string) ($i['quantite'] ?? 1)) ?>"/>
                  <select name="unite"><option value="piece" <?= ($i['unite'] ?? '') === 'piece' ? 'selected' : '' ?>>Piece</option><option value="kg" <?= ($i['unite'] ?? '') === 'kg' ? 'selected' : '' ?>>KG</option><option value="litre" <?= ($i['unite'] ?? '') === 'litre' ? 'selected' : '' ?>>Litre</option></select>
                  <button type="submit" class="btn-add">OK</button>
                </form>
              </details>
              <form method="post" action="<?= hf($formAction) ?>" onsubmit="return confirm('Supprimer cet ingrédient ?');" style="margin-top:8px;">
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
      <div class="section-heading">Ingrédients par recette</div>
      <form id="recetteSelectForm" method="get" action="<?= hf($formAction) ?>" style="margin-bottom:12px;">
        <label for="front_id_recette">Choisir une recette :</label>
        <select id="front_id_recette" name="id_recette" onchange="submitRecetteSelectForm()">
          <?php foreach ($recipesWithFavoris as $r): ?>
            <option value="<?= (int) $r['id_recette'] ?>" <?= ((int) $r['id_recette'] === $selectedRecetteId) ? 'selected' : '' ?>>
              <?= hf((string) $r['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="scroll_y" value="0"/>
      </form>
      <table>
        <thead><tr><th>ID ingrédient</th><th>Nom</th><th>Bio</th><th>Local</th><th>Saisonnier</th><th>Quantité</th><th>Unité</th></tr></thead>
        <tbody>
        <?php if ($linkedIngredients === []): ?><tr><td colspan="7">Aucune association pour cette recette.</td></tr>
        <?php else: foreach ($linkedIngredients as $li): ?>
          <tr>
            <td><?= (int) $li['id_ingredient'] ?></td>
            <td><?= hf((string) $li['nom']) ?></td>
            <td><?= hf((string) $li['bio']) ?></td>
            <td><?= hf((string) $li['local']) ?></td>
            <td><?= hf((string) $li['saisonnier']) ?></td>
            <td><?= hf((string) ($li['quantite'] ?? 0)) ?></td>
            <td><?= hf((string) ($li['unite'] ?? 'piece')) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="form-modal-overlay" id="frontConfirmModal">
    <div class="confirm-modal">
      <div style="font-size:2.5rem;margin-bottom:0.75rem;">🗑️</div>
      <h3>Supprimer cette recette ?</h3>
      <p id="frontConfirmText"></p>
      <form method="post" action="<?= hf($formAction) ?>">
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="id_recette" id="front_delete_id" value=""/>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
          <button type="button" class="btn-cancel" onclick="closeFrontDeleteModal()">Annuler</button>
          <button type="submit" class="btn-danger">Supprimer</button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast" id="toast"><?= $flash !== null && $flash !== '' ? hf($flash) : '' ?></div>

  <script>
    window.__FRONT_RECIPES = <?= json_encode($recipesWithFavoris, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_FLASH = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_ERRORS = <?= json_encode($formErrors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_OLD = <?= json_encode($old, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_SCROLL_Y = <?= (int) ($scrollYOnLoad ?? 0) ?>;
  </script>
  <script src="../../assets/js/recettes-front.js" defer></script>
</body>
</html>
