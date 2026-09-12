-- Migration: table extraits_livres
-- À exécuter dans la base clair-obscur

CREATE TABLE IF NOT EXISTS `extraits_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livre_id` int(11) NOT NULL,
  `livre_url` varchar(500) NOT NULL,
  `contenu` text NOT NULL,
  `parsed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parsed` (`parsed`),
  KEY `idx_livre_id` (`livre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Si la table existe déjà, ajouter les colonnes manquantes
-- ALTER TABLE `extraits_livres` ADD COLUMN `livre_id` int(11) NOT NULL AFTER `id`;
-- ALTER TABLE `extraits_livres` ADD COLUMN `livre_url` varchar(500) NOT NULL AFTER `livre_id`;
-- ALTER TABLE `extraits_livres` ADD INDEX `idx_livre_id` (`livre_id`);
