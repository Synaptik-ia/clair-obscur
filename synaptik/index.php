<?php
require_once 'includes/config.php';

$page_title = 'Accueil - IA et IT pour les entreprises';
$page_description = 'Synaptik IA Solutions : L\'intelligence artificielle simplifiée et les services IT performants. Création de contenu, automatisation, agents virtuels, support technique et hébergement. Audit gratuit.';
include 'header.php';
?>

<main>
<!-- 01 - Hero -->
<section class="section hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <h1>L'intelligence artificielle <span class="gradient-text">au service de votre business</span>, simplement.</h1>
                <p class="hero-text">De la création de contenu par IA à l'hébergement sécurisé de votre site, Synaptik IA Solutions est votre partenaire technologique unique.</p>
                <div class="hero-buttons">
                    <a href="contact.php" class="btn btn-primary">Discutez de votre projet →</a>
                    <a href="services-ia.php" class="btn btn-secondary">Découvrir nos services →</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/hero-ia.png" alt="Intelligence artificielle et business" class="hero-img">
            </div>
        </div>
    </div>
</section>

    <!-- 02 - Intro IA : Le Futur de l'IA -->
    <section class="section section-bg-light">
        <div class="container text-center">
            <span class="section-badge">Innovation IA</span>
            <h2>Le Futur de l'IA, <span class="gradient-text">Aujourd'hui</span></h2>
            <div class="section-subtitle">
                Imaginez des campagnes marketing créées en quelques clics, ou un agent IA qui gère votre service client 24/7. C'est possible, et nous vous aidons à le mettre en place.
            </div>
            <div class="grid-icons">
                <div class="icon-card">
                    <div class="icon-emoji">🎨✨</div>
                    <h3>Création IA</h3>
                    <p>Contenus textuels, images & vidéos percutants générés en quelques secondes.</p>
                </div>
                <div class="icon-card">
                    <div class="icon-emoji">⚙️🤖</div>
                    <h3>Automatisation</h3>
                    <p>Workflows intelligents, productivité décuplée, tâches répétitives automatisées.</p>
                </div>
                <div class="icon-card">
                    <div class="icon-emoji">📱💡</div>
                    <h3>Applications IA</h3>
                    <p>Logiciels sur mesure, agents intelligents et assistants virtuels dédiés.</p>
                </div>
                <div class="icon-card">
                    <div class="icon-emoji">🌐🧩</div>
                    <h3>Sites Web IA</h3>
                    <p>Création de sites nouvelle génération, optimisés SEO et contenus IA.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 03 - Intro IT : Une Base Informatique Solide -->
    <section class="section">
        <div class="container text-center">
            <span class="section-badge dark">Infrastructure IT</span>
            <h2>Une Base Informatique <span class="gradient-text">Solide</span></h2>
            <div class="section-subtitle">
                L'IA ne fonctionne pas sans infrastructure. Nous assurons la stabilité de vos systèmes, le dépannage de vos postes et l'hébergement de vos projets.
            </div>
            <div class="grid-icons">
                <div class="icon-card">
                    <div class="icon-emoji">🛠️💻</div>
                    <h3>Support & Dépannage</h3>
                    <p>Intervention rapide, assistance proactive et maintenance préventive.</p>
                </div>
                <div class="icon-card">
                    <div class="icon-emoji">🔌🌍</div>
                    <h3>Réseaux & Systèmes</h3>
                    <p>Admin systèmes, sécurité, cloud hybride et supervision 24/7.</p>
                </div>
                <div class="icon-card">
                    <div class="icon-emoji">☁️📧</div>
                    <h3>Hébergement Web & Emails</h3>
                    <p>Performance, sauvegardes automatiques, confiance et support inclus.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 05 - Vision (Pourquoi Synaptik ?) -->
    <section class="section section-bg-light">
        <div class="container">
            <div class="vision-grid">
                <div class="vision-content">
                    <span class="section-badge">Notre ADN</span>
                    <h2>Pourquoi <span class="gradient-text">Synaptik</span> ?</h2>
                    <p class="vision-text">Nous croyons en une technologie accessible et performante. Pas de jargon complexe, juste des solutions concrètes adaptées à votre taille.</p>
                    <p class="vision-commitment">Notre engagement est de vous fournir des solutions clés en main, de l'audit initial à l'installation finale, avec un accompagnement transparent et sans surprise.</p>
                    <div class="vision-features">
                        <div class="vision-feature">
                            <span>✓</span> Audit gratuit sans engagement
                        </div>
                        <div class="vision-feature">
                            <span>✓</span> Solutions modulaires et évolutives
                        </div>
                        <div class="vision-feature">
                            <span>✓</span> Support réactif et personnalisé
                        </div>
                    </div>
                    <a href="apropos.php" class="btn-link">En savoir plus sur notre approche →</a>
                </div>
                <div class="vision-image">
                    <img src="assets/images/vision-tech.png" alt="Notre vision de la technologie" class="vision-img">
                </div>
            </div>
        </div>
    </section>

    <!-- 06 - Services en bref -->
    <section class="section">
        <div class="container text-center">
            <span class="section-badge dark">Notre offre</span>
            <h2>Des solutions <span class="gradient-text">complètes</span></h2>
            <div class="services-preview">
                <div class="service-preview-card">
                    <div class="service-preview-icon">🧠</div>
                    <h3>Intelligence Artificielle</h3>
                    <p>Création de contenu, automatisation, agents virtuels, applications sur mesure.</p>
                    <a href="services-ia.php" class="btn-preview">Découvrir →</a>
                </div>
                <div class="service-preview-card">
                    <div class="service-preview-icon">💻</div>
                    <h3>Services IT</h3>
                    <p>Dépannage, conseil stratégique, administration systèmes, hébergement.</p>
                    <a href="services-it.php" class="btn-preview">Découvrir →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 08 - CTA final (Prêt à simplifier ?) -->
    <section class="cta-section">
        <div class="container text-center">
            <div class="final-cta-block">
                <h2>Prêt à simplifier votre technologie ?</h2>
                <p>Un projet, un besoin d'audit ou une question sur nos solutions IA/IT ?</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-white">Demandez un devis gratuit →</a>
                    <a href="tel:+33123456789" class="btn btn-outline-light">📞 Nous appeler</a>
                </div>
            </div>
        </div>
    </section>
</main>



<?php include 'footer.php'; ?>