<?php
require_once "../config/db.php";
require_once "../usermodel/UserModel.php";

class UserController {

    // 🔹 GET (charger données)
    public static function getProfile() {
        global $pdo;

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_GET['id_user'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "id_user manquant"]);
            return;
        }

        $id_user = intval($_GET['id_user']);
        
        if ($id_user <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "id_user invalide"]);
            return;
        }

        try {
            $data = UserModel::getProfile($pdo, $id_user);
            echo json_encode(["status" => "success", "data" => $data]);
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

        $id_user = intval($data['id_user']);
        $preferences = is_array($data['preferences'] ?? null) ? $data['preferences'] : [];
        $allergies = is_array($data['allergies'] ?? null) ? $data['allergies'] : [];

        if ($id_user <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "id_user invalide"]);
            return;
        }

        try {
            UserModel::saveProfile($pdo, $id_user, $preferences, $allergies);
            echo json_encode(["status" => "success", "message" => "Profil enregistré avec succès"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
?>