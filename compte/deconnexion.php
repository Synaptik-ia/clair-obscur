<?php
// compte/deconnexion.php - Déconnexion du client

require_once '../config/database.php';
require_once '../includes/functions.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Supprimer le cookie "se souvenir de moi"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Redirection vers la page d'accueil avec message
session_start(); // Redémarrer une session temporaire pour le message
$_SESSION['flash_message'] = "Vous avez été déconnecté avec succès.";
$_SESSION['flash_type'] = "success";
session_write_close();

header('Location: ' . SITE_URL);
exit();
?>