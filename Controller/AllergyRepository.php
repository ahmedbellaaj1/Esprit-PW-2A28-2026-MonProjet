<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Allergy.php';

final class AllergyRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPdo();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM master_allergies ORDER BY nom ASC');
        $rows = $stmt->fetchAll();
        return array_map(static fn (array $row): Allergy => Allergy::fromArray($row), $rows);
    }

    public function create(string $nom, ?string $description): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO master_allergies (nom, description) VALUES (:nom, :description)');
        return $stmt->execute(['nom' => $nom, 'description' => $description]);
    }

    public function update(int $id, string $nom, ?string $description): bool
    {
        $stmt = $this->pdo->prepare('UPDATE master_allergies SET nom = :nom, description = :description WHERE id = :id');
        return $stmt->execute(['id' => $id, 'nom' => $nom, 'description' => $description]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM master_allergies WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
