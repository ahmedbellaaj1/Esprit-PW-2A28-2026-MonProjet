-- ============================================================
-- Migration : Module Événements → Base de données greenbite
-- À exécuter APRÈS que la base greenbite existe déjà
-- (les tables users, products, etc. sont supposées présentes)
-- ============================================================

USE `greenbite`;

-- --------------------------------------------------------
-- Table `organisateur`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `adresse` text DEFAULT NULL,
  `site_web` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `evenement`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `evenement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_event` date NOT NULL,
  `lieu` varchar(100) NOT NULL,
  `type` enum('Atelier','Conférence','Festival','Autre') NOT NULL,
  `organisateur_id` int(11) NOT NULL,
  `capacite_max` int(11) DEFAULT 100,
  `prix` decimal(10,2) DEFAULT 0.00,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_event`),
  KEY `idx_type` (`type`),
  KEY `idx_lieu` (`lieu`),
  KEY `idx_organisateur` (`organisateur_id`),
  CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (`organisateur_id`) REFERENCES `organisateur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `participation` (lie users de greenbite)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `participation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evenement_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `statut` enum('inscrit','present','annule','en_attente') DEFAULT 'inscrit',
  `code_qr` varchar(255) DEFAULT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_validation` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_participation` (`evenement_id`,`user_id`),
  UNIQUE KEY `code_qr` (`code_qr`),
  KEY `idx_evenement` (`evenement_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `participation_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenement` (`id`) ON DELETE CASCADE,
  CONSTRAINT `participation_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `avis` (lie users de greenbite)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evenement_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('en_attente','publie','rejete') DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evenement` (`evenement_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenement` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `favoris` (lie users de greenbite)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favoris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evenement_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favori` (`evenement_id`,`user_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenement` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `newsletter` (lie users de greenbite)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `date_abonnement` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  CONSTRAINT `newsletter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Données de démo : Organisateurs
-- --------------------------------------------------------
INSERT IGNORE INTO `organisateur` (`nom`, `email`, `telephone`, `adresse`, `site_web`) VALUES
('GreenBite Association', 'contact@greenbite.com', '71 123 456', 'Tunis Centre', 'https://greenbite.com'),
('EcoEvent Tunisie', 'info@ecoevent.tn', '72 789 012', 'Sfax', 'https://ecoevent.tn'),
('Nature & Découverte', 'contact@nature.tn', '73 456 789', 'Hammamet', 'https://nature.tn'),
('Tech For Good', 'contact@techforgood.tn', '70 123 456', 'Tunis', 'https://techforgood.tn'),
('Art & Culture', 'info@artculture.tn', '71 987 654', 'Sousse', 'https://artculture.tn');

-- --------------------------------------------------------
-- Données de démo : Événements
-- --------------------------------------------------------
INSERT IGNORE INTO `evenement` (`titre`, `description`, `date_event`, `lieu`, `type`, `organisateur_id`, `capacite_max`, `prix`) VALUES
('Atelier Vegan', 'Apprenez à cuisiner des plats végétaliens délicieux et équilibrés.', '2026-06-23', 'Tunis', 'Atelier', 1, 50, 0.00),
('Conférence Écologie', 'Découvrez les initiatives pour des villes plus vertes et durables.', '2026-07-08', 'Sfax', 'Conférence', 2, 200, 0.00),
('Festival GreenBite', 'Célébrez l''écologie avec des concerts, ateliers et food trucks.', '2026-08-08', 'Hammamet', 'Festival', 1, 500, 0.00),
('Atelier Jardinage Bio', 'Techniques de jardinage biologique pour débutants.', '2026-06-29', 'Sousse', 'Atelier', 3, 30, 0.00),
('Conférence Zéro Déchet', 'Astuces pour réduire ses déchets au quotidien.', '2026-09-28', 'Tunis', 'Conférence', 2, 100, 0.00),
('Hackathon Écologique', 'Créez des solutions innovantes pour l''environnement.', '2026-10-06', 'Tunis', 'Autre', 4, 80, 0.00),
('Exposition Art Recyclé', 'Découvrez des œuvres d''art créées à partir de matériaux recyclés.', '2026-11-06', 'Sfax', 'Autre', 5, 150, 0.00),
('Atelier Cuisine Locale', 'Valorisez les produits locaux et de saison.', '2026-11-16', 'Nabeul', 'Atelier', 3, 40, 0.00);
