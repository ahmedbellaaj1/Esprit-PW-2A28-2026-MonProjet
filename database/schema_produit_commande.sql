-- ============================================================
-- Ajouter dans la base : projetwebnova
-- ============================================================

USE projetwebnova;

CREATE TABLE IF NOT EXISTS produits (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(150) NOT NULL,
    marque        VARCHAR(100) DEFAULT NULL,
    code_barre    VARCHAR(50)  DEFAULT NULL UNIQUE,
    categorie     VARCHAR(80)  DEFAULT NULL,
    prix          DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    calories      DECIMAL(7,2) DEFAULT NULL,
    proteines     DECIMAL(6,2) DEFAULT NULL,
    glucides      DECIMAL(6,2) DEFAULT NULL,
    lipides       DECIMAL(6,2) DEFAULT NULL,
    nutriscore    ENUM('A','B','C','D','E') NOT NULL DEFAULT 'C',
    image         VARCHAR(255) DEFAULT NULL,
    statut        ENUM('disponible','rupture') NOT NULL DEFAULT 'disponible',
    date_ajout    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS commandes (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_produit        INT UNSIGNED NOT NULL,
    id_utilisateur    INT UNSIGNED NOT NULL,
    quantite          INT UNSIGNED NOT NULL DEFAULT 1,
    prix_total        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    adresse_livraison VARCHAR(255) DEFAULT NULL,
    statut            ENUM('en_attente','confirmee','expediee','livree','annulee') NOT NULL DEFAULT 'en_attente',
    date_commande     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_produit      FOREIGN KEY (id_produit)     REFERENCES produits(id) ON DELETE CASCADE,
    CONSTRAINT fk_commande_utilisateur  FOREIGN KEY (id_utilisateur)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── DONNÉES DE TEST ─────────────────────────────────────────

INSERT INTO produits (nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, statut) VALUES
('Yaourt Nature Bio',      'Danone Bio',    '3017620422001', 'Produits laitiers',  2.50,  65,  4.5,  5.2,  1.8, 'A', 'disponible'),
('Lait Entier Frais',      'Delice',        '3017620422002', 'Produits laitiers',  1.80,  61,  3.2,  4.8,  3.5, 'B', 'disponible'),
('Pain Complet Bio',       'Harrabi',       '3017620422003', 'Cereales & Pains',   3.20, 247,  9.0, 41.0,  3.2, 'A', 'disponible'),
('Flocons d Avoine',       'Quaker',        '3017620422004', 'Cereales & Pains',   4.50, 370, 13.0, 58.0,  7.0, 'A', 'disponible'),
('Jus d Orange Pur',       'Tropicana',     '3017620422005', 'Boissons',           3.90,  45,  0.7, 10.0,  0.1, 'B', 'disponible'),
('Eau Minerale Naturelle', 'Safia',         '3017620422006', 'Boissons',           0.80,   0,  0.0,  0.0,  0.0, 'A', 'disponible'),
('Brocoli Frais Bio',      'Ferme Verte',   '3017620422007', 'Fruits & Legumes',   2.20,  34,  2.8,  6.6,  0.4, 'A', 'disponible'),
('Bananes Biologiques',    'Dole Bio',      '3017620422008', 'Fruits & Legumes',   3.10,  89,  1.1, 22.8,  0.3, 'A', 'disponible'),
('Chips Nature',           'Lays',          '3017620422009', 'Snacks & Biscuits',  2.40, 536,  7.0, 53.0, 34.0, 'E', 'disponible'),
('Biscuits Chocolat',      'LU',            '3017620422010', 'Snacks & Biscuits',  3.60, 480,  6.5, 65.0, 22.0, 'D', 'disponible'),
('Thon en Conserve',       'Saupiquet',     '3017620422011', 'Conserves',          3.20, 130, 28.0,  0.0,  1.5, 'A', 'disponible'),
('Haricots Rouges',        'Bonduelle',     '3017620422012', 'Conserves',          1.90, 127,  8.7, 22.0,  0.5, 'A', 'disponible'),
('Pates Completes Bio',    'Barilla Bio',   '3017620422013', 'Epicerie',           2.60, 352, 13.0, 68.0,  2.0, 'B', 'disponible'),
('Huile d Olive Extra',    'Zitoun d Or',   '3017620422014', 'Epicerie',           8.90, 884,  0.0,  0.0,100.0, 'B', 'disponible'),
('Miel Naturel Bio',       'Abeille d Or',  '3017620422015', 'Epicerie',           7.50, 304,  0.3, 82.4,  0.0, 'C', 'disponible'),
('Fromage Blanc 0%',       'Activia',       '3017620422016', 'Produits laitiers',  2.90,  45,  8.0,  4.0,  0.1, 'A', 'disponible'),
('Boisson Energisante',    'Red Bull',      '3017620422017', 'Boissons',           3.50,  45,  0.0, 11.0,  0.0, 'E', 'rupture'),
('Quinoa Bio',             'Alter Eco',     '3017620422018', 'Epicerie',           6.90, 368, 14.0, 64.0,  6.0, 'A', 'disponible'),
('Sauce Tomate Nature',    'Mutti',         '3017620422019', 'Conserves',          2.30,  32,  1.5,  6.4,  0.2, 'A', 'disponible'),
('Beurre Doux',            'President',     '3017620422020', 'Produits laitiers',  4.80, 717,  0.7,  0.6, 81.0, 'D', 'rupture');

INSERT INTO commandes (id_produit, id_utilisateur, quantite, prix_total, adresse_livraison, statut, date_commande) VALUES
(1,  1, 3,  7.50, '12 Rue de la Republique, Tunis',          'livree',     '2025-03-01 09:15:00'),
(3,  1, 2,  6.40, '12 Rue de la Republique, Tunis',          'livree',     '2025-03-05 11:00:00'),
(5,  1, 4, 15.60, '12 Rue de la Republique, Tunis',          'expediee',   '2025-03-28 09:00:00'),
(11, 1, 5, 16.00, '12 Rue de la Republique, Tunis',          'confirmee',  '2025-04-01 08:30:00'),
(7,  1, 2,  4.40, '12 Rue de la Republique, Tunis',          'en_attente', '2025-04-06 10:00:00'),
(18, 1, 1,  6.90, '12 Rue de la Republique, Tunis',          'en_attente', '2025-04-09 11:30:00'),
(13, 1, 3,  7.80, '45 Avenue Habib Bourguiba, Sfax',         'livree',     '2025-03-15 09:30:00'),
(14, 1, 1,  8.90, '45 Avenue Habib Bourguiba, Sfax',         'expediee',   '2025-03-30 10:15:00'),
(15, 1, 2,  5.80, '8 Rue Ibn Khaldoun, Sousse',              'confirmee',  '2025-04-03 11:00:00'),
(9,  1, 3,  7.20, '22 Avenue de Carthage, Bizerte',          'annulee',    '2025-03-15 12:00:00');
