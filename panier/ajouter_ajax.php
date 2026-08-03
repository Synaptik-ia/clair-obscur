<?php
// panier/ajouter_ajax.php - Ajout au panier en AJAX sans rechargement

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialiser la réponse
$response = ['success' => false, 'message' => '', 'cart_count' => 0];

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser le panier si nécessaire
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Vérifier les paramètres
if (!isset($_POST['livre_id']) || !is_numeric($_POST['livre_id'])) {
    $response['message'] = "Livre invalide.";
    echo json_encode($response);
    exit();
}

$livre_id = (int)$_POST['livre_id'];
$quantite = isset($_POST['quantite']) ? max(1, (int)$_POST['quantite']) : 1;
$type_commande = isset($_POST['type_commande']) ? $_POST['type_commande'] : 'ebook';
$dedicace = isset($_POST['dedicace']) ? (int)$_POST['dedicace'] : 0;

// Vérifier que le livre existe
$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT * FROM livres WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $livre_id]);
$livre = $stmt->fetch();

if (!$livre) {
    $response['message'] = "Livre introuvable.";
    echo json_encode($response);
    exit();
}

// Validation du type de commande
if ($type_commande == 'ebook') {
    // OK pour ebook
} elseif ($type_commande == 'physique' || $type_commande == 'physique_dedicace') {
    if (!$livre['prix_physique'] || $livre['prix_physique'] <= 0) {
        $response['message'] = "La version physique n'est pas disponible pour ce livre.";
        echo json_encode($response);
        exit();
    }
    if ($livre['stock_physique'] < $quantite) {
        $response['message'] = "Stock insuffisant pour la version physique.";
        echo json_encode($response);
        exit();
    }
} else {
    $type_commande = 'ebook';
}

// Ajout ou mise à jour du panier
if (isset($_SESSION['panier'][$livre_id])) {
    if ($_SESSION['panier'][$livre_id]['type_commande'] !== $type_commande || 
        $_SESSION['panier'][$livre_id]['dedicace'] !== $dedicace) {
        $_SESSION['panier'][$livre_id] = [
            'quantite' => $quantite,
            'type_commande' => $type_commande,
            'dedicace' => $dedicace
        ];
    } else {
        $_SESSION['panier'][$livre_id]['quantite'] += $quantite;
    }
} else {
    $_SESSION['panier'][$livre_id] = [
        'quantite' => $quantite,
        'type_commande' => $type_commande,
        'dedicace' => $dedicace
    ];
}

// Compter le nombre total d'articles dans le panier
$total_articles = 0;
foreach ($_SESSION['panier'] as $item) {
    $total_articles += isset($item['quantite']) ? (int)$item['quantite'] : 1;
}

$response['success'] = true;
$response['message'] = "Ajouté au panier !";
$response['cart_count'] = $total_articles;

echo json_encode($response);
exit();
?>