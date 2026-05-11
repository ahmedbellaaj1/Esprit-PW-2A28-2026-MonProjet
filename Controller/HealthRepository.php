<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Health.php';

final class HealthRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPdo();
    }

    public function findByUserId(int $idUser): ?Health
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user_health WHERE id_user = :id_user LIMIT 1');
        $stmt->execute(['id_user' => $idUser]);
        $row = $stmt->fetch();

        return $row ? Health::fromArray($row) : null;
    }

    public function save(Health $health): bool
    {
        $sql = 'INSERT INTO user_health (id_user, preference_alimentaire, allergies, poids, age, taille, sexe)
                VALUES (:id_user, :preference, :allergies, :poids, :age, :taille, :sexe)
                ON DUPLICATE KEY UPDATE 
                    preference_alimentaire = VALUES(preference_alimentaire),
                    allergies = VALUES(allergies),
                    poids = VALUES(poids),
                    age = VALUES(age),
                    taille = VALUES(taille),
                    sexe = VALUES(sexe)';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_user' => $health->getIdUser(),
            'preference' => $health->getPreferenceAlimentaire(),
            'allergies' => $health->getAllergies(),
            'poids' => $health->getPoids(),
            'age' => $health->getAge(),
            'taille' => $health->getTaille(),
            'sexe' => $health->getSexe(),
        ]);
    }

    public function getAdvice(string $type, string $name): ?string
    {
        $table = ($type === 'preference') ? 'master_preferences' : 'master_allergies';
        $stmt = $this->pdo->prepare("SELECT description FROM $table WHERE nom = :nom LIMIT 1");
        $stmt->execute(['nom' => $name]);
        $row = $stmt->fetch();
        return $row ? (string)$row['description'] : null;
    }
}
