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
 * Inscrit un email à la newsletter (ou réactive un compte supprimé)
 * Retourne le token de confirmation
 */
function newsletter_subscribe($email) {
    $db = new Database();
    $conn = $db->getConnection();

    $token = bin2hex(random_bytes(32));

    // Vérifier si l'email existe déjà
    $sql = "SELECT id, confirmed, deleted_at FROM newsletter WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['deleted_at'] === null && $existing['confirmed'] == 1) {
            return ['status' => 'already_subscribed'];
        }
        // Réactiver l'abonnement
        $sql = "UPDATE newsletter SET token = :token, confirmed = 0, deleted_at = NULL, created_at = NOW() WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':token' => $token, ':id' => $existing['id']]);
    } else {
        $sql = "INSERT INTO newsletter (email, token, confirmed, created_at) VALUES (:email, :token, 0, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email, ':token' => $token]);
    }

    // Envoyer l'email de confirmation
    newsletter_send_confirmation($email, $token);

    return ['status' => 'confirmation_sent', 'token' => $token];
}

/**
 * Confirme l'inscription via le token
 */
function newsletter_confirm($token) {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT id, email FROM newsletter WHERE token = :token AND confirmed = 0 AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':token' => $token]);
    $subscriber = $stmt->fetch();

    if (!$subscriber) {
        return false;
    }

    $sql = "UPDATE newsletter SET confirmed = 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $subscriber['id']]);

    return $subscriber['email'];
}

/**
 * Désabonne un email (soft delete)
 */
function newsletter_unsubscribe($email) {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "UPDATE newsletter SET deleted_at = NOW() WHERE email = :email AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
        newsletter_send_removal($email);
        return true;
    }
    return false;
}

/**
 * Supprime définitivement un abonné (hard delete) + envoie email
 */
function newsletter_delete($id) {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT email FROM newsletter WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $subscriber = $stmt->fetch();

    if ($subscriber) {
        $sql = "DELETE FROM newsletter WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        newsletter_send_removal($subscriber['email']);
        return true;
    }
    return false;
}

/**
 * Récupère tous les abonnés (pour l'admin)
 */
function newsletter_get_all($page = 1, $limit = 50) {
    $db = new Database();
    $conn = $db->getConnection();
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM newsletter WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function newsletter_count_all() {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT COUNT(*) as total FROM newsletter WHERE deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetch()['total'];
}

/**
 * Envoie l'email de confirmation d'inscription
 */
function newsletter_send_confirmation($email, $token) {
    $confirm_link = SITE_URL . 'ajax/newsletter.php?action=confirm&token=' . $token;
    $subject = "Confirmez votre inscription à la newsletter - Clair-Obscur Éditions";

    $message = "Bonjour,\n\n";
    $message .= "Merci de votre inscription à la newsletter de Clair-Obscur Éditions.\n\n";
    $message .= "Pour confirmer votre inscription, veuillez cliquer sur le lien suivant :\n";
    $message .= $confirm_link . "\n\n";
    $message .= "Si vous n'avez pas demandé cette inscription, ignorez simplement cet email.\n\n";
    $message .= "À bientôt,\nL'équipe Clair-Obscur Éditions";

    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($email, $subject, $message, $headers);
}

/**
 * Envoie l'email de notification de désabonnement
 */
function newsletter_send_removal($email) {
    $subject = "Désabonnement de la newsletter - Clair-Obscur Éditions";

    $message = "Bonjour,\n\n";
    $message .= "Votre adresse email a été retirée de notre liste de diffusion, comme demandé.\n\n";
    $message .= "Si vous souhaitez vous réabonner, vous pouvez le faire à tout moment sur notre site :\n";
    $message .= SITE_URL . "\n\n";
    $message .= "Merci de votre intérêt pour Clair-Obscur Éditions.\n\n";
    $message .= "Cordialement,\nL'équipe Clair-Obscur Éditions";

    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($email, $subject, $message, $headers);
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