<?php
// admin/commentaires.php - Gestion des commentaires

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des commentaires - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Validation d'un commentaire
if (isset($_GET['valider']) && is_numeric($_GET['valider'])) {
    $id = (int)$_GET['valider'];
    $sql = "UPDATE commentaires SET status = 'valide' WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Commentaire validé avec succès.";
        $message_type = "success";
    } else {
        $message = "Erreur lors de la validation.";
        $message_type = "danger";
    }
}

// Suppression d'un commentaire
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $sql = "DELETE FROM commentaires WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Commentaire supprimé avec succès.";
        $message_type = "success";
    } else {
        $message = "Erreur lors de la suppression.";
        $message_type = "danger";
    }
}

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'en_attente';
$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

// Récupération de la liste des commentaires
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total 
              FROM commentaires c 
              JOIN utilisateurs u ON c.utilisateur_id = u.id 
              JOIN livres l ON c.livre_id = l.id";
$sql_commentaires = "SELECT c.*, u.nom, u.prenom, u.email, l.titre as livre_titre 
                     FROM commentaires c 
                     JOIN utilisateurs u ON c.utilisateur_id = u.id 
                     JOIN livres l ON c.livre_id = l.id";

$where_conditions = [];
$params = [];

if (!empty($status_filter) && $status_filter != 'all') {
    $where_conditions[] = "c.status = :status";
    $params[':status'] = $status_filter;
}
if (!empty($search)) {
    $where_conditions[] = "(c.commentaire LIKE :search OR u.nom LIKE :search OR u.email LIKE :search OR l.titre LIKE :search)";
    $params[':search'] = "%$search%";
}

if (count($where_conditions) > 0) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    $sql_count .= $where_clause;
    $sql_commentaires .= $where_clause;
}

$sql_commentaires .= " ORDER BY c.date_creation DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_commentaires = $stmt_count->fetch()['total'];
$total_pages = ceil($total_commentaires / $limit);

$stmt_commentaires = $conn->prepare($sql_commentaires);
foreach ($params as $key => $value) {
    $stmt_commentaires->bindValue($key, $value);
}
$stmt_commentaires->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_commentaires->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_commentaires->execute();
$commentaires = $stmt_commentaires->fetchAll();

// Statistiques
$sql_stats = "SELECT 
                SUM(CASE WHEN status = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN status = 'valide' THEN 1 ELSE 0 END) as valides,
                SUM(CASE WHEN status = 'supprime' THEN 1 ELSE 0 END) as supprimes,
                COUNT(*) as total
              FROM commentaires";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetch();

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
            <h1 class="mb-4"><i class="fas fa-comments"></i> Gestion des commentaires</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5 class="card-title">En attente</h5>
                            <h2 class="mb-0"><?php echo $stats['en_attente']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Validés</h5>
                            <h2 class="mb-0"><?php echo $stats['valides']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Supprimés</h5>
                            <h2 class="mb-0"><?php echo $stats['supprimes']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total</h5>
                            <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="en_attente" <?php echo $status_filter == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="valide" <?php echo $status_filter == 'valide' ? 'selected' : ''; ?>>Validés</option>
                                <option value="supprime" <?php echo $status_filter == 'supprime' ? 'selected' : ''; ?>>Supprimés</option>
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Tous</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher par commentaire, client, livre..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des commentaires -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Commentaires (<?php echo $total_commentaires; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($commentaires) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Livre</th>
                                        <th>Note</th>
                                        <th>Commentaire</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commentaires as $commentaire): ?>
                                    <tr>
                                        <td><?php echo $commentaire['id']; ?></td>
                                        <td>
                                            <?php echo cleanXSS($commentaire['prenom'] . ' ' . $commentaire['nom']); ?><br>
                                            <small class="text-muted"><?php echo cleanXSS($commentaire['email']); ?></small>
                                        </td>
                                        <td><?php echo cleanXSS($commentaire['livre_titre']); ?></td>
                                        <td>
                                            <?php if ($commentaire['note']): ?>
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $commentaire['note'] ? 'text-warning' : 'text-secondary'; ?> fa-sm"></i>
                                                <?php endfor; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo substr(cleanXSS($commentaire['commentaire']), 0, 80); ?>...
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch ($commentaire['status']) {
                                                case 'en_attente':
                                                    $status_class = 'bg-warning text-dark';
                                                    $status_text = 'En attente';
                                                    break;
                                                case 'valide':
                                                    $status_class = 'bg-success';
                                                    $status_text = 'Validé';
                                                    break;
                                                case 'supprime':
                                                    $status_class = 'bg-danger';
                                                    $status_text = 'Supprimé';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($commentaire['status'] == 'en_attente'): ?>
                                                <a href="?valider=<?php echo $commentaire['id']; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?supprimer=<?php echo $commentaire['id']; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce commentaire définitivement ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $commentaire['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                         </span>
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
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
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
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p>Aucun commentaire trouvé.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales pour chaque commentaire -->
<?php foreach ($commentaires as $commentaire): ?>
<div class="modal fade" id="modal-<?php echo $commentaire['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Commentaire #<?php echo $commentaire['id']; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Client :</strong> <?php echo cleanXSS($commentaire['prenom'] . ' ' . $commentaire['nom']); ?></p>
                <p><strong>Email :</strong> <?php echo cleanXSS($commentaire['email']); ?></p>
                <p><strong>Livre :</strong> <?php echo cleanXSS($commentaire['livre_titre']); ?></p>
                <p><strong>Note :</strong> 
                    <?php if ($commentaire['note']): ?>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $commentaire['note'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                        <?php endfor; ?>
                    <?php else: ?>
                        Non noté
                    <?php endif; ?>
                </p>
                <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?></p>
                <hr>
                <p><strong>Commentaire :</strong></p>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(cleanXSS($commentaire['commentaire'])); ?>
                </div>
            </div>
            <div class="modal-footer">
                <?php if ($commentaire['status'] == 'en_attente'): ?>
                    <a href="?valider=<?php echo $commentaire['id']; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-success">
                        <i class="fas fa-check"></i> Valider
                    </a>
                <?php endif; ?>
                <a href="?supprimer=<?php echo $commentaire['id']; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce commentaire ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>