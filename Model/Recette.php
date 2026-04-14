<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Recette
{
    public function findAll(): array
    {
        $stmt = getPdo()->query(
            'SELECT id_recette, nom, calories, description, date_creation FROM recette ORDER BY date_creation DESC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = getPdo()->prepare(
            'SELECT id_recette, nom, calories, description, date_creation FROM recette WHERE id_recette = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $nom, float $calories, string $description): int
    {
        $stmt = getPdo()->prepare(
            'INSERT INTO recette (nom, calories, description, date_creation)
             VALUES (:nom, :calories, :description, NOW())'
        );
        $stmt->execute([
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
        ]);
        return (int) getPdo()->lastInsertId();
    }

    public function update(int $id, string $nom, float $calories, string $description): bool
    {
        $stmt = getPdo()->prepare(
            'UPDATE recette SET nom = :nom, calories = :calories, description = :description WHERE id_recette = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'calories' => $calories,
            'description' => $description,
        ]);
    }

    public function delete(int $id): bool
    {
        if ($this->findById($id) === null) {
            return false;
        }

        $pdo = getPdo();
        try {
            // Autorise la suppression même si d’autres tables référencent encore la recette (schémas variés).
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            try {
                $pdo->prepare('DELETE FROM recette_ingredient WHERE id_recette = :id')->execute(['id' => $id]);
            } catch (\PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) !== 1146) {
                    throw $e;
                }
            }
            $stmt = $pdo->prepare('DELETE FROM recette WHERE id_recette = :id');
            $stmt->execute(['id' => $id]);
            // rowCount() peut renvoyer 0 avec PDO MySQL même si le DELETE a réussi
            return $this->findById($id) === null;
        } finally {
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $ignored) {
            }
        }
    }
}
