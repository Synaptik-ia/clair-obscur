<?php
// admin/index.php - Tableau de bord administrateur

require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
if (!estConnecte()) {
    header('Location: login.php');
    exit();
}

if (!estAdmin()) {
    $_SESSION['flash_message'] = "Accès non autorisé.";
    $_SESSION['flash_type'] = "danger";
    header('Location: ' . SITE_URL);
    exit();
}

$page_title = "Administration - Clair-Obscur";

$db = new Database();
$conn = $db->getConnection();

// Statistiques globales
$stats = [];

// Nombre de livres
$sql = "SELECT COUNT(*) as total FROM livres";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['livres'] = $stmt->fetch()['total'];

// Nombre d'auteurs
$sql = "SELECT COUNT(*) as total FROM auteurs";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['auteurs'] = $stmt->fetch()['total'];

// Nombre de clients
$sql = "SELECT COUNT(*) as total FROM utilisateurs WHERE is_admin = 0";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['clients'] = $stmt->fetch()['total'];

// Nombre de commandes
$sql = "SELECT COUNT(*) as total FROM commandes";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['commandes'] = $stmt->fetch()['total'];

// Chiffre d'affaires total
$sql = "SELECT SUM(montant_total) as total FROM commandes WHERE statut = 'paye'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['ca'] = $stmt->fetch()['total'] ?? 0;

// Commandes en attente
$sql = "SELECT COUNT(*) as total FROM commandes WHERE statut = 'en_attente'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['commandes_attente'] = $stmt->fetch()['total'];

// Nombre de livres dans la liseuse
$sql = "SELECT COUNT(*) as total FROM liseuse_livres";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats['liseuse'] = $stmt->fetch()['total'];

// Dernières commandes
$sql = "SELECT c.*, u.nom, u.prenom, u.email 
        FROM commandes c 
        JOIN utilisateurs u ON c.utilisateur_id = u.id 
        ORDER BY c.date_commande DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$dernieres_commandes = $stmt->fetchAll();

// Derniers commentaires en attente
$sql = "SELECT c.*, l.titre as livre_titre, u.nom, u.prenom 
        FROM commentaires c 
        JOIN livres l ON c.livre_id = l.id 
        JOIN utilisateurs u ON c.utilisateur_id = u.id 
        WHERE c.status = 'en_attente' 
        ORDER BY c.date_creation DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$commentaires_attente = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar admin - inclusion du menu centralisé -->
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
            
            <!-- Cartes statistiques -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Livres</h6>
                                    <h2 class="mb-0"><?php echo $stats['livres']; ?></h2>
                                </div>
                                <i class="fas fa-book fa-3x opacity-50"></i>
                            </div>
                            <a href="livres.php" class="text-white text-decoration-none small">Gérer <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Auteurs</h6>
                                    <h2 class="mb-0"><?php echo $stats['auteurs']; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                            <a href="auteurs.php" class="text-white text-decoration-none small">Gérer <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Clients</h6>
                                    <h2 class="mb-0"><?php echo $stats['clients']; ?></h2>
                                </div>
                                <i class="fas fa-user-friends fa-3x opacity-50"></i>
                            </div>
                            <a href="clients.php" class="text-white text-decoration-none small">Gérer <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Commandes</h6>
                                    <h2 class="mb-0"><?php echo $stats['commandes']; ?></h2>
                                </div>
                                <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                            </div>
                            <a href="commandes.php" class="text-dark text-decoration-none small">Gérer <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Deuxième ligne de stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Commandes en attente</h6>
                            <h2 class="mb-0"><?php echo $stats['commandes_attente']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-dark text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Chiffre d'affaires</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['ca'], 2); ?> €</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-secondary text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Commentaires en attente</h6>
                            <h2 class="mb-0"><?php echo count($commentaires_attente); ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Liseuse</h6>
                            <h2 class="mb-0"><?php echo $stats['liseuse']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Dernières commandes -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-clock"></i> Dernières commandes</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($dernieres_commandes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Réf.</th>
                                                <th>Client</th>
                                                <th>Montant</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dernieres_commandes as $commande): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($commande['reference']); ?></td>
                                                <td><?php echo htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']); ?></td>
                                                <td><?php echo number_format($commande['montant_total'], 2); ?> €</span></td>
                                                <td>
                                                    <?php
                                                    $badge_class = $commande['statut'] == 'paye' ? 'success' : ($commande['statut'] == 'en_attente' ? 'warning' : 'secondary');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo $commande['statut']; ?>
                                                    </span>
                                                 </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="commandes.php" class="btn btn-sm btn-outline-primary">Voir toutes les commandes</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">Aucune commande pour le moment.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Commentaires en attente -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-comment-dots"></i> Commentaires à modérer</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($commentaires_attente) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($commentaires_attente as $commentaire): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo htmlspecialchars($commentaire['prenom'] . ' ' . $commentaire['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted">Livre : <?php echo htmlspecialchars($commentaire['livre_titre']); ?></small>
                                                <p class="mb-0 small mt-1"><?php echo substr(htmlspecialchars($commentaire['commentaire']), 0, 100); ?>...</p>
                                            </div>
                                            <div>
                                                <a href="commentaires.php?action=valider&id=<?php echo $commentaire['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="commentaires.php?action=supprimer&id=<?php echo $commentaire['id']; ?>" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="commentaires.php" class="btn btn-sm btn-outline-primary">Gérer tous les commentaires</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">Aucun commentaire en attente de validation.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liens rapides -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt"></i> Actions rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="livres.php?action=ajouter" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter un livre
                                </a>
                                <a href="auteurs.php?action=ajouter" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Ajouter un auteur
                                </a>
                                <a href="nouvelles.php?action=ajouter" class="btn btn-info">
                                    <i class="fas fa-plus"></i> Ajouter une nouvelle
                                </a>
                                <a href="liseuse_config.php" class="btn btn-secondary">
                                    <i class="fas fa-book-open"></i> Gérer la liseuse
                                </a>
                                <a href="../livres/liste.php" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> Voir le catalogue
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>