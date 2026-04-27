-- Migration: Add quantite_disponible column if it doesn't exist
ALTER TABLE produits ADD COLUMN IF NOT EXISTS quantite_disponible INT NOT NULL DEFAULT 0 AFTER image;
