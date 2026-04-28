<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Evenement.php";

class EvenementController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    // CREATE
    public function addEvenement($event) {
        try {
            if (!$event->isValid()) {
                $errors = $event->getErrors();
                throw new Exception(implode(', ', $errors));
            }

            $sql = "INSERT INTO evenement (titre, description, date_event, lieu, type) 
                    VALUES (:titre, :description, :date_event, :lieu, :type)";
            
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType()
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Événement ajouté avec succès', 'id' => $this->db->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout'];
        } catch (Exception $e) {
            error_log("AddEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // READ - Liste tous les événements
    public function listEvenements() {
        try {
            $sql = "SELECT * FROM evenement ORDER BY date_event ASC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("ListEvenements error: " . $e->getMessage());
            return [];
        }
    }

    // READ - Événements à venir
    public function getUpcomingEvents() {
        try {
            $sql = "SELECT * FROM evenement WHERE date_event >= CURDATE() ORDER BY date_event ASC LIMIT 6";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("GetUpcomingEvents error: " . $e->getMessage());
            return [];
        }
    }

    // READ - Événements passés
    public function getPastEvents() {
        try {
            $sql = "SELECT * FROM evenement WHERE date_event < CURDATE() ORDER BY date_event DESC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("GetPastEvents error: " . $e->getMessage());
            return [];
        }
    }

    // READ - Un événement par ID
    public function getEvenementById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            $sql = "SELECT * FROM evenement WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            
            if (!$result) {
                throw new Exception("Événement non trouvé");
            }
            return $result;
        } catch (Exception $e) {
            error_log("GetEvenementById error: " . $e->getMessage());
            return null;
        }
    }

    // READ - Filtrer par type
    public function getEventsByType($type) {
        try {
            $sql = "SELECT * FROM evenement WHERE type = :type AND date_event >= CURDATE() ORDER BY date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['type' => $type]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("GetEventsByType error: " . $e->getMessage());
            return [];
        }
    }

    // READ - Rechercher
    public function searchEvents($keyword) {
        try {
            $keyword = '%' . trim($keyword) . '%';
            $sql = "SELECT * FROM evenement 
                    WHERE (titre LIKE :keyword OR description LIKE :keyword OR lieu LIKE :keyword)
                    AND date_event >= CURDATE()
                    ORDER BY date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['keyword' => $keyword]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("SearchEvents error: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE
    public function updateEvenement($event, $id) {
        try {
            if (!$event->isValid()) {
                $errors = $event->getErrors();
                throw new Exception(implode(', ', $errors));
            }

            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            $sql = "UPDATE evenement SET 
                    titre = :titre,
                    description = :description,
                    date_event = :date_event,
                    lieu = :lieu,
                    type = :type
                    WHERE id = :id";

            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'id' => $id,
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType()
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Événement modifié avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la modification'];
        } catch (Exception $e) {
            error_log("UpdateEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // DELETE
    public function deleteEvenement($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            $checkSql = "SELECT id FROM evenement WHERE id = :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['id' => $id]);
            if (!$checkQuery->fetch()) {
                throw new Exception("Événement non trouvé");
            }

            $sql = "DELETE FROM evenement WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);

            if ($result) {
                return ['success' => true, 'message' => 'Événement supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            error_log("DeleteEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Statistiques
    public function getStats() {
        try {
            $stats = [];
            
            $sql = "SELECT COUNT(*) as total FROM evenement";
            $result = $this->db->query($sql);
            $stats['total'] = $result->fetch()['total'];
            
            $sql = "SELECT COUNT(*) as upcoming FROM evenement WHERE date_event >= CURDATE()";
            $result = $this->db->query($sql);
            $stats['upcoming'] = $result->fetch()['upcoming'];
            
            $sql = "SELECT type, COUNT(*) as count FROM evenement GROUP BY type";
            $result = $this->db->query($sql);
            $stats['byType'] = $result->fetchAll();
            
            $sql = "SELECT * FROM evenement WHERE date_event >= CURDATE() ORDER BY date_event ASC LIMIT 5";
            $result = $this->db->query($sql);
            $stats['nextEvents'] = $result->fetchAll();
            
            return $stats;
        } catch (Exception $e) {
            error_log("GetStats error: " . $e->getMessage());
            return ['total' => 0, 'upcoming' => 0, 'byType' => [], 'nextEvents' => []];
        }
    }
}
?>