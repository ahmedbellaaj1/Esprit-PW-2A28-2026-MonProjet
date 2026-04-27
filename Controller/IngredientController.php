<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Ingredient.php';

final class IngredientController
{
    private ?bool $hasUniteColumnCache = null;
    private ?bool $hasQuantiteDecimalCache = null;

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

        if ($action === 'create') {
            $v = $this->normalizeInput($_POST, false);
            if ($v['errors'] !== []) {
                $_SESSION['ingredient_form_errors'] = $v['errors'];
                $_SESSION['ingredient_form_old'] = $_POST;
                return 'error';
            }
            $this->createIngredient(
                $v['data']['nom'],
                $v['data']['bio'],
                $v['data']['local'],
                $v['data']['saisonnier'],
                $v['data']['quantite'],
                $v['data']['unite']
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
            $this->updateIngredient(
                (int) $v['data']['id_ingredient'],
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
            $ok = $this->deleteIngredient((int) $id);
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
        $fields = $this->hasUniteColumn()
            ? 'id_ingredient, nom, bio, `local`, saisonnier, quantite, unite'
            : "id_ingredient, nom, bio, `local`, saisonnier, quantite, 'piece' AS unite";
        $stmt = getPdo()->prepare("SELECT {$fields} FROM ingredient WHERE id_ingredient = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapIngredientRow($row) : null;
    }

    public function createIngredient(string $nom, int $bio, int $local, int $saisonnier, float $quantite, string $unite = 'piece'): int
    {
        $this->ensureUniteColumn();
        $existingId = $this->findIngredientIdByName($nom);
        if ($existingId !== null) {
            return $existingId;
        }
        $nextIdStmt = getPdo()->query('SELECT COALESCE(MAX(id_ingredient), -1) + 1 AS next_id FROM ingredient');
        $nextId = (int) ($nextIdStmt->fetch()['next_id'] ?? 0);

        if ($this->hasUniteColumn()) {
            $stmt = getPdo()->prepare(
                'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier, quantite, unite)
                 VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier, :quantite, :unite)'
            );
            $stmt->execute([
                'id_ingredient' => $nextId,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
                'unite' => $unite,
            ]);
        } else {
            $stmt = getPdo()->prepare(
                'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier, quantite)
                 VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier, :quantite)'
            );
            $stmt->execute([
                'id_ingredient' => $nextId,
                'nom' => $nom,
                'bio' => $bio,
                'local' => $local,
                'saisonnier' => $saisonnier,
                'quantite' => $quantite,
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

    public function deleteIngredient(int $id): bool
    {
        $pdo = getPdo();
        try {
            $pdo->prepare('DELETE FROM recette_ingredient WHERE id_ingredient = :id')->execute(['id' => $id]);
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1146) {
                throw $e;
            }
        }
        $stmt = $pdo->prepare('DELETE FROM ingredient WHERE id_ingredient = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function allRecettes(): array
    {
        $stmt = getPdo()->query('SELECT id_recette, nom FROM recette ORDER BY nom ASC');
        return $stmt->fetchAll();
    }

    private function hasRecetteIngredientQuantiteColumn(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $stmt = getPdo()->query("SHOW COLUMNS FROM recette_ingredient LIKE 'quantite'");
        $cache = (bool) $stmt->fetch();
        return $cache;
    }

    private function ensureRecetteIngredientQuantiteColumns(): void
    {
        if ($this->hasRecetteIngredientQuantiteColumn()) {
            return;
        }
        getPdo()->exec("ALTER TABLE recette_ingredient ADD COLUMN quantite DECIMAL(10,3) DEFAULT 1.0");
        getPdo()->exec("ALTER TABLE recette_ingredient ADD COLUMN unite VARCHAR(20) DEFAULT 'piece'");
    }

    public function linkIngredientToRecette(int $idRecette, int $idIngredient, ?float $quantite = null, ?string $unite = null): bool
    {
        $this->ensureRecetteIngredientQuantiteColumns();
        
        $exists = getPdo()->prepare(
            'SELECT 1 FROM recette_ingredient WHERE id_recette = :id_recette AND id_ingredient = :id_ingredient'
        );
        $exists->execute([
            'id_recette' => $idRecette,
            'id_ingredient' => $idIngredient,
        ]);
        if ($exists->fetchColumn()) {
            if ($quantite !== null || $unite !== null) {
                $updateStmt = getPdo()->prepare(
                    'UPDATE recette_ingredient SET quantite = :quantite, unite = :unite 
                     WHERE id_recette = :id_recette AND id_ingredient = :id_ingredient'
                );
                return $updateStmt->execute([
                    'id_recette' => $idRecette,
                    'id_ingredient' => $idIngredient,
                    'quantite' => $quantite ?? 1.0,
                    'unite' => $unite ?? 'piece',
                ]);
            }
            return true;
        }

        $stmt = getPdo()->prepare(
            'INSERT INTO recette_ingredient (id_recette, id_ingredient, quantite, unite)
             VALUES (:id_recette, :id_ingredient, :quantite, :unite)'
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
        $this->ensureRecetteIngredientQuantiteColumns();
        
        $stmt = getPdo()->prepare(
            'SELECT i.id_ingredient, i.nom, i.bio, i.`local` AS `local`, i.saisonnier, ' .
            'COALESCE(ri.quantite, i.quantite) AS quantite, ' .
            'COALESCE(ri.unite, ' . ($this->hasUniteColumn() ? 'i.unite' : "'piece'") . ') AS unite ' .
            'FROM recette_ingredient ri
             INNER JOIN ingredient i ON i.id_ingredient = ri.id_ingredient
             WHERE ri.id_recette = :id_recette'
        );
        $stmt->execute(['id_recette' => $idRecette]);
        $rows = $stmt->fetchAll();
        return array_map(fn (array $row): array => $this->mapIngredientRow($row), $rows);
    }

    public function unlinkIngredientFromRecette(int $idRecette, int $idIngredient): bool
    {
        $stmt = getPdo()->prepare(
            'DELETE FROM recette_ingredient
             WHERE id_recette = :id_recette AND id_ingredient = :id_ingredient'
        );
        return $stmt->execute([
            'id_recette' => $idRecette,
            'id_ingredient' => $idIngredient,
        ]);
    }
}
