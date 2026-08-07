<?php
// admin/commandes.php - Gestion des commandes

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des commandes - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Mise à jour du statut d'une commande
if (isset($_POST['update_statut']) && isset($_POST['commande_id']) && isset($_POST['statut'])) {
    $commande_id = (int)$_POST['commande_id'];
    $statut = $_POST['statut'];
    
    $sql_update = "UPDATE commandes SET statut = :statut WHERE id = :id";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update->execute([':statut' => $statut, ':id' => $commande_id])) {
        $message = "Statut de la commande mis à jour.";
        $message_type = "success";
    } else {
        $message = "Erreur lors de la mise à jour.";
        $message_type = "danger";
    }
}

// Génération manuelle d'un lien de téléchargement
if (isset($_POST['generate_link']) && isset($_POST['commande_id'])) {
    $commande_id = (int)$_POST['commande_id'];
    
    $sql_check = "SELECT type_commande, statut FROM commandes WHERE id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':id' => $commande_id]);
    $commande = $stmt_check->fetch();
    
    if ($commande && $commande['type_commande'] == 'ebook' && $commande['statut'] == 'paye') {
        $lien = genererLienTelechargement($commande_id, 0);
        $message = "Lien de téléchargement généré avec succès.";
        $message_type = "success";
    } else {
        $message = "Impossible de générer un lien pour cette commande.";
        $message_type = "danger";
    }
}

// Filtres
$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Récupération de la liste des commandes
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id";
$sql_commandes = "SELECT c.*, u.nom, u.prenom, u.email 
                  FROM commandes c 
                  JOIN utilisateurs u ON c.utilisateur_id = u.id";

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(c.reference LIKE :search OR u.email LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($statut_filter)) {
    $where_conditions[] = "c.statut = :statut";
    $params[':statut'] = $statut_filter;
}
if (!empty($type_filter)) {
    $where_conditions[] = "c.type_commande = :type";
    $params[':type'] = $type_filter;
}

if (count($where_conditions) > 0) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    $sql_count .= $where_clause;
    $sql_commandes .= $where_clause;
}

$sql_commandes .= " ORDER BY c.date_commande DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_commandes = $stmt_count->fetch()['total'];
$total_pages = ceil($total_commandes / $limit);

$stmt_commandes = $conn->prepare($sql_commandes);
foreach ($params as $key => $value) {
    $stmt_commandes->bindValue($key, $value);
}
$stmt_commandes->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_commandes->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_commandes->execute();
$commandes = $stmt_commandes->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar admin -->
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <!-- Contenu -->
        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Gestion des commandes</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Réf., email, nom..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente" <?php echo $statut_filter == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="paye" <?php echo $statut_filter == 'paye' ? 'selected' : ''; ?>>Payée</option>
                                <option value="livre" <?php echo $statut_filter == 'livre' ? 'selected' : ''; ?>>Livrée</option>
                                <option value="annule" <?php echo $statut_filter == 'annule' ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="ebook" <?php echo $type_filter == 'ebook' ? 'selected' : ''; ?>>Ebook PDF</option>
                                <option value="physique" <?php echo $type_filter == 'physique' ? 'selected' : ''; ?>>Version papier</option>
                                <option value="physique_dedicace" <?php echo $type_filter == 'physique_dedicace' ? 'selected' : ''; ?>>Version dédicacée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des commandes -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Commandes (<?php echo $total_commandes; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($commandes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Référence</th>
                                        <th>Client</th>
                                        <th>Date</th>
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
                                        
                                        $type_texte = '';
                                        switch ($commande['type_commande']) {
                                            case 'ebook':
                                                $type_texte = 'PDF';
                                                break;
                                            case 'physique':
                                                $type_texte = 'Papier';
                                                break;
                                            case 'physique_dedicace':
                                                $type_texte = 'Dédicacé';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $commande['id']; ?></td>
                                            <td><strong><?php echo cleanXSS($commande['reference']); ?></strong></td>
                                            <td>
                                                <?php echo cleanXSS($commande['prenom'] . ' ' . $commande['nom']); ?><br>
                                                <small class="text-muted"><?php echo cleanXSS($commande['email']); ?></small>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $type_texte; ?></span></td>
                                            <td><?php echo number_format($commande['montant_total'], 2); ?> €</td>
                                            <td><span class="badge <?php echo $statut_class; ?>"><?php echo $statut_texte; ?></span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $commande['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($commande['type_commande'] == 'ebook' && $commande['statut'] == 'paye' && !$commande['lien_telechargement_unique']): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
                                                        <button type="submit" name="generate_link" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-link"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                             </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut_filter; ?>&type=<?php echo $type_filter; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center p-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p>Aucune commande trouvée.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales pour chaque commande -->
<?php foreach ($commandes as $commande): ?>
<div class="modal fade" id="modal-<?php echo $commande['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Commande n° <?php echo cleanXSS($commande['reference']); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php
                $sql_details = "SELECT dc.*, l.titre 
                               FROM details_commandes dc
                               JOIN livres l ON dc.livre_id = l.id
                               WHERE dc.commande_id = :commande_id";
                $stmt_details = $conn->prepare($sql_details);
                $stmt_details->execute([':commande_id' => $commande['id']]);
                $details = $stmt_details->fetchAll();
                ?>
                
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
                        <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?php echo cleanXSS($detail['titre']); ?></span></td>
                            <td><?php echo $detail['quantite']; ?></span></td>
                            <td><?php echo number_format($detail['prix_unitaire'], 2); ?> €</span></td>
                            <td><?php echo number_format($detail['quantite'] * $detail['prix_unitaire'], 2); ?> €</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php if ($commande['frais_port'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Frais de port :</strong></span></td>
                            <td><?php echo number_format($commande['frais_port'], 2); ?> €</span></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-active">
                            <td colspan="3" class="text-end"><strong>Total :</strong></span></td>
                            <td><strong><?php echo number_format($commande['montant_total'], 2); ?> €</strong></span></td>
                        </tr>
                    </tfoot>
                </table>
                
                <hr>
                
                <h6>Informations client :</h6>
                <p>
                    <?php echo cleanXSS($commande['prenom'] . ' ' . $commande['nom']); ?><br>
                    <?php echo cleanXSS($commande['email']); ?>
                </p>
                
                <?php if ($commande['type_commande'] != 'ebook'): ?>
                <h6>Adresse de livraison :</h6>
                <p><?php echo nl2br(cleanXSS($commande['adresse_livraison'] ?? 'Non renseignée')); ?><br>
                Pays : <?php echo cleanXSS($commande['pays_livraison'] ?? 'France'); ?></p>
                <?php endif; ?>
                
                <hr>
                
                <h6>Changer le statut :</h6>
                <form method="POST" action="" class="row g-2">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
                    <div class="col-auto">
                        <select name="statut" class="form-select">
                            <option value="en_attente" <?php echo $commande['statut'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="paye" <?php echo $commande['statut'] == 'paye' ? 'selected' : ''; ?>>Payée</option>
                            <option value="livre" <?php echo $commande['statut'] == 'livre' ? 'selected' : ''; ?>>Livrée</option>
                            <option value="annule" <?php echo $commande['statut'] == 'annule' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="update_statut" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>