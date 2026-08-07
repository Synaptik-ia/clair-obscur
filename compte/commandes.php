<?php
// compte/commandes.php - Liste des commandes du client

require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
redirigerSiNonConnecte();

$page_title = "Mes commandes - Clair-Obscur";
$page_description = "Consultez l'historique de vos commandes et téléchargez vos livres numériques.";

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Récupération des commandes
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM details_commandes dc WHERE dc.commande_id = c.id) as nb_articles
        FROM commandes c 
        WHERE c.utilisateur_id = :user_id 
        ORDER BY c.date_commande DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$commandes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Menu latéral -->
            <div class="list-group shadow-sm">
                <a href="profil.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user"></i> Mon profil
                </a>
                <a href="commandes.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-shopping-bag"></i> Mes commandes
                </a>
                <a href="deconnexion.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h1 class="mb-4"><i class="fas fa-shopping-bag"></i> Mes commandes</h1>
            
            <?php if (count($commandes) > 0): ?>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Articles</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $commande): ?>
                                <?php
                                $statut_class = '';
                                $statut_texte = '';
                                switch ($commande['statut']) {
                                    case 'en_attente':
                                        $statut_class = 'bg-warning text-dark';
                                        $statut_texte = 'En attente';
                                        break;
                                    case 'paye':
                                        $statut_class = 'bg-success';
                                        $statut_texte = 'Payée';
                                        break;
                                    case 'livre':
                                        $statut_class = 'bg-info';
                                        $statut_texte = 'Livrée';
                                        break;
                                    case 'annule':
                                        $statut_class = 'bg-danger';
                                        $statut_texte = 'Annulée';
                                        break;
                                    default:
                                        $statut_class = 'bg-secondary';
                                        $statut_texte = $commande['statut'];
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($commande['reference']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?></td>
                                    <td><?php echo $commande['nb_articles']; ?></td>
                                    <td>
                                        <?php
                                        switch ($commande['type_commande']) {
                                            case 'ebook':
                                                echo '<span class="badge bg-primary">Ebook PDF</span>';
                                                break;
                                            case 'physique':
                                                echo '<span class="badge bg-secondary">Version physique</span>';
                                                break;
                                            case 'physique_dedicace':
                                                echo '<span class="badge bg-success">Version dédicacée</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo number_format($commande['montant_total'], 2); ?> €</td>
                                    <td><span class="badge <?php echo $statut_class; ?>"><?php echo $statut_texte; ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $commande['id']; ?>">
                                            <i class="fas fa-eye"></i> Détails
                                        </button>
                                        
                                        <?php if ($commande['statut'] == 'paye' && $commande['type_commande'] == 'ebook' && $commande['lien_telechargement_unique']): ?>
                                            <a href="<?php echo SITE_URL; ?>download.php?token=<?php echo $commande['lien_telechargement_unique']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> Télécharger
                                            </a>
                                        <?php elseif ($commande['statut'] == 'paye' && $commande['type_commande'] == 'ebook' && !$commande['lien_telechargement_unique']): ?>
                                            <form method="POST" action="generer_lien.php" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-link"></i> Générer lien
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Modales des détails pour chaque commande -->
                <?php foreach ($commandes as $commande): ?>
                <div class="modal fade" id="modal-<?php echo $commande['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">Commande n° <?php echo htmlspecialchars($commande['reference']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Détails des articles -->
                                <h6>Articles commandés :</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Livre</th>
                                            <th>Quantité</th>
                                            <th>Prix unitaire</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_details = "SELECT dc.*, l.titre 
                                                       FROM details_commandes dc
                                                       JOIN livres l ON dc.livre_id = l.id
                                                       WHERE dc.commande_id = :commande_id";
                                        $stmt_details = $conn->prepare($sql_details);
                                        $stmt_details->execute([':commande_id' => $commande['id']]);
                                        $details = $stmt_details->fetchAll();
                                        ?>
                                        <?php foreach ($details as $detail): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detail['titre']); ?></td>
                                            <td><?php echo $detail['quantite']; ?></td>
                                            <td><?php echo number_format($detail['prix_unitaire'], 2); ?> €</td>
                                            <td><?php echo number_format($detail['quantite'] * $detail['prix_unitaire'], 2); ?> €</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <?php if ($commande['frais_port'] > 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Frais de port :</strong></td>
                                            <td><?php echo number_format($commande['frais_port'], 2); ?> €</td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="table-active">
                                            <td colspan="3" class="text-end"><strong>Total :</strong></td>
                                            <td><strong><?php echo number_format($commande['montant_total'], 2); ?> €</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                
                                <hr>
                                
                                <!-- Informations de livraison -->
                                <?php if ($commande['type_commande'] != 'ebook'): ?>
                                <h6>Adresse de livraison :</h6>
                                <p>
                                    <?php echo nl2br(htmlspecialchars($commande['adresse_livraison'] ?? 'Non renseignée')); ?><br>
                                    Pays : <?php echo htmlspecialchars($commande['pays_livraison'] ?? 'France'); ?>
                                </p>
                                <?php endif; ?>
                                
                                <!-- Lien de téléchargement pour ebook -->
                                <?php if ($commande['type_commande'] == 'ebook' && $commande['statut'] == 'paye'): ?>
                                    <?php if ($commande['lien_telechargement_unique']): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            Lien de téléchargement valable jusqu'au : 
                                            <?php echo date('d/m/Y H:i', strtotime($commande['lien_expire_le'])); ?>
                                        </div>
                                        <a href="<?php echo SITE_URL; ?>download.php?token=<?php echo $commande['lien_telechargement_unique']; ?>" class="btn btn-success w-100">
                                            <i class="fas fa-download"></i> Télécharger le PDF
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            Aucun lien de téléchargement généré. Contactez le support.
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <div class="card shadow-sm text-center p-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h4>Aucune commande pour le moment</h4>
                    <p>Vous n'avez pas encore passé de commande chez Clair-Obscur.</p>
                    <a href="<?php echo SITE_URL; ?>livres/liste.php" class="btn btn-primary mt-2">
                        <i class="fas fa-book"></i> Découvrir nos livres
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>