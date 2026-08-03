<?php
// admin/client_detail.php - Détail d'un client pour l'administrateur

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Détail client - Administration";

$db = new Database();
$conn = $db->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: clients.php');
    exit();
}

// Récupération des informations du client
$sql = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: clients.php');
    exit();
}

// Récupération des commandes du client
$sql_commandes = "SELECT * FROM commandes WHERE utilisateur_id = :id ORDER BY date_commande DESC";
$stmt_commandes = $conn->prepare($sql_commandes);
$stmt_commandes->execute([':id' => $id]);
$commandes = $stmt_commandes->fetchAll();

// Récupération des commentaires du client
$sql_commentaires = "SELECT c.*, l.titre as livre_titre 
                     FROM commentaires c 
                     JOIN livres l ON c.livre_id = l.id 
                     WHERE c.utilisateur_id = :id 
                     ORDER BY c.date_creation DESC 
                     LIMIT 10";
$stmt_commentaires = $conn->prepare($sql_commentaires);
$stmt_commentaires->execute([':id' => $id]);
$commentaires = $stmt_commentaires->fetchAll();

// Statistiques
$total_commandes = count($commandes);
$total_depenses = 0;
foreach ($commandes as $commande) {
    if ($commande['statut'] == 'paye') {
        $total_depenses += $commande['montant_total'];
    }
}

$sql_count_commentaires = "SELECT COUNT(*) as total FROM commentaires WHERE utilisateur_id = :id";
$stmt_count = $conn->prepare($sql_count_commentaires);
$stmt_count->execute([':id' => $id]);
$total_commentaires = $stmt_count->fetch()['total'];

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
                <h1><i class="fas fa-user"></i> Détail du client</h1>
                <a href="clients.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
            
            <div class="row">
                <!-- Informations personnelles -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card"></i> Informations personnelles</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="35%">ID client :</th>
                                    <td><?php echo $client['id']; ?></span></td>
                                </tr>
                                <tr>
                                    <th>Nom complet :</th>
                                    <td><?php echo cleanXSS($client['prenom'] . ' ' . $client['nom']); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Email :</th>
                                    <td><a href="mailto:<?php echo cleanXSS($client['email']); ?>"><?php echo cleanXSS($client['email']); ?></a></span></td>
                                </tr>
                                <tr>
                                    <th>Téléphone :</th>
                                    <td><?php echo cleanXSS($client['telephone'] ?? 'Non renseigné'); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Date d'inscription :</th>
                                    <td><?php echo date('d/m/Y H:i', strtotime($client['date_inscription'])); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Statut :</th>
                                    <td>
                                        <?php if ($client['is_admin'] == 1): ?>
                                            <span class="badge bg-primary">Administrateur</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Client</span>
                                        <?php endif; ?>
                                     </span>
                                </tr>
                             </table>
                        </div>
                    </div>
                </div>
                
                <!-- Adresse de livraison -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-home"></i> Adresse de livraison</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($client['adresse']): ?>
                                <p>
                                    <?php echo nl2br(cleanXSS($client['adresse'])); ?><br>
                                    <?php echo cleanXSS($client['code_postal'] . ' ' . $client['ville']); ?><br>
                                    <?php echo cleanXSS($client['pays'] ?? 'France'); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted">Aucune adresse renseignée.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ... suite du fichier inchangée ... -->
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>