<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚠️ Panier Vide - Solution</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 700px;
    padding: 40px;
}
h1 {
    color: #667eea;
    margin-bottom: 10px;
    font-size: 1.8em;
}
.subtitle {
    color: #666;
    margin-bottom: 30px;
}
.icon { font-size: 3em; margin-bottom: 20px; }
.problem {
    background: #fef3f2;
    border: 1px solid #fca5a5;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    color: #991b1b;
}
.problem h2 {
    color: #991b1b;
    margin-bottom: 10px;
}
.solution {
    background: #d4edda;
    border: 1px solid #28a745;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}
.solution h2 {
    color: #155724;
    margin-bottom: 15px;
}
.button {
    display: inline-block;
    padding: 12px 30px;
    background: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    margin: 10px 5px;
    border: none;
    cursor: pointer;
    font-size: 1em;
}
.button:hover {
    background: #20c997;
}
.button-secondary {
    background: #667eea;
}
.button-secondary:hover {
    background: #764ba2;
}
.steps {
    text-align: left;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}
.step {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.step:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.step strong {
    color: #667eea;
}
</style>
</head>
<body>

<div class="container">
    <div style="text-align: center;">
        <div class="icon">⚠️</div>
        <h1>Votre Panier Est Vide</h1>
        <div class="subtitle">C'est normal! Voici comment le remplir.</div>
    </div>

    <div class="problem">
        <h2>❌ Problème</h2>
        <p>
            Vous êtes allé au <strong>Checkout</strong> sans ajouter de produits au panier.
            C'est pour ça que vous voyez l'erreur: <strong>"Votre panier est vide"</strong>
        </p>
    </div>

    <div class="solution">
        <h2>✅ Solution (2 Options)</h2>

        <div class="steps">
            <div class="step">
                <strong>OPTION 1: Rapide (Automatique) ⚡</strong>
                <p style="margin-top: 10px; color: #666;">
                    Cliquez ce bouton pour ajouter un produit automatiquement:
                </p>
                <a href="add-to-cart-test.php" class="button" style="margin-top: 10px;">
                    🛒 Ajouter Produit & Aller au Checkout
                </a>
            </div>

            <div class="step">
                <strong>OPTION 2: Manuel 🛍️</strong>
                <p style="margin-top: 10px; color: #666;">
                    Aller à la boutique et ajouter des produits vous-même:
                </p>
                <ol style="margin-left: 20px; margin-top: 10px; color: #666;">
                    <li>Allez à: <a href="index.php" style="color: #667eea; text-decoration: underline;">Accueil GreenBite</a></li>
                    <li>Cliquez: <strong>"Ajouter au panier"</strong> pour un produit</li>
                    <li>Allez à: <a href="cart.php" style="color: #667eea; text-decoration: underline;">Mon Panier</a></li>
                    <li>Cliquez: <strong>"Passer la commande"</strong></li>
                </ol>
            </div>
        </div>
    </div>

    <div style="background: #f0f4ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="color: #667eea; margin-bottom: 10px;">💡 Astuce</h3>
        <p style="color: #666; line-height: 1.6;">
            Une fois que votre panier contient des produits, le checkout fonctionnera parfaitement.
            Vous pourrez alors entrer votre adresse, choisir le paiement Stripe et confirmer la commande.
        </p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="add-to-cart-test.php" class="button" style="font-size: 1.1em; padding: 15px 40px;">
            ➜ Commencer Maintenant (Panier Automatique)
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 0.9em;">
        <p>Besoin d'aide? Consultez: <a href="../FIXED_ERRORS.md" target="_blank" style="color: #667eea;">FIXED_ERRORS.md</a></p>
    </div>
</div>

</body>
</html>
