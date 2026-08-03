<?php
// cgv/index.php - Conditions Générales de Vente (Version Québec/Canada)

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Conditions Générales de Vente - Clair-Obscur (Version Québec)";
$page_description = "Consultez les conditions générales de vente de la maison d'édition Clair-Obscur, conformes à la législation québécoise et canadienne.";

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h3 mb-0"><i class="fas fa-file-contract"></i> Conditions Générales de Vente</h1>
                    <p class="mb-0 small">Version conforme à la législation du Québec et du Canada</p>
                </div>
                <div class="card-body">
                    <p class="text-muted">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-maple-leaf"></i> <strong>Présentes CGV - Juridiction du Québec</strong><br>
                        Les présentes conditions générales de vente sont rédigées conformément à la <strong>Loi sur la protection du consommateur (L.R.Q., c. P-40.1)</strong> et à la <strong>Loi sur la protection des renseignements personnels dans le secteur privé (Loi 25)</strong>.
                    </div>
                    
                    <hr>
                    
                    <h4>Article 1 - Champ d'application</h4>
                    <p>Les présentes conditions générales de vente (CGV) s'appliquent à toutes les commandes passées sur le site internet Clair-Obscur (<a href="<?php echo SITE_URL; ?>"><?php echo SITE_URL; ?></a>) par des clients adultes résidant au Canada, et plus particulièrement au Québec. En passant commande, le client reconnaît avoir pris connaissance et accepté sans réserve les présentes CGV.</p>
                    
                    <h4>Article 2 - Identité du vendeur</h4>
                    <p>
                        <strong>Clair-Obscur Éditions</strong><br>
                        Adresse : 12 Rue des Libraires, 75001 Paris, France<br>
                        Pour les clients canadiens, veuillez noter que Clair-Obscur Éditions est une entreprise établie en France. Les lois applicables sont celles de la province de Québec et du Canada, conformément aux règles de conflit de lois.
                    </p>
                    
                    <h4>Article 3 - Produits</h4>
                    <p>Les produits proposés à la vente sont :</p>
                    <ul>
                        <li>Livres numériques (Ebook au format PDF) - aucune livraison physique requise</li>
                        <li>Livres physiques (version papier) - expédiés depuis la France</li>
                        <li>Options de dédicace pour les versions physiques</li>
                    </ul>
                    <p>Chaque fiche produit présente une description détaillée, le prix en dollars canadiens (CAD) ou en euros (EUR) selon sélection du client, et les caractéristiques du livre. Les photos et illustrations sont fournies à titre indicatif.</p>
                    
                    <h4>Article 4 - Prix</h4>
                    <p>Les prix sont indiqués en <strong>dollars canadiens (CAD)</strong> toutes taxes comprises (TPS/TVQ applicables pour les résidents du Québec/Canada, conformément aux lois fiscales canadiennes). La TPS (5%) et la TVQ (9.975%) sont calculées automatiquement lors du paiement pour les commandes livrées au Québec. Les clients résidant hors du Québec peuvent être sujets à des taux de taxe différents.</p>
                    <p>Les frais de livraison pour les versions physiques sont calculés en fonction du pays de livraison (Canada, États-Unis, international) et s'ajoutent au prix du produit. Clair-Obscur se réserve le droit de modifier ses prix à tout moment, les produits étant facturés sur la base des tarifs en vigueur au moment de la commande.</p>
                    
                    <h4>Article 5 - Commandes</h4>
                    <p>Pour passer commande, le client doit :</p>
                    <ol>
                        <li>Créer un compte client ou se connecter</li>
                        <li>Sélectionner les produits souhaités</li>
                        <li>Ajouter les produits au panier</li>
                        <li>Valider le panier et choisir le mode de livraison</li>
                        <li>Fournir les informations de facturation et de livraison</li>
                        <li>Procéder au paiement sécurisé</li>
                        <li>Accepter les présentes CGV</li>
                    </ol>
                    <p>Une confirmation de commande est envoyée par email après validation du paiement. Conformément à la <strong>Loi sur la protection du consommateur du Québec</strong>, cette confirmation constitue une preuve de la transaction.</p>
                    
                    <h4>Article 6 - Paiement</h4>
                    <p>Le paiement s'effectue en ligne via PayPal, garantissant une transaction sécurisée. Les méthodes de paiement acceptées sont : cartes de crédit (Visa, MasterCard, American Express), compte PayPal, et virement bancaire. Aucune information bancaire n'est stockée sur notre site. Le paiement est exigible immédiatement à la commande.</p>
                    <p>Les prix affichés sont en <strong>dollars canadiens (CAD)</strong> incluant les taxes applicables. Le taux de change entre l'euro et le dollar canadien est déterminé par PayPal au moment de la transaction.</p>
                    
                    <h4>Article 7 - Livraison</h4>
                    <h6>7.1 Livres numériques (Ebook)</h6>
                    <p>Après validation du paiement, un lien de téléchargement unique est généré et envoyé au client. Ce lien est valable 48 heures. Passé ce délai, le client doit contacter le support pour obtenir un nouveau lien. Les livres numériques sont considérés comme livrés immédiatement après mise à disposition du lien de téléchargement.</p>
                    
                    <h6>7.2 Livres physiques</h6>
                    <p>Les délais de livraison pour le Canada sont les suivants :</p>
                    <ul>
                        <li><strong>Québec</strong> : 10 à 15 jours ouvrés (en raison de l'expédition depuis la France)</li>
                        <li><strong>Ontario, Colombie-Britannique, Alberta</strong> : 12 à 18 jours ouvrés</li>
                        <li><strong>Autres provinces et territoires</strong> : 15 à 21 jours ouvrés</li>
                    </ul>
                    <p>Les frais de port sont calculés selon la destination et sont indiqués avant validation de la commande :</p>
                    <ul>
                        <li>Québec : 12,00 $ CAD</li>
                        <li>Autres provinces canadiennes : 15,00 $ CAD</li>
                        <li>États-Unis : 18,00 $ CAD</li>
                        <li>International : 25,00 $ CAD</li>
                    </ul>
                    <p>Le risque de perte ou d'endommagement des produits physiques est transféré au client au moment de la livraison. En cas de non-livraison, le client dispose d'un délai de 60 jours à compter de la date de commande pour signaler le problème.</p>
                    
                    <h4>Article 8 - Droit de rétractation (Loi québécoise)</h4>
                    <p>Conformément à la <strong>Loi sur la protection du consommateur du Québec</strong> (articles 212 à 227) :</p>
                    <ul>
                        <li>Le consommateur dispose d'un délai de <strong>14 jours</strong> à compter de la réception du produit pour exercer son droit de rétractation, sans pénalité et sans justification.</li>
                        <li>Pour les contrats conclus à distance, le délai est de <strong>14 jours</strong> à compter de la réception du bien.</li>
                        <li><strong>Exception :</strong> Le droit de rétractation ne s'applique pas aux livres numériques (Ebook) téléchargés, conformément à l'article 212.1 de la Loi sur la protection du consommateur (biens numériques dématérialisés).</li>
                        <li>Pour les livres physiques (incluant les ouvrages dédicacés), le client doit retourner le produit en parfait état, aux frais du consommateur, sauf si l'entreprise offre de les prendre en charge.</li>
                        <li>Le remboursement doit être effectué dans les 15 jours suivant la réception du produit retourné ou la preuve de son expédition.</li>
                    </ul>
                    <p><strong>Procédure de rétractation :</strong> Pour exercer votre droit de rétractation, veuillez nous contacter à <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a> avec votre numéro de commande et la mention "Rétractation".</p>
                    
                    <h4>Article 9 - Garantie légale (Québec)</h4>
                    <p>Conformément aux articles 37 à 54 de la Loi sur la protection du consommateur et au Code civil du Québec (articles 1590, 1726 et suivants) :</p>
                    <ul>
                        <li>Les livres physiques bénéficient de la <strong>garantie légale de qualité</strong> et de la <strong>garantie contre les vices cachés</strong>.</li>
                        <li>Le consommateur a droit à la réparation, au remplacement ou au remboursement du produit défectueux.</li>
                        <li>La garantie légale s'applique indépendamment de toute garantie conventionnelle.</li>
                        <li>Pour les livres numériques, la garantie de conformité s'applique (fichier PDF non corrompu, téléchargeable).</li>
                    </ul>
                    
                    <h4>Article 10 - Propriété intellectuelle</h4>
                    <p>Tous les livres vendus sur le site Clair-Obscur sont protégés par le droit d'auteur (Loi sur le droit d'auteur du Canada). Le client s'engage à ne pas reproduire, diffuser ou partager les fichiers PDF téléchargés. Tout partage non autorisé est passible de poursuites civiles et pénales.</p>
                    
                    <h4>Article 11 - Responsabilité</h4>
                    <p>Clair-Obscur met tout en œuvre pour assurer l'exactitude des informations fournies sur le site. La responsabilité de Clair-Obscur ne saurait être engagée en cas d'indisponibilité du site, de perte de données liée à une force majeure, ou d'utilisation frauduleuse du compte client. Dans tous les cas, la responsabilité de Clair-Obscur est limitée au montant payé par le client pour la commande concernée.</p>
                    
                    <h4>Article 12 - Protection des renseignements personnels (Loi 25 - Québec)</h4>
                    <p>Conformément à la <strong>Loi sur la protection des renseignements personnels dans le secteur privé</strong> (Loi 25, L.R.Q., c. P-39.1) :</p>
                    <ul>
                        <li>Les informations collectées sont nécessaires au traitement des commandes et à la gestion de la relation client.</li>
                        <li>Vous avez le droit d'accéder, de rectifier et de supprimer vos renseignements personnels.</li>
                        <li>Vous pouvez retirer votre consentement à tout moment.</li>
                        <li>Vos données ne sont pas vendues à des tiers.</li>
                        <li>Pour exercer vos droits, contactez notre responsable de la protection des renseignements personnels à : <strong><?php echo ADMIN_EMAIL; ?></strong></li>
                    </ul>
                    <p>Vos informations sont conservées pour une durée de 7 ans après votre dernière commande, conformément aux exigences fiscales.</p>
                    
                    <h4>Article 13 - Cookies</h4>
                    <p>Le site utilise des cookies pour améliorer l'expérience de navigation et à des fins statistiques. Le client peut paramétrer son navigateur pour refuser les cookies conformément à la directive sur les cookies et à la Loi sur la protection des renseignements personnels.</p>
                    
                    <h4>Article 14 - Litiges et loi applicable</h4>
                    <p>Les présentes CGV sont régies par les lois de la <strong>province de Québec</strong> et du <strong>Canada</strong>. En cas de litige, une solution amiable sera recherchée avant toute action judiciaire. À défaut d'accord amiable, le consommateur québécois peut recourir gratuitement à l'<strong>Office de la protection du consommateur (OPC) du Québec</strong> :</p>
                    <ul>
                        <li>Site web : <a href="https://www.opc.gouv.qc.ca" target="_blank">www.opc.gouv.qc.ca</a></li>
                        <li>Téléphone : 514-253-6556 / 1-888-672-2556</li>
                        <li>Adresse : 400, boulevard Jean-Lesage, Québec (Québec) G1K 8W4</li>
                    </ul>
                    <p>Le consommateur peut également recourir à la <strong>Division des petites créances</strong> de la Cour du Québec pour les litiges dont la valeur n'excède pas 15 000 $ CAD.</p>
                    
                    <h4>Article 15 - Médiation</h4>
                    <p>Conformément au Code civil du Québec, nous nous engageons à tenter une résolution amiable de tout différend. Un processus de médiation peut être proposé avant toute action judiciaire.</p>
                    
                    <hr>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Information importante pour les clients du Québec :</strong><br>
                        Ce site est exploité par une entreprise établie en France. Les produits physiques sont expédiés depuis la France. Des frais de douane ou d'importation peuvent s'appliquer pour les commandes livrées au Canada, à la charge du client. Veuillez consulter l'Agence des services frontaliers du Canada pour plus d'informations.
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pour toute question concernant nos CGV ou pour exercer vos droits en tant que consommateur québécois, vous pouvez nous contacter via <a href="<?php echo SITE_URL; ?>contact/">notre formulaire de contact</a> ou par téléphone au +33 1 23 45 67 89.
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>