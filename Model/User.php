<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, nom, prenom, email, role, statut, date_inscription FROM users ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (nom, prenom, email, mot_de_passe, photo, role, statut)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :photo, :role, :statut)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'mot_de_passe' => $data['mot_de_passe'],
            'photo' => $data['photo'] ?? null,
            'role' => $data['role'] ?? 'user',
            'statut' => $data['statut'] ?? 'actif',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sql = 'UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    photo = :photo,
                    mot_de_passe = :mot_de_passe
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'photo' => $data['photo'],
            'mot_de_passe' => $data['mot_de_passe'],
        ]);
    }

    public function updateByAdmin(int $id, array $data): bool
    {
        $sql = 'UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    role = :role,
                    statut = :statut
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'role' => $data['role'],
            'statut' => $data['statut'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
