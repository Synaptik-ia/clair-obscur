<?php
// panier/ajax-count.php - Compteur AJAX du panier

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialiser la réponse
$response = ['count' => 0];

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compter les articles dans le panier
if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    $total_articles = 0;
    foreach ($_SESSION['panier'] as $item) {
        $total_articles += isset($item['quantite']) ? (int)$item['quantite'] : 1;
    }
    $response['count'] = $total_articles;
}

// Retourner la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>