<?php
// admin/nouvelles.php - Gestion des actualités/nouvelles

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/sitemap.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des nouvelles - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Suppression d'une nouvelle
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Récupérer l'image pour suppression
    $sql_image = "SELECT image FROM nouvelles WHERE id = :id";
    $stmt_image = $conn->prepare($sql_image);
    $stmt_image->execute([':id' => $id]);
    $nouvelle = $stmt_image->fetch();
    
    if ($nouvelle && $nouvelle['image'] && file_exists('../assets/images/' . $nouvelle['image'])) {
        unlink('../assets/images/' . $nouvelle['image']);
    }
    
    $sql_delete = "DELETE FROM nouvelles WHERE id = :id";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete->execute([':id' => $id])) {
        // Supprimer l'URL du sitemap
        $url = SITE_URL . 'nouvelles/article.php?id=' . $id;
        $stmt_url = $conn->prepare("DELETE FROM site_pages WHERE url = :url");
        $stmt_url->execute([':url' => $url]);
        generateSitemap($conn);

        $message = "Nouvelle supprimée avec succès.";
        $message_type = "success";
    } else {
        $message = "Erreur lors de la suppression.";
        $message_type = "danger";
    }
}

// Récupération de la liste des nouvelles avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

$sql_count = "SELECT COUNT(*) as total FROM nouvelles";
$sql_nouvelles = "SELECT * FROM nouvelles";

if (!empty($search)) {
    $where = " WHERE titre LIKE :search OR contenu LIKE :search";
    $sql_count .= $where;
    $sql_nouvelles .= $where;
}

$sql_nouvelles .= " ORDER BY date_publication DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
if (!empty($search)) {
    $stmt_count->bindValue(':search', "%$search%");
}
$stmt_count->execute();
$total_nouvelles = $stmt_count->fetch()['total'];
$total_pages = ceil($total_nouvelles / $limit);

$stmt_nouvelles = $conn->prepare($sql_nouvelles);
if (!empty($search)) {
    $stmt_nouvelles->bindValue(':search', "%$search%");
}
$stmt_nouvelles->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_nouvelles->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_nouvelles->execute();
$nouvelles = $stmt_nouvelles->fetchAll();

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
                <h1><i class="fas fa-newspaper"></i> Gestion des nouvelles</h1>
                <a href="nouvelle_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une nouvelle
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
                            <input type="text" name="search" class="form-control" placeholder="Rechercher une nouvelle..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total des nouvelles</h6>
                            <h2 class="mb-0"><?php echo $total_nouvelles; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Avec image</h6>
                            <?php
                            $sql_img = "SELECT COUNT(*) as total FROM nouvelles WHERE image IS NOT NULL AND image != ''";
                            $stmt_img = $conn->prepare($sql_img);
                            $stmt_img->execute();
                            $avec_image = $stmt_img->fetch()['total'];
                            ?>
                            <h2 class="mb-0"><?php echo $avec_image; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Sans image</h6>
                            <?php
                            $sql_no_img = "SELECT COUNT(*) as total FROM nouvelles WHERE image IS NULL OR image = ''";
                            $stmt_no_img = $conn->prepare($sql_no_img);
                            $stmt_no_img->execute();
                            $sans_image = $stmt_no_img->fetch()['total'];
                            ?>
                            <h2 class="mb-0"><?php echo $sans_image; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des nouvelles -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Liste des nouvelles (<?php echo $total_nouvelles; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($nouvelles) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">ID</th>
                                        <th width="80">Image</th>
                                        <th>Titre</th>
                                        <th width="180">Date publication</th>
                                        <th>Contenu (extrait)</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($nouvelles as $nouvelle): ?>
                                    <tr>
                                        <td><?php echo $nouvelle['id']; ?></td>
                                        <td>
                                            <?php if ($nouvelle['image']): ?>
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($nouvelle['image']); ?>" alt="<?php echo cleanXSS($nouvelle['titre']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 4px;">
                                                    <i class="fas fa-newspaper fa-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo cleanXSS($nouvelle['titre']); ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($nouvelle['date_publication'])); ?></td>
                                        <td><?php echo substr(strip_tags(html_entity_decode($nouvelle['contenu'])), 0, 100); ?>...</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="nouvelle_form.php?id=<?php echo $nouvelle['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo SITE_URL; ?>nouvelles/article.php?id=<?php echo $nouvelle['id']; ?>" class="btn btn-sm btn-info" title="Voir sur le site" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?delete=<?php echo $nouvelle['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer cette nouvelle définitivement ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center p-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5>Aucune nouvelle trouvée</h5>
                            <p class="text-muted">
                                <?php if (!empty($search)): ?>
                                    Aucune nouvelle ne correspond à votre recherche.
                                    <br><a href="nouvelles.php" class="btn btn-sm btn-outline-primary mt-2">Voir toutes les nouvelles</a>
                                <?php else: ?>
                                    Commencez par ajouter votre première nouvelle !
                                <?php endif; ?>
                            </p>
                            <?php if (empty($search)): ?>
                                <a href="nouvelle_form.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Ajouter une nouvelle
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lien vers la page publique -->
            <div class="mt-4 text-center">
                <a href="<?php echo SITE_URL; ?>nouvelles/" class="btn btn-outline-secondary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Voir les nouvelles sur le site
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>