<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../Controller/IngredientController.php';

$controller = new IngredientController();
$ingredients = $controller->allIngredients();
$recettes = $controller->allRecettes();
$selectedRecetteId = (int) ($_GET['id_recette'] ?? ($recettes[0]['id_recette'] ?? 0));
$linkedIngredients = $selectedRecetteId >= 0 ? $controller->ingredientsByRecette($selectedRecetteId) : [];

function hfi(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenBite – Ingrédients</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style.css"/>
</head>
<body>
  <nav class="navbar">
    <a class="navbar-logo" href="#">Green<span>Bite</span></a>
    <ul class="navbar-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="recettes.php">Recettes</a></li>
      <li><a href="ingredients.php" class="active">Ingrédients</a></li>
    </ul>
    <div class="navbar-right">
      <a class="primary-btn" style="padding:9px 20px;font-size:0.85rem;text-decoration:none;display:inline-block;" href="../back/ingredients.php">Espace admin</a>
    </div>
  </nav>

  <section class="hero-section">
    <h1>🥕 Ingrédients</h1>
    <p>Découvrir les ingrédients et leurs caractéristiques durables.</p>
  </section>

  <div class="main-container">
    <div class="table-container" style="margin-bottom:16px;">
      <div class="section-heading">Liste des ingrédients</div>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Bio</th>
            <th>Local</th>
            <th>Saisonnier</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($ingredients === []): ?>
            <tr><td colspan="5">Aucun ingrédient disponible.</td></tr>
          <?php else: ?>
            <?php foreach ($ingredients as $i): ?>
              <tr>
                <td><?= (int) $i['id_ingredient'] ?></td>
                <td><?= hfi((string) $i['nom']) ?></td>
                <td><?= hfi((string) $i['bio']) ?></td>
                <td><?= hfi((string) $i['local']) ?></td>
                <td><?= hfi((string) $i['saisonnier']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="table-container">
      <div class="section-heading">Ingrédients par recette</div>
      <form method="get" action="ingredients.php" style="margin-bottom:12px;">
        <label for="id_recette">Choisir une recette :</label>
        <select id="id_recette" name="id_recette" onchange="this.form.submit()">
          <?php foreach ($recettes as $r): ?>
            <option value="<?= (int) $r['id_recette'] ?>" <?= ((int) $r['id_recette'] === $selectedRecetteId) ? 'selected' : '' ?>>
              <?= hfi((string) $r['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
      <table>
        <thead>
          <tr>
            <th>ID ingrédient</th>
            <th>Nom</th>
            <th>Bio</th>
            <th>Local</th>
            <th>Saisonnier</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($linkedIngredients === []): ?>
            <tr><td colspan="5">Aucune association pour cette recette.</td></tr>
          <?php else: ?>
            <?php foreach ($linkedIngredients as $li): ?>
              <tr>
                <td><?= (int) $li['id_ingredient'] ?></td>
                <td><?= hfi((string) $li['nom']) ?></td>
                <td><?= hfi((string) $li['bio']) ?></td>
                <td><?= hfi((string) $li['local']) ?></td>
                <td><?= hfi((string) $li['saisonnier']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
