<?php
// admin/livres.php - Gestion des livres

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/sitemap.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des livres - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Suppression d'un livre
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si le livre existe
    $sql_check = "SELECT fichier_pdf, couverture FROM livres WHERE id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':id' => $id]);
    $livre = $stmt_check->fetch();
    
    if ($livre) {
        // Supprimer les fichiers associés
        if ($livre['fichier_pdf'] && file_exists('../assets/pdfs/' . $livre['fichier_pdf'])) {
            unlink('../assets/pdfs/' . $livre['fichier_pdf']);
        }
        if ($livre['couverture'] && file_exists('../assets/images/' . $livre['couverture'])) {
            unlink('../assets/images/' . $livre['couverture']);
        }
        
        $sql_delete = "DELETE FROM livres WHERE id = :id";
        $stmt_delete = $conn->prepare($sql_delete);
        if ($stmt_delete->execute([':id' => $id])) {
            // Supprimer l'URL du sitemap
            $url = SITE_URL . 'livres/fiche.php?id=' . $id;
            $stmt_url = $conn->prepare("DELETE FROM site_pages WHERE url = :url");
            $stmt_url->execute([':url' => $url]);
            generateSitemap($conn);

            $message = "Livre supprimé avec succès.";
            $message_type = "success";
        } else {
            $message = "Erreur lors de la suppression.";
            $message_type = "danger";
        }
    }
}

// Récupération de la liste des livres avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

$sql_count = "SELECT COUNT(*) as total FROM livres l LEFT JOIN auteurs a ON l.auteur_id = a.id";
$sql_livres = "SELECT l.*, a.nom as auteur_nom 
               FROM livres l 
               LEFT JOIN auteurs a ON l.auteur_id = a.id";

if (!empty($search)) {
    $where = " WHERE l.titre LIKE :search OR l.isbn LIKE :search OR a.nom LIKE :search";
    $sql_count .= $where;
    $sql_livres .= $where;
}

$sql_livres .= " ORDER BY l.date_parution DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
if (!empty($search)) {
    $stmt_count->bindValue(':search', "%$search%");
}
$stmt_count->execute();
$total_livres = $stmt_count->fetch()['total'];
$total_pages = ceil($total_livres / $limit);

$stmt_livres = $conn->prepare($sql_livres);
if (!empty($search)) {
    $stmt_livres->bindValue(':search', "%$search%");
}
$stmt_livres->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_livres->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_livres->execute();
$livres = $stmt_livres->fetchAll();

// Récupération des auteurs pour le filtre
$sql_auteurs = "SELECT id, nom FROM auteurs ORDER BY nom";
$stmt_auteurs = $conn->prepare($sql_auteurs);
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
                <h1><i class="fas fa-book"></i> Gestion des livres</h1>
                <a href="livre_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un livre
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
                            <input type="text" name="search" class="form-control" placeholder="Rechercher par titre, ISBN ou auteur..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des livres -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Liste des livres (<?php echo $total_livres; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($livres) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Couverture</th>
                                        <th>Titre</th>
                                        <th>Auteur</th>
                                        <th>Prix Ebook</th>
                                        <th>Prix Papier</th>
                                        <th>Stock</th>
                                        <th>Date parution</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($livres as $livre): ?>
                                    <tr>
                                        <td><?php echo $livre['id']; ?></td>
                                        <td>
                                            <?php if ($livre['couverture']): ?>
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['couverture']); ?>" alt="<?php echo cleanXSS($livre['titre']); ?>" style="width: 40px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 50px;">
                                                    <i class="fas fa-book fa-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                          </td>
                                        <td><?php echo cleanXSS($livre['titre']); ?></td>
                                        <td><?php echo cleanXSS($livre['auteur_nom'] ?? '-'); ?></td>
                                        <td><?php echo number_format($livre['prix_ebook'], 2); ?> €</span></td>
                                        <td><?php echo $livre['prix_physique'] ? number_format($livre['prix_physique'], 2) . ' €' : '-'; ?></td>
                                        <td><?php echo $livre['stock_physique'] ?? 0; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($livre['date_parution'])); ?></td>
                                        <td>
                                            <a href="livre_form.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $livre['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce livre définitivement ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>livres/fiche.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-info" target="_blank">
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
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <p>Aucun livre trouvé.</p>
                            <a href="livre_form.php" class="btn btn-primary">Ajouter un livre</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>