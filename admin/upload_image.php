<?php
// admin/upload_image.php - Upload d'images pour TinyMCE

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

// Vérifier qu'un fichier a été uploadé
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Aucun fichier reçu ou erreur lors de l\'upload']);
    exit();
}

$file = $_FILES['file'];

// Vérifier le type MIME
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['error' => 'Format de fichier non autorisé. Utilisez JPEG, PNG, WEBP ou GIF.']);
    exit();
}

// Vérifier la taille (max 5 Mo)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['error' => 'Le fichier est trop volumineux. Maximum 5 Mo.']);
    exit();
}

// Générer un nom unique
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'editor_' . uniqid() . '_' . time() . '.' . $ext;
$destination = '../assets/images/' . $filename;

// Créer le dossier s'il n'existe pas
if (!is_dir('../assets/images/')) {
    mkdir('../assets/images/', 0755, true);
}

// Déplacer le fichier
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Retourner l'URL de l'image pour TinyMCE
    echo json_encode(['location' => SITE_URL . 'assets/images/' . $filename]);
} else {
    echo json_encode(['error' => 'Erreur lors du déplacement du fichier.']);
}
?>