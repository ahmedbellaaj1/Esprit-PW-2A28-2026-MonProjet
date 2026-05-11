<?php
/**
 * EvenementController - Adapté pour Green-Bite
 * Utilise getPdo() au lieu de config::getConnexion()
 */
require_once __DIR__ . '/../Model/Evenement.php';

class EvenementController {
    private $db;

    public function __construct() {
        $this->db = getPdo();
    }

    private function validateEvenementData($data) {
        $errors = [];
        if (empty($data['titre'])) {
            $errors['titre'] = "Le titre est obligatoire";
        } elseif (strlen($data['titre']) < 3) {
            $errors['titre'] = "Le titre doit contenir au moins 3 caractères";
        } elseif (strlen($data['titre']) > 100) {
            $errors['titre'] = "Le titre ne peut pas dépasser 100 caractères";
        }
        if (empty($data['description'])) {
            $errors['description'] = "La description est obligatoire";
        } elseif (strlen($data['description']) < 10) {
            $errors['description'] = "La description doit contenir au moins 10 caractères";
        } elseif (strlen($data['description']) > 5000) {
            $errors['description'] = "La description ne peut pas dépasser 5000 caractères";
        }
        if (empty($data['date_event'])) {
            $errors['date_event'] = "La date est obligatoire";
        } else {
            $dateObj = DateTime::createFromFormat('Y-m-d', $data['date_event']);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $data['date_event']) {
                $errors['date_event'] = "Format de date invalide";
            }
        }
        if (empty($data['lieu'])) {
            $errors['lieu'] = "Le lieu est obligatoire";
        } elseif (strlen($data['lieu']) < 2) {
            $errors['lieu'] = "Le lieu doit contenir au moins 2 caractères";
        } elseif (strlen($data['lieu']) > 100) {
            $errors['lieu'] = "Le lieu ne peut pas dépasser 100 caractères";
        }
        $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
        if (empty($data['type'])) {
            $errors['type'] = "Le type est obligatoire";
        } elseif (!in_array($data['type'], $validTypes)) {
            $errors['type'] = "Type d'événement invalide";
        }
        if (empty($data['organisateur_id'])) {
            $errors['organisateur_id'] = "L'organisateur est obligatoire";
        } elseif (!is_numeric($data['organisateur_id']) || $data['organisateur_id'] <= 0) {
            $errors['organisateur_id'] = "Veuillez sélectionner un organisateur valide";
        }
        return $errors;
    }

    public function addEvenement($event) {
        try {
            $data = [
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
            ];
            $errors = $this->validateEvenementData($data);
            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }
            if (!$this->organisateurExists($event->getOrganisateurId())) {
                throw new Exception("L'organisateur sélectionné n'existe pas");
            }
            $sql = "INSERT INTO evenement (titre, description, date_event, lieu, type, organisateur_id) 
                    VALUES (:titre, :description, :date_event, :lieu, :type, :organisateur_id)";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'titre' => htmlspecialchars($event->getTitre(), ENT_QUOTES),
                'description' => htmlspecialchars($event->getDescription(), ENT_QUOTES),
                'date_event' => $event->getDate(),
                'lieu' => htmlspecialchars($event->getLieu(), ENT_QUOTES),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
            ]);
            if ($result) {
                return ['success' => true, 'message' => 'Événement ajouté avec succès', 'id' => $this->db->lastInsertId()];
            }
            return ['success' => false, 'message' => "Erreur lors de l'ajout"];
        } catch (Exception $e) {
            error_log("AddEvenement error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

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
            return [];
        }
    }

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
            return [];
        }
    }

    public function getEvenementById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email, 
                           o.telephone as organisateur_telephone, o.adresse as organisateur_adresse,
                           o.site_web as organisateur_site_web
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            if (!$result) throw new Exception("Événement non trouvé");
            return $result;
        } catch (Exception $e) {
            error_log("GetEvenementById error: " . $e->getMessage());
            return null;
        }
    }

    public function getEventsByType($type) {
        try {
            $type = trim($type);
            $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
            if (!in_array($type, $validTypes)) return [];
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.type = :type AND e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['type' => $type]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function searchEvents($keyword) {
        try {
            $keyword = trim($keyword);
            if (empty($keyword)) return $this->getUpcomingEvents();
            $keyword = htmlspecialchars($keyword, ENT_QUOTES);
            $searchTerm = '%' . $keyword . '%';
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE (e.titre LIKE :keyword OR e.description LIKE :keyword OR e.lieu LIKE :keyword OR o.nom LIKE :keyword)
                    AND e.date_event >= CURDATE()
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['keyword' => $searchTerm]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function searchAdvanced($filters) {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom, o.email as organisateur_email 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE 1=1";
            $params = [];
            if (!empty($filters['keyword'])) {
                $keyword = '%' . trim($filters['keyword']) . '%';
                $sql .= " AND (e.titre LIKE :keyword OR e.description LIKE :keyword OR e.lieu LIKE :keyword OR o.nom LIKE :keyword)";
                $params['keyword'] = $keyword;
            }
            if (!empty($filters['type']) && $filters['type'] != 'all') {
                $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
                if (in_array($filters['type'], $validTypes)) {
                    $sql .= " AND e.type = :type";
                    $params['type'] = $filters['type'];
                }
            }
            if (!empty($filters['lieu'])) {
                $sql .= " AND e.lieu LIKE :lieu";
                $params['lieu'] = '%' . trim($filters['lieu']) . '%';
            }
            if (!empty($filters['organisateur_id']) && $filters['organisateur_id'] != 'all') {
                $orgId = filter_var($filters['organisateur_id'], FILTER_VALIDATE_INT);
                if ($orgId && $orgId > 0) {
                    $sql .= " AND e.organisateur_id = :organisateur_id";
                    $params['organisateur_id'] = $orgId;
                }
            }
            if (!empty($filters['date_debut']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_debut'])) {
                $sql .= " AND e.date_event >= :date_debut";
                $params['date_debut'] = $filters['date_debut'];
            }
            if (!empty($filters['date_fin']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_fin'])) {
                $sql .= " AND e.date_event <= :date_fin";
                $params['date_fin'] = $filters['date_fin'];
            }
            if (!empty($filters['statut'])) {
                if ($filters['statut'] == 'upcoming') $sql .= " AND e.date_event >= CURDATE()";
                elseif ($filters['statut'] == 'past') $sql .= " AND e.date_event < CURDATE()";
                elseif ($filters['statut'] == 'today') $sql .= " AND e.date_event = CURDATE()";
            }
            $orderBy = "e.date_event ASC";
            if (!empty($filters['tri'])) {
                switch ($filters['tri']) {
                    case 'date_desc': $orderBy = "e.date_event DESC"; break;
                    case 'titre_asc': $orderBy = "e.titre ASC"; break;
                    case 'titre_desc': $orderBy = "e.titre DESC"; break;
                    case 'lieu_asc': $orderBy = "e.lieu ASC"; break;
                    case 'type_asc': $orderBy = "e.type ASC"; break;
                    case 'organisateur_asc': $orderBy = "o.nom ASC"; break;
                    default: $orderBy = "e.date_event ASC";
                }
            }
            $sql .= " ORDER BY " . $orderBy;
            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("SearchAdvanced error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllLieus() {
        try {
            $result = $this->db->query("SELECT DISTINCT lieu FROM evenement ORDER BY lieu ASC");
            return $result->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getEventsByOrganisateur($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id || $organisateur_id <= 0) return [];
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.organisateur_id = :organisateur_id 
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['organisateur_id' => $organisateur_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function countEventsByOrganisateur($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id || $organisateur_id <= 0) return 0;
            $query = $this->db->prepare("SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :organisateur_id");
            $query->execute(['organisateur_id' => $organisateur_id]);
            return (int)$query->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function updateEvenement($event, $id) {
        try {
            $data = [
                'titre' => $event->getTitre(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
            ];
            $errors = $this->validateEvenementData($data);
            if (!empty($errors)) throw new Exception(implode(', ', $errors));
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $checkQuery = $this->db->prepare("SELECT id FROM evenement WHERE id = :id");
            $checkQuery->execute(['id' => $id]);
            if (!$checkQuery->fetch()) throw new Exception("Événement non trouvé");
            if (!$this->organisateurExists($event->getOrganisateurId())) throw new Exception("L'organisateur sélectionné n'existe pas");
            $sql = "UPDATE evenement SET titre=:titre, description=:description, date_event=:date_event, lieu=:lieu, type=:type, organisateur_id=:organisateur_id WHERE id=:id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'id' => $id,
                'titre' => htmlspecialchars($event->getTitre(), ENT_QUOTES),
                'description' => htmlspecialchars($event->getDescription(), ENT_QUOTES),
                'date_event' => $event->getDate(),
                'lieu' => htmlspecialchars($event->getLieu(), ENT_QUOTES),
                'type' => $event->getType(),
                'organisateur_id' => $event->getOrganisateurId()
            ]);
            return ['success' => $result, 'message' => $result ? 'Événement modifié avec succès' : 'Erreur lors de la modification'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteEvenement($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $checkQuery = $this->db->prepare("SELECT id, titre FROM evenement WHERE id = :id");
            $checkQuery->execute(['id' => $id]);
            $event = $checkQuery->fetch();
            if (!$event) throw new Exception("Événement non trouvé");
            $query = $this->db->prepare("DELETE FROM evenement WHERE id = :id");
            $result = $query->execute(['id' => $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Événement "' . htmlspecialchars($event['titre']) . '" supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getNextEvents($limit = 5) {
        try {
            $limit = filter_var($limit, FILTER_VALIDATE_INT);
            if ($limit === false || $limit <= 0) $limit = 5;
            if ($limit > 50) $limit = 50;
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC 
                    LIMIT :limit";
            $query = $this->db->prepare($sql);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getStats() {
        try {
            $stats = [];
            $stats['total'] = (int)$this->db->query("SELECT COUNT(*) as total FROM evenement")->fetch()['total'];
            $stats['upcoming'] = (int)$this->db->query("SELECT COUNT(*) as upcoming FROM evenement WHERE date_event >= CURDATE()")->fetch()['upcoming'];
            $stats['past'] = $stats['total'] - $stats['upcoming'];
            $stats['byType'] = $this->db->query("SELECT type, COUNT(*) as count FROM evenement GROUP BY type")->fetchAll();
            $stats['nextEvents'] = $this->getNextEvents(5);
            $stats['organisateurs'] = (int)$this->db->query("SELECT COUNT(DISTINCT organisateur_id) as organisateurs FROM evenement")->fetch()['organisateurs'];
            return $stats;
        } catch (Exception $e) {
            return ['total' => 0, 'upcoming' => 0, 'past' => 0, 'byType' => [], 'nextEvents' => [], 'organisateurs' => 0];
        }
    }

    public function getAllOrganisateurs() {
        try {
            return $this->db->query("SELECT id, nom, email FROM organisateur ORDER BY nom ASC")->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function organisateurExists($organisateur_id) {
        try {
            $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
            if (!$organisateur_id || $organisateur_id <= 0) return false;
            $query = $this->db->prepare("SELECT id FROM organisateur WHERE id = :id");
            $query->execute(['id' => $organisateur_id]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getNextEvent() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    WHERE e.date_event >= CURDATE() 
                    ORDER BY e.date_event ASC LIMIT 1";
            return $this->db->query($sql)->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getLastEvent() {
        try {
            $sql = "SELECT e.*, o.nom as organisateur_nom 
                    FROM evenement e 
                    LEFT JOIN organisateur o ON e.organisateur_id = o.id 
                    ORDER BY e.id DESC LIMIT 1";
            return $this->db->query($sql)->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
