<?php
// controllers/PartenaireController.php — Logique métier (CRUD) pour les partenaires

require_once __DIR__ . '/../config/database.php';

class PartenaireController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── READ ────────────────────────────────────────────────────

    public function getAll(string $type = '', string $search = ''): array {
        $where  = [];
        $params = [];

        if ($type !== '') {
            $where[]  = 'type = ?';
            $params[] = $type;
        }
        if ($search !== '') {
            $where[]  = '(nom LIKE ? OR adresse LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = 'SELECT * FROM Partenaire';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── CREATE ──────────────────────────────────────────────────

    public function create(array $data): int|false {
        $stmt = $this->db->prepare(
            "INSERT INTO Partenaire (nom, type, adresse, telephone, email)
             VALUES (?, ?, ?, ?, ?)"
        );
        $nom = $data['nom'];
        $type = $data['type'];
        $adresse = $data['adresse'] ?? null;
        $telephone = $data['telephone'] ?? null;
        $email = $data['email'] ?? null;
        $ok = $stmt->execute([
            $nom,
            $type,
            $adresse,
            $telephone,
            $email
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    // ── UPDATE ──────────────────────────────────────────────────

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE Partenaire SET nom=?, type=?, adresse=?, telephone=?, email=?
             WHERE id_partenaire = ?"
        );
        $nom = $data['nom'];
        $type = $data['type'];
        $adresse = $data['adresse'] ?? null;
        $telephone = $data['telephone'] ?? null;
        $email = $data['email'] ?? null;
        return $stmt->execute([
            $nom,
            $type,
            $adresse,
            $telephone,
            $email,
            $id
        ]);
    }

    // ── DELETE ──────────────────────────────────────────────────

    public function delete(int $id): bool|string {
        $check = $this->db->prepare(
            "SELECT COUNT(*) AS nb FROM Don
             WHERE id_partenaire = ? AND statut != 'récupéré'"
        );
        $check->execute([$id]);
        $nb = (int)$check->fetch()['nb'];
        if ($nb > 0) {
            return "Impossible : $nb don(s) actif(s) lié(s) à ce partenaire.";
        }

        $stmt = $this->db->prepare("DELETE FROM Partenaire WHERE id_partenaire = ?");
        return $stmt->execute([$id]);
    }

    public function countDons(int $id): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS nb FROM Don WHERE id_partenaire = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetch()['nb'];
    }

    // ── TOP PARTENAIRES (meilleurs par nombre de dons) ──────────

    /**
     * Récupère les meilleurs partenaires (ceux avec le plus de dons)
     * @param int $limit Nombre maximum de partenaires à retourner (défaut: 5)
     * @return array Liste des partenaires avec leur nombre de dons
     */
    public function getTop(int $limit = 5): array {
        $sql = "
            SELECT 
                p.id_partenaire,
                p.nom,
                p.type,
                p.adresse,
                p.telephone,
                p.email,
                COUNT(d.id_don) AS nb_dons
            FROM Partenaire p
            LEFT JOIN Don d ON p.id_partenaire = d.id_partenaire
            GROUP BY p.id_partenaire
            ORDER BY nb_dons DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>