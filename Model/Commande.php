<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Commande
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.*, c.id_commande AS id,
                    p.nom   AS produit_nom,
                    p.prix  AS produit_prix
             FROM commande c
             LEFT JOIN produit p ON c.id_produit = p.id_produit
             ORDER BY c.date_commande DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUtilisateur(int $id_utilisateur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, c.id_commande AS id,
                    p.nom  AS produit_nom,
                    p.prix AS produit_prix
             FROM commande c
             LEFT JOIN produit p ON c.id_produit = p.id_produit
             WHERE c.id_utilisateur = :id_utilisateur
             ORDER BY c.date_commande DESC'
        );
        $stmt->execute(['id_utilisateur' => $id_utilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, c.id_commande AS id,
                    p.nom AS produit_nom
             FROM commande c
             LEFT JOIN produit p ON c.id_produit = p.id_produit
             WHERE c.id_commande = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByStatut(string $statut): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, c.id_commande AS id,
                    p.nom AS produit_nom
             FROM commande c
             LEFT JOIN produit p ON c.id_produit = p.id_produit
             WHERE c.statut = :statut
             ORDER BY c.date_commande DESC'
        );
        $stmt->execute(['statut' => $statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO commande
                    (id_produit, id_utilisateur, quantite, prix_total, adresse_livraison, statut)
                VALUES
                    (:id_produit, :id_utilisateur, :quantite, :prix_total, :adresse_livraison, :statut)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_produit'        => $data['id_produit'],
            'id_utilisateur'    => $data['id_utilisateur'],
            'quantite'          => $data['quantite']          ?? 1,
            'prix_total'        => $data['prix_total']        ?? 0.00,
            'adresse_livraison' => $data['adresse_livraison'] ?? null,
            'statut'            => $data['statut']            ?? 'en_attente',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE commande SET statut = :statut WHERE id_commande = :id'
        );
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM commande WHERE id_commande = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM commande');
        return (int) $stmt->fetchColumn();
    }

    public function countByStatut(string $statut): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM commande WHERE statut = :statut');
        $stmt->execute(['statut' => $statut]);
        return (int) $stmt->fetchColumn();
    }

    public function totalRevenu(): float
    {
        $stmt = $this->pdo->query(
            "SELECT COALESCE(SUM(prix_total), 0) FROM commande WHERE statut = 'livree'"
        );
        return (float) $stmt->fetchColumn();
    }
}