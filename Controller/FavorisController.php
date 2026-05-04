<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Favoris.php';
require_once __DIR__ . '/../Model/Recette.php';

final class FavorisController
{
    /**
     * Catégories de calories pour le tri
     * @return array<string, array{min: int, max: int|null}>
     */
    private function getCalorieCategories(): array
    {
        return [
            'moins_400' => ['min' => 0, 'max' => 399],
            '400_700' => ['min' => 400, 'max' => 700],
            '700_1000' => ['min' => 701, 'max' => 1000],
            'plus_1000' => ['min' => 1001, 'max' => null],
        ];
    }

    /**
     * Récupère le libellé de la catégorie de calories
     */
    private function getCategoryLabel(string $category): string
    {
        return match($category) {
            'moins_400' => '< 400 kcal',
            '400_700' => '400 - 700 kcal',
            '700_1000' => '700 - 1000 kcal',
            'plus_1000' => '> 1000 kcal',
            default => 'Autre',
        };
    }

    /**
     * Détermine la catégorie de calories pour une recette
     */
    private function getCategoryForCalories(float $calories): ?string
    {
        $categories = $this->getCalorieCategories();
        foreach ($categories as $key => $range) {
            if ($calories >= $range['min']) {
                if ($range['max'] === null || $calories <= $range['max']) {
                    return $key;
                }
            }
        }
        return null;
    }

    /**
     * Ajoute une recette aux favoris d'un utilisateur
     */
    public function addFavorite(int $idRecette, int $idUser): bool
    {
        try {
            // Vérifier si le favoris existe déjà
            $checkStmt = getPdo()->prepare(
                'SELECT id_favoris FROM favoris WHERE id_recette = :id_recette AND id_user = :id_user'
            );
            $checkStmt->execute(['id_recette' => $idRecette, 'id_user' => $idUser]);
            
            if ($checkStmt->fetch()) {
                // Le favoris existe déjà
                return true;
            }

            // Insérer le nouveau favoris
            $stmt = getPdo()->prepare(
                'INSERT INTO favoris (id_recette, id_user) VALUES (:id_recette, :id_user)'
            );
            return $stmt->execute([
                'id_recette' => $idRecette,
                'id_user' => $idUser,
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur ajout favoris: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une recette des favoris d'un utilisateur
     */
    public function removeFavorite(int $idRecette, int $idUser): bool
    {
        try {
            $stmt = getPdo()->prepare(
                'DELETE FROM favoris WHERE id_recette = :id_recette AND id_user = :id_user'
            );
            return $stmt->execute([
                'id_recette' => $idRecette,
                'id_user' => $idUser,
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur suppression favoris: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si une recette est dans les favoris d'un utilisateur
     */
    public function isFavorite(int $idRecette, int $idUser): bool
    {
        try {
            $stmt = getPdo()->prepare(
                'SELECT 1 FROM favoris WHERE id_recette = :id_recette AND id_user = :id_user LIMIT 1'
            );
            $stmt->execute(['id_recette' => $idRecette, 'id_user' => $idUser]);
            return (bool) $stmt->fetch();
        } catch (\PDOException $e) {
            error_log('Erreur vérification favoris: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les favoris d'un utilisateur
     * @return array<array{id_favoris: int, id_recette: int, id_user: int, nom: string, calories: float, description: string, date_creation: string, duree_prep: string}>
     */
    public function getFavoritesByUser(int $idUser): array
    {
        try {
            $stmt = getPdo()->prepare(
                'SELECT f.id_favoris, f.id_recette, f.id_user, r.nom, r.calories, r.description, r.date_creation, r.duree_prep
                 FROM favoris f
                 INNER JOIN recette r ON f.id_recette = r.id_recette
                 WHERE f.id_user = :id_user
                 ORDER BY f.id_favoris DESC'
            );
            $stmt->execute(['id_user' => $idUser]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Erreur récupération favoris: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les favoris d'un utilisateur groupés par catégorie de calories
     * @return array<string, array<array{id_favoris: int, id_recette: int, id_user: int, nom: string, calories: float, description: string, date_creation: string, duree_prep: string}>>
     */
    public function getFavoritesByUserGroupedByCalories(int $idUser): array
    {
        $favorites = $this->getFavoritesByUser($idUser);
        $grouped = [];

        foreach ($favorites as $favorite) {
            $category = $this->getCategoryForCalories((float) $favorite['calories']);
            if ($category === null) {
                $category = 'plus_1000';
            }
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $favorite;
        }

        return $grouped;
    }

    /**
     * Récupère le nombre de favoris d'un utilisateur
     */
    public function countFavoritesByUser(int $idUser): int
    {
        try {
            $stmt = getPdo()->prepare(
                'SELECT COUNT(*) as count FROM favoris WHERE id_user = :id_user'
            );
            $stmt->execute(['id_user' => $idUser]);
            $result = $stmt->fetch();
            return (int) ($result['count'] ?? 0);
        } catch (\PDOException $e) {
            error_log('Erreur comptage favoris: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les recettes les plus populaires (les plus ajoutées en favoris)
     * @return array<array{id_recette: int, nom: string, calories: float, count: int}>
     */
    public function getMostFavoritedRecettes(int $limit = 10): array
    {
        try {
            $stmt = getPdo()->prepare(
                'SELECT r.id_recette, r.nom, r.calories, COUNT(f.id_favoris) as count
                 FROM recette r
                 LEFT JOIN favoris f ON r.id_recette = f.id_recette
                 GROUP BY r.id_recette, r.nom, r.calories
                 ORDER BY count DESC
                 LIMIT :limit'
            );
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Erreur recettes populaires: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gère les actions POST (ajouter/supprimer favoris)
     */
    public function handlePost(): ?string
    {
        $action = trim((string) ($_POST['action'] ?? ''));
        if ($action === '') {
            return null;
        }

        // Utiliser l'utilisateur actuellement logué (sinon id 1 par défaut)
        $idUser = (int) ($_SESSION['user_id'] ?? 1);
        $idRecette = filter_var($_POST['id_recette'] ?? '', FILTER_VALIDATE_INT);

        if ($idRecette === false || $idRecette < 0) {
            $_SESSION['favoris_flash'] = 'Identifiant de recette invalide.';
            return 'redirect';
        }

        if ($action === 'add') {
            $ok = $this->addFavorite((int) $idRecette, $idUser);
            $_SESSION['favoris_flash'] = $ok
                ? 'Recette ajoutée aux favoris !'
                : 'Erreur lors de l\'ajout aux favoris.';
            return 'redirect';
        }

        if ($action === 'remove') {
            $ok = $this->removeFavorite((int) $idRecette, $idUser);
            $_SESSION['favoris_flash'] = $ok
                ? 'Recette supprimée des favoris.'
                : 'Erreur lors de la suppression des favoris.';
            return 'redirect';
        }

        return null;
    }

    /**
     * Récupère tous les favoris avec les catégories de calories pour l'affichage
     */
    public function getFavoritesWithCategories(int $idUser): array
    {
        $grouped = $this->getFavoritesByUserGroupedByCalories($idUser);
        $categories = $this->getCalorieCategories();
        
        $result = [];
        foreach ($categories as $key => $range) {
            $result[$key] = [
                'label' => $this->getCategoryLabel($key),
                'min' => $range['min'],
                'max' => $range['max'],
                'recettes' => $grouped[$key] ?? [],
            ];
        }

        return $result;
    }
}
