<?php
declare(strict_types=1);

/** @var array<int, array<string,mixed>> $recipes */
/** @var string|null $flash */
/** @var array<string,string> $formErrors */
/** @var array<string,mixed> $old */
/** @var string $formAction */

$formAction = $formAction ?? 'recettes.php';

function hf(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$count = count($recipes);
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
    <a class="navbar-logo" href="#">Green<span>Bite</span></a>
    <ul class="navbar-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="recettes.php" class="active">Recettes</a></li>
      <li><a href="ingredients.php">Ingrédients</a></li>
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
    </div>

    <div class="section-heading" style="justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <span>Recettes publiées <span id="countLabel" style="font-size:0.85rem;font-weight:400;color:#64748b;margin-left:0.5rem;">(<?= hf((string) $count) ?>)</span></span>
      <button type="button" class="primary-btn" style="padding:10px 22px;font-size:0.85rem;" onclick="openFrontModal('create')">+ Proposer une recette</button>
    </div>

    <div class="products-grid" id="recettesGrid">
      <?php if ($count === 0): ?>
        <p style="color:#64748b;grid-column:1/-1;">Aucune recette. Proposez la première !</p>
      <?php else: ?>
        <?php foreach ($recipes as $r): ?>
          <?php
            $cal = (float) $r['calories'];
            $band = $cal < 400 ? 'low' : ($cal <= 700 ? 'mid' : 'high');
          ?>
          <article class="product-card recette-card"
              data-id="<?= hf((string) $r['id_recette']) ?>"
              data-nom="<?= hf(strtolower((string) $r['nom'])) ?>"
              data-desc="<?= hf(strtolower((string) $r['description'])) ?>"
              data-cal="<?= hf((string) $cal) ?>"
              data-band="<?= hf($band) ?>">
            <div class="product-img" style="background:linear-gradient(135deg,#d1fae5,#6ee7b7);">
              <span style="font-size:3rem;">🥗</span>
            </div>
            <div class="product-body">
              <div class="product-brand">Recette durable</div>
              <div class="product-name"><?= hf((string) $r['nom']) ?></div>
              <div class="product-cal"><?= hf((string) $r['calories']) ?> kcal · <?= hf(substr((string) $r['date_creation'], 0, 10)) ?></div>
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
    window.__FRONT_RECIPES = <?= json_encode($recipes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_FLASH = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_ERRORS = <?= json_encode($formErrors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.__FRONT_RECETTE_OLD = <?= json_encode($old, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>
  <script src="../../assets/js/recettes-front.js" defer></script>
</body>
</html>
