<?php
// Ajouter la sécurité au début du header
require_once __DIR__ . '/security.php';

// Générer un token CSRF pour les formulaires
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? cleanXSS($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- SEO Meta -->
    <meta name="description" content="<?php echo isset($page_description) ? cleanXSS($page_description) : 'Maison d\'édition Clair-Obscur - Livres pour adultes'; ?>">
    <meta name="keywords" content="<?php echo isset($keywords) ? cleanXSS($keywords) : 'livres, édition, clair-obscur, littérature adulte'; ?>">
    
    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Bandeau cookies -->
<div id="cookie-banner" class="cookie-banner" style="display: none;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-0">
                    <i class="fas fa-cookie-bite"></i> 
                    Nous utilisons des cookies pour améliorer votre expérience. 
                    En continuant, vous acceptez notre <a href="<?php echo SITE_URL; ?>cgv/" target="_blank">politique de confidentialité</a>.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button id="accept-cookies" class="btn btn-sm btn-primary">Accepter</button>
                <button id="decline-cookies" class="btn btn-sm btn-secondary">Refuser</button>
            </div>
        </div>
    </div>
</div>

<!-- Bannière image seule - réduite et centrée -->
<div class="hero-banner2">
    <img src="<?php echo SITE_URL; ?>assets/images/banniere.png" alt="Clair-Obscur - Maison d'édition" class="banner-image">
</div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav" style="margin-left: auto !important;margin-right: auto !important;">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>">
                        <i class="fas fa-feather-alt"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>livres/liste.php">
                        <i class="fas fa-book"></i> Livres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>nouvelles/">
                        <i class="fas fa-newspaper"></i> Actualités
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>auteurs/">
                        <i class="fas fa-users"></i> Auteurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>contact/">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
                
                <?php if (estConnecte()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo cleanXSS($_SESSION['user_nom'] ?? 'Mon compte'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>compte/profil.php">
                                <i class="fas fa-id-card"></i> Mon profil</a>
                            </li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>compte/commandes.php">
                                <i class="fas fa-shopping-bag"></i> Mes commandes</a>
                            </li>
                            <?php if (estAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item bg-warning text-dark" href="<?php echo SITE_URL; ?>admin/">
                                    <i class="fas fa-crown"></i> Administration</a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>compte/deconnexion.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>compte/connexion.php">
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-signup" href="<?php echo SITE_URL; ?>compte/inscription.php">
                            <i class="fas fa-user-plus"></i> Inscription
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link cart-link position-relative" href="<?php echo SITE_URL; ?>panier/">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill">
                            0
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenu principal -->
<main class="container my-5">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo cleanXSS($_SESSION['flash_type']); ?> alert-dismissible fade show" role="alert">
            <?php 
                echo cleanXSS($_SESSION['flash_message']); 
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>