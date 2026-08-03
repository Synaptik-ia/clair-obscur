<?php
// admin/nouvelle_form.php - Formulaire d'ajout/modification de nouvelle avec CKEditor 5

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Formulaire nouvelle - Administration";

$db = new Database();
$conn = $db->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($id > 0);

$nouvelle = [
    'id' => '',
    'titre' => '',
    'contenu' => '',
    'image' => '',
    'seo_title' => '',
    'seo_description' => ''
];

$message = '';
$message_type = '';
$upload_error = '';

// Récupération de la nouvelle si édition
if ($is_edit) {
    $sql = "SELECT * FROM nouvelles WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $nouvelle_data = $stmt->fetch();
    if ($nouvelle_data) {
        $nouvelle = $nouvelle_data;
    } else {
        header('Location: nouvelles.php');
        exit();
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelle['titre'] = cleanSQL(trim($_POST['titre'] ?? ''));
    // NE PAS nettoyer avec cleanXSS ici car cela encode le HTML
    $nouvelle['contenu'] = $_POST['contenu'] ?? '';
    $nouvelle['seo_title'] = cleanSQL(trim($_POST['seo_title'] ?? ''));
    $nouvelle['seo_description'] = cleanSQL(trim($_POST['seo_description'] ?? ''));
    
    // Protection contre les injections XSS
    $allowed_tags = '<p><br><strong><em><i><b><u><ul><li><ol><h1><h2><h3><h4><h5><h6><a><img><td><th><thead><tbody><blockquote><code><pre><span><div><figure><figcaption>';
    $nouvelle['contenu'] = strip_tags($nouvelle['contenu'], $allowed_tags);
    
    $erreurs = [];
    
    if (empty($nouvelle['titre'])) {
        $erreurs[] = "Le titre est requis.";
    }
    if (empty($nouvelle['contenu'])) {
        $erreurs[] = "Le contenu est requis.";
    }
    
    // Gestion de l'upload de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $erreurs[] = "Format d'image non autorisé (JPEG, PNG, WEBP).";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $erreurs[] = "L'image est trop volumineuse. Maximum 5 Mo.";
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'news_' . uniqid() . '_' . time() . '.' . $ext;
            $destination = '../assets/images/' . $filename;
            
            if (!is_dir('../assets/images/')) {
                mkdir('../assets/images/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                if ($is_edit && !empty($nouvelle['image']) && file_exists('../assets/images/' . $nouvelle['image'])) {
                    unlink('../assets/images/' . $nouvelle['image']);
                }
                $nouvelle['image'] = $filename;
            } else {
                $erreurs[] = "Erreur lors de l'upload de l'image.";
            }
        }
    }
    
    // Suppression de l'image existante
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if (!empty($nouvelle['image']) && file_exists('../assets/images/' . $nouvelle['image'])) {
            unlink('../assets/images/' . $nouvelle['image']);
        }
        $nouvelle['image'] = '';
    }
    
    if (empty($erreurs)) {
        if ($is_edit) {
            $sql = "UPDATE nouvelles SET 
                    titre = :titre, 
                    contenu = :contenu, 
                    image = :image,
                    seo_title = :seo_title,
                    seo_description = :seo_description
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':titre' => $nouvelle['titre'],
                ':contenu' => $nouvelle['contenu'],
                ':image' => $nouvelle['image'],
                ':seo_title' => $nouvelle['seo_title'],
                ':seo_description' => $nouvelle['seo_description'],
                ':id' => $id
            ]);
            $message = "Nouvelle modifiée avec succès.";
        } else {
            $sql = "INSERT INTO nouvelles (titre, contenu, image, seo_title, seo_description, date_publication) 
                    VALUES (:titre, :contenu, :image, :seo_title, :seo_description, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':titre' => $nouvelle['titre'],
                ':contenu' => $nouvelle['contenu'],
                ':image' => $nouvelle['image'],
                ':seo_title' => $nouvelle['seo_title'],
                ':seo_description' => $nouvelle['seo_description']
            ]);
            $message = "Nouvelle ajoutée avec succès.";
        }
        $message_type = "success";
        
        header("refresh:2;url=nouvelles.php");
    } else {
        $message = implode('<br>', $erreurs);
        $message_type = "danger";
    }
}

include '../includes/header.php';
?>

<!-- CKEditor 5 - Version Classic sans plugins problématiques -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<style>
    .ck-editor__editable {
        min-height: 500px !important;
        max-height: 700px !important;
    }
    .ck-editor__editable_inline {
        padding: 20px !important;
    }
    /* Masquer le textarea original mais le garder dans le flux */
    #contenu {
        visibility: hidden;
        height: 0;
        width: 0;
        position: absolute;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <!-- Contenu -->
        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><?php echo $is_edit ? 'Modifier la nouvelle' : 'Ajouter une nouvelle'; ?></h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="newsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" name="titre" id="titre" class="form-control" value="<?php echo htmlspecialchars_decode(cleanXSS($nouvelle['titre'])); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Contenu (éditeur HTML) *</label>
                                    <div id="editor-container"></div>
                                    <textarea name="contenu" id="contenu" style="display:none;"><?php echo html_entity_decode($nouvelle['contenu']); ?></textarea>
                                    <small class="text-muted">Utilisez l'éditeur pour formater votre texte (gras, italique, listes, liens, etc.)</small>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5>Référencement SEO</h5>
                                <div class="mb-3">
                                    <label class="form-label">Meta title</label>
                                    <input type="text" name="seo_title" class="form-control" value="<?php echo cleanXSS($nouvelle['seo_title']); ?>" placeholder="Titre pour les moteurs de recherche">
                                    <small class="text-muted">Laissez vide pour utiliser le titre de la nouvelle.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Meta description</label>
                                    <textarea name="seo_description" rows="3" class="form-control" placeholder="Description pour les moteurs de recherche (max 160 caractères)"><?php echo cleanXSS($nouvelle['seo_description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-dark text-white">
                                        Image d'illustration
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if (!empty($nouvelle['image'])): ?>
                                            <div class="mb-3">
                                                <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($nouvelle['image']); ?>" class="img-fluid rounded" alt="<?php echo cleanXSS($nouvelle['titre']); ?>">
                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="delete_image" id="delete_image" value="1">
                                                        <label class="form-check-label text-danger" for="delete_image">
                                                            <i class="fas fa-trash"></i> Supprimer cette image
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3 bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="fas fa-image fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Changer l'image</label>
                                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                            <small class="text-muted">JPEG, PNG, WEBP. Max 5 Mo. Format 16:9 recommandé (1200x675px).</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        Aide à la rédaction
                                    </div>
                                    <div class="card-body">
                                        <p class="small text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            L'éditeur HTML vous permet de :
                                        </p>
                                        <ul class="small">
                                            <li>Mettre en forme votre texte (gras, italique, souligné)</li>
                                            <li>Créer des listes à puces ou numérotées</li>
                                            <li>Insérer des liens</li>
                                            <li>Ajouter des tableaux</li>
                                            <li>Prévisualiser le résultat final</li>
                                        </ul>
                                        <hr>
                                        <p class="small text-muted mb-0">
                                            <i class="fas fa-shield-alt"></i> 
                                            Le contenu est automatiquement nettoyé contre les injections XSS.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Mettre à jour' : 'Publier'; ?></button>
                            <a href="nouvelles.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer le contenu du textarea
        var textarea = document.getElementById('contenu');
        var container = document.getElementById('editor-container');
        var initialData = textarea.value || '';
        
        // Initialisation CKEditor dans le conteneur dédié
        ClassicEditor
            .create(container, {
                toolbar: {
                    items: [
                        'undo', 'redo',
                        '|', 'heading',
                        '|', 'bold', 'italic', 'underline', 'strikethrough',
                        '|', 'bulletedList', 'numberedList',
                        '|', 'alignment',
                        '|', 'link',
                        '|', 'blockQuote',
                        '|', 'removeFormat', 'sourceEditing'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraphe', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Titre 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Titre 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Titre 3', class: 'ck-heading_heading3' }
                    ]
                },
                link: {
                    addTargetToExternalLinks: true,
                    decorators: {
                        openInNewTab: {
                            mode: 'manual',
                            label: 'Ouvrir dans un nouvel onglet',
                            defaultValue: false,
                            attributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }
                        }
                    }
                },
                language: 'fr',
                height: '500px',
                initialData: initialData,
                removePlugins: [
                    'EasyImage', 
                    'Image', 
                    'ImageUpload', 
                    'MediaEmbed', 
                    'PasteFromOffice', 
                    'FindAndReplace',
                    'CKFinder',
                    'ImageToolbar',
                    'ImageCaption',
                    'ImageStyle',
                    'ImageResize',
                    'LinkImage'
                ]
            })
            .then(editor => {
                window.editor = editor;
                
                // Mettre à jour le textarea avant soumission
                document.getElementById('newsForm').addEventListener('submit', function(e) {
                    var editorData = window.editor.getData();
                    textarea.value = editorData;
                    
                    // Vérifier que le contenu n'est pas vide
                    if (!editorData || editorData.trim() === '' || editorData === '<p>&nbsp;</p>' || editorData === '<p><br></p>') {
                        e.preventDefault();
                        alert('Le contenu est requis. Veuillez saisir du texte.');
                        return false;
                    }
                });
            })
            .catch(error => {
                console.error('Erreur CKEditor:', error);
                // Fallback: afficher le textarea normal
                textarea.style.display = 'block';
                textarea.style.visibility = 'visible';
                textarea.style.height = '400px';
                textarea.required = true;
                container.style.display = 'none';
            });
    });
</script>

<?php include '../includes/footer.php'; ?>