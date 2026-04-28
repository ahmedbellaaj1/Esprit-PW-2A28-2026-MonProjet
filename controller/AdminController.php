<?php
require_once __DIR__ . "/../model/ProductModel.php";

class AdminController {
    public static function getDashboardData($pdo) {
        // Fetch users (this logic could be in a UserModel)
        $sqlUsers = "SELECT u.id as user_real_id, u.nom, u.email, 
                            pa.id as pref_id, pa.type_preference, pa.poids, pa.age, pa.calories 
                     FROM users u
                     LEFT JOIN preferences_alimentaires pa ON u.id = pa.id_user
                     ORDER BY u.id DESC, pa.id ASC";
        $stmt = $pdo->query($sqlUsers);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch products using ProductModel
        $products = ProductModel::getAll($pdo);

        return [
            'users' => $users,
            'products' => $products
        ];
    }
}
