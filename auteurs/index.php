<?php
// auteurs/index.php - Liste des auteurs

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$page_title = "Nos auteurs - Clair-Obscur";
$page_description = "Découvrez les auteurs publiés par la maison d'édition Clair-Obscur. Biographies, œuvres et actualités.";

$db = new Database();
$conn = $db->getConnection();

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

// Récupération du nombre total d'auteurs
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

<div class="container">
    <h1 class="mb-4 text-center"><i class="fas fa-users"></i> Nos auteurs</h1>
    
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form method="GET" action="" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un auteur..." value="<?php echo cleanXSS($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <?php if (count($auteurs) > 0): ?>
        <div class="row">
            <?php foreach ($auteurs as $auteur): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <?php if ($auteur['photo']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($auteur['photo']); ?>" class="card-img-top" alt="<?php echo cleanXSS($auteur['nom']); ?>" style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 250px;">
                            <i class="fas fa-user fa-5x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo cleanXSS($auteur['nom']); ?></h5>
                        <p class="card-text small">
                            <?php echo substr(cleanXSS($auteur['biographie']), 0, 100) . '...'; ?>
                        </p>
                        <a href="fiche.php?id=<?php echo $auteur['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> En savoir plus
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i> Précédent
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
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
            <h5>Aucun auteur trouvé</h5>
            <p><?php echo !empty($search) ? 'Aucun auteur ne correspond à votre recherche.' : 'Aucun auteur n\'est disponible pour le moment.'; ?></p>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="btn btn-primary">Voir tous les auteurs</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>