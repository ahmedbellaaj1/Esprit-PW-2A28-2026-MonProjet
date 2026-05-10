<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle pour gérer les avis et notations
 */
class Review
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /**
     * Ajouter un nouvel avis
     */
    public function addReview(int $id_produit, int $id_utilisateur, int $note, string $titre, string $texte): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO avis (id_produit, id_utilisateur, note, titre, texte, statut)
                VALUES (:id_produit, :id_utilisateur, :note, :titre, :texte, "en-attente")
            ');

            return $stmt->execute([
                ':id_produit' => $id_produit,
                ':id_utilisateur' => $id_utilisateur,
                ':note' => $note,
                ':titre' => $titre,
                ':texte' => $texte
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout d'un avis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les avis approuvés d'un produit
     */
    public function getProductReviews(int $id_produit, int $limit = 10, int $offset = 0): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    a.*,
                    CONCAT(SUBSTRING(p.nom, 1, 20), "...") as produit_nom
                FROM avis a
                LEFT JOIN produits p ON a.id_produit = p.id_produit
                WHERE a.id_produit = :id_produit AND a.statut = "approuve"
                ORDER BY a.date_avis DESC
                LIMIT :limit OFFSET :offset
            ');

            $stmt->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer la moyenne et le nombre d'avis
     */
    public function getProductRatingStats(int $id_produit): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    ROUND(AVG(note), 2) as moyenne_note,
                    COUNT(*) as nombre_avis
                FROM avis
                WHERE id_produit = :id_produit AND statut = "approuve"
            ');

            $stmt->execute([':id_produit' => $id_produit]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'moyenne_note' => $result['moyenne_note'] ?? 0,
                'nombre_avis' => (int)($result['nombre_avis'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des stats: " . $e->getMessage());
            return ['moyenne_note' => 0, 'nombre_avis' => 0];
        }
    }

    /**
     * Récupérer la distribution des notes (1-5 stars)
     */
    public function getRatingDistribution(int $id_produit): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    note,
                    COUNT(*) as count
                FROM avis
                WHERE id_produit = :id_produit AND statut = "approuve"
                GROUP BY note
                ORDER BY note DESC
            ');

            $stmt->execute([':id_produit' => $id_produit]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialiser les compteurs
            $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

            foreach ($results as $row) {
                $distribution[$row['note']] = (int)$row['count'];
            }

            return $distribution;
        } catch (PDOException $e) {
            error_log("Erreur lors de la distribution des notes: " . $e->getMessage());
            return [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        }
    }

    /**
     * Récupérer tous les avis (admin)
     */
    public function getAllReviews(string $statut = null, int $limit = 20, int $offset = 0): array
    {
        try {
            $where = '';
            if ($statut) {
                $where = ' WHERE statut = "' . $statut . '"';
            }

            $stmt = $this->pdo->prepare('
                SELECT a.*, p.nom as produit_nom
                FROM avis a
                LEFT JOIN produits p ON a.id_produit = p.id_produit
                ' . $where . '
                ORDER BY a.date_avis DESC
                LIMIT :limit OFFSET :offset
            ');

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis admin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Approuver un avis
     */
    public function approveReview(int $id_avis): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE avis SET statut = "approuve" WHERE id_avis = :id_avis
            ');
            return $stmt->execute([':id_avis' => $id_avis]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'approbation d'un avis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rejeter un avis
     */
    public function rejectReview(int $id_avis): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE avis SET statut = "rejet" WHERE id_avis = :id_avis
            ');
            return $stmt->execute([':id_avis' => $id_avis]);
        } catch (PDOException $e) {
            error_log("Erreur lors du rejet d'un avis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un avis
     */
    public function deleteReview(int $id_avis): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM avis WHERE id_avis = :id_avis
            ');
            return $stmt->execute([':id_avis' => $id_avis]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression d'un avis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compter les avis en attente
     */
    public function countPendingReviews(): int
    {
        try {
            $stmt = $this->pdo->query('
                SELECT COUNT(*) as count FROM avis WHERE statut = "en-attente"
            ');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des avis en attente: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marquer un avis comme utile
     */
    public function markAsHelpful(int $id_avis): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE avis SET nombre_utilites = nombre_utilites + 1 WHERE id_avis = :id_avis
            ');
            return $stmt->execute([':id_avis' => $id_avis]);
        } catch (PDOException $e) {
            error_log("Erreur lors du marquage de l'avis: " . $e->getMessage());
            return false;
        }
    }
}
