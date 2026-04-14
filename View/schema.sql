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
    adresse_livraison TEXT NOT NULL,
    CONSTRAINT fk_commande_produit FOREIGN KEY (id_produit) REFERENCES produits(id_produit) ON DELETE CASCADE
);
