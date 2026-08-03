<?php
// admin/auteur_form.php - Formulaire d'ajout/modification d'auteur

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Formulaire auteur - Administration";

$db = new Database();
$conn = $db->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($id > 0);

$auteur = [
    'id' => '',
    'nom' => '',
    'biographie' => '',
    'photo' => '',
    'seo_title' => '',
    'seo_description' => ''
];

$message = '';
$message_type = '';
$upload_error = '';

// Récupération de l'auteur si édition
if ($is_edit) {
    $sql = "SELECT * FROM auteurs WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $auteur_data = $stmt->fetch();
    if ($auteur_data) {
        $auteur = $auteur_data;
    } else {
        header('Location: auteurs.php');
        exit();
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auteur['nom'] = cleanSQL(trim($_POST['nom'] ?? ''));
    $auteur['biographie'] = cleanSQL(trim($_POST['biographie'] ?? ''));
    $auteur['seo_title'] = cleanSQL(trim($_POST['seo_title'] ?? ''));
    $auteur['seo_description'] = cleanSQL(trim($_POST['seo_description'] ?? ''));
    
    $erreurs = [];
    
    if (empty($auteur['nom'])) {
        $erreurs[] = "Le nom de l'auteur est requis.";
    }
    
    // Gestion de l'upload de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur lors de l'upload de la photo.";
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed)) {
                $erreurs[] = "Format de photo non autorisé (JPEG, PNG, WEBP).";
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $erreurs[] = "Le fichier est trop volumineux. Maximum 2 Mo.";
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'author_' . uniqid() . '_' . time() . '.' . $ext;
                $destination = '../assets/images/' . $filename;
                
                if (!is_dir('../assets/images/')) {
                    mkdir('../assets/images/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    if ($is_edit && !empty($auteur['photo']) && file_exists('../assets/images/' . $auteur['photo'])) {
                        unlink('../assets/images/' . $auteur['photo']);
                    }
                    $auteur['photo'] = $filename;
                } else {
                    $erreurs[] = "Erreur lors du déplacement du fichier.";
                }
            }
        }
    }
    
    // Suppression de la photo
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        if (!empty($auteur['photo']) && file_exists('../assets/images/' . $auteur['photo'])) {
            unlink('../assets/images/' . $auteur['photo']);
        }
        $auteur['photo'] = '';
    }
    
    if (empty($erreurs)) {
        if ($is_edit) {
            $sql = "UPDATE auteurs SET 
                    nom = :nom, 
                    biographie = :biographie, 
                    photo = :photo,
                    seo_title = :seo_title,
                    seo_description = :seo_description
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nom' => $auteur['nom'],
                ':biographie' => $auteur['biographie'],
                ':photo' => $auteur['photo'],
                ':seo_title' => $auteur['seo_title'],
                ':seo_description' => $auteur['seo_description'],
                ':id' => $id
            ]);
            $message = "Auteur modifié avec succès.";
        } else {
            $sql = "INSERT INTO auteurs (nom, biographie, photo, seo_title, seo_description, date_creation) 
                    VALUES (:nom, :biographie, :photo, :seo_title, :seo_description, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nom' => $auteur['nom'],
                ':biographie' => $auteur['biographie'],
                ':photo' => $auteur['photo'],
                ':seo_title' => $auteur['seo_title'],
                ':seo_description' => $auteur['seo_description']
            ]);
            $message = "Auteur ajouté avec succès.";
        }
        $message_type = "success";
        header("refresh:2;url=auteurs.php");
    } else {
        $message = implode('<br>', $erreurs);
        $message_type = "danger";
    }
}

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
            <h1 class="mb-4"><?php echo $is_edit ? 'Modifier l\'auteur' : 'Ajouter un auteur'; ?></h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nom de l'auteur *</label>
                                    <input type="text" name="nom" class="form-control" value="<?php echo cleanXSS($auteur['nom']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Biographie</label>
                                    <textarea name="biographie" rows="8" class="form-control" placeholder="Biographie de l'auteur..."><?php echo cleanXSS($auteur['biographie']); ?></textarea>
                                    <small class="text-muted">Vous pouvez utiliser du texte brut. Les retours à la ligne seront conservés.</small>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5>Référencement SEO</h5>
                                <div class="mb-3">
                                    <label class="form-label">Meta title</label>
                                    <input type="text" name="seo_title" class="form-control" value="<?php echo cleanXSS($auteur['seo_title']); ?>" placeholder="Titre pour les moteurs de recherche">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Meta description</label>
                                    <textarea name="seo_description" rows="3" class="form-control" placeholder="Description pour les moteurs de recherche (max 160 caractères)"><?php echo cleanXSS($auteur['seo_description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        Photo de l'auteur
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if (!empty($auteur['photo'])): ?>
                                            <div class="mb-3">
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($auteur['photo']); ?>" class="img-fluid rounded-circle" alt="<?php echo cleanXSS($auteur['nom']); ?>" style="width: 150px; height: 150px; object-fit: cover;">
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="delete_photo" id="delete_photo" value="1">
                                                <label class="form-check-label text-danger" for="delete_photo">
                                                    <i class="fas fa-trash"></i> Supprimer cette photo
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                                <i class="fas fa-user fa-4x"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Changer la photo</label>
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            <small class="text-muted">JPEG, PNG, WEBP. Max 2 Mo. Format carré recommandé.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Mettre à jour' : 'Ajouter'; ?></button>
                            <a href="auteurs.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>