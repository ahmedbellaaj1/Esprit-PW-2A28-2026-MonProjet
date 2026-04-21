<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Product.php';
require_once __DIR__ . '/../config/database.php';

final class ProductController
{
    private PDO $pdo;
    private const ALLOWED_NUTRISCORES = ['A', 'B', 'C', 'D', 'E'];
    private const ALLOWED_STATUS = ['actif', 'inactif', 'attente'];

    public function __construct()
    {
        $this->pdo = Database::connection();
    }


    public function list(array $filters = []): array
    {
        $where = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        $categorie = trim((string) ($filters['categorie'] ?? ''));
        $nutriscore = trim((string) ($filters['nutriscore'] ?? ''));

        if ($q !== '') {
            $where[] = '(nom LIKE :q OR marque LIKE :q OR code_barre LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($categorie !== '') {
            $where[] = 'categorie = :categorie';
            $params['categorie'] = $categorie;
        }
        if ($nutriscore !== '') {
            $where[] = 'nutriscore = :nutriscore';
            $params['nutriscore'] = $nutriscore;
        }

        $sql = 'SELECT * FROM produits';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY id_produit DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM produits WHERE id_produit = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function categories(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT categorie FROM produits WHERE categorie IS NOT NULL AND categorie <> "" ORDER BY categorie');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM produits')->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produits WHERE statut = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function latest(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT id_produit, nom, marque, prix, statut, nutriscore FROM produits ORDER BY date_ajout DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function create(Product $product): void
    {
        $data = $product->toArray();
        $sql = 'INSERT INTO produits (nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, statut, date_ajout)
                VALUES (:nom, :marque, :code_barre, :categorie, :prix, :calories, :proteines, :glucides, :lipides, :nutriscore, :image, :statut, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':marque' => $data['marque'],
            ':code_barre' => $data['code_barre'],
            ':categorie' => $data['categorie'],
            ':prix' => $data['prix'],
            ':calories' => $data['calories'],
            ':proteines' => $data['proteines'],
            ':glucides' => $data['glucides'],
            ':lipides' => $data['lipides'],
            ':nutriscore' => $data['nutriscore'],
            ':image' => $data['image'],
            ':statut' => $data['statut'],
        ]);
    }

    private function update(int $id, Product $product): void
    {
        $data = $product->toArray();
        $sql = 'UPDATE produits SET nom = :nom, marque = :marque, code_barre = :code_barre, categorie = :categorie,
                prix = :prix, calories = :calories, proteines = :proteines, glucides = :glucides,
                lipides = :lipides, nutriscore = :nutriscore, image = :image, statut = :statut
                WHERE id_produit = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':marque' => $data['marque'],
            ':code_barre' => $data['code_barre'],
            ':categorie' => $data['categorie'],
            ':prix' => $data['prix'],
            ':calories' => $data['calories'],
            ':proteines' => $data['proteines'],
            ':glucides' => $data['glucides'],
            ':lipides' => $data['lipides'],
            ':nutriscore' => $data['nutriscore'],
            ':image' => $data['image'],
            ':statut' => $data['statut'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        if ($id > 0) {
            $stmt = $this->pdo->prepare('DELETE FROM produits WHERE id_produit = :id');
            $stmt->execute(['id' => $id]);
        }
    }



    public function save(array $input, ?int $id = null): array
    {
        $product = new Product();
        $product->setNom(trim((string) ($input['nom'] ?? '')));
        $product->setMarque(trim((string) ($input['marque'] ?? '')));
        $product->setCodeBarre(trim((string) ($input['code_barre'] ?? '')));
        $product->setCategorie(trim((string) ($input['categorie'] ?? '')));
        $product->setPrix((float) ($input['prix'] ?? 0));
        $product->setCalories((float) ($input['calories'] ?? 0));
        $product->setProteines((float) ($input['proteines'] ?? 0));
        $product->setGlucides((float) ($input['glucides'] ?? 0));
        $product->setLipides((float) ($input['lipides'] ?? 0));
        $product->setNutriscore(trim((string) ($input['nutriscore'] ?? 'C')));
        $product->setImage(trim((string) ($input['image'] ?? '')));
        $product->setStatut(trim((string) ($input['statut'] ?? 'actif')));

        $errors = $this->validate($product);
        if ($errors) {
            return [
                'ok' => false,
                'errors' => $errors,
                'error' => 'Veuillez corriger les champs invalides.',
                'data' => $product->toArray(),
            ];
        }

        if ($id && $id > 0) {
            $this->update($id, $product);
        } else {
            $this->create($product);
        }

        return ['ok' => true, 'data' => $product->toArray()];
    }

    public function metrics(): array
    {
        return [
            'total' => $this->countAll(),
            'active' => $this->countByStatus('actif'),
        ];
    }


    private function validate(Product $product): array
    {
        $errors = [];

        if ($product->getNom() === '' || mb_strlen($product->getNom()) < 2 || mb_strlen($product->getNom()) > 150) {
            $errors['nom'] = 'Le nom doit contenir 2 a 150 caracteres.';
        }

        if ($product->getMarque() === '' || mb_strlen($product->getMarque()) < 2 || mb_strlen($product->getMarque()) > 120) {
            $errors['marque'] = 'La marque doit contenir 2 a 120 caracteres.';
        }

        if ($product->getCodeBarre() !== '' && !preg_match('/^[0-9]{8,20}$/', $product->getCodeBarre())) {
            $errors['code_barre'] = 'Le code barre doit contenir 8 a 20 chiffres.';
        }

        if (mb_strlen($product->getCategorie()) > 120) {
            $errors['categorie'] = 'La categorie ne doit pas depasser 120 caracteres.';
        }

        if ($product->getPrix() < 0 || $product->getPrix() > 100000) {
            $errors['prix'] = 'Le prix doit etre compris entre 0 et 100000.';
        }

        foreach (['calories', 'proteines', 'glucides', 'lipides'] as $field) {
            $getter = 'get' . ucfirst($field);
            $value = $product->$getter();
            if ($value < 0 || $value > 5000) {
                $errors[$field] = 'La valeur doit etre comprise entre 0 et 5000.';
            }
        }

        if (!in_array($product->getNutriscore(), self::ALLOWED_NUTRISCORES, true)) {
            $errors['nutriscore'] = 'Le nutriscore est invalide.';
        }

        if ($product->getImage() !== '' && filter_var($product->getImage(), FILTER_VALIDATE_URL) === false) {
            $errors['image'] = 'Le lien image doit etre une URL valide.';
        }

        if (!in_array($product->getStatut(), self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut est invalide.';
        }

        return $errors;
    }
}
