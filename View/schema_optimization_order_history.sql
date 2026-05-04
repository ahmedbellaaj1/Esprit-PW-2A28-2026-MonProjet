-- ===============================================
-- Optimisation de la Base de Données
-- Fonctionnalité: Historique d'Achats
-- ===============================================

-- Ce script ajoute des index et optimisations
-- pour la fonctionnalité d'historique d'achats

USE greenbite;

-- ===============================================
-- 1. INDEX POUR AMÉLIORER LES PERFORMANCES
-- ===============================================

-- Index sur id_utilisateur (critère de filtrage principal)
ALTER TABLE commandes ADD INDEX IF NOT EXISTS idx_id_utilisateur (id_utilisateur);

-- Index sur la date de commande (pour le tri)
ALTER TABLE commandes ADD INDEX IF NOT EXISTS idx_date_commande (date_commande DESC);

-- Index composite (id_utilisateur, date_commande)
-- Cet index est très efficace pour les requêtes du type:
-- WHERE id_utilisateur = ? ORDER BY date_commande DESC
ALTER TABLE commandes ADD INDEX IF NOT EXISTS idx_user_date (id_utilisateur, date_commande DESC);

-- Index sur le statut (pour les filtres futurs)
ALTER TABLE commandes ADD INDEX IF NOT EXISTS idx_statut (statut);

-- Index sur id_produit pour les joins
ALTER TABLE commandes ADD INDEX IF NOT EXISTS idx_id_produit (id_produit);

-- Index sur produits.id_produit (clé primaire, déjà existant)
-- Vérifier les performances du join

-- ===============================================
-- 2. ANALYSE DES PERFORMANCES
-- ===============================================

-- Exécutez ces requêtes EXPLAIN pour analyser les performances:

-- Query de base: récupérer l'historique d'un utilisateur
-- EXPLAIN SELECT c.*, p.nom as produit_nom, p.marque as produit_marque 
-- FROM commandes c 
-- LEFT JOIN produits p ON c.id_produit = p.id_produit
-- WHERE c.id_utilisateur = 1
-- ORDER BY c.date_commande DESC;

-- ===============================================
-- 3. VÉRIFICATION DES INDEX
-- ===============================================

-- Afficher tous les index de la table commandes
-- SHOW INDEX FROM commandes;

-- ===============================================
-- 4. STATISTIQUES DE LA TABLE
-- ===============================================

-- Analyser la table commandes pour mettre à jour les statistiques
ANALYZE TABLE commandes;
ANALYZE TABLE produits;

-- ===============================================
-- 5. CONFIGURATION DE STOCKAGE (Optionnel)
-- ===============================================

-- Pour les très grandes tables, considérez le partitionnement:
-- (Cette partie dépend de votre volume de données)

-- Exemple: Partitionnement par année
-- ALTER TABLE commandes PARTITION BY RANGE (YEAR(date_commande)) (
--     PARTITION p2022 VALUES LESS THAN (2023),
--     PARTITION p2023 VALUES LESS THAN (2024),
--     PARTITION p2024 VALUES LESS THAN (2025),
--     PARTITION p_future VALUES LESS THAN MAXVALUE
-- );

-- ===============================================
-- 6. VUE POUR LES STATISTIQUES UTILISATEURS
-- ===============================================

-- Créer une vue pour les statistiques d'un utilisateur
CREATE OR REPLACE VIEW v_user_order_stats AS
SELECT 
    c.id_utilisateur,
    COUNT(c.id_commande) as total_commandes,
    SUM(c.prix_total) as montant_total,
    AVG(c.prix_total) as prix_moyen,
    MAX(c.date_commande) as derniere_commande,
    MIN(c.date_commande) as premiere_commande
FROM commandes c
GROUP BY c.id_utilisateur;

-- ===============================================
-- 7. VUE POUR LES DÉTAILS COMPLETS DES COMMANDES
-- ===============================================

-- Créer une vue pour faciliter les requêtes d'historique
CREATE OR REPLACE VIEW v_order_history AS
SELECT 
    c.id_commande,
    c.id_produit,
    c.id_utilisateur,
    c.quantite,
    c.prix_total,
    c.date_commande,
    c.statut,
    c.mode_livraison,
    c.date_livraison_souhaitee,
    c.adresse_livraison,
    c.methode_paiement,
    p.nom as produit_nom,
    p.marque as produit_marque,
    p.categorie as produit_categorie,
    p.image as produit_image
FROM commandes c
LEFT JOIN produits p ON c.id_produit = p.id_produit
ORDER BY c.date_commande DESC;

-- ===============================================
-- 8. PROCÉDURE STOCKÉE POUR L'HISTORIQUE
-- ===============================================

DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_user_order_history(IN p_id_utilisateur INT)
BEGIN
    SELECT 
        c.id_commande,
        c.id_produit,
        c.quantite,
        c.prix_total,
        c.date_commande,
        c.statut,
        c.mode_livraison,
        c.date_livraison_souhaitee,
        c.adresse_livraison,
        p.nom as produit_nom,
        p.marque as produit_marque
    FROM commandes c
    LEFT JOIN produits p ON c.id_produit = p.id_produit
    WHERE c.id_utilisateur = p_id_utilisateur
    ORDER BY c.date_commande DESC;
END //

DELIMITER ;

-- ===============================================
-- 9. PROCÉDURE POUR LES STATISTIQUES
-- ===============================================

DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_user_statistics(IN p_id_utilisateur INT)
BEGIN
    SELECT 
        COUNT(id_commande) as total_commandes,
        SUM(prix_total) as montant_total,
        AVG(prix_total) as prix_moyen,
        MIN(date_commande) as premiere_commande,
        MAX(date_commande) as derniere_commande
    FROM commandes
    WHERE id_utilisateur = p_id_utilisateur;
END //

DELIMITER ;

-- ===============================================
-- 10. TRIGGERS POUR L'AUDIT (Optionnel)
-- ===============================================

-- Créer une table d'audit si elle n'existe pas
CREATE TABLE IF NOT EXISTS commandes_audit (
    id_audit INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT NOT NULL,
    ancien_statut VARCHAR(20),
    nouveau_statut VARCHAR(20),
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
    modifie_par VARCHAR(100) DEFAULT 'SYSTEM'
);

-- Trigger pour tracker les changements de statut
DELIMITER //

CREATE TRIGGER IF NOT EXISTS tr_commande_statut_change
AFTER UPDATE ON commandes
FOR EACH ROW
BEGIN
    IF OLD.statut != NEW.statut THEN
        INSERT INTO commandes_audit 
        (id_commande, ancien_statut, nouveau_statut) 
        VALUES 
        (NEW.id_commande, OLD.statut, NEW.statut);
    END IF;
END //

DELIMITER ;

-- ===============================================
-- 11. REQUÊTES D'OPTIMISATION UTILES
-- ===============================================

-- Compacter les tables (à faire régulièrement)
-- OPTIMIZE TABLE commandes;
-- OPTIMIZE TABLE produits;

-- ===============================================
-- 12. MAINTENANCE SCHEDULE
-- ===============================================

-- À exécuter mensuellement:
-- 1. ANALYZE TABLE commandes;
-- 2. ANALYZE TABLE produits;
-- 3. OPTIMIZE TABLE commandes;

-- À exécuter trimestriellement:
-- 1. CHECK TABLE commandes;
-- 2. REPAIR TABLE commandes;

-- ===============================================
-- 13. SCRIPTS DE TEST
-- ===============================================

-- Insérer des données de test pour l'historique
-- (À utiliser seulement en développement)

/*
INSERT INTO commandes 
(id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, 
 mode_livraison, date_livraison_souhaitee, adresse_livraison)
VALUES 
(1, 1, 2, 25.00, '2024-05-01 10:00:00', 'livree', 'standard', '2024-05-03', '123 Rue de la Paix'),
(2, 1, 1, 15.50, '2024-05-02 14:30:00', 'confirmee', 'express', '2024-05-03', '123 Rue de la Paix'),
(3, 1, 3, 45.75, '2024-05-03 09:15:00', 'en-preparation', 'standard', '2024-05-05', '123 Rue de la Paix');
*/

-- ===============================================
-- 14. VERIFICATION FINALE
-- ===============================================

-- Vérifier que les index sont bien créés
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'greenbite' 
AND TABLE_NAME = 'commandes'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- Vérifier les statistiques de la table
SHOW TABLE STATUS FROM greenbite WHERE NAME = 'commandes';

-- ===============================================
-- NOTES DE PERFORMANCE
-- ===============================================

/*
Configuration optimale pour les requêtes d'historique:

1. INDEX COMPOSITE (id_utilisateur, date_commande DESC):
   - Permet une recherche efficace par utilisateur
   - Le tri par date se fait directement via l'index
   - Évite les sorts en mémoire

2. LEFT JOIN sur produits:
   - Récupère les informations du produit
   - L'index sur id_produit permet un join rapide

3. Considérations:
   - Pour les très gros volumes (>100k commandes), 
     utiliser la pagination
   - Mettre en cache les vues si les données ne changent 
     pas très fréquemment
   - Redis/Memcached peuvent être utilisés pour le cache

4. Limitation de résultats:
   SELECT ... LIMIT 50 OFFSET 0;
   - Réduire la bande passante
   - Améliorer les temps de réponse

5. Monitoring:
   - Utiliser EXPLAIN EXTENDED pour analyser les requêtes
   - Vérifier le slow query log régulièrement
   - SET SESSION sql_mode='STRICT_TRANS_TABLES';
*/

-- ===============================================
-- FIN DU SCRIPT D'OPTIMISATION
-- ===============================================
