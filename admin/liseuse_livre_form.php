<?php
// admin/liseuse_livre_form.php - Traitement du formulaire livre

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

redirigerSiNonAdmin();

$db = new Database();
$conn = $db->getConnection();

$livre_id = isset($_POST['livre_id']) ? (int)$_POST['livre_id'] : 0;
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
    // Nettoyer le slug (caractères autorisés : lettres, chiffres, tirets)
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $slug));
    $slug = trim($slug, '-');
    
    // Vérifier que le slug n'existe pas déjà (sauf pour le même livre)
    $sql_check = "SELECT id FROM liseuse_livres WHERE slug = :slug";
    if ($livre_id > 0) {
        $sql_check .= " AND id != :id";
    }
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':slug', $slug);
    if ($livre_id > 0) {
        $stmt_check->bindParam(':id', $livre_id);
    }
    $stmt_check->execute();
    if ($stmt_check->fetch()) {
        $erreurs[] = "Ce slug est déjà utilisé. Veuillez en choisir un autre.";
    }
}

// Gestion images
function uploadImage($file, $prefix, $livre_id) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed)) {
        return false;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . $livre_id . '_' . time() . '.' . $ext;
    $destination = '../assets/images/' . $filename;
    
    if (!is_dir('../assets/images/')) {
        mkdir('../assets/images/', 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return false;
}

if (empty($erreurs)) {
    if ($livre_id > 0) {
        // Mise à jour du livre
        $sql = "UPDATE liseuse_livres SET titre = :titre, slug = :slug, description = :description, date_modification = NOW() 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':titre' => $titre,
            ':slug' => $slug,
            ':description' => $description,
            ':id' => $livre_id
        ]);
        
        // Upload couverture
        $image = uploadImage($_FILES['image_couverture'], 'cover', $livre_id);
        if ($image) {
            $sql_img = "UPDATE liseuse_livres SET image_couverture = :img WHERE id = :id";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->execute([':img' => $image, ':id' => $livre_id]);
        }
        
        $image4 = uploadImage($_FILES['image_4eme'], 'back', $livre_id);
        if ($image4) {
            $sql_img = "UPDATE liseuse_livres SET image_4eme = :img WHERE id = :id";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->execute([':img' => $image4, ':id' => $livre_id]);
        }
        
        $_SESSION['flash_message'] = "Livre modifié avec succès.";
    } else {
        // Création du livre
        $sql = "INSERT INTO liseuse_livres (titre, slug, description, date_creation) 
                VALUES (:titre, :slug, :description, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':titre' => $titre,
            ':slug' => $slug,
            ':description' => $description
        ]);
        
        $nouveau_id = $conn->lastInsertId();
        
        $image = uploadImage($_FILES['image_couverture'], 'cover', $nouveau_id);
        if ($image) {
            $sql_img = "UPDATE liseuse_livres SET image_couverture = :img WHERE id = :id";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->execute([':img' => $image, ':id' => $nouveau_id]);
        }
        
        $image4 = uploadImage($_FILES['image_4eme'], 'back', $nouveau_id);
        if ($image4) {
            $sql_img = "UPDATE liseuse_livres SET image_4eme = :img WHERE id = :id";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->execute([':img' => $image4, ':id' => $nouveau_id]);
        }
        
        $_SESSION['flash_message'] = "Livre créé avec succès.";
    }
    
    $_SESSION['flash_type'] = "success";
} else {
    $_SESSION['flash_message'] = implode('<br>', $erreurs);
    $_SESSION['flash_type'] = "danger";
}

header('Location: liseuse_config.php');
exit();
?>