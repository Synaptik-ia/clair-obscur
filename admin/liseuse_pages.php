<?php
// admin/liseuse_pages.php - Gestion des pages d'un livre avec upload d'images

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$livre_id = isset($_GET['livre_id']) ? (int)$_GET['livre_id'] : 0;
$edit_page_id = isset($_GET['edit_page']) ? (int)$_GET['edit_page'] : 0;

if ($livre_id <= 0) {
    header('Location: liseuse_config.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Récupérer les infos du livre
$sql_livre = "SELECT * FROM liseuse_livres WHERE id = :id";
$stmt_livre = $conn->prepare($sql_livre);
$stmt_livre->execute([':id' => $livre_id]);
$livre = $stmt_livre->fetch();

if (!$livre) {
    header('Location: liseuse_config.php');
    exit();
}

$page_title = "Pages de " . $livre['titre'] . " - Administration";
$message = '';
$message_type = '';

// Récupération des pages existantes
$sql_pages = "SELECT * FROM liseuse_pages WHERE livre_id = :livre_id ORDER BY page_num ASC";
$stmt_pages = $conn->prepare($sql_pages);
$stmt_pages->execute([':livre_id' => $livre_id]);
$pages = $stmt_pages->fetchAll();

// Récupération de la page à modifier
$edit_page = null;
if ($edit_page_id > 0) {
    $sql_edit = "SELECT * FROM liseuse_pages WHERE id = :id AND livre_id = :livre_id";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->execute([':id' => $edit_page_id, ':livre_id' => $livre_id]);
    $edit_page = $stmt_edit->fetch();
}

// Traitement du formulaire d'ajout/modification de page (avec image)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page'])) {
    $page_num = (int)$_POST['page_num'];
    $titre = cleanSQL(trim($_POST['titre'] ?? ''));
    $image_page = '';
    
    $erreurs = [];
    
    if (empty($page_num) || $page_num < 1) {
        $erreurs[] = "Le numéro de page est requis.";
    }
    
    // Gestion de l'upload d'image
    if (isset($_FILES['image_page']) && $_FILES['image_page']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image_page']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $erreurs[] = "Format d'image non autorisé (JPEG, PNG, WEBP, GIF).";
        } elseif ($_FILES['image_page']['size'] > 10 * 1024 * 1024) {
            $erreurs[] = "L'image est trop volumineuse. Maximum 10 Mo.";
        } else {
            $ext = pathinfo($_FILES['image_page']['name'], PATHINFO_EXTENSION);
            $filename = 'page_' . $livre_id . '_' . $page_num . '_' . time() . '.' . $ext;
            $destination = '../assets/images/liseuse/' . $filename;
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir('../assets/images/liseuse/')) {
                mkdir('../assets/images/liseuse/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image_page']['tmp_name'], $destination)) {
                $image_page = $filename;
            } else {
                $erreurs[] = "Erreur lors de l'upload de l'image.";
            }
        }
    } elseif (!$edit_page && empty($_FILES['image_page']['name'])) {
        // Pour une nouvelle page, l'image est obligatoire
        $erreurs[] = "L'image de la page est requise.";
    }
    
    // Vérifier que le numéro de page n'existe pas déjà (sauf pour modification)
    if ($edit_page_id <= 0) {
        $sql_check = "SELECT id FROM liseuse_pages WHERE livre_id = :livre_id AND page_num = :page_num";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([
            ':livre_id' => $livre_id,
            ':page_num' => $page_num
        ]);
        if ($stmt_check->fetch()) {
            $erreurs[] = "Une page avec ce numéro existe déjà.";
        }
    }
    
    if (empty($erreurs)) {
        try {
            if ($edit_page_id > 0) {
                // Mise à jour - ne pas changer l'image si aucune nouvelle n'est fournie
                $sql = "UPDATE liseuse_pages SET 
                        page_num = :page_num, 
                        titre = :titre";
                
                if (!empty($image_page)) {
                    // Supprimer l'ancienne image
                    if (!empty($edit_page['image_page']) && file_exists('../assets/images/liseuse/' . $edit_page['image_page'])) {
                        unlink('../assets/images/liseuse/' . $edit_page['image_page']);
                    }
                    $sql .= ", image_page = :image_page";
                } else {
                    $sql .= ", image_page = image_page";
                }
                
                $sql .= " WHERE id = :id AND livre_id = :livre_id";
                $stmt = $conn->prepare($sql);
                
                $params = [
                    ':page_num' => $page_num,
                    ':titre' => $titre,
                    ':id' => $edit_page_id,
                    ':livre_id' => $livre_id
                ];
                if (!empty($image_page)) {
                    $params[':image_page'] = $image_page;
                }
                $stmt->execute($params);
                $message = "Page modifiée avec succès.";
            } else {
                // Nouvelle page
                $sql = "INSERT INTO liseuse_pages (livre_id, page_num, titre, image_page) 
                        VALUES (:livre_id, :page_num, :titre, :image_page)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':livre_id' => $livre_id,
                    ':page_num' => $page_num,
                    ':titre' => $titre,
                    ':image_page' => $image_page
                ]);
                $message = "Page ajoutée avec succès.";
            }
            $message_type = "success";
            
            // Redirection pour vider le formulaire
            header("Location: liseuse_pages.php?livre_id=" . $livre_id);
            exit();
        } catch (PDOException $e) {
            $message = "Erreur base de données : " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = implode('<br>', $erreurs);
        $message_type = "danger";
    }
}

// Suppression d'une page (supprime aussi l'image)
if (isset($_GET['delete_page']) && is_numeric($_GET['delete_page'])) {
    $page_id = (int)$_GET['delete_page'];
    
    // Récupérer l'image pour la supprimer
    $sql_img = "SELECT image_page FROM liseuse_pages WHERE id = :id AND livre_id = :livre_id";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->execute([':id' => $page_id, ':livre_id' => $livre_id]);
    $page_img = $stmt_img->fetch();
    
    if ($page_img && !empty($page_img['image_page']) && file_exists('../assets/images/liseuse/' . $page_img['image_page'])) {
        unlink('../assets/images/liseuse/' . $page_img['image_page']);
    }
    
    $sql = "DELETE FROM liseuse_pages WHERE id = :id AND livre_id = :livre_id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $page_id, ':livre_id' => $livre_id])) {
        $message = "Page supprimée avec succès.";
        $message_type = "success";
        header("Location: liseuse_pages.php?livre_id=" . $livre_id);
        exit();
    } else {
        $message = "Erreur lors de la suppression.";
        $message_type = "danger";
    }
}

include '../includes/header.php';
?>

<style>
    .image-preview {
        max-width: 200px;
        max-height: 280px;
        margin-top: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        background: #f5f5f5;
    }
    .image-preview img {
        max-width: 100%;
        height: auto;
        display: block;
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
                <h1><i class="fas fa-book-open"></i> Pages de : <?php echo cleanXSS($livre['titre']); ?></h1>
                <div>
                    <a href="liseuse_config.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="<?php echo SITE_URL; ?>liseuse/<?php echo cleanXSS($livre['slug']); ?>" class="btn btn-success ms-2" target="_blank">
                        <i class="fas fa-eye"></i> Voir le livre
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout/modification de page avec upload d'image -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <?php echo $edit_page ? 'Modifier la page' : 'Ajouter une nouvelle page'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="pageForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="save_page" value="1">
                        
                        <div class="row">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Numéro de page *</label>
                                    <input type="number" name="page_num" id="page_num" class="form-control" value="<?php echo $edit_page ? $edit_page['page_num'] : (count($pages) + 1); ?>" required min="1">
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <label class="form-label">Titre de la page (optionnel)</label>
                                    <input type="text" name="titre" id="titre" class="form-control" value="<?php echo $edit_page ? cleanXSS($edit_page['titre']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image de la page *</label>
                            <input type="file" name="image_page" id="image_page" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" <?php echo !$edit_page ? 'required' : ''; ?>>
                            <small class="text-muted">Formats acceptés : JPEG, PNG, WEBP, GIF. Max 10 Mo. Format recommandé : 800x1100px (proportions livre).</small>
                            
                            <?php if ($edit_page && !empty($edit_page['image_page'])): ?>
                                <div class="image-preview mt-2">
                                    <img src="<?php echo SITE_URL . 'assets/images/liseuse/' . cleanXSS($edit_page['image_page']); ?>" alt="Aperçu">
                                    <div class="mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_image" id="delete_image" value="1">
                                            <label class="form-check-label text-danger" for="delete_image">
                                                <i class="fas fa-trash"></i> Supprimer cette image
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $edit_page ? 'Mettre à jour' : 'Ajouter la page'; ?>
                            </button>
                            
                            <?php if ($edit_page): ?>
                                <a href="liseuse_pages.php?livre_id=<?php echo $livre_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler la modification
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des pages existantes -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Pages du livre (<?php echo count($pages); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($pages) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80">Page</th>
                                        <th width="100">Aperçu</th>
                                        <th>Titre</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pages as $page): ?>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-secondary"><?php echo $page['page_num']; ?></span></td>
                                        <td>
                                            <?php if ($page['image_page']): ?>
                                                <img src="<?php echo SITE_URL . 'assets/images/liseuse/' . cleanXSS($page['image_page']); ?>" style="width: 50px; height: 70px; object-fit: cover; border-radius: 3px;">
                                            <?php else: ?>
                                                <span class="text-muted">Aucune image</span>
                                            <?php endif; ?>
                                         </span>
                                        <td><?php echo cleanXSS($page['titre']) ?: '<em class="text-muted">Sans titre</em>'; ?></td>
                                        <td>
                                            <a href="?livre_id=<?php echo $livre_id; ?>&edit_page=<?php echo $page['id']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?livre_id=<?php echo $livre_id; ?>&delete_page=<?php echo $page['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer cette page définitivement ?')">
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
                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                            <p>Aucune page pour ce livre. Commencez par en ajouter une !</p>
                            <small class="text-muted">Les images doivent être au format paysage ou portrait (recommandé 800x1100px).</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations sur les dimensions recommandées -->
            <div class="card shadow-sm mt-4 bg-light">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Recommandations pour les images</h6>
                    <ul class="small mb-0">
                        <li><strong>Dimensions recommandées :</strong> 800px de large × 1100px de haut (proportions 1:1.4)</li>
                        <li><strong>Format :</strong> JPG ou PNG pour une meilleure qualité</li>
                        <li><strong>Poids :</strong> Moins de 5 Mo par image pour des performances optimales</li>
                        <li><strong>Résolution :</strong> 150 DPI minimum pour un affichage net sur écran</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Aperçu de l'image avant upload
    document.getElementById('image_page').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(event) {
                // Vérifier si une prévisualisation existe déjà
                var preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'image-preview mt-2';
                    document.querySelector('#image_page').parentNode.appendChild(preview);
                }
                preview.innerHTML = '<img src="' + event.target.result + '" style="max-width:200px; max-height:280px;">';
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include '../includes/footer.php'; ?>