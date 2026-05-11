<?php
/**
 * OrganisateurController - Adapté pour Green-Bite
 * Utilise getPdo() au lieu de config::getConnexion()
 */
require_once __DIR__ . '/../Model/Organisateur.php';

class OrganisateurController {
    private $db;

    public function __construct() {
        $this->db = getPdo();
    }

    private function validateOrganisateurData($data) {
        $errors = [];
        if (empty($data['nom'])) {
            $errors['nom'] = "Le nom de l'organisateur est obligatoire";
        } elseif (strlen($data['nom']) < 2) {
            $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
        } elseif (strlen($data['nom']) > 100) {
            $errors['nom'] = "Le nom ne peut pas dépasser 100 caractères";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/', $data['nom'])) {
            $errors['nom'] = "Le nom ne peut contenir que des lettres, espaces, tirets, points et apostrophes";
        }
        if (empty($data['email'])) {
            $errors['email'] = "L'email est obligatoire";
        } elseif (strlen($data['email']) > 150) {
            $errors['email'] = "L'email ne peut pas dépasser 150 caractères";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide (ex: nom@domaine.com)";
        }
        if (empty($data['telephone'])) {
            $errors['telephone'] = "Le numéro de téléphone est obligatoire";
        } elseif (strlen($data['telephone']) < 8) {
            $errors['telephone'] = "Le téléphone doit contenir au moins 8 chiffres";
        } elseif (strlen($data['telephone']) > 20) {
            $errors['telephone'] = "Le téléphone ne peut pas dépasser 20 caractères";
        } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $data['telephone'])) {
            $errors['telephone'] = "Format de téléphone invalide (ex: 71 123 456)";
        }
        if (!empty($data['adresse']) && strlen($data['adresse']) < 5) {
            $errors['adresse'] = "L'adresse doit contenir au moins 5 caractères";
        }
        if (!empty($data['site_web'])) {
            if (strlen($data['site_web']) > 200) {
                $errors['site_web'] = "Le site web ne peut pas dépasser 200 caractères";
            } elseif (!filter_var($data['site_web'], FILTER_VALIDATE_URL)) {
                $errors['site_web'] = "Format d'URL invalide (ex: https://exemple.com)";
            }
        }
        return $errors;
    }

    public function addOrganisateur($organisateur) {
        try {
            $data = [
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ];
            $errors = $this->validateOrganisateurData($data);
            if (!empty($errors)) throw new Exception(implode(', ', $errors));

            $checkQuery = $this->db->prepare("SELECT id FROM organisateur WHERE email = :email");
            $checkQuery->execute(['email' => $organisateur->getEmail()]);
            if ($checkQuery->fetch()) throw new Exception("Cet email est déjà utilisé par un autre organisateur");

            $sql = "INSERT INTO organisateur (nom, email, telephone, adresse, site_web) 
                    VALUES (:nom, :email, :telephone, :adresse, :site_web)";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'nom' => htmlspecialchars($organisateur->getNom(), ENT_QUOTES),
                'email' => strtolower($organisateur->getEmail()),
                'telephone' => htmlspecialchars($organisateur->getTelephone(), ENT_QUOTES),
                'adresse' => htmlspecialchars($organisateur->getAdresse(), ENT_QUOTES),
                'site_web' => htmlspecialchars($organisateur->getSiteWeb(), ENT_QUOTES)
            ]);
            if ($result) {
                return ['success' => true, 'message' => 'Organisateur ajouté avec succès', 'id' => $this->db->lastInsertId()];
            }
            return ['success' => false, 'message' => "Erreur lors de l'ajout"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function listOrganisateurs() {
        try {
            $sql = "SELECT * FROM organisateur ORDER BY nom ASC";
            $result = $this->db->query($sql);
            $organisateurs = $result->fetchAll();
            foreach ($organisateurs as &$org) {
                $org['event_count'] = $this->getEventCountByOrganisateur($org['id']);
            }
            return $organisateurs;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getOrganisateurById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $query = $this->db->prepare("SELECT * FROM organisateur WHERE id = :id");
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            if (!$result) throw new Exception("Organisateur non trouvé");
            $result['event_count'] = $this->getEventCountByOrganisateur($id);
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getEventCountByOrganisateur($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) return 0;
            $query = $this->db->prepare("SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :id");
            $query->execute(['id' => $id]);
            return (int)$query->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function searchOrganisateurs($keyword) {
        try {
            $keyword = trim($keyword);
            if (empty($keyword)) return $this->listOrganisateurs();
            $keyword = htmlspecialchars($keyword, ENT_QUOTES);
            $searchTerm = '%' . $keyword . '%';
            $sql = "SELECT * FROM organisateur 
                    WHERE (nom LIKE :keyword OR email LIKE :keyword OR telephone LIKE :keyword OR adresse LIKE :keyword)
                    ORDER BY nom ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['keyword' => $searchTerm]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function updateOrganisateur($organisateur, $id) {
        try {
            $data = [
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ];
            $errors = $this->validateOrganisateurData($data);
            if (!empty($errors)) throw new Exception(implode(', ', $errors));
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $checkExistsQuery = $this->db->prepare("SELECT id FROM organisateur WHERE id = :id");
            $checkExistsQuery->execute(['id' => $id]);
            if (!$checkExistsQuery->fetch()) throw new Exception("Organisateur non trouvé");
            $checkEmailQuery = $this->db->prepare("SELECT id FROM organisateur WHERE email = :email AND id != :id");
            $checkEmailQuery->execute(['email' => $organisateur->getEmail(), 'id' => $id]);
            if ($checkEmailQuery->fetch()) throw new Exception("Cet email est déjà utilisé par un autre organisateur");
            $sql = "UPDATE organisateur SET nom=:nom, email=:email, telephone=:telephone, adresse=:adresse, site_web=:site_web WHERE id=:id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'id' => $id,
                'nom' => htmlspecialchars($organisateur->getNom(), ENT_QUOTES),
                'email' => strtolower($organisateur->getEmail()),
                'telephone' => htmlspecialchars($organisateur->getTelephone(), ENT_QUOTES),
                'adresse' => htmlspecialchars($organisateur->getAdresse(), ENT_QUOTES),
                'site_web' => htmlspecialchars($organisateur->getSiteWeb(), ENT_QUOTES)
            ]);
            return ['success' => $result, 'message' => $result ? 'Organisateur modifié avec succès' : 'Erreur lors de la modification'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteOrganisateur($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $checkQuery = $this->db->prepare("SELECT id, nom FROM organisateur WHERE id = :id");
            $checkQuery->execute(['id' => $id]);
            $organisateur = $checkQuery->fetch();
            if (!$organisateur) throw new Exception("Organisateur non trouvé");
            $eventCount = $this->getEventCountByOrganisateur($id);
            if ($eventCount > 0) {
                throw new Exception("Impossible de supprimer cet organisateur car il organise {$eventCount} événement(s). Supprimez d'abord ses événements.");
            }
            $query = $this->db->prepare("DELETE FROM organisateur WHERE id = :id");
            $result = $query->execute(['id' => $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Organisateur "' . htmlspecialchars($organisateur['nom']) . '" supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getStats() {
        try {
            $stats = [];
            $stats['total'] = (int)$this->db->query("SELECT COUNT(*) as total FROM organisateur")->fetch()['total'];
            $stats['total_events'] = (int)$this->db->query("SELECT COUNT(*) as total_events FROM evenement")->fetch()['total_events'];
            $stats['avg_events'] = $stats['total'] > 0 ? round($stats['total_events'] / $stats['total'], 2) : 0;
            $sql = "SELECT o.nom, COUNT(e.id) as event_count 
                    FROM organisateur o 
                    LEFT JOIN evenement e ON o.id = e.organisateur_id 
                    GROUP BY o.id ORDER BY event_count DESC LIMIT 1";
            $stats['top_organisateur'] = $this->db->query($sql)->fetch();
            return $stats;
        } catch (Exception $e) {
            return ['total' => 0, 'total_events' => 0, 'avg_events' => 0, 'top_organisateur' => null];
        }
    }

    public function getTotalOrganisateurs() {
        try {
            return (int)$this->db->query("SELECT COUNT(*) as total FROM organisateur")->fetch()['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function emailExists($email, $excludeId = null) {
        try {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
            $sql = "SELECT id FROM organisateur WHERE email = :email";
            $params = ['email' => $email];
            if ($excludeId) {
                $excludeId = filter_var($excludeId, FILTER_VALIDATE_INT);
                if ($excludeId && $excludeId > 0) {
                    $sql .= " AND id != :id";
                    $params['id'] = $excludeId;
                }
            }
            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
