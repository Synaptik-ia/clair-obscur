<?php
// admin/extraits.php - Gestion des extraits de livre

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

redirigerSiNonAdmin();

$page_title = "Extraits de livre - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM extraits_livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Extrait supprimé.";
        $message_type = "success";
    } else {
        $message = "Erreur lors de la suppression.";
        $message_type = "danger";
    }
}

// Toggle parsed
if (isset($_GET['toggle_parsed']) && is_numeric($_GET['toggle_parsed'])) {
    $id = (int)$_GET['toggle_parsed'];
    $sql = "UPDATE extraits_livres SET parsed = 1 - parsed WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Statut parsé mis à jour.";
        $message_type = "success";
    }
}

// Ajout / Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $contenu = $_POST['contenu'] ?? '';
    $parsed = isset($_POST['parsed']) ? 1 : 0;

    if (empty(trim($contenu))) {
        $message = "Le contenu de l'extrait est requis.";
        $message_type = "danger";
    } else {
        if ($edit_id > 0) {
            $sql = "UPDATE extraits_livres SET contenu = :contenu, parsed = :parsed WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':contenu' => $contenu,
                ':parsed' => $parsed,
                ':id' => $edit_id
            ]);
            $message = "Extrait modifié.";
        } else {
            $sql = "INSERT INTO extraits_livres (contenu, parsed, created_at) VALUES (:contenu, :parsed, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':contenu' => $contenu,
                ':parsed' => $parsed
            ]);
            $message = "Extrait ajouté.";
        }
        $message_type = "success";
    }
}

// Récupération de l'extrait à éditer
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM extraits_livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $edit_id]);
    $edit_data = $stmt->fetch();
}

// Pagination et filtres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$parsed_filter = isset($_GET['parsed']) ? $_GET['parsed'] : '';

$where = "";
$params = [];
if ($parsed_filter !== '') {
    $where = " WHERE parsed = :parsed";
    $params[':parsed'] = (int)$parsed_filter;
}

$sql_count = "SELECT COUNT(*) as total FROM extraits_livres" . $where;
$sql_list = "SELECT * FROM extraits_livres" . $where . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt_count->execute();
$total = $stmt_count->fetch()['total'];
$total_pages = ceil($total / $limit);

$stmt_list = $conn->prepare($sql_list);
foreach ($params as $key => $value) {
    $stmt_list->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt_list->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_list->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_list->execute();
$extraits = $stmt_list->fetchAll();

// Stats
$sql = "SELECT parsed, COUNT(*) as total FROM extraits_livres GROUP BY parsed";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stats_data = $stmt->fetchAll();
$stats = [0 => 0, 1 => 0];
foreach ($stats_data as $row) {
    $stats[(int)$row['parsed']] = $row['total'];
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>

        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-quote-right"></i> Extraits de livre</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total</h6>
                            <h2 class="mb-0"><?php echo array_sum($stats); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Non parsés</h6>
                            <h2 class="mb-0"><?php echo $stats[0]; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Parsés</h6>
                            <h2 class="mb-0"><?php echo $stats[1]; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Formulaire -->
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus'; ?>"></i>
                                <?php echo $edit_data ? 'Modifier l\'extrait' : 'Ajouter un extrait'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php if ($edit_data): ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Contenu de l'extrait *</label>
                                    <textarea name="contenu" rows="10" class="form-control" required><?php echo htmlspecialchars($edit_data['contenu'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="parsed" name="parsed" value="1" <?php echo ($edit_data && $edit_data['parsed']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="parsed">Parsé</label>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?php echo $edit_data ? 'Mettre à jour' : 'Ajouter'; ?>
                                    </button>
                                    <?php if ($edit_data): ?>
                                        <a href="extraits.php" class="btn btn-secondary">Annuler</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Liste -->
                <div class="col-lg-7">
                    <!-- Filtres -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-body py-2">
                            <form method="GET" action="" class="row g-2 align-items-end">
                                <div class="col-auto">
                                    <select name="parsed" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">Tous</option>
                                        <option value="0" <?php echo $parsed_filter === '0' ? 'selected' : ''; ?>>Non parsé</option>
                                        <option value="1" <?php echo $parsed_filter === '1' ? 'selected' : ''; ?>>Parsé</option>
                                    </select>
                                </div>
                                <?php if ($parsed_filter !== ''): ?>
                                <div class="col-auto">
                                    <a href="extraits.php" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Extraits (<?php echo $total; ?>)</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($extraits) > 0): ?>
                                <?php foreach ($extraits as $e): ?>
                                <div class="border-bottom p-3 <?php echo $e['parsed'] ? '' : 'bg-warning bg-opacity-10'; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-secondary me-2">#<?php echo $e['id']; ?></span>
                                            <?php if ($e['parsed']): ?>
                                                <span class="badge bg-success">Parsé</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Non parsé</span>
                                            <?php endif; ?>
                                            <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($e['created_at'])); ?></small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?toggle_parsed=<?php echo $e['id']; ?>&parsed=<?php echo $parsed_filter; ?>&page=<?php echo $page; ?>" class="btn <?php echo $e['parsed'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $e['parsed'] ? 'Marquer non parsé' : 'Marquer parsé'; ?>">
                                                <i class="fas <?php echo $e['parsed'] ? 'fa-undo' : 'fa-check'; ?>"></i>
                                            </a>
                                            <a href="?edit=<?php echo $e['id']; ?>&parsed=<?php echo $parsed_filter; ?>&page=<?php echo $page; ?>" class="btn btn-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $e['id']; ?>&parsed=<?php echo $parsed_filter; ?>&page=<?php echo $page; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Supprimer cet extrait ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="text-truncate" style="max-height: 60px; overflow: hidden;" title="<?php echo htmlspecialchars($e['contenu']); ?>">
                                        <?php echo nl2br(htmlspecialchars(substr($e['contenu'], 0, 200))); ?><?php echo strlen($e['contenu']) > 200 ? '...' : ''; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <?php if ($total_pages > 1): ?>
                                <div class="card-footer">
                                    <nav>
                                        <ul class="pagination justify-content-center mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&parsed=<?php echo $parsed_filter; ?>">Précédent</a></li>
                                            <?php endif; ?>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&parsed=<?php echo $parsed_filter; ?>"><?php echo $i; ?></a></li>
                                            <?php endfor; ?>
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&parsed=<?php echo $parsed_filter; ?>">Suivant</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="text-center p-4 text-muted">
                                    <i class="fas fa-quote-right fa-3x mb-3 d-block"></i>
                                    Aucun extrait trouvé.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
