<?php
// download.php - Téléchargement sécurisé du PDF

require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
redirigerSiNonConnecte();

// Récupérer le token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: ' . SITE_URL . 'compte/commandes.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Rechercher la commande correspondant au token
$sql = "SELECT c.*, u.id as user_id 
        FROM commandes c 
        JOIN utilisateurs u ON c.utilisateur_id = u.id 
        WHERE c.lien_telechargement_unique = :token 
        AND c.statut = 'paye' 
        AND c.type_commande = 'ebook'";
$stmt = $conn->prepare($sql);
$stmt->execute([':token' => $token]);
$commande = $stmt->fetch();

// Vérifications
if (!$commande) {
    die("Lien de téléchargement invalide.");
}

// Vérifier que l'utilisateur est bien le propriétaire
if ($commande['user_id'] != $_SESSION['user_id']) {
    die("Vous n'êtes pas autorisé à télécharger ce fichier.");
}

// Vérifier l'expiration du lien
$date_expiration = strtotime($commande['lien_expire_le']);
if (time() > $date_expiration) {
    die("Ce lien de téléchargement a expiré (valable 48h). Veuillez contacter le support.");
}

// Récupérer le livre associé à la commande
$sql_livre = "SELECT dc.livre_id, l.fichier_pdf, l.titre 
              FROM details_commandes dc 
              JOIN livres l ON dc.livre_id = l.id 
              WHERE dc.commande_id = :commande_id 
              LIMIT 1";
$stmt_livre = $conn->prepare($sql_livre);
$stmt_livre->execute([':commande_id' => $commande['id']]);
$livre = $stmt_livre->fetch();

if (!$livre || empty($livre['fichier_pdf'])) {
    die("Fichier PDF introuvable.");
}

// Chemin du fichier PDF
$file_path = __DIR__ . '/assets/pdfs/' . $livre['fichier_pdf'];

if (!file_exists($file_path)) {
    die("Le fichier demandé n'existe pas sur le serveur.");
}

// Enregistrer le téléchargement dans les logs (optionnel)
$sql_log = "INSERT INTO logs_telechargements (commande_id, utilisateur_id, ip_address, date_telechargement) 
            VALUES (:commande_id, :user_id, :ip, NOW())";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->execute([
    ':commande_id' => $commande['id'],
    ':user_id' => $_SESSION['user_id'],
    ':ip' => $_SERVER['REMOTE_ADDR']
]);

// Forcer le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . urlencode($livre['titre']) . '.pdf"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Nettoyer le buffer de sortie
ob_clean();
flush();

// Lire et envoyer le fichier
readfile($file_path);
exit();
?>