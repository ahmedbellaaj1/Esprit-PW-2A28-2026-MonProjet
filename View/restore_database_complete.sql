-- =============================================================================
-- SCRIPT DE RESTAURATION COMPLÈTE DE LA BASE DE DONNÉES GREENBITE
-- Exécutez ce script dans phpMyAdmin ou en ligne de commande MySQL
-- =============================================================================

-- Supprimer la base de données existante s'il existe
DROP DATABASE IF EXISTS greenbite;

-- Créer la base de données avec le jeu de caractères UTF-8
CREATE DATABASE greenbite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE greenbite;

-- =============================================================================
-- CRÉATION DE LA TABLE PRODUITS
-- =============================================================================
CREATE TABLE produits (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    marque VARCHAR(120) NOT NULL,
    code_barre VARCHAR(80) DEFAULT NULL,
    categorie VARCHAR(120) DEFAULT NULL,
    prix DECIMAL(10,2) NOT NULL DEFAULT 0,
    calories DECIMAL(10,2) DEFAULT 0,
    proteines DECIMAL(10,2) DEFAULT 0,
    glucides DECIMAL(10,2) DEFAULT 0,
    lipides DECIMAL(10,2) DEFAULT 0,
    nutriscore CHAR(1) DEFAULT 'C',
    image TEXT DEFAULT NULL,
    quantite_disponible INT NOT NULL DEFAULT 0,
    statut ENUM('actif', 'inactif', 'attente') DEFAULT 'actif',
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- CRÉATION DE LA TABLE COMMANDES
-- =============================================================================
CREATE TABLE commandes (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    id_produit INT NOT NULL,
    id_utilisateur INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    date_commande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en-cours', 'en-preparation', 'confirmee', 'livree', 'annulee') DEFAULT 'en-cours',
    mode_livraison ENUM('standard', 'express') NOT NULL DEFAULT 'standard',
    date_livraison_souhaitee DATE DEFAULT NULL,
    adresse_livraison TEXT NOT NULL,
    methode_paiement ENUM('cash', 'carte') NOT NULL DEFAULT 'cash',
    numero_carte VARCHAR(20) DEFAULT NULL,
    nom_titulaire VARCHAR(100) DEFAULT NULL,
    date_expiration VARCHAR(5) DEFAULT NULL,
    cvv VARCHAR(3) DEFAULT NULL,
    CONSTRAINT fk_commande_produit FOREIGN KEY (id_produit) REFERENCES produits(id_produit) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- CRÉATION DE LA TABLE CATÉGORIES
-- =============================================================================
CREATE TABLE categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    icone VARCHAR(50) DEFAULT NULL,
    couleur VARCHAR(7) DEFAULT '#16a34a',
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- INSERTION DES CATÉGORIES
-- =============================================================================
INSERT INTO categories (nom, icone, couleur, date_ajout) VALUES
('Produits laitiers', '🥛', '#3b82f6', NOW()),
('Boissons', '🥤', '#06b6d4', NOW()),
('Cereales & Pains', '🍞', '#f59e0b', NOW()),
('Epicerie', '🏪', '#8b5cf6', NOW()),
('Snacks & Biscuits', '🍪', '#ec4899', NOW()),
('Conserves', '🥫', '#6366f1', NOW()),
('Fruits & Legumes', '🥦', '#10b981', NOW()),
('Viandes & Poissons', '🍗', '#ef4444', NOW()),
('Produits surgelés', '🧊', '#0ea5e9', NOW()),
('Chocolat & Bonbons', '🍫', '#a16207', NOW()),
('Cafe & The', '☕', '#b45309', NOW()),
('Miel & Confitures', '🍯', '#dcfce7', NOW()),
('Huiles & Condiments', '🧈', '#fef3c7', NOW()),
('Produits bio', '🌱', '#dcfce7', NOW()),
('Petit déjeuner', '🥐', '#fed7aa', NOW());

-- =============================================================================
-- INSERTION DES PRODUITS (50 produits)
-- =============================================================================
INSERT INTO produits (
    nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, quantite_disponible, statut, date_ajout
) VALUES

-- PRODUITS LAITIERS
('Yaourt Nature Bio 500g', 'Tunisian Dairy', '3017620422003', 'Produits laitiers', 6.50, 95, 4.5, 12.0, 3.2, 'A', 'https://images.unsplash.com/photo-1488477181946-6428a0291840?w=800&q=80', 25, 'actif', NOW()),
('Fromage Blanc Frais 400g', 'Ben Arous', '3017620422004', 'Produits laitiers', 8.50, 110, 14.0, 6.0, 4.5, 'A', 'https://images.unsplash.com/photo-1452894913252-c55d9abf8517?w=800&q=80', 20, 'actif', NOW()),
('Lait Entier Frais 1L', 'Vitalait', '3017620422005', 'Produits laitiers', 4.50, 60, 3.2, 4.8, 3.5, 'A', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=800&q=80', 30, 'actif', NOW()),
('Fromage Emmental Tranches', 'Galbani', '3017620422006', 'Produits laitiers', 15.90, 380, 28.0, 0.7, 30.0, 'C', 'https://images.unsplash.com/photo-1452894913252-c55d9abf8517?w=800&q=80', 15, 'actif', NOW()),
('Yaourt aux Fruits 125g', 'Sidi Saad', '3017620422007', 'Produits laitiers', 1.20, 120, 3.5, 18.0, 2.8, 'B', 'https://images.unsplash.com/photo-1488477181946-6428a0291840?w=800&q=80', 50, 'actif', NOW()),

-- BOISSONS
('Jus Orange Frais 1L', 'Tunisian Citrus', '8437001234567', 'Boissons', 9.50, 44, 0.7, 9.8, 0.2, 'B', 'https://images.unsplash.com/photo-1600271886742-f049cd5bba3f?w=800&q=80', 40, 'actif', NOW()),
('Eau Minérale 1.5L', 'Safia', '8437001234568', 'Boissons', 1.50, 0, 0.0, 0.0, 0.0, 'A', 'https://images.unsplash.com/photo-1548638550-7f8c3c326c7c?w=800&q=80', 100, 'actif', NOW()),
('Jus Fraise Nectar 200ml', 'Kech', '8437001234569', 'Boissons', 2.50, 60, 0.5, 14.0, 0.2, 'C', 'https://images.unsplash.com/photo-1553530666-ba953a5ad00b?w=800&q=80', 35, 'actif', NOW()),
('Thé Chamomille 20 Sachets', 'Aïda', '8437001234570', 'Boissons', 4.50, 2, 0.1, 0.3, 0.0, 'A', 'https://images.unsplash.com/photo-1597318972211-88a1c1d9e3ca?w=800&q=80', 22, 'actif', NOW()),
('Café Moulu Premium 250g', 'Caftan', '8437001234571', 'Cafe & The', 12.00, 2, 0.2, 0.0, 0.1, 'A', 'https://images.unsplash.com/photo-1559056199-641a0ac8b8d5?w=800&q=80', 18, 'actif', NOW()),

-- CEREALES & PAINS
('Pain Complet Frais 600g', 'Boulangerie Municipale', '6111245678901', 'Cereales & Pains', 3.50, 247, 9.0, 41.0, 3.5, 'A', 'https://images.unsplash.com/photo-1608198093002-ad4e005484ec?w=800&q=80', 45, 'actif', NOW()),
('Granola Miel Amandes 500g', 'Natura', '7613035678901', 'Cereales & Pains', 16.00, 410, 8.2, 62.0, 13.0, 'B', 'https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?w=800&q=80', 20, 'actif', NOW()),
('Pâtes Complètes 500g', 'Barilla', '8076809512345', 'Cereales & Pains', 7.50, 350, 12.5, 67.0, 2.5, 'B', 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=800&q=80', 30, 'actif', NOW()),
('Riz Complet Bio 1kg', 'Ali Bey', '6111245678902', 'Cereales & Pains', 8.50, 360, 7.5, 75.0, 3.0, 'A', 'https://images.unsplash.com/photo-1586985289688-cacb595b51ca?w=800&q=80', 25, 'actif', NOW()),
('Couscous Traditionnel 500g', 'Couscous Ben', '6111245678903', 'Cereales & Pains', 5.50, 340, 12.0, 70.0, 1.5, 'B', 'https://images.unsplash.com/photo-1498193692148-552af4e98ca2?w=800&q=80', 35, 'actif', NOW()),

-- SNACKS & BISCUITS
('Biscuits Digestifs 400g', 'McVities', '7622210123456', 'Snacks & Biscuits', 8.90, 480, 6.0, 62.0, 20.0, 'C', 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80', 28, 'actif', NOW()),
('Chips Nature 150g', 'Lay\'s', '5053990123456', 'Snacks & Biscuits', 4.50, 536, 5.5, 49.0, 34.0, 'D', 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=800&q=80', 40, 'actif', NOW()),
('Crackers Complets 200g', 'Tisano', '5053990123457', 'Snacks & Biscuits', 5.50, 420, 8.5, 56.0, 16.0, 'B', 'https://images.unsplash.com/photo-1557512943-a04ceb158055?w=800&q=80', 32, 'actif', NOW()),
('Pop-Corn Non Salé 100g', 'Maïs Tunisien', '5053990123458', 'Snacks & Biscuits', 3.50, 380, 10.0, 76.0, 5.5, 'B', 'https://images.unsplash.com/photo-1599599810694-b5ac4dd3b2d8?w=800&q=80', 50, 'actif', NOW()),

-- CONSERVES
('Thon Naturel 180g', 'Rio Mare', '8004030123456', 'Conserves', 11.50, 115, 25.0, 0.0, 1.2, 'A', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80', 35, 'actif', NOW()),
('Tomates Pelées 400g', 'San Raso', '8004030123457', 'Conserves', 3.50, 18, 1.0, 3.5, 0.2, 'A', 'https://images.unsplash.com/photo-1585518419759-66dbb2ca5d87?w=800&q=80', 45, 'actif', NOW()),
('Pois Chiches 400g', 'Ben Youssef', '8004030123458', 'Conserves', 4.00, 120, 8.5, 18.0, 2.0, 'B', 'https://images.unsplash.com/photo-1606787620802-413f5a98f1ce?w=800&q=80', 40, 'actif', NOW()),
('Olives Noires Dénoyautées 350g', 'Zebar', '8004030123459', 'Conserves', 8.50, 115, 1.4, 0.6, 11.0, 'B', 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=800&q=80', 25, 'actif', NOW()),

-- FRUITS & LEGUMES
('Compote Pomme Bio 400g', 'Fruit de Bonheur', '3608580123456', 'Fruits & Legumes', 6.50, 72, 0.3, 16.2, 0.1, 'A', 'https://images.unsplash.com/photo-1599599810694-b5ac4dd3b2d8?w=800&q=80', 30, 'actif', NOW()),
('Jus Pamplemousse Frais 200ml', 'Citrus Sud', '3608580123457', 'Fruits & Legumes', 3.50, 38, 0.6, 9.0, 0.1, 'B', 'https://images.unsplash.com/photo-1600271886742-f049cd5bba3f?w=800&q=80', 25, 'actif', NOW()),

-- VIANDES & POISSONS
('Filet Poulet Surgélé 500g', 'Agro-Meat', '8004030123460', 'Viandes & Poissons', 18.50, 165, 31.0, 0.0, 3.6, 'A', 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=800&q=80', 20, 'actif', NOW()),
('Poisson Blanc Frais 400g', 'Pêche Côtière', '8004030123461', 'Viandes & Poissons', 22.00, 90, 20.0, 0.0, 1.0, 'A', 'https://images.unsplash.com/photo-1580959375944-abd7e991f971?w=800&q=80', 15, 'actif', NOW()),

-- PRODUITS SURGELÉS
('Légumes Surgelés Mélange 400g', 'Agrifresh', '8004030123462', 'Produits surgelés', 6.50, 35, 2.5, 6.0, 0.3, 'A', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', 28, 'actif', NOW()),
('Pizza Surgelée 400g', 'Mr Frost', '8004030123463', 'Produits surgelés', 10.50, 285, 12.0, 32.0, 12.0, 'C', 'https://images.unsplash.com/photo-1535920527154-d9247a67b6d5?w=800&q=80', 22, 'actif', NOW()),

-- CHOCOLAT & BONBONS
('Chocolat Noir 70% 100g', 'Lindt', '7622210123457', 'Chocolat & Bonbons', 9.50, 530, 7.0, 46.0, 30.0, 'C', 'https://images.unsplash.com/photo-1599599810831-fac2c70f1313?w=800&q=80', 30, 'actif', NOW()),
('Bonbons Fruités 200g', 'Haribo', '7622210123458', 'Chocolat & Bonbons', 7.50, 340, 5.0, 77.0, 0.5, 'D', 'https://images.unsplash.com/photo-1599599810964-f9e41b58d186?w=800&q=80', 35, 'actif', NOW()),

-- MIEL & CONFITURES
('Miel Naturel 500g', 'Apiculture Tunisienne', '3608580123458', 'Miel & Confitures', 14.50, 304, 0.3, 82.0, 0.0, 'B', 'https://images.unsplash.com/photo-1587046139220-f1d2a1e4e5d0?w=800&q=80', 20, 'actif', NOW()),
('Confiture Fraise 400g', 'Délices du Sud', '3608580123459', 'Miel & Confitures', 8.50, 272, 0.5, 66.0, 0.2, 'C', 'https://images.unsplash.com/photo-1595799586985-8cf6c6cb0df9?w=800&q=80', 25, 'actif', NOW()),

-- HUILES & CONDIMENTS
('Huile Olive Extra Vierge 500ml', 'Chemlal', '8004030123464', 'Huiles & Condiments', 22.00, 884, 0.0, 0.0, 100.0, 'A', 'https://images.unsplash.com/photo-1471397681619-2b18b5422a8d?w=800&q=80', 15, 'actif', NOW()),
('Sauce Tomate Classique 200g', 'Deglet Noor', '8004030123465', 'Huiles & Condiments', 4.50, 25, 1.0, 5.0, 0.3, 'B', 'https://images.unsplash.com/photo-1585518419759-66dbb2ca5d87?w=800&q=80', 35, 'actif', NOW()),
('Moutarde Française 200g', 'Pommery', '8004030123466', 'Huiles & Condiments', 5.50, 66, 3.0, 4.0, 4.0, 'B', 'https://images.unsplash.com/photo-1599599810694-b5ac4dd3b2d8?w=800&q=80', 28, 'actif', NOW()),

-- PRODUITS BIO
('Riz Basmati Bio 1kg', 'Nature & Saveur', '6111245678904', 'Produits bio', 12.50, 360, 7.0, 80.0, 0.5, 'A', 'https://images.unsplash.com/photo-1586985289688-cacb595b51ca?w=800&q=80', 18, 'actif', NOW()),
('Poudre Amande Bio 200g', 'Bio Santé', '6111245678905', 'Produits bio', 13.50, 579, 21.0, 21.0, 50.0, 'A', 'https://images.unsplash.com/photo-1585399572627-3e5a87de2c1f?w=800&q=80', 12, 'actif', NOW()),

-- OEUFS & PRODUITS FRAIS
('Oeufs Fermiers 6 Pièces', 'Farm Fresh', '8004030123467', 'Oeufs & Produits frais', 6.00, 155, 13.0, 1.1, 11.0, 'A', 'https://images.unsplash.com/photo-1582722872223-4ac097a0b4b4?w=800&q=80', 40, 'actif', NOW()),
('Muesli Fruits Secs 500g', 'Cereals Plus', '6111245678906', 'Petit déjeuner', 10.50, 390, 9.0, 70.0, 8.0, 'B', 'https://images.unsplash.com/photo-1585345921539-6c0db5fb0b13?w=800&q=80', 22, 'actif', NOW()),
('Miel Eucalyptus 300g', 'Api Pure', '3608580123460', 'Petit déjeuner', 11.00, 304, 0.2, 82.0, 0.0, 'A', 'https://images.unsplash.com/photo-1587046139220-f1d2a1e4e5d0?w=800&q=80', 18, 'actif', NOW()),

-- SAUCES & ASSAISONNEMENTS
('Harissa Traditionnelle 200g', 'Spice Master', '8004030123468', 'Sauces & Assaisonnements', 6.50, 140, 4.0, 12.0, 6.0, 'C', 'https://images.unsplash.com/photo-1596040606159-e1f9e1c82daa?w=800&q=80', 30, 'actif', NOW()),
('Sauce Soja 250ml', 'Tamari', '8004030123469', 'Sauces & Assaisonnements', 7.50, 65, 11.0, 5.0, 0.5, 'B', 'https://images.unsplash.com/photo-1583034938009-ecdcdcb5c89c?w=800&q=80', 25, 'actif', NOW()),

-- NOIX & GRAINES
('Amandes Grillées Non Salées 200g', 'Nuts & Co', '8004030123470', 'Noix & Graines', 14.50, 579, 21.0, 21.0, 50.0, 'A', 'https://images.unsplash.com/photo-1585399572627-3e5a87de2c1f?w=800&q=80', 20, 'actif', NOW()),
('Graines Courge 150g', 'Seed Power', '8004030123471', 'Noix & Graines', 8.50, 541, 29.0, 20.0, 46.0, 'A', 'https://images.unsplash.com/photo-1599599810964-f9e41b58d186?w=800&q=80', 24, 'actif', NOW()),
('Cacahuètes Grillées 250g', 'Peanut Joy', '8004030123472', 'Noix & Graines', 9.50, 567, 26.0, 16.0, 49.0, 'B', 'https://images.unsplash.com/photo-1585399572627-3e5a87de2c1f?w=800&q=80', 28, 'actif', NOW()),

-- SUCRES & MIEL
('Miel Tilleul 400g', 'Golden Hive', '3608580123461', 'Sucres & Miel', 13.00, 304, 0.4, 82.0, 0.0, 'B', 'https://images.unsplash.com/photo-1587046139220-f1d2a1e4e5d0?w=800&q=80', 16, 'actif', NOW()),
('Sucre Complet Bio 500g', 'Bio Sweet', '8004030123473', 'Sucres & Miel', 6.50, 387, 0.0, 97.0, 0.0, 'C', 'https://images.unsplash.com/photo-1599599810694-b5ac4dd3b2d8?w=800&q=80', 35, 'actif', NOW()),

-- PRODUITS FERMENTÉS
('Yaourt Probiotique 500g', 'BioLive', '3017620422008', 'Produits fermentés', 7.50, 85, 4.0, 10.0, 2.5, 'A', 'https://images.unsplash.com/photo-1488477181946-6428a0291840?w=800&q=80', 20, 'actif', NOW()),
('Kimchi Traditionnel 300g', 'Korea Fresh', '8004030123474', 'Produits fermentés', 10.50, 30, 2.0, 4.0, 1.2, 'B', 'https://images.unsplash.com/photo-1585518419759-66dbb2ca5d87?w=800&q=80', 15, 'actif', NOW());

-- =============================================================================
-- INSERTION DES COMMANDES DE DÉMONSTRATION
-- =============================================================================
INSERT INTO commandes (
    id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, mode_livraison, date_livraison_souhaitee, adresse_livraison
) VALUES
((SELECT id_produit FROM produits WHERE nom = 'Yaourt Nature Bio 500g' LIMIT 1), 1, 2, 13.00, NOW(), 'confirmee', 'standard', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12 Rue Bourguiba, Tunis'),
((SELECT id_produit FROM produits WHERE nom = 'Yaourt aux Fruits 125g' LIMIT 1), 2, 3, 3.60, NOW(), 'en-cours', 'express', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '45 Avenue 14 Janvier, Sfax'),
((SELECT id_produit FROM produits WHERE nom = 'Jus Orange Frais 1L' LIMIT 1), 3, 1, 9.50, NOW(), 'livree', 'standard', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '8 Rue Mondher Bey, Sousse'),
((SELECT id_produit FROM produits WHERE nom = 'Miel Naturel 500g' LIMIT 1), 4, 4, 66.00, NOW(), 'en-preparation', 'express', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '22 Boulevard Tahar Maamouri, Monastir'),
((SELECT id_produit FROM produits WHERE nom = 'Huile Olive Extra Vierge 500ml' LIMIT 1), 5, 2, 44.00, NOW(), 'confirmee', 'standard', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '77 Avenue Habib Bourguiba, Bizerte');

-- =============================================================================
-- CRÉATION DES INDEX POUR LES PERFORMANCES
-- =============================================================================
CREATE INDEX idx_produit_categorie ON produits(categorie);
CREATE INDEX idx_produit_statut ON produits(statut);
CREATE INDEX idx_commande_utilisateur ON commandes(id_utilisateur);
CREATE INDEX idx_commande_statut ON commandes(statut);
CREATE INDEX idx_commande_date ON commandes(date_commande);

-- =============================================================================
-- RÉSUMÉ
-- =============================================================================
-- Base de données: greenbite
-- Nombre de tables: 3 (produits, commandes, categories)
-- Nombre de produits: 50
-- Nombre de commandes de démonstration: 5
-- Nombre de catégories: 15
-- Charset: UTF-8 (utf8mb4)
-- =============================================================================
