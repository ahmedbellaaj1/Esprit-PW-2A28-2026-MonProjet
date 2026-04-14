<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/OrderModel.php';

final class OrderController
{
    private OrderModel $model;
    private const ALLOWED_STATUS = ['en-cours', 'en-preparation', 'confirmee', 'livree', 'annulee'];
    private const ALLOWED_FRONT_STATUS = ['en-cours', 'confirmee', 'annulee'];

    public function __construct()
    {
        $this->model = new OrderModel();
    }

    public function list(array $filters = []): array
    {
        return $this->model->list($filters);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function save(array $input, ?int $id = null): array
    {
        $data = [
            'id_produit' => (int) ($input['id_produit'] ?? 0),
            'id_utilisateur' => (int) ($input['id_utilisateur'] ?? 0),
            'quantite' => (int) ($input['quantite'] ?? 0),
            'prix_total' => (float) ($input['prix_total'] ?? 0),
            'date_commande' => trim((string) ($input['date_commande'] ?? date('Y-m-d H:i:s'))),
            'statut' => trim((string) ($input['statut'] ?? 'en-cours')),
            'adresse_livraison' => trim((string) ($input['adresse_livraison'] ?? '')),
        ];

        $errors = $this->validateForBackOffice($data);
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

    public function createFromFront(array $input): array
    {
        $idProduit = (int) ($input['id_produit'] ?? 0);
        $idUtilisateur = (int) ($input['id_utilisateur'] ?? 0);
        $quantite = (int) ($input['quantite'] ?? 0);
        $prixUnitaire = (float) ($input['prix_unitaire'] ?? 0);
        $statut = trim((string) ($input['statut'] ?? 'en-cours'));
        $adresse = trim((string) ($input['adresse_livraison'] ?? ''));

        $errors = [];

        if ($idProduit <= 0) {
            $errors['id_produit'] = 'Produit invalide.';
        }
        if ($idUtilisateur <= 0) {
            $errors['id_utilisateur'] = 'ID utilisateur invalide.';
        }

        if ($quantite < 1 || $quantite > 1000) {
            $errors['quantite'] = 'Quantite invalide (1 a 1000).';
        }

        if ($prixUnitaire <= 0 || $prixUnitaire > 100000) {
            $errors['prix_unitaire'] = 'Prix unitaire invalide.';
        }

        if (!in_array($statut, self::ALLOWED_FRONT_STATUS, true)) {
            $errors['statut'] = 'Statut de commande invalide.';
        }

        if (mb_strlen($adresse) < 10 || mb_strlen($adresse) > 255) {
            $errors['adresse_livraison'] = 'Adresse invalide (10 a 255 caracteres).';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'error' => 'Veuillez corriger les champs invalides.'];
        }

        $this->model->createNow([
            'id_produit' => $idProduit,
            'id_utilisateur' => $idUtilisateur,
            'quantite' => $quantite,
            'prix_total' => $prixUnitaire * $quantite,
            'statut' => $statut,
            'adresse_livraison' => $adresse,
        ]);

        return ['ok' => true];
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
            'pending' => $this->model->countPending(),
        ];
    }

    public function latest(int $limit = 5): array
    {
        return $this->model->latest($limit);
    }

    private function validateForBackOffice(array $data): array
    {
        $errors = [];

        if ($data['id_produit'] <= 0 || $data['id_utilisateur'] <= 0) {
            if ($data['id_produit'] <= 0) {
                $errors['id_produit'] = 'ID produit invalide.';
            }
            if ($data['id_utilisateur'] <= 0) {
                $errors['id_utilisateur'] = 'ID utilisateur invalide.';
            }
        }

        if ($data['quantite'] < 1 || $data['quantite'] > 1000) {
            $errors['quantite'] = 'La quantite doit etre comprise entre 1 et 1000.';
        }

        if ($data['prix_total'] < 0 || $data['prix_total'] > 1000000) {
            $errors['prix_total'] = 'Le prix total doit etre compris entre 0 et 1000000.';
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_commande']);
        if (!$date || $date->format('Y-m-d H:i:s') !== $data['date_commande']) {
            $errors['date_commande'] = 'Format attendu: YYYY-MM-DD HH:MM:SS.';
        }

        if (!in_array($data['statut'], self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut de commande est invalide.';
        }

        if (mb_strlen($data['adresse_livraison']) < 10 || mb_strlen($data['adresse_livraison']) > 255) {
            $errors['adresse_livraison'] = 'L\'adresse doit contenir entre 10 et 255 caracteres.';
        }

        return $errors;
    }
}
