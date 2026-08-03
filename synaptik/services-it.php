<?php
require_once 'includes/config.php';

$page_title = 'Services IT - Support, conseil, infrastructure, hébergement';
$page_description = 'Services informatiques classiques : dépannage réactif, conseil stratégique, administration systèmes, hébergement web sécurisé. Support 24/7.';
include 'header.php';
?>

<main>
    <!-- Hero spécifique IT -->
    <section class="hero-it">
        <div class="container text-center">
            <h1>Une Infrastructure <span class="gradient-text">Solide et Sereine</span></h1>
            <p class="hero-subtitle">Des services informatiques traditionnels mais exigeants : support réactif, conseil stratégique, administration système et hébergement haute disponibilité.</p>
        </div>
    </section>

    <!-- 01 - Dépannage & Support -->
    <section class="service-block-it">
        <div class="container">
            <div class="service-grid-it">
                <div class="service-content-it">
                    <span class="service-tag-it">🔧 Support & Dépannage</span>
                    <h2>Assistance Technique Réactive</h2>
                    <p>Résolution rapide des problèmes matériels et logiciels pour vos postes de travail et serveurs. Une équipe dédiée pour minimiser vos interruptions.</p>
                    <ul class="service-list-it">
                        <li>🖥️ Dépannage à distance et sur site</li>
                        <li>⚡ Intervention sous 4h ouvrées</li>
                        <li>📦 Gestion du parc matériel</li>
                        <li>🔄 Maintenance préventive</li>
                    </ul>
                    <a href="contact.php" class="service-link-it">Besoin d'assistance ? →</a>
                </div>
                <div class="service-visual-it">
                    <div class="circuit-bg-it">
                        <img src="assets/images/it-support.png" alt="Support et dépannage IT" class="service-img-it">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 02 - Conseils Stratégiques -->
    <section class="service-block-it alt">
        <div class="container">
            <div class="service-grid-it reverse">
                <div class="service-content-it">
                    <span class="service-tag-it">📊 Audit & Conseil</span>
                    <h2>Audit et Conseil IT</h2>
                    <p>Optimisez votre infrastructure, planifiez vos investissements et sécurisez vos données. Nous vous accompagnons dans vos décisions stratégiques.</p>
                    <ul class="service-list-it">
                        <li>📈 Audit complet de votre SI</li>
                        <li>🛡️ Sécurité et conformité (RGPD, ISO)</li>
                        <li>💡 Plan de transformation IT</li>
                        <li>📋 Roadmap budgétaire personnalisée</li>
                    </ul>
                    <a href="contact.php" class="service-link-it">Demander un audit →</a>
                </div>
                <div class="service-visual-it">
                    <div class="circuit-bg-it">
                        <img src="assets/images/it-conseil.png" alt="Audit et conseil IT" class="service-img-it">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 03 - Admin Systèmes & Réseaux -->
    <section class="service-block-it">
        <div class="container">
            <div class="service-grid-it">
                <div class="service-content-it">
                    <span class="service-tag-it">🏗️ Infrastructure</span>
                    <h2>Gestion d'Infrastructure</h2>
                    <p>Administration complète de vos serveurs (Windows/Linux), supervision, sauvegarde, et gestion des réseaux d'entreprise.</p>
                    <ul class="service-list-it">
                        <li>🖧 Administration serveurs (on-prem/cloud)</li>
                        <li>🌐 Gestion réseau (LAN, VLAN, VPN)</li>
                        <li>💾 Sauvegarde automatisée et DRP</li>
                        <li>📊 Supervision 24/7 et alerting</li>
                    </ul>
                    <a href="contact.php" class="service-link-it">Auditer mon infrastructure →</a>
                </div>
                <div class="service-visual-it">
                    <div class="circuit-bg-it">
                        <img src="assets/images/it-infrastructure.png" alt="Administration systèmes et réseaux" class="service-img-it">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 04 - Hébergement Web -->
    <section class="service-block-it alt">
        <div class="container">
            <div class="service-grid-it reverse">
                <div class="service-content-it">
                    <span class="service-tag-it">☁️ Hébergement</span>
                    <h2>Hébergement Performant et Sécurisé</h2>
                    <p>Nous hébergeons vos sites internet et emails sur nos serveurs haute disponibilité. Service de gestion, sauvegardes et support inclus.</p>
                    <ul class="service-list-it">
                        <li>🚀 Hébergement Web ultra-rapide</li>
                        <li>📧 Messagerie professionnelle sécurisée</li>
                        <li>🔒 Certificats SSL et firewall</li>
                        <li>📞 Support technique inclus</li>
                    </ul>
                    <a href="contact.php" class="service-link-it">Découvrir nos offres →</a>
                </div>
                <div class="service-visual-it">
                    <div class="circuit-bg-it">
                        <img src="assets/images/it-hebergement.png" alt="Hébergement web sécurisé" class="service-img-it">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA final spécifique IT -->
    <section class="cta-it">
        <div class="container text-center">
            <h2>Vous avez besoin d'une infrastructure fiable ?</h2>
            <p>Un audit, un dépannage urgent ou un conseil stratégique ? Contactez-nous.</p>
            <a href="contact.php" class="btn-it">Discuter de mon projet IT →</a>
        </div>
    </section>
</main>


<?php include 'footer.php'; ?>