<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../Controller/IngredientController.php';

$controller = new IngredientController();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $controller->handlePost();
        if ($result === 'redirect') {
            header('Location: ' . basename(__FILE__));
            exit;
        }
    } catch (\Throwable $e) {
        $_SESSION['ingredient_flash'] = 'Erreur SQL: ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
}

$ingredients = $controller->allIngredients();
$recettes = $controller->allRecettes();
$flash = $_SESSION['ingredient_flash'] ?? null;
unset($_SESSION['ingredient_flash']);
$formErrors = $_SESSION['ingredient_form_errors'] ?? [];
unset($_SESSION['ingredient_form_errors']);
$old = $_SESSION['ingredient_form_old'] ?? [];
unset($_SESSION['ingredient_form_old']);

$selectedRecetteId = (int) ($_GET['id_recette'] ?? ($recettes[0]['id_recette'] ?? 0));
$linkedIngredients = $selectedRecetteId >= 0 ? $controller->ingredientsByRecette($selectedRecetteId) : [];

function h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenBite Back-office - Ingrédients</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style.css"/>
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-logo">Green<span>Bite</span></div>
    <div class="sidebar-role">Administration</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="recettes.php"><span class="icon">🍽️</span> Recettes</a>
      <a class="sidebar-link active" href="ingredients.php"><span class="icon">🥕</span> Ingrédients</a>
    </nav>
  </aside>

  <div class="dashboard-main">
    <header class="dashboard-header">
      <div class="header-title">Ingrédients & associations</div>
      <div class="header-right"><span class="header-badge">🟢 Ingredient</span></div>
    </header>

    <div class="page-content">
      <?php if ($flash !== null): ?>
        <div class="field-error" style="margin-bottom:12px;"><?= h($flash) ?></div>
      <?php endif; ?>
      <?php if ($error !== null): ?>
        <div class="field-error" style="margin-bottom:12px;"><?= h($error) ?></div>
      <?php endif; ?>

      <div class="table-container" style="margin-bottom:16px;">
        <h2 style="margin:0 0 12px 0;">Ajouter un ingrédient</h2>
        <form method="post" action="ingredients.php" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;align-items:end;">
          <input type="hidden" name="action" value="create"/>
          <div class="form-group" style="margin:0;">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= h((string) ($old['nom'] ?? '')) ?>" maxlength="150" required/>
            <?php if (isset($formErrors['nom'])): ?><div class="field-error"><?= h($formErrors['nom']) ?></div><?php endif; ?>
          </div>
          <div class="form-group" style="margin:0;">
            <label for="bio">Bio</label>
            <select id="bio" name="bio">
              <option value="non" <?= (($old['bio'] ?? 'non') === 'non') ? 'selected' : '' ?>>non</option>
              <option value="oui" <?= (($old['bio'] ?? '') === 'oui') ? 'selected' : '' ?>>oui</option>
            </select>
          </div>
          <div class="form-group" style="margin:0;">
            <label for="local">Local</label>
            <select id="local" name="local">
              <option value="non" <?= (($old['local'] ?? 'non') === 'non') ? 'selected' : '' ?>>non</option>
              <option value="oui" <?= (($old['local'] ?? '') === 'oui') ? 'selected' : '' ?>>oui</option>
            </select>
          </div>
          <div class="form-group" style="margin:0;">
            <label for="saisonnier">Saisonnier</label>
            <select id="saisonnier" name="saisonnier">
              <option value="non" <?= (($old['saisonnier'] ?? 'non') === 'non') ? 'selected' : '' ?>>non</option>
              <option value="oui" <?= (($old['saisonnier'] ?? '') === 'oui') ? 'selected' : '' ?>>oui</option>
            </select>
          </div>
          <button type="submit" class="primary-btn" style="padding:10px 16px;">Ajouter</button>
        </form>
      </div>

      <div class="table-container" style="margin-bottom:16px;">
        <h2 style="margin:0 0 12px 0;">Liste des ingrédients</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Bio</th>
              <th>Local</th>
              <th>Saisonnier</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($ingredients === []): ?>
              <tr><td colspan="6">Aucun ingrédient.</td></tr>
            <?php else: ?>
              <?php foreach ($ingredients as $i): ?>
                <tr>
                  <td><?= (int) $i['id_ingredient'] ?></td>
                  <td><?= h((string) $i['nom']) ?></td>
                  <td><?= h((string) $i['bio']) ?></td>
                  <td><?= h((string) $i['local']) ?></td>
                  <td><?= h((string) $i['saisonnier']) ?></td>
                  <td>
                    <details>
                      <summary style="cursor:pointer;">Modifier</summary>
                      <form method="post" action="ingredients.php" style="display:grid;grid-template-columns:repeat(5,minmax(120px,1fr));gap:8px;margin-top:8px;">
                        <input type="hidden" name="action" value="update"/>
                        <input type="hidden" name="id_ingredient" value="<?= (int) $i['id_ingredient'] ?>"/>
                        <input type="text" name="nom" value="<?= h((string) $i['nom']) ?>" required maxlength="150"/>
                        <select name="bio">
                          <option value="non" <?= $i['bio'] === 'non' ? 'selected' : '' ?>>Non</option>
                          <option value="oui" <?= $i['bio'] === 'oui' ? 'selected' : '' ?>>Oui</option>
                        </select>
                        <select name="local">
                          <option value="non" <?= $i['local'] === 'non' ? 'selected' : '' ?>>Non</option>
                          <option value="oui" <?= $i['local'] === 'oui' ? 'selected' : '' ?>>Oui</option>
                        </select>
                        <select name="saisonnier">
                          <option value="non" <?= $i['saisonnier'] === 'non' ? 'selected' : '' ?>>Non</option>
                          <option value="oui" <?= $i['saisonnier'] === 'oui' ? 'selected' : '' ?>>Oui</option>
                        </select>
                        <button type="submit" class="btn-add">Enregistrer</button>
                      </form>
                    </details>
                    <form method="post" action="ingredients.php" style="display:inline;" onsubmit="return confirm('Supprimer cet ingrédient ?');">
                      <input type="hidden" name="action" value="delete"/>
                      <input type="hidden" name="id_ingredient" value="<?= (int) $i['id_ingredient'] ?>"/>
                      <button type="submit" class="btn-icon btn-del" title="Supprimer">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="table-container">
        <h2 style="margin:0 0 12px 0;">Associer un ingrédient à une recette</h2>
        <form method="post" action="ingredients.php" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
          <input type="hidden" name="action" value="link"/>
          <div class="form-group" style="margin:0;">
            <label for="id_recette">Recette</label>
            <select id="id_recette" name="id_recette" required>
              <?php foreach ($recettes as $r): ?>
                <option value="<?= (int) $r['id_recette'] ?>" <?= ((int) $r['id_recette'] === $selectedRecetteId) ? 'selected' : '' ?>>
                  <?= h((string) $r['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;">
            <label for="id_ingredient">Ingrédient</label>
            <select id="id_ingredient" name="id_ingredient" required>
              <?php foreach ($ingredients as $i): ?>
                <option value="<?= (int) $i['id_ingredient'] ?>"><?= h((string) $i['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="primary-btn" style="padding:10px 16px;">Associer</button>
        </form>

        <form method="get" action="ingredients.php" style="margin-top:12px;">
          <label for="filter_recipe">Afficher les ingrédients de la recette :</label>
          <select id="filter_recipe" name="id_recette" onchange="this.form.submit()">
            <?php foreach ($recettes as $r): ?>
              <option value="<?= (int) $r['id_recette'] ?>" <?= ((int) $r['id_recette'] === $selectedRecetteId) ? 'selected' : '' ?>>
                <?= h((string) $r['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>

        <table style="margin-top:12px;">
          <thead>
            <tr>
              <th>ID ingrédient</th>
              <th>Nom</th>
              <th>Bio</th>
              <th>Local</th>
              <th>Saisonnier</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($linkedIngredients === []): ?>
              <tr><td colspan="6">Aucune association pour cette recette.</td></tr>
            <?php else: ?>
              <?php foreach ($linkedIngredients as $li): ?>
                <tr>
                  <td><?= (int) $li['id_ingredient'] ?></td>
                  <td><?= h((string) $li['nom']) ?></td>
                  <td><?= h((string) $li['bio']) ?></td>
                  <td><?= h((string) $li['local']) ?></td>
                  <td><?= h((string) $li['saisonnier']) ?></td>
                  <td>
                    <form method="post" action="ingredients.php" onsubmit="return confirm('Retirer cet ingrédient de la recette ?');">
                      <input type="hidden" name="action" value="unlink"/>
                      <input type="hidden" name="id_recette" value="<?= (int) $selectedRecetteId ?>"/>
                      <input type="hidden" name="id_ingredient" value="<?= (int) $li['id_ingredient'] ?>"/>
                      <button type="submit" class="btn-icon btn-del">🗑️</button>
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
</body>
</html>
