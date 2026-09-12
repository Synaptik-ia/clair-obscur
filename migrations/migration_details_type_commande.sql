-- Migration : type de commande par ligne de détail (paniers mixtes ebook + papier)
-- Permet de livrer le PDF des ebooks même quand la commande contient aussi du physique
-- À appliquer : mysql -u clair-obscur -p clair-obscur < migrations/migration_details_type_commande.sql

ALTER TABLE `details_commandes`
    ADD COLUMN `type_commande` VARCHAR(20) NOT NULL DEFAULT 'ebook' AFTER `prix_unitaire`;

-- Rétro-remplir : les lignes des commandes physiques deviennent 'physique'
UPDATE `details_commandes` dc
JOIN `commandes` c ON dc.commande_id = c.id
SET dc.type_commande = c.type_commande
WHERE c.type_commande IN ('physique', 'physique_dedicace');
