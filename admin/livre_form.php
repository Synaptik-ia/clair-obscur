<?php
// admin/livre_form.php - Formulaire d'ajout/modification de livre

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Formulaire livre - Administration";

$db = new Database();
$conn = $db->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($id > 0);

$livre = [
    'id' => '',
    'titre' => '',
    'sous_titre' => '',
    'auteur_id' => '',
    'isbn' => '',
    'description' => '',
    'prix_ebook' => '',
    'prix_physique' => '',
    'couverture' => '',
    'fichier_pdf' => '',
    'date_parution' => date('Y-m-d'),
    'stock_physique' => 0,
    'seo_title' => '',
    'seo_description' => '',
    'statut_vente' => 'en_vente',
    'date_precommande' => '',
    'date_sortie' => '',
    'prix_precommande' => ''
];

$message = '';
$message_type = '';

// Récupération du livre si édition
if ($is_edit) {
    $sql = "SELECT * FROM livres WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $livre_data = $stmt->fetch();
    if ($livre_data) {
        $livre = $livre_data;
    } else {
        header('Location: livres.php');
        exit();
    }
}

// Récupération des auteurs pour le select
$sql_auteurs = "SELECT id, nom FROM auteurs ORDER BY nom";
$stmt_auteurs = $conn->prepare($sql_auteurs);
$stmt_auteurs->execute();
$auteurs = $stmt_auteurs->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livre['titre'] = trim($_POST['titre'] ?? '');
    $livre['sous_titre'] = trim($_POST['sous_titre'] ?? '');
    $livre['auteur_id'] = $_POST['auteur_id'] ?? null;
    $livre['isbn'] = trim($_POST['isbn'] ?? '');
    $livre['description'] = trim($_POST['description'] ?? '');
    $livre['prix_ebook'] = floatval($_POST['prix_ebook'] ?? 0);
    $livre['prix_physique'] = !empty($_POST['prix_physique']) ? floatval($_POST['prix_physique']) : null;
    $livre['date_parution'] = $_POST['date_parution'] ?? date('Y-m-d');
    $livre['stock_physique'] = intval($_POST['stock_physique'] ?? 0);
    $livre['seo_title'] = trim($_POST['seo_title'] ?? '');
    $livre['seo_description'] = trim($_POST['seo_description'] ?? '');
    $livre['statut_vente'] = $_POST['statut_vente'] ?? 'en_vente';
    $livre['date_precommande'] = !empty($_POST['date_precommande']) ? $_POST['date_precommande'] : null;
    $livre['date_sortie'] = !empty($_POST['date_sortie']) ? $_POST['date_sortie'] : null;
    $livre['prix_precommande'] = !empty($_POST['prix_precommande']) ? floatval($_POST['prix_precommande']) : null;
    
    $erreurs = [];
    
    if (empty($livre['titre'])) {
        $erreurs[] = "Le titre est requis.";
    }
    if (empty($livre['auteur_id'])) {
        $erreurs[] = "L'auteur est requis.";
    }
    
    // Validation des dates
    if ($livre['statut_vente'] == 'precommande') {
        if (empty($livre['date_precommande'])) {
            $erreurs[] = "La date de précommande est requise pour ce statut.";
        }
        if (empty($livre['date_sortie'])) {
            $erreurs[] = "La date de sortie est requise pour ce statut.";
        }
        if (empty($livre['prix_precommande']) && $livre['prix_precommande'] !== 0) {
            $erreurs[] = "Le prix de précommande est requis pour ce statut.";
        }
    }
    
    if ($livre['prix_ebook'] <= 0 && $livre['statut_vente'] != 'non_vendable') {
        $erreurs[] = "Le prix de l'ebook doit être supérieur à 0.";
    }
    
    // Gestion de l'upload de la couverture
    if (isset($_FILES['couverture']) && $_FILES['couverture']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['couverture']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $erreurs[] = "Format de couverture non autorisé (JPEG, PNG, WEBP).";
        } else {
            $ext = strtolower(pathinfo($_FILES['couverture']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $erreurs[] = "Extension de fichier non autorisée.";
            } else {
            $filename = 'cover_' . uniqid() . '.' . $ext;
            $destination = '../assets/images/' . $filename;
            
            if (move_uploaded_file($_FILES['couverture']['tmp_name'], $destination)) {
                if ($is_edit && !empty($livre['couverture']) && file_exists('../assets/images/' . $livre['couverture'])) {
                    unlink('../assets/images/' . $livre['couverture']);
                }
                $livre['couverture'] = $filename;
            } else {
                $erreurs[] = "Erreur lors de l'upload de la couverture.";
            }
            } // fin inner else (extension check)
        }
    }
    
    // Gestion de l'upload du PDF
    if (isset($_FILES['fichier_pdf']) && $_FILES['fichier_pdf']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['fichier_pdf']['tmp_name']);
        finfo_close($finfo);
        
        if ($mime_type !== 'application/pdf') {
            $erreurs[] = "Le fichier doit être un PDF.";
        } else {
            $filename = 'book_' . uniqid() . '.pdf';
            $destination = '../assets/pdfs/' . $filename;
            
            if (!is_dir('../assets/pdfs/')) {
                mkdir('../assets/pdfs/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['fichier_pdf']['tmp_name'], $destination)) {
                if ($is_edit && !empty($livre['fichier_pdf']) && file_exists('../assets/pdfs/' . $livre['fichier_pdf'])) {
                    unlink('../assets/pdfs/' . $livre['fichier_pdf']);
                }
                $livre['fichier_pdf'] = $filename;
            } else {
                $erreurs[] = "Erreur lors de l'upload du PDF.";
            }
        }
    } elseif (!$is_edit && $livre['statut_vente'] != 'non_vendable' && empty($livre['fichier_pdf'])) {
        $erreurs[] = "Le fichier PDF est requis pour un nouveau livre.";
    }
    
    if (empty($erreurs)) {
        if ($is_edit) {
            $sql = "UPDATE livres SET 
                    titre = :titre, sous_titre = :sous_titre, auteur_id = :auteur_id,
                    isbn = :isbn, description = :description, prix_ebook = :prix_ebook,
                    prix_physique = :prix_physique, couverture = :couverture,
                    fichier_pdf = :fichier_pdf, date_parution = :date_parution,
                    stock_physique = :stock_physique, seo_title = :seo_title,
                    seo_description = :seo_description,
                    statut_vente = :statut_vente, date_precommande = :date_precommande,
                    date_sortie = :date_sortie, prix_precommande = :prix_precommande
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':titre' => $livre['titre'],
                ':sous_titre' => $livre['sous_titre'],
                ':auteur_id' => $livre['auteur_id'],
                ':isbn' => $livre['isbn'],
                ':description' => $livre['description'],
                ':prix_ebook' => $livre['prix_ebook'],
                ':prix_physique' => $livre['prix_physique'],
                ':couverture' => $livre['couverture'],
                ':fichier_pdf' => $livre['fichier_pdf'],
                ':date_parution' => $livre['date_parution'],
                ':stock_physique' => $livre['stock_physique'],
                ':seo_title' => $livre['seo_title'],
                ':seo_description' => $livre['seo_description'],
                ':statut_vente' => $livre['statut_vente'],
                ':date_precommande' => $livre['date_precommande'],
                ':date_sortie' => $livre['date_sortie'],
                ':prix_precommande' => $livre['prix_precommande'],
                ':id' => $id
            ]);
            $message = "Livre modifié avec succès.";
        } else {
            $sql = "INSERT INTO livres (titre, sous_titre, auteur_id, isbn, description, prix_ebook, prix_physique, couverture, fichier_pdf, date_parution, stock_physique, seo_title, seo_description, statut_vente, date_precommande, date_sortie, prix_precommande) 
                    VALUES (:titre, :sous_titre, :auteur_id, :isbn, :description, :prix_ebook, :prix_physique, :couverture, :fichier_pdf, :date_parution, :stock_physique, :seo_title, :seo_description, :statut_vente, :date_precommande, :date_sortie, :prix_precommande)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':titre' => $livre['titre'],
                ':sous_titre' => $livre['sous_titre'],
                ':auteur_id' => $livre['auteur_id'],
                ':isbn' => $livre['isbn'],
                ':description' => $livre['description'],
                ':prix_ebook' => $livre['prix_ebook'],
                ':prix_physique' => $livre['prix_physique'],
                ':couverture' => $livre['couverture'],
                ':fichier_pdf' => $livre['fichier_pdf'],
                ':date_parution' => $livre['date_parution'],
                ':stock_physique' => $livre['stock_physique'],
                ':seo_title' => $livre['seo_title'],
                ':seo_description' => $livre['seo_description'],
                ':statut_vente' => $livre['statut_vente'],
                ':date_precommande' => $livre['date_precommande'],
                ':date_sortie' => $livre['date_sortie'],
                ':prix_precommande' => $livre['prix_precommande']
            ]);
            $message = "Livre ajouté avec succès.";
        }
        $message_type = "success";

        // Mettre à jour le sitemap si le statut change
        require_once '../includes/sitemap.php';
        if ($livre['statut_vente'] === 'non_vendable') {
            $url = SITE_URL . 'livres/fiche.php?id=' . ($is_edit ? $id : $conn->lastInsertId());
            $stmt_url = $conn->prepare("DELETE FROM site_pages WHERE url = :url");
            $stmt_url->execute([':url' => $url]);
        }
        generateSitemap($conn);

        header("refresh:2;url=livres.php");
    } else {
        $message = implode('<br>', $erreurs);
        $message_type = "danger";
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><?php echo $is_edit ? 'Modifier le livre' : 'Ajouter un livre'; ?></h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Sous-titre</label>
                                    <input type="text" name="sous_titre" class="form-control" value="<?php echo htmlspecialchars($livre['sous_titre']); ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Auteur *</label>
                                        <select name="auteur_id" class="form-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach ($auteurs as $auteur): ?>
                                                <option value="<?php echo $auteur['id']; ?>" <?php echo $livre['auteur_id'] == $auteur['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($auteur['nom']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ISBN</label>
                                        <input type="text" name="isbn" class="form-control" value="<?php echo htmlspecialchars($livre['isbn']); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="6" class="form-control"><?php echo htmlspecialchars($livre['description']); ?></textarea>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5>Statut de vente</h5>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="statut_vente" id="statut_en_vente" value="en_vente" <?php echo $livre['statut_vente'] == 'en_vente' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-success" for="statut_en_vente">
                                                <i class="fas fa-check-circle"></i> En vente
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="statut_vente" id="statut_precommande" value="precommande" <?php echo $livre['statut_vente'] == 'precommande' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-warning" for="statut_precommande">
                                                <i class="fas fa-clock"></i> Précommande
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="statut_vente" id="statut_non_vendable" value="non_vendable" <?php echo $livre['statut_vente'] == 'non_vendable' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-secondary" for="statut_non_vendable">
                                                <i class="fas fa-ban"></i> Non vendable
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="precommande_fields" style="display: <?php echo $livre['statut_vente'] == 'precommande' ? 'block' : 'none'; ?>;">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Date de début précommande</label>
                                            <input type="date" name="date_precommande" class="form-control" value="<?php echo $livre['date_precommande']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Date de sortie</label>
                                            <input type="date" name="date_sortie" class="form-control" value="<?php echo $livre['date_sortie']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Prix précommande (€)</label>
                                            <input type="number" step="0.01" name="prix_precommande" class="form-control" value="<?php echo $livre['prix_precommande']; ?>" placeholder="Prix spécial précommande">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Prix Ebook (€) *</label>
                                        <input type="number" step="0.01" name="prix_ebook" class="form-control" value="<?php echo $livre['prix_ebook']; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Prix Papier (€)</label>
                                        <input type="number" step="0.01" name="prix_physique" class="form-control" value="<?php echo $livre['prix_physique']; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Stock papier</label>
                                        <input type="number" name="stock_physique" class="form-control" value="<?php echo $livre['stock_physique']; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Date de parution</label>
                                    <input type="date" name="date_parution" class="form-control" value="<?php echo $livre['date_parution']; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">Fichiers</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Couverture</label>
                                            <?php if ($livre['couverture']): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo SITE_URL . 'assets/images/' . $livre['couverture']; ?>" class="img-fluid rounded" alt="Couverture">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="couverture" class="form-control" accept="image/*">
                                            <small class="text-muted">JPEG, PNG, WEBP</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Fichier PDF</label>
                                            <?php if ($livre['fichier_pdf']): ?>
                                                <div class="alert alert-info">PDF existant : <?php echo $livre['fichier_pdf']; ?></div>
                                            <?php endif; ?>
                                            <input type="file" name="fichier_pdf" class="form-control" accept="application/pdf" <?php echo !$is_edit ? 'required' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Référencement SEO</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta title</label>
                                <input type="text" name="seo_title" class="form-control" value="<?php echo htmlspecialchars($livre['seo_title']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta description</label>
                                <textarea name="seo_description" rows="3" class="form-control"><?php echo htmlspecialchars($livre['seo_description']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Mettre à jour' : 'Ajouter'; ?></button>
                            <a href="livres.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Afficher/masquer les champs de précommande selon le statut
    document.querySelectorAll('input[name="statut_vente"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var precommandeFields = document.getElementById('precommande_fields');
            if (this.value === 'precommande') {
                precommandeFields.style.display = 'block';
            } else {
                precommandeFields.style.display = 'none';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>