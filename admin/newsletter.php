<?php
// admin/newsletter.php - Gestion des abonnés newsletter

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

redirigerSiNonAdmin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide.';
        $messageType = 'danger';
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            if (newsletter_delete((int)$_POST['id'])) {
                $message = 'Abonné supprimé définitivement. Un email de notification a été envoyé.';
                $messageType = 'success';
            } else {
                $message = 'Erreur lors de la suppression.';
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] === 'export') {
            $subscribers = newsletter_get_all(1, 10000);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=newsletter_export_' . date('Y-m-d') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email', 'Confirmé', 'Date inscription']);
            foreach ($subscribers as $s) {
                fputcsv($output, [
                    $s['email'],
                    $s['confirmed'] ? 'Oui' : 'Non',
                    $s['created_at']
                ]);
            }
            fclose($output);
            exit;
        }
    }
}

$page = max(1, (int)($_GET['p'] ?? 1));
$limit = 50;
$subscribers = newsletter_get_all($page, $limit);
$total = newsletter_count_all();
$totalPages = ceil($total / $limit);

// Stats
$sql = "SELECT COUNT(*) as total FROM newsletter WHERE deleted_at IS NULL AND confirmed = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$confirmedCount = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM newsletter WHERE deleted_at IS NULL AND confirmed = 0";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pendingCount = $stmt->fetch()['total'];

$page_title = 'Gestion Newsletter - Administration';
include 'menu.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-envelope-open-text"></i> Gestion de la newsletter</h1>
        <div>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <button type="submit" name="action" value="export" class="btn btn-success">
                    <i class="fas fa-download"></i> Exporter CSV
                </button>
            </form>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <?php echo cleanXSS($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?php echo $total; ?></h3>
                    <small>Abonnés totaux</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo $confirmedCount; ?></h3>
                    <small>Confirmés</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?php echo $pendingCount; ?></h3>
                    <small>En attente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des abonnés -->
    <div class="card">
        <div class="card-header">
            <h5>Liste des abonnés</h5>
        </div>
        <div class="card-body">
            <?php if (empty($subscribers)): ?>
                <p class="text-muted">Aucun abonné pour le moment.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo cleanXSS($s['email']); ?></td>
                                <td>
                                    <?php if ($s['confirmed']): ?>
                                        <span class="badge bg-success">Confirmé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer définitivement cet abonné ? Un email de notification sera envoyé.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?p=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
