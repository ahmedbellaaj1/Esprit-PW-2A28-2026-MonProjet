<?php
// controllers/DonController.php — Logique métier (CRUD) pour les dons

require_once __DIR__ . '/../config/database.php';

class DonController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── READ ────────────────────────────────────────────────────

    public function getAll(string $statut = '', string $search = ''): array {
        // Mettre à jour automatiquement les dons périmés en DB
        $this->autoUpdatePerimes();

        $where  = [];
        $params = [];

        // "périmé" peut maintenant être filtré directement en DB
        $filterPerimee = ($statut === 'périmé');

        if ($statut !== '') {
            $where[]  = 'd.statut = ?';
            $params[] = $statut;
        }

        $sql = "SELECT d.id_don, d.statut, d.date_publication, d.id_user, d.id_partenaire,
                       p.nom AS partenaire_nom, p.type AS partenaire_type
                FROM Don d
                LEFT JOIN Partenaire p ON d.id_partenaire = p.id_partenaire";

        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= " ORDER BY
                    CASE WHEN d.statut = 'périmé' THEN 1 ELSE 0 END ASC,
                    (
                      SELECT MIN(dp.date_peremption)
                      FROM Don_Produit dp
                      WHERE dp.id_don = d.id_don
                    ) ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dons = $stmt->fetchAll();

        // Charger les produits pour chaque don
        foreach ($dons as &$don) {
            $don['produits'] = $this->getProduitsByDon((int)$don['id_don']);
        }

        // Si filtre périmé : déjà géré en DB via autoUpdatePerimes()
        // On filtre ici aussi par sécurité si statut non passé en SQL
        if ($filterPerimee) {
            $dons = array_filter($dons, fn($d) => $d['statut'] === 'périmé');
            $dons = array_values($dons);
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

    public function getById(int $id): ?array {
        $this->autoUpdatePerimes();
        $stmt = $this->db->prepare(
            "SELECT d.*, p.nom AS partenaire_nom, p.type AS partenaire_type
             FROM Don d
             LEFT JOIN Partenaire p ON d.id_partenaire = p.id_partenaire
             WHERE d.id_don = ?"
        );
        $stmt->execute([$id]);
        $don = $stmt->fetch();
        if (!$don) return null;
        $don['produits'] = $this->getProduitsByDon($id);
        return $don;
    }

    public function getProduitsByDon(int $id_don): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM Don_Produit WHERE id_don = ? ORDER BY id_ligne"
        );
        $stmt->execute([$id_don]);
        $produits = $stmt->fetchAll();

        $today = new DateTime('today');

        foreach ($produits as &$produit) {
            if (!empty($produit['date_peremption'])) {
                $datePeremption = new DateTime($produit['date_peremption']);
                $produit['statut_produit'] = $datePeremption < $today ? 'périmée' : 'valide';
            } else {
                $produit['statut_produit'] = 'inconnu';
            }
        }

        return $produits;
    }

    public function getStats(): array {
        // Mettre à jour les périmés avant de calculer les stats
        $this->autoUpdatePerimes();

        $res   = $this->db->query("SELECT statut, COUNT(*) AS total FROM Don GROUP BY statut");
        $stats = ['disponible' => 0, 'réservé' => 0, 'récupéré' => 0, 'périmé' => 0];
        while ($row = $res->fetch()) {
            $stats[$row['statut']] = (int)$row['total'];
        }
        $stats['total'] = array_sum($stats);
        return $stats;
    }

    // ── CREATE ──────────────────────────────────────────────────

    public function create(array $data): int|false {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Don (statut, id_user, id_partenaire) VALUES (?, ?, ?)"
            );
            $statut        = $data['statut'] ?? 'disponible';
            $id_user       = (int)($data['id_user'] ?? 1);
            $id_partenaire = $data['id_partenaire'] ? (int)$data['id_partenaire'] : null;

            if ($id_partenaire !== null) {
                $statut = 'réservé';
            }

            $stmt->bindValue(1, $statut, PDO::PARAM_STR);
            $stmt->bindValue(2, $id_user, PDO::PARAM_INT);
            $stmt->bindValue(3, $id_partenaire, $id_partenaire === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
            $id_don = (int)$this->db->lastInsertId();

            $this->insertProduits($id_don, $data['produits']);

            $this->db->commit();
            return $id_don;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // ── UPDATE ──────────────────────────────────────────────────

    public function update(int $id, array $data): bool {
        $this->db->beginTransaction();
        try {
            $current = $this->getById($id);
            if (!$current) {
                $this->db->rollBack();
                return false;
            }

            $statut        = $data['statut'] ?? $current['statut'];
            $id_partenaire = $data['id_partenaire'] !== null ? (int)$data['id_partenaire'] : null;

            if ($id_partenaire !== null && $current['statut'] === 'disponible') {
                $statut = 'réservé';
            }

            $stmt = $this->db->prepare(
                "UPDATE Don SET statut = ?, id_partenaire = ? WHERE id_don = ?"
            );
            $stmt->bindValue(1, $statut, PDO::PARAM_STR);
            $stmt->bindValue(2, $id_partenaire, $id_partenaire === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(3, $id, PDO::PARAM_INT);
            $stmt->execute();

            $del = $this->db->prepare("DELETE FROM Don_Produit WHERE id_don = ?");
            $del->execute([$id]);
            $this->insertProduits($id, $data['produits']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateStatut(int $id, string $statut, ?int $id_partenaire = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE Don SET statut = ?, id_partenaire = COALESCE(?, id_partenaire) WHERE id_don = ?"
        );
        return $stmt->execute([$statut, $id_partenaire, $id]);
    }

    // ── DELETE ──────────────────────────────────────────────────

    public function delete(int $id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM Don WHERE id_don = ? AND statut = 'disponible'"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // ── PRIVÉ ───────────────────────────────────────────────────

    /**
     * Met à jour automatiquement en DB le statut des dons dont
     * TOUS les produits ont dépassé leur date de péremption → statut = 'périmé'
     * Un don déjà récupéré n'est jamais marqué périmé.
     */
    private function autoUpdatePerimes(): void {
        $sql = "UPDATE Don
                SET statut = 'périmé'
                WHERE statut NOT IN ('récupéré', 'périmé')
                  AND id_don IN (
                      SELECT id_don FROM Don_Produit
                      GROUP BY id_don
                      HAVING MAX(date_peremption) < CURDATE()
                  )";
        $this->db->exec($sql);
    }

    private function insertProduits(int $id_don, array $produits): void {
        $stmt = $this->db->prepare(
            "INSERT INTO Don_Produit (id_don, nom_produit, quantite, date_peremption)
             VALUES (?, ?, ?, ?)"
        );
        foreach ($produits as $p) {
            $nomProduit = $p['nom_produit'];
            $quantite = (int)$p['quantite'];
            $datePeremption = $p['date_peremption'];
            $stmt->execute([
                $id_don,
                $nomProduit,
                $quantite,
                $datePeremption
            ]);
        }
    }
}