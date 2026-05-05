<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Organisateur.php";

class OrganisateurController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Validation PHP des données d'organisateur avant insertion
     * @param array $data
     * @return array
     */
    private function validateOrganisateurData($data) {
        $errors = [];
        
        // Validation du nom
        if (empty($data['nom'])) {
            $errors['nom'] = "Le nom de l'organisateur est obligatoire";
        } elseif (strlen($data['nom']) < 2) {
            $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
        } elseif (strlen($data['nom']) > 100) {
            $errors['nom'] = "Le nom ne peut pas dépasser 100 caractères";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/', $data['nom'])) {
            $errors['nom'] = "Le nom ne peut contenir que des lettres, espaces, tirets, points et apostrophes";
        }
        
        // Validation de l'email
        if (empty($data['email'])) {
            $errors['email'] = "L'email est obligatoire";
        } elseif (strlen($data['email']) > 150) {
            $errors['email'] = "L'email ne peut pas dépasser 150 caractères";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide (ex: nom@domaine.com)";
        } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $data['email'])) {
            $errors['email'] = "Format d'email invalide";
        }
        
        // Validation du téléphone
        if (empty($data['telephone'])) {
            $errors['telephone'] = "Le numéro de téléphone est obligatoire";
        } elseif (strlen($data['telephone']) < 8) {
            $errors['telephone'] = "Le téléphone doit contenir au moins 8 chiffres";
        } elseif (strlen($data['telephone']) > 20) {
            $errors['telephone'] = "Le téléphone ne peut pas dépasser 20 caractères";
        } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $data['telephone'])) {
            $errors['telephone'] = "Format de téléphone invalide (ex: 71 123 456 ou +21671234567)";
        }
        
        // Validation de l'adresse (optionnelle)
        if (!empty($data['adresse']) && strlen($data['adresse']) < 5) {
            $errors['adresse'] = "L'adresse doit contenir au moins 5 caractères";
        }
        if (!empty($data['adresse']) && strlen($data['adresse']) > 500) {
            $errors['adresse'] = "L'adresse ne peut pas dépasser 500 caractères";
        }
        
        // Validation du site web (optionnel)
        if (!empty($data['site_web'])) {
            if (strlen($data['site_web']) > 200) {
                $errors['site_web'] = "Le site web ne peut pas dépasser 200 caractères";
            } elseif (!filter_var($data['site_web'], FILTER_VALIDATE_URL)) {
                $errors['site_web'] = "Format d'URL invalide (ex: https://exemple.com)";
            } elseif (!preg_match('/^https?:\/\/.+\..+$/', $data['site_web'])) {
                $errors['site_web'] = "L'URL doit commencer par http:// ou https://";
            }
        }
        
        return $errors;
    }

    /**
     * CREATE - Ajouter un organisateur
     */
    public function addOrganisateur($organisateur) {
        try {
            // Validation PHP des données
            $data = [
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ];
            
            $errors = $this->validateOrganisateurData($data);
            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }

            // Vérifier si l'email existe déjà
            $checkSql = "SELECT id FROM organisateur WHERE email = :email";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['email' => $organisateur->getEmail()]);
            if ($checkQuery->fetch()) {
                throw new Exception("Cet email est déjà utilisé par un autre organisateur");
            }

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
                return [
                    'success' => true, 
                    'message' => 'Organisateur ajouté avec succès', 
                    'id' => $this->db->lastInsertId()
                ];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout'];
        } catch (Exception $e) {
            error_log("AddOrganisateur error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * READ - Liste tous les organisateurs
     */
    public function listOrganisateurs() {
        try {
            $sql = "SELECT * FROM organisateur ORDER BY nom ASC";
            $result = $this->db->query($sql);
            $organisateurs = $result->fetchAll();
            
            // Ajouter le nombre d'événements pour chaque organisateur
            foreach ($organisateurs as &$org) {
                $org['event_count'] = $this->getEventCountByOrganisateur($org['id']);
            }
            
            return $organisateurs;
        } catch (Exception $e) {
            error_log("ListOrganisateurs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ - Un organisateur par ID
     */
    public function getOrganisateurById($id) {
        try {
            // Validation PHP de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                throw new Exception("ID invalide");
            }

            $sql = "SELECT * FROM organisateur WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            
            if (!$result) {
                throw new Exception("Organisateur non trouvé");
            }
            
            // Ajouter le nombre d'événements
            $result['event_count'] = $this->getEventCountByOrganisateur($id);
            
            return $result;
        } catch (Exception $e) {
            error_log("GetOrganisateurById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * READ - Nombre d'événements par organisateur
     */
    public function getEventCountByOrganisateur($id) {
        try {
            // Validation PHP de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("GetEventCountByOrganisateur error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * READ - Rechercher des organisateurs
     */
    public function searchOrganisateurs($keyword) {
        try {
            // Validation PHP du mot-clé
            $keyword = trim($keyword);
            if (empty($keyword)) {
                return $this->listOrganisateurs();
            }
            
            // Protection contre les injections
            $keyword = htmlspecialchars($keyword, ENT_QUOTES);
            $searchTerm = '%' . $keyword . '%';
            
            $sql = "SELECT * FROM organisateur 
                    WHERE (nom LIKE :keyword 
                           OR email LIKE :keyword 
                           OR telephone LIKE :keyword
                           OR adresse LIKE :keyword)
                    ORDER BY nom ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['keyword' => $searchTerm]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("SearchOrganisateurs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UPDATE - Modifier un organisateur
     */
    public function updateOrganisateur($organisateur, $id) {
        try {
            // Validation PHP des données
            $data = [
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ];
            
            $errors = $this->validateOrganisateurData($data);
            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }

            // Validation PHP de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                throw new Exception("ID invalide");
            }

            // Vérifier si l'organisateur existe
            $checkExistsSql = "SELECT id FROM organisateur WHERE id = :id";
            $checkExistsQuery = $this->db->prepare($checkExistsSql);
            $checkExistsQuery->execute(['id' => $id]);
            if (!$checkExistsQuery->fetch()) {
                throw new Exception("Organisateur non trouvé");
            }

            // Vérifier si l'email existe déjà pour un autre organisateur
            $checkSql = "SELECT id FROM organisateur WHERE email = :email AND id != :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['email' => $organisateur->getEmail(), 'id' => $id]);
            if ($checkQuery->fetch()) {
                throw new Exception("Cet email est déjà utilisé par un autre organisateur");
            }

            $sql = "UPDATE organisateur SET 
                    nom = :nom,
                    email = :email,
                    telephone = :telephone,
                    adresse = :adresse,
                    site_web = :site_web
                    WHERE id = :id";

            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'id' => $id,
                'nom' => htmlspecialchars($organisateur->getNom(), ENT_QUOTES),
                'email' => strtolower($organisateur->getEmail()),
                'telephone' => htmlspecialchars($organisateur->getTelephone(), ENT_QUOTES),
                'adresse' => htmlspecialchars($organisateur->getAdresse(), ENT_QUOTES),
                'site_web' => htmlspecialchars($organisateur->getSiteWeb(), ENT_QUOTES)
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Organisateur modifié avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la modification'];
        } catch (Exception $e) {
            error_log("UpdateOrganisateur error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * DELETE - Supprimer un organisateur
     */
    public function deleteOrganisateur($id) {
        try {
            // Validation PHP de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                throw new Exception("ID invalide");
            }

            // Vérifier si l'organisateur existe
            $checkExistsSql = "SELECT id, nom FROM organisateur WHERE id = :id";
            $checkExistsQuery = $this->db->prepare($checkExistsSql);
            $checkExistsQuery->execute(['id' => $id]);
            $organisateur = $checkExistsQuery->fetch();
            if (!$organisateur) {
                throw new Exception("Organisateur non trouvé");
            }

            // Vérifier si l'organisateur a des événements
            $eventCount = $this->getEventCountByOrganisateur($id);
            if ($eventCount > 0) {
                throw new Exception("Impossible de supprimer cet organisateur car il organise $eventCount événement(s). Supprimez d'abord ses événements.");
            }

            $sql = "DELETE FROM organisateur WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);

            if ($result) {
                return ['success' => true, 'message' => 'Organisateur "' . htmlspecialchars($organisateur['nom']) . '" supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            error_log("DeleteOrganisateur error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * STATISTIQUES - Obtenir les statistiques des organisateurs
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Total organisateurs
            $sql = "SELECT COUNT(*) as total FROM organisateur";
            $result = $this->db->query($sql);
            $stats['total'] = (int)$result->fetch()['total'];
            
            // Total événements organisés
            $sql = "SELECT COUNT(*) as total_events FROM evenement";
            $result = $this->db->query($sql);
            $stats['total_events'] = (int)$result->fetch()['total_events'];
            
            // Moyenne d'événements par organisateur
            if ($stats['total'] > 0) {
                $stats['avg_events'] = round($stats['total_events'] / $stats['total'], 2);
            } else {
                $stats['avg_events'] = 0;
            }
            
            // Organisateur avec le plus d'événements
            $sql = "SELECT o.nom, COUNT(e.id) as event_count 
                    FROM organisateur o 
                    LEFT JOIN evenement e ON o.id = e.organisateur_id 
                    GROUP BY o.id 
                    ORDER BY event_count DESC LIMIT 1";
            $result = $this->db->query($sql);
            $stats['top_organisateur'] = $result->fetch();
            
            return $stats;
        } catch (Exception $e) {
            error_log("GetStats error: " . $e->getMessage());
            return [
                'total' => 0,
                'total_events' => 0,
                'avg_events' => 0,
                'top_organisateur' => null
            ];
        }
    }

    /**
     * UTILITAIRE - Vérifier si un email existe déjà
     */
    public function emailExists($email, $excludeId = null) {
        try {
            // Validation PHP de l'email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            
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
            error_log("EmailExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UTILITAIRE - Obtenir le nombre total d'organisateurs
     */
    public function getTotalOrganisateurs() {
        try {
            $sql = "SELECT COUNT(*) as total FROM organisateur";
            $result = $this->db->query($sql);
            return (int)$result->fetch()['total'];
        } catch (Exception $e) {
            error_log("GetTotalOrganisateurs error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * UTILITAIRE - Obtenir le dernier organisateur ajouté
     */
    public function getLastOrganisateur() {
        try {
            $sql = "SELECT * FROM organisateur ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($sql);
            return $result->fetch();
        } catch (Exception $e) {
            error_log("GetLastOrganisateur error: " . $e->getMessage());
            return null;
        }
    }
}
?>