-- Migration: table extraits_livres
-- À exécuter dans la base clair-obscur

CREATE TABLE IF NOT EXISTS `extraits_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `parsed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parsed` (`parsed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
