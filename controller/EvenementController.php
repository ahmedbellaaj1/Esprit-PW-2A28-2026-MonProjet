<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Evenement.php";

class EvenementController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * CREATE - Ajouter un événement
     */
    public function addEvenement($event) {
        try {
            if (!$event->isValid()) {
                $errors = $event->getErrors();
                throw new Exception(implode(', ', $errors));
            }

            $sql = "INSERT INTO evenement (titre, description, date_event, lieu, type, organisateur_id) 
                    VALUES (:titre, :description, :date_event, :lieu, :type, :organisateur_id)";
            
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
            ]);

            if ($result) {
                return [
                    'success' => true, 
                    'message' => 'Événement ajouté avec succès', 
                    'id' => $this->db->lastInsertId()
                ];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout'];
        } catch (Exception $e) {
            error_log("AddEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * READ - Liste tous les événements avec jointure
     */
    public function listEvenements() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    ORDER BY e.date_event ASC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("ListEvenements error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Événements à venir avec jointure
     */
    public function getUpcomingEvents() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC LIMIT 6";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("GetUpcomingEvents error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Événements passés avec jointure
     */
    public function getPastEvents() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event < CURDATE() 
                    ORDER BY e.date_event DESC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("GetPastEvents error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Un événement par ID avec jointure
     */
    public function getEvenementById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email, 
                           o.telephone as organisateur_telephone, o.adresse as organisateur_adresse,
                           o.site_web as organisateur_site_web
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.id = :id";
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

    /**
     * READ - Filtrer par type avec jointure
     */
    public function getEventsByType($type) {
        try {
            $type = trim($type);
            $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
            if (!in_array($type, $validTypes)) {
                return [];
            }

            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.type = :type AND e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['type' => $type]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("GetEventsByType error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Rechercher des événements
     */
    public function searchEvents($keyword) {
        try {
            $keyword = trim($keyword);
            if (empty($keyword)) {
                return $this->getUpcomingEvents();
            }
            
            $searchTerm = '%' . $keyword . '%';
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE (e.titre LIKE :keyword 
                           OR e.description LIKE :keyword 
                           OR e.lieu LIKE :keyword
                           OR o.nom LIKE :keyword)
                    AND e.date_event >= CURDATE()
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['keyword' => $searchTerm]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("SearchEvents error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Événements par organisateur
     */
    public function getEventsByOrganisateur($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id) {
                return [];
            }

            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.organisateur_id = :organisateur_id 
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['organisateur_id' => $organisateur_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("GetEventsByOrganisateur error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Compter les événements par organisateur
     */
    public function countEventsByOrganisateur($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id) {
                return 0;
            }

            $sql = "SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :organisateur_id";
            $query = $this->db->prepare($sql);
            $query->execute(['organisateur_id' => $organisateur_id]);
            $result = $query->fetch();
            return $result['count'];
        } catch (Exception $e) {
            error_log("CountEventsByOrganisateur error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * UPDATE - Modifier un événement
     */
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

            // Vérifier si l'événement existe
            $checkSql = "SELECT id FROM evenement WHERE id = :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['id' => $id]);
            if (!$checkQuery->fetch()) {
                throw new Exception("Événement non trouvé");
            }

            $sql = "UPDATE evenement SET 
                    titre = :titre,
                    description = :description,
                    date_event = :date_event,
                    lieu = :lieu,
                    type = :type,
                    organisateur_id = :organisateur_id
                    WHERE id = :id";

            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'id' => $id,
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
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

    /**
     * DELETE - Supprimer un événement
     */
    public function deleteEvenement($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            // Vérifier si l'événement existe
            $checkSql = "SELECT id, titre FROM evenement WHERE id = :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['id' => $id]);
            $event = $checkQuery->fetch();
            if (!$event) {
                throw new Exception("Événement non trouvé");
            }

            $sql = "DELETE FROM evenement WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);

            if ($result) {
                return ['success' => true, 'message' => 'Événement "' . $event['titre'] . '" supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            error_log("DeleteEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * STATISTIQUES - Obtenir les statistiques des événements
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Total des événements
            $sql = "SELECT COUNT(*) as total FROM evenement";
            $result = $this->db->query($sql);
            $stats['total'] = $result->fetch()['total'];
            
            // Événements à venir
            $sql = "SELECT COUNT(*) as upcoming FROM evenement WHERE date_event >= CURDATE()";
            $result = $this->db->query($sql);
            $stats['upcoming'] = $result->fetch()['upcoming'];
            
            // Événements passés
            $stats['past'] = $stats['total'] - $stats['upcoming'];
            
            // Événements par type
            $sql = "SELECT type, COUNT(*) as count FROM evenement GROUP BY type";
            $result = $this->db->query($sql);
            $stats['byType'] = $result->fetchAll();
            
            // Prochains événements (5 prochains)
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC LIMIT 5";
            $result = $this->db->query($sql);
            $stats['nextEvents'] = $result->fetchAll();
            
            // Nombre d'organisateurs distincts
            $sql = "SELECT COUNT(DISTINCT organisateur_id) as organisateurs FROM evenement";
            $result = $this->db->query($sql);
            $stats['organisateurs'] = $result->fetch()['organisateurs'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("GetStats error: " . $e->getMessage());
            return [
                'total' => 0, 
                'upcoming' => 0, 
                'past' => 0,
                'byType' => [], 
                'nextEvents' => [],
                'organisateurs' => 0
            ];
        }
    }

    /**
     * UTILITAIRE - Récupérer tous les organisateurs pour les formulaires
     */
    public function getAllOrganisateurs() {
        try {
            $sql = "SELECT id, nom, email FROM organisateur ORDER BY nom ASC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("GetAllOrganisateurs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UTILITAIRE - Vérifier si un organisateur existe
     */
    public function organisateurExists($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id) {
                return false;
            }
            
            $sql = "SELECT id FROM organisateur WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $organisateur_id]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * UTILITAIRE - Obtenir le prochain événement
     */
    public function getNextEvent() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC LIMIT 1";
            $result = $this->db->query($sql);
            return $result->fetch();
        } catch (Exception $e) {
            error_log("GetNextEvent error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * UTILITAIRE - Obtenir le dernier événement ajouté
     */
    public function getLastEvent() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    ORDER BY e.created_at DESC LIMIT 1";
            $result = $this->db->query($sql);
            return $result->fetch();
        } catch (Exception $e) {
            error_log("GetLastEvent error: " . $e->getMessage());
            return null;
        }
    }
}
?>