<?php
require_once 'includes/config.php';

$page_title = 'Notre Vision - Approche et méthode';
$page_description = 'Découvrez la vision de Synaptik IA Solutions : une technologie accessible, performante et sans jargon. Audit, conseil et solutions IA/IT clés en main.';
include 'header.php';
?>

<main>
    <!-- Hero Vision -->
    <section class="hero-vision">
        <div class="container text-center">
            <span class="badge-vision">Notre ADN</span>
            <h1>Une technologie <span class="gradient-text">accessible et performante</span></h1>
            <p class="hero-vision-text">Chez Synaptik IA Solutions, nous ne croyons pas au jargon technique ni aux solutions hors-sol. Nous construisons des ponts entre l'innovation IA et les besoins réels des entreprises.</p>
        </div>
    </section>

    <!-- Philosophie centrale -->
    <section class="philosophy">
        <div class="container">
            <div class="philosophy-grid">
                <div class="philosophy-card">
                    <div class="philo-icon">🎯</div>
                    <h3>Consistance avant tout</h3>
                    <p>Chaque solution que nous proposons répond à un besoin opérationnel identifié. Pas de technologie pour la technologie.</p>
                </div>
                <div class="philosophy-card">
                    <div class="philo-icon">🧩</div>
                    <h3>Approche modulaire</h3>
                    <p>Nos services s'adaptent à votre rythme : commencez par un audit, déployez un agent IA, ou confiez-nous toute votre infrastructure.</p>
                </div>
                <div class="philosophy-card">
                    <div class="philo-icon">🔒</div>
                    <h3>Sécurité & robustesse</h3>
                    <p>L'IA ne doit jamais compromettre la stabilité. Nous veillons à des infrastructures solides avant toute innovation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Notre Vision détaillée -->
    <section class="vision-detail">
        <div class="container">
            <div class="vision-block-single">
                <div class="vision-text-col">
                    <h2>Notre Vision de la Technologie</h2>
                    <p class="lead">L'intelligence artificielle ne remplace pas l'humain, elle le libère des tâches répétitives pour qu'il se concentre sur ce qui a du sens.</p>
                    <p>Nous vivons une époque où l'IA peut sembler complexe, coûteuse ou réservée aux grandes entreprises. Notre conviction est inverse : <strong>la technologie doit être un levier accessible, transparent et parfaitement intégré</strong> à vos processus existants.</p>
                    <p>C'est pourquoi nous avons construit une offre hybride, qui marie l'innovation IA avec la fiabilité des services informatiques traditionnels. Vous ne choisissez pas entre modernité et stabilité – vous bénéficiez des deux.</p>
                </div>
                <div class="vision-image-col">
                    <img src="assets/images/vision-detail.png" alt="Notre vision de la technologie" class="vision-detail-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Notre Méthode (processus) -->
    <section class="method">
        <div class="container text-center">
            <h2>Notre Méthode : <span class="gradient-text">Clé en main, sans surprise</span></h2>
            <p class="method-sub">Une approche en 4 temps, transparente et itérative.</p>
            <div class="method-steps">
                <div class="step">
                    <div class="step-number">01</div>
                    <h3>Audit & Découverte</h3>
                    <p>Nous analysons vos besoins, votre infrastructure existante et vos objectifs. Un diagnostic clair sans engagement.</p>
                </div>
                <div class="step">
                    <div class="step-number">02</div>
                    <h3>Stratégie & Architecture</h3>
                    <p>Nous concevons une solution modulaire, adaptée à votre budget et à votre calendrier. Vous validez chaque étape.</p>
                </div>
                <div class="step">
                    <div class="step-number">03</div>
                    <h3>Déploiement & Intégration</h3>
                    <p>Nos équipes techniques installent, configurent et testent. L'IA s'intègre discrètement à vos outils existants.</p>
                </div>
                <div class="step">
                    <div class="step-number">04</div>
                    <h3>Suivi & Évolution</h3>
                    <p>Nous restons à vos côtés : support, maintenance, mises à jour et montée en compétence de vos équipes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Engagement & Valeurs -->
    <section class="values">
        <div class="container">
            <div class="values-header text-center">
                <h2>Ce qui nous anime</h2>
                <p>Quatre principes qui guident chacune de nos interventions.</p>
            </div>
            <div class="values-grid">
                <div class="value-item">
                    <span class="value-icon">🔍</span>
                    <h4>Transparence totale</h4>
                    <p>Pas de facturation obscure. Un devis clair, des rapports réguliers, une communication directe.</p>
                </div>
                <div class="value-item">
                    <span class="value-icon">⚡</span>
                    <h4>Réactivité</h4>
                    <p>Un support technique joignable, des délais tenus, une vraie proximité.</p>
                </div>
                <div class="value-item">
                    <span class="value-icon">🧠</span>
                    <h4>Veille technologique</h4>
                    <p>Nous testons et sélectionnons les meilleures IA pour vous proposer l'essentiel, pas le superflu.</p>
                </div>
                <div class="value-item">
                    <span class="value-icon">🤝</span>
                    <h4>Relation durable</h4>
                    <p>Nous construisons des partenariats sur le long terme, avec une vraie compréhension de votre métier.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA final -->
    <section class="cta-vision">
        <div class="container text-center">
            <h2>Convaincu par notre approche ?</h2>
            <p>Échangeons sur vos besoins sans engagement.</p>
            <a href="contact.php" class="btn-vision">Discuter de mon projet →</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>