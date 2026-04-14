<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class ProductModel
{
    private PDO $pdo;

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

    public function create(array $data): void
    {
        $sql = 'INSERT INTO produits (nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, statut, date_ajout)
                VALUES (:nom, :marque, :code_barre, :categorie, :prix, :calories, :proteines, :glucides, :lipides, :nutriscore, :image, :statut, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE produits SET nom = :nom, marque = :marque, code_barre = :code_barre, categorie = :categorie,
                prix = :prix, calories = :calories, proteines = :proteines, glucides = :glucides,
                lipides = :lipides, nutriscore = :nutriscore, image = :image, statut = :statut
                WHERE id_produit = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produits WHERE id_produit = :id');
        $stmt->execute(['id' => $id]);
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
        $stmt = $this->pdo->prepare('SELECT id_produit, nom, marque, prix, statut FROM produits ORDER BY date_ajout DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
