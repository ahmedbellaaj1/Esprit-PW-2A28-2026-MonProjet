<?php
require_once __DIR__ . "/../model/ProductModel.php";
require_once __DIR__ . "/../model/Product.php";


class ProductController {
    public static function addProduct($pdo, $postData) {
        try {
            $product = new Product();
            $product->setNom($postData['nom'] ?? '');
            $product->setCategorie($postData['categorie'] ?? '');
            $product->setDescription($postData['description'] ?? '');
            $product->setCalories($postData['calories'] ?? 0);
            $product->setPrix($postData['prix'] ?? 0.00);

            if (empty($product->getNom()) || empty($product->getCategorie())) {
                return ['error' => true, 'message' => 'Le nom et la catégorie sont obligatoires.'];
            }

            ProductModel::add($pdo, $product);
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
