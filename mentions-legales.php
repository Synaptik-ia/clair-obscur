<?php
// mentions-legales.php - Mentions légales (Version Québec/Canada)

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

$page_title = "Mentions légales - Clair-Obscur (Version Québec)";
$page_description = "Mentions légales conformes à la législation du Québec et du Canada pour la maison d'édition Clair-Obscur.";

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h3 mb-0"><i class="fas fa-gavel"></i> Mentions légales</h1>
                    <p class="mb-0 small">Version conforme à la législation du Québec et du Canada</p>
                </div>
                <div class="card-body">
                    <p class="text-muted">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-maple-leaf"></i> <strong>Juridiction applicable</strong><br>
                        Les présentes mentions légales sont rédigées conformément à la <strong>Loi sur la protection du consommateur (L.R.Q., c. P-40.1)</strong>, à la <strong>Loi sur la protection des renseignements personnels dans le secteur privé (Loi 25)</strong> et à la <strong>Loi canadienne anti-pourriel (LCAP)</strong>.
                    </div>
                    
                    <hr>
                  
                    <!-- Section 4 : Protection des renseignements personnels (Loi 25 Québec) -->
                    <h4><i class="fas fa-lock"></i> 1. Protection des renseignements personnels (Loi 25)</h4>
                    <p>Conformément à la <strong>Loi sur la protection des renseignements personnels dans le secteur privé</strong> (L.R.Q., c. P-39.1), dite <strong>Loi 25</strong> :</p>
                    <ul>
                        <li>Les renseignements personnels collectés sont nécessaires au traitement des commandes, à la livraison des produits et à la gestion de la relation client.</li>
                        <li>Les catégories de renseignements collectés incluent : nom, prénom, adresse postale, adresse courriel, numéro de téléphone, historique d'achats.</li>
                        <li>Vous disposez d'un droit d'accès, de rectification et de suppression de vos renseignements personnels.</li>
                        <li>Vous pouvez retirer votre consentement à tout moment en nous contactant.</li>
                        <li>Vos données ne sont pas vendues à des tiers ni utilisées à des fins de profilage non consenti.</li>
                        <li>Les renseignements personnels sont conservés pour une durée de <strong>7 ans</strong> après la dernière interaction (exigences fiscales).</li>
                    </ul>
                    <p>
                        <strong>Responsable de la protection des renseignements personnels :</strong><br>
                        Courriel : <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a><br>
                        Délai de réponse : 30 jours maximum
                    </p>
                    
                    <!-- Section 5 : Responsable de la protection des données -->
                    <h4><i class="fas fa-user-shield"></i> 2. Responsable de la protection des données</h4>
                    <p>
                        <strong>Courriel :</strong> <a href="mailto:contact@clair-obscur.com">contact@clair-obscur.com</a><br>
                        Pour toute question relative à la protection de vos données personnelles, veuillez contacter notre DPO.
                    </p>
                    
                    <!-- Section 6 : Cookies -->
                    <h4><i class="fas fa-cookie-bite"></i> 3. Cookies</h4>
                    <p>Le site utilise des cookies à des fins :</p>
                    <ul>
                        <li><strong>Cookies techniques :</strong> Nécessaires au fonctionnement du site (panier, authentification). Durée de vie : session.</li>
                        <li><strong>Cookies statistiques :</strong> Mesure d'audience (Google Analytics anonymisé). Durée de vie : 13 mois.</li>
                        <li><strong>Cookies de préférences :</strong> Mémorisation de vos choix (langue, affichage). Durée de vie : 12 mois.</li>
                    </ul>
                    <p>Conformément à la directive sur les cookies et à la Loi 25, vous pouvez à tout moment paramétrer votre navigateur pour refuser les cookies ou utiliser notre outil de gestion des cookies.</p>
                    
                    <!-- Section 7 : Loi canadienne anti-pourriel (LCAP) -->
                    <h4><i class="fas fa-envelope"></i> 4. Loi canadienne anti-pourriel (LCAP)</h4>
                    <p>Conformément à la <strong>Loi canadienne anti-pourriel (L.C. 2010, ch. 23)</strong> :</p>
                    <ul>
                        <li>Nous n'envoyons de communications électroniques commerciales qu'avec votre consentement explicite.</li>
                        <li>Chaque courriel contient un lien de désabonnement visible et fonctionnel.</li>
                        <li>Vous pouvez vous désinscrire à tout moment de nos listes d'envoi.</li>
                        <li>Nous conservons la preuve de votre consentement.</li>
                    </ul>
                    <p><strong>Pour vous désabonner :</strong> <a href="<?php echo SITE_URL; ?>desinscription.php">Cliquez ici</a> ou envoyez un courriel à <a href="mailto:contact@clair-obscur.com">unsubscribe@clair-obscur.com</a></p>
                    
                    <!-- Section 8 : Propriété intellectuelle -->
                    <h4><i class="fas fa-copyright"></i> 5. Propriété intellectuelle</h4>
                    <p>Tous les contenus présents sur le site Clair-Obscur (textes, images, logos, vidéos, graphismes, icônes) sont protégés par le <strong>droit d'auteur canadien</strong> et le <strong>Code de la propriété intellectuelle français</strong>. Toute reproduction, représentation, modification, publication, adaptation totale ou partielle des éléments du site est interdite sans autorisation écrite préalable.</p>
                    
                    <!-- Section 9 : Limitation de responsabilité -->
                    <h4><i class="fas fa-exclamation-triangle"></i> 6. Limitation de responsabilité</h4>
                    <p>Clair-Obscur s'efforce d'assurer l'exactitude des informations publiées. Toutefois, nous ne pouvons garantir l'exhaustivité ou l'absence de modification par des tiers. La responsabilité de Clair-Obscur ne peut être engagée en cas d'indisponibilité du site, de perte de données, d'utilisation frauduleuse du compte client, ou de dommages indirects. Dans tous les cas, la responsabilité est limitée au montant payé par le client pour la commande concernée.</p>
                    
                    <!-- Section 10 : Litiges -->
                    <h4><i class="fas fa-gavel"></i> 7. Litiges et loi applicable</h4>
                    <p>Les présentes mentions légales sont régies par les lois de la <strong>province de Québec</strong> et du <strong>Canada</strong>. En cas de litige, une solution amiable sera recherchée avant toute action judiciaire.</p>
                    <p><strong>Pour les consommateurs québécois :</strong></p>
                    <ul>
                        <li><strong>Office de la protection du consommateur (OPC) :</strong> <a href="https://www.opc.gouv.qc.ca" target="_blank">www.opc.gouv.qc.ca</a> - Tél. : 514-253-6556 / 1-888-672-2556</li>
                        <li><strong>Division des petites créances :</strong> Pour les litiges jusqu'à 15 000 $ CAD</li>
                        <li><strong>Médiateur :</strong> [Nom de l'organisme de médiation]</li>
                    </ul>
                    
                    <!-- Section 11 : Médiation -->
                    <h4><i class="fas fa-handshake"></i> 8. Médiation</h4>
                    <p>Conformément au Code civil du Québec, un processus de médiation peut être proposé avant toute action judiciaire. En cas d'échec de la médiation, les tribunaux de la province de Québec sont compétents.</p>
                    
                    <!-- Section 12 : Contact -->
                    <h4><i class="fas fa-phone-alt"></i> 9. Nous contacter</h4>
                    <p>
                        Pour toute question relative aux mentions légales, à la protection des données, ou pour exercer vos droits :<br>
                        <strong>Courriel :</strong> <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a><br>
                        <strong>Formulaire de contact :</strong> <a href="<?php echo SITE_URL; ?>contact/">Cliquez ici</a><br>
                    </p>
                    
                    <hr>
                    <p>
                    <div class=" alert-warning">
                        <i class="fas fa-info-circle"></i> <strong>Information importante</strong><br>
                        Nous respectons les lois applicables du Québec et du Canada. En cas de conflit de lois, la législation québécoise prévaudra pour les consommateurs.
                    </div>
                    </p>
                    <p>     
                    <div class=" alert-info">
                        <i class="fas fa-shield-alt"></i> <strong>Confidentialité et sécurité</strong><br>
                        Notre site utilise un certificat SSL/TLS pour sécuriser les échanges de données. Les paiements sont traités par PayPal, qui ne nous transmet aucune information bancaire sensible.
                    </div>
                    </p>
                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                        <a href="<?php echo SITE_URL; ?>cgv/" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-file-contract"></i> Voir nos CGV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>