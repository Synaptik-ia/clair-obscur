<?php
// admin/liseuse_config.php - Configuration des livres/liseuses

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Configuration Liseuse - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';
$edit_livre_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Récupérer le livre à modifier
$edit_livre = null;
if ($edit_livre_id > 0) {
    $sql = "SELECT * FROM liseuse_livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $edit_livre_id]);
    $edit_livre = $stmt->fetch();
    if (!$edit_livre) {
        $edit_livre_id = 0;
    }
}

// Traitement du formulaire d'ajout/modification de livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_livre'])) {
    $id = (int)$_POST['livre_id'];
    $titre = cleanSQL(trim($_POST['titre'] ?? ''));
    $slug = cleanSQL(trim($_POST['slug'] ?? ''));
    $description = cleanSQL(trim($_POST['description'] ?? ''));
    
    $erreurs = [];
    
    if (empty($titre)) {
        $erreurs[] = "Le titre est requis.";
    }
    if (empty($slug)) {
        $erreurs[] = "Le slug est requis.";
    } else {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $slug));
        $slug = trim($slug, '-');
        
        // Vérifier l'unicité du slug
        $sql_check = "SELECT id FROM liseuse_livres WHERE slug = :slug";
        if ($id > 0) {
            $sql_check .= " AND id != :id";
        }
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':slug', $slug);
        if ($id > 0) {
            $stmt_check->bindParam(':id', $id);
        }
        $stmt_check->execute();
        if ($stmt_check->fetch()) {
            $erreurs[] = "Ce slug est déjà utilisé.";
        }
    }
    
    // Gestion des images
    $image_couverture = null;
    $image_4eme = null;
    
    // Upload image couverture
    if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image_couverture']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $erreurs[] = "Format de couverture non autorisé.";
        } else {
            $ext = pathinfo($_FILES['image_couverture']['name'], PATHINFO_EXTENSION);
            $filename = 'cover_' . uniqid() . '_' . time() . '.' . $ext;
            $destination = '../assets/images/' . $filename;
            
            if (!is_dir('../assets/images/')) {
                mkdir('../assets/images/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image_couverture']['tmp_name'], $destination)) {
                $image_couverture = $filename;
            }
        }
    }
    
    // Upload image 4ème
    if (isset($_FILES['image_4eme']) && $_FILES['image_4eme']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image_4eme']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $erreurs[] = "Format de 4ème couverture non autorisé.";
        } else {
            $ext = pathinfo($_FILES['image_4eme']['name'], PATHINFO_EXTENSION);
            $filename = 'back_' . uniqid() . '_' . time() . '.' . $ext;
            $destination = '../assets/images/' . $filename;
            
            if (move_uploaded_file($_FILES['image_4eme']['tmp_name'], $destination)) {
                $image_4eme = $filename;
            }
        }
    }
    
    if (empty($erreurs)) {
        if ($id > 0) {
            // Mise à jour
            $sql = "UPDATE liseuse_livres SET titre = :titre, slug = :slug, description = :description";
            if ($image_couverture) {
                $sql .= ", image_couverture = :image_couverture";
            }
            if ($image_4eme) {
                $sql .= ", image_4eme = :image_4eme";
            }
            $sql .= ", date_modification = NOW() WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':titre' => $titre,
                ':slug' => $slug,
                ':description' => $description,
                ':id' => $id
            ];
            if ($image_couverture) {
                $params[':image_couverture'] = $image_couverture;
                // Supprimer l'ancienne image
                if ($edit_livre && !empty($edit_livre['image_couverture']) && file_exists('../assets/images/' . $edit_livre['image_couverture'])) {
                    unlink('../assets/images/' . $edit_livre['image_couverture']);
                }
            }
            if ($image_4eme) {
                $params[':image_4eme'] = $image_4eme;
                if ($edit_livre && !empty($edit_livre['image_4eme']) && file_exists('../assets/images/' . $edit_livre['image_4eme'])) {
                    unlink('../assets/images/' . $edit_livre['image_4eme']);
                }
            }
            $stmt->execute($params);
            $message = "Livre modifié avec succès.";
        } else {
            // Création
            $sql = "INSERT INTO liseuse_livres (titre, slug, description, image_couverture, image_4eme, date_creation) 
                    VALUES (:titre, :slug, :description, :image_couverture, :image_4eme, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':titre' => $titre,
                ':slug' => $slug,
                ':description' => $description,
                ':image_couverture' => $image_couverture,
                ':image_4eme' => $image_4eme
            ]);
            $message = "Livre créé avec succès.";
        }
        $message_type = "success";
        
        // Redirection pour vider le formulaire
        header("Location: liseuse_config.php");
        exit();
    } else {
        $message = implode('<br>', $erreurs);
        $message_type = "danger";
    }
}

// Suppression d'un livre
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Récupérer les images
    $sql_img = "SELECT image_couverture, image_4eme FROM liseuse_livres WHERE id = :id";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->execute([':id' => $id]);
    $images = $stmt_img->fetch();
    
    if ($images) {
        if (!empty($images['image_couverture']) && file_exists('../assets/images/' . $images['image_couverture'])) {
            unlink('../assets/images/' . $images['image_couverture']);
        }
        if (!empty($images['image_4eme']) && file_exists('../assets/images/' . $images['image_4eme'])) {
            unlink('../assets/images/' . $images['image_4eme']);
        }
    }
    
    // Supprimer les pages associées
    $sql_pages = "SELECT image_page FROM liseuse_pages WHERE livre_id = :id";
    $stmt_pages = $conn->prepare($sql_pages);
    $stmt_pages->execute([':id' => $id]);
    $pages_images = $stmt_pages->fetchAll();
    foreach ($pages_images as $page_img) {
        if (!empty($page_img['image_page']) && file_exists('../assets/images/liseuse/' . $page_img['image_page'])) {
            unlink('../assets/images/liseuse/' . $page_img['image_page']);
        }
    }
    
    $sql = "DELETE FROM liseuse_livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        $message = "Livre supprimé avec succès.";
        $message_type = "success";
        header("Location: liseuse_config.php");
        exit();
    }
}

// Récupération des livres
$sql_livres = "SELECT * FROM liseuse_livres ORDER BY date_creation DESC";
$stmt_livres = $conn->prepare($sql_livres);
$stmt_livres->execute();
$livres = $stmt_livres->fetchAll();

include '../includes/header.php';
?>

<style>
    .edit-form {
        background-color: #f8f9fa;
        border-left: 4px solid #c9a03d;
    }
    .image-preview {
        max-width: 100px;
        max-height: 120px;
        margin-top: 10px;
    }
    .image-preview img {
        max-width: 100%;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 3px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar admin -->
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-book-open"></i> Configuration de la liseuse</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i> Retour au tableau de bord
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout/modification de livre -->
            <div class="card shadow-sm mb-4 <?php echo $edit_livre ? 'edit-form' : ''; ?>">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <?php if ($edit_livre): ?>
                            <i class="fas fa-edit"></i> Modifier le livre : <?php echo cleanXSS($edit_livre['titre']); ?>
                        <?php else: ?>
                            <i class="fas fa-plus"></i> Ajouter un nouveau livre
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="save_livre" value="1">
                        <input type="hidden" name="livre_id" value="<?php echo $edit_livre ? $edit_livre['id'] : 0; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre du livre *</label>
                                    <input type="text" name="titre" class="form-control" value="<?php echo $edit_livre ? cleanXSS($edit_livre['titre']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Slug (URL unique) *</label>
                                    <input type="text" name="slug" class="form-control" placeholder="ex: mon-livre" value="<?php echo $edit_livre ? cleanXSS($edit_livre['slug']) : ''; ?>" required>
                                    <small class="text-muted">Utilisé pour l'URL : /liseuse/&lt;slug&gt;</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control"><?php echo $edit_livre ? cleanXSS($edit_livre['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image de couverture (1ère page)</label>
                                    <input type="file" name="image_couverture" class="form-control" accept="image/*">
                                    <small class="text-muted">JPEG, PNG, WEBP. Max 2 Mo.</small>
                                    <?php if ($edit_livre && $edit_livre['image_couverture']): ?>
                                        <div class="image-preview">
                                            <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($edit_livre['image_couverture']); ?>" alt="Couverture actuelle">
                                            <small class="text-muted d-block">Image actuelle</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image 4ème de couverture (dernière page)</label>
                                    <input type="file" name="image_4eme" class="form-control" accept="image/*">
                                    <small class="text-muted">JPEG, PNG, WEBP. Max 2 Mo.</small>
                                    <?php if ($edit_livre && $edit_livre['image_4eme']): ?>
                                        <div class="image-preview">
                                            <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($edit_livre['image_4eme']); ?>" alt="4ème couverture actuelle">
                                            <small class="text-muted d-block">Image actuelle</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $edit_livre ? 'Mettre à jour' : 'Créer le livre'; ?>
                            </button>
                            <?php if ($edit_livre): ?>
                                <a href="liseuse_config.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler la modification
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des livres existants -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Livres disponibles</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($livres) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Couverture</th>
                                        <th>Titre</th>
                                        <th>Slug</th>
                                        <th>Pages</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($livres as $livre): 
                                        $sql_pages_count = "SELECT COUNT(*) as total FROM liseuse_pages WHERE livre_id = :id";
                                        $stmt_count = $conn->prepare($sql_pages_count);
                                        $stmt_count->execute([':id' => $livre['id']]);
                                        $page_count = $stmt_count->fetch()['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $livre['id']; ?></td>
                                        <td>
                                            <?php if ($livre['image_couverture']): ?>
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['image_couverture']); ?>" style="width: 40px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-book fa-2x text-muted"></i>
                                            <?php endif; ?>
                                         </span>
                                        <td><strong><?php echo cleanXSS($livre['titre']); ?></strong></td>
                                        <td><code><?php echo cleanXSS($livre['slug']); ?></code></td>
                                        <td><span class="badge bg-info"><?php echo $page_count; ?> pages</span></td>
                                        <td><?php echo date('d/m/Y', strtotime($livre['date_creation'])); ?></td>
                                        <td>
                                            <a href="liseuse_pages.php?livre_id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-primary" title="Gérer les pages">
                                                <i class="fas fa-pencil-alt"></i> Pages
                                            </a>
                                            <a href="liseuse_config.php?edit=<?php echo $livre['id']; ?>" class="btn btn-sm btn-warning" title="Modifier le livre">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>liseuse/<?php echo cleanXSS($livre['slug']); ?>" class="btn btn-sm btn-success" title="Lire le livre" target="_blank">
                                                <i class="fas fa-eye"></i> Lire
                                            </a>
                                            <a href="?delete=<?php echo $livre['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer ce livre et toutes ses pages ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                         </span>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                             </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p>Aucun livre configuré pour la liseuse.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>