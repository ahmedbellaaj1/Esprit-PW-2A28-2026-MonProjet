<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Participation.php";

class ParticipationController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Vérifier si un utilisateur est déjà inscrit
     */
    public function isUserRegistered($evenement_id, $user_id) {
        try {
            $sql = "SELECT id FROM participation WHERE evenement_id = :event_id AND user_id = :user_id";
            $query = $this->db->prepare($sql);
            $query->execute([
                'event_id' => $evenement_id,
                'user_id' => $user_id
            ]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            error_log("isUserRegistered error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compter les participants par événement
     */
    public function countParticipantsByEvent($evenement_id) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            
            $sql = "SELECT COUNT(*) as count FROM participation WHERE evenement_id = :event_id AND statut != 'annule'";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            $result = $query->fetch();
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("countParticipantsByEvent error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compter les participants par statut
     */
    public function countParticipantsByStatut($evenement_id, $statut) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            
            $validStatuts = ['inscrit', 'present', 'annule', 'en_attente'];
            if (!in_array($statut, $validStatuts)) return 0;
            
            $sql = "SELECT COUNT(*) as count FROM participation WHERE evenement_id = :event_id AND statut = :statut";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id, 'statut' => $statut]);
            $result = $query->fetch();
            return (int)$result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Ajouter une participation avec envoi d'email et QR code
     */
    public function addParticipation($participation) {
        try {
            if (!$participation->isValid()) {
                throw new Exception("Données invalides");
            }

            // Vérifier si déjà inscrit
            if ($this->isUserRegistered($participation->getEvenementId(), $participation->getUserId())) {
                throw new Exception("Vous êtes déjà inscrit à cet événement");
            }

            // Vérifier la capacité
            $remainingPlaces = $this->checkRemainingCapacity($participation->getEvenementId());
            if ($remainingPlaces <= 0) {
                throw new Exception("Désolé, cet événement est complet");
            }

            // Générer un token unique pour le QR code
            $uniqueToken = uniqid('QR_') . bin2hex(random_bytes(16));
            
            $sql = "INSERT INTO participation (evenement_id, user_id, code_qr, statut) 
                    VALUES (:event_id, :user_id, :code_qr, :statut)";
            
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'event_id' => $participation->getEvenementId(),
                'user_id' => $participation->getUserId(),
                'code_qr' => $uniqueToken,
                'statut' => $participation->getStatut()
            ]);

            if ($result) {
                $id = $this->db->lastInsertId();
                
                // Envoyer l'email de confirmation avec QR code
                $this->sendConfirmationEmail($participation->getEvenementId(), $participation->getUserId(), $id, $uniqueToken);
                
                return [
                    'success' => true, 
                    'message' => 'Inscription réussie ! Un email de confirmation vous a été envoyé.', 
                    'id' => $id,
                    'qr_token' => $uniqueToken
                ];
            }
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        } catch (Exception $e) {
            error_log("addParticipation error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer l'email de confirmation avec QR code (chemins corrigés)
     */
    private function sendConfirmationEmail($evenement_id, $user_id, $participation_id, $qr_token) {
        try {
            require_once __DIR__ . "/EvenementController.php";
            require_once __DIR__ . "/MailController.php";
            
            // Correction du chemin vers UserRepository - Version flexible
            $userRepoPaths = [
                __DIR__ . "/../ModuleUser/Controller/UserRepository.php",
                __DIR__ . "/../Controller/UserRepository.php",
                __DIR__ . "/../model/UserRepository.php"
            ];
            
            $found = false;
            foreach ($userRepoPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                error_log("UserRepository.php not found in any expected location");
                return;
            }
            
            $eventController = new EvenementController();
            $userRepo = new UserRepository();
            $mailController = new MailController();
            
            $event = $eventController->getEvenementById($evenement_id);
            $user = $userRepo->findById($user_id);
            
            if ($user && $event) {
                $userArray = [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ];
                
                $mailController->sendConfirmationEmail($userArray, $event, $participation_id, $qr_token);
            }
        } catch (Exception $e) {
            error_log("sendConfirmationEmail error: " . $e->getMessage());
        }
    }

    /**
     * Récupérer les participations d'un utilisateur
     */
    public function getParticipationsByUser($user_id) {
        try {
            $user_id = filter_var($user_id, FILTER_VALIDATE_INT);
            if (!$user_id || $user_id <= 0) return [];
            
            $sql = "SELECT p.*, e.titre as event_titre, e.date_event, e.lieu, e.type 
                    FROM participation p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    WHERE p.user_id = :user_id 
                    ORDER BY e.date_event ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['user_id' => $user_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("getParticipationsByUser error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer tous les participants d'un événement (admin)
     */
    public function getParticipantsByEvent($evenement_id) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return [];
            
            $sql = "SELECT p.*, u.nom, u.prenom, u.email, u.photo 
                    FROM participation p 
                    LEFT JOIN users u ON p.user_id = u.id 
                    WHERE p.evenement_id = :event_id 
                    ORDER BY p.date_inscription ASC";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("getParticipantsByEvent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer toutes les participations (admin)
     */
    public function getAllParticipations() {
        try {
            $sql = "SELECT p.*, e.titre as event_titre, e.date_event, e.lieu,
                           u.id as user_id, u.nom, u.prenom, u.email, u.photo, u.role
                    FROM participation p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    LEFT JOIN users u ON p.user_id = u.id 
                    ORDER BY p.date_inscription DESC";
            $result = $this->db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            error_log("getAllParticipations error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mettre à jour le statut d'une participation
     */
    public function updateParticipationStatut($id, $statut) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                throw new Exception("ID invalide");
            }
            
            $validStatuts = ['inscrit', 'present', 'annule', 'en_attente'];
            if (!in_array($statut, $validStatuts)) {
                throw new Exception("Statut invalide");
            }
            
            $sql = "UPDATE participation SET statut = :statut, date_validation = NOW() WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id, 'statut' => $statut]);
            
            // Si le statut devient "present", envoyer un récépissé
            if ($result && $statut === 'present') {
                $this->sendReceiptEmail($id);
            }
            
            return ['success' => $result, 'message' => $result ? 'Statut mis à jour' : 'Erreur lors de la mise à jour'];
        } catch (Exception $e) {
            error_log("updateParticipationStatut error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer un email de récépissé après validation (chemins corrigés)
     */
    private function sendReceiptEmail($participation_id) {
        try {
            require_once __DIR__ . "/EvenementController.php";
            require_once __DIR__ . "/MailController.php";
            
            // Correction du chemin vers UserRepository
            $userRepoPaths = [
                __DIR__ . "/../ModuleUser/Controller/UserRepository.php",
                __DIR__ . "/../Controller/UserRepository.php",
                __DIR__ . "/../model/UserRepository.php"
            ];
            
            $found = false;
            foreach ($userRepoPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                error_log("UserRepository.php not found in any expected location");
                return;
            }
            
            $participation = $this->getParticipationById($participation_id);
            if (!$participation) return;
            
            $eventController = new EvenementController();
            $userRepo = new UserRepository();
            $mailController = new MailController();
            
            $event = $eventController->getEvenementById($participation['evenement_id']);
            $user = $userRepo->findById($participation['user_id']);
            
            if ($user && $event) {
                $userArray = [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ];
                
                $mailController->sendReceiptEmail($userArray, $event, $participation);
            }
        } catch (Exception $e) {
            error_log("sendReceiptEmail error: " . $e->getMessage());
        }
    }

    /**
     * Supprimer une participation
     */
    public function deleteParticipation($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                throw new Exception("ID invalide");
            }
            
            // Vérifier si la participation existe
            $checkSql = "SELECT id FROM participation WHERE id = :id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['id' => $id]);
            if (!$checkQuery->fetch()) {
                throw new Exception("Participation non trouvée");
            }
            
            $sql = "DELETE FROM participation WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);
            
            return ['success' => $result, 'message' => $result ? 'Participation supprimée avec succès' : 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            error_log("deleteParticipation error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifier la capacité restante
     */
    public function checkRemainingCapacity($evenement_id) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            
            $sql = "SELECT e.capacite_max, COUNT(p.id) as inscrits 
                    FROM evenement e 
                    LEFT JOIN participation p ON e.id = p.evenement_id AND p.statut != 'annule'
                    WHERE e.id = :event_id
                    GROUP BY e.id";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            $result = $query->fetch();
            
            if (!$result) return 0;
            
            $capacite = (int)$result['capacite_max'];
            $inscrits = (int)$result['inscrits'];
            
            if ($capacite == 0) return 999999;
            return max(0, $capacite - $inscrits);
        } catch (Exception $e) {
            error_log("checkRemainingCapacity error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupérer une participation par ID
     */
    public function getParticipationById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) return null;
            
            $sql = "SELECT p.*, e.titre as event_titre, e.date_event, e.lieu, e.type,
                           u.id as user_id, u.nom, u.prenom, u.email, u.photo, u.role
                    FROM participation p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    LEFT JOIN users u ON p.user_id = u.id 
                    WHERE p.id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("getParticipationById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer une participation par token QR
     */
    public function getParticipationByToken($token) {
        try {
            if (empty($token)) return null;
            
            $sql = "SELECT p.*, e.titre as event_titre, e.date_event, e.lieu, e.type,
                           u.id as user_id, u.nom, u.prenom, u.email, u.photo, u.role
                    FROM participation p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    LEFT JOIN users u ON p.user_id = u.id 
                    WHERE p.code_qr = :token";
            $query = $this->db->prepare($sql);
            $query->execute(['token' => $token]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("getParticipationByToken error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer le nombre total de participations
     */
    public function getTotalParticipations() {
        try {
            $sql = "SELECT COUNT(*) as total FROM participation";
            $result = $this->db->query($sql);
            return (int)$result->fetch()['total'];
        } catch (Exception $e) {
            error_log("getTotalParticipations error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupérer les statistiques des participations
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Total des participations
            $sql = "SELECT COUNT(*) as total FROM participation";
            $result = $this->db->query($sql);
            $stats['total'] = (int)$result->fetch()['total'];
            
            // Participations par statut
            $sql = "SELECT statut, COUNT(*) as count FROM participation GROUP BY statut";
            $result = $this->db->query($sql);
            $stats['byStatut'] = $result->fetchAll();
            
            // Top 5 événements les plus populaires
            $sql = "SELECT e.titre, COUNT(p.id) as nb_participants 
                    FROM evenement e 
                    LEFT JOIN participation p ON e.id = p.evenement_id 
                    GROUP BY e.id 
                    ORDER BY nb_participants DESC 
                    LIMIT 5";
            $result = $this->db->query($sql);
            $stats['topEvents'] = $result->fetchAll();
            
            // Top 5 utilisateurs les plus actifs
            $sql = "SELECT u.nom, u.prenom, COUNT(p.id) as nb_participations 
                    FROM users u 
                    LEFT JOIN participation p ON u.id = p.user_id 
                    GROUP BY u.id 
                    ORDER BY nb_participations DESC 
                    LIMIT 5";
            $result = $this->db->query($sql);
            $stats['topUsers'] = $result->fetchAll();
            
            // Participations par jour (30 derniers jours)
            $sql = "SELECT DATE(date_inscription) as jour, COUNT(*) as inscriptions 
                    FROM participation 
                    WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(date_inscription) 
                    ORDER BY jour DESC";
            $result = $this->db->query($sql);
            $stats['inscriptionsByDay'] = $result->fetchAll();
            
            return $stats;
        } catch (Exception $e) {
            error_log("getStats error: " . $e->getMessage());
            return [
                'total' => 0,
                'byStatut' => [],
                'topEvents' => [],
                'topUsers' => [],
                'inscriptionsByDay' => []
            ];
        }
    }

    /**
     * Exporter les participations d'un événement en CSV
     */
    public function exportParticipationsToCSV($event_id) {
        try {
            $event_id = filter_var($event_id, FILTER_VALIDATE_INT);
            if (!$event_id || $event_id <= 0) {
                throw new Exception("ID d'événement invalide");
            }
            
            $participants = $this->getParticipantsByEvent($event_id);
            
            if (empty($participants)) {
                return ['success' => false, 'message' => 'Aucun participant à exporter'];
            }
            
            $filename = "participants_event_" . $event_id . "_" . date('Y-m-d') . ".csv";
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM pour UTF-8
            
            fputcsv($output, ['ID', 'Nom', 'Prénom', 'Email', 'Statut', "Date d'inscription", 'Code QR']);
            
            foreach ($participants as $p) {
                $fullName = trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? ''));
                fputcsv($output, [
                    $p['id'],
                    $fullName,
                    $p['email'] ?? '',
                    $p['statut'],
                    date('d/m/Y H:i', strtotime($p['date_inscription'])),
                    $p['code_qr']
                ]);
            }
            
            fclose($output);
            exit();
        } catch (Exception $e) {
            error_log("exportParticipationsToCSV error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>