// Gestion du panier avec localStorage
class Cart {
    constructor() {
        this.storageKey = 'greenbite_cart';
        this.items = this.load();
        this.observers = [];
    }

    // Charger le panier depuis localStorage
    load() {
        const data = localStorage.getItem(this.storageKey);
        return data ? JSON.parse(data) : [];
    }

    // Sauvegarder le panier
    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
        this.notifyObservers();
    }

    // Ajouter un produit au panier
    addItem(product) {
        const existingItem = this.items.find(item => item.id_produit === product.id_produit);
        
        if (existingItem) {
            existingItem.quantite += product.quantite;
        } else {
            this.items.push({
                id_produit: product.id_produit,
                nom: product.nom,
                marque: product.marque,
                prix: product.prix,
                image: product.image,
                quantite: product.quantite
            });
        }
        
        this.save();
        return true;
    }

    // Supprimer un produit du panier
    removeItem(productId) {
        this.items = this.items.filter(item => item.id_produit !== productId);
        this.save();
    }

    // Mettre à jour la quantité
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id_produit === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                item.quantite = quantity;
                this.save();
            }
        }
    }

    // Vider le panier
    clear() {
        this.items = [];
        this.save();
    }

    // Obtenir le nombre total d'articles
    getCount() {
        return this.items.reduce((sum, item) => sum + item.quantite, 0);
    }

    // Obtenir le prix total
    getTotal() {
        return this.items.reduce((sum, item) => sum + (item.prix * item.quantite), 0);
    }

    // Obtenir les items
    getItems() {
        return this.items;
    }

    // Observateurs
    subscribe(callback) {
        this.observers.push(callback);
    }

    notifyObservers() {
        this.observers.forEach(callback => callback(this));
    }
}

// Instance globale du panier
const cart = new Cart();

// Mettre à jour le badge du panier dans la navbar
function updateCartBadge() {
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        const count = cart.getCount();
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

// Ajouter un produit au panier
function addProductToCart(productId, nom, marque, prix, image, quantiteDisponible) {
    const quantiteInput = document.getElementById('cart_quantite');
    const quantite = quantiteInput ? parseInt(quantiteInput.value) || 1 : 1;
    
    if (quantite <= 0) {
        showCartMessage('Veuillez entrer une quantité valide', 'error');
        return;
    }
    
    // Validation du stock disponible
    if (quantiteDisponible && quantite > quantiteDisponible) {
        showCartMessage(`❌ Stock insuffisant! Seulement ${quantiteDisponible} disponible(s)`, 'error');
        quantiteInput.value = quantiteDisponible;
        return;
    }
    
    cart.addItem({
        id_produit: productId,
        nom: nom,
        marque: marque,
        prix: prix,
        image: image,
        quantite: quantite,
        quantite_disponible: quantiteDisponible
    });
    
    showCartMessage('✅ Produit ajouté au panier!', 'success');
    if (quantiteInput) {
        quantiteInput.value = '1';
    }
}

// Valider la quantité saisie (empêcher de dépasser le max)
function validateQuantityInput(inputElement, maxQuantite) {
    const value = parseInt(inputElement.value) || 0;
    if (value > maxQuantite) {
        inputElement.value = maxQuantite;
    }
    if (value < 1) {
        inputElement.value = 1;
    }
}

// Afficher un message
function showCartMessage(message, type) {
    const messageDiv = document.getElementById('cartMessage');
    if (!messageDiv) return;
    
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
    messageDiv.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    messageDiv.style.color = type === 'success' ? '#166534' : '#991b1b';
    messageDiv.style.border = type === 'success' ? '1px solid #86efac' : '1px solid #fca5a5';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

// Aller au checkout
function goToCheckout() {
    window.location.href = 'checkout.php';
}

// Écouter les changements du panier
cart.subscribe(() => {
    updateCartBadge();
});

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
});
