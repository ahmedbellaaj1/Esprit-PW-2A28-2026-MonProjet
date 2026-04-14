<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class OrderModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function list(array $filters = []): array
    {
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '' && ctype_digit($q)) {
            $id = (int) $q;
            $stmt = $this->pdo->prepare('SELECT * FROM commandes WHERE id_commande = :id OR id_produit = :id OR id_utilisateur = :id ORDER BY id_commande DESC');
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll();
        }

        return $this->pdo->query('SELECT * FROM commandes ORDER BY id_commande DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM commandes WHERE id_commande = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO commandes (id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, adresse_livraison)
                VALUES (:id_produit, :id_utilisateur, :quantite, :prix_total, :date_commande, :statut, :adresse_livraison)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function createNow(array $data): void
    {
        $sql = 'INSERT INTO commandes (id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, adresse_livraison)
                VALUES (:id_produit, :id_utilisateur, :quantite, :prix_total, NOW(), :statut, :adresse_livraison)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE commandes SET id_produit = :id_produit, id_utilisateur = :id_utilisateur, quantite = :quantite,
                prix_total = :prix_total, date_commande = :date_commande, statut = :statut,
                adresse_livraison = :adresse_livraison WHERE id_commande = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM commandes WHERE id_commande = :id');
        $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
    }

    public function countPending(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM commandes WHERE statut IN ('en-cours', 'en-preparation')")->fetchColumn();
    }

    public function latest(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT id_commande, id_produit, id_utilisateur, quantite, prix_total, statut, date_commande FROM commandes ORDER BY date_commande DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
