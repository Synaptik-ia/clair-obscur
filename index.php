<?php
// index.php - Page d'accueil

require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = "Accueil - Maison d'édition Clair-Obscur";
$page_description = "Découvrez les livres de la maison d'édition Clair-Obscur. Littérature pour adultes, romans contemporains, et nouvelles exclusives.";
$keywords = "maison d'édition érotique;maison d’édition littérature adulte;éditeur romans érotiques;édition romans adultes;littérature érotique française;romans érotiques français;livres érotiques adultes;livres littérature adulte;romans sensuels;romans passionnels;romans sulfureux;romans pour adultes;fiction érotique;littérature sensuelle;littérature romantique adulte;romans contemporains adultes";
$db = new Database();
$conn = $db->getConnection();

// Récupérer les 3 derniers livres
$sql_livres = "SELECT l.*, a.nom as auteur_nom 
               FROM livres l 
               LEFT JOIN auteurs a ON l.auteur_id = a.id 
               ORDER BY l.date_parution DESC 
               LIMIT 3";
$stmt_livres = $conn->prepare($sql_livres);
$stmt_livres->execute();
$derniers_livres = $stmt_livres->fetchAll();

// Récupérer les 3 dernières nouvelles
$sql_nouvelles = "SELECT * FROM nouvelles ORDER BY date_publication DESC LIMIT 3";
$stmt_nouvelles = $conn->prepare($sql_nouvelles);
$stmt_nouvelles->execute();
$dernieres_nouvelles = $stmt_nouvelles->fetchAll();

// Récupérer un auteur mis en avant (le plus récent ou aléatoire)
$sql_auteur = "SELECT * FROM auteurs ORDER BY date_creation DESC LIMIT 1";
$stmt_auteur = $conn->prepare($sql_auteur);
$stmt_auteur->execute();
$auteur_honneur = $stmt_auteur->fetch();

// Récupérer les livres les plus likés
$sql_populaires = "SELECT l.id, l.titre, l.couverture, COUNT(lk.id) as total_likes 
                   FROM livres l 
                   LEFT JOIN likes lk ON l.id = lk.livre_id 
                   GROUP BY l.id 
                   ORDER BY total_likes DESC 
                   LIMIT 4";
$stmt_populaires = $conn->prepare($sql_populaires);
$stmt_populaires->execute();
$livres_populaires = $stmt_populaires->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Banner -->
<section class="text-center py-5 mb-4 p-4 bg-dark text-white text-center rounded">
    <div class="container">
        <h1 class="display-4 fw-bold">Clair-Obscur</h1>
        <p class="lead">Maison d'édition indépendante - Littérature pour adultes</p>
        <p class="mb-4">Plongez dans des univers uniques, entre ombre et lumière</p>
        <a href="<?php echo SITE_URL; ?>livres/liste.php" class="btn btn-primary btn-lg">
            <i class="fas fa-book"></i> Découvrir nos livres
        </a>
    </div>
</section>

<div class="container">
    
	    <!-- Dernières nouvelles -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-newspaper"></i> Actualités & Nouvelles</h2>
            <a href="<?php echo SITE_URL; ?>nouvelles/" class="btn btn-outline-secondary">Toutes les nouvelles <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="row">
            <?php if (count($dernieres_nouvelles) > 0): ?>
                <?php foreach ($dernieres_nouvelles as $nouvelle): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><a href="/nouvelles/article.php?id=<?php echo $nouvelle['id']; ?>"><?php echo htmlspecialchars($nouvelle['titre']); ?></a></h5>
                            <p class="card-text text-muted small">
                                <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($nouvelle['date_publication'])); ?>
                            </p>
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
        echo html_entity_decode(htmlspecialchars_decode(cleanXSS($extrait)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    ?>
</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Aucune nouvelle pour le moment.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
	
	
    <!-- Derniers livres -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book-open"></i> Dernières parutions</h2>
            <a href="<?php echo SITE_URL; ?>livres/liste.php" class="btn btn-outline-secondary">Voir tous <i class="fas fa-arrow-right"></i></a>
        </div>
 
        <div class="row">
            <?php if (count($derniers_livres) > 0): ?>
                <?php foreach ($derniers_livres as $livre): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if ($livre['couverture']): ?>
                            <img src="<?php echo SITE_URL . 'assets/images/' . $livre['couverture']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($livre['titre']); ?>" style="height: 300px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 300px;">
                                <i class="fas fa-book fa-4x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($livre['auteur_nom'] ?? 'Auteur inconnu'); ?>
                            </p>
                            <p class="card-text">
                                <?php echo substr(htmlspecialchars($livre['description']), 0, 100) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary"><?php echo number_format($livre['prix_ebook'], 2); ?> €</span>
                                <a href="<?php echo SITE_URL; ?>livres/fiche.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-outline-primary">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Aucun livre disponible pour le moment.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Livres populaires (likes) -->
    <?php if (count($livres_populaires) > 0): ?>
    <section class="mb-5 bg-light p-4 rounded">
        <h2 class="mb-4 text-center"><i class="fas fa-heart text-danger"></i> Coups de cœur des lecteurs</h2>
        <div class="row">
            <?php 
            // Récupérer les détails complets des livres populaires
            $ids = array_column($livres_populaires, 'id');
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql_details = "SELECT l.*, a.nom as auteur_nom 
                               FROM livres l 
                               LEFT JOIN auteurs a ON l.auteur_id = a.id 
                               WHERE l.id IN ($placeholders)";
                $stmt_details = $conn->prepare($sql_details);
                $stmt_details->execute($ids);
                $livres_pop_details = $stmt_details->fetchAll();
            } else {
                $livres_pop_details = [];
            }
            ?>
            <?php foreach ($livres_pop_details as $livre): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <?php if ($livre['couverture']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . $livre['couverture']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($livre['titre']); ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h6>
                        <a href="<?php echo SITE_URL; ?>livres/fiche.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-danger">
                            <i class="fas fa-heart"></i> Découvrir
                        </a>
                    </div>

<div class="d-flex justify-content-between align-items-center">
    <div>
        <?php if ($livre['statut_vente'] == 'precommande'): ?>
            <span class="badge bg-warning text-dark mb-1">Précommande</span>
            <span class="h5 text-primary"><?php echo number_format($livre['prix_precommande'] ?? $livre['prix_ebook'], 2); ?> €</span>
        <?php elseif ($livre['statut_vente'] == 'non_vendable'): ?>
            <span class="badge bg-secondary">Non disponible</span>
        <?php else: ?>
            <span class="h5 text-primary"><?php echo number_format($livre['prix_ebook'], 2); ?> €</span>
        <?php endif; ?>
    </div>
</div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Auteur à l'honneur -->
    <?php if ($auteur_honneur): ?>
    <section class="mb-5">
        <div class="row align-items-center">
            <div class="col-md-4">
                <?php if ($auteur_honneur['photo']): ?>
                    <img src="<?php echo SITE_URL . 'assets/images/' . $auteur_honneur['photo']; ?>" class="img-fluid rounded-circle shadow" alt="<?php echo htmlspecialchars($auteur_honneur['nom']); ?>">
                <?php else: ?>
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px;">
                        <i class="fas fa-user fa-5x"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <h3><i class="fas fa-feather-alt"></i> Auteur à l'honneur</h3>
                <h4 class="text-primary"><?php echo htmlspecialchars($auteur_honneur['nom']); ?></h4>
                <p><?php echo substr(htmlspecialchars($auteur_honneur['biographie']), 0, 300) . '...'; ?></p>
                <a href="<?php echo SITE_URL; ?>auteurs/fiche.php?id=<?php echo $auteur_honneur['id']; ?>" class="btn btn-outline-primary">
                    En savoir plus <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    

    
    <!-- Newsletter (optionnel) -->
    <section class="mb-5 p-4 bg-dark text-white text-center rounded">
        <h3>Restez informé</h3>
        <p>Inscrivez-vous à notre newsletter pour recevoir nos actualités et nos offres</p>
        <form class="row g-3 justify-content-center" method="POST" action="#">
            <div class="col-auto">
                <input type="email" name="newsletter_email" class="form-control" placeholder="Votre email" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </div>
        </form>
    </section>
    
</div>

<?php
// Traitement newsletter (simple simulation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    // Ici vous pourriez enregistrer l'email dans une table newsletter
    $_SESSION['flash_message'] = "Merci de votre inscription à notre newsletter !";
    $_SESSION['flash_type'] = "success";
    header('Location: ' . SITE_URL);
    exit();
}

include 'includes/footer.php';
?>