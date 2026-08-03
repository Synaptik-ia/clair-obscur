<?php
// config/database.php - Configuration base de données

class Database {
    private $host = "localhost";
    private $db_name = "clair-obscur";
    private $username = "clair-obscur";     // À modifier selon votre configuration
    private $password = "sosVedknip09@";         // À modifier selon votre configuration
    private $conn;

 public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // Désactive les requêtes préparées émulées (protection SQL injection)
                    PDO::ATTR_STRINGIFY_FETCHES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Erreur de connexion BDD: " . $e->getMessage());
            die("Erreur technique. Veuillez réessayer plus tard.");
        }
        return $this->conn;
    }
}

// Configuration générale du site
define('SITE_NAME', 'Clair-Obscur Éditions');
define('SITE_URL', 'https://clair-obscur-editions.com/');  // À modifier en production
define('ADMIN_EMAIL', 'contact@clair-obscur-editions.com');

// PayPal configuration (mode sandbox pour tests)
define('PAYPAL_CLIENT_ID', 'VOTRE_CLIENT_ID_ICI');
define('PAYPAL_SECRET', 'VOTRE_SECRET_ICI');
define('PAYPAL_MODE', 'sandbox'); // sandbox ou live

/// Session start avec paramètres de sécurité
if (session_status() === PHP_SESSION_NONE) {
    // Paramètres de session sécurisés
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Passer à 1 en HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Fonction de débogage (à désactiver en production)
function debug($data) {
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}
?>