# Documentation du Système de Panier GreenBite

## 📋 Vue d'ensemble

J'ai implémenté un système de panier complet pour le front-office GreenBite permettant aux clients d'ajouter plusieurs produits au panier avant de passer commande.

## ✨ Fonctionnalités implémentées

### 1. **Icône de Panier dans la Navbar**
- Ajoutée dans tous les fichiers front-office (index.php, product.php, cart.php, checkout.php)
- Badge numérique affichant le nombre d'articles dans le panier
- Lien vers la page du panier

### 2. **Gestion du Panier (localStorage)**
- Fichier `View/assets/cart.js` contenant la classe `Cart`
- Stockage des produits dans le localStorage du navigateur
- Persistence du panier entre les sessions de navigation
- Méthodes:
  - `addItem()`: Ajouter un produit ou augmenter la quantité
  - `removeItem()`: Retirer un produit
  - `updateQuantity()`: Modifier la quantité
  - `clear()`: Vider le panier
  - `getCount()`: Obtenir le nombre total d'articles
  - `getTotal()`: Calculer le prix total

### 3. **Page Produit Améliorée (product.php)**
- Remplacé le formulaire de commande direct par:
  - Input pour la quantité
  - Bouton "🛒 Ajouter au panier"
  - Message de confirmation
- Suppression des formulaires de paiement de cette page

### 4. **Page Panier (cart.php)** - NOUVELLE
- Affichage de tous les produits du panier
- Possibilité de modifier les quantités (+/-)
- Possibilité de retirer des produits individuels
- Affichage du résumé:
  - Liste des produits avec prix
  - Calcul du total
  - Bouton "Passer la commande"
  - Lien "Continuer les achats"
- Affichage d'un message vide si le panier est vide

### 5. **Page Checkout (checkout.php)** - NOUVELLE
- Affichage de tous les produits du panier à commander
- Résumé du panier avec total
- Formulaire de livraison unifié:
  - ID utilisateur
  - Adresse de livraison
  - Mode de livraison (Standard/Express)
  - Date de livraison souhaitée
- Formulaire de paiement:
  - Choix entre paiement à la livraison (Cash) ou par carte
  - Champs de carte bancaire (conditionnels)
- Soumission crée une commande par produit (une seule fois par produit)

### 6. **Amélioration du OrderController**
- Méthode `save()` améliorée pour supporter les données de paiement
- Nouvelles propriétés:
  - `methodePaiement`: Type de paiement
  - `numeroCarte`: Numéro de carte (optionnel)
  - `nomTitulaire`: Nom du titulaire
  - `dateExpiration`: Date d'expiration
  - `cvv`: CVV

### 7. **Styles CSS Ajoutés (style.css)**
- `.cart-icon`: Style de l'icône du panier
- `.cart-badge`: Badge du compteur d'articles
- `.cart-container`: Layout du panier
- `.cart-item`: Style d'un article du panier
- `.cart-summary`: Résumé du panier

## 📁 Fichiers Modifiés/Créés

### Créés:
- `View/assets/cart.js` - Gestion du localStorage du panier
- `View/front-office/cart.php` - Page affichage du panier
- `View/front-office/checkout.php` - Page de commande

### Modifiés:
- `View/front-office/index.php` - Ajout icône panier + script cart.js
- `View/front-office/product.php` - Remplacé formulaire commande par ajout au panier
- `View/style.css` - Ajout styles du panier
- `Controller/OrderController.php` - Amélioration méthode save()

## 🔄 Flux d'utilisation

```
1. Client navigue sur page produits (index.php)
2. Client clique sur un produit
3. Sur la page produit (product.php):
   - Choisit la quantité
   - Clique "Ajouter au panier"
   - Le produit est ajouté au localStorage
   - Le badge du panier se met à jour
4. Client peut continuer à ajouter d'autres produits
5. Client clique sur l'icône panier 🛒
6. Sur la page panier (cart.php):
   - Voit tous les produits ajoutés
   - Peut modifier les quantités
   - Peut retirer des produits
   - Clique "Passer la commande"
7. Sur la page checkout (checkout.php):
   - Voit le résumé des produits
   - Remplit les infos de livraison
   - Choisit le mode de paiement
   - Remplit les infos de paiement (si carte)
   - Soumet le formulaire
8. Une commande est créée pour chaque produit
```

## ⚙️ Détails Techniques

### localStorage Structure
```json
{
  "greenbite_cart": [
    {
      "id_produit": 20,
      "nom": "Yaourt Nature Bio",
      "marque": "Abeille d'Or",
      "prix": 1.00,
      "image": "url...",
      "quantite": 3
    },
    ...
  ]
}
```

### Créations de Commandes
- Une commande est créée par produit dans le panier
- Chaque commande reçoit les mêmes infos de livraison
- Le prix_total = prix_unitaire × quantité
- Le statut initial est "en-cours"

## 🐛 Problèmes Connus/À Vérifier

1. Le script `cart.js` se charge à la fin du body pour assurer que le DOM est chargé
2. Les fonctions JavaScript sont globales et disponibles dans tous les fichiers
3. Nécessite que le localStorage soit activé dans le navigateur
4. Le panier persiste entre les sessions (même après fermeture du navigateur)

## 🚀 Utilisation

### Pour les clients:
1. Naviguer sur les produits
2. Ajouter des produits au panier
3. Consulter le panier
4. Passer une commande complète

### Pour les administrateurs:
- Les commandes sont enregistrées dans la base de données comme avant
- Une commande par produit du panier
- Toutes les commandes ont les mêmes infos de livraison et paiement

## 📝 Notes Importantes

- Le localStorage est limité par navigateur/domaine
- Les données du panier sont effacées si le cache du navigateur est vidé
- Un seul panier par utilisateur (pas de session utilisateur)
- Pas de limite de stock (à implémenter si nécessaire)
- Les prix sont en Dinars Tunisiens (DT)

---

**Date de création**: Avril 2026
**Statut**: Implémenté et testé
