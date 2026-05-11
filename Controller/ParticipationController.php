<?php
/**
 * ParticipationController - Adapté pour Green-Bite
 * Utilise getPdo() et $_SESSION['user'] de Green-Bite
 * Le user_id est récupéré depuis la table users de greenbite
 */
require_once __DIR__ . '/../Model/Participation.php';

class ParticipationController {
    private $db;

    public function __construct() {
        $this->db = getPdo();
    }

    public function isUserRegistered($evenement_id, $user_id) {
        try {
            $sql = "SELECT id FROM participation WHERE evenement_id = :event_id AND user_id = :user_id";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id, 'user_id' => $user_id]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function countParticipantsByEvent($evenement_id) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            $query = $this->db->prepare("SELECT COUNT(*) as count FROM participation WHERE evenement_id = :event_id AND statut != 'annule'");
            $query->execute(['event_id' => $evenement_id]);
            return (int)$query->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function countParticipantsByStatut($evenement_id, $statut) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            $validStatuts = ['inscrit', 'present', 'annule', 'en_attente'];
            if (!in_array($statut, $validStatuts)) return 0;
            $query = $this->db->prepare("SELECT COUNT(*) as count FROM participation WHERE evenement_id = :event_id AND statut = :statut");
            $query->execute(['event_id' => $evenement_id, 'statut' => $statut]);
            return (int)$query->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function addParticipation($participation) {
        try {
            if (!$participation->isValid()) throw new Exception("Données invalides");
            if ($this->isUserRegistered($participation->getEvenementId(), $participation->getUserId())) {
                throw new Exception("Vous êtes déjà inscrit à cet événement");
            }
            $remainingPlaces = $this->checkRemainingCapacity($participation->getEvenementId());
            if ($remainingPlaces <= 0) throw new Exception("Désolé, cet événement est complet");

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
                    'message' => "Inscription réussie ! Un email de confirmation vous a été envoyé.",
                    'id' => $id,
                    'qr_token' => $uniqueToken
                ];
            }
            return ['success' => false, 'message' => "Erreur lors de l'inscription"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer email de confirmation - utilise directement la table users de greenbite
     */
    private function sendConfirmationEmail($evenement_id, $user_id, $participation_id, $qr_token) {
        try {
            require_once __DIR__ . '/EvenementController.php';
            require_once __DIR__ . '/EvenementMailController.php';

            $eventController = new EvenementController();
            $mailController = new EvenementMailController();

            $event = $eventController->getEvenementById($evenement_id);

            // Récupérer l'utilisateur directement depuis la table users de greenbite
            $userQuery = $this->db->prepare("SELECT id, nom, prenom, email FROM users WHERE id = :id");
            $userQuery->execute(['id' => $user_id]);
            $user = $userQuery->fetch();

            if ($user && $event) {
                $mailController->sendConfirmationEmail($user, $event, $participation_id, $qr_token);
            }
        } catch (Exception $e) {
            error_log("sendConfirmationEmail error: " . $e->getMessage());
        }
    }

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
            return [];
        }
    }

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
            return [];
        }
    }

    public function getAllParticipations() {
        try {
            $sql = "SELECT p.*, e.titre as event_titre, e.date_event, e.lieu,
                           u.id as user_id, u.nom, u.prenom, u.email, u.photo, u.role
                    FROM participation p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    LEFT JOIN users u ON p.user_id = u.id 
                    ORDER BY p.date_inscription DESC";
            return $this->db->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function updateParticipationStatut($id, $statut) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $validStatuts = ['inscrit', 'present', 'annule', 'en_attente'];
            if (!in_array($statut, $validStatuts)) throw new Exception("Statut invalide");
            $sql = "UPDATE participation SET statut = :statut, date_validation = NOW() WHERE id = :id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id, 'statut' => $statut]);
            if ($result && $statut === 'present') {
                $this->sendReceiptEmail($id);
            }
            return ['success' => $result, 'message' => $result ? 'Statut mis à jour' : 'Erreur lors de la mise à jour'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendReceiptEmail($participation_id) {
        try {
            require_once __DIR__ . '/EvenementController.php';
            require_once __DIR__ . '/EvenementMailController.php';
            $participation = $this->getParticipationById($participation_id);
            if (!$participation) return;
            $eventController = new EvenementController();
            $mailController = new EvenementMailController();
            $event = $eventController->getEvenementById($participation['evenement_id']);
            $userQuery = $this->db->prepare("SELECT id, nom, prenom, email FROM users WHERE id = :id");
            $userQuery->execute(['id' => $participation['user_id']]);
            $user = $userQuery->fetch();
            if ($user && $event) {
                $mailController->sendReceiptEmail($user, $event, $participation);
            }
        } catch (Exception $e) {
            error_log("sendReceiptEmail error: " . $e->getMessage());
        }
    }

    public function deleteParticipation($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) throw new Exception("ID invalide");
            $checkQuery = $this->db->prepare("SELECT id FROM participation WHERE id = :id");
            $checkQuery->execute(['id' => $id]);
            if (!$checkQuery->fetch()) throw new Exception("Participation non trouvée");
            $query = $this->db->prepare("DELETE FROM participation WHERE id = :id");
            $result = $query->execute(['id' => $id]);
            return ['success' => $result, 'message' => $result ? 'Participation supprimée avec succès' : 'Erreur lors de la suppression'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkRemainingCapacity($evenement_id) {
        try {
            $evenement_id = filter_var($evenement_id, FILTER_VALIDATE_INT);
            if (!$evenement_id || $evenement_id <= 0) return 0;
            $sql = "SELECT e.capacite_max, COUNT(p.id) as inscrits 
                    FROM evenement e 
                    LEFT JOIN participation p ON e.id = p.evenement_id AND p.statut != 'annule'
                    WHERE e.id = :event_id GROUP BY e.id";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $evenement_id]);
            $result = $query->fetch();
            if (!$result) return 0;
            $capacite = (int)$result['capacite_max'];
            $inscrits = (int)$result['inscrits'];
            if ($capacite == 0) return 999999;
            return max(0, $capacite - $inscrits);
        } catch (Exception $e) {
            return 0;
        }
    }

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
            return null;
        }
    }

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
            return null;
        }
    }

    public function getTotalParticipations() {
        try {
            return (int)$this->db->query("SELECT COUNT(*) as total FROM participation")->fetch()['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getStats() {
        try {
            $stats = [];
            $stats['total'] = (int)$this->db->query("SELECT COUNT(*) as total FROM participation")->fetch()['total'];
            $stats['byStatut'] = $this->db->query("SELECT statut, COUNT(*) as count FROM participation GROUP BY statut")->fetchAll();
            $sql = "SELECT e.titre, COUNT(p.id) as nb_participants 
                    FROM evenement e LEFT JOIN participation p ON e.id = p.evenement_id 
                    GROUP BY e.id ORDER BY nb_participants DESC LIMIT 5";
            $stats['topEvents'] = $this->db->query($sql)->fetchAll();
            $sql = "SELECT u.nom, u.prenom, COUNT(p.id) as nb_participations 
                    FROM users u LEFT JOIN participation p ON u.id = p.user_id 
                    GROUP BY u.id ORDER BY nb_participations DESC LIMIT 5";
            $stats['topUsers'] = $this->db->query($sql)->fetchAll();
            $sql = "SELECT DATE(date_inscription) as jour, COUNT(*) as inscriptions 
                    FROM participation WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(date_inscription) ORDER BY jour DESC";
            $stats['inscriptionsByDay'] = $this->db->query($sql)->fetchAll();
            return $stats;
        } catch (Exception $e) {
            return ['total' => 0, 'byStatut' => [], 'topEvents' => [], 'topUsers' => [], 'inscriptionsByDay' => []];
        }
    }
}
?>
