<?php
// livres/fiche.php - Fiche détaillée d'un livre

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérification de l'ID du livre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit();
}

$livre_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Récupération des infos du livre
$sql_livre = "SELECT l.*, a.nom as auteur_nom, a.id as auteur_id, a.biographie as auteur_bio, a.photo as auteur_photo
              FROM livres l 
              LEFT JOIN auteurs a ON l.auteur_id = a.id 
              WHERE l.id = :id";
$stmt_livre = $conn->prepare($sql_livre);
$stmt_livre->execute([':id' => $livre_id]);
$livre = $stmt_livre->fetch();

if (!$livre) {
    header('Location: liste.php');
    exit();
}

$page_title = cleanXSS($livre['titre']) . ' - Clair-Obscur';
$page_description = substr(strip_tags($livre['description']), 0, 160);

// Gestion des likes (AJAX ou redirection)
if (isset($_POST['toggle_like']) && estConnecte()) {
    toggleLike($_SESSION['user_id'], $livre_id);
    header('Location: fiche.php?id=' . $livre_id);
    exit();
}

// Gestion des commentaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_commentaire']) && estConnecte()) {
    $commentaire = $_POST['commentaire'] ?? '';
    $note = isset($_POST['note']) ? (int)$_POST['note'] : null;
    
    if (!empty($commentaire)) {
        if (ajouterCommentaire($_SESSION['user_id'], $livre_id, $commentaire, $note)) {
            $_SESSION['flash_message'] = "Votre commentaire a été ajouté et sera visible après validation.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Erreur lors de l'ajout du commentaire.";
            $_SESSION['flash_type'] = "danger";
        }
    }
    header('Location: fiche.php?id=' . $livre_id);
    exit();
}

// Récupération des commentaires validés
$commentaires = getCommentaires($livre_id);

// Vérification si l'utilisateur a liké
$user_like = false;
if (estConnecte()) {
    $user_like = utilisateurADelike($_SESSION['user_id'], $livre_id);
}
$total_likes = compterLikes($livre_id);

// Liens de partage sociaux
$share_url = SITE_URL . 'livres/fiche.php?id=' . $livre_id;
$share_title = $livre['titre'];
$social_links = liensPartageSociaux($share_url, $share_title);

include '../includes/header.php';
?>

<div class="container">
    
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
            <li class="breadcrumb-item"><a href="liste.php">Livres</a></li>
            <li class="breadcrumb-item active"><?php echo cleanXSS($livre['titre']); ?></li>
        </ol>
    </nav>
    
    <!-- Détails du livre -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <?php if ($livre['couverture']): ?>
                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['couverture']); ?>" class="img-fluid rounded shadow" alt="<?php echo cleanXSS($livre['titre']); ?>">
            <?php else: ?>
                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="fas fa-book fa-5x"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <h1><?php echo cleanXSS($livre['titre']); ?></h1>
            <?php if ($livre['sous_titre']): ?>
                <h4 class="text-muted"><?php echo cleanXSS($livre['sous_titre']); ?></h4>
            <?php endif; ?>
            
            <p class="mt-3">
                <strong><i class="fas fa-user"></i> Auteur :</strong> 
                <a href="<?php echo SITE_URL; ?>auteurs/fiche.php?id=<?php echo $livre['auteur_id']; ?>">
                    <?php echo cleanXSS($livre['auteur_nom']); ?>
                </a>
            </p>
            
            <?php if ($livre['isbn']): ?>
                <p><strong>ISBN :</strong> <?php echo cleanXSS($livre['isbn']); ?></p>
            <?php endif; ?>
            
            <p><strong>Date de parution :</strong> <?php echo date('d/m/Y', strtotime($livre['date_parution'])); ?></p>
            
            <!-- Like Button -->
            <div class="mb-3">
                <?php if (estConnecte()): ?>
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="toggle_like" class="btn <?php echo $user_like ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            <i class="fas fa-heart"></i> <?php echo $user_like ? 'Je n\'aime plus' : 'J\'aime'; ?> (<?php echo $total_likes; ?>)
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>compte/connexion.php" class="btn btn-outline-danger">
                        <i class="fas fa-heart"></i> Connectez-vous pour aimer (<?php echo $total_likes; ?>)
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Partage sociaux -->
            <div class="mb-3">
                <strong>Partager :</strong>
                <a href="<?php echo $social_links['facebook']; ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fab fa-facebook-f"></i></a>
                <a href="<?php echo $social_links['twitter']; ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="fab fa-x-twitter"></i></a>
                <a href="<?php echo $social_links['email']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-envelope"></i></a>
            </div>
            
            <!-- Description -->
            <div class="mt-4">
                <h5>Description</h5>
                <p><?php echo nl2br(cleanXSS($livre['description'])); ?></p>
            </div>
            
            <!-- Prix et ajout panier avec gestion du statut -->
            <div class="mt-4 p-3 bg-light rounded">
                
                <!-- Message pour livre non vendable -->
                <?php if ($livre['statut_vente'] == 'non_vendable'): ?>
                    <div class="alert alert-secondary">
                        <i class="fas fa-ban"></i> Ce livre n'est actuellement pas disponible à la vente.
                    </div>
                <?php else: ?>
                    
                    <!-- Message pour précommande -->
                    <?php if ($livre['statut_vente'] == 'precommande'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> <strong>Précommande</strong><br>
                            <?php if ($livre['date_precommande'] && strtotime($livre['date_precommande']) > time()): ?>
                                Début des précommandes le <?php echo date('d/m/Y', strtotime($livre['date_precommande'])); ?><br>
                            <?php endif; ?>
                            Sortie prévue le <strong><?php echo date('d/m/Y', strtotime($livre['date_sortie'])); ?></strong>
                            <?php if ($livre['prix_precommande']): ?>
                                <br><span class="h5 text-success mt-2 d-inline-block">Prix spécial précommande : <?php echo number_format($livre['prix_precommande'], 2); ?> €</span>
                                <br><small class="text-muted">Prix normal : <?php echo number_format($livre['prix_ebook'], 2); ?> €</small>
                            <?php else: ?>
                                <span class="h5 text-primary mt-2 d-inline-block"><?php echo number_format($livre['prix_ebook'], 2); ?> €</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Affichage des prix normaux -->
                    <?php if ($livre['statut_vente'] != 'precommande' || !$livre['prix_precommande']): ?>
                        <h4 class="text-primary"><?php echo number_format($livre['prix_ebook'], 2); ?> € (Ebook PDF)</h4>
                    <?php endif; ?>
                    
                    <!-- Version physique -->
                    <?php if ($livre['prix_physique'] && $livre['stock_physique'] > 0 && $livre['statut_vente'] != 'non_vendable'): ?>
                        <p class="mt-2">Version papier : <?php echo number_format($livre['prix_physique'], 2); ?> €</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="dedicace" name="dedicace" value="1">
                            <label class="form-check-label" for="dedicace">
                                Option dédicacée (+0 €)
                            </label>
                        </div>
                    <?php elseif ($livre['prix_physique'] && $livre['stock_physique'] <= 0 && $livre['statut_vente'] != 'non_vendable'): ?>
                        <p class="text-warning">Version papier : Indisponible pour le moment</p>
                    <?php endif; ?>
                    
                    <!-- Formulaire d'achat / précommande -->
                    <?php if ($livre['statut_vente'] == 'precommande'): ?>
                        <form method="POST" action="<?php echo SITE_URL; ?>panier/ajouter.php" class="mt-3">
                            <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                            <input type="hidden" name="type_commande" id="type_commande" value="ebook">
                            <input type="hidden" name="dedicace" id="dedicace_value" value="0">
                            
                            <div class="row align-items-end">
                                <div class="col-auto">
                                    <label>Quantité :</label>
                                    <input type="number" name="quantite" value="1" min="1" max="10" class="form-control" style="width: 80px;">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-shopping-cart"></i> Précommander
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php elseif ($livre['statut_vente'] == 'en_vente'): ?>
                        <form method="POST" action="<?php echo SITE_URL; ?>panier/ajouter.php" class="mt-3">
                            <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                            <input type="hidden" name="type_commande" id="type_commande" value="ebook">
                            <input type="hidden" name="dedicace" id="dedicace_value" value="0">
                            
                            <div class="row align-items-end">
                                <div class="col-auto">
                                    <label>Quantité :</label>
                                    <input type="number" name="quantite" value="1" min="1" max="10" class="form-control" style="width: 80px;">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Section Commentaires -->
    <div class="row mt-5">
        <div class="col-12">
            <h3><i class="fas fa-comments"></i> Commentaires des lecteurs</h3>
            <hr>
            
            <?php if (count($commentaires) > 0): ?>
                <?php foreach ($commentaires as $commentaire): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-subtitle mb-2 text-muted">
                                <i class="fas fa-user"></i> <?php echo cleanXSS($commentaire['prenom'] . ' ' . $commentaire['nom']); ?>
                            </h6>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?></small>
                        </div>
                        <?php if ($commentaire['note']): ?>
                            <div class="mb-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $commentaire['note'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        <p class="card-text"><?php echo nl2br(cleanXSS($commentaire['commentaire'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-secondary">Soyez le premier à laisser un commentaire !</div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout de commentaire -->
            <?php if (estConnecte()): ?>
                <div class="mt-4 p-3 border rounded">
                    <h5>Laisser un commentaire</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Note (optionnelle) :</label>
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <label class="me-2">
                                        <input type="radio" name="note" value="<?php echo $i; ?>"> <?php echo $i; ?> étoile(s)
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Votre commentaire :</label>
                            <textarea name="commentaire" rows="4" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="submit_commentaire" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Publier mon commentaire
                        </button>
                        <small class="text-muted d-block mt-2">Votre commentaire sera vérifié avant publication.</small>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3">
                    <a href="<?php echo SITE_URL; ?>compte/connexion.php">Connectez-vous</a> pour laisser un commentaire.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Gestion du type de commande (ebook vs physique avec dédicace)
document.addEventListener('DOMContentLoaded', function() {
    const checkDedicace = document.getElementById('dedicace');
    const typeCommande = document.getElementById('type_commande');
    const dedicaceValue = document.getElementById('dedicace_value');
    
    if (checkDedicace) {
        checkDedicace.addEventListener('change', function() {
            if (this.checked) {
                typeCommande.value = 'physique_dedicace';
                dedicaceValue.value = '1';
            } else {
                typeCommande.value = 'physique';
                dedicaceValue.value = '0';
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>