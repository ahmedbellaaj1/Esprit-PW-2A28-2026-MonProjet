<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/ProductModel.php';

final class ProductController
{
    private ProductModel $model;
    private const ALLOWED_NUTRISCORES = ['A', 'B', 'C', 'D', 'E'];
    private const ALLOWED_STATUS = ['actif', 'inactif', 'attente'];

    public function __construct()
    {
        $this->model = new ProductModel();
    }

    public function list(array $filters = []): array
    {
        return $this->model->list($filters);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function categories(): array
    {
        return $this->model->categories();
    }

    public function save(array $input, ?int $id = null): array
    {
        $data = [
            'nom' => trim((string) ($input['nom'] ?? '')),
            'marque' => trim((string) ($input['marque'] ?? '')),
            'code_barre' => trim((string) ($input['code_barre'] ?? '')),
            'categorie' => trim((string) ($input['categorie'] ?? '')),
            'prix' => (float) ($input['prix'] ?? 0),
            'calories' => (float) ($input['calories'] ?? 0),
            'proteines' => (float) ($input['proteines'] ?? 0),
            'glucides' => (float) ($input['glucides'] ?? 0),
            'lipides' => (float) ($input['lipides'] ?? 0),
            'nutriscore' => trim((string) ($input['nutriscore'] ?? 'C')),
            'image' => trim((string) ($input['image'] ?? '')),
            'statut' => trim((string) ($input['statut'] ?? 'actif')),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'error' => 'Veuillez corriger les champs invalides.', 'data' => $data];
        }

        if ($id && $id > 0) {
            $this->model->update($id, $data);
        } else {
            $this->model->create($data);
        }

        return ['ok' => true, 'data' => $data];
    }

    public function delete(int $id): void
    {
        if ($id > 0) {
            $this->model->delete($id);
        }
    }

    public function metrics(): array
    {
        return [
            'total' => $this->model->countAll(),
            'active' => $this->model->countByStatus('actif'),
        ];
    }

    public function latest(int $limit = 5): array
    {
        return $this->model->latest($limit);
    }

    private function validate(array &$data): array
    {
        $errors = [];

        if ($data['nom'] === '' || mb_strlen($data['nom']) < 2 || mb_strlen($data['nom']) > 150) {
            $errors['nom'] = 'Le nom doit contenir 2 a 150 caracteres.';
        }

        if ($data['marque'] === '' || mb_strlen($data['marque']) < 2 || mb_strlen($data['marque']) > 120) {
            $errors['marque'] = 'La marque doit contenir 2 a 120 caracteres.';
        }

        if ($data['code_barre'] !== '' && !preg_match('/^[0-9]{8,20}$/', $data['code_barre'])) {
            $errors['code_barre'] = 'Le code barre doit contenir 8 a 20 chiffres.';
        }

        if (mb_strlen($data['categorie']) > 120) {
            $errors['categorie'] = 'La categorie ne doit pas depasser 120 caracteres.';
        }

        if ($data['prix'] < 0 || $data['prix'] > 100000) {
            $errors['prix'] = 'Le prix doit etre compris entre 0 et 100000.';
        }

        foreach (['calories', 'proteines', 'glucides', 'lipides'] as $field) {
            if ($data[$field] < 0 || $data[$field] > 5000) {
                $errors[$field] = 'La valeur doit etre comprise entre 0 et 5000.';
            }
        }

        $data['nutriscore'] = strtoupper($data['nutriscore']);
        if (!in_array($data['nutriscore'], self::ALLOWED_NUTRISCORES, true)) {
            $errors['nutriscore'] = 'Le nutriscore est invalide.';
        }

        if ($data['image'] !== '' && filter_var($data['image'], FILTER_VALIDATE_URL) === false) {
            $errors['image'] = 'Le lien image doit etre une URL valide.';
        }

        if (!in_array($data['statut'], self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut est invalide.';
        }

        return $errors;
    }
}
