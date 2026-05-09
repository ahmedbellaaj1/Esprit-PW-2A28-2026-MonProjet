<?php
require_once __DIR__ . "/../model/ProductModel.php";
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/Product.php";

class AdminController {
    public static function getDashboardData($pdo) {
        // Fetch users (this logic could be in a UserModel)
        $sqlUsers = "SELECT u.id as user_real_id, u.nom, u.email, 
                            pa.id as pref_id, pa.type_preference, pa.poids, pa.age, pa.calories,
                            (SELECT GROUP_CONCAT(nom_allergie SEPARATOR ', ') FROM allergies WHERE id_user = u.id) as allergies
                     FROM users u
                     LEFT JOIN preferences_alimentaires pa ON u.id = pa.id_user
                     ORDER BY u.id DESC";
        $stmt = $pdo->query($sqlUsers);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $u = new User();
            $u->setNom($row['nom']);
            $u->setEmail($row['email']);
            $u->setPreferences($row['type_preference']);
            $u->setAllergies($row['allergies']);
            $u->setPoids($row['poids']);
            $u->setAge($row['age']);
            $u->setCalories($row['calories']);
            $u->setId($row['user_real_id']);
            $users[] = $u;
        }

        // Fetch products using ProductModel
        $products = ProductModel::getAll($pdo);

        return [
            'users' => $users,
            'products' => $products
        ];
    }

    public static function getStats($pdo) {
        // 1. Nombre total de préférences alimentaires
        $stmtPref = $pdo->query("SELECT COUNT(*) FROM preferences_alimentaires WHERE type_preference IS NOT NULL AND type_preference != ''");
        $totalPrefs = $stmtPref->fetchColumn();
        
        // 2. Moyenne des calories visées
        $stmtCal = $pdo->query("SELECT AVG(calories) FROM preferences_alimentaires WHERE calories > 0");
        $avgCalories = round($stmtCal->fetchColumn() ?? 0, 0);

        // 3. Moyenne d'âge
        $stmtAge = $pdo->query("SELECT AVG(age) FROM preferences_alimentaires WHERE age > 0");
        $avgAge = round($stmtAge->fetchColumn() ?? 0, 1);

        // 4. Poids moyen
        $stmtPoids = $pdo->query("SELECT AVG(poids) FROM preferences_alimentaires WHERE poids > 0");
        $avgPoids = round($stmtPoids->fetchColumn() ?? 0, 1);

        return [
            'total_preferences' => $totalPrefs,
            'avg_calories' => $avgCalories,
            'avg_age' => $avgAge,
            'avg_poids' => $avgPoids
        ];
    }
}
