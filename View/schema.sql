CREATE DATABASE IF NOT EXISTS greenbite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE greenbite;

CREATE TABLE IF NOT EXISTS produits (
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
);

CREATE TABLE IF NOT EXISTS commandes (
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
);

ALTER TABLE commandes
    ADD COLUMN IF NOT EXISTS mode_livraison ENUM('standard', 'express') NOT NULL DEFAULT 'standard' AFTER statut,
    ADD COLUMN IF NOT EXISTS date_livraison_souhaitee DATE DEFAULT NULL AFTER mode_livraison;

ALTER TABLE produits
    ADD COLUMN IF NOT EXISTS quantite_disponible INT NOT NULL DEFAULT 0 AFTER image;

ALTER TABLE commandes
    ADD COLUMN IF NOT EXISTS methode_paiement ENUM('cash', 'carte') NOT NULL DEFAULT 'cash' AFTER adresse_livraison,
    ADD COLUMN IF NOT EXISTS numero_carte VARCHAR(20) DEFAULT NULL AFTER methode_paiement,

-- Nouvelle table pour gérer les catégories
CREATE TABLE IF NOT EXISTS categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    icone VARCHAR(50) DEFAULT NULL,
    couleur VARCHAR(7) DEFAULT '#16a34a',
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Migration des catégories existantes
INSERT IGNORE INTO categories (nom, icone, couleur) VALUES
('Produits laitiers', '🥛', '#3b82f6'),
('Boissons', '🥤', '#06b6d4'),
('Cereales & Pains', '🍞', '#f59e0b'),
('Epicerie', '🏪', '#8b5cf6'),
('Snacks & Biscuits', '🍪', '#ec4899'),
('Conserves', '🥫', '#6366f1'),
('Fruits & Legumes', '🥦', '#10b981'),
('Viandes & Poissons', '🍗', '#ef4444'),
('Produits surgelés', '🧊', '#0ea5e9'),
('Chocolat & Bonbons', '🍫', '#a16207'),
('Cafe & The', '☕', '#b45309'),
('Miel & Confitures', '🍯', '#dcfce7'),
('Huiles & Condiments', '🧈', '#fef3c7'),
('Produits bio', '🌱', '#dcfce7'),
('Petit déjeuner', '🥐', '#fed7aa');
    ADD COLUMN IF NOT EXISTS nom_titulaire VARCHAR(100) DEFAULT NULL AFTER numero_carte,
    ADD COLUMN IF NOT EXISTS date_expiration VARCHAR(5) DEFAULT NULL AFTER nom_titulaire,
    ADD COLUMN IF NOT EXISTS cvv VARCHAR(3) DEFAULT NULL AFTER date_expiration;
