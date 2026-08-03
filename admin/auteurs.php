<?php
// admin/auteurs.php - Gestion des auteurs

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des auteurs - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Suppression d'un auteur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si l'auteur a des livres
    $sql_check = "SELECT COUNT(*) as total FROM livres WHERE auteur_id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':id' => $id]);
    $nb_livres = $stmt_check->fetch()['total'];
    
    if ($nb_livres > 0) {
        $message = "Impossible de supprimer cet auteur car il possède $nb_livres livre(s) associé(s).";
        $message_type = "danger";
    } else {
        // Récupérer la photo pour suppression
        $sql_photo = "SELECT photo FROM auteurs WHERE id = :id";
        $stmt_photo = $conn->prepare($sql_photo);
        $stmt_photo->execute([':id' => $id]);
        $auteur = $stmt_photo->fetch();
        
        if ($auteur && $auteur['photo'] && file_exists('../assets/images/' . $auteur['photo'])) {
            unlink('../assets/images/' . $auteur['photo']);
        }
        
        $sql_delete = "DELETE FROM auteurs WHERE id = :id";
        $stmt_delete = $conn->prepare($sql_delete);
        if ($stmt_delete->execute([':id' => $id])) {
            $message = "Auteur supprimé avec succès.";
            $message_type = "success";
        } else {
            $message = "Erreur lors de la suppression.";
            $message_type = "danger";
        }
    }
}

// Récupération de la liste des auteurs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

$sql_count = "SELECT COUNT(*) as total FROM auteurs";
$sql_auteurs = "SELECT * FROM auteurs";

if (!empty($search)) {
    $where = " WHERE nom LIKE :search OR biographie LIKE :search";
    $sql_count .= $where;
    $sql_auteurs .= $where;
}

$sql_auteurs .= " ORDER BY nom ASC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
if (!empty($search)) {
    $stmt_count->bindValue(':search', "%$search%");
}
$stmt_count->execute();
$total_auteurs = $stmt_count->fetch()['total'];
$total_pages = ceil($total_auteurs / $limit);

$stmt_auteurs = $conn->prepare($sql_auteurs);
if (!empty($search)) {
    $stmt_auteurs->bindValue(':search', "%$search%");
}
$stmt_auteurs->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_auteurs->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_auteurs->execute();
$auteurs = $stmt_auteurs->fetchAll();

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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users"></i> Gestion des auteurs</h1>
                <a href="auteur_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un auteur
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Barre de recherche -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher un auteur..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des auteurs -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Liste des auteurs (<?php echo $total_auteurs; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($auteurs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Biographie</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auteurs as $auteur): ?>
                                    <tr>
                                        <td><?php echo $auteur['id']; ?></td>
                                        <td>
                                            <?php if ($auteur['photo']): ?>
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($auteur['photo']); ?>" alt="<?php echo cleanXSS($auteur['nom']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user fa-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                          </td>
                                        <td><?php echo cleanXSS($auteur['nom']); ?></td>
                                        <td><?php echo substr(cleanXSS($auteur['biographie']), 0, 80) . '...'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($auteur['date_creation'])); ?></td>
                                        <td>
                                            <a href="auteur_form.php?id=<?php echo $auteur['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $auteur['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet auteur définitivement ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>auteurs/fiche.php?id=<?php echo $auteur['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                          </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                              </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
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
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p>Aucun auteur trouvé.</p>
                            <a href="auteur_form.php" class="btn btn-primary">Ajouter un auteur</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>