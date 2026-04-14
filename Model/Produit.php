<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Produit
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *, id_produit AS id FROM produit ORDER BY date_ajout DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDisponibles(): array
    {
        $stmt = $this->pdo->query(
            "SELECT *, id_produit AS id FROM produit WHERE statut = 'disponible' ORDER BY date_ajout DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *, id_produit AS id FROM produit WHERE id_produit = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByCodeBarre(string $code): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *, id_produit AS id FROM produit WHERE code_barre = :code LIMIT 1'
        );
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
{
    $sql = 'INSERT INTO produit 
            (nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, statut, date_ajout)
            VALUES 
            (:nom, :marque, :code_barre, :categorie, :prix, :calories, :proteines, :glucides, :lipides, :nutriscore, :image, :statut, NOW())';

    $stmt = $this->pdo->prepare($sql);
    
    $stmt->execute([
        'nom'        => $data['nom'],
        'marque'     => $data['marque']     ?? null,
        'code_barre' => $data['code_barre'] ?? null,
        'categorie'  => $data['categorie']  ?? null,
        'prix'       => $data['prix']        ?? 0.00,
        'calories'   => $data['calories']   ?? null,
        'proteines'  => $data['proteines']  ?? null,
        'glucides'   => $data['glucides']   ?? null,
        'lipides'    => $data['lipides']    ?? null,
        'nutriscore' => $data['nutriscore'] ?? 'C',
        'image'      => $data['image']      ?? null,
        'statut'     => $data['statut']     ?? 'disponible',
    ]);

    return (int) $this->pdo->lastInsertId();
}

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE produit
                SET nom        = :nom,
                    marque     = :marque,
                    code_barre = :code_barre,
                    categorie  = :categorie,
                    prix       = :prix,
                    calories   = :calories,
                    proteines  = :proteines,
                    glucides   = :glucides,
                    lipides    = :lipides,
                    nutriscore = :nutriscore,
                    statut     = :statut
                WHERE id_produit = :id';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'         => $id,
            'nom'        => $data['nom'],
            'marque'     => $data['marque']     ?? null,
            'code_barre' => $data['code_barre'] ?? null,
            'categorie'  => $data['categorie']  ?? null,
            'prix'       => $data['prix']        ?? 0.00,
            'calories'   => $data['calories']   ?? null,
            'proteines'  => $data['proteines']  ?? null,
            'glucides'   => $data['glucides']   ?? null,
            'lipides'    => $data['lipides']    ?? null,
            'nutriscore' => $data['nutriscore'] ?? 'C',
            'statut'     => $data['statut']     ?? 'disponible',
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM produit WHERE id_produit = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM produit');
        return (int) $stmt->fetchColumn();
    }

    public function countByStatut(string $statut): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produit WHERE statut = :statut');
        $stmt->execute(['statut' => $statut]);
        return (int) $stmt->fetchColumn();
    }
}