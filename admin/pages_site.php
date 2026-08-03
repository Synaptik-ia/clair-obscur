<?php
// admin/pages_site.php - Gestion des pages du site (site_pages)

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Pages du site - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Action : régénérer les URLs depuis les auteurs, livres et nouvelles activés
if (isset($_POST['action']) && $_POST['action'] === 'regenerate') {
    $inserted = 0;
    $skipped = 0;

    // Pages statiques
    $static_pages = [
        SITE_URL,
        SITE_URL . 'auteurs/',
        SITE_URL . 'livres/liste.php',
        SITE_URL . 'nouvelles/',
        SITE_URL . 'contact/',
        SITE_URL . 'cgv/',
    ];

    foreach ($static_pages as $url) {
        $sql = "INSERT IGNORE INTO site_pages (url, parsed, created_at) VALUES (:url, 0, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':url' => $url]);
        if ($stmt->rowCount() > 0) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    // Auteurs (tous les auteurs sont considérés comme activés)
    $sql = "SELECT id FROM auteurs ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $auteurs = $stmt->fetchAll();
    foreach ($auteurs as $a) {
        $url = SITE_URL . 'auteurs/fiche.php?id=' . $a['id'];
        $sql_insert = "INSERT IGNORE INTO site_pages (url, parsed, created_at) VALUES (:url, 0, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':url' => $url]);
        if ($stmt_insert->rowCount() > 0) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    // Livres activés (statut_vente != 'non_vendable')
    $sql = "SELECT id FROM livres WHERE statut_vente != 'non_vendable' ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $livres = $stmt->fetchAll();
    foreach ($livres as $l) {
        $url = SITE_URL . 'livres/fiche.php?id=' . $l['id'];
        $sql_insert = "INSERT IGNORE INTO site_pages (url, parsed, created_at) VALUES (:url, 0, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':url' => $url]);
        if ($stmt_insert->rowCount() > 0) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    // Nouvelles (toutes les nouvelles sont considérées comme activées)
    $sql = "SELECT id FROM nouvelles ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $nouvelles = $stmt->fetchAll();
    foreach ($nouvelles as $n) {
        $url = SITE_URL . 'nouvelles/article.php?id=' . $n['id'];
        $sql_insert = "INSERT IGNORE INTO site_pages (url, parsed, created_at) VALUES (:url, 0, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':url' => $url]);
        if ($stmt_insert->rowCount() > 0) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    $message = "Régénération terminée : $inserted nouvelle(s) page(s) ajoutée(s), $skipped déjà existante(s).";
    $message_type = "success";
}

// Action : marquer comme parsé / non parsé
if (isset($_GET['toggle_parsed']) && is_numeric($_GET['toggle_parsed'])) {
    $id = (int)$_GET['toggle_parsed'];
    $sql = "UPDATE site_pages SET parsed = 1 - parsed WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Statut mis à jour.";
        $message_type = "success";
    }
}

// Action : suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM site_pages WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Page supprimée.";
        $message_type = "success";
    }
}

// Action : tout marquer comme non parsé
if (isset($_POST['action']) && $_POST['action'] === 'reset_all_parsed') {
    $sql = "UPDATE site_pages SET parsed = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $message = "Toutes les pages ont été marquées comme non parsées.";
    $message_type = "success";
}

// Pagination et filtres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

$parsed_filter = isset($_GET['parsed']) ? $_GET['parsed'] : '';

$where = "";
$params = [];
if ($parsed_filter !== '') {
    $where = " WHERE parsed = :parsed";
    $params[':parsed'] = (int)$parsed_filter;
}

$sql_count = "SELECT COUNT(*) as total FROM site_pages" . $where;
$sql_pages = "SELECT * FROM site_pages" . $where . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt_count = $conn->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt_count->execute();
$total_pages_count = $stmt_count->fetch()['total'];
$total_pages_nav = ceil($total_pages_count / $limit);

$stmt_pages = $conn->prepare($sql_pages);
foreach ($params as $key => $value) {
    $stmt_pages->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt_pages->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_pages->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_pages->execute();
$pages_list = $stmt_pages->fetchAll();

// Stats
$sql_stats = "SELECT parsed, COUNT(*) as total FROM site_pages GROUP BY parsed";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute();
$stats_data = $stmt_stats->fetchAll();
$stats = [0 => 0, 1 => 0];
foreach ($stats_data as $row) {
    $stats[(int)$row['parsed']] = $row['total'];
}

// Compteurs pour la régénération
$sql = "SELECT COUNT(*) as total FROM auteurs";
$stmt = $conn->prepare($sql); $stmt->execute();
$nb_auteurs = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM livres WHERE statut_vente != 'non_vendable'";
$stmt = $conn->prepare($sql); $stmt->execute();
$nb_livres = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM nouvelles";
$stmt = $conn->prepare($sql); $stmt->execute();
$nb_nouvelles = $stmt->fetch()['total'];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>

        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-sitemap"></i> Pages du site</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <!-- Cartes statistiques -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total pages</h6>
                            <h2 class="mb-0"><?php echo array_sum($stats); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Non parsées</h6>
                            <h2 class="mb-0"><?php echo $stats[0]; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">Parsées</h6>
                            <h2 class="mb-0"><?php echo $stats[1]; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title">À générer</h6>
                            <h2 class="mb-0"><?php echo $nb_auteurs + $nb_livres + $nb_nouvelles + 6; ?></h2>
                            <small><?php echo $nb_auteurs; ?> auteurs + <?php echo $nb_livres; ?> livres + <?php echo $nb_nouvelles; ?> nouvelles + 6 statiques</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="regenerate">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Régénérer les URLs depuis les auteurs, livres activés et nouvelles ? Les doublons seront ignorés.')">
                                <i class="fas fa-sync-alt"></i> Régénérer les URLs (auteurs + livres activés + nouvelles)
                            </button>
                        </form>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="reset_all_parsed">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Marquer TOUTES les pages comme non parsées ?')">
                                <i class="fas fa-undo"></i> Tout marquer non parsé
                            </button>
                        </form>
                    </div>
                    <small class="text-muted d-block mt-2">
                        La régénération crée les URLs pour : auteurs (tous), livres (en_vente + précommande uniquement), nouvelles (toutes) + pages statiques.
                        Les URLs déjà existantes sont ignorées. Toutes les nouvelles URLs sont créées avec <strong>parsed = 0</strong>.
                    </small>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label">Statut parsé</label>
                            <select name="parsed" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous</option>
                                <option value="0" <?php echo $parsed_filter === '0' ? 'selected' : ''; ?>>Non parsé</option>
                                <option value="1" <?php echo $parsed_filter === '1' ? 'selected' : ''; ?>>Parsé</option>
                            </select>
                        </div>
                        <?php if ($parsed_filter !== ''): ?>
                        <div class="col-auto">
                            <a href="pages_site.php" class="btn btn-outline-secondary">Réinitialiser</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Liste des pages -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Pages (<?php echo $total_pages_count; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($pages_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>URL</th>
                                        <th style="width: 100px;">Parsé</th>
                                        <th style="width: 150px;">Créé le</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pages_list as $p): ?>
                                    <tr class="<?php echo $p['parsed'] ? '' : 'table-warning'; ?>">
                                        <td><?php echo $p['id']; ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 500px;" title="<?php echo htmlspecialchars($p['url']); ?>">
                                                <a href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank" rel="noopener">
                                                    <?php echo htmlspecialchars($p['url']); ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($p['parsed']): ?>
                                                <span class="badge bg-success">Parsé</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Non parsé</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></small></td>
                                        <td>
                                            <a href="?toggle_parsed=<?php echo $p['id']; ?>&parsed=<?php echo $parsed_filter; ?>&page=<?php echo $page; ?>" class="btn btn-sm <?php echo $p['parsed'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $p['parsed'] ? 'Marquer non parsé' : 'Marquer parsé'; ?>">
                                                <i class="fas <?php echo $p['parsed'] ? 'fa-undo' : 'fa-check'; ?>"></i>
                                            </a>
                                            <a href="?delete=<?php echo $p['id']; ?>&parsed=<?php echo $parsed_filter; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer cette page ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages_nav > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&parsed=<?php echo $parsed_filter; ?>">Précédent</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages_nav; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&parsed=<?php echo $parsed_filter; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages_nav): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&parsed=<?php echo $parsed_filter; ?>">Suivant</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-sitemap fa-3x text-muted mb-3 d-block"></i>
                            <p>Aucune page trouvée. Utilisez le bouton "Régénérer les URLs" ci-dessus.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
