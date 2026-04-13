CREATE DATABASE IF NOT EXISTS projetwebnova CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projetwebnova;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'user', 'moderateur') NOT NULL DEFAULT 'user',
    statut ENUM('actif', 'inactif', 'suspendu') NOT NULL DEFAULT 'actif',
    date_inscription TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (nom, prenom, email, mot_de_passe, role, statut)
VALUES
('Admin', 'System', 'admin@greenbit.local', '$2y$10$NGWC2/odjiL57B4VKr0m8.EPJf6dL4vQj7OariCOvS6JLL.NZMGAG', 'admin', 'actif')
ON DUPLICATE KEY UPDATE email = email;
