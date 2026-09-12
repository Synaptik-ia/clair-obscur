-- Migration P0/P1 - Corrections audit
-- À exécuter dans la base clair-obscur

-- Table de logs de téléchargements
CREATE TABLE IF NOT EXISTS `logs_telechargements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `date_telechargement` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_commande` (`commande_id`),
  KEY `idx_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Ajout index manquants pour les performances
ALTER TABLE `commandes` ADD INDEX `idx_statut` (`statut`);
ALTER TABLE `commandes` ADD INDEX `idx_date_commande` (`date_commande`);
ALTER TABLE `livres` ADD INDEX `idx_statut_vente` (`statut_vente`);
ALTER TABLE `commentaires` ADD INDEX `idx_status` (`status`);

-- Ajout FK CASCADE sur commandes.utilisateur_id
ALTER TABLE `commandes` DROP FOREIGN KEY IF EXISTS `commandes_ibfk_1`;
ALTER TABLE `commandes` ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

-- Correction updated_at (valeur par défaut dépréciée)
ALTER TABLE `site_pages` MODIFY `updated_at` datetime DEFAULT NULL;
