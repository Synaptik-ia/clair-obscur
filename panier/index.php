<?php
// panier/index.php - Page du panier

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Mon panier - Clair-Obscur";
$page_description = "Consultez et gérez votre panier avant validation de votre commande.";

$db = new Database();
$conn = $db->getConnection();

// Initialisation du panier en session
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$message_type = '';

// Supprimer un article
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['panier'][$remove_id])) {
        unset($_SESSION['panier'][$remove_id]);
        $_SESSION['flash_message'] = "Article retiré du panier.";
        $_SESSION['flash_type'] = "success";
        header('Location: index.php');
        exit();
    }
}

// Vider le panier
if (isset($_GET['empty'])) {
    $_SESSION['panier'] = [];
    $_SESSION['flash_message'] = "Votre panier a été vidé.";
    $_SESSION['flash_type'] = "info";
    header('Location: index.php');
    exit();
}

// Mise à jour des quantités
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $quantites = $_POST['quantite'] ?? [];
    foreach ($quantites as $livre_id => $qte) {
        $qte = (int)$qte;
        if ($qte <= 0) {
            unset($_SESSION['panier'][$livre_id]);
        } else {
            $_SESSION['panier'][$livre_id]['quantite'] = $qte;
        }
    }
    $_SESSION['flash_message'] = "Panier mis à jour.";
    $_SESSION['flash_type'] = "success";
    header('Location: index.php');
    exit();
}

// Récupération des détails des livres dans le panier
$panier_details = [];
$total_ht = 0;
$total_articles = 0;

if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM livres WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($ids);
    $livres = $stmt->fetchAll();
    
    foreach ($livres as $livre) {
        $item = $_SESSION['panier'][$livre['id']];
        $type = $item['type_commande'] ?? 'ebook';
        $dedicace = isset($item['dedicace']) && $item['dedicace'] ? true : false;
        
        // Déterminer le prix selon le type
        if ($type == 'ebook') {
            $prix_unitaire = $livre['prix_ebook'];
        } else {
            $prix_unitaire = $livre['prix_physique'] ?? $livre['prix_ebook'];
        }
        
        $total_ligne = $prix_unitaire * $item['quantite'];
        $total_ht += $total_ligne;
        $total_articles += $item['quantite'];
        
        $panier_details[] = [
            'id' => $livre['id'],
            'titre' => $livre['titre'],
            'couverture' => $livre['couverture'],
            'type' => $type,
            'dedicace' => $dedicace,
            'quantite' => $item['quantite'],
            'prix_unitaire' => $prix_unitaire,
            'total_ligne' => $total_ligne
        ];
    }
}

// Gestion des frais de port selon le pays (si connecté ou par défaut)
$pays_livraison = 'France';
$frais_port = 0;
$has_physical = false;

// Vérifier si le panier contient des livres physiques
foreach ($panier_details as $item) {
    if ($item['type'] != 'ebook') {
        $has_physical = true;
        break;
    }
}

if ($has_physical) {
    // Si client connecté, utiliser son pays
    if (estConnecte()) {
        $sql_user = "SELECT pays FROM utilisateurs WHERE id = :id";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt_user->fetch();
        if ($user && $user['pays']) {
            $pays_livraison = $user['pays'];
        }
    }
    $frais_port = calculerFraisPort($pays_livraison);
}

$total_ttc = $total_ht + $frais_port;

include '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Mon panier</h1>
    
    <?php if (empty($panier_details)): ?>
        <div class="card shadow-sm text-center p-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4>Votre panier est vide</h4>
            <p>Découvrez nos livres et ajoutez-en à votre panier.</p>
            <a href="<?php echo SITE_URL; ?>livres/liste.php" class="btn btn-primary mt-2">
                <i class="fas fa-book"></i> Voir les livres
            </a>
        </div>
    <?php else: ?>
    
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Articles dans votre panier (<?php echo $total_articles; ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Livre</th>
                                        <th>Type</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($panier_details as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['couverture']): ?>
                                                    <img src="<?php echo SITE_URL . 'assets/images/' . $item['couverture']; ?>" alt="<?php echo htmlspecialchars($item['titre']); ?>" style="width: 50px; height: 60px; object-fit: cover;" class="me-2">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 60px;">
                                                        <i class="fas fa-book"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <?php echo htmlspecialchars($item['titre']); ?>
                                                    <?php if ($item['dedicace']): ?>
                                                        <span class="badge bg-success d-block mt-1">+ Dédicace</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($item['type'] == 'ebook'): ?>
                                                <span class="badge bg-primary">Ebook PDF</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Version papier</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($item['prix_unitaire'], 2); ?> €</td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="number" name="quantite[<?php echo $item['id']; ?>]" value="<?php echo $item['quantite']; ?>" min="1" max="10" style="width: 70px;" class="form-control form-control-sm">
                                        </td>
                                        <td><?php echo number_format($item['total_ligne'], 2); ?> €</td>
                                        <td>
                                            <a href="?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cet article ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo SITE_URL; ?>livres/liste.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Continuer mes achats
                            </a>
                            <div>
                                <button type="submit" name="update_cart" form="update-cart-form" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-sync-alt"></i> Mettre à jour
                                </button>
                                <a href="?empty=1" class="btn btn-outline-danger" onclick="return confirm('Vider tout le panier ?')">
                                    <i class="fas fa-trash-alt"></i> Vider
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Récapitulatif</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total articles :</span>
                            <span><?php echo $total_articles; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total :</span>
                            <span><?php echo number_format($total_ht, 2); ?> €</span>
                        </div>
                        
                        <?php if ($has_physical): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frais de port (<?php echo htmlspecialchars($pays_livraison); ?>) :</span>
                            <span><?php echo number_format($frais_port, 2); ?> €</span>
                        </div>
                        <hr>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total TTC :</strong>
                            <strong class="h5 text-primary"><?php echo number_format($total_ttc, 2); ?> €</strong>
                        </div>
                        
                        <?php if ($has_physical && !estConnecte()): ?>
                            <div class="alert alert-warning small">
                                <i class="fas fa-info-circle"></i> Les frais de port sont calculés selon votre pays. 
                                <a href="<?php echo SITE_URL; ?>compte/connexion.php?redirect=<?php echo urlencode(SITE_URL . 'panier/'); ?>">
                                    Connectez-vous
                                </a> 
                                ou 
                                <a href="<?php echo SITE_URL; ?>compte/inscription.php">inscrivez-vous</a> 
                                pour les mettre à jour.
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo SITE_URL; ?>paiement/paypal.php" class="btn btn-success w-100 mt-2">
                            <i class="fab fa-paypal"></i> Procéder au paiement
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <form id="update-cart-form" method="POST" action=""></form>
        
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>