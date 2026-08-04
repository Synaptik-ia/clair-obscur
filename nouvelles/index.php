<?php
// nouvelles/index.php - Liste des actualités et nouvelles

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Nouveautés & Actualités — Romans érotiques et littérature adulte | Clair-Obscur Éditions";
$page_description = "Retrouvez les nouveautés de Clair-Obscur Éditions : nouvelles parutions, romans érotiques, littérature adulte, actualités éditoriales, annonces d’auteurs et sorties de livres.";
$keywords = "actualités Clair-Obscur Éditions;news maison d’édition;annonces de publication;lancement de livre;sortie officielle roman;publication à venir;prochains livres;coulisses éditoriales;nouveautés catalogue;agenda éditorial";

// Traitement newsletter (avant tout output HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $_SESSION['flash_message'] = "Merci de votre inscription à notre newsletter !";
    $_SESSION['flash_type'] = "success";
    header('Location: index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Nombre de nouvelles par page
$offset = ($page - 1) * $limit;

// Récupération du nombre total de nouvelles
$sql_count = "SELECT COUNT(*) as total FROM nouvelles";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute();
$total_nouvelles = $stmt_count->fetch()['total'];
$total_pages = ceil($total_nouvelles / $limit);

// Récupération des nouvelles avec pagination
$sql_nouvelles = "SELECT * FROM nouvelles ORDER BY date_publication DESC LIMIT :limit OFFSET :offset";
$stmt_nouvelles = $conn->prepare($sql_nouvelles);
$stmt_nouvelles->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_nouvelles->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_nouvelles->execute();
$nouvelles = $stmt_nouvelles->fetchAll();

// Récupération des 3 dernières nouvelles pour la sidebar
$sql_recentes = "SELECT id, titre, date_publication FROM nouvelles ORDER BY date_publication DESC LIMIT 5";
$stmt_recentes = $conn->prepare($sql_recentes);
$stmt_recentes->execute();
$nouvelles_recentes = $stmt_recentes->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Contenu principal -->
        <div class="col-lg-8">
            <h1 class="mb-4"><i class="fas fa-newspaper"></i> Nouveautés & Actualités — Clair-Obscur Éditions</h1>
            
            <?php if (count($nouvelles) > 0): ?>
                
                <?php foreach ($nouvelles as $nouvelle): ?>
                <article class="card mb-4 shadow-sm">
                    <?php if ($nouvelle['image']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . $nouvelle['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($nouvelle['titre']); ?>" style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-newspaper fa-4x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary">
                                <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($nouvelle['date_publication'])); ?>
                            </span>
                        </div>
                        <h2 class="card-title h4"><?php echo htmlspecialchars($nouvelle['titre']); ?></h2>
<p class="card-text">
    <?php 
    // Nettoyer le HTML et extraire le texte brut pour l'aperçu
    $contenu_brut = strip_tags(html_entity_decode($nouvelle['contenu']));
    $extrait = substr($contenu_brut, 0, 120);
    // Éviter de couper un mot
    if (strlen($contenu_brut) > 120) {
        $extrait = substr($extrait, 0, strrpos($extrait, ' ')) . '...';
    } else {
        $extrait = $extrait . '...';
    }
    echo htmlspecialchars_decode(cleanXSS($extrait));
    ?>
</p>
                        <a href="article.php?id=<?php echo $nouvelle['id']; ?>" class="btn btn-outline-primary">
                            Lire la suite <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>">
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
                    <h5>Aucune actualité pour le moment</h5>
                    <p>Revenez bientôt pour découvrir nos nouvelles publications.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Bloc recherche -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-search"></i> Rechercher
                </div>
                <div class="card-body">
                    <form method="GET" action="recherche.php">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Rechercher une actualité...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Dernières actualités -->
            <?php if (count($nouvelles_recentes) > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-clock"></i> Dernières actualités
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($nouvelles_recentes as $recente): ?>
                    <a href="article.php?id=<?php echo $recente['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <?php echo htmlspecialchars($recente['titre']); ?>
                            </div>
                            <small class="text-muted"><?php echo date('d/m', strtotime($recente['date_publication'])); ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
  
            
            <!-- Newsletter -->
            <div class="card border-primary">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-envelope"></i> Newsletter
                </div>
                <div class="card-body">
                    <p>Recevez nos actualités directement dans votre boîte mail.</p>
                    <form method="POST" action="#">
                        <div class="mb-3">
                            <input type="email" name="newsletter_email" class="form-control" placeholder="Votre email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php';
?>