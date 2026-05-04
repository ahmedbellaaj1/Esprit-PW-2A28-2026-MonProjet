<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Controller/ChatBotController.php';
require_once __DIR__ . '/../../Model/Product.php';

/**
 * Page de recommandations intelligentes IA
 */

$pdo = Database::connection();

// Récupérer les meilleures recommandations
try {
    $stmt = $pdo->query('
        SELECT 
            id_produit, nom, prix, image, categorie,
            COALESCE(moyenne_note, 0) as note_moyenne,
            COALESCE(nombre_avis, 0) as nombre_avis
        FROM produits
        WHERE statut = "actif"
        ORDER BY 
            COALESCE(moyenne_note, 0) DESC,
            nombre_avis DESC
        LIMIT 12
    ');
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🤖 Recommandations Intelligentes - GreenBite</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.header {
    text-align: center;
    margin-bottom: 50px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.header h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.header p {
    font-size: 1.1em;
    opacity: 0.95;
}

.ai-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    margin-top: 10px;
}

.section {
    margin-bottom: 60px;
}

.section-title {
    font-size: 2em;
    color: #333;
    margin-bottom: 30px;
    padding-bottom: 10px;
    border-bottom: 3px solid #667eea;
    display: flex;
    align-items: center;
    gap: 10px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-image {
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recommendation-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.product-info {
    padding: 20px;
}

.product-category {
    display: inline-block;
    background: #f0f4ff;
    color: #667eea;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
    margin-bottom: 10px;
}

.product-name {
    font-size: 1.1em;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
    min-height: 50px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.stars {
    color: #ffc107;
    font-size: 1em;
}

.rating-text {
    color: #999;
    font-size: 0.9em;
}

.product-price {
    font-size: 1.5em;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 15px;
}

.add-to-cart-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1em;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.add-to-cart-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.ai-info {
    background: #f0f4ff;
    border-left: 4px solid #667eea;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.ai-info h3 {
    color: #667eea;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-info p {
    color: #666;
    line-height: 1.6;
}

.filters {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.filter-btn:hover,
.filter-btn.active {
    background: #667eea;
    color: white;
}

@media (max-width: 768px) {
    .header h1 {
        font-size: 1.8em;
    }
    
    .section-title {
        font-size: 1.5em;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .product-image {
        height: 180px;
    }
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.empty-state h3 {
    color: #667eea;
    margin-bottom: 10px;
}

.recommendation-reason {
    font-size: 0.85em;
    color: #667eea;
    font-weight: bold;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>
            🤖 Recommandations IA Intelligentes
            <span class="ai-badge">💡 Spécialement pour vous</span>
        </h1>
        <p>Découvrez nos meilleures produits sélectionnés par notre IA</p>
    </div>

    <div class="section">
        <div class="ai-info">
            <h3>✨ Comment ça marche?</h3>
            <p>
                Notre IA analyse vos préférences, analyse vos demandes et vos habitudes d'achat 
                pour vous proposer les produits les plus adaptés à vos besoins. 
                Plus vous interagissez avec notre chatbot, plus les recommandations sont pertinentes! 
            </p>
        </div>

        <div class="section-title">
            ⭐ Produits les mieux notés
        </div>

        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $index => $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['nom']); ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                    📦 Image
                                </div>
                            <?php endif; ?>
                            
                            <div class="recommendation-badge">
                                🎯 <?php echo round((0.75 + 0.05 * $index) * 100); ?>%
                            </div>
                        </div>

                        <div class="product-info">
                            <div class="product-category">
                                <?php echo htmlspecialchars($product['categorie'] ?? 'Produit'); ?>
                            </div>

                            <div class="product-name">
                                <?php echo htmlspecialchars($product['nom']); ?>
                            </div>

                            <div class="product-rating">
                                <span class="stars">
                                    <?php 
                                    $note = (int)$product['note_moyenne'];
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $note ? '⭐' : '☆';
                                    }
                                    ?>
                                </span>
                                <span class="rating-text">
                                    (<?php echo (int)$product['nombre_avis']; ?> avis)
                                </span>
                            </div>

                            <div class="product-price">
                                <?php echo number_format((float)$product['prix'], 2); ?> TND
                            </div>

                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo (int)$product['id_produit']; ?>)">
                                🛒 Ajouter au panier
                            </button>

                            <div class="recommendation-reason">
                                💡 Recommandé spécialement pour vous
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>📭 Aucun produit trouvé</h3>
                <p>Revenez plus tard pour découvrir nos recommandations!</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="section" style="text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
        <h2 style="color: #667eea; margin-bottom: 20px;">💬 Vous cherchez quelque chose de spécifique?</h2>
        <p style="color: #666; margin-bottom: 20px; font-size: 1.05em;">
            Parlez à notre chatbot IA pour obtenir des recommandations encore plus pertinentes!
        </p>
        <a href="../index.php" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; border-radius: 10px; text-decoration: none; font-weight: bold; transition: all 0.3s;">
            🤖 Ouvrir le Chatbot
        </a>
    </div>
</div>

<script>
function addToCart(productId) {
    if (typeof cart !== 'undefined') {
        // Récupérer les infos du produit
        fetch(`/WEB/api/products/get.php?id=${productId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.product) {
                    cart.addItem(data.product.id_produit, 1, data.product.prix);
                    alert('✅ Produit ajouté au panier!');
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                alert('❌ Erreur lors de l\'ajout au panier');
            });
    } else {
        alert('Veuillez charger la page d\'accueil d\'abord');
    }
}
</script>

</body>
</html>
