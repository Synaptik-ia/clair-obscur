# Rapport d'Audit — Clair-Obscur Éditions

**Date :** 3 août 2026  
**Périmètre :** Ensemble du repository  
**Note globale : 4/10**

---

## Résumé exécutif

Clair-Obscur est une application PHP monolithique de maison d'édition (librairie en ligne + backoffice + liseuse interactive). Le projet est fonctionnel mais présente des lacunes de sécurité critiques, une absence totale de tests, une duplication massive de code, et une architecture sans séparation des responsabilités. Les secrets (base de données, PayPal, reCAPTCHA, n8n, Google Analytics) sont tous hardcodés en clair. La protection CSRF est incohérente. Aucun framework ni gestionnaire de dépendances n'est utilisé.

---

## 1. Architecture actuelle

```
clair-obscur/
├── admin/           (22 fichiers) — Backoffice (CRUD auteurs, livres, nouvelles, commandes, etc.)
├── ajax/            (3 fichiers)  — Endpoints AJAX (like, frais_port, ipn PayPal)
├── assets/          — Images, CSS, JS, Turn.js
├── auteurs/         — Pages publiques auteurs (liste + fiche)
├── cgv/             — Page CGV
├── compte/          — Authentification client (connexion, inscription, profil, commandes)
├── config/          — Configuration BDD (1 fichier)
├── contact/         — Formulaire de contact
├── includes/        — Header, footer, functions, security
├── liseuse/         — Lecteur de livre interactif (Turn.js)
├── livres/          — Pages publiques livres (liste + fiche)
├── logs/            — Logs applicatifs
├── nouvelles/       — Pages publiques actualités (liste + article)
├── paiement/        — Intégration PayPal
├── panier/          — Gestion du panier
├── synaptik/        — Site vitrine secondaire (agence web)
├── .htaccess        — Configuration Apache
├── clair-obscur.sql — Schéma + données BDD
└── index.php        — Page d'accueil
```

**Stack technique :**
- PHP 8.4 (procédural, sans framework)
- MySQL/MariaDB 5.5
- Bootstrap 5.3 CDN
- Font Awesome 6.7 CDN
- jQuery 3.6/3.7 CDN
- Turn.js (liseuse flipbook)
- n8n Chat widget (assistant IA)
- Google Analytics (gtag)
- PayPal Standard (form POST, pas de SDK)

**Dépendances externes :** Aucune (pas de composer.json, pas de package.json)

---

## 2. Problèmes CRITIQUES (P0)

### [SEC-CRIT-01] Secrets hardcodés dans le code source
- **Fichier :** `config/database.php:7-8`
- **Description :** Identifiants MySQL (`clair-obscur` / `sosVedknip09@`) en clair
- **Gravité :** CRITIQUE
- **Impact :** Compromission totale de la base de données si le code source fuit

- **Fichier :** `config/database.php:39-41`
- **Description :** `PAYPAL_CLIENT_ID` et `PAYPAL_SECRET` hardcodés (valeurs placeholder mais le pattern est dangereux)
- **Gravité :** CRITIQUE

- **Fichier :** `contact/index.php:16-17`
- **Description :** Clés reCAPTCHA v3 hardcodées (`6LeWgvks...`)
- **Gravité :** CRITIQUE

- **Fichier :** `includes/footer.php:149`
- **Description :** URL webhook n8n exposée avec token (`f26dc497-54c0-4ea5-8b16-130e3a433a93`)
- **Gravité :** CRITIQUE

- **Fichier :** `includes/footer.php:245`
- **Description :** ID Google Analytics hardcodé (`G-66VWG0RS8D`)
- **Gravité :** ÉLEVÉ

**Recommandation :** Utiliser un fichier `.env` + `getenv()` pour tous les secrets. Ajouter `.env` au `.gitignore`.

### [SEC-CRIT-02] Protection CSRF incohérente et contournable
- **Fichier :** `includes/header.php:6-19`
- **Description :** Le check CSRF est dans `header.php`, donc seules les pages qui incluent `header.php` sont protégées. Les pages admin qui n'incluent PAS `header.php` (ex: `admin/login.php`, `admin/auteurs.php`, etc.) ne sont PAS protégées contre CSRF.
- **Gravité :** CRITIQUE
- **Impact :** Un attaquant peut forger des requêtes POST vers les pages admin (suppression, modification) si l'admin est connecté.

**Recommandation :** Déplacer la vérification CSRF dans `security.php` (inclus par toutes les pages admin) ou dans un middleware central.

### [SEC-CRIT-03] CSRF token généré après vérification dans admin/login.php
- **Fichier :** `admin/login.php:48` et `admin/login.php:118`
- **Description :** Le token CSRF est vérifié ligne 48 (`$_SESSION['csrf_token']`) mais il est généré ligne 118, APRÈS le traitement du formulaire. La première soumission échoue toujours car le token n'existe pas encore.
- **Gravité :** CRITIQUE
- **Impact :** La page de login admin est inutilisable en l'état (ou le check CSRF est bypassé car le token n'existe pas).

### [BUG-CRIT-01] Aucun test
- **Description :** Zéro test unitaire, fonctionnel ou d'intégration dans tout le projet.
- **Gravité :** CRITIQUE
- **Impact :** Aucune régression détectée, modifications à risque, pas de filet de sécurité.

---

## 3. Problèmes de HAUTE priorité (P1)

### [SEC-HIGH-01] Validation CSRF absente sur formulaires publics
- **Fichiers :** `index.php:239`, `compte/connexion.php:80`, `compte/inscription.php:130`
- **Description :** Les formulaires newsletter, connexion et inscription n'incluent pas de token CSRF. Le check dans `header.php` bloque toute soumission POST sans token.
- **Gravité :** ÉLEVÉ
- **Impact :** Soit ces formulaires ne fonctionnent pas (bloqués par le check CSRF), soit ils contournent le check (si `header.php` n'est pas inclus avant traitement).

### [SEC-HIGH-02] Session cookie non sécurisé
- **Fichier :** `config/database.php:48`
- **Description :** `session.cookie_secure = 0` — les cookies de session sont envoyés en HTTP (non chiffré).
- **Gravité :** ÉLEVÉ
- **Impact :** Vol de session possible via attaque Man-in-the-Middle.

### [SEC-HIGH-03] Pas de régénération de session après login client
- **Fichier :** `compte/connexion.php:38-44`
- **Description :** `session_regenerate_id(true)` est appelé dans `admin/login.php:85` mais PAS dans `compte/connexion.php`. Risque de session fixation.
- **Gravité :** ÉLEVÉ

### [SEC-HIGH-04] Upload de fichiers : pas de sanitization du nom
- **Fichiers :** `admin/auteur_form.php:75`, `admin/livre_form.php:118`, `admin/nouvelle_form.php:82`, `admin/upload_image.php:38`
- **Description :** Le nom de fichier est généré avec `uniqid()` + extension originale. L'extension est prise depuis `$_FILES['name']` sans validation supplémentaire. Un attaquant pourrait uploader un fichier `.php` déguisé en image si la vérification MIME est contournée.
- **Gravité :** ÉLEVÉ
- **Impact :** Exécution de code arbitraire potentielle.

### [SEC-HIGH-05] `cleanXSS()` appliqué à `$_POST` corrompt les données
- **Fichier :** `includes/security.php:16-27` et `includes/security.php:88-108`
- **Description :** `sanitizeSuperGlobals()` applique `cleanXSS()` sur `$_POST`, `$_GET`, `$_COOKIE`, `$_REQUEST`. La fonction fait `htmlspecialchars()` puis `html_entity_decode()`, ce qui est un no-op pour la plupart des chaînes MAIS peut altérer les données contenant des entités HTML. Surtout, elle modifie les superglobales en place, ce qui peut casser les comparaisons.
- **Gravité :** ÉLEVÉ
- **Impact :** Corruption silencieuse des données POST, bugs intermittents.

### [BUG-HIGH-01] Table `logs_telechargements` inexistante
- **Fichier :** `download.php:70-77`
- **Description :** Le code tente d'insérer dans `logs_telechargements` mais cette table n'existe pas dans le schéma SQL.
- **Gravité :** ÉLEVÉ
- **Impact :** Erreur fatale lors du téléchargement d'un PDF acheté.

### [BUG-HIGH-02] URL IPN PayPal incorrecte
- **Fichier :** `paiement/paypal.php:206` et `ajax/ipn.php`
- **Description :** Le formulaire PayPal pointe `notify_url` vers `paiement/ipn.php` mais le fichier IPN est dans `ajax/ipn.php`. Les notifications PayPal ne seront jamais reçues.
- **Gravité :** ÉLEVÉ
- **Impact :** Les commandes payées ne sont jamais automatiquement validées.

### [BUG-HIGH-03] Newsletter : header déjà envoyé
- **Fichier :** `index.php:253-259`
- **Description :** Le traitement newsletter est après `include 'includes/footer.php'` (ligne 261), donc après l'envoi du HTML. Le `header('Location: ...')` échoue car les headers sont déjà envoyés.
- **Gravité :** ÉLEVÉ
- **Impact :** L'inscription newsletter ne fonctionne pas (erreur + pas de redirection).

### [DB-HIGH-01] Pas de foreign key avec CASCADE sur commandes
- **Fichier :** `clair-obscur.sql:636-637`
- **Description :** `commandes.utilisateur_id` référence `utilisateurs.id` sans `ON DELETE CASCADE`. La suppression d'un utilisateur ayant des commandes échoue (protégé dans le code admin, mais pas au niveau BDD).
- **Gravité :** ÉLEVÉ

---

## 4. Problèmes de priorité MOYENNE (P2)

### [SEC-MED-01] Mot de passe : minimum 6 caractères, pas de complexité
- **Fichier :** `compte/inscription.php:47`
- **Description :** `strlen($password) < 6` est la seule vérification. Aucune exigence de majuscule, chiffre, ou caractère spécial.
- **Gravité :** MOYEN

### [SEC-MED-02] CSP désactivé
- **Fichier :** `.htaccess:48`
- **Description :** La politique Content-Security-Policy est commentée. Sans CSP, les attaques XSS sont plus faciles.
- **Gravité :** MOYEN

### [SEC-MED-03] HTTPS non forcé
- **Fichier :** `.htaccess:69-70`
- **Description :** La redirection HTTP→HTTPS est commentée.
- **Gravité :** MOYEN

### [SEC-MED-04] Pas de rate limiting sur login client
- **Fichier :** `compte/connexion.php`
- **Description :** Seul `admin/login.php` a un rate limiting (5 tentatives / 15 min). La connexion client n'a aucune protection anti brute-force.
- **Gravité :** MOYEN

### [BUG-MED-01] HTML malformé dans admin/commandes.php et admin/commentaires.php
- **Fichiers :** `admin/commandes.php:226-249`, `admin/commentaires.php:218-270`
- **Description :** Balises `</span>` orphelines après des `<td>` et autres éléments. Probablement un résidu de refactoring.
- **Gravité :** MOYEN

### [BUG-MED-02] `$stmt->execute()` sur un statement déjà consommé
- **Fichier :** `admin/seo.php:106`
- **Description :** Après traitement POST, `$stmt->execute()` est rappelé sur le statement de la ligne 44 qui a déjà été fetch. Les résultats peuvent être incohérents.
- **Gravité :** MOYEN

### [BUG-MED-03] `SITE_URL` non défini dans main.js
- **Fichier :** `assets/js/main.js:12`
- **Description :** `SITE_URL` est une constante PHP, pas une variable JavaScript. Le code `fetch(SITE_URL + 'panier/ajax-count.php')` échoue.
- **Gravité :** MOYEN
- **Impact :** Le compteur panier AJAX et les likes AJAX ne fonctionnent pas.

### [CODE-MED-01] Fichier dupliqué
- **Fichier :** `admin/nouvelles2.php`
- **Description :** Semble être une copie de `admin/nouvelle_form.php`. Fichier mort ou doublon.
- **Gravité :** MOYEN

### [CODE-MED-02] Duplication massive de code dans les pages admin
- **Fichiers :** Tous les `admin/*.php`
- **Description :** Chaque page admin répète la même structure : connexion BDD, pagination, filtres, requêtes SQL, HTML. Aucune réutilisation.
- **Gravité :** MOYEN
- **Impact :** ~300+ lignes par page, maintenance difficile, bugs propagés.

### [CODE-MED-03] Mélange français/anglais
- **Description :** Noms de variables et fonctions en français (`estConnecte`, `redirigerSiNonAdmin`) mais commentaires et noms de colonnes BDD parfois en anglais (`status`, `pending`). Pas de convention cohérente.
- **Gravité :** MOYEN

### [DB-MED-01] Index manquants
- **Fichier :** `clair-obscur.sql`
- **Description :** Pas d'index sur `commandes.statut`, `commandes.date_commande`, `livres.statut_vente`, `commentaires.status`. Les requêtes filtrées font des scans complets.
- **Gravité :** MOYEN

### [DB-MED-02] `updated_at` avec valeur par défaut dépréciée
- **Fichier :** `clair-obscur.sql:336`
- **Description :** `site_pages.updated_at` a `DEFAULT '0000-00-00 00:00:00'` qui n'est plus supporté dans les versions récentes de MySQL.
- **Gravité :** MOYEN

### [PERF-MED-01] Requête N+1 dans la page d'accueil
- **Fichier :** `index.php:36-44` et `index.php:157-166`
- **Description :** Les livres populaires sont d'abord récupérés avec un GROUP BY, puis une seconde requête récupère les détails complets. Pourrait être fait en une seule requête.
- **Gravité :** MOYEN

### [PERF-MED-02] Connexions BDD multiples par requête
- **Description :** Chaque appel à `new Database()` crée une nouvelle connexion PDO. Dans une même page, plusieurs instances sont créées (ex: `functions.php` crée la sienne, la page aussi).
- **Gravité :** MOYEN
- **Impact :** Connexions MySQL gaspillées, pas de réutilisation.

---

## 5. Problèmes MINEURS (P3)

### [CODE-LOW-01] Typo dans `cleanXSS()` allowed tags
- **Fichier :** `includes/security.php:22`
- **Description :** `<img>}` au lieu de `<img><table><tr>` — le `}` est probablement une erreur de frappe.
- **Gravité :** FAIBLE

### [CODE-LOW-02] Newsletter traitée en double
- **Fichiers :** `index.php:253-259` et `nouvelles/index.php:189-195` et `nouvelles/article.php:258-270`
- **Description :** Le même code de traitement newsletter est copié-collé dans 3 fichiers.
- **Gravité :** FAIBLE

### [CODE-LOW-03] README.md vide
- **Fichier :** `README.md`
- **Description :** Contient uniquement `# clair-obscur`. Aucune documentation d'installation, de déploiement ou d'architecture.
- **Gravité :** FAIBLE

### [UX-LOW-01] Bannière image cassée
- **Fichier :** `includes/header.php:72`
- **Description :** Référence `assets/images/banniere.png` mais le fichier est `banniere.png` à la racine.
- **Gravité :** FAIBLE

### [UX-LOW-02] Pas d'accessibilité
- **Description :** Aucun attribut `aria`, pas de gestion du focus clavier, pas de `alt` pertinent sur certaines images, contrastes non vérifiés.
- **Gravité :** FAIBLE

---

## 6. Synthèse des vulnérabilités de sécurité

| ID | Gravité | Description |
|----|---------|-------------|
| SEC-CRIT-01 | CRITIQUE | Secrets hardcodés (BDD, PayPal, reCAPTCHA, n8n, GA) |
| SEC-CRIT-02 | CRITIQUE | CSRF incohérent — pages admin non protégées |
| SEC-CRIT-03 | CRITIQUE | CSRF token admin/login généré après vérification |
| SEC-HIGH-01 | ÉLEVÉ | Formulaires publics sans token CSRF |
| SEC-HIGH-02 | ÉLEVÉ | Cookies de session non Secure |
| SEC-HIGH-03 | ÉLEVÉ | Pas de régénération de session après login client |
| SEC-HIGH-04 | ÉLEVÉ | Upload sans sanitization du nom de fichier |
| SEC-HIGH-05 | ÉLEVÉ | `cleanXSS()` corrompt `$_POST` |
| SEC-MED-01 | MOYEN | Mot de passe faible (6 car. min, pas de complexité) |
| SEC-MED-02 | MOYEN | CSP désactivé |
| SEC-MED-03 | MOYEN | HTTPS non forcé |
| SEC-MED-04 | MOYEN | Pas de rate limiting login client |

---

## 7. Dette technique

- **Absence de framework :** Code 100% procédural, pas d'ORM, pas de routing, pas de templating.
- **Absence de gestion de dépendances :** Pas de Composer, pas de NPM.
- **Absence de tests :** 0 test.
- **Absence de CI/CD :** Pas de GitHub Actions, pas de déploiement automatisé.
- **Absence d'environnement :** Pas de `.env`, pas de Docker, pas de config par environnement.
- **Duplication de code :** ~70% du code admin est dupliqué.
- **Pas de logging structuré :** `error_log()` basique, pas de niveaux de log, pas de rotation.

---

## 8. Roadmap de correction

### P0 — Immédiat (critique)

1. **Externaliser tous les secrets** dans un fichier `.env` + `.gitignore`
2. **Réparer le CSRF admin/login** — générer le token avant le traitement POST
3. **Corriger l'URL IPN PayPal** — `paiement/ipn.php` → `ajax/ipn.php` (ou déplacer le fichier)
4. **Créer la table `logs_telechargements`** ou supprimer l'INSERT dans `download.php`
5. **Corriger `SITE_URL` dans main.js** — utiliser une variable JS définie côté serveur
6. **Corriger le traitement newsletter** — déplacer avant l'inclusion du footer

### P1 — Important

7. **Uniformiser la protection CSRF** — déplacer dans `security.php`, ajouter tokens aux formulaires publics
8. **Activer `session.cookie_secure = 1`** (après passage en HTTPS)
9. **Ajouter `session_regenerate_id(true)`** dans `compte/connexion.php`
10. **Ajouter rate limiting** sur `compte/connexion.php`
11. **Forcer HTTPS** dans `.htaccess`
12. **Activer le CSP** dans `.htaccess`
13. **Ajouter index manquants** sur `commandes.statut`, `livres.statut_vente`, `commentaires.status`
14. **Renforcer la validation des mots de passe** (8+ caractères, mix majuscule/chiffre)

### P2 — Recommandé

15. **Refactorer les pages admin** — extraire la logique commune (pagination, filtres, layout)
16. **Mettre en place un singleton de connexion BDD**
17. **Nettoyer le HTML malformé** dans `commandes.php` et `commentaires.php`
18. **Supprimer `admin/nouvelles2.php`** (fichier mort)
19. **Corriger la typo `cleanXSS`** (`<img>}` → `<img><table><tr>`)
20. **Corriger `admin/seo.php`** — ré-exécuter la requête après POST
21. **Ajouter des foreign keys avec CASCADE** sur `commandes`

### P3 — Optionnel

22. **Écrire des tests** (priorité : login, panier, commandes, upload)
23. **Mettre en place Composer** + autoloading
24. **Documenter l'installation** dans README.md
25. **Ajouter l'accessibilité** (aria, focus, contrastes)
26. **Corriger le chemin de la bannière** dans `header.php`
27. **Extraire le traitement newsletter** dans une fonction réutilisable
28. **Mettre en place un environnement Docker** pour le développement
