<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ProductController.php';

$controller = new ProductController();
$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$categories = $controller->categories();

$product = [
    'nom' => '',
    'marque' => '',
    'code_barre' => '',
    'categorie' => '',
    'prix' => '',
    'calories' => '',
    'proteines' => '',
    'glucides' => '',
    'lipides' => '',
    'nutriscore' => 'C',
    'image' => '',
    'quantite_disponible' => '',
    'statut' => 'actif',
];

if ($isEdit) {
    $row = $controller->find($id);
    if (!$row) {
        redirect('products.php');
    }
    $product = $row;
}

$error = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->save($_POST, $isEdit ? $id : null);
    if (!$result['ok']) {
        $error = $result['error'];
        $errors = $result['errors'] ?? [];
        $product = array_merge($product, $result['data']);
    } else {
        redirect('products.php?ok=1');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Produit</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:14px;">
            <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" style="width:32px;height:32px;">
            <div class="brand" style="margin:0;">Admin GreenBite</div>
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="products.php">Gestion Produits</a>
        <a href="orders.php">Gestion Commandes</a>
        <a href="../front-office/index.php">Voir Front Office</a>
    </aside>

    <main class="main">
        <h1 class="page-title" style="margin-top:0;"><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un produit</h1>
        <p class="subtitle">Tous les attributs de la classe Produit sont disponibles.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card">
            <div class="row">
                <div>
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= h($product['nom']) ?>" placeholder="Ex: Yaourt nature">
                    <small style="color:#6b7280;">Combinaison de lettres et chiffres (pas uniquement chiffres)</small>
                    <?php if (isset($errors['nom'])): ?><small style="color:#b91c1c;"><?= h($errors['nom']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Marque</label>
                    <input type="text" name="marque" value="<?= h($product['marque']) ?>" placeholder="Ex: Danone">
                    <small style="color:#6b7280;">Combinaison de lettres et chiffres (pas uniquement chiffres)</small>
                    <?php if (isset($errors['marque'])): ?><small style="color:#b91c1c;"><?= h($errors['marque']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Code barre</label>
                    <input type="text" name="code_barre" value="<?= h($product['code_barre']) ?>" placeholder="8-20 chiffres" pattern="[0-9]{8,20}">
                    <small style="color:#6b7280;">Chiffres uniquement (8-20 caractères)</small>
                    <?php if (isset($errors['code_barre'])): ?><small style="color:#b91c1c;"><?= h($errors['code_barre']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Categorie 🏷️</label>
                    <select name="categorie">
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= h($cat) ?>" <?= $product['categorie'] === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categorie'])): ?><small style="color:#b91c1c;"><?= h($errors['categorie']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Prix</label>
                    <input type="number" name="prix" value="<?= h((string) $product['prix']) ?>" step="0.01" min="0" max="100000" placeholder="0.00">
                    <small style="color:#6b7280;">Chiffres uniquement (0-100000)</small>
                    <?php if (isset($errors['prix'])): ?><small style="color:#b91c1c;"><?= h($errors['prix']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Quantité disponible *</label>
                    <input type="number" name="quantite_disponible" value="<?= h((string) $product['quantite_disponible']) ?>" min="0" max="999999" placeholder="0" required>
                    <small style="color:#6b7280;">Chiffres uniquement (≥ 0)</small>
                    <?php if (isset($errors['quantite_disponible'])): ?><small style="color:#b91c1c;"><?= h($errors['quantite_disponible']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Calories</label>
                    <input type="number" name="calories" value="<?= h((string) $product['calories']) ?>" step="0.01" min="0.01" max="5000" placeholder="0.00" class="nutritional-field" required>
                    <small style="color:#6b7280;">Chiffres uniquement (> 0)</small>
                    <?php if (isset($errors['calories'])): ?><small style="color:#b91c1c;"><?= h($errors['calories']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Proteines</label>
                    <input type="number" name="proteines" value="<?= h((string) $product['proteines']) ?>" step="0.01" min="0.01" max="5000" placeholder="0.00" class="nutritional-field" required>
                    <small style="color:#6b7280;">Chiffres uniquement (> 0)</small>
                    <?php if (isset($errors['proteines'])): ?><small style="color:#b91c1c;"><?= h($errors['proteines']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Glucides</label>
                    <input type="number" name="glucides" value="<?= h((string) $product['glucides']) ?>" step="0.01" min="0.01" max="5000" placeholder="0.00" class="nutritional-field" required>
                    <small style="color:#6b7280;">Chiffres uniquement (> 0)</small>
                    <?php if (isset($errors['glucides'])): ?><small style="color:#b91c1c;"><?= h($errors['glucides']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Lipides</label>
                    <input type="number" name="lipides" value="<?= h((string) $product['lipides']) ?>" step="0.01" min="0.01" max="5000" placeholder="0.00" class="nutritional-field" required>
                    <small style="color:#6b7280;">Chiffres uniquement (> 0)</small>
                    <?php if (isset($errors['lipides'])): ?><small style="color:#b91c1c;"><?= h($errors['lipides']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Image (URL) *</label>
                    <input type="url" name="image" value="<?= h($product['image']) ?>" placeholder="https://example.com/image.jpg" required>
                    <small style="color:#6b7280;">URL obligatoire (http:// ou https://)</small>
                    <?php if (isset($errors['image'])): ?><small style="color:#b91c1c;"><?= h($errors['image']) ?></small><?php endif; ?>
                </div>
                <div>
                    <label>Statut</label>
                    <select name="statut">
                        <?php foreach (['actif', 'inactif', 'attente'] as $s): ?>
                            <option value="<?= $s ?>" <?= $product['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['statut'])): ?><small style="color:#b91c1c;"><?= h($errors['statut']) ?></small><?php endif; ?>
                </div>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
                <button class="btn" type="submit" id="submit-btn">Enregistrer</button>
                <a class="btn secondary" href="products.php">Annuler</a>
            </div>
        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const caloriesInput = document.querySelector('input[name="calories"]');
    const proteinesInput = document.querySelector('input[name="proteines"]');
    const glucidesInput = document.querySelector('input[name="glucides"]');
    const lipidesInput = document.querySelector('input[name="lipides"]');
    const nutriscoreDisplay = document.getElementById('nutriscore-display');
    const nutriscoreHidden = document.getElementById('nutriscore-hidden');
    const nutriscoreInfo = document.getElementById('nutriscore-info');
    const submitBtn = document.getElementById('submit-btn');
    
    // Fonction pour calculer le nutriscore
    function calculateNutriscore() {
        const calories = parseFloat(caloriesInput.value) || 0;
        const proteines = parseFloat(proteinesInput.value) || 0;
        const glucides = parseFloat(glucidesInput.value) || 0;
        const lipides = parseFloat(lipidesInput.value) || 0;
        
        // Vérifier que tous les champs sont remplis et > 0
        if (calories <= 0 || proteines <= 0 || glucides <= 0 || lipides <= 0) {
            nutriscoreDisplay.value = '?';
            nutriscoreHidden.value = '';
            nutriscoreInfo.textContent = '(remplissez tous les champs)';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            return;
        }
        
        // Calculer la moyenne
        const moyenne = (calories + glucides + proteines + lipides) / 4;
        
        // Déterminer le nutriscore basé sur la moyenne
        // NOTE: GreenBite n'accepte jamais les produits avec score E (mauvais pour la santé)
        let nutriscore;
        let description;
        if (moyenne < 250) {
            nutriscore = 'A';
            description = 'Très bon';
        } else if (moyenne < 500) {
            nutriscore = 'B';
            description = 'Bon';
        } else if (moyenne < 750) {
            nutriscore = 'C';
            description = 'Moyen';
        } else {
            // GreenBite: score maximum D pour maintenir une sélection de produits sains
            nutriscore = 'D';
            description = 'Moins bon (produit accepté, mais qualité nutritive limitée)';
        }
        
        // Mettre à jour l'affichage
        nutriscoreDisplay.value = nutriscore;
        nutriscoreHidden.value = nutriscore;
        nutriscoreInfo.textContent = '(' + description + ', moyenne: ' + moyenne.toFixed(2) + ')';
        
        // Activer le bouton de soumission
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
    
    // Ajouter les écouteurs d'événements sur les champs nutritionnels
    [caloriesInput, proteinesInput, glucidesInput, lipidesInput].forEach(input => {
        input.addEventListener('input', calculateNutriscore);
        input.addEventListener('change', calculateNutriscore);
    });
    
    // Calculer le nutriscore à l'initialisation
    calculateNutriscore();
    
    // Avant de soumettre le formulaire, s'assurer que le nutriscore est défini
    form.addEventListener('submit', function(e) {
        // Vérifier que tous les champs nutritionnels sont remplis
        const calories = parseFloat(caloriesInput.value) || 0;
        const proteines = parseFloat(proteinesInput.value) || 0;
        const glucides = parseFloat(glucidesInput.value) || 0;
        const lipides = parseFloat(lipidesInput.value) || 0;
        
        if (calories <= 0 || proteines <= 0 || glucides <= 0 || lipides <= 0) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs nutritionnels (calories, protéines, glucides, lipides) avec des valeurs supérieures à 0.');
            return false;
        }
        
        // Mettre à jour le nutriscore caché avant la soumission
        calculateNutriscore();
    });
});
</script>

</body>
</html>
