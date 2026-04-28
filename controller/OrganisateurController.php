<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Organisateur.php";

class OrganisateurController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    // CREATE
    public function addOrganisateur($organisateur) {
        try {
            if (!$organisateur->isValid()) {
                $errors = $organisateur->getErrors();
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
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Organisateur ajouté avec succès', 'id' => $this->db->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // READ - Liste tous les organisateurs
    public function listOrganisateurs() {
        try {
            $sql = "SELECT * FROM organisateur ORDER BY nom ASC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // READ - Un organisateur par ID
    public function getOrganisateurById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            $sql = "SELECT * FROM organisateur WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            
            if (!$result) {
                throw new Exception("Organisateur non trouvé");
            }
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    // READ - Nombre d'événements par organisateur
    public function getEventCountByOrganisateur($id) {
        try {
            $sql = "SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $result = $query->fetch();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // UPDATE
    public function updateOrganisateur($organisateur, $id) {
        try {
            if (!$organisateur->isValid()) {
                $errors = $organisateur->getErrors();
                throw new Exception(implode(', ', $errors));
            }

            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
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
                'nom' => $organisateur->getNom(),
                'email' => $organisateur->getEmail(),
                'telephone' => $organisateur->getTelephone(),
                'adresse' => $organisateur->getAdresse(),
                'site_web' => $organisateur->getSiteWeb()
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Organisateur modifié avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la modification'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // DELETE
    public function deleteOrganisateur($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID invalide");
            }

            // Vérifier si l'organisateur a des événements
            $checkSql = "SELECT COUNT(*) as count FROM evenement WHERE organisateur_id = :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['id' => $id]);
            $count = $checkQuery->fetch()['count'];
            
            if ($count > 0) {
                throw new Exception("Impossible de supprimer cet organisateur car il organise $count événement(s). Supprimez d'abord ses événements.");
            }

            $sql = "DELETE FROM organisateur WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);

            if ($result) {
                return ['success' => true, 'message' => 'Organisateur supprimé avec succès'];
            }
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Statistiques
    public function getStats() {
        try {
            $sql = "SELECT COUNT(*) as total FROM organisateur";
            $result = $this->db->query($sql);
            return $result->fetch()['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>