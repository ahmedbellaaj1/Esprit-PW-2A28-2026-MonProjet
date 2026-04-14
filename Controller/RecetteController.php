<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Recette.php';

final class RecetteController
{
    private Recette $model;

    public function __construct()
    {
        $this->model = new Recette();
    }

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
            $this->model->create($v['data']['nom'], $v['data']['calories'], $v['data']['description']);
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
            $this->model->update($id, $v['data']['nom'], $v['data']['calories'], $v['data']['description']);
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
                $ok = $this->model->delete($id);
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
        return $this->model->findAll();
    }

    public function oneRecipe(int $id): ?array
    {
        return $this->model->findById($id);
    }
}
