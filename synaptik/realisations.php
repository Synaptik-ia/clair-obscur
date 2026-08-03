<?php
require_once 'includes/config.php';

$page_title = 'Nos Réalisations - Projets et études de cas';
$page_description = 'Découvrez les projets réalisés par Synaptik IA Solutions : automatisation du tri des courriels, application de comptabilité web Python, jeu marketing Roue de la Fortune. Cas clients et témoignages.';
include 'header.php';
?>

<main>
    <!-- Introduction Réalisations -->
    <section class="section">
        <div class="container text-center">
            <span class="section-badge">Notre portfolio</span>
            <h1>Projets <span class="gradient-text">Récents</span></h1>
            <div class="intro-text">
                <p>Découvrez les solutions que nous avons développées pour nos clients. Des workflows d'automatisation IA aux applications web métier sur mesure, en passant par des outils marketing interactifs, nos réalisations parlent d'elles-mêmes.</p>
            </div>
        </div>
    </section>

    <!-- Grille de projets -->
    <section class="section section-bg-light">
        <div class="container">
            <div class="grid-portfolio">
                
                <!-- PROJET 1 : Automatisation du tri des courriels -->
                <div class="portfolio-card">
                    <div class="card-image-placeholder">
                        <img src="assets/images/realisation-workflow.png" alt="Workflow d'automatisation du tri des courriels - Synaptik IA" class="portfolio-img">
                    </div>
                    <div class="card-badge">🤖 Automatisation IA</div>
                    <h3 class="project-title project-title-workflow">Automatisation intelligente du tri des courriels</h3>
                    <p class="card-excerpt">Dans le cadre de ce projet, nous avons conçu et déployé un système automatisé de gestion des courriels permettant de trier, analyser et classer automatiquement les messages entrants selon leur nature et leur niveau de priorité.</p>
                    
                    <div class="card-excerpt-more">
                        <p>Grâce à l'intelligence artificielle, les courriels sont identifiés dès leur réception et dirigés vers les bonnes catégories : demandes clients, factures, infolettres, notifications système, messages publicitaires ou communications nécessitant une intervention humaine. Les messages prioritaires sont mis en évidence tandis que les courriels à faible valeur ajoutée sont automatiquement organisés pour réduire l'encombrement de la boîte de réception.</p>
                        <p>Cette solution permet aux équipes de gagner un temps considérable, d'améliorer leur réactivité et de réduire les risques d'oublis ou de perte d'information importante. L'ensemble du processus s'intègre naturellement aux outils existants, sans modifier les habitudes de travail des utilisateurs.</p>
                        <p>Afin de garantir un contrôle total des données et une intégration optimale dans l'environnement informatique du client, l'agent d'intelligence artificielle a été déployé localement sur une infrastructure <strong>Docker</strong>. Cette approche offre une meilleure maîtrise de la sécurité, une grande flexibilité d'évolution et une indépendance vis-à-vis des plateformes cloud externes, tout en conservant les avantages de l'automatisation intelligente.</p>
                        <p>Le résultat est une boîte de réception plus organisée, une meilleure productivité et une gestion des communications plus efficace au quotidien.</p>
                    </div>

                    <div class="card-tech">
                        <span class="tech-label">Technologies utilisées :</span>
                        <div class="tech-tags">
                            <span>Intelligence artificielle</span>
                            <span>Automatisation</span>
                            <span>Docker</span>
                            <span>Déploiement local</span>
                            <span>Gmail</span>
                            <span>Workflows</span>
                        </div>
                    </div>
                    
                    <div class="portfolio-tags">
                        <span>IA</span>
                        <span>Automatisation</span>
                        <span>Email</span>
                        <span>Workflow</span>
                        <span>Docker</span>
                    </div>
                    
                    <div class="card-stats">
                        <div class="stat-mini">
                            <span class="stat-mini-value">50+</span>
                            <span class="stat-mini-label">Emails traités/h</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">95%</span>
                            <span class="stat-mini-label">Automatisation</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">24/7</span>
                            <span class="stat-mini-label">Disponibilité</span>
                        </div>
                    </div>
                    
                    <div class="card-features">
                        <span>✅ Tri intelligent des emails par catégorie</span>
                        <span>✅ Identification des messages prioritaires</span>
                        <span>✅ Intégration Gmail & outils existants</span>
                        <span>✅ Déploiement local sur Docker</span>
                        <span>✅ Réduction de l'encombrement de la boîte réception</span>
                    </div>

                </div>

                <!-- PROJET 2 : Application de comptabilité web Python -->
                <div class="portfolio-card">
                    <div class="card-image-placeholder">
                        <img src="assets/images/realisation-dashboard.png" alt="Application de comptabilité web Python - Synaptik IA" class="portfolio-img">
                    </div>
                    <div class="card-badge">🐍 Python Web App</div>
                    <h3 class="project-title project-title-dashboard">Application de comptabilité web sur mesure en Python</h3>
                    <p class="card-excerpt">Une application de comptabilité légère et intuitive permettant aux entrepreneurs et aux petites entreprises de centraliser leur gestion financière au sein d'une interface web moderne.</p>
                    
                    <div class="card-excerpt-more">
                        <p>Dans le cadre de ce projet, nous avons conçu et développé une application de comptabilité légère et intuitive permettant aux entrepreneurs et aux petites entreprises de centraliser leur gestion financière au sein d'une interface web moderne.</p>
                        <p>Développée en Python et accessible directement depuis un navigateur, cette solution offre un environnement simple et efficace pour gérer les factures clients, les factures fournisseurs, les comptes bancaires et les principaux indicateurs financiers de l'entreprise.</p>
                        <p>L'application fournit un tableau de bord centralisé permettant de visualiser en temps réel les montants à encaisser, les factures à payer, les dépenses, le chiffre d'affaires et la trésorerie globale. L'ensemble des informations est présenté de manière claire afin d'aider les dirigeants à prendre rapidement les bonnes décisions sans avoir à naviguer dans des outils complexes.</p>
                        <p>L'un des principaux objectifs du projet était de proposer une solution personnalisée, adaptée aux besoins réels de l'entreprise, tout en conservant une architecture évolutive capable d'intégrer de futures fonctionnalités telles que l'automatisation des processus, l'analyse financière assistée par intelligence artificielle ou la synchronisation avec d'autres outils de gestion.</p>
                    </div>

                    <div class="card-tech">
                        <span class="tech-label">Technologies utilisées :</span>
                        <div class="tech-tags">
                            <span>Python</span>
                            <span>Web App</span>
                            <span>Base de données</span>
                            <span>Dashboard</span>
                            <span>Architecture évolutive</span>
                        </div>
                    </div>
                    
                    <div class="portfolio-tags">
                        <span>Python</span>
                        <span>Web App</span>
                        <span>Comptabilité</span>
                        <span>Sur mesure</span>
                    </div>
                    
                    <div class="card-stats">
                        <div class="stat-mini">
                            <span class="stat-mini-value">100%</span>
                            <span class="stat-mini-label">Personnalisée</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">24/7</span>
                            <span class="stat-mini-label">Accès web</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">✓</span>
                            <span class="stat-mini-label">Évolutive</span>
                        </div>
                    </div>
                    
                    <div class="card-features">
                        <span>✅ Gestion des factures clients et fournisseurs</span>
                        <span>✅ Suivi des paiements et des échéances</span>
                        <span>✅ Gestion des comptes bancaires</span>
                        <span>✅ Tableau de bord financier en temps réel</span>
                        <span>✅ Architecture évolutive compatible IA</span>
                    </div>

                </div>

                <!-- PROJET 3 : Jeu marketing Roue de la Fortune -->
                <div class="portfolio-card">
                    <div class="card-image-placeholder">
                        <img src="assets/images/realisation-wheel.png" alt="Jeu marketing Roue de la Fortune - Synaptik IA" class="portfolio-img">
                    </div>
                    <div class="card-badge">🎡 Marketing Interactif</div>
                    <h3 class="project-title project-title-wheel">Jeu marketing interactif de la roue de la fortune</h3>
                    <p class="card-excerpt">Un jeu promotionnel interactif basé sur le principe de la roue de la fortune, permettant aux entreprises de dynamiser leur présence en ligne et d'augmenter l'engagement de leurs visiteurs.</p>
                    
                    <div class="card-excerpt-more">
                        <p>Pour aider les entreprises à dynamiser leur présence en ligne et à augmenter l'engagement de leurs visiteurs, nous avons développé un jeu promotionnel interactif basé sur le principe de la roue de la fortune.</p>
                        <p>Accessible directement depuis un navigateur web, cette application permet aux utilisateurs de participer à un tirage instantané offrant des remises, cadeaux, coupons promotionnels ou avantages exclusifs. L'expérience est conçue pour être simple, ludique et optimisée pour maximiser les conversions tout en renforçant l'image de marque de l'entreprise.</p>
                        <p>Développée en HTML, CSS, JavaScript et PHP, la solution permet de personnaliser entièrement les visuels, les probabilités de gain, les récompenses ainsi que les mécanismes de collecte de données. Les participants peuvent être invités à s'inscrire, laisser leurs coordonnées ou rejoindre une campagne marketing avant de faire tourner la roue.</p>
                        <p>L'objectif du projet était de créer un outil promotionnel performant permettant de générer des prospects qualifiés, d'augmenter les inscriptions à une infolettre et de stimuler l'interaction avec la marque tout en offrant une expérience utilisateur moderne et engageante.</p>
                        <p>Cette réalisation démontre notre capacité à développer des solutions web marketing sur mesure alliant design, expérience utilisateur et performance commerciale.</p>
                    </div>

                    <div class="card-tech">
                        <span class="tech-label">Technologies utilisées :</span>
                        <div class="tech-tags">
                            <span>HTML5</span>
                            <span>CSS3</span>
                            <span>JavaScript</span>
                            <span>PHP</span>
                            <span>Base de données</span>
                        </div>
                    </div>
                    
                    <div class="portfolio-tags">
                        <span>HTML5</span>
                        <span>CSS3</span>
                        <span>JavaScript</span>
                        <span>PHP</span>
                        <span>Marketing</span>
                    </div>
                    
                    <div class="card-stats">
                        <div class="stat-mini">
                            <span class="stat-mini-value">100%</span>
                            <span class="stat-mini-label">Personnalisable</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">📱</span>
                            <span class="stat-mini-label">Responsive</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-value">🎯</span>
                            <span class="stat-mini-label">Prospects qualifiés</span>
                        </div>
                    </div>
                    
                    <div class="card-features">
                        <span>✅ Roue interactive et personnalisable</span>
                        <span>✅ Attribution automatique des récompenses</span>
                        <span>✅ Gestion des probabilités de gain</span>
                        <span>✅ Collecte de prospects & formulaires d'inscription</span>
                        <span>✅ Compatible mobile, tablette et ordinateur</span>
                        <span>✅ Administration des campagnes promotionnelles</span>
                    </div>

                </div>

            </div>

            <!-- Note supplémentaire -->
            <div class="more-note">
                <p>✨ D'autres études de cas arrivent bientôt. <a href="contact.php">Vous avez un projet similaire ? Parlons-en.</a></p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-portfolio">
        <div class="container text-center">
            <h2>Vous avez un projet en tête ?</h2>
            <p>Discutons de vos besoins et construisons ensemble la solution idéale.</p>
            <a href="contact.php" class="btn-cta-portfolio">Demander un audit gratuit →</a>
        </div>
    </section>
</main>


<?php include 'footer.php'; ?>