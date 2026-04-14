USE greenbite;

INSERT INTO produits (
    nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, statut, date_ajout
) VALUES
('Yaourt Nature Bio', 'Danone Bio', '3017620422003', 'Produits laitiers', 7.50, 95, 4.5, 12.0, 3.2, 'A', 'https://images.unsplash.com/photo-1571212515416-fef01fc43637?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Lait Avoine', 'Alpro', '5411188112345', 'Boissons', 18.90, 46, 1.0, 6.7, 1.5, 'B', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Granola Miel', 'Nestle', '7613035678901', 'Cereales & Pains', 24.00, 410, 8.2, 62.0, 13.0, 'C', 'https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Jus Orange 100%', 'Valencia', '8437001234567', 'Boissons', 14.50, 44, 0.7, 9.8, 0.2, 'B', 'https://images.unsplash.com/photo-1600271886742-f049cd5bba3f?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Pates Complete', 'Barilla', '8076809512345', 'Epicerie', 12.00, 350, 12.5, 67.0, 2.5, 'B', 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Biscuit Chocolat', 'Lu', '7622210123456', 'Snacks & Biscuits', 10.90, 498, 5.8, 62.0, 24.0, 'D', 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=800&q=60', 'attente', NOW()),
('Thon Naturel', 'Rio Mare', '8004030123456', 'Conserves', 21.50, 115, 25.0, 0.0, 1.2, 'A', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Pain Complet', 'Boulangerie Atlas', '6111245678901', 'Cereales & Pains', 8.00, 247, 9.0, 41.0, 3.5, 'A', 'https://images.unsplash.com/photo-1608198093002-ad4e005484ec?auto=format&fit=crop&w=800&q=60', 'actif', NOW()),
('Chips Salees', 'Pringles', '5053990123456', 'Snacks & Biscuits', 17.00, 536, 5.5, 49.0, 34.0, 'E', 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=800&q=60', 'inactif', NOW()),
('Compote Pomme', 'Andros', '3608580123456', 'Fruits & Legumes', 9.20, 72, 0.3, 16.2, 0.1, 'A', 'https://images.unsplash.com/photo-1579613832125-5d34a13ffe2a?auto=format&fit=crop&w=800&q=60', 'actif', NOW());

INSERT INTO commandes (
    id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, adresse_livraison
) VALUES
((SELECT id_produit FROM produits WHERE code_barre = '3017620422003' ORDER BY id_produit DESC LIMIT 1), 1, 2, 15.00, NOW(), 'confirmee', '12 Rue Hassan II, Casablanca'),
((SELECT id_produit FROM produits WHERE code_barre = '5411188112345' ORDER BY id_produit DESC LIMIT 1), 2, 3, 56.70, NOW(), 'en-cours', '45 Avenue Mohammed V, Rabat'),
((SELECT id_produit FROM produits WHERE code_barre = '7613035678901' ORDER BY id_produit DESC LIMIT 1), 3, 1, 24.00, NOW(), 'livree', '8 Rue Ibn Sina, Marrakech'),
((SELECT id_produit FROM produits WHERE code_barre = '8437001234567' ORDER BY id_produit DESC LIMIT 1), 4, 4, 58.00, NOW(), 'en-preparation', '22 Quartier Agdal, Rabat'),
((SELECT id_produit FROM produits WHERE code_barre = '8076809512345' ORDER BY id_produit DESC LIMIT 1), 5, 2, 24.00, NOW(), 'confirmee', '77 Bd Zerktouni, Casablanca'),
((SELECT id_produit FROM produits WHERE code_barre = '7622210123456' ORDER BY id_produit DESC LIMIT 1), 6, 5, 54.50, NOW(), 'annulee', '14 Rue Oued Sebou, Fes'),
((SELECT id_produit FROM produits WHERE code_barre = '8004030123456' ORDER BY id_produit DESC LIMIT 1), 7, 1, 21.50, NOW(), 'livree', '31 Avenue des FAR, Tanger'),
((SELECT id_produit FROM produits WHERE code_barre = '6111245678901' ORDER BY id_produit DESC LIMIT 1), 8, 6, 48.00, NOW(), 'en-cours', '3 Hay Salam, Agadir'),
((SELECT id_produit FROM produits WHERE code_barre = '5053990123456' ORDER BY id_produit DESC LIMIT 1), 9, 2, 34.00, NOW(), 'en-preparation', '90 Rue Al Qods, Oujda'),
((SELECT id_produit FROM produits WHERE code_barre = '3608580123456' ORDER BY id_produit DESC LIMIT 1), 10, 3, 27.60, NOW(), 'confirmee', '66 Quartier Maarif, Casablanca');
