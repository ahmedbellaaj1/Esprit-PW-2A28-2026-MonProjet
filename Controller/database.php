<?php

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'projetwebnova';
const DB_USER = 'root';
const DB_PASS = '';
const DB_ADMIN_EMAIL = 'admin@greenbit.local';
const DB_ADMIN_PASSWORD_HASH = '$2y$10$NGWC2/odjiL57B4VKr0m8.EPJf6dL4vQj7OariCOvS6JLL.NZMGAG';

// Google OAuth Configuration (loaded from .env)
const GOOGLE_CLIENT_ID = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
const GOOGLE_CLIENT_SECRET = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';

function getServerPdo(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

function initializeDatabase(PDO $serverPdo): void
{
    $dbNameQuoted = '`' . str_replace('`', '``', DB_NAME) . '`';

    $serverPdo->exec('CREATE DATABASE IF NOT EXISTS ' . $dbNameQuoted . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $serverPdo->exec('USE ' . $dbNameQuoted);

    $serverPdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
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
        ) ENGINE=InnoDB"
    );

    $serverPdo->exec(
        "CREATE TABLE IF NOT EXISTS password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $serverPdo->exec(
        "CREATE TABLE IF NOT EXISTS email_verifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL UNIQUE,
            token VARCHAR(255) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            photo VARCHAR(255) DEFAULT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'user',
            statut VARCHAR(50) NOT NULL DEFAULT 'actif',
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $seed = $serverPdo->prepare(
        'INSERT INTO users (nom, prenom, email, mot_de_passe, role, statut)
         SELECT :nom, :prenom, :email, :mot_de_passe, :role, :statut
         FROM DUAL
         WHERE NOT EXISTS (
             SELECT 1 FROM users WHERE email = :seed_email
         )'
    );

    $seed->execute([
        'nom' => 'Admin',
        'prenom' => 'System',
        'email' => DB_ADMIN_EMAIL,
        'seed_email' => DB_ADMIN_EMAIL,
        'mot_de_passe' => DB_ADMIN_PASSWORD_HASH,
        'role' => 'admin',
        'statut' => 'actif',
    ]);
}

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $serverPdo = getServerPdo();
    initializeDatabase($serverPdo);

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}
