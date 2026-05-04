<?php
/**
 * Interface ChatBot IA - Page complète
 * Accédez: http://localhost/WEB/View/front-office/chatbot.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

// ID utilisateur (à adapter selon votre système d'authentification)
$id_utilisateur = $_GET['user_id'] ?? 1;
$id_conversation = $_GET['conversation_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant IA - GreenBite</title>
    <link rel="stylesheet" href="../assets/chatbot-style.css">
</head>
<body>
    <div class="chatbot-container">
        <!-- Header du chat -->
        <div class="chat-header">
            <div class="header-content">
                <h1>🤖 Assistant IA GreenBite</h1>
                <p>Votre assistant intelligent pour trouver les meilleurs produits</p>
            </div>
            <button class="btn-close" onclick="closeChat()">✕</button>
        </div>

        <!-- Zone des messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                <div class="message-avatar">🤖</div>
                <div class="message-content">
                    <div class="message-text">
                        Bonjour! 👋 Je suis votre assistant IA GreenBite. Je suis ici pour vous aider à trouver les produits parfaits en fonction de vos besoins, préférences alimentaires et budget. 
                    </div>
                    <div class="message-time">À l'instant</div>
                </div>
            </div>

            <div class="message bot-message">
                <div class="message-avatar">🤖</div>
                <div class="message-content">
                    <div class="message-text">Comment puis-je vous aider aujourd'hui?</div>
                    <div class="suggestions-container" id="suggestionsContainer">
                        <!-- Suggestions rapides chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de saisie -->
        <div class="chat-input-area">
            <form id="messageForm" onsubmit="sendMessage(event)">
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="messageInput" 
                        placeholder="Écrivez votre message..." 
                        autocomplete="off"
                        maxlength="2000"
                    >
                    <button type="submit" class="btn-send" id="btnSend">
                        <span>Envoyer</span>
                        <span class="loader" id="loader" style="display: none;">⏳</span>
                    </button>
                </div>
                <input type="hidden" id="id_conversation" value="<?php echo htmlspecialchars($id_conversation ?? ''); ?>">
                <input type="hidden" id="id_utilisateur" value="<?php echo htmlspecialchars($id_utilisateur); ?>">
            </form>
        </div>
    </div>

    <script src="../assets/cart.js"></script>
    <script>
        // État global
        let currentConversation = <?php echo $id_conversation ? $id_conversation : 'null'; ?>;
        const userId = <?php echo (int)$id_utilisateur; ?>;
        const apiBase = '/WEB/api/chatbot';

        // Initialiser le chat
        async function initChat() {
            // Charger les suggestions rapides
            try {
                const response = await fetch(`${apiBase}/suggestions.php`);
                const data = await response.json();
                if (data.success) {
                    displaySuggestions(data.suggestions);
                }
            } catch (error) {
                console.error('Erreur suggestions:', error);
            }

            // Charger la conversation existante si elle existe
            if (currentConversation) {
                await loadConversation(currentConversation);
            }
        }

        /**
         * Afficher les suggestions rapides
         */
        function displaySuggestions(suggestions) {
            const container = document.getElementById('suggestionsContainer');
            container.innerHTML = '';

            suggestions.forEach(suggestion => {
                const btn = document.createElement('button');
                btn.className = 'suggestion-btn';
                btn.textContent = suggestion.text;
                btn.onclick = (e) => {
                    e.preventDefault();
                    document.getElementById('messageInput').value = suggestion.query;
                    sendMessage({preventDefault: () => {}});
                };
                container.appendChild(btn);
            });
        }

        /**
         * Envoyer un message
         */
        async function sendMessage(event) {
            event.preventDefault();

            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();

            if (!message) return;

            // Afficher le message utilisateur
            addMessageToChat('utilisateur', message);
            messageInput.value = '';

            // Activer le loader
            document.getElementById('loader').style.display = 'inline';
            document.getElementById('btnSend').disabled = true;

            try {
                const response = await fetch(`${apiBase}/message.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_conversation: currentConversation,
                        id_utilisateur: userId,
                        message: message
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mettre à jour l'ID de conversation
                    if (data.id_conversation) {
                        currentConversation = data.id_conversation;
                        document.getElementById('id_conversation').value = currentConversation;
                    }

                    // Afficher la réponse du bot
                    addMessageToChat('bot', data.bot_response);

                    // Afficher les recommandations
                    if (data.recommendations && data.recommendations.length > 0) {
                        displayRecommendations(data.recommendations);
                    }
                } else {
                    addMessageToChat('bot', '❌ Erreur: ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                addMessageToChat('bot', '❌ Une erreur s\'est produite. Veuillez réessayer.');
            } finally {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('btnSend').disabled = false;
                messageInput.focus();
            }
        }

        /**
         * Ajouter un message au chat
         */
        function addMessageToChat(type, content) {
            const chatMessages = document.getElementById('chatMessages');

            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}-message`;

            const avatar = type === 'bot' ? '🤖' : '👤';
            const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

            messageDiv.innerHTML = `
                <div class="message-avatar">${avatar}</div>
                <div class="message-content">
                    <div class="message-text">${escapeHtml(content)}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Afficher les recommandations de produits
         */
        function displayRecommendations(recommendations) {
            const chatMessages = document.getElementById('chatMessages');

            const recommendDiv = document.createElement('div');
            recommendDiv.className = 'recommendations-container';

            let html = '<div class="recommendations-title">📦 Recommandations pour vous:</div>';
            html += '<div class="products-grid">';

            recommendations.forEach(product => {
                const stars = '⭐'.repeat(Math.round(product.note_moyenne)) + 
                             '☆'.repeat(5 - Math.round(product.note_moyenne));

                html += `
                    <div class="product-card" data-product-id="${product.id_produit}">
                        <div class="product-image">
                            <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.nom)}" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                            <div class="confidence-badge">${Math.round(product.confiance * 100)}% Match</div>
                        </div>
                        <div class="product-info">
                            <div class="product-name" title="${escapeHtml(product.nom)}">${escapeHtml(product.nom)}</div>
                            <div class="product-brand">${escapeHtml(product.marque)}</div>
                            <div class="product-category">${escapeHtml(product.categorie)}</div>
                            <div class="product-rating">${stars} (${product.nombre_avis})</div>
                            <div class="product-reason" title="${escapeHtml(product.raison)}">${escapeHtml(product.raison)}</div>
                            <div class="product-price">${product.prix} TND</div>
                            <div class="product-footer">
                                <button class="btn-view" onclick="viewProductDetails(${product.id_produit}, '${escapeHtml(product.nom).replace(/'/g, "\\'")}')">
                                    👁️ Voir
                                </button>
                                <button class="btn-add-cart" onclick="showAddToCartForm(${product.id_produit}, '${escapeHtml(product.nom).replace(/'/g, "\\'")}', ${product.prix})">
                                    🛒 Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            recommendDiv.innerHTML = html;

            chatMessages.appendChild(recommendDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Voir les détails du produit
         */
        function viewProductDetails(productId, productName) {
            // Ouvrir la page du produit dans un nouvel onglet
            window.open(`/WEB/View/front-office/product.php?id=${productId}`, '_blank');
        }

        /**
         * Afficher le formulaire pour ajouter au panier
         */
        function showAddToCartForm(productId, productName, productPrice) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Créer le modal
            const modalDiv = document.createElement('div');
            modalDiv.className = 'add-to-cart-modal';
            modalDiv.innerHTML = `
                <div class="modal-overlay" onclick="this.closest('.add-to-cart-modal').remove()"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>🛒 Ajouter au panier</h3>
                        <button class="modal-close" onclick="this.closest('.add-to-cart-modal').remove()">✕</button>
                    </div>
                    <div class="modal-body">
                        <div class="product-preview">
                            <strong>${escapeHtml(productName)}</strong>
                            <div class="price-tag">${productPrice} TND</div>
                        </div>
                        <div class="quantity-selector">
                            <label for="quantity-${productId}">Quantité:</label>
                            <div class="quantity-input-group">
                                <button onclick="decreaseQuantity(this)">−</button>
                                <input type="number" id="quantity-${productId}" value="1" min="1" max="999" class="quantity-input">
                                <button onclick="increaseQuantity(this)">+</button>
                            </div>
                        </div>
                        <div class="total-price">
                            <span>Total: </span>
                            <strong id="total-price-${productId}">${productPrice} TND</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-cancel" onclick="this.closest('.add-to-cart-modal').remove()">Annuler</button>
                        <button class="btn-confirm" onclick="confirmAddToCart(${productId}, '${escapeHtml(productName).replace(/'/g, "\\'")}', ${productPrice})">
                            Ajouter au panier ✓
                        </button>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(modalDiv);
            
            // Ajouter l'event listener pour la mise à jour du prix total
            const quantityInput = document.getElementById(`quantity-${productId}`);
            quantityInput.addEventListener('change', () => updateTotalPrice(productId, productPrice));
            quantityInput.focus();
        }

        /**
         * Augmenter la quantité
         */
        function increaseQuantity(button) {
            const input = button.nextElementSibling;
            input.value = Math.min(parseInt(input.value) + 1, 999);
            const productId = input.id.split('-')[1];
            const price = parseFloat(button.closest('.quantity-input-group').parentElement.nextElementSibling.textContent);
            updateTotalPrice(productId, price);
        }

        /**
         * Diminuer la quantité
         */
        function decreaseQuantity(button) {
            const input = button.nextElementSibling;
            input.value = Math.max(parseInt(input.value) - 1, 1);
            const productId = input.id.split('-')[1];
            const price = parseFloat(button.closest('.quantity-input-group').parentElement.nextElementSibling.textContent);
            updateTotalPrice(productId, price);
        }

        /**
         * Mettre à jour le prix total
         */
        function updateTotalPrice(productId, unitPrice) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
            const quantity = parseInt(quantityInput.value) || 1;
            const total = (quantity * unitPrice).toFixed(2);
            document.getElementById(`total-price-${productId}`).textContent = `${total} TND`;
        }

        /**
         * Confirmer l'ajout au panier
         */
        async function confirmAddToCart(productId, productName, productPrice) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
            const quantity = parseInt(quantityInput.value) || 1;

            try {
                // Récupérer les informations du produit via l'API
                const response = await fetch(`/WEB/api/product.php?id=${productId}`);
                const data = await response.json();

                if (data.success && data.product) {
                    const product = data.product;
                    
                    // Ajouter au panier global
                    if (typeof cart !== 'undefined') {
                        cart.addItem({
                            id_produit: productId,
                            nom: product.nom || productName,
                            marque: product.marque || '',
                            prix: productPrice,
                            image: product.image || 'https://via.placeholder.com/150',
                            quantite: quantity,
                            quantite_disponible: product.quantite_disponible || 999
                        });

                        // Message de succès
                        addMessageToChat('bot', `✅ ${quantity}x ${escapeHtml(productName)} ajouté(s) au panier! 🛒`);
                        
                        // Fermer le modal
                        document.querySelector('.add-to-cart-modal').remove();
                    } else {
                        console.error('Le panier global n\'est pas disponible');
                        addMessageToChat('bot', '❌ Erreur: impossible d\'ajouter au panier');
                    }
                } else {
                    throw new Error('Produit non trouvé');
                }
            } catch (error) {
                console.error('Erreur:', error);
                addMessageToChat('bot', '❌ Erreur lors de l\'ajout au panier. Veuillez réessayer.');
            }
        }

        /**
         * Ajouter au panier (ancienne version - conservée pour compatibilité)
         */
        async function addToCart(button, productId) {
            button.disabled = true;
            button.textContent = '✓ Ajouté!';

            try {
                console.log('Produit ajouté au panier:', productId);
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = '🛒 Ajouter';
                }, 2000);
            } catch (error) {
                console.error('Erreur:', error);
                button.disabled = false;
                button.textContent = '🛒 Ajouter';
            }
        }

        /**
         * Charger une conversation existante
         */
        async function loadConversation(id_conv) {
            try {
                const response = await fetch(`${apiBase}/conversation.php?id_conversation=${id_conv}`);
                const data = await response.json();

                if (data.success && data.messages) {
                    // Effacer les messages de bienvenue
                    document.getElementById('chatMessages').innerHTML = '';

                    // Afficher tous les messages
                    data.messages.forEach(msg => {
                        const type = msg.type === 'bot' ? 'bot' : 'utilisateur';
                        addMessageToChat(type, msg.contenu);
                    });
                }
            } catch (error) {
                console.error('Erreur chargement:', error);
            }
        }

        /**
         * Fermer le chat
         */
        function closeChat() {
            if (confirm('Fermer le chat?')) {
                window.history.back();
            }
        }

        /**
         * Échapper le HTML
         */
        function escapeHtml(text) {
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Initialiser au chargement
        document.addEventListener('DOMContentLoaded', initChat);

        // Focus sur l'input au chargement
        window.addEventListener('load', () => {
            document.getElementById('messageInput').focus();
        });

        // Raccourci clavier: Entrée pour envoyer
        document.getElementById('messageForm').addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage({preventDefault: () => {}});
            }
        });
    </script>
</body>
</html>
