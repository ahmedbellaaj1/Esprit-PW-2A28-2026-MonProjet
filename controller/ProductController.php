<?php
require_once __DIR__ . "/../model/ProductModel.php";

class ProductController {
    public static function addProduct($pdo, $postData) {
        $nom = trim($postData['nom'] ?? '');
        $categorie = trim($postData['categorie'] ?? '');
        $description = trim($postData['description'] ?? '');
        $calories = intval($postData['calories'] ?? 0);
        $prix = floatval($postData['prix'] ?? 0.00);

        if (empty($nom) || empty($categorie)) {
            return ['error' => true, 'message' => 'Le nom et la catégorie sont obligatoires.'];
        }

        try {
            ProductModel::add($pdo, [
                'nom' => $nom,
                'categorie' => $categorie,
                'description' => $description,
                'calories' => $calories,
                'prix' => $prix
            ]);
            return ['error' => false, 'message' => 'Produit ajouté avec succès !'];
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Erreur lors de l\'ajout', 'details' => $e->getMessage()];
        }
    }

    public static function deleteProduct($pdo, $id) {
        try {
            ProductModel::delete($pdo, $id);
            return ['success' => true, 'message' => 'Produit supprimé'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
