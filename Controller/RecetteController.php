<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class RecetteController
{
    /** @return array{errors: array<string,string>, data: array<string,mixed>} */
    public function validateInput(array $post, bool $isUpdate = false): array
    {
        $errors = [];
        $nom = trim((string) ($post['nom'] ?? ''));
        $caloriesRaw = (string) ($post['calories'] ?? '');
        $description = trim((string) ($post['description'] ?? ''));

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
            $this->createRecipe($v['data']['nom'], $v['data']['calories'], $v['data']['description']);
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
            $this->updateRecipe($id, $v['data']['nom'], $v['data']['calories'], $v['data']['description']);
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
        return $this->handleAdminPost();
    }

    public function allRecipes(): array
    {
        return $this->findAllRecipes();
    }

    public function oneRecipe(int $id): ?array
    {
        return $this->findRecipeById($id);
    }

    public function findAllRecipes(): array
    {
        $stmt = getPdo()->query(
            'SELECT id_recette, nom, calories, description, date_creation FROM recette ORDER BY date_creation DESC'
        );
        return $stmt->fetchAll();
    }

    public function findRecipeById(int $id): ?array
    {
        $stmt = getPdo()->prepare(
            'SELECT id_recette, nom, calories, description, date_creation FROM recette WHERE id_recette = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createRecipe(string $nom, float $calories, string $description): int
    {
        $stmt = getPdo()->prepare(
            'INSERT INTO recette (nom, calories, description, date_creation)
             VALUES (:nom, :calories, :description, NOW())'
        );
        $stmt->execute([
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
        ]);
        return (int) getPdo()->lastInsertId();
    }

    public function updateRecipe(int $id, string $nom, float $calories, string $description): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE recette SET nom = :nom, calories = :calories, description = :description WHERE id_recette = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
        ]);
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
