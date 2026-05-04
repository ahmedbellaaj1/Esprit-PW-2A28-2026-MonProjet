<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/IngredientController.php';

final class RecetteController
{
    private function ensureIdUserColumn(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        try {
            $stmt = getPdo()->query("SHOW COLUMNS FROM recette LIKE 'id_user'");
            if (!$stmt->fetch()) {
                getPdo()->exec("ALTER TABLE recette ADD COLUMN id_user INT(10) UNSIGNED NULL DEFAULT NULL");
            }
        } catch (\PDOException $e) {
            error_log('Erreur vérification colonne id_user: ' . $e->getMessage());
        }
        $checked = true;
    }

    /** @return array{errors: array<string,string>, data: array<string,mixed>} */
    public function validateInput(array $post, bool $isUpdate = false): array
    {
        $errors = [];
        $nom = trim((string) ($post['nom'] ?? ''));
        $caloriesRaw = (string) ($post['calories'] ?? '');
        $description = trim((string) ($post['description'] ?? ''));
        $dureePrep = trim((string) ($post['duree_prep'] ?? ''));
        $ingredientNoms = $post['ingredient_nom'] ?? [];
        $ingredientBios = $post['ingredient_bio'] ?? [];
        $ingredientLocals = $post['ingredient_local'] ?? [];
        $ingredientSaisonniers = $post['ingredient_saisonnier'] ?? [];
        $ingredientQuantites = $post['ingredient_quantite'] ?? [];
        $ingredientUnites = $post['ingredient_unite'] ?? [];

        if (!is_array($ingredientNoms)) {
            $ingredientNoms = [$ingredientNoms];
        }
        if (!is_array($ingredientBios)) {
            $ingredientBios = [$ingredientBios];
        }
        if (!is_array($ingredientLocals)) {
            $ingredientLocals = [$ingredientLocals];
        }
        if (!is_array($ingredientSaisonniers)) {
            $ingredientSaisonniers = [$ingredientSaisonniers];
        }
        if (!is_array($ingredientQuantites)) {
            $ingredientQuantites = [$ingredientQuantites];
        }
        if (!is_array($ingredientUnites)) {
            $ingredientUnites = [$ingredientUnites];
        }

        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($nom) > 150) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 150 caractères.';
        } elseif (preg_match('/\d/u', $nom) === 1 && preg_match('/[[:alpha:]]/u', $nom) !== 1) {
            $errors['nom'] = 'Si le nom contient des chiffres, il doit aussi contenir au moins une lettre.';
        }

        if ($caloriesRaw === '') {
            $errors['calories'] = 'Les calories sont obligatoires.';
        } elseif (!is_numeric($caloriesRaw)) {
            $errors['calories'] = 'Les calories doivent être un nombre.';
        } elseif ((float) $caloriesRaw < 0) {
            $errors['calories'] = 'Les calories ne peuvent pas être négatives.';
        }

        if ($description === '') {
            $errors['description'] = 'La description est obligatoire.';
        }

        if ($dureePrep === '') {
            $errors['duree_prep'] = 'La durée de préparation est obligatoire.';
        } elseif (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $dureePrep)) {
            $errors['duree_prep'] = 'Format invalide (HH:MM ou HH:MM:SS).';
        } elseif (strlen($dureePrep) === 5) {
            $dureePrep .= ':00';
        }
        if (!isset($errors['duree_prep'])) {
            [$h, $m, $s] = array_map('intval', explode(':', $dureePrep));
            if (($h * 3600 + $m * 60 + $s) < 60) {
                $errors['duree_prep'] = 'La durée minimale est 00:01.';
            }
        }

        $ingredientItems = [];
        $rowsCount = max(
            count($ingredientNoms),
            count($ingredientBios),
            count($ingredientLocals),
            count($ingredientSaisonniers),
            count($ingredientQuantites),
            count($ingredientUnites)
        );

        for ($i = 0; $i < $rowsCount; $i++) {
            $ingNom = trim((string) ($ingredientNoms[$i] ?? ''));
            $ingBio = strtolower(trim((string) ($ingredientBios[$i] ?? 'non')));
            $ingLocal = strtolower(trim((string) ($ingredientLocals[$i] ?? 'non')));
            $ingSaisonnier = strtolower(trim((string) ($ingredientSaisonniers[$i] ?? 'non')));
            $ingQuantiteRaw = str_replace(',', '.', trim((string) ($ingredientQuantites[$i] ?? '')));
            $ingUnite = strtolower(trim((string) ($ingredientUnites[$i] ?? 'piece')));

            if ($ingNom === '' && $ingQuantiteRaw === '' && $ingUnite === '') {
                continue;
            }

            if ($ingNom === '') {
                $errors["ingredient_nom_{$i}"] = 'Le nom de l’ingrédient est obligatoire.';
            } elseif (mb_strlen($ingNom) > 50) {
                $errors["ingredient_nom_{$i}"] = 'Le nom de l’ingrédient ne doit pas dépasser 50 caractères.';
            } elseif (preg_match("/^[\p{L}\s'-]+$/u", $ingNom) !== 1) {
                $errors["ingredient_nom_{$i}"] = 'Le nom de l’ingrédient doit contenir uniquement des lettres.';
            }

            foreach (
                [
                    "ingredient_bio_{$i}" => $ingBio,
                    "ingredient_local_{$i}" => $ingLocal,
                    "ingredient_saisonnier_{$i}" => $ingSaisonnier,
                ] as $field => $value
            ) {
                if (!in_array($value, ['oui', 'non'], true)) {
                    $errors[$field] = 'Choisissez oui ou non.';
                }
            }

            if ($ingQuantiteRaw === '' || !is_numeric($ingQuantiteRaw)) {
                $errors["ingredient_quantite_{$i}"] = 'La quantité de l’ingrédient doit être un nombre (ex: 0,5).';
            } elseif ((float) $ingQuantiteRaw <= 0) {
                $errors["ingredient_quantite_{$i}"] = 'La quantité de l’ingrédient doit être supérieure à 0.';
            }

            if (!in_array($ingUnite, ['piece', 'kg', 'litre'], true)) {
                $errors["ingredient_unite_{$i}"] = 'Unité invalide.';
            }

            $ingredientItems[] = [
                'nom' => $ingNom,
                'bio' => $ingBio,
                'local' => $ingLocal,
                'saisonnier' => $ingSaisonnier,
                'quantite' => $ingQuantiteRaw === '' ? 0.0 : (float) $ingQuantiteRaw,
                'unite' => $ingUnite === '' ? 'piece' : $ingUnite,
            ];
        }

        $id = null;
        if ($isUpdate) {
            $idRaw = $post['id_recette'] ?? '';
            if ($idRaw === '' || !ctype_digit((string) $idRaw)) {
                $errors['id_recette'] = 'Identifiant de recette invalide.';
            } else {
                $id = (int) $idRaw;
            }
        }

        return [
            'errors' => $errors,
            'data' => [
                'nom' => $nom,
                'calories' => $caloriesRaw === '' ? 0.0 : (float) $caloriesRaw,
                'description' => $description,
                'duree_prep' => $dureePrep,
                'ingredient_items' => $ingredientItems,
                'id_recette' => $id,
            ],
        ];
    }

    public function handleAdminPost(): ?string
    {
        $action = trim((string) ($_POST['action'] ?? ''));
        if ($action === '') {
            return null;
        }

        if ($action === 'create') {
            $v = $this->validateInput($_POST, false);
            if ($v['errors'] !== []) {
                $_SESSION['recette_form_errors'] = $v['errors'];
                $_SESSION['recette_form_old'] = $_POST;
                return 'error';
            }
            $recetteId = $this->createRecipe(
                $v['data']['nom'],
                $v['data']['calories'],
                $v['data']['description'],
                $v['data']['duree_prep']
            );
            $this->attachIngredientIfProvided($recetteId, $v['data'], null, false);
            $_SESSION['recette_flash'] = 'Recette ajoutée avec succès.';
            return 'redirect';
        }

        if ($action === 'update') {
            $v = $this->validateInput($_POST, true);
            if ($v['errors'] !== []) {
                $_SESSION['recette_form_errors'] = $v['errors'];
                $_SESSION['recette_form_old'] = $_POST;
                return 'error';
            }
            $id = (int) $v['data']['id_recette'];
            $this->updateRecipe(
                $id,
                $v['data']['nom'],
                $v['data']['calories'],
                $v['data']['description'],
                $v['data']['duree_prep']
            );
            $this->attachIngredientIfProvided($id, $v['data'], null, true);
            $_SESSION['recette_flash'] = 'Recette mise à jour.';
            return 'redirect';
        }

        if ($action === 'delete') {
            $idRaw = trim((string) ($_POST['delete_id_recette'] ?? $_POST['id_recette'] ?? ''));
            $id = filter_var($idRaw, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                $_SESSION['recette_flash'] = 'Suppression impossible : identifiant invalide.';
                return 'redirect';
            }
            try {
                $ok = $this->deleteRecipe($id);
                $_SESSION['recette_flash'] = $ok
                    ? 'Recette supprimée.'
                    : 'Suppression impossible (aucune ligne supprimée).';
            } catch (\Throwable $e) {
                $_SESSION['recette_flash'] = 'Erreur lors de la suppression. Vérifiez la base de données.';
            }
            return 'redirect';
        }

        return null;
    }

    public function handleFrontPost(): ?string
    {
        $action = trim((string) ($_POST['action'] ?? ''));
        if ($action === '') {
            return null;
        }

        // Obtenir l'id de l'utilisateur actuellement logué (par défaut 1)
        $idUser = (int) ($_SESSION['user_id'] ?? 1);

        if ($action === 'create') {
            $v = $this->validateInput($_POST, false);
            if ($v['errors'] !== []) {
                $_SESSION['recette_form_errors'] = $v['errors'];
                $_SESSION['recette_form_old'] = $_POST;
                return 'error';
            }
            // Créer la recette avec l'id de l'utilisateur
            $recetteId = $this->createRecipe(
                $v['data']['nom'],
                $v['data']['calories'],
                $v['data']['description'],
                $v['data']['duree_prep'],
                $idUser
            );
            $this->attachIngredientIfProvided($recetteId, $v['data'], $idUser, false);
            $_SESSION['recette_flash'] = 'Recette ajoutée avec succès.';
            return 'redirect';
        }

        if ($action === 'update') {
            $v = $this->validateInput($_POST, true);
            if ($v['errors'] !== []) {
                $_SESSION['recette_form_errors'] = $v['errors'];
                $_SESSION['recette_form_old'] = $_POST;
                return 'error';
            }
            $id = (int) $v['data']['id_recette'];
            
            // Vérifier que l'utilisateur peut modifier cette recette
            if (!$this->canModifyRecipe($id, $idUser)) {
                $_SESSION['recette_flash'] = 'Vous n\'êtes pas autorisé à modifier cette recette.';
                return 'redirect';
            }
            
            $this->updateRecipe(
                $id,
                $v['data']['nom'],
                $v['data']['calories'],
                $v['data']['description'],
                $v['data']['duree_prep']
            );
            $this->attachIngredientIfProvided($id, $v['data'], $idUser, true);
            $_SESSION['recette_flash'] = 'Recette mise à jour.';
            return 'redirect';
        }

        if ($action === 'delete') {
            $idRaw = trim((string) ($_POST['delete_id_recette'] ?? $_POST['id_recette'] ?? ''));
            $id = filter_var($idRaw, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                $_SESSION['recette_flash'] = 'Suppression impossible : identifiant invalide.';
                return 'redirect';
            }
            
            // Vérifier que l'utilisateur peut supprimer cette recette
            if (!$this->canModifyRecipe($id, $idUser)) {
                $_SESSION['recette_flash'] = 'Vous n\'êtes pas autorisé à supprimer cette recette.';
                return 'redirect';
            }
            
            try {
                $ok = $this->deleteRecipe($id);
                $_SESSION['recette_flash'] = $ok
                    ? 'Recette supprimée.'
                    : 'Suppression impossible (aucune ligne supprimée).';
            } catch (\Throwable $e) {
                $_SESSION['recette_flash'] = 'Erreur lors de la suppression. Vérifiez la base de données.';
            }
            return 'redirect';
        }

        return null;
    }

    public function allRecipes(?string $sortBy = null, ?string $sortOrder = 'ASC'): array
    {
        return $this->findAllRecipes($sortBy, $sortOrder);
    }

    public function oneRecipe(int $id): ?array
    {
        return $this->findRecipeById($id);
    }

    public function findAllRecipes(?string $sortBy = null, ?string $sortOrder = 'ASC'): array
    {
        $this->ensureIdUserColumn();
        
        $allowedSortColumns = ['date_creation', 'duree_prep', 'calories'];
        $allowedOrders = ['ASC', 'DESC'];
        
        $column = 'date_creation';
        $order = 'DESC'; // Default to newest first
        
        if (in_array($sortBy, $allowedSortColumns, true)) {
            $column = $sortBy;
        }
        if (in_array(strtoupper($sortOrder ?? 'ASC'), $allowedOrders, true)) {
            $order = strtoupper($sortOrder);
        }
        
        $sql = "SELECT id_recette, nom, calories, description, duree_prep, date_creation, id_user 
                FROM recette 
                ORDER BY $column $order";
                
        $stmt = getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function findRecipeById(int $id): ?array
    {
        $this->ensureIdUserColumn();
        $stmt = getPdo()->prepare(
            'SELECT id_recette, nom, calories, description, duree_prep, date_creation, id_user FROM recette WHERE id_recette = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createRecipe(string $nom, float $calories, string $description, string $dureePrep, ?int $idUser = null): int
    {
        $this->ensureIdUserColumn();
        $stmt = getPdo()->prepare(
            'INSERT INTO recette (nom, calories, description, duree_prep, date_creation, id_user)
             VALUES (:nom, :calories, :description, :duree_prep, NOW(), :id_user)'
        );
        $stmt->execute([
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
            'duree_prep' => $dureePrep,
            'id_user' => $idUser,
        ]);
        return (int) getPdo()->lastInsertId();
    }

    public function updateRecipe(int $id, string $nom, float $calories, string $description, string $dureePrep): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE recette
             SET nom = :nom, calories = :calories, description = :description, duree_prep = :duree_prep
             WHERE id_recette = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
            'duree_prep' => $dureePrep,
        ]);
    }

    /** @param array<string,mixed> $data */
    private function attachIngredientIfProvided(int $recetteId, array $data, ?int $idUser = null, bool $isUpdate = false): void
    {
        $items = $data['ingredient_items'] ?? [];
        if (!is_array($items) || $items === []) {
            return;
        }

        $ingredientController = new IngredientController();

        // On update: delete all existing ingredients first to avoid duplicates
        if ($isUpdate) {
            $ingredientController->deleteIngredientsByRecette($recetteId);
        }

        foreach ($items as $item) {
            $ingredientNom = trim((string) ($item['nom'] ?? ''));
            if ($ingredientNom === '') {
                continue;
            }
            $quantite = (float) ($item['quantite'] ?? 1);
            $unite = (string) ($item['unite'] ?? 'piece');

            $ingredientController->createIngredient(
                $recetteId,
                $ingredientNom,
                ((string) ($item['bio'] ?? 'non')) === 'oui' ? 1 : 0,
                ((string) ($item['local'] ?? 'non')) === 'oui' ? 1 : 0,
                ((string) ($item['saisonnier'] ?? 'non')) === 'oui' ? 1 : 0,
                $quantite,
                $unite,
                $idUser
            );
        }
    }

    /**
     * Vérifie si l'utilisateur peut modifier/supprimer cette recette
     * L'utilisateur ne peut modifier que ses propres recettes
     * Les recettes admin (id_user NULL) ne peuvent pas être modifiées en front
     */
    public function canModifyRecipe(int $idRecette, int $idUser): bool
    {
        $recipe = $this->findRecipeById($idRecette);
        if ($recipe === null) {
            return false;
        }
        
        // Si c'est une recette admin (id_user NULL), l'utilisateur ne peut pas la modifier
        if ($recipe['id_user'] === null) {
            return false;
        }
        
        // L'utilisateur ne peut modifier que ses propres recettes
        return (int) $recipe['id_user'] === $idUser;
    }

    /**
     * Vérifie si une recette est une recette admin
     */
    public function isAdminRecipe(int $idRecette): bool
    {
        $recipe = $this->findRecipeById($idRecette);
        return $recipe !== null && $recipe['id_user'] === null;
    }

    public function deleteRecipe(int $id): bool
    {
        if ($this->findRecipeById($id) === null) {
            return false;
        }

        $pdo = getPdo();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            try {
                $pdo->prepare('DELETE FROM recette_ingredient WHERE id_recette = :id')->execute(['id' => $id]);
            } catch (\PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) !== 1146) {
                    throw $e;
                }
            }
            $stmt = $pdo->prepare('DELETE FROM recette WHERE id_recette = :id');
            $stmt->execute(['id' => $id]);
            return $this->findRecipeById($id) === null;
        } finally {
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $ignored) {
            }
        }
    }
}
