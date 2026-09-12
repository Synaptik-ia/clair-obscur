-- Migration: Table newsletter
-- À exécuter dans la base clair-obscur

CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  INDEX `idx_confirmed` (`confirmed`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Ajout colonne newsletter dans utilisateurs
ALTER TABLE `utilisateurs` ADD COLUMN `newsletter` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_admin`;
