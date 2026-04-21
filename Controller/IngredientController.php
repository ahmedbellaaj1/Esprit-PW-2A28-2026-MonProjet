<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Ingredient.php';

final class IngredientController
{
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
        return $row;
    }

    /** @return array{errors: array<string,string>, data: array<string,mixed>} */
    private function normalizeInput(array $post, bool $isUpdate = false): array
    {
        $errors = [];
        $nom = trim((string) ($post['nom'] ?? ''));
        $bio = $this->normalizeBooleanInput($post['bio'] ?? 'non', 'bio', $errors);
        $local = $this->normalizeBooleanInput($post['local'] ?? 'non', 'local', $errors);
        $saisonnier = $this->normalizeBooleanInput($post['saisonnier'] ?? 'non', 'saisonnier', $errors);

        if ($nom === '') {
            $errors['nom'] = 'Le nom de l\'ingrédient est obligatoire.';
        } elseif (mb_strlen($nom) > 150) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 150 caractères.';
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
                $v['data']['saisonnier']
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
                $v['data']['saisonnier']
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
        $stmt = getPdo()->query(
            'SELECT id_ingredient, nom, bio, `local`, saisonnier FROM ingredient ORDER BY id_ingredient DESC'
        );
        $rows = $stmt->fetchAll();
        return array_map(fn (array $row): array => $this->mapIngredientRow($row), $rows);
    }

    public function oneIngredient(int $id): ?array
    {
        $stmt = getPdo()->prepare(
            'SELECT id_ingredient, nom, bio, `local`, saisonnier FROM ingredient WHERE id_ingredient = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapIngredientRow($row) : null;
    }

    public function createIngredient(string $nom, int $bio, int $local, int $saisonnier): int
    {
        $nextIdStmt = getPdo()->query('SELECT COALESCE(MAX(id_ingredient), -1) + 1 AS next_id FROM ingredient');
        $nextId = (int) ($nextIdStmt->fetch()['next_id'] ?? 0);

        $stmt = getPdo()->prepare(
            'INSERT INTO ingredient (id_ingredient, nom, bio, `local`, saisonnier)
             VALUES (:id_ingredient, :nom, :bio, :local, :saisonnier)'
        );
        $stmt->execute([
            'id_ingredient' => $nextId,
            'nom' => $nom,
            'bio' => $bio,
            'local' => $local,
            'saisonnier' => $saisonnier,
        ]);
        return $nextId;
    }

    public function updateIngredient(int $id, string $nom, int $bio, int $local, int $saisonnier): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE ingredient
             SET nom = :nom, bio = :bio, `local` = :local, saisonnier = :saisonnier
             WHERE id_ingredient = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'bio' => $bio,
            'local' => $local,
            'saisonnier' => $saisonnier,
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

    public function linkIngredientToRecette(int $idRecette, int $idIngredient): bool
    {
        $exists = getPdo()->prepare(
            'SELECT 1 FROM recette_ingredient WHERE id_recette = :id_recette AND id_ingredient = :id_ingredient'
        );
        $exists->execute([
            'id_recette' => $idRecette,
            'id_ingredient' => $idIngredient,
        ]);
        if ($exists->fetchColumn()) {
            return true;
        }

        $stmt = getPdo()->prepare(
            'INSERT INTO recette_ingredient (id_recette, id_ingredient)
             VALUES (:id_recette, :id_ingredient)'
        );
        return $stmt->execute([
            'id_recette' => $idRecette,
            'id_ingredient' => $idIngredient,
        ]);
    }

    public function ingredientsByRecette(int $idRecette): array
    {
        $stmt = getPdo()->prepare(
            'SELECT i.id_ingredient, i.nom, i.bio, i.`local` AS `local`, i.saisonnier
             FROM recette_ingredient ri
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
