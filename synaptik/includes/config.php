<?php
// Configuration du site
define('SITE_NAME', 'Synaptik IA Solutions');
define('SITE_DOMAIN', 'https://votre-domaine.com');
define('CONTACT_EMAIL', 'contact@synaptik-ia.fr');

// Configuration Google reCAPTCHA v3
// Inscrivez-vous sur https://www.google.com/recaptcha/admin
define('RECAPTCHA_SITE_KEY', 'VOTRE_SITE_KEY_ICI');
define('RECAPTCHA_SECRET_KEY', 'VOTRE_SECRET_KEY_ICI');

// Configuration BDD (optionnelle pour stocker les messages)
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'synaptik_db');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// Sécurisation des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Uniquement en HTTPS
session_start();

// Protection XSS et sécurité
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Fonction de nettoyage des entrées
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour envoyer un email (protégée)
function send_contact_email($to, $subject, $message, $from_email, $from_name) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>