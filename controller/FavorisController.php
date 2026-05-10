<?php
// controller/FavorisController.php
require_once __DIR__ . "/../config/database.php";

class FavorisController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Ajouter un favori
     */
    public function addFavori($user_id, $evenement_id) {
        try {
            // Vérifier si déjà favori
            if ($this->isFavori($user_id, $evenement_id)) {
                return ['success' => false, 'message' => 'Déjà dans vos favoris'];
            }

            $sql = "INSERT INTO favoris (evenement_id, user_id) VALUES (:event_id, :user_id)";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'event_id' => $evenement_id,
                'user_id' => $user_id
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Ajouté aux favoris'];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Supprimer un favori
     */
    public function removeFavori($user_id, $evenement_id) {
        try {
            $sql = "DELETE FROM favoris WHERE user_id = :user_id AND evenement_id = :event_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'user_id' => $user_id,
                'event_id' => $evenement_id
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Retiré des favoris'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifier si un événement est dans les favoris d'un utilisateur
     */
    public function isFavori($user_id, $evenement_id) {
        try {
            $sql = "SELECT id FROM favoris WHERE user_id = :user_id AND evenement_id = :event_id";
            $query = $this->db->prepare($sql);
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $evenement_id
            ]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupérer tous les favoris d'un utilisateur
     */
    public function getFavorisByUser($user_id) {
        try {
            $sql = "SELECT f.*, e.titre, e.date_event, e.lieu, e.type, e.image_url 
                    FROM favoris f 
                    LEFT JOIN evenement e ON f.evenement_id = e.id 
                    WHERE f.user_id = :user_id 
                    ORDER BY f.date_ajout DESC";
            $query = $this->db->prepare($sql);
            $query->execute(['user_id' => $user_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Compter les favoris d'un événement
     */
    public function countFavorisByEvent($evenement_id) {
        try {
            $sql = "SELECT COUNT(*) as count FROM favoris WHERE evenement_id = :event_id";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            $result = $query->fetch();
            return (int)$result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>