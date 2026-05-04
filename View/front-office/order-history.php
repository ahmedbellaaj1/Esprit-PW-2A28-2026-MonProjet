<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

$id_utilisateur = trim($_GET['id'] ?? '');
$orders = [];
$error = '';
$currentUser = '';

// Récupérer les commandes si l'ID utilisateur est fourni
if ($id_utilisateur && ctype_digit($id_utilisateur)) {
    $controller = new OrderController();
    $orders = $controller->list(['id_user' => $id_utilisateur]);
    $currentUser = $id_utilisateur;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique d'Achats - GreenBite</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
    <style>
        .order-history-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .order-history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
        }

        .order-history-header h1 {
            font-size: 2rem;
            color: #1f2937;
            margin: 0;
        }

        .user-info-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f3f4f6;
            border-radius: 8px;
            align-items: flex-end;
        }

        .user-info-form .form-group {
            flex: 1;
            min-width: 250px;
        }

        .user-info-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .user-info-form input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .user-info-form button {
            padding: 0.75rem 1.5rem;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .user-info-form button:hover {
            background: #15803d;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            background: #f9fafb;
            border-radius: 8px;
            border: 2px dashed #d1d5db;
        }

        .no-orders p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }

        .orders-list {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: box-shadow 0.3s ease;
            overflow: hidden;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-id {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.1rem;
        }

        .order-date {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .order-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-en-cours {
            background: #fef3c7;
            color: #92400e;
        }

        .status-en-preparation {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-confirmee {
            background: #dcfce7;
            color: #166534;
        }

        .status-livree {
            background: #dcfce7;
            color: #166534;
        }

        .status-annulee {
            background: #fee2e2;
            color: #991b1b;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            color: #1f2937;
            font-size: 1rem;
            font-weight: 500;
        }

        .order-product {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .product-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .product-brand {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .product-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            font-size: 0.9rem;
        }

        .product-detail {
            display: flex;
            justify-content: space-between;
        }

        .product-detail-label {
            color: #6b7280;
        }

        .product-detail-value {
            font-weight: 600;
            color: #1f2937;
        }

        .delivery-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .delivery-info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .delivery-info-value {
            color: #1f2937;
            margin-left: 1rem;
        }

        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .user-info-form {
                flex-direction: column;
                align-items: stretch;
            }

            .user-info-form input,
            .user-info-form button {
                width: 100%;
            }

            .order-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="navbar-logo" href="index.php">
        <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="navbar-logo-img">
        <span class="navbar-logo-text">Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="#">Recettes</a></li>
        <li><a href="index.php">Produits</a></li>
        <li><a href="#">Dons</a></li>
        <li><a href="#">Magasins</a></li>
    </ul>
    <div class="navbar-right">
        <a class="primary-btn nav-quick-btn" href="../back-office/dashboard.php">Dashboard Admin</a>
        <a href="cart.php" class="cart-icon" title="Voir le panier">
            🛒
            <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
        </a>
        <div class="nav-avatar">AB</div>
    </div>
</nav>

<div class="order-history-container">
    <div class="order-history-header">
        <h1>📋 Mon Historique d'Achats</h1>
    </div>

    <!-- Formulaire pour entrer l'ID utilisateur -->
    <form method="GET" class="user-info-form">
        <div class="form-group">
            <label for="user-id">Votre ID Utilisateur</label>
            <input 
                type="text" 
                id="user-id" 
                name="id" 
                placeholder="Entrez votre ID utilisateur"
                value="<?= h($currentUser) ?>"
                required
            >
        </div>
        <button type="submit">Voir mes Commandes</button>
    </form>

    <?php if ($currentUser): ?>
        <!-- Affichage des commandes -->
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>😔 Aucune commande trouvée pour votre compte.</p>
                <p>Commencez vos achats dès maintenant!</p>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Commande #<?= h((string)$order['id_commande']) ?></div>
                                <div class="order-date">
                                    <?php
                                    $date = new DateTime($order['date_commande']);
                                    echo $date->format('d/m/Y à H:i');
                                    ?>
                                </div>
                            </div>
                            <span class="order-status status-<?= h($order['statut']) ?>">
                                <?= h($order['statut']) ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Produit</span>
                                <span class="detail-value"><?= h($order['produit_nom'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Quantité</span>
                                <span class="detail-value"><?= h((string)$order['quantite']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Prix Total</span>
                                <span class="detail-value"><?= number_format((float)$order['prix_total'], 2, ',', ' ') ?> DT</span>
                            </div>
                        </div>

                        <!-- Informations du produit -->
                        <?php if ($order['produit_nom']): ?>
                            <div class="order-product">
                                <div class="product-name">📦 <?= h($order['produit_nom']) ?></div>
                                <?php if ($order['produit_marque']): ?>
                                    <div class="product-brand">Marque: <?= h($order['produit_marque']) ?></div>
                                <?php endif; ?>
                                <div class="product-details">
                                    <div class="product-detail">
                                        <span class="product-detail-label">Quantité:</span>
                                        <span class="product-detail-value"><?= h((string)$order['quantite']) ?></span>
                                    </div>
                                    <div class="product-detail">
                                        <span class="product-detail-label">Prix Unitaire:</span>
                                        <span class="product-detail-value"><?= number_format((float)$order['prix_total'] / (int)$order['quantite'], 2, ',', ' ') ?> DT</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Informations de livraison -->
                        <div class="delivery-info">
                            <div class="delivery-info-label">🚚 Informations de Livraison</div>
                            <div style="margin-top: 0.5rem;">
                                <div><strong>Mode:</strong> <span class="delivery-info-value"><?= ucfirst(h($order['mode_livraison'])) ?></span></div>
                                <?php if ($order['date_livraison_souhaitee']): ?>
                                    <div><strong>Date souhaitée:</strong> <span class="delivery-info-value">
                                        <?php
                                        $delivery_date = new DateTime($order['date_livraison_souhaitee']);
                                        echo $delivery_date->format('d/m/Y');
                                        ?>
                                    </span></div>
                                <?php endif; ?>
                                <div><strong>Adresse:</strong> <span class="delivery-info-value"><?= h(substr($order['adresse_livraison'], 0, 50)) ?>...</span></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Résumé des statistiques -->
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f3f4f6; border-radius: 8px;">
                <h3 style="margin-top: 0;">📊 Vos Statistiques</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Total Commandes</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a;"><?= count($orders) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Montant Total</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a;">
                            <?= number_format(array_sum(array_column($orders, 'prix_total')), 2, ',', ' ') ?> DT
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-message">
            <p>Veuillez entrer votre ID utilisateur pour voir votre historique d'achats.</p>
        </div>
    <?php endif; ?>
</div>

<footer style="text-align: center; padding: 2rem; color: #6b7280; border-top: 1px solid #e5e7eb; margin-top: 3rem;">
    <p>&copy; 2024 GreenBite. Tous droits réservés.</p>
</footer>

<script src="../assets/cart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => updateCartBadge());
</script>
</body>
</html>
