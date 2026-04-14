<?php
// models/Partenaire.php

require_once __DIR__ . '/../config/database.php';

class Partenaire {

    private mysqli $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── READ ────────────────────────────────────────────────────

    public function getAll(string $type = '', string $search = ''): array {
        $where  = [];
        $params = [];
        $types  = '';

        if ($type !== '') {
            $where[]  = 'type = ?';
            $params[] = $type;
            $types   .= 's';
        }
        if ($search !== '') {
            $where[]  = '(nom LIKE ? OR adresse LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types   .= 'ss';
        }

        $sql = 'SELECT * FROM Partenaire';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY nom ASC';

        $stmt = $this->db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
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
        $stmt->bind_param('sssss',
            $nom,
            $type,
            $adresse,
            $telephone,
            $email
        );
        return $stmt->execute() ? $this->db->insert_id : false;
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
        $stmt->bind_param('sssssi',
            $nom,
            $type,
            $adresse,
            $telephone,
            $email,
            $id
        );
        return $stmt->execute();
    }

    // ── DELETE ──────────────────────────────────────────────────

    public function delete(int $id): bool|string {
        // Vérifier les dons actifs liés
        $check = $this->db->prepare(
            "SELECT COUNT(*) AS nb FROM Don
             WHERE id_partenaire = ? AND statut != 'récupéré'"
        );
        $check->bind_param('i', $id);
        $check->execute();
        $nb = (int)$check->get_result()->fetch_assoc()['nb'];
        if ($nb > 0) {
            return "Impossible : $nb don(s) actif(s) lié(s) à ce partenaire.";
        }

        $stmt = $this->db->prepare("DELETE FROM Partenaire WHERE id_partenaire = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function countDons(int $id): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS nb FROM Don WHERE id_partenaire = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['nb'];
    }
}
