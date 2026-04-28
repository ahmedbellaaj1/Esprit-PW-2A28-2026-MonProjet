<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../Model/User.php';

final class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPdo();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    /**
     * @return User[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY id DESC');
        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): User => User::fromArray($row), $rows);
    }

    public function create(User $user): int
    {
        $sql = 'INSERT INTO users (nom, prenom, email, mot_de_passe, photo, role, statut)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :photo, :role, :statut)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'mot_de_passe' => $user->getMotDePasse(),
            'photo' => $user->getPhoto(),
            'role' => $user->getRole(),
            'statut' => $user->getStatut(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(User $user): bool
    {
        if ($user->getId() === null) {
            throw new InvalidArgumentException('ID utilisateur manquant pour updateProfile.');
        }

        $sql = 'UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    photo = :photo,
                    mot_de_passe = :mot_de_passe
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'photo' => $user->getPhoto(),
            'mot_de_passe' => $user->getMotDePasse(),
        ]);
    }

    public function updateByAdmin(User $user): bool
    {
        if ($user->getId() === null) {
            throw new InvalidArgumentException('ID utilisateur manquant pour updateByAdmin.');
        }

        $sql = 'UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    role = :role,
                    statut = :statut
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'statut' => $user->getStatut(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
