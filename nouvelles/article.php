<?php
// nouvelles/article.php - Page détaillée d'une actualité

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$article_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Récupération de l'article
$sql_article = "SELECT * FROM nouvelles WHERE id = :id";
$stmt_article = $conn->prepare($sql_article);
$stmt_article->execute([':id' => $article_id]);
// Récupération de l'article (après la requête SQL)
$article = $stmt_article->fetch();

// IMPORTANT: Décoder le HTML pour l'affichage
if ($article && !empty($article['contenu'])) {
    $article['contenu'] = html_entity_decode($article['contenu']);
}

if (!$article) {
    header('Location: index.php');
    exit();
}

$page_title = cleanXSS($article['titre']) . ' - Clair-Obscur';
$page_description = substr(strip_tags(cleanXSS($article['contenu'])), 0, 160);

// Récupération des articles précédent et suivant
$sql_prev = "SELECT id, titre FROM nouvelles WHERE id < :id ORDER BY id DESC LIMIT 1";
$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->execute([':id' => $article_id]);
$prev_article = $stmt_prev->fetch();

$sql_next = "SELECT id, titre FROM nouvelles WHERE id > :id ORDER BY id ASC LIMIT 1";
$stmt_next = $conn->prepare($sql_next);
$stmt_next->execute([':id' => $article_id]);
$next_article = $stmt_next->fetch();

// Récupération des 5 dernières actualités pour la sidebar
$sql_recentes = "SELECT id, titre, date_publication FROM nouvelles ORDER BY date_publication DESC LIMIT 5";
$stmt_recentes = $conn->prepare($sql_recentes);
$stmt_recentes->execute();
$nouvelles_recentes = $stmt_recentes->fetchAll();

// Liens de partage sociaux
$share_url = urlencode(SITE_URL . 'nouvelles/article.php?id=' . $article_id);
$share_title = urlencode($article['titre']);
$share_description = urlencode(substr(strip_tags($article['contenu']), 0, 200));

// Instagram n'a pas d'API de partage direct, on copie le lien dans le presse-papier
// Facebook, Twitter, Email restent disponibles
$social_links = [
    'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . $share_url,
    'twitter' => "https://twitter.com/intent/tweet?url=" . $share_url . "&text=" . $share_title,
    'instagram' => "#", // Pas d'API directe, on utilise un modal ou une copie de lien
    'email' => "mailto:?subject=" . $share_title . "&body=" . $share_url
];

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Contenu principal -->
        <div class="col-lg-8">
            <!-- Fil d'Ariane -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Actualités</a></li>
                    <li class="breadcrumb-item active"><?php echo cleanXSS($article['titre']); ?></li>
                </ol>
            </nav>
            
            <article>
                <!-- En-tête de l'article -->
                <header class="mb-4">
                    <h1 class="display-5"><?php echo cleanXSS($article['titre']); ?></h1>
                    <div class="text-muted mb-3">
                        <i class="far fa-calendar-alt"></i> Publié le <?php echo date('d/m/Y à H:i', strtotime($article['date_publication'])); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-user"></i> Par l'équipe Clair-Obscur
                    </div>
                    
                    <!-- Image de couverture -->
                    <?php if ($article['image']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($article['image']); ?>" class="img-fluid rounded shadow-sm mb-4 w-100" alt="<?php echo cleanXSS($article['titre']); ?>" style="max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                </header>
                
                <!-- Contenu -->
                <div class="article-content mb-4">
                    <?php echo $article['contenu']; ?>
					
					<!-- Image de couverture -->
                    <?php if ($article['image']): ?>
                       <div style="text-align:center"><br /><br /><img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($article['image']); ?>" class="img-fluid rounded shadow-sm mb-4 w-100" alt="<?php echo cleanXSS($article['titre']); ?>" style="max-width:60%; object-fit: cover;"></div>
                    <?php endif; ?>
                </div>
                
                <!-- Partage sociaux -->
                <div class="share-section p-3 bg-light rounded mb-4">
                    <strong><i class="fas fa-share-alt"></i> Partager cet article :</strong>
                    <div class="mt-2">
                        <a href="<?php echo $social_links['facebook']; ?>" target="_blank" class="btn btn-sm btn-primary me-1">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="<?php echo $social_links['twitter']; ?>" target="_blank" class="btn btn-sm btn-info text-white me-1">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <button type="button" class="btn btn-sm btn-instagram me-1" data-bs-toggle="modal" data-bs-target="#instagramModal" style="background: linear-gradient(45deg, #f09433, #d62976, #962fbf, #405de6); color: white; border: none;">
                            <i class="fab fa-instagram"></i> Instagram
                        </button>
                        <a href="<?php echo $social_links['email']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>
                
                <!-- Modal Instagram pour copier le lien -->
                <div class="modal fade" id="instagramModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(45deg, #f09433, #d62976, #962fbf, #405de6); color: white;">
                                <h5 class="modal-title"><i class="fab fa-instagram"></i> Partager sur Instagram</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body text-center">
                                <i class="fab fa-instagram fa-3x mb-3" style="background: linear-gradient(45deg, #f09433, #d62976, #962fbf, #405de6); -webkit-background-clip: text; background-clip: text; color: transparent;"></i>
                                <p>Pour partager cet article sur Instagram :</p>
                                <ol class="text-start">
                                    <li>Copiez le lien ci-dessous</li>
                                    <li>Ouvrez l'application Instagram</li>
                                    <li>Publiez votre contenu avec le lien dans votre bio ou en story</li>
                                </ol>
                                <div class="input-group mt-3">
                                    <input type="text" id="shareLink" class="form-control" value="<?php echo SITE_URL; ?>nouvelles/article.php?id=<?php echo $article_id; ?>" readonly>
                                    <button class="btn btn-primary" onclick="copyLink()">
                                        <i class="fas fa-copy"></i> Copier
                                    </button>
                                </div>
                                <div id="copyMessage" class="text-success mt-2 small" style="display: none;">Lien copié !</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation entre articles -->
                <div class="row mt-4">
                    <div class="col-6">
                        <?php if ($prev_article): ?>
                            <a href="article.php?id=<?php echo $prev_article['id']; ?>" class="btn btn-outline-secondary w-100 text-start">
                                <i class="fas fa-arrow-left"></i> Article précédent<br>
                                <small><?php echo cleanXSS($prev_article['titre']); ?></small>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <?php if ($next_article): ?>
                            <a href="article.php?id=<?php echo $next_article['id']; ?>" class="btn btn-outline-secondary w-100 text-end">
                                Article suivant <i class="fas fa-arrow-right"></i><br>
                                <small><?php echo cleanXSS($next_article['titre']); ?></small>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Dernières actualités -->
            <?php if (count($nouvelles_recentes) > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-clock"></i> Dernières actualités
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($nouvelles_recentes as $recente): ?>
                    <a href="article.php?id=<?php echo $recente['id']; ?>" class="list-group-item list-group-item-action <?php echo $recente['id'] == $article_id ? 'active' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt me-2"></i>
                                <?php echo cleanXSS($recente['titre']); ?>
                            </div>
                            <small><?php echo date('d/m', strtotime($recente['date_publication'])); ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Retour aux actualités -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <a href="index.php" class="btn btn-primary w-100">
                        <i class="fas fa-newspaper"></i> Toutes les actualités
                    </a>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="card border-primary">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-envelope"></i> Newsletter
                </div>
                <div class="card-body">
                    <p>Ne manquez aucune actualité de Clair-Obscur.</p>
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

<script>
function copyLink() {
    var copyText = document.getElementById("shareLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    var message = document.getElementById("copyMessage");
    message.style.display = "block";
    setTimeout(function() {
        message.style.display = "none";
    }, 2000);
}
</script>

<style>
.btn-instagram:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<?php
// Traitement newsletter simple
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $newsletter_email = cleanSQL(trim($_POST['newsletter_email']));
    if (validateEmail($newsletter_email)) {
        // Ici vous pourriez enregistrer l'email dans une table newsletter
        $_SESSION['flash_message'] = "Merci de votre inscription à notre newsletter !";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Veuillez entrer un email valide.";
        $_SESSION['flash_type'] = "danger";
    }
    header('Location: article.php?id=' . $article_id);
    exit();
}

include '../includes/footer.php';
?>