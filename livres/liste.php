<?php
// livres/liste.php - Liste des livres

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$page_title = "Tous nos livres - Clair-Obscur";
$page_description = "Découvrez tous les livres de la maison d'édition Clair-Obscur. Romans, essais, littérature pour adultes.";

$db = new Database();
$conn = $db->getConnection();

// Paramètres de filtrage et pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'recent';

// Construction de la requête COUNT
$sql_count = "SELECT COUNT(*) as total FROM livres l LEFT JOIN auteurs a ON l.auteur_id = a.id";
$sql_livres = "SELECT l.*, a.nom as auteur_nom 
               FROM livres l 
               LEFT JOIN auteurs a ON l.auteur_id = a.id";

$where = "";
$params = [];

if (!empty($search)) {
    $where = " WHERE l.titre LIKE :search OR l.description LIKE :search OR a.nom LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($statut_filter) && $statut_filter != 'all') {
    if (empty($where)) {
        $where = " WHERE l.statut_vente = :statut";
    } else {
        $where .= " AND l.statut_vente = :statut";
    }
    $params[':statut'] = $statut_filter;
}

// Ajout du WHERE
$sql_count .= $where;
$sql_livres .= $where;

// Tri
switch ($tri) {
    case 'prix_asc':
        $order = " ORDER BY l.prix_ebook ASC";
        break;
    case 'prix_desc':
        $order = " ORDER BY l.prix_ebook DESC";
        break;
    case 'titre_asc':
        $order = " ORDER BY l.titre ASC";
        break;
    case 'ancien':
        $order = " ORDER BY l.date_parution ASC";
        break;
    case 'recent':
    default:
        $order = " ORDER BY l.date_parution DESC";
        break;
}

// Requête finale avec LIMIT
$sql_livres .= $order . " LIMIT :limit OFFSET :offset";

// Exécution COUNT
$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_livres = $stmt_count->fetch()['total'];
$total_pages = ceil($total_livres / $limit);

// Exécution requête livres
$stmt_livres = $conn->prepare($sql_livres);
foreach ($params as $key => $value) {
    $stmt_livres->bindValue($key, $value);
}
$stmt_livres->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_livres->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_livres->execute();
$livres = $stmt_livres->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4"><i class="fas fa-book"></i> Catalogue des livres</h1>
    
    <!-- Barre de recherche et filtres -->
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <form method="GET" action="" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un livre, un auteur..." value="<?php echo cleanXSS($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
            </form>
        </div>
        <div class="col-md-3 mb-2">
            <select name="statut" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="en_vente" <?php echo $statut_filter == 'en_vente' ? 'selected' : ''; ?>>En vente</option>
                <option value="precommande" <?php echo $statut_filter == 'precommande' ? 'selected' : ''; ?>>Précommande</option>
                <option value="non_vendable" <?php echo $statut_filter == 'non_vendable' ? 'selected' : ''; ?>>Non vendable</option>
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <select name="tri" class="form-select" onchange="this.form.submit()">
                <option value="recent" <?php echo $tri == 'recent' ? 'selected' : ''; ?>>Plus récents</option>
                <option value="ancien" <?php echo $tri == 'ancien' ? 'selected' : ''; ?>>Plus anciens</option>
                <option value="prix_asc" <?php echo $tri == 'prix_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                <option value="prix_desc" <?php echo $tri == 'prix_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                <option value="titre_asc" <?php echo $tri == 'titre_asc' ? 'selected' : ''; ?>>Titre A-Z</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <span class="badge bg-secondary p-2"><?php echo $total_livres; ?> livre(s)</span>
        </div>
    </div>
    
    <!-- Résultats -->
    <?php if (count($livres) > 0): ?>
        <div class="row">
            <?php foreach ($livres as $livre): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($livre['couverture']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['couverture']); ?>" class="card-img-top" alt="<?php echo cleanXSS($livre['titre']); ?>" style="height: 280px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 280px;">
                            <i class="fas fa-book fa-4x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo cleanXSS($livre['titre']); ?></h5>
                        <p class="card-text text-muted">
                            <i class="fas fa-user"></i> <?php echo cleanXSS($livre['auteur_nom'] ?? 'Auteur inconnu'); ?>
                        </p>
                        
                        <!-- Badge de statut -->
                        <div class="mb-2">
                            <?php if ($livre['statut_vente'] == 'precommande'): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock"></i> Précommande
                                    <?php if ($livre['date_sortie']): ?>
                                        - Sortie le <?php echo date('d/m/Y', strtotime($livre['date_sortie'])); ?>
                                    <?php endif; ?>
                                </span>
                                <?php if ($livre['prix_precommande']): ?>
                                    <span class="badge bg-info ms-1">
                                        <i class="fas fa-tag"></i> Prix préco : <?php echo number_format($livre['prix_precommande'], 2); ?> €
                                    </span>
                                <?php endif; ?>
                            <?php elseif ($livre['statut_vente'] == 'non_vendable'): ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-ban"></i> Non disponible
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($livre['sous_titre']): ?>
                            <p class="card-text small text-secondary"><?php echo cleanXSS($livre['sous_titre']); ?></p>
                        <?php endif; ?>
                        <p class="card-text">
                            <?php echo substr(cleanXSS($livre['description']), 0, 100) . '...'; ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if ($livre['statut_vente'] == 'precommande' && $livre['prix_precommande']): ?>
                                    <span class="h5 text-primary"><?php echo number_format($livre['prix_precommande'], 2); ?> €</span>
                                    <small class="text-muted text-decoration-line-through"><?php echo number_format($livre['prix_ebook'], 2); ?> €</small>
                                <?php elseif ($livre['statut_vente'] != 'non_vendable'): ?>
                                    <span class="h5 text-primary"><?php echo number_format($livre['prix_ebook'], 2); ?> €</span>
                                <?php endif; ?>
                                <?php if ($livre['prix_physique'] && $livre['statut_vente'] != 'non_vendable'): ?>
                                    <small class="text-muted">/ physique: <?php echo number_format($livre['prix_physique'], 2); ?> €</small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <i class="fas fa-heart text-danger"></i> 
                                <?php echo compterLikes($livre['id']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="<?php echo SITE_URL; ?>livres/fiche.php?id=<?php echo $livre['id']; ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-info-circle"></i> Voir détails
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
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut_filter; ?>&tri=<?php echo $tri; ?>">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut_filter; ?>&tri=<?php echo $tri; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut_filter; ?>&tri=<?php echo $tri; ?>">
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
            <h5>Aucun livre trouvé</h5>
            <p>Essayez d'autres termes de recherche ou <a href="liste.php">réinitialisez les filtres</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>