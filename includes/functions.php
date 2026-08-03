<?php
// includes/functions.php - Fonctions utilitaires

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Génère un lien de téléchargement unique pour un PDF
 */
function genererLienTelechargement($commande_id, $livre_id) {
    $token = bin2hex(random_bytes(32));
    $expiration = date('Y-m-d H:i:s', strtotime('+48 hours'));
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "UPDATE commandes SET lien_telechargement_unique = :token, lien_expire_le = :expiration 
            WHERE id = :commande_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':token' => $token,
        ':expiration' => $expiration,
        ':commande_id' => $commande_id
    ]);
    
    return SITE_URL . "download.php?token=" . $token;
}

/**
 * Calcule les frais de port selon le pays
 */
function calculerFraisPort($pays) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT frais_port FROM pays_frais_port WHERE pays = :pays";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':pays' => $pays]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['frais_port'];
    }
    
    // Par défaut : frais "Autre"
    $sql = "SELECT frais_port FROM pays_frais_port WHERE pays = 'Autre'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    
    return $result ? $result['frais_port'] : 15.00;
}

/**
 * Vérifie si un utilisateur a aimé un livre
 */
function utilisateurADelike($utilisateur_id, $livre_id) {
    if (!$utilisateur_id) return false;
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT id FROM likes WHERE utilisateur_id = :user_id AND livre_id = :livre_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $utilisateur_id,
        ':livre_id' => $livre_id
    ]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Compte le nombre de likes pour un livre
 */
function compterLikes($livre_id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT COUNT(*) as total FROM likes WHERE livre_id = :livre_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':livre_id' => $livre_id]);
    $result = $stmt->fetch();
    
    return $result['total'];
}

/**
 * Ajoute ou retire un like
 */
function toggleLike($utilisateur_id, $livre_id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (utilisateurADelike($utilisateur_id, $livre_id)) {
        $sql = "DELETE FROM likes WHERE utilisateur_id = :user_id AND livre_id = :livre_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $utilisateur_id,
            ':livre_id' => $livre_id
        ]);
        return 'removed';
    } else {
        $sql = "INSERT INTO likes (utilisateur_id, livre_id) VALUES (:user_id, :livre_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $utilisateur_id,
            ':livre_id' => $livre_id
        ]);
        return 'added';
    }
}

/**
 * Ajoute un commentaire sur un livre
 */
function ajouterCommentaire($utilisateur_id, $livre_id, $commentaire, $note = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "INSERT INTO commentaires (utilisateur_id, livre_id, commentaire, note, status) 
            VALUES (:user_id, :livre_id, :commentaire, :note, 'en_attente')";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        ':user_id' => $utilisateur_id,
        ':livre_id' => $livre_id,
        ':commentaire' => htmlspecialchars($commentaire),
        ':note' => $note
    ]);
}

/**
 * Récupère les commentaires validés d'un livre
 */
function getCommentaires($livre_id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT c.*, u.nom, u.prenom 
            FROM commentaires c
            JOIN utilisateurs u ON c.utilisateur_id = u.id
            WHERE c.livre_id = :livre_id AND c.status = 'valide'
            ORDER BY c.date_creation DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':livre_id' => $livre_id]);
    
    return $stmt->fetchAll();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function estAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Redirige si non connecté
 */
function redirigerSiNonConnecte() {
    if (!estConnecte()) {
        header('Location: ' . SITE_URL . 'compte/connexion.php');
        exit();
    }
}

/**
 * Redirige si non admin
 */
function redirigerSiNonAdmin() {
    if (!estAdmin()) {
        header('Location: ' . SITE_URL);
        exit();
    }
}

/**
 * Nettoie une entrée pour le SEO
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Partage sur les réseaux sociaux (génère les liens)
 */
function liensPartageSociaux($url, $titre) {
    $url_encode = urlencode($url);
    $titre_encode = urlencode($titre);
    
    return [
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . $url_encode,
        'twitter' => "https://twitter.com/intent/tweet?url=" . $url_encode . "&text=" . $titre_encode,
        'instagram' => "#", // Pas d'API directe - lien à copier manuellement
        'email' => "mailto:?subject=" . $titre_encode . "&body=" . $url_encode
    ];
}
?>