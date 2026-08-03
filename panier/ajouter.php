<?php
// panier/ajouter.php - Ajouter un article au panier

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialisation du panier en session
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Vérification des données POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['livre_id'])) {
    header('Location: ' . SITE_URL . 'livres/liste.php');
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
    $_SESSION['flash_message'] = "Livre introuvable.";
    $_SESSION['flash_type'] = "danger";
    header('Location: ' . SITE_URL . 'livres/liste.php');
    exit();
}

// Validation du type de commande
if ($type_commande == 'ebook') {
    // OK pour ebook
} elseif ($type_commande == 'physique' || $type_commande == 'physique_dedicace') {
    // Vérifier que la version physique existe et est en stock
    if (!$livre['prix_physique'] || $livre['prix_physique'] <= 0) {
        $_SESSION['flash_message'] = "La version physique n'est pas disponible pour ce livre.";
        $_SESSION['flash_type'] = "warning";
        header('Location: ' . SITE_URL . 'livres/fiche.php?id=' . $livre_id);
        exit();
    }
    if ($livre['stock_physique'] < $quantite) {
        $_SESSION['flash_message'] = "Stock insuffisant pour la version physique.";
        $_SESSION['flash_type'] = "warning";
        header('Location: ' . SITE_URL . 'livres/fiche.php?id=' . $livre_id);
        exit();
    }
} else {
    $type_commande = 'ebook';
}

// Ajout ou mise à jour du panier
if (isset($_SESSION['panier'][$livre_id])) {
    // Si le type de commande est différent, on remplace
    if ($_SESSION['panier'][$livre_id]['type_commande'] !== $type_commande || 
        $_SESSION['panier'][$livre_id]['dedicace'] !== $dedicace) {
        $_SESSION['panier'][$livre_id] = [
            'quantite' => $quantite,
            'type_commande' => $type_commande,
            'dedicace' => $dedicace
        ];
    } else {
        // Sinon on cumule les quantités
        $_SESSION['panier'][$livre_id]['quantite'] += $quantite;
    }
} else {
    $_SESSION['panier'][$livre_id] = [
        'quantite' => $quantite,
        'type_commande' => $type_commande,
        'dedicace' => $dedicace
    ];
}

// Message de confirmation
$nom_livre = $livre['titre'];
$type_texte = ($type_commande == 'ebook') ? 'Ebook PDF' : (($dedicace) ? 'Version papier dédicacée' : 'Version papier');

$_SESSION['flash_message'] = "\"{$nom_livre}\" ({$type_texte}) a été ajouté au panier.";
$_SESSION['flash_type'] = "success";

// Redirection vers le panier ou la fiche livre
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL . 'panier/';
header('Location: ' . $redirect);
exit();
?>