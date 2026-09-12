# Clair-Obscur Éditions

Site web de la société d'édition **Clair-Obscur**, spécialisée dans les livres pour adultes et érotiques.

Le site comprend :

- Une partie **vitrine** : présentation des ouvrages, des auteurs, des infos et des nouveautés
- Une **boutique** permettant d'acheter chaque livre en version digitale ou en version physique

## Environnement

- **PHP 8.4**
- **MariaDB 10.11**

## Base de données

Le schéma complet est dans `clair-obscur.sql`.

Toute modification de la base doit être fournie sous forme d'**update incrémental** : un fichier `migrations/migration_<description>.sql` (ex. `ALTER TABLE`, `ADD COLUMN`), jamais en modifiant `clair-obscur.sql` directement. Les fichiers de migration sont stockés dans le répertoire `migrations/` et s'appliquent manuellement sur la base existante.

À la fin de chaque traitement, indiquer clairement le fichier de mise à jour SQL à exécuter ou les commandes SQL à lancer, le cas échéant.

## Documentation pour agents IA

Voir [AGENTS.md](AGENTS.md) : architecture, conventions, sécurité et commandes de test.
