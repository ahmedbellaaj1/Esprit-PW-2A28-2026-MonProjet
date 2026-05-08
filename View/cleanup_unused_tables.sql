-- =============================================================================
-- SCRIPT DE NETTOYAGE - SUPPRESSION DES TABLES INUTILISÉES
-- =============================================================================
-- Ce script supprime les tables qui ne sont pas utilisées dans le projet
-- Exécutez ce script dans phpMyAdmin ou en ligne de commande MySQL
-- =============================================================================

USE greenbite;

-- Avant de supprimer, on désactive les contraintes de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- TABLE NON UTILISÉE : avis_photos
-- Description : Créée pour stocker les photos des avis, mais jamais utilisée
--               dans le code source
-- =============================================================================
DROP TABLE IF EXISTS avis_photos;

-- Réactiver les contraintes de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Confirmation de suppression
-- =============================================================================
-- Tables supprimées : 1 (avis_photos)
-- Raison : Cette table était créée mais n'était jamais utilisée dans le code.
--          Elle prenait de l'espace mémoire inutilement.
-- =============================================================================

SHOW TABLES;  -- Affiche la liste des tables restantes
