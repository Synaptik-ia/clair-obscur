<?php
// admin/clients.php - Gestion des clients

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des clients - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Suppression d'un client
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $sql_check = "SELECT is_admin FROM utilisateurs WHERE id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':id' => $id]);
    $user = $stmt_check->fetch();
    
    if ($user && $user['is_admin'] == 1) {
        $message = "Impossible de supprimer un administrateur.";
        $message_type = "danger";
    } else {
        $sql_cmd = "SELECT COUNT(*) as total FROM commandes WHERE utilisateur_id = :id";
        $stmt_cmd = $conn->prepare($sql_cmd);
        $stmt_cmd->execute([':id' => $id]);
        $nb_commandes = $stmt_cmd->fetch()['total'];
        
        if ($nb_commandes > 0) {
            $message = "Impossible de supprimer ce client car il a $nb_commandes commande(s) associée(s).";
            $message_type = "danger";
        } else {
            $sql_delete = "DELETE FROM utilisateurs WHERE id = :id AND is_admin = 0";
            $stmt_delete = $conn->prepare($sql_delete);
            if ($stmt_delete->execute([':id' => $id])) {
                $message = "Client supprimé avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la suppression.";
                $message_type = "danger";
            }
        }
    }
}

// Récupération de la liste des clients
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? cleanSQL(trim($_GET['search'])) : '';

$sql_count = "SELECT COUNT(*) as total FROM utilisateurs WHERE is_admin = 0";
$sql_clients = "SELECT * FROM utilisateurs WHERE is_admin = 0";

if (!empty($search)) {
    $where = " AND (email LIKE :search OR nom LIKE :search OR prenom LIKE :search OR ville LIKE :search)";
    $sql_count .= $where;
    $sql_clients .= $where;
}

$sql_clients .= " ORDER BY date_inscription DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
if (!empty($search)) {
    $stmt_count->bindValue(':search', "%$search%");
}
$stmt_count->execute();
$total_clients = $stmt_count->fetch()['total'];
$total_pages = ceil($total_clients / $limit);

$stmt_clients = $conn->prepare($sql_clients);
if (!empty($search)) {
    $stmt_clients->bindValue(':search', "%$search%");
}
$stmt_clients->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_clients->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_clients->execute();
$clients = $stmt_clients->fetchAll();

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
                <h1><i class="fas fa-user-friends"></i> Gestion des clients</h1>
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
                            <input type="text" name="search" class="form-control" placeholder="Rechercher par email, nom, prénom ou ville..." value="<?php echo cleanXSS($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total clients</h6>
                            <h2 class="mb-0"><?php echo $total_clients; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Avec adresse renseignée</h6>
                            <?php
                            $sql_addr = "SELECT COUNT(*) as total FROM utilisateurs WHERE is_admin = 0 AND adresse IS NOT NULL AND adresse != ''";
                            $stmt_addr = $conn->prepare($sql_addr);
                            $stmt_addr->execute();
                            $avec_adresse = $stmt_addr->fetch()['total'];
                            ?>
                            <h2 class="mb-0"><?php echo $avec_adresse; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Avec commande</h6>
                            <?php
                            $sql_cmd = "SELECT COUNT(DISTINCT utilisateur_id) as total FROM commandes";
                            $stmt_cmd = $conn->prepare($sql_cmd);
                            $stmt_cmd->execute();
                            $avec_commande = $stmt_cmd->fetch()['total'];
                            ?>
                            <h2 class="mb-0"><?php echo $avec_commande; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des clients -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Liste des clients (<?php echo $total_clients; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($clients) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Nom complet</th>
                                        <th>Téléphone</th>
                                        <th>Ville</th>
                                        <th>Pays</th>
                                        <th>Date inscription</th>
                                        <th>Commandes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): 
                                        $sql_cmd_count = "SELECT COUNT(*) as total FROM commandes WHERE utilisateur_id = :id";
                                        $stmt_cmd_count = $conn->prepare($sql_cmd_count);
                                        $stmt_cmd_count->execute([':id' => $client['id']]);
                                        $nb_commandes = $stmt_cmd_count->fetch()['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $client['id']; ?></span></td>
                                        <td><?php echo cleanXSS($client['email']); ?></span></td>
                                        <td><?php echo cleanXSS($client['prenom'] . ' ' . $client['nom']); ?></span></td>
                                        <td><?php echo cleanXSS($client['telephone'] ?? '-'); ?></span></td>
                                        <td><?php echo cleanXSS($client['ville'] ?? '-'); ?></span></td>
                                        <td><?php echo cleanXSS($client['pays'] ?? 'France'); ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?></span></td>
                                        <td>
                                            <a href="commandes.php?user_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-info">
                                                <?php echo $nb_commandes; ?> commande(s)
                                            </a>
                                         </span>
                                        <td>
                                            <a href="client_detail.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?delete=<?php echo $client['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce client définitivement ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                         </span>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                              </table>
                        </div>
                        
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
                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                            <p>Aucun client trouvé.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>