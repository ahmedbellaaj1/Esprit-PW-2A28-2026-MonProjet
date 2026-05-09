<?php
include 'config/db.php';

try {
    // Insérer des ingrédients
    $ingredients = ['Lait', 'Oeuf', 'Blé', 'Soja', 'Arachide', 'Tomate', 'Laitue', 'Poulet', 'Boeuf', 'Fromage'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO ingredients (nom) VALUES (?)");
    foreach ($ingredients as $ing) {
        $stmt->execute([$ing]);
    }

    // Insérer des produits
    $products = [
        [1, 'Salade César', '123456789', 'Salade fraîche avec poulet grillé et parmesan', 'Salades'],
        [2, 'Burger Végétarien', '987654321', 'Burger sans viande avec galette végétale', 'Fast Food'],
        [3, 'Yaourt Nature', '456789123', 'Yaourt au lait entier nature', 'Produits Laitiers'],
        [4, 'Pain Complet', '789123456', 'Pain aux céréales complètes', 'Boulangerie'],
        [5, 'Pâtes à la Bolognaise', '321654987', 'Pâtes avec sauce à la viande', 'Plats préparés']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO produits (id, nom, barcode, description, categorie) VALUES (?, ?, ?, ?, ?)");
    foreach ($products as $prod) {
        $stmt->execute($prod);
    }

    // Associer ingrédients aux produits
    $productIngredients = [
        [1, ['Laitue', 'Tomate', 'Poulet', 'Fromage']], // Salade César
        [2, ['Tomate', 'Laitue', 'Soja']], // Burger Végé
        [3, ['Lait']], // Yaourt
        [4, ['Blé']], // Pain
        [5, ['Blé', 'Boeuf', 'Tomate']] // Pâtes Bolognaise
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO produit_ingredients (id_produit, id_ingredient) 
                           SELECT ?, id FROM ingredients WHERE nom = ?");
    foreach ($productIngredients as $pi) {
        $productId = $pi[0];
        foreach ($pi[1] as $ingName) {
            $stmt->execute([$productId, $ingName]);
        }
    }

    // Ajouter des allergènes aux produits
    $allergenes = [
        [1, 'Lactose', 'a_verifier'], // Salade César - fromage
        [3, 'Lactose', 'interdit'], // Yaourt
        [4, 'Gluten', 'interdit'], // Pain
        [5, 'Gluten', 'interdit'] // Pâtes
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO allergenes_produits (id_produit, allergene, niveau) VALUES (?, ?, ?)");
    foreach ($allergenes as $all) {
        $stmt->execute($all);
    }

    // Ajouter des préférences aux produits
    $preferences = [
        [2, 'Végétarien', true], // Burger Végé
        [2, 'Vegan', false], // Burger Végé - pas vegan
        [3, 'Végétarien', true], // Yaourt
        [3, 'Vegan', false], // Yaourt - contient lait
        [4, 'Végétarien', true], // Pain
        [4, 'Vegan', true], // Pain
        [5, 'Végétarien', false] // Pâtes Bolognaise
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO preferences_produits (id_produit, preference, compatible) VALUES (?, ?, ?)");
    foreach ($preferences as $pref) {
        $stmt->execute($pref);
    }

    // Ajouter des valeurs nutritionnelles
    $nutrition = [
        [1, 'Calories', 350, 'kcal'],
        [1, 'Protéines', 25, 'g'],
        [2, 'Calories', 450, 'kcal'],
        [3, 'Calories', 80, 'kcal'],
        [3, 'Protéines', 3, 'g'],
        [4, 'Calories', 120, 'kcal']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO valeurs_nutritionnelles (id_produit, nutrient, valeur, unite) VALUES (?, ?, ?, ?)");
    foreach ($nutrition as $nut) {
        $stmt->execute($nut);
    }

    echo "Données insérées avec succès.";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>