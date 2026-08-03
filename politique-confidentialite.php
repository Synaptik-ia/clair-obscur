<?php
// politique-confidentialite.php - Politique de confidentialité (Version Québec/Canada)

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

$page_title = "Politique de confidentialité - Clair-Obscur";
$page_description = "Politique de confidentialité conforme à la Loi 25 du Québec et aux lois canadiennes sur la protection des données personnelles.";

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h3 mb-0"><i class="fas fa-shield-alt"></i> Politique de confidentialité</h1>
                    <p class="mb-0 small">Conforme à la Loi 25 (Québec) et à la LPRPDE (Canada)</p>
                </div>
                <div class="card-body">
                    <p class="text-muted">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                    <p>
                    <div class=" alert-info">
                        <i class="fas fa-maple-leaf"></i> <strong>Protection des renseignements personnels</strong><br>
                        La présente politique décrit comment Clair-Obscur collecte, utilise, divulgue et protège vos renseignements personnels, conformément à la <strong>Loi sur la protection des renseignements personnels dans le secteur privé (Loi 25)</strong> et à la <strong>Loi sur la protection des renseignements personnels et les documents électroniques (LPRPDE)</strong>.
                    </div>
                    </p>
                    <hr>
                    
                    <h4>1. Collecte des renseignements personnels</h4>
                    <p>Nous collectons les renseignements suivants :</p>
                    <ul>
                        <li><strong>Informations d'identification :</strong> nom, prénom, adresse email, adresse postale, numéro de téléphone</li>
                        <li><strong>Informations de compte :</strong> identifiant, mot de passe (chiffré)</li>
                        <li><strong>Informations de transaction :</strong> historique d'achats, commandes, factures</li>
                        <li><strong>Informations techniques :</strong> adresse IP, type de navigateur, pages visitées, durée de visite</li>
                        <li><strong>Préférences :</strong> langue, paramètres de notification</li>
                    </ul>
                    <p>Ces renseignements sont collectés via :</p>
                    <ul>
                        <li>Formulaire d'inscription</li>
                        <li>Formulaire de commande</li>
                        <li>Formulaire de contact</li>
                        <li>Cookies et technologies similaires</li>
                        <li>Newsletter</li>
                    </ul>
                    
                    <h4>2. Finalités de la collecte</h4>
                    <p>Vos renseignements sont utilisés pour :</p>
                    <ul>
                        <li>Créer et gérer votre compte client</li>
                        <li>Traiter vos commandes et assurer la livraison</li>
                        <li>Fournir le service client et le support technique</li>
                        <li>Vous informer des nouveautés et offres (avec votre consentement)</li>
                        <li>Améliorer notre site et nos services (analytiques)</li>
                        <li>Respecter nos obligations légales et fiscales</li>
                        <li>Prévenir la fraude et sécuriser les transactions</li>
                    </ul>
                    
                    <h4>3. Base légale du traitement (consentement)</h4>
                    <p>Conformément à la Loi 25, nous recueillons votre consentement avant toute collecte, utilisation ou communication de vos renseignements personnels. Vous pouvez retirer votre consentement à tout moment en nous contactant.</p>
                    
                    <h4>4. Durée de conservation</h4>
                    <p>Vos renseignements personnels sont conservés :</p>
                    <ul>
                        <li><strong>Compte client actif :</strong> Durée de vie du compte</li>
                        <li><strong>Compte inactif :</strong> 3 ans après la dernière connexion</li>
                        <li><strong>Données de commande :</strong> 7 ans (obligations fiscales)</li>
                        <li><strong>Données de navigation :</strong> 13 mois maximum</li>
                        <li><strong>Demandes de contact :</strong> 3 ans</li>
                    </ul>
                    
                    <h4>5. Partage des renseignements</h4>
                    <p>Nous ne vendons pas vos renseignements personnels. Ils peuvent être partagés avec :</p>
                    <ul>
                        <li><strong>PayPal :</strong> traitement des paiements (leurs propres politiques de confidentialité s'appliquent)</li>
                        <li><strong>Partenaires de livraison :</strong> pour l'acheminement des colis (nom, adresse)</li>
                        <li><strong>Autorités légales :</strong> si requis par la loi</li>
                        <li><strong>Prestataires techniques :</strong> hébergement, maintenance</li>
                    </ul>
                    <p>Tous nos partenaires sont tenus au respect de la confidentialité des données.</p>
                    
                    <h4>6. Transfert hors du Québec/Canada</h4>
                    <p>Vos données peuvent être transférées vers la France (siège social de Clair-Obscur). Des mesures de protection contractuelles sont en place pour assurer un niveau de protection équivalent à celui du Québec.</p>
                    
                    <h4>7. Sécurité des données</h4>
                    <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles :</p>
                    <ul>
                        <li>Chiffrement SSL/TLS pour les échanges de données</li>
                        <li>Mots de passe hachés et salés</li>
                        <li>Accès restreint aux données personnelles</li>
                        <li>Journalisation des accès</li>
                        <li>Sauvegardes chiffrées</li>
                        <li>Protection contre les injections SQL et XSS</li>
                    </ul>
                    
                    <h4>8. Vos droits (Loi 25 Québec)</h4>
                    <p>Conformément à la Loi 25, vous disposez des droits suivants :</p>
                    <ul>
                        <li><strong>Droit d'accès :</strong> consulter vos renseignements personnels</li>
                        <li><strong>Droit de rectification :</strong> corriger vos informations inexactes</li>
                        <li><strong>Droit de suppression :</strong> demander l'effacement de vos données</li>
                        <li><strong>Droit d'opposition :</strong> refuser certains traitements</li>
                        <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
                        <li><strong>Droit de retirer votre consentement :</strong> à tout moment</li>
                    </ul>
                    <p>Pour exercer ces droits, contactez : <strong><?php echo ADMIN_EMAIL; ?></strong></p>
                    
                    <h4>9. Délais de réponse</h4>
                    <p>Nous nous engageons à répondre à vos demandes dans un délai maximum de <strong>30 jours</strong> (conformément à la Loi 25).</p>
                    
                    <h4>10. Cookies et technologies similaires</h4>
                    <p>Notre site utilise des cookies pour améliorer votre expérience. Vous pouvez gérer vos préférences via notre bandeau cookies ou les paramètres de votre navigateur.</p>
                    
                    <h4>11. Liens externes</h4>
                    <p>Notre site peut contenir des liens vers des sites tiers. Nous ne sommes pas responsables de leurs pratiques en matière de confidentialité. Nous vous invitons à consulter leurs politiques respectives.</p>
                    
                    <h4>12. Modifications de la politique</h4>
                    <p>Nous pouvons modifier cette politique de confidentialité. Toute modification sera publiée sur cette page avec la date de mise à jour. En cas de modification importante, vous en serez informé par email.</p>
                    
                    <h4>13. Responsable de la protection des renseignements personnels</h4>
                    <p>
                        <strong>Courriel :</strong> <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a><br>
                    </p>
                    
                    <h4>14. Plainte à l'OPC</h4>
                    <p>En cas de non-respect de vos droits, vous avez le droit de déposer une plainte auprès de l'<strong>Office de la protection du consommateur du Québec</strong> :</p>
                    <ul>
                        <li>Site web : <a href="https://www.opc.gouv.qc.ca" target="_blank">www.opc.gouv.qc.ca</a></li>
                        <li>Téléphone : 514-253-6556 / 1-888-672-2556</li>
                        <li>Adresse : 400, boulevard Jean-Lesage, Québec (Québec) G1K 8W4</li>
                    </ul>
                    
                    <hr>
                    <p>
                    <div class=" alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Engagement de confidentialité</strong><br>
                        Nous nous engageons à respecter la confidentialité de vos renseignements personnels et à ne les utiliser que dans le cadre défini par la présente politique.
                    </div>
                    </p>
                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                        <a href="<?php echo SITE_URL; ?>mentions-legales.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-gavel"></i> Mentions légales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>