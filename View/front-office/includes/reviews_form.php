<?php
/**
 * Formulaire pour ajouter un avis sur un produit
 * À inclure dans product.php
 */
?>

<div class="avis-container">
    <h2>Avis des Clients</h2>

    <!-- Formulaire d'ajout d'avis -->
    <div class="add-review-section">
        <h3>Laisser un avis</h3>
        <form id="reviewForm" class="review-form">
            <input type="hidden" id="id_produit" name="id_produit" value="<?php echo isset($id_produit) ? htmlspecialchars($id_produit) : ''; ?>">

            <div class="form-group">
                <label for="rating">Note *</label>
                <div class="star-rating" id="starRating">
                    <input type="radio" id="star5" name="note" value="5">
                    <label for="star5" class="star">⭐</label>
                    <input type="radio" id="star4" name="note" value="4">
                    <label for="star4" class="star">⭐</label>
                    <input type="radio" id="star3" name="note" value="3">
                    <label for="star3" class="star">⭐</label>
                    <input type="radio" id="star2" name="note" value="2">
                    <label for="star2" class="star">⭐</label>
                    <input type="radio" id="star1" name="note" value="1">
                    <label for="star1" class="star">⭐</label>
                </div>
                <span id="ratingValue" class="rating-value">Aucune note sélectionnée</span>
            </div>

            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" placeholder="Résumez votre avis..." required minlength="5" maxlength="150">
                <small>5-150 caractères</small>
            </div>

            <div class="form-group">
                <label for="texte">Votre avis *</label>
                <textarea id="texte" name="texte" placeholder="Décrivez votre expérience avec ce produit..." required minlength="10" maxlength="1000" rows="5"></textarea>
                <small id="charCount">0/1000 caractères</small>
            </div>

            <div class="form-group">
                <input type="hidden" id="id_utilisateur" name="id_utilisateur" value="1">
                <!-- À adapter selon votre système d'authentification -->
            </div>

            <button type="submit" class="btn-submit">Soumettre mon avis</button>
            <div id="reviewMessage" class="message"></div>
        </form>
    </div>

    <!-- Affichage des avis -->
    <div class="reviews-display-section">
        <div id="reviewsList" class="reviews-list">
            <!-- Chargé dynamiquement -->
        </div>
    </div>
</div>

<style>
.avis-container {
    margin: 30px 0;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fafafa;
}

.add-review-section {
    margin-bottom: 40px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.add-review-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.review-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-group input[type="text"],
.form-group textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
}

.form-group small {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

/* Système de notation avec étoiles */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
    width: fit-content;
}

.star-rating input {
    display: none;
}

.star-rating label {
    font-size: 32px;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
    margin: 0;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}

.rating-value {
    font-size: 14px;
    color: #666;
    margin-top: 8px;
}

.btn-submit {
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.message {
    padding: 10px;
    border-radius: 4px;
    text-align: center;
    min-height: 20px;
}

.message.success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #4caf50;
}

.message.error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #f44336;
}

/* Affichage des avis */
.reviews-display-section h3 {
    margin-top: 30px;
    margin-bottom: 20px;
    color: #333;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.review-item {
    padding: 15px;
    background: white;
    border-left: 4px solid #667eea;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.review-rating {
    display: flex;
    gap: 2px;
    font-size: 14px;
}

.review-rating .star {
    color: #ffc107;
}

.review-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.review-meta {
    font-size: 12px;
    color: #999;
}

.review-text {
    color: #555;
    line-height: 1.6;
    margin-bottom: 10px;
}

.review-helpful {
    display: flex;
    gap: 10px;
    font-size: 12px;
}

.review-helpful button {
    background: #f0f0f0;
    border: 1px solid #ddd;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.review-helpful button:hover {
    background: #e0e0e0;
}

.no-reviews {
    padding: 20px;
    text-align: center;
    color: #999;
    background: white;
    border-radius: 4px;
}

@media (max-width: 600px) {
    .avis-container {
        padding: 15px;
    }

    .star-rating label {
        font-size: 24px;
    }

    .review-header {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewForm = document.getElementById('reviewForm');
    const starRating = document.getElementById('starRating');
    const ratingValue = document.getElementById('ratingValue');
    const texteTextarea = document.getElementById('texte');
    const charCount = document.getElementById('charCount');
    const id_produit = document.getElementById('id_produit').value;

    // Mise à jour du texte de la note
    const stars = starRating.querySelectorAll('input[name="note"]');
    stars.forEach(star => {
        star.addEventListener('change', function() {
            const value = this.value;
            const labels = ['Horrible', 'Mauvais', 'Correct', 'Bon', 'Excellent'];
            ratingValue.textContent = value + '/5 - ' + labels[value - 1];
        });
    });

    // Compteur de caractères
    texteTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length + '/1000 caractères';
    });

    // Soumettre le formulaire
    reviewForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {
            id_produit: parseInt(id_produit),
            id_utilisateur: parseInt(document.getElementById('id_utilisateur').value),
            note: parseInt(document.querySelector('input[name="note"]:checked').value),
            titre: document.getElementById('titre').value,
            texte: texteTextarea.value
        };

        try {
            const response = await fetch('api/reviews/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            const messageDiv = document.getElementById('reviewMessage');

            if (result.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = result.message;
                reviewForm.reset();
                ratingValue.textContent = 'Aucune note sélectionnée';
                charCount.textContent = '0/1000 caractères';
                loadReviews();
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = result.message;
            }
        } catch (error) {
            console.error('Erreur:', error);
            document.getElementById('reviewMessage').className = 'message error';
            document.getElementById('reviewMessage').textContent = 'Erreur lors de l\'envoi';
        }
    });

    // Charger les avis
    async function loadReviews() {
        try {
            const response = await fetch('api/reviews/get.php?id_produit=' + id_produit);
            const result = await response.json();

            if (result.success) {
                displayReviews(result.avis, result.stats);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    function displayReviews(reviews, stats) {
        const reviewsList = document.getElementById('reviewsList');

        if (reviews.length === 0) {
            reviewsList.innerHTML = '<div class="no-reviews">Aucun avis pour le moment. Soyez le premier!</div>';
            return;
        }

        reviewsList.innerHTML = '<h3>📝 Avis (' + stats.nombre_avis + ') - Note moyenne: ' + stats.moyenne_note + '/5</h3>' + 
            reviews.map(review => `
                <div class="review-item">
                    <div class="review-header">
                        <div>
                            <div class="review-rating">
                                ${[...Array(5)].map((_, i) => 
                                    '<span class="star">' + (i < review.note ? '⭐' : '☆') + '</span>'
                                ).join('')}
                            </div>
                            <div class="review-title">${escapeHtml(review.titre)}</div>
                            <div class="review-meta">Par utilisateur ${review.id_utilisateur} • ${formatDate(review.date_avis)}</div>
                        </div>
                    </div>
                    <div class="review-text">${escapeHtml(review.texte)}</div>
                    <div class="review-helpful">
                        <button onclick="markHelpful(${review.id_avis})">👍 Utile (${review.nombre_utilites})</button>
                    </div>
                </div>
            `).join('');
    }

    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) return 'Aujourd\'hui';
        if (days === 1) return 'Hier';
        if (days < 7) return days + ' jours ago';
        if (days < 30) return Math.floor(days / 7) + ' semaines ago';
        return date.toLocaleDateString('fr-FR');
    }

    // Charger les avis au chargement
    loadReviews();
});

function markHelpful(id_avis) {
    console.log('Marqué utile:', id_avis);
    // À implémenter avec l'API
}
</script>
