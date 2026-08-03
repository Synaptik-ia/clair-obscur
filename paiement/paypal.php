<?php
// paiement/paypal.php - Intégration paiement PayPal

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Paiement - Clair-Obscur";

// Vérifier que l'utilisateur est connecté
redirigerSiNonConnecte();

// Vérifier que le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: ' . SITE_URL . 'panier/');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$sql_user = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->execute([':id' => $user_id]);
$user = $stmt_user->fetch();

// Récupérer les détails du panier
$panier_details = [];
$total_ht = 0;
$has_physical = false;

foreach ($_SESSION['panier'] as $livre_id => $item) {
    $sql = "SELECT * FROM livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $livre_id]);
    $livre = $stmt->fetch();
    
    if ($livre) {
        $type = $item['type_commande'];
        $dedicace = isset($item['dedicace']) && $item['dedicace'];
        
        if ($type == 'ebook') {
            $prix_unitaire = $livre['prix_ebook'];
        } else {
            $prix_unitaire = $livre['prix_physique'] ?? $livre['prix_ebook'];
        }
        
        $total_ligne = $prix_unitaire * $item['quantite'];
        $total_ht += $total_ligne;
        
        if ($type != 'ebook') {
            $has_physical = true;
        }
        
        $panier_details[] = [
            'id' => $livre_id,
            'titre' => $livre['titre'],
            'quantite' => $item['quantite'],
            'type' => $type,
            'dedicace' => $dedicace,
            'prix_unitaire' => $prix_unitaire,
            'total_ligne' => $total_ligne
        ];
    }
}

// Calcul des frais de port
$pays_livraison = $user['pays'] ?? 'France';
$frais_port = 0;
if ($has_physical) {
    $frais_port = calculerFraisPort($pays_livraison);
}

$total_ttc = $total_ht + $frais_port;

// Génération d'une référence unique pour la commande
$reference = 'CMD-' . strtoupper(uniqid()) . '-' . date('YmdHis');

// Création de la commande en base (statut en_attente)
$sql_commande = "INSERT INTO commandes (utilisateur_id, reference, montant_total, type_commande, frais_port, statut, adresse_livraison, pays_livraison, date_commande) 
                 VALUES (:user_id, :reference, :montant, :type_commande, :frais_port, 'en_attente', :adresse, :pays, NOW())";

// Déterminer le type global de commande
$global_type = $has_physical ? ($panier_details[0]['dedicace'] ? 'physique_dedicace' : 'physique') : 'ebook';

$stmt_commande = $conn->prepare($sql_commande);
$stmt_commande->execute([
    ':user_id' => $user_id,
    ':reference' => $reference,
    ':montant' => $total_ttc,
    ':type_commande' => $global_type,
    ':frais_port' => $frais_port,
    ':adresse' => $user['adresse'] ?? '',
    ':pays' => $pays_livraison
]);

$commande_id = $conn->lastInsertId();

// Ajouter les détails de la commande
$sql_detail = "INSERT INTO details_commandes (commande_id, livre_id, quantite, prix_unitaire) VALUES (:commande_id, :livre_id, :quantite, :prix)";
$stmt_detail = $conn->prepare($sql_detail);

foreach ($panier_details as $item) {
    $stmt_detail->execute([
        ':commande_id' => $commande_id,
        ':livre_id' => $item['id'],
        ':quantite' => $item['quantite'],
        ':prix' => $item['prix_unitaire']
    ]);
}

// Configuration PayPal (REST API simplifiée - redirection)
// Dans un environnement de production, utilisez le SDK PayPal officiel
// Ici nous utilisons une approche par redirection (PayPal Standard)

$paypal_url = (PAYPAL_MODE == 'live') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
$business_email = 'votre-email-paypal@example.com'; // À remplacer

// Générer un token temporaire pour la validation après paiement
$payment_token = bin2hex(random_bytes(32));
$_SESSION['pending_payment'] = [
    'commande_id' => $commande_id,
    'reference' => $reference,
    'token' => $payment_token,
    'total' => $total_ttc
];

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h3 class="mb-0"><i class="fab fa-paypal"></i> Paiement sécurisé</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Récapitulatif de la commande -->
                    <h5 class="mb-3">Récapitulatif de votre commande</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Livre</th>
                                    <th>Type</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($panier_details as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['titre']); ?>
                                        <?php if ($item['dedicace']): ?>
                                            <span class="badge bg-success">Dédicacé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['type'] == 'ebook'): ?>
                                            <span class="badge bg-primary">PDF</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Papier</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['quantite']; ?></td>
                                    <td><?php echo number_format($item['total_ligne'], 2); ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if ($frais_port > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Frais de port (<?php echo htmlspecialchars($pays_livraison); ?>) :</strong></td>
                                    <td><strong><?php echo number_format($frais_port, 2); ?> €</strong></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><strong>Total TTC :</strong></td>
                                    <td><strong class="h5 text-primary"><?php echo number_format($total_ttc, 2); ?> €</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Informations de livraison -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-truck"></i> <strong>Livraison :</strong><br>
                        <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?><br>
                        <?php echo nl2br(htmlspecialchars($user['adresse'] ?? 'Adresse non renseignée')); ?><br>
                        <?php echo htmlspecialchars($user['code_postal'] . ' ' . $user['ville']); ?><br>
                        <?php echo htmlspecialchars($user['pays'] ?? 'France'); ?>
                    </div>
                    
                    <!-- Formulaire de paiement PayPal -->
                    <div class="text-center">
                        <form action="<?php echo $paypal_url; ?>" method="post" target="_top">
                            <input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="business" value="<?php echo $business_email; ?>">
                            <input type="hidden" name="item_name" value="Commande Clair-Obscur - <?php echo $reference; ?>">
                            <input type="hidden" name="item_number" value="<?php echo $reference; ?>">
                            <input type="hidden" name="amount" value="<?php echo number_format($total_ttc, 2, '.', ''); ?>">
                            <input type="hidden" name="currency_code" value="EUR">
                            <input type="hidden" name="return" value="<?php echo SITE_URL; ?>paiement/validation.php?token=<?php echo $payment_token; ?>&commande=<?php echo $commande_id; ?>">
                            <input type="hidden" name="cancel_return" value="<?php echo SITE_URL; ?>panier/">
                            <input type="hidden" name="notify_url" value="<?php echo SITE_URL; ?>ajax/ipn.php">
                            <input type="hidden" name="custom" value="<?php echo $commande_id; ?>">
                            <input type="hidden" name="no_shipping" value="1">
                            <input type="hidden" name="no_note" value="1">
                            
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fab fa-paypal fa-2x align-middle me-2"></i> 
                                    Payer avec PayPal (<?php echo number_format($total_ttc, 2); ?> €)
                                </button>
                                <a href="<?php echo SITE_URL; ?>panier/" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour au panier
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> Paiement sécurisé par PayPal - Vos données bancaires ne sont pas stockées sur notre site
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>