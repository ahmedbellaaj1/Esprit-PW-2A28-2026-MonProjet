<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Ingredient.php';

final class IngredientController
{
    private ?bool $hasUniteColumnCache = null;
    private ?bool $hasQuantiteDecimalCache = null;
    private ?bool $hasIdUserColumnCache = null;

    private function hasUniteColumn(): bool
    {
        if ($this->hasUniteColumnCache !== null) {
            return $this->hasUniteColumnCache;
        }
        $stmt = getPdo()->query("SHOW COLUMNS FROM ingredient LIKE 'unite'");
        $this->hasUniteColumnCache = (bool) $stmt->fetch();
        return $this->hasUniteColumnCache;
    }

    private function ensureUniteColumn(): void
    {
        if ($this->hasUniteColumn()) {
            return;
        }
        getPdo()->exec("ALTER TABLE ingredient ADD COLUMN unite VARCHAR(20) NOT NULL DEFAULT 'piece'");
        $this->hasUniteColumnCache = true;
    }

    private function hasIdUserColumn(): bool
    {
        if ($this->hasIdUserColumnCache !== null) {
            return $this->hasIdUserColumnCache;
        }
        $stmt = getPdo()->query("SHOW COLUMNS FROM ingredient LIKE 'id_user'");
        $this->hasIdUserColumnCache = (bool) $stmt->fetch();
        return $this->hasIdUserColumnCache;
    }

    private function ensureIdUserColumn(): void
    {
        if ($this->hasIdUserColumn()) {
            return;
        }
        try {
            getPdo()->exec("ALTER TABLE ingredient ADD COLUMN id_user INT(10) UNSIGNED NULL DEFAULT NULL");
            getPdo()->exec("ALTER TABLE ingredient ADD KEY idx_id_user (id_user)");
            $this->hasIdUserColumnCache = true;
        } catch (\PDOException $e) {
            error_log('Erreur vérification colonne id_user: ' . $e->getMessage());
        }
    }

    private function hasQuantiteDecimal(): bool
    {
        if ($this->hasQuantiteDecimalCache !== null) {
            return $this->hasQuantiteDecimalCache;
        }
        $stmt = getPdo()->query("SHOW COLUMNS FROM ingredient LIKE 'quantite'");
        $col = $stmt->fetch();
        $type = strtolower((string) ($col['Type'] ?? ''));
        $this->hasQuantiteDecimalCache = str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double');
        return $this->hasQuantiteDecimalCache;
    }

    private function normalizeBooleanInput(mixed $raw, string $field, array &$errors): int
    {
        $value = strtolower(trim((string) $raw));
        if (in_array($value, ['oui', '1', 'true'], true)) {
            return 1;
        }
        if (in_array($value, ['non', '0', 'false', ''], true)) {
            return 0;
        }
        $errors[$field] = 'Valeur invalide, choisissez oui ou non.';
        return 0;
    }

    private function boolToOuiNon(mixed $value): string
    {
        return ((int) $value) === 1 ? 'oui' : 'non';
    }

    /** @param array<string,mixed> $row */
    private function mapIngredientRow(array $row): array
    {
        $row['bio'] = $this->boolToOuiNon($row['bio'] ?? 0);
        $row['local'] = $this->boolToOuiNon($row['local'] ?? 0);
        $row['saisonnier'] = $this->boolToOuiNon($row['saisonnier'] ?? 0);
        $row['quantite'] = (float) ($row['quantite'] ?? 0);
        $row['unite'] = (string) ($row['unite'] ?? 'piece');
        return $row;
    }

    private function normalizeQuantityInput(mixed $raw, array &$errors): float
    {
        $value = str_replace(',', '.', trim((string) $raw));
        if ($value === '' || !is_numeric($value)) {
            $errors['quantite'] = 'La quantité doit être un nombre (ex: 0,5).';
            return 0.0;
        }
        $q = (float) $value;
        if ($q <= 0) {
            $errors['quantite'] = 'La quantité doit être supérieure à 0.';
            return 0.0;
        }
        return $q;
    }

    private function findIngredientIdByName(string $nom): ?int
    {
        $stmt = getPdo()->prepare(
            'SELECT id_ingredient
             FROM ingredient
             WHERE LOWER(TRIM(nom)) = LOWER(TRIM(:nom))
             LIMIT 1'
        );
        $stmt->execute(['nom' => $nom]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    /** @return array{errors: array<string,string>, data: array<string,mixed>} */
    private function normalizeInput(array $post, bool $isUpdate = false): array
    {
        $errors = [];
        $nom = trim((string) ($post['nom'] ?? ''));
        $bio = $this->normalizeBooleanInput($post['bio'] ?? 'non', 'bio', $errors);
        $local = $this->normalizeBooleanInput($post['local'] ?? 'non', 'local', $errors);
        $saisonnier = $this->normalizeBooleanInput($post['saisonnier'] ?? 'non', 'saisonnier', $errors);
        $quantite = $this->normalizeQuantityInput($post['quantite'] ?? '', $errors);
        $unite = strtolower(trim((string) ($post['unite'] ?? 'piece')));

        if ($nom === '') {
            $errors['nom'] = 'Le nom de l\'ingrédient est obligatoire.';
        } elseif (mb_strlen($nom) > 50) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 50 caractères.';
        } elseif (preg_match("/^[\p{L}\s'-]+$/u", $nom) !== 1) {
            $errors['nom'] = 'Le nom de l’ingrédient doit contenir uniquement des lettres.';
        }
        if (!in_array($unite, ['piece', 'kg', 'litre'], true)) {
            $errors['unite'] = 'L’unité doit être piece, kg ou litre.';
        }

        $id = null;
        if ($isUpdate) {
            $idRaw = $post['id_ingredient'] ?? '';
            if ($idRaw === '' || !ctype_digit((string) $idRaw)) {
                $errors['id_ingredient'] = 'Identifiant ingrédient invalide.';
            } else {
                $id = (int) $idRaw;
            }
        }

        return [
            'errors' => $errors,
            'data' => [
                'id_ingredient' => $id,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'unite' => $unite,
            ],
        ];
    }

    /** @return array{errors: array<string,string>, data: array<string,mixed>} */
    public function validateInput(array $post, bool $isUpdate = false): array
    {
        return $this->normalizeInput($post, $isUpdate);
    }

    public function handlePost(): ?string
    {
        $action = trim((string) ($_POST['action'] ?? ''));
        if ($action === '') {
            return null;
        }

        // Obtenir l'id de l'utilisateur actuellement logué (par défaut 1)
        $idUser = (int) ($_SESSION['user_id'] ?? 1);

        if ($action === 'create') {
            $v = $this->normalizeInput($_POST, false);
            if ($v['errors'] !== []) {
                $_SESSION['ingredient_form_errors'] = $v['errors'];
                $_SESSION['ingredient_form_old'] = $_POST;
                return 'error';
            }
            $this->createIngredient(
                (int) ($_POST['id_recette'] ?? 0),
                $v['data']['nom'],
                $v['data']['bio'],
                $v['data']['local'],
                $v['data']['saisonnier'],
                $v['data']['quantite'],
                $v['data']['unite'],
                $idUser
            );
            $_SESSION['ingredient_flash'] = 'Ingrédient ajouté avec succès.';
            return 'redirect';
        }

        if ($action === 'update') {
            $v = $this->normalizeInput($_POST, true);
            if ($v['errors'] !== []) {
                $_SESSION['ingredient_form_errors'] = $v['errors'];
                $_SESSION['ingredient_form_old'] = $_POST;
                return 'error';
            }
            $idIngredient = (int) $v['data']['id_ingredient'];
            
            // Vérifier que l'utilisateur peut modifier cet ingrédient
            if (!$this->canModifyIngredient($idIngredient, $idUser)) {
                $_SESSION['ingredient_flash'] = 'Vous n\'êtes pas autorisé à modifier cet ingrédient.';
                return 'redirect';
            }
            
            $this->updateIngredient(
                $idIngredient,
                $v['data']['nom'],
                $v['data']['bio'],
                $v['data']['local'],
                $v['data']['saisonnier'],
                $v['data']['quantite'],
                $v['data']['unite']
            );
            $_SESSION['ingredient_flash'] = 'Ingrédient mis à jour.';
            return 'redirect';
        }

        if ($action === 'delete') {
            $id = filter_var($_POST['id_ingredient'] ?? '', FILTER_VALIDATE_INT);
            if ($id === false || $id < 0) {
                $_SESSION['ingredient_flash'] = 'Suppression impossible : identifiant invalide.';
                return 'redirect';
            }
            
            // Vérifier que l'utilisateur peut supprimer cet ingrédient
            if (!$this->canModifyIngredient($id, $idUser)) {
                $_SESSION['ingredient_flash'] = 'Vous n\'êtes pas autorisé à supprimer cet ingrédient.';
                return 'redirect';
            }
            
            $ok = $this->deleteIngredient($id);
            $_SESSION['ingredient_flash'] = $ok
                ? 'Ingrédient supprimé.'
                : 'Suppression impossible.';
            return 'redirect';
        }

        if ($action === 'link') {
            $idRecette = filter_var($_POST['id_recette'] ?? '', FILTER_VALIDATE_INT);
            $idIngredient = filter_var($_POST['id_ingredient'] ?? '', FILTER_VALIDATE_INT);
            if ($idRecette === false || $idRecette < 0 || $idIngredient === false || $idIngredient < 0) {
                $_SESSION['ingredient_flash'] = 'Association impossible : identifiants invalides.';
                return 'redirect';
            }
            $this->linkIngredientToRecette((int) $idRecette, (int) $idIngredient);
            $_SESSION['ingredient_flash'] = 'Association recette-ingrédient enregistrée.';
            return 'redirect';
        }

        if ($action === 'unlink') {
            $idRecette = filter_var($_POST['id_recette'] ?? '', FILTER_VALIDATE_INT);
            $idIngredient = filter_var($_POST['id_ingredient'] ?? '', FILTER_VALIDATE_INT);
            if ($idRecette === false || $idRecette < 0 || $idIngredient === false || $idIngredient < 0) {
                $_SESSION['ingredient_flash'] = 'Dissociation impossible : identifiants invalides.';
                return 'redirect';
            }
            $this->unlinkIngredientFromRecette((int) $idRecette, (int) $idIngredient);
            $_SESSION['ingredient_flash'] = 'Association supprimée.';
            return 'redirect';
        }

        return null;
    }

    public function allIngredients(): array
    {
        $fields = $this->hasUniteColumn()
            ? 'id_ingredient, nom, bio, `local`, saisonnier, quantite, unite'
            : "id_ingredient, nom, bio, `local`, saisonnier, quantite, 'piece' AS unite";
        $stmt = getPdo()->query("SELECT {$fields} FROM ingredient ORDER BY id_ingredient DESC");
        $rows = $stmt->fetchAll();
        return array_map(fn (array $row): array => $this->mapIngredientRow($row), $rows);
    }

    public function oneIngredient(int $id): ?array
    {
        $this->ensureIdUserColumn();
        $fields = $this->hasUniteColumn()
            ? 'id_ingredient, nom, bio, `local`, saisonnier, quantite, unite, id_recette, id_user'
            : "id_ingredient, nom, bio, `local`, saisonnier, quantite, 'piece' AS unite, id_recette, id_user";
        $stmt = getPdo()->prepare("SELECT {$fields} FROM ingredient WHERE id_ingredient = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapIngredientRow($row) : null;
    }

    public function createIngredient(int $idRecette, string $nom, int $bio, int $local, int $saisonnier, float $quantite, string $unite = 'piece', ?int $idUser = null): int
    {
        $this->ensureUniteColumn();
        $this->ensureIdUserColumn();
        $nextIdStmt = getPdo()->query('SELECT COALESCE(MAX(id_ingredient), -1) + 1 AS next_id FROM ingredient');
        $nextId = (int) ($nextIdStmt->fetch()['next_id'] ?? 0);

        if ($this->hasUniteColumn() && $this->hasIdUserColumn()) {
            $stmt = getPdo()->prepare(
                'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier, quantite, unite, id_recette, id_user)
                 VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier, :quantite, :unite, :id_recette, :id_user)'
            );
            $stmt->execute([
                'id_ingredient' => $nextId,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'unite' => $unite,
                'id_recette' => $idRecette,
                'id_user' => $idUser,
            ]);
        } elseif ($this->hasUniteColumn()) {
            $stmt = getPdo()->prepare(
                'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier, quantite, unite, id_recette)
                 VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier, :quantite, :unite, :id_recette)'
            );
            $stmt->execute([
                'id_ingredient' => $nextId,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'unite' => $unite,
                'id_recette' => $idRecette,
            ]);
        } else {
            $stmt = getPdo()->prepare(
                'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier, quantite, id_recette)
                 VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier, :quantite, :id_recette)'
            );
            $stmt->execute([
                'id_ingredient' => $nextId,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'id_recette' => $idRecette,
            ]);
        }
        return $nextId;
    }

    public function updateIngredient(int $id, string $nom, int $bio, int $local, int $saisonnier, float $quantite, string $unite = 'piece'): bool
    {
        $this->ensureUniteColumn();
        if ($this->hasUniteColumn()) {
            $stmt = getPdo()->prepare(
                'UPDATE ingredient
                 SET nom = :nom, bio = :bio, `local` = :local, saisonnier = :saisonnier, quantite = :quantite, unite = :unite
                 WHERE id_ingredient = :id'
            );
            return $stmt->execute([
                'id' => $id,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'unite' => $unite,
            ]);
        }
        $stmt = getPdo()->prepare(
            'UPDATE ingredient
             SET nom = :nom, bio = :bio, `local` = :local, saisonnier = :saisonnier, quantite = :quantite
             WHERE id_ingredient = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'bio' => $bio,
            'local' => $local,
            'saisonnier' => $saisonnier,
            'quantite' => $quantite,
        ]);
    }

    public function deleteIngredientsByRecette(int $idRecette): void
    {
        $stmt = getPdo()->prepare('DELETE FROM ingredient WHERE id_recette = :id_recette');
        $stmt->execute(['id_recette' => $idRecette]);
    }

    public function deleteIngredient(int $id): bool
    {
        $stmt = getPdo()->prepare('DELETE FROM ingredient WHERE id_ingredient = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function allRecettes(): array
    {
        $stmt = getPdo()->query('SELECT id_recette, nom FROM recette ORDER BY nom ASC');
        return $stmt->fetchAll();
    }



    public function linkIngredientToRecette(int $idRecette, int $idIngredient, ?float $quantite = null, ?string $unite = null): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE ingredient SET id_recette = :id_recette, quantite = :quantite, unite = :unite
             WHERE id_ingredient = :id_ingredient'
        );
        return $stmt->execute([
            'id_recette' => $idRecette,
            'id_ingredient' => $idIngredient,
            'quantite' => $quantite ?? 1.0,
            'unite' => $unite ?? 'piece',
        ]);
    }

    public function ingredientsByRecette(int $idRecette): array
    {
        $this->ensureIdUserColumn();
        $fields = $this->hasUniteColumn()
            ? 'id_ingredient, nom, bio, `local`, saisonnier, quantite, unite, id_user'
            : "id_ingredient, nom, bio, `local`, saisonnier, quantite, 'piece' AS unite, id_user";
        $stmt = getPdo()->prepare(
            "SELECT {$fields} FROM ingredient WHERE id_recette = :id_recette ORDER BY id_ingredient ASC"
        );
        $stmt->execute(['id_recette' => $idRecette]);
        $rows = $stmt->fetchAll();
        return array_map(fn (array $row): array => $this->mapIngredientRow($row), $rows);
    }

    /**
     * Vérifie si l'utilisateur peut modifier/supprimer cet ingrédient
     * L'utilisateur ne peut modifier que ses propres ingrédients
     * Les ingrédients admin (id_user NULL) ne peuvent pas être modifiés en front
     */
    public function canModifyIngredient(int $idIngredient, int $idUser): bool
    {
        $ingredient = $this->oneIngredient($idIngredient);
        if ($ingredient === null) {
            return false;
        }
        
        // Si c'est un ingrédient admin (id_user NULL), l'utilisateur ne peut pas le modifier
        if ($ingredient['id_user'] === null) {
            return false;
        }
        
        // L'utilisateur ne peut modifier que ses propres ingrédients
        return (int) $ingredient['id_user'] === $idUser;
    }

    /**
     * Vérifie si un ingrédient est un ingrédient admin
     */
    public function isAdminIngredient(int $idIngredient): bool
    {
        $ingredient = $this->oneIngredient($idIngredient);
        return $ingredient !== null && $ingredient['id_user'] === null;
    }

    public function unlinkIngredientFromRecette(int $idRecette, int $idIngredient): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE ingredient SET id_recette = NULL
             WHERE id_ingredient = :id_ingredient AND id_recette = :id_recette'
        );
        return $stmt->execute([
            'id_ingredient' => $idIngredient,
            'id_recette' => $idRecette,
        ]);
    }

    /**
     * Récupère tous les ingrédients avec leurs informations d'utilisateur
     */
    public function allIngredientsWithUser(): array
    {
        $this->ensureIdUserColumn();
        $fields = $this->hasUniteColumn()
            ? 'id_ingredient, id_recette, nom, bio, `local`, saisonnier, quantite, unite, id_user'
            : "id_ingredient, id_recette, nom, bio, `local`, saisonnier, quantite, 'piece' AS unite, id_user";
        $stmt = getPdo()->query("SELECT {$fields} FROM ingredient ORDER BY id_ingredient DESC");
        $rows = $stmt->fetchAll();
        return array_map(fn (array $row): array => $this->mapIngredientRow($row), $rows);
    }
}
