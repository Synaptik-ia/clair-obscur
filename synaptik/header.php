<?php
require_once 'includes/config.php';

// Déterminer la page active pour la navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Solutions IA et services informatiques pour les entreprises. Audit, automatisation, agents virtuels, support technique et hébergement.'; ?>">
    <meta name="keywords" content="IA, intelligence artificielle, automatisation, services IT, dépannage informatique, hébergement web, conseil stratégique">
    <meta name="author" content="Synaptik IA Solutions">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo SITE_DOMAIN . '/' . $current_page; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_DOMAIN . '/' . $current_page; ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'Solutions IA et services informatiques pour les entreprises.'; ?>">
    <meta property="og:image" content="<?php echo SITE_DOMAIN; ?>/assets/images/og-image.jpg">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Global CSS unique -->
    <link rel="stylesheet" href="assets/css/global.css">
    
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
</head>
<body>

<!-- ========== PAGE LOADER AMÉLIORÉ ========== -->
<div class="page-loader" id="pageLoader">
    <div class="loader-container">
        <div class="loader-ring">
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="loader-logo">S⚡</div>
        </div>
        <div class="loader-text">
            Chargement
            <span class="loader-dots">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </div>
    </div>
</div>

<!-- ========== EFFETS DE LUMIÈRE ========== -->
<div class="light-effect">
    <div class="technical-grain"></div>
    <div class="scan-line"></div>
    <div class="shine-effect"></div>
</div>

<header class="site-header">
    <div class="container header-inner">
        <div class="logo-area">
           <a href="index.php" style="text-decoration: none;"><img class="logo-img" src="assets/images/logo-synaptik.png" alt="Synaptik IA Solutions - Logo circuit S"></a>
           <a href="index.php" style="text-decoration: none;"><span class="logo-text">Synaptik IA Solutions</span></a>
        </div>
        <button class="mobile-toggle" aria-label="Menu" id="menuToggle">☰</button>
        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Accueil</a></li>
                <li><a href="realisations.php" class="<?php echo $current_page == 'realisations.php' ? 'active' : ''; ?>">Nos Réalisations</a></li>
                <li><a href="services-ia.php" class="<?php echo $current_page == 'services-ia.php' ? 'active' : ''; ?>">Services IA</a></li>
                <li><a href="services-it.php" class="<?php echo $current_page == 'services-it.php' ? 'active' : ''; ?>">Services IT</a></li>
                <li><a href="apropos.php" class="<?php echo $current_page == 'apropos.php' ? 'active' : ''; ?>">À Propos</a></li>
                <li><a href="contact.php" class="nav-cta <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<script>
    // Menu mobile toggle
    const toggleBtn = document.getElementById('menuToggle');
    const navMenu = document.getElementById('mainNav');
    if(toggleBtn && navMenu) {
        toggleBtn.addEventListener('click', () => navMenu.classList.toggle('open'));
    }
    
    // ===== PAGE LOADER =====
    // Afficher le loader au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            // Ajouter un délai minimal pour que le loader soit visible
            setTimeout(() => {
                loader.classList.remove('active');
            }, 500);
        }
        
        // Ajouter la classe de transition au contenu principal
        const mainContent = document.querySelector('main');
        if (mainContent) {
            mainContent.classList.add('page-transition');
        }
    });
    
    // Intercepter les clics sur les liens internes pour la transition
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && link.href && link.href.indexOf(window.location.origin) === 0 && !link.hasAttribute('download') && link.target !== '_blank') {
            // Ignorer les liens avec href "#"
            if (link.getAttribute('href') === '#') return;
            
            e.preventDefault();
            const destination = link.href;
            
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('active');
            }
            
            setTimeout(() => {
                window.location.href = destination;
            }, 350);
        }
    });
    
    // ===== EFFETS DE LUMIÈRES ALÉATOIRES =====
    
    // Création de particules aléatoires
    function createParticle() {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        const size = Math.random() * 4 + 2;
        const xPos = Math.random() * 100;
        const duration = Math.random() * 8 + 4;
        const delay = Math.random() * 10;
        const xOffset = (Math.random() - 0.5) * 200;
        
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = xPos + '%';
        particle.style.animationDuration = duration + 's';
        particle.style.animationDelay = delay + 's';
        particle.style.setProperty('--x-offset', xOffset + 'px');
        
        document.querySelector('.light-effect').appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, (duration + delay) * 1000);
    }
    
    // Créer des particules en continu
    setInterval(() => {
        if (Math.random() > 0.7) {
            createParticle();
        }
    }, 300);
    
    // Création de néons aléatoires
    function createNeonGlow() {
        const neon = document.createElement('div');
        neon.classList.add('neon-glow');
        
        const xPos = Math.random() * 100;
        const yPos = Math.random() * 100;
        const size = Math.random() * 200 + 200;
        const duration = Math.random() * 10 + 5;
        const delay = Math.random() * 5;
        
        neon.style.left = xPos + '%';
        neon.style.top = yPos + '%';
        neon.style.width = size + 'px';
        neon.style.height = size + 'px';
        neon.style.animationDuration = duration + 's';
        neon.style.animationDelay = delay + 's';
        
        document.querySelector('.light-effect').appendChild(neon);
        
        setTimeout(() => {
            neon.remove();
        }, (duration + delay) * 1000);
    }
    
    // Créer des néons périodiquement
    setInterval(() => {
        if (Math.random() > 0.85) {
            createNeonGlow();
        }
    }, 4000);
    
    // Création de lignes de circuit aléatoires
    function createCircuitLine() {
        const line = document.createElement('div');
        line.classList.add('circuit-line');
        
        const isHorizontal = Math.random() > 0.5;
        const pos = Math.random() * 100;
        
        if (isHorizontal) {
            line.style.top = pos + '%';
            line.style.left = '0';
            line.style.height = '2px';
            line.style.width = '0';
        } else {
            line.style.left = pos + '%';
            line.style.top = '0';
            line.style.width = '2px';
            line.style.height = '0';
            line.style.background = 'linear-gradient(180deg, transparent, #a78bfa, #6366f1, #a78bfa, transparent)';
        }
        
        document.querySelector('.light-effect').appendChild(line);
        
        setTimeout(() => {
            line.remove();
        }, 3000);
    }
    
    // Créer des circuits périodiquement
    setInterval(() => {
        if (Math.random() > 0.9) {
            createCircuitLine();
        }
    }, 5000);
</script>