-- ============================================================
-- GreenBite — Schéma MySQL (module Dons)
-- ============================================================

CREATE DATABASE IF NOT EXISTS greenbite
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE greenbite;

-- Table Partenaire
CREATE TABLE IF NOT EXISTS Partenaire (
    id_partenaire INT          PRIMARY KEY AUTO_INCREMENT,
    nom           VARCHAR(150) NOT NULL,
    type          ENUM('association','restaurant','épicerie') NOT NULL,
    adresse       VARCHAR(255),
    telephone     VARCHAR(20),
    email         VARCHAR(100)
);

-- Table Don  (id_user = FK vers table Utilisateur d'un autre module)
CREATE TABLE IF NOT EXISTS Don (
    id_don           INT      PRIMARY KEY AUTO_INCREMENT,
    statut           ENUM('disponible','réservé','récupéré') DEFAULT 'disponible',
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user          INT      NOT NULL,
    id_partenaire    INT,
    FOREIGN KEY (id_partenaire)
        REFERENCES Partenaire(id_partenaire) ON DELETE SET NULL
);

-- Table Don_Produit  (plusieurs produits par don)
CREATE TABLE IF NOT EXISTS Don_Produit (
    id_ligne        INT          PRIMARY KEY AUTO_INCREMENT,
    id_don          INT          NOT NULL,
    nom_produit     VARCHAR(100) NOT NULL,
    quantite        INT          NOT NULL CHECK (quantite > 0),
    date_peremption DATE         NOT NULL,
    FOREIGN KEY (id_don)
        REFERENCES Don(id_don) ON DELETE CASCADE
);

-- ── Données de test ──────────────────────────────────────────
INSERT INTO Partenaire (nom, type, adresse, telephone, email) VALUES
('Association Espoir Tunis',  'association', '12 rue de la Paix, Tunis',      '+216 71 234 567', 'contact@espoir.tn'),
('Restaurant Le Partage',    'restaurant',  '5 Av. Habib Bourguiba, Sfax',   '+216 74 111 222', 'info@lepartage.tn'),
('Épicerie Sociale Carthage','épicerie',    '8 Rue Ibn Khaldoun, La Marsa',  '+216 71 999 888', 'epicerie@carthage.tn');

INSERT INTO Don (statut, id_user, id_partenaire) VALUES
('disponible', 1, NULL),
('réservé',    1, 1),
('récupéré',   2, 2),
('disponible', 1, NULL);

INSERT INTO Don_Produit (id_don, nom_produit, quantite, date_peremption) VALUES
(1, 'Pain de mie',            10, '2025-07-01'),
(2, 'Bananes',                15, '2025-06-18'),
(2, 'Fraises',                20, '2025-06-15'),
(2, 'Pain complet',            5, '2025-06-20'),
(3, 'Conserves de tomates',   50, '2026-01-10'),
(3, 'Lentilles',              20, '2026-03-01'),
(4, 'Lait UHT',               30, '2025-08-15');
