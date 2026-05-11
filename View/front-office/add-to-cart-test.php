<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🛒 Ajouter au panier - GreenBite</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    text-align: center;
    padding: 50px 20px;
}
.message {
    background: white;
    padding: 40px;
    border-radius: 10px;
    max-width: 500px;
    margin: 0 auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.icon { font-size: 3em; margin-bottom: 20px; }
h1 { color: #333; margin-bottom: 20px; }
p { color: #666; line-height: 1.6; margin-bottom: 20px; }
.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</head>
<body>

<div class="message">
    <div class="icon">🛒</div>
    <h1>Préparation du panier...</h1>
    <p>Ajout d'un produit et redirection vers le checkout</p>
    <div class="loader"></div>
</div>

<script src="../assets/cart.js"></script>
<script>
// Attendre que la page charge
window.addEventListener('load', function() {
    // Créer le panier
    const cart = new Cart();
    
    // Ajouter un produit de test
    const testProduct = {
        id_produit: 1,
        nom: 'Salade Verte Bio',
        marque: 'GreenBite',
        prix: 5.99,
        image: '../assets/default-product.jpg',
        quantite: 1
    };
    
    // Ajouter au panier
    cart.addItem(testProduct);
    
    // Rediriger vers le checkout après 1 seconde
    setTimeout(function() {
        window.location.href = 'checkout.php';
    }, 1000);
});
</script>

</body>
</html>
