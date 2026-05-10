-- =====================================================
-- BASE DE DONNÉES GREENBITE - VERSION FINALE
-- AVEC AVIS ET FAVORIS (pour tous les utilisateurs)
-- =====================================================

-- 1. Création de la base de données (si elle n'existe pas)
CREATE DATABASE IF NOT EXISTS projetwebnova;
USE projetwebnova;

-- =====================================================
-- SUPPRESSION DES TABLES EXISTANTES
-- (ordre correct : supprimer d'abord les tables enfant, puis les tables parent)
-- =====================================================

DROP TABLE IF EXISTS favoris;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS participation;
DROP TABLE IF EXISTS participant;
DROP TABLE IF EXISTS newsletter;
DROP TABLE IF EXISTS evenement;
DROP TABLE IF EXISTS organisateur;
DROP TABLE IF EXISTS users;

-- =====================================================
-- TABLE USERS (Module User)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    photo VARCHAR(500) NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE ORGANISATEUR
-- =====================================================
CREATE TABLE organisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20) NOT NULL,
    adresse TEXT,
    site_web VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE EVENEMENT
-- =====================================================
CREATE TABLE evenement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    date_event DATE NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    type ENUM('Atelier', 'Conférence', 'Festival', 'Autre') NOT NULL,
    organisateur_id INT NOT NULL,
    capacite_max INT DEFAULT 100,
    prix DECIMAL(10,2) DEFAULT 0.00,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisateur_id) REFERENCES organisateur(id) ON DELETE CASCADE,
    INDEX idx_date (date_event),
    INDEX idx_type (type),
    INDEX idx_lieu (lieu),
    INDEX idx_organisateur (organisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE PARTICIPATION (liée à users)
-- =====================================================
CREATE TABLE participation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    user_id INT NOT NULL,
    statut ENUM('inscrit', 'present', 'annule', 'en_attente') DEFAULT 'inscrit',
    code_qr VARCHAR(255) UNIQUE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP NULL,
    FOREIGN KEY (evenement_id) REFERENCES evenement(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (evenement_id, user_id),
    INDEX idx_evenement (evenement_id),
    INDEX idx_user (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE AVIS (Pour tous les utilisateurs connectés)
-- =====================================================
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    user_id INT NOT NULL,
    note INT NOT NULL,
    commentaire TEXT NOT NULL,
    statut ENUM('en_attente', 'publie', 'rejete') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evenement_id) REFERENCES evenement(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avis (evenement_id, user_id),
    INDEX idx_evenement (evenement_id),
    INDEX idx_user (user_id),
    INDEX idx_note (note),
    INDEX idx_statut (statut),
    INDEX idx_date (date_creation),
    CONSTRAINT check_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE FAVORIS (Pour tous les utilisateurs connectés)
-- =====================================================
CREATE TABLE favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    user_id INT NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evenement_id) REFERENCES evenement(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (evenement_id, user_id),
    INDEX idx_user (user_id),
    INDEX idx_evenement (evenement_id),
    INDEX idx_date (date_ajout)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE NEWSLETTER
-- =====================================================
CREATE TABLE newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    date_abonnement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTION DES DONNÉES DE TEST
-- =====================================================

-- 1. Insertion des utilisateurs
INSERT INTO users (nom, prenom, email, mot_de_passe, photo, role, statut) VALUES
('Ben Ali', 'Ahmed', 'ahmed@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Mansour', 'Sarra', 'sarra@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Jbeli', 'Karim', 'karim@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Trabelsi', 'Leila', 'leila@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Amine', 'Mohamed', 'amine@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Cherni', 'Nour', 'nour@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Hamdi', 'Oussama', 'oussama@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'user', 'actif'),
('Admin', 'GreenBite', 'admin@greenbite.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', NULL, 'admin', 'actif');

-- 2. Insertion des organisateurs
INSERT INTO organisateur (nom, email, telephone, adresse, site_web) VALUES
('GreenBite Association', 'contact@greenbite.com', '71 123 456', 'Tunis Centre', 'https://greenbite.com'),
('EcoEvent Tunisie', 'info@ecoevent.tn', '72 789 012', 'Sfax', 'https://ecoevent.tn'),
('Nature & Découverte', 'contact@nature.tn', '73 456 789', 'Hammamet', 'https://nature.tn'),
('Tech For Good', 'contact@techforgood.tn', '70 123 456', 'Tunis', 'https://techforgood.tn'),
('Art & Culture', 'info@artculture.tn', '71 987 654', 'Sousse', 'https://artculture.tn');

-- 3. Insertion des événements (dates dynamiques dans le futur)
INSERT INTO evenement (titre, description, date_event, lieu, type, organisateur_id, capacite_max, prix) VALUES
('Atelier Vegan', 'Apprenez à cuisiner des plats végétaliens délicieux et équilibrés.', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Tunis', 'Atelier', 1, 50, 0.00),
('Conférence Écologie', 'Découvrez les initiatives pour des villes plus vertes et durables.', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Sfax', 'Conférence', 2, 200, 0.00),
('Festival GreenBite', 'Célébrez l\'écologie avec des concerts, ateliers et food trucks.', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Hammamet', 'Festival', 1, 500, 0.00),
('Atelier Jardinage Bio', 'Techniques de jardinage biologique pour débutants.', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Sousse', 'Atelier', 3, 30, 0.00),
('Conférence Zéro Déchet', 'Astuces pour réduire ses déchets au quotidien.', DATE_ADD(CURDATE(), INTERVAL 80 DAY), 'Tunis', 'Conférence', 2, 100, 0.00),
('Hackathon Écologique', 'Créez des solutions innovantes pour l\'environnement.', DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Tunis', 'Autre', 4, 80, 0.00),
('Exposition Art Recyclé', 'Découvrez des œuvres d\'art créées à partir de matériaux recyclés.', DATE_ADD(CURDATE(), INTERVAL 150 DAY), 'Sfax', 'Autre', 5, 150, 0.00),
('Atelier Cuisine Locale', 'Valorisez les produits locaux et de saison.', DATE_ADD(CURDATE(), INTERVAL 160 DAY), 'Nabeul', 'Atelier', 3, 40, 0.00);

-- 4. Insertion des participations (inscriptions)
INSERT INTO participation (evenement_id, user_id, statut, code_qr) VALUES
(1, 1, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(1, 2, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(2, 3, 'present', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(3, 4, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(3, 5, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(3, 6, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', ''))),
(4, 7, 'inscrit', CONCAT('QR_', REPLACE(UUID(), '-', '')));

-- 5. Insertion des avis (pour tous les utilisateurs connectés)
INSERT INTO avis (evenement_id, user_id, note, commentaire, statut) VALUES
(1, 1, 5, 'Atelier très instructif, je recommande !', 'publie'),
(1, 2, 4, 'Bonne organisation, cuisine délicieuse.', 'publie'),
(2, 3, 5, 'Conférence inspirante, intervenants de qualité.', 'publie'),
(3, 4, 5, 'Festival génial, à refaire chaque année !', 'publie'),
(4, 7, 4, 'Très bon atelier, j\'ai beaucoup appris.', 'en_attente'),
(5, 1, 5, 'Super conférence, très intéressante !', 'publie'),
(6, 2, 4, 'Hackathon très enrichissant.', 'publie');

-- 6. Insertion des favoris (pour tous les utilisateurs connectés)
INSERT INTO favoris (evenement_id, user_id) VALUES
(1, 1),
(2, 1),
(3, 2),
(3, 3),
(5, 4),
(1, 3),
(4, 1),
(7, 1);

-- 7. Insertion des abonnés newsletter
INSERT INTO newsletter (user_id, actif) VALUES
(1, TRUE),
(2, TRUE),
(3, TRUE),
(4, TRUE);

-- =====================================================
-- MISES À JOUR POUR LES ÉVÉNEMENTS AVEC DATES PASSÉES
-- =====================================================

-- Ajouter quelques événements avec des dates passées pour tester les filtres
INSERT INTO evenement (titre, description, date_event, lieu, type, organisateur_id, capacite_max, prix) VALUES
('Événement Passé 1', 'Cet événement a déjà eu lieu.', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Tunis', 'Atelier', 1, 30, 0.00),
('Événement Passé 2', 'Cet événement a déjà eu lieu également.', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Sfax', 'Conférence', 2, 50, 0.00),
('Événement Passé 3', 'Un autre événement passé.', DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Hammamet', 'Festival', 1, 100, 0.00);

-- Ajouter des avis sur les événements passés
INSERT INTO avis (evenement_id, user_id, note, commentaire, statut) VALUES
(9, 5, 3, 'Événement moyen, déçue.', 'publie'),
(10, 6, 4, 'Bonne conférence malgré la date.', 'publie');

-- Ajouter des favoris sur les événements passés
INSERT INTO favoris (evenement_id, user_id) VALUES
(9, 2),
(10, 3);

-- =====================================================
-- VÉRIFICATION DES DONNÉES
-- =====================================================

-- Afficher les statistiques
SELECT 
    (SELECT COUNT(*) FROM users) AS nb_utilisateurs,
    (SELECT COUNT(*) FROM organisateur) AS nb_organisateurs,
    (SELECT COUNT(*) FROM evenement) AS nb_evenements,
    (SELECT COUNT(*) FROM participation) AS nb_participations,
    (SELECT COUNT(*) FROM avis WHERE statut = 'publie') AS nb_avis,
    (SELECT COUNT(*) FROM favoris) AS nb_favoris,
    (SELECT COUNT(*) FROM newsletter WHERE actif = TRUE) AS nb_abonnes;

-- Afficher les avis avec détails utilisateur
SELECT a.id, u.nom, u.prenom, e.titre as evenement, a.note, a.commentaire, a.statut, a.date_creation
FROM avis a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN evenement e ON a.evenement_id = e.id
WHERE a.statut = 'publie'
ORDER BY a.date_creation DESC;

-- Afficher les favoris par utilisateur
SELECT u.nom, u.prenom, COUNT(f.id) as nb_favoris
FROM users u
LEFT JOIN favoris f ON u.id = f.user_id
GROUP BY u.id
ORDER BY nb_favoris DESC;

-- Afficher les événements les plus populaires (favoris + avis)
SELECT 
    e.titre,
    COUNT(DISTINCT f.id) as nb_favoris,
    COUNT(DISTINCT a.id) as nb_avis,
    ROUND(AVG(a.note), 1) as note_moyenne
FROM evenement e
LEFT JOIN favoris f ON e.id = f.evenement_id
LEFT JOIN avis a ON e.id = a.evenement_id AND a.statut = 'publie'
GROUP BY e.id
ORDER BY nb_favoris DESC, note_moyenne DESC;

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================