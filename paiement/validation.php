<?php
// paiement/validation.php - Validation du paiement PayPal

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Validation du paiement - Clair-Obscur";

// Vérification des paramètres
$token = $_GET['token'] ?? '';
$commande_id = (int)($_GET['commande'] ?? 0);

if (!$token || !$commande_id) {
    header('Location: ' . SITE_URL);
    exit();
}

// Vérifier le token de session
if (!isset($_SESSION['pending_payment']) || 
    $_SESSION['pending_payment']['commande_id'] != $commande_id || 
    $_SESSION['pending_payment']['token'] != $token) {
    $_SESSION['flash_message'] = "Session de paiement invalide.";
    $_SESSION['flash_type'] = "danger";
    header('Location: ' . SITE_URL . 'panier/');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Récupérer la commande
$sql = "SELECT * FROM commandes WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $commande_id]);
$commande = $stmt->fetch();

if (!$commande) {
    header('Location: ' . SITE_URL);
    exit();
}

// IMPORTANT : ne JAMAIS marquer la commande payée ici.
// L'URL de retour est visible côté client — seule la notification IPN
// (vérifiée auprès de PayPal dans ajax/ipn.php) peut passer la commande à 'paye'.

// Récupérer les livres de la commande pour générer les liens
$sql_details = "SELECT dc.*, l.fichier_pdf, l.titre 
                FROM details_commandes dc
                JOIN livres l ON dc.livre_id = l.id
                WHERE dc.commande_id = :commande_id";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->execute([':commande_id' => $commande_id]);
$details = $stmt_details->fetchAll();

// La commande contient un ebook si son type global est 'ebook'
// ou si une ligne de détail porte le type 'ebook' (commande mixte)
$has_ebook = ($commande['type_commande'] == 'ebook');
foreach ($details as $detail) {
    if (isset($detail['type_commande']) && $detail['type_commande'] == 'ebook') {
        $has_ebook = true;
        break;
    }
}

// Si l'IPN a déjà confirmé le paiement, générer le lien de téléchargement
$lien_telechargement = null;
if ($commande['statut'] == 'paye' && $has_ebook && empty($commande['lien_telechargement_unique'])) {
    $lien_telechargement = genererLienTelechargement($commande_id, $details[0]['livre_id']);
} elseif ($commande['statut'] == 'paye' && !empty($commande['lien_telechargement_unique'])) {
    $lien_telechargement = SITE_URL . 'download.php?token=' . $commande['lien_telechargement_unique'];
}

// Nettoyer la session de paiement en attente
unset($_SESSION['pending_payment']);

// Vider le panier
$_SESSION['panier'] = [];

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm text-center">
                <?php if ($commande['statut'] == 'paye'): ?>
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <h3 class="mb-0 mt-2">Paiement confirmé !</h3>
                </div>
                <?php else: ?>
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-clock fa-3x"></i>
                    <h3 class="mb-0 mt-2">Paiement en cours de confirmation</h3>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h4>Merci pour votre commande !</h4>
                    <?php if ($commande['statut'] == 'paye'): ?>
                        <p class="lead">Votre paiement a été accepté avec succès.</p>
                    <?php else: ?>
                        <p class="lead">PayPal confirme votre paiement. Cela prend généralement quelques secondes.</p>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <strong>Référence commande :</strong> <?php echo htmlspecialchars($commande['reference']); ?>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 offset-md-3">
                            <div class="list-group text-start">
                                <div class="list-group-item">
                                    <strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?>
                                </div>
                                <div class="list-group-item">
                                    <strong>Montant total :</strong> <?php echo number_format($commande['montant_total'], 2); ?> €
                                </div>
                                <div class="list-group-item">
                                    <strong>Type :</strong>
                                    <?php
                                    switch ($commande['type_commande']) {
                                        case 'ebook':
                                            echo 'Ebook PDF (téléchargement immédiat)';
                                            break;
                                        case 'physique':
                                            echo 'Version physique - En cours de préparation';
                                            break;
                                        case 'physique_dedicace':
                                            echo 'Version physique dédicacée - En cours de préparation';
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($has_ebook && $lien_telechargement): ?>
                        <div class="alert alert-success mt-4">
                            <i class="fas fa-download"></i> 
                            <strong>Votre livre est prêt à être téléchargé !</strong><br>
                            Le lien est valable 48 heures.
                        </div>
                        <a href="<?php echo $lien_telechargement; ?>" class="btn btn-success btn-lg mt-2">
                            <i class="fas fa-download"></i> Télécharger mon PDF
                        </a>
                    <?php elseif ($has_ebook): ?>
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-hourglass-half"></i>
                            <strong>Votre ebook sera disponible dès confirmation du paiement.</strong><br>
                            Retrouvez votre lien de téléchargement dans 
                            <a href="<?php echo SITE_URL; ?>compte/commandes.php">Mes commandes</a>.
                        </div>
                    <?php elseif ($commande['type_commande'] != 'ebook'): ?>
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-truck"></i> 
                            <strong>Commande en préparation</strong><br>
                            Vous serez notifié par email dès l'expédition de votre commande.
                        </div>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?php echo SITE_URL; ?>compte/commandes.php" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-bag"></i> Voir mes commandes
                        </a>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Inscription newsletter -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="newsletter-optin">
                        <label class="form-check-label" for="newsletter-optin">
                            <i class="fas fa-envelope-open-text"></i> <strong>Recevoir nos nouveautés par email</strong><br>
                            <small class="text-muted">Soyez informé des nouvelles parutions, offres exclusives et actualités de Clair-Obscur Éditions.</small>
                        </label>
                    </div>
                    <div id="nl-optin-msg" class="small mt-2" style="display:none;"></div>
                </div>
            </div>

            <!-- Email de confirmation (simulation) -->
            <div class="card mt-4 bg-light">
                <div class="card-body text-center small text-muted">
                    <i class="fas fa-envelope"></i> Un email de confirmation vous a été envoyé à l'adresse associée à votre compte.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>