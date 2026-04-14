<?php
// models/Don.php

require_once __DIR__ . '/../config/database.php';

class Don {

    private mysqli $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── READ ────────────────────────────────────────────────────

    /**
     * Retourne tous les dons avec leurs produits et le nom du partenaire.
     * Filtres optionnels : statut, recherche par produit.
     */
    public function getAll(string $statut = '', string $search = ''): array {
        $where  = [];
        $params = [];
        $types  = '';

        if ($statut !== '') {
            $where[]  = 'd.statut = ?';
            $params[] = $statut;
            $types   .= 's';
        }

        $sql = "SELECT d.id_don, d.statut, d.date_publication, d.id_user, d.id_partenaire,
                       p.nom AS partenaire_nom, p.type AS partenaire_type
                FROM Don d
                LEFT JOIN Partenaire p ON d.id_partenaire = p.id_partenaire";

        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY d.date_publication DESC';

        $stmt = $this->db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $dons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Charger les produits pour chaque don
        foreach ($dons as &$don) {
            $don['produits'] = $this->getProduitsByDon((int)$don['id_don']);
        }

        // Filtrer par nom de produit si search
        if ($search !== '') {
            $s    = strtolower($search);
            $dons = array_filter($dons, fn($d) =>
                array_reduce($d['produits'], fn($carry, $p) =>
                    $carry || str_contains(strtolower($p['nom_produit']), $s), false)
            );
            $dons = array_values($dons);
        }

        return $dons;
    }

    /**
     * Retourne un seul don (avec ses produits).
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT d.*, p.nom AS partenaire_nom, p.type AS partenaire_type
             FROM Don d
             LEFT JOIN Partenaire p ON d.id_partenaire = p.id_partenaire
             WHERE d.id_don = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $don = $stmt->get_result()->fetch_assoc();
        if (!$don) return null;
        $don['produits'] = $this->getProduitsByDon($id);
        return $don;
    }

    /**
     * Retourne les produits d'un don.
     */
    public function getProduitsByDon(int $id_don): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM Don_Produit WHERE id_don = ? ORDER BY id_ligne"
        );
        $stmt->bind_param('i', $id_don);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Statistiques agrégées par statut.
     */
    public function getStats(): array {
        $res   = $this->db->query("SELECT statut, COUNT(*) AS total FROM Don GROUP BY statut");
        $stats = ['disponible' => 0, 'réservé' => 0, 'récupéré' => 0];
        while ($row = $res->fetch_assoc()) {
            $stats[$row['statut']] = (int)$row['total'];
        }
        return $stats;
    }

    // ── CREATE ──────────────────────────────────────────────────

    /**
     * Crée un don + ses produits dans une transaction.
     * $data = ['statut', 'id_user', 'id_partenaire', 'produits' => [...]]
     */
    public function create(array $data): int|false {
        $this->db->begin_transaction();
        try {
            // 1. Insérer le don
            $stmt = $this->db->prepare(
                "INSERT INTO Don (statut, id_user, id_partenaire) VALUES (?, ?, ?)"
            );
            $statut        = $data['statut']        ?? 'disponible';
            $id_user       = (int)($data['id_user'] ?? 1);
            $id_partenaire = $data['id_partenaire'] ? (int)$data['id_partenaire'] : null;
            $stmt->bind_param('sii', $statut, $id_user, $id_partenaire);
            $stmt->execute();
            $id_don = $this->db->insert_id;

            // 2. Insérer chaque produit
            $this->insertProduits($id_don, $data['produits']);

            $this->db->commit();
            return $id_don;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // ── UPDATE ──────────────────────────────────────────────────

    /**
     * Met à jour un don et remplace tous ses produits.
     */
    public function update(int $id, array $data): bool {
        $this->db->begin_transaction();
        try {
            // 1. Mettre à jour le don
            $stmt = $this->db->prepare(
                "UPDATE Don SET statut = ?, id_partenaire = ? WHERE id_don = ?"
            );
            $statut        = $data['statut'] ?? 'disponible';
            $id_partenaire = $data['id_partenaire'] ? (int)$data['id_partenaire'] : null;
            $stmt->bind_param('sii', $statut, $id_partenaire, $id);
            $stmt->execute();

            // 2. Supprimer anciens produits puis réinsérer
            $del = $this->db->prepare("DELETE FROM Don_Produit WHERE id_don = ?");
            $del->bind_param('i', $id);
            $del->execute();
            $this->insertProduits($id, $data['produits']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Change uniquement le statut (pour réservation / récupération).
     */
    public function updateStatut(int $id, string $statut, ?int $id_partenaire = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE Don SET statut = ?, id_partenaire = COALESCE(?, id_partenaire) WHERE id_don = ?"
        );
        $stmt->bind_param('sii', $statut, $id_partenaire, $id);
        return $stmt->execute();
    }

    // ── DELETE ──────────────────────────────────────────────────

    /**
     * Supprime un don (CASCADE supprime automatiquement Don_Produit).
     * Seuls les dons 'disponible' peuvent être supprimés.
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM Don WHERE id_don = ? AND statut = 'disponible'"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    // ── PRIVÉ ───────────────────────────────────────────────────

    private function insertProduits(int $id_don, array $produits): void {
        $stmt = $this->db->prepare(
            "INSERT INTO Don_Produit (id_don, nom_produit, quantite, date_peremption)
             VALUES (?, ?, ?, ?)"
        );
        foreach ($produits as $p) {
            $nomProduit = $p['nom_produit'];
            $quantite = (int)$p['quantite'];
            $datePeremption = $p['date_peremption'];
            $stmt->bind_param('isis',
                $id_don,
                $nomProduit,
                $quantite,
                $datePeremption
            );
            $stmt->execute();
        }
    }
}
