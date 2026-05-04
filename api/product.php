<?php
/**
 * API Produit - Récupérer les détails d'un produit
 * GET /api/product.php?id={id_produit}
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $id_produit = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$id_produit) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID produit invalide'
        ]);
        exit;
    }
    
    $pdo = Database::connection();
    
    // Récupérer le produit
    $stmt = $pdo->prepare('
        SELECT 
            id_produit,
            nom,
            marque,
            code_barre,
            categorie,
            prix,
            calories,
            proteines,
            glucides,
            lipides,
            nutriscore,
            image,
            quantite_disponible,
            statut,
            date_ajout
        FROM produits
        WHERE id_produit = ? AND statut = "actif"
    ');
    
    $stmt->execute([$id_produit]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Produit non trouvé'
        ]);
        exit;
    }
    
    // Récupérer les avis
    $stmtAvis = $pdo->prepare('
        SELECT 
            AVG(note) as note_moyenne,
            COUNT(id_avis) as nombre_avis
        FROM avis
        WHERE id_produit = ? AND statut = "approuve"
    ');
    
    $stmtAvis->execute([$id_produit]);
    $avis = $stmtAvis->fetch(PDO::FETCH_ASSOC);
    
    // Réponse
    echo json_encode([
        'success' => true,
        'product' => [
            'id_produit' => (int)$product['id_produit'],
            'nom' => (string)$product['nom'],
            'marque' => (string)$product['marque'],
            'code_barre' => (string)$product['code_barre'],
            'categorie' => (string)$product['categorie'],
            'prix' => (float)$product['prix'],
            'calories' => (float)$product['calories'],
            'proteines' => (float)$product['proteines'],
            'glucides' => (float)$product['glucides'],
            'lipides' => (float)$product['lipides'],
            'nutriscore' => (string)$product['nutriscore'],
            'image' => (string)$product['image'],
            'quantite_disponible' => (int)$product['quantite_disponible'],
            'note_moyenne' => (float)($avis['note_moyenne'] ?? 0),
            'nombre_avis' => (int)($avis['nombre_avis'] ?? 0)
        ]
    ]);
    
} catch (Throwable $e) {
    http_response_code(500);
    error_log("Erreur API Produit: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
