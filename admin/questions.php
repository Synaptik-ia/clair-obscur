<?php
// admin/questions.php - Gestion des questions non répondues

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Gestion des questions - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = (int)$_POST['id'];

        if ($_POST['action'] === 'answer' && !empty($_POST['answer'])) {
            $answer = trim($_POST['answer']);
            $sql = "UPDATE unanswered_questions SET answer = :answer, status = 'answered', answered_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([':answer' => $answer, ':id' => $id])) {
                $message = "Réponse enregistrée avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de l'enregistrement de la réponse.";
                $message_type = "danger";
            }
        } elseif ($_POST['action'] === 'close') {
            $sql = "UPDATE unanswered_questions SET status = 'closed' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([':id' => $id])) {
                $message = "Question fermée avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la fermeture de la question.";
                $message_type = "danger";
            }
        } elseif ($_POST['action'] === 'reopen') {
            $sql = "UPDATE unanswered_questions SET status = 'pending' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([':id' => $id])) {
                $message = "Question rouverte avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la réouverture de la question.";
                $message_type = "danger";
            }
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM unanswered_questions WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([':id' => $id])) {
                $message = "Question supprimée avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la suppression.";
                $message_type = "danger";
            }
        }
    }
}

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Construction des requêtes
$where = "";
$params = [];

if (!empty($status_filter) && in_array($status_filter, ['pending', 'answered', 'closed'])) {
    $where = " WHERE status = :status";
    $params[':status'] = $status_filter;
}

$sql_count = "SELECT COUNT(*) as total FROM unanswered_questions" . $where;
$sql_questions = "SELECT * FROM unanswered_questions" . $where . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}
$stmt_count->execute();
$total_questions = $stmt_count->fetch()['total'];
$total_pages = ceil($total_questions / $limit);

$stmt_questions = $conn->prepare($sql_questions);
foreach ($params as $key => $value) {
    $stmt_questions->bindValue($key, $value);
}
$stmt_questions->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_questions->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_questions->execute();
$questions = $stmt_questions->fetchAll();

// Statistiques
$sql_stats = "SELECT status, COUNT(*) as total FROM unanswered_questions GROUP BY status";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute();
$stats_data = $stmt_stats->fetchAll();
$stats = ['pending' => 0, 'answered' => 0, 'closed' => 0];
foreach ($stats_data as $row) {
    $stats[$row['status']] = $row['total'];
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>

        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-question-circle"></i> Gestion des questions</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <!-- Cartes statistiques -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">En attente</h6>
                            <h2 class="mb-0"><?php echo $stats['pending']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Répondues</h6>
                            <h2 class="mb-0"><?php echo $stats['answered']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-secondary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Fermées</h6>
                            <h2 class="mb-0"><?php echo $stats['closed']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total</h6>
                            <h2 class="mb-0"><?php echo array_sum($stats); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                <option value="answered" <?php echo $status_filter === 'answered' ? 'selected' : ''; ?>>Répondues</option>
                                <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Fermées</option>
                            </select>
                        </div>
                        <?php if (!empty($status_filter)): ?>
                        <div class="col-auto">
                            <a href="questions.php" class="btn btn-outline-secondary">Réinitialiser</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Liste des questions -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Questions (<?php echo $total_questions; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($questions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Question</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($questions as $q): ?>
                                    <tr class="<?php echo $q['status'] === 'pending' ? 'table-warning' : ($q['status'] === 'answered' ? 'table-success' : ''); ?>">
                                        <td><?php echo $q['id']; ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($q['question']); ?>">
                                                <?php echo htmlspecialchars($q['question']); ?>
                                            </div>
                                            <?php if (!empty($q['answer'])): ?>
                                                <small class="text-success d-block mt-1">
                                                    <strong>Réponse :</strong> <?php echo htmlspecialchars(substr($q['answer'], 0, 100)); ?><?php echo strlen($q['answer']) > 100 ? '...' : ''; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($q['email']); ?></td>
                                        <td>
                                            <?php
                                            $badge = 'secondary';
                                            $label = 'Fermée';
                                            if ($q['status'] === 'pending') { $badge = 'warning text-dark'; $label = 'En attente'; }
                                            elseif ($q['status'] === 'answered') { $badge = 'success'; $label = 'Répondue'; }
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $label; ?></span>
                                        </td>
                                        <td><small><?php echo date('d/m/Y H:i', strtotime($q['created_at'])); ?></small></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal<?php echo $q['id']; ?>" title="Voir / Répondre">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($q['status'] === 'pending'): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                                <input type="hidden" name="action" value="close">
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Fermer" onclick="return confirm('Fermer cette question ?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <?php elseif ($q['status'] === 'closed'): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                                <input type="hidden" name="action" value="reopen">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Rouvrir">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer définitivement cette question ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>">Précédent</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>">Suivant</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p>Aucune question trouvée.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour chaque question -->
<?php foreach ($questions as $q): ?>
<div class="modal fade" id="questionModal<?php echo $q['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-question-circle"></i> Question #<?php echo $q['id']; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Email :</strong> <?php echo htmlspecialchars($q['email']); ?>
                </div>
                <div class="mb-3">
                    <strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($q['created_at'])); ?>
                </div>
                <div class="mb-3">
                    <strong>Statut :</strong>
                    <?php
                    $badge = 'secondary'; $label = 'Fermée';
                    if ($q['status'] === 'pending') { $badge = 'warning text-dark'; $label = 'En attente'; }
                    elseif ($q['status'] === 'answered') { $badge = 'success'; $label = 'Répondue'; }
                    ?>
                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $label; ?></span>
                </div>
                <div class="mb-3 p-3 bg-light rounded">
                    <strong>Question :</strong>
                    <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($q['question'])); ?></p>
                </div>

                <?php if (!empty($q['answer'])): ?>
                <div class="mb-3 p-3 bg-success bg-opacity-10 rounded">
                    <strong>Réponse :</strong>
                    <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($q['answer'])); ?></p>
                    <?php if (!empty($q['answered_at'])): ?>
                        <small class="text-muted">Répondu le <?php echo date('d/m/Y à H:i', strtotime($q['answered_at'])); ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($q['status'] !== 'closed'): ?>
                <form method="POST" action="" class="mt-3">
                    <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                    <input type="hidden" name="action" value="answer">
                    <div class="mb-3">
                        <label class="form-label"><strong><?php echo !empty($q['answer']) ? 'Modifier la réponse' : 'Votre réponse'; ?> :</strong></label>
                        <textarea name="answer" rows="4" class="form-control" required><?php echo htmlspecialchars($q['answer'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> <?php echo !empty($q['answer']) ? 'Mettre à jour la réponse' : 'Envoyer la réponse'; ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
