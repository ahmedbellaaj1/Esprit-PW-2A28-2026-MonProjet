<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/UserModel.php";

class UserController {

    // 🔹 GET (charger données)
    public static function getProfile() {
        global $pdo;

        header('Content-Type: application/json; charset=utf-8');

        try {
            $u = new User();
            $u->setId($_GET['id_user'] ?? 0);
            
            if ($u->getId() <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "id_user invalide"]);
                return;
            }

            $u = UserModel::getById($pdo, $u->getId());
            if (!$u) {
                 echo json_encode(["status" => "error", "message" => "Utilisateur non trouvé"]);
                 return;
            }

            // On récupère les détails via UserModel
            $details = UserModel::getProfileDetails($pdo, $u->getId());
            
            // On peut setter les valeurs dans l'objet User (même si elles y sont déjà peut-être partiellement)
            $u->setPreferences($details['preferences']);
            $u->setAllergies($details['allergies']);

            // Et on utilise les GETTERS pour construire la réponse
            echo json_encode([
                "status" => "success", 
                "data" => [
                    "preferences" => $u->getPreferences(),
                    "allergies" => $u->getAllergies()
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // 🔹 POST (enregistrer données)
    public static function saveProfile() {
        global $pdo;

        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id_user'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Données manquantes ou invalides"]);
            return;
        }

        try {
            $user = new User();
            $user->setId($data['id_user'] ?? 0);
            $user->setPreferences($data['preferences'] ?? []);
            $user->setAllergies($data['allergies'] ?? []);

            if ($user->getId() <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "id_user invalide"]);
                return;
            }

            // Utiliser le Model avec l'objet
            UserModel::saveProfile($pdo, $user);
            
            echo json_encode(["status" => "success", "message" => "Profil enregistré avec succès"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public static function register($pdo, $postData) {
        try {
            $user = new User();
            $user->setNom($postData['nom'] ?? '');
            $user->setEmail($postData['email'] ?? '');
            $user->setPreferences($postData['preferences'] ?? '');
            $user->setAllergies($postData['allergies'] ?? '');
            $user->setPoids($postData['poids'] ?? 0);
            $user->setAge($postData['age'] ?? 0);
            $user->setCalories($postData['calories'] ?? 0);

            if (empty($user->getNom()) || empty($user->getEmail())) {
                throw new Exception("Nom et email obligatoires");
            }

            $user_id = UserModel::save($pdo, $user);
            $_SESSION['user_id'] = $user_id;

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?>