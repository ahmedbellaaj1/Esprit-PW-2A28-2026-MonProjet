<?php
// controller/AvisController.php
require_once __DIR__ . "/../config/database.php";

class AvisController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Ajouter ou modifier un avis
     */
    public function addOrUpdateAvis($user_id, $evenement_id, $note, $commentaire) {
        try {
            // Vérifier si l'utilisateur a déjà donné un avis
            $checkSql = "SELECT id FROM avis WHERE user_id = :user_id AND evenement_id = :event_id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute([
                'user_id' => $user_id,
                'event_id' => $evenement_id
            ]);
            $existing = $checkQuery->fetch();

            if ($existing) {
                // Mettre à jour l'avis existant
                $sql = "UPDATE avis SET note = :note, commentaire = :commentaire, statut = 'en_attente', updated_at = NOW() 
                        WHERE user_id = :user_id AND evenement_id = :event_id";
                $query = $this->db->prepare($sql);
                $result = $query->execute([
                    'note' => $note,
                    'commentaire' => htmlspecialchars($commentaire, ENT_QUOTES),
                    'user_id' => $user_id,
                    'event_id' => $evenement_id
                ]);
                $message = "Votre avis a été modifié avec succès !";
            } else {
                // Ajouter un nouvel avis
                $sql = "INSERT INTO avis (evenement_id, user_id, note, commentaire, statut) 
                        VALUES (:event_id, :user_id, :note, :commentaire, 'en_attente')";
                $query = $this->db->prepare($sql);
                $result = $query->execute([
                    'event_id' => $evenement_id,
                    'user_id' => $user_id,
                    'note' => $note,
                    'commentaire' => htmlspecialchars($commentaire, ENT_QUOTES)
                ]);
                $message = "Merci pour votre avis ! Il sera visible après modération.";
            }

            if ($result) {
                return ['success' => true, 'message' => $message];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Récupérer les avis d'un événement (uniquement publiés)
     */
    public function getAvisByEvent($evenement_id) {
        try {
            $sql = "SELECT a.*, u.nom, u.prenom, u.photo 
                    FROM avis a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE a.evenement_id = :event_id AND a.statut = 'publie' 
                    ORDER BY a.date_creation DESC";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Récupérer l'avis d'un utilisateur pour un événement spécifique
     */
    public function getUserAvis($user_id, $evenement_id) {
        try {
            $sql = "SELECT * FROM avis WHERE user_id = :user_id AND evenement_id = :event_id";
            $query = $this->db->prepare($sql);
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $evenement_id
            ]);
            return $query->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Calculer la note moyenne d'un événement
     */
    public function getAverageNote($evenement_id) {
        try {
            $sql = "SELECT AVG(note) as avg_note, COUNT(*) as total_avis 
                    FROM avis 
                    WHERE evenement_id = :event_id AND statut = 'publie'";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            $result = $query->fetch();
            return [
                'moyenne' => round($result['avg_note'] ?? 0, 1),
                'total' => (int)$result['total_avis']
            ];
        } catch (Exception $e) {
            return ['moyenne' => 0, 'total' => 0];
        }
    }

    /**
     * Supprimer un avis (admin)
     */
    public function deleteAvis($id) {
        try {
            $sql = "DELETE FROM avis WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);
            return ['success' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Modérer un avis (admin)
     */
    public function moderateAvis($id, $statut) {
        try {
            $validStatuts = ['publie', 'rejete'];
            if (!in_array($statut, $validStatuts)) {
                throw new Exception("Statut invalide");
            }
            $sql = "UPDATE avis SET statut = :statut WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id, 'statut' => $statut]);
            return ['success' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>