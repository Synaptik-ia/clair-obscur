<?php
// ajax/like.php - Gestion AJAX des likes

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialiser la réponse
$response = ['success' => false, 'liked' => false, 'likes' => 0, 'redirect' => null];

// Vérifier que l'utilisateur est connecté
if (!estConnecte()) {
    $response['redirect'] = SITE_URL . 'compte/connexion.php';
    echo json_encode($response);
    exit();
}

// Vérifier les paramètres
if (!isset($_POST['livre_id']) || !is_numeric($_POST['livre_id'])) {
    echo json_encode($response);
    exit();
}

$livre_id = (int)$_POST['livre_id'];
$user_id = $_SESSION['user_id'];

// Toggle du like
$result = toggleLike($user_id, $livre_id);
$response['success'] = true;
$response['liked'] = ($result === 'added');
$response['likes'] = compterLikes($livre_id);

echo json_encode($response);
exit();
?>