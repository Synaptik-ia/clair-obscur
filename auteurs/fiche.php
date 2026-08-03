<?php
// auteurs/fiche.php - Page détaillée d'un auteur

require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . 'livres/liste.php');
    exit();
}

$auteur_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Récupération des infos de l'auteur
$sql_auteur = "SELECT * FROM auteurs WHERE id = :id";
$stmt_auteur = $conn->prepare($sql_auteur);
$stmt_auteur->execute([':id' => $auteur_id]);
$auteur = $stmt_auteur->fetch();

if (!$auteur) {
    header('Location: ' . SITE_URL . 'auteurs/index.php');
    exit();
}

$page_title = $auteur['nom'] . ' - Clair-Obscur';
$page_description = substr(strip_tags($auteur['biographie']), 0, 160);

// Récupération des livres de l'auteur
$sql_livres = "SELECT * FROM livres WHERE auteur_id = :auteur_id ORDER BY date_parution DESC";
$stmt_livres = $conn->prepare($sql_livres);
$stmt_livres->execute([':auteur_id' => $auteur_id]);
$livres = $stmt_livres->fetchAll();

// Récupération d'autres auteurs pour la sidebar
$sql_autres = "SELECT id, nom, photo FROM auteurs WHERE id != :id ORDER BY RAND() LIMIT 3";
$stmt_autres = $conn->prepare($sql_autres);
$stmt_autres->execute([':id' => $auteur_id]);
$autres_auteurs = $stmt_autres->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>livres/liste.php">Livres</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($auteur['nom']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Colonne gauche : Photo et infos -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <?php if ($auteur['photo']): ?>
                    <img src="<?php echo SITE_URL . 'assets/images/' . $auteur['photo']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($auteur['nom']); ?>">
                <?php else: ?>
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 300px;">
                        <i class="fas fa-user fa-5x"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body text-center">
                    <h2 class="card-title h3"><?php echo htmlspecialchars($auteur['nom']); ?></h2>
                    <p class="text-muted">
                        <i class="fas fa-book"></i> <?php echo count($livres); ?> livre(s) publié(s)
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Colonne droite : Biographie et livres -->
        <div class="col-md-8">
            <!-- Biographie -->
            <div class="mb-4">
                <h3><i class="fas fa-feather-alt"></i> Biographie</h3>
                <div class="p-3 bg-light rounded">
                    <?php if ($auteur['biographie']): ?>
                        <p><?php echo nl2br(htmlspecialchars($auteur['biographie'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">Aucune biographie disponible pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Liste des livres de l'auteur -->
            <div>
                <h3><i class="fas fa-book"></i> Œuvres de <?php echo htmlspecialchars($auteur['nom']); ?></h3>
                
                <?php if (count($livres) > 0): ?>
                    <div class="row mt-3">
                        <?php foreach ($livres as $livre): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <?php if ($livre['couverture']): ?>
                                            <img src="<?php echo SITE_URL . 'assets/images/' . $livre['couverture']; ?>" class="img-fluid rounded-start h-100 object-fit-cover" alt="<?php echo htmlspecialchars($livre['titre']); ?>" style="object-fit: cover; height: 100%;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center h-100">
                                                <i class="fas fa-book fa-2x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                                            <?php if ($livre['sous_titre']): ?>
                                                <p class="card-text small text-muted"><?php echo htmlspecialchars($livre['sous_titre']); ?></p>
                                            <?php endif; ?>
                                            <p class="card-text">
                                                <strong><?php echo number_format($livre['prix_ebook'], 2); ?> €</strong>
                                            </p>
                                            <a href="<?php echo SITE_URL; ?>livres/fiche.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                Voir détails <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucun livre publié pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Autres auteurs à découvrir -->
    <?php if (count($autres_auteurs) > 0): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3><i class="fas fa-users"></i> Découvrez d'autres auteurs</h3>
            <hr>
        </div>
        <?php foreach ($autres_auteurs as $autre): ?>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <?php if ($autre['photo']): ?>
                    <img src="<?php echo SITE_URL . 'assets/images/' . $autre['photo']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($autre['nom']); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($autre['nom']); ?></h5>
                    <a href="fiche.php?id=<?php echo $autre['id']; ?>" class="btn btn-sm btn-outline-secondary">
                        Voir sa biographie
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
</div>

<?php include '../includes/footer.php'; ?>