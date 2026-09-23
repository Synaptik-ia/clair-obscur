# AGENTS.md — Guide pour les agents IA

Documentation technique du site **Clair-Obscur Éditions** pour aider les IA à intervenir sur le code.

## Stack

- **PHP 8.4** — PHP procédural « vanilla », **sans framework ni Composer**
- **MariaDB 10.11** via PDO (`utf8mb4`, requêtes préparées obligatoires)
- **Frontend** : Bootstrap 5.3, Font Awesome 6.7, Google Fonts, jQuery 3.6 — tous chargés via CDN dans `includes/header.php`
- **Apache** : règles de réécriture et sécurité dans `.htaccess` à la racine
- Pas de build, pas de package.json : tout est PHP/HTML/CSS/JS direct

## Structure du projet

| Dossier | Rôle |
|---|---|
| `admin/` | Back-office (une page par fichier). Protégé par `redirigerSiNonAdmin()` |
| `ajax/` | Endpoints AJAX retournant du JSON (newsletter, likes, frais de port, IPN PayPal) |
| `assets/` | `css/style.css`, `js/main.js`, `images/`, `turnjs/` (liseuse flipbook) |
| `auteurs/`, `livres/`, `nouvelles/` | Pages publiques vitrine (index/fiche/article) |
| `compte/` | Espace client : connexion, inscription, profil, commandes |
| `config/` | `env.php` (chargeur .env), `database.php` (PDO + constantes + session) |
| `includes/` | `header.php`, `footer.php`, `functions.php`, `security.php` |
| `liseuse/` | Lecteur d'extraits en ligne (flipbook turn.js) |
| `paiement/`, `panier/` | Tunnel d'achat PayPal |
| `tests/` | Tests standalone exécutés via `php tests/runtests.php` |

## Configuration

- Variables d'environnement dans `.env` (copier `.env.example`). Chargées par `config/env.php` via le helper `env('CLE', 'defaut')`.
- Constantes définies dans `config/database.php` : `SITE_NAME`, `SITE_URL` (avec slash final), `ADMIN_EMAIL`, `PAYPAL_*`.
- `PAYPAL_BUSINESS_EMAIL` (dans `.env`) est requis pour le paiement PayPal Standard — sans lui, `paiement/paypal.php` bloque la commande.
- **`config/database.php` démarre la session** (cookie `Secure` + `SameSite=Strict`). En local sans HTTPS, la session ne persiste pas → adapter temporairement `session.cookie_secure` si besoin.
- Schéma BDD : `clair-obscur.sql`. Les migrations incrémentales sont les `migrations/migration_*.sql` — **à appliquer manuellement** (ex. `migrations/migration_details_type_commande.sql` ajoute `details_commandes.type_commande` pour les paniers mixtes ; le code reste compatible si elle n'est pas appliquée).
- **Fin de traitement** : à la fin de chaque modification, indiquer explicitement le fichier `migration_*.sql` à exécuter ou les commandes SQL à lancer, le cas échéant.
- `DEBUG_MODE=true` dans `.env` active `debug($data)`.

## Conventions à respecter

### Structure d'une page publique

```php
require_once '../config/database.php';   // ou 'config/database.php' à la racine
require_once '../includes/functions.php';
require_once '../includes/security.php';

$page_title = 'Titre de la page';        // utilisé par header.php
include '../includes/header.php';        // génère <!DOCTYPE>, <head>, CSS, nav
?>
<!-- contenu HTML Bootstrap -->
<?php include '../includes/footer.php'; // footer + scripts + </body></html>
```

### Structure d'une page admin

```php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

redirigerSiNonAdmin();                   // garde-fou obligatoire
// ... logique POST/GET AVANT tout output ...

$page_title = '...';
include '../includes/header.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>  <!-- menu.php = sidebar uniquement, PAS un header -->
        </div>
        <div class="col-md-9 col-lg-10">
            <!-- contenu -->
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
```

> Piège connu : `admin/menu.php` n'inclut **pas** le `<head>` — oublier `header.php` supprime tout le CSS (bug corrigé sur `admin/newsletter.php`).

### Sécurité (obligatoire)

- **Toute sortie utilisateur** : `cleanXSS($var)` (= `strip_tags` + `htmlspecialchars`, aucune balise autorisée) ou `htmlspecialchars()`. Les contenus riches admin s'affichent via `html_entity_decode` + echo brut — ne pas passer par `cleanXSS`.
- **Tout formulaire POST** : champ `csrf_token` via `generateCSRFToken()`, vérifié côté serveur avec `verifyCSRFToken()`. `security.php` bloque globalement tout POST sans token (exceptions listées dans `$exempt_pages` : IPN PayPal, `panier/ajouter_ajax.php`).
- **Aucune action d'état via GET** : suppressions/validations en POST uniquement. `panier/index.php` utilise l'attribut `form="update-cart-form"` pour rattacher les inputs au formulaire.
- **Redirections** : toujours via `safeRedirect($url, $fallback)` (functions.php) — jamais de `header('Location: ' . $_GET['redirect'])` brut.
- **Paiement** : `paiement/validation.php` ne marque JAMAIS une commande payée — seul `ajax/ipn.php` (vérification postback PayPal) le fait.
- **Toute requête SQL** : PDO prepare/execute avec placeholders nommés — jamais de concaténation
- Auth : `estConnecte()`, `estAdmin()`, `redirigerSiNonConnecte()`, `redirigerSiNonAdmin()`
- Autres helpers dans `includes/security.php` : `validateEmail()`, `rateLimit()`, `logAction()`, `hasInjectionAttempt()`

### Style de code

- Commentaires et identifiants métier **en français**
- Fonctions utilitaires centralisées dans `includes/functions.php`
- URLs absolues via `SITE_URL` (ex : `SITE_URL . 'assets/css/style.css'`), chemins d'include relatifs (`'../includes/...'`)
- Pas de système de templates : HTML et PHP mélangés dans les fichiers de page
- Les `header('Location: ...')` et `header('Content-Type: ...')` doivent précéder tout output → logique POST en haut de fichier

## Tests

```bash
php tests/runtests.php
```

Lance tous les `test_*.php` listés dans `runtests.php`. Convention : chaque test affiche un résumé `X réussis, Y échoués` et exit code 0 si OK. Pour ajouter un test, créer `tests/test_*.php` et l'enregistrer dans le tableau `$test_files` de `runtests.php`.

## Fonctionnalités principales

- **Boutique** : panier (session), paiement PayPal (`PAYPAL_MODE` sandbox/live), IPN dans `ajax/ipn.php`, livres digitaux téléchargeables via `download.php` (lien signé)
- **Newsletter** : double opt-in (token + email de confirmation), soft delete (`deleted_at`), endpoints `ajax/newsletter.php`, gestion dans `admin/newsletter.php`
- **Social** : likes (`toggleLike`), commentaires modérés (`status = 'en_attente'`)
- **Liseuse** : extraits flipbook (turn.js) configurés via `admin/liseuse_*`
- **Chat IA** : widget n8n « Julia » intégré dans `includes/footer.php` (`N8N_WEBHOOK_URL`)
- **SEO** : `sitemap.xml`, `includes/sitemap.php`, page admin `seo.php`, meta dans `header.php` via `$page_description`/`$keywords`
