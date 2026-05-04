<?php
/**
 * Widget ChatBot Flottant
 * À inclure dans le footer/bootstrap du front office
 * Place le bouton chatbot en bas à droite
 */
?>

<!-- ChatBot Widget -->
<div id="chatbot-widget">
    <!-- Bouton flottant -->
    <button id="chatbot-toggle" class="chatbot-btn" title="Ouvrir ChatBot">
        <span class="chatbot-icon">🤖</span>
        <span class="chatbot-badge">1</span>
    </button>

    <!-- Modal ChatBot -->
    <div id="chatbot-modal" class="chatbot-modal">
        <div class="chatbot-modal-content">
            <!-- Header -->
            <div class="chatbot-header">
                <div class="chatbot-header-info">
                    <h2>🤖 Assistant IA</h2>
                    <p>Bonjour! Comment puis-je vous aider?</p>
                </div>
                <button id="chatbot-close" class="chatbot-close-btn" title="Fermer">✕</button>
            </div>

            <!-- Messages Container -->
            <div id="chatbot-messages" class="chatbot-messages">
                <div class="message-loading">
                    <span>Chargement du ChatBot...</span>
                </div>
            </div>

            <!-- Input Area -->
            <div class="chatbot-input-area">
                <input 
                    type="text" 
                    id="chatbot-input" 
                    placeholder="Dites-moi ce que vous cherchez..." 
                    maxlength="2000"
                >
                <button id="chatbot-send" class="chatbot-send-btn" title="Envoyer">➤</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Widget ChatBot Flottant */
#chatbot-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    z-index: 9999;
}

/* Bouton Flottant */
.chatbot-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.chatbot-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.chatbot-btn:active {
    transform: scale(0.95);
}

/* Badge pour notifications */
.chatbot-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff6b6b;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

/* Modal */
.chatbot-modal {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 420px;
    max-width: 90vw;
    height: auto;
    max-height: calc(100vh - 150px);
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
    display: none;
    flex-direction: column;
    animation: slideUp 0.3s ease;
    z-index: 10000;
    min-height: 400px;
}

.chatbot-modal.active {
    display: flex;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Header */
.chatbot-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-header h2 {
    margin: 0;
    font-size: 20px;
}

.chatbot-header p {
    margin: 4px 0 0 0;
    font-size: 13px;
    opacity: 0.9;
}

.chatbot-header-info {
    flex: 1;
}

.chatbot-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Messages Container */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 15px;
    background: #f9f9f9;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
    max-height: calc(100vh - 300px);
    min-height: 200px;
}

.message-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
    font-size: 14px;
}

/* Message Styles */
.message {
    display: flex;
    margin-bottom: 10px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.message.user {
    justify-content: flex-end;
}

.message.bot {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 80%;
    padding: 12px 15px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.4;
    word-wrap: break-word;
}

.message.user .message-bubble {
    background: #667eea;
    color: white;
    border-radius: 12px 0 12px 12px;
}

.message.bot .message-bubble {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    border-radius: 0 12px 12px 12px;
}

.message-time {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
}

/* Recommandations Grid - DESIGN PRO */
.recommendations-container {
    background: linear-gradient(135deg, #f8f9ff 0%, #fff8f9 100%);
    border: 2px solid #667eea;
    padding: 15px;
    margin: 12px 0;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
}

.recommendations-title {
    font-size: 13px;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.recommendations-title:before {
    content: "🎯";
    font-size: 16px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.product-card {
    border: 1px solid #e0d7ff;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    position: relative;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
    border-color: #667eea;
}

.product-image {
    width: 100%;
    height: 80px;
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
    transition: transform 0.3s;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-image:after {
    content: "📦";
    position: absolute;
    font-size: 40px;
    opacity: 0.1;
}

.product-info {
    padding: 10px;
    font-size: 11px;
}

.product-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 24px;
}

.product-rating-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.product-rating {
    color: #ffc107;
    font-size: 11px;
    font-weight: bold;
}

.product-price {
    color: #667eea;
    font-weight: bold;
    font-size: 12px;
}

.btn-add-cart {
    width: 100%;
    padding: 8px 6px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.btn-add-cart:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-add-cart:active {
    transform: scale(0.98);
}

/* Input Area */
.chatbot-input-area {
    display: flex;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #e0e0e0;
    background: white;
    border-radius: 0 0 12px 12px;
}

#chatbot-input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    padding: 8px 15px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

#chatbot-input:focus {
    border-color: #667eea;
}

.chatbot-send-btn {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 16px;
}

.chatbot-send-btn:hover {
    background: #5568d3;
}

.chatbot-send-btn.loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Mobile Responsive */
@media (max-width: 600px) {
    .chatbot-modal {
        width: calc(100vw - 40px);
        max-height: calc(100vh - 120px);
        bottom: 80px;
        right: 20px;
        left: 20px;
        min-height: 300px;
    }

    .chatbot-messages {
        max-height: calc(100vh - 280px);
    }

    .products-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 400px) {
    .chatbot-btn {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }

    .chatbot-modal {
        width: calc(100vw - 30px);
        max-height: calc(100vh - 100px);
        bottom: 70px;
        right: 15px;
        left: 15px;
        min-height: 280px;
    }

    .chatbot-messages {
        max-height: calc(100vh - 260px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const modal = document.getElementById('chatbot-modal');
    const closeBtn = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');

    let currentConversation = null;
    let userId = 1; // À remplacer par l'ID utilisateur réel si disponible

    // Toggle modal
    toggleBtn.addEventListener('click', function() {
        modal.classList.toggle('active');
        if (modal.classList.contains('active')) {
            initChatBot();
            input.focus();
        }
    });

    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });

    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });

    // Envoyer message
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function initChatBot() {
        if (messagesContainer.querySelector('.message-loading')) {
            loadSuggestions();
        }
    }

    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        // Ajouter message utilisateur
        addMessage(message, 'user');
        input.value = '';
        sendBtn.classList.add('loading');

        // Envoyer au serveur
        fetch('/WEB/api/chatbot/message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_conversation: currentConversation,
                id_utilisateur: userId,
                message: message
            })
        })
        .then(r => r.json())
        .then(data => {
            sendBtn.classList.remove('loading');
            if (data.success) {
                currentConversation = data.id_conversation;
                addMessage(data.bot_response, 'bot');
                
                if (data.recommendations && data.recommendations.length) {
                    displayRecommendations(data.recommendations);
                }
            } else {
                addMessage('Oups! Une erreur est survenue.', 'bot');
            }
        })
        .catch(err => {
            sendBtn.classList.remove('loading');
            addMessage('Erreur de connexion.', 'bot');
        });
    }

    function loadSuggestions() {
        fetch('/WEB/api/chatbot/suggestions.php')
            .then(r => r.json())
            .then(data => {
                messagesContainer.innerHTML = '';
                const welcome = document.createElement('div');
                welcome.className = 'message bot';
                welcome.innerHTML = '<div class="message-bubble">Bienvenue! 👋 Que puis-je faire pour vous aujourd\'hui?</div>';
                messagesContainer.appendChild(welcome);

                if (data.suggestions) {
                    const buttonsDiv = document.createElement('div');
                    buttonsDiv.style.marginTop = '10px';
                    data.suggestions.forEach(sug => {
                        const btn = document.createElement('button');
                        btn.textContent = sug.text;
                        btn.style.display = 'block';
                        btn.style.width = '100%';
                        btn.style.padding = '8px';
                        btn.style.margin = '4px 0';
                        btn.style.background = '#667eea';
                        btn.style.color = 'white';
                        btn.style.border = 'none';
                        btn.style.borderRadius = '6px';
                        btn.style.cursor = 'pointer';
                        btn.onclick = () => {
                            input.value = sug.query;
                            sendMessage();
                        };
                        buttonsDiv.appendChild(btn);
                    });
                    messagesContainer.appendChild(buttonsDiv);
                }
            })
            .catch(err => console.error('Erreur suggestions:', err));
    }

    function addMessage(text, type) {
        const msg = document.createElement('div');
        msg.className = `message ${type}`;
        const time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        msg.innerHTML = `<div class="message-bubble">${text}<div class="message-time">${time}</div></div>`;
        messagesContainer.appendChild(msg);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function displayRecommendations(products) {
        const recDiv = document.createElement('div');
        recDiv.className = 'recommendations-container';
        recDiv.innerHTML = '<div class="recommendations-title">📦 Recommandations pour vous</div>';
        
        const grid = document.createElement('div');
        grid.className = 'products-grid';
        
        products.slice(0, 4).forEach(product => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <div class="product-image">
                    <img src="${product.image}" alt="${product.nom}">
                </div>
                <div class="product-info">
                    <div class="product-name">${product.nom}</div>
                    <div class="product-price">${product.prix.toFixed(2)} TND</div>
                    <div class="product-rating">⭐ ${product.note_moyenne || 'N/A'}</div>
                    <button class="btn-add-cart" onclick="addToCart(${product.id_produit})">🛒 Ajouter</button>
                </div>
            `;
            grid.appendChild(card);
        });
        
        recDiv.appendChild(grid);
        messagesContainer.appendChild(recDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});

// Fonction globale pour ajouter au panier
function addToCart(productId) {
    // À adapter selon votre système de panier
    console.log('Ajouter produit:', productId);
    alert('Produit ajouté au panier! ✓');
}
</script>
