<?php
// config/database.php - Configuration base de données

require_once __DIR__ . '/env.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = env('DB_HOST', 'localhost');
        $this->db_name = env('DB_NAME', 'clair-obscur');
        $this->username = env('DB_USER', 'clair-obscur');
        $this->password = env('DB_PASS', '');
    }

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
define('SITE_NAME', env('SITE_NAME', 'Clair-Obscur Éditions'));
define('SITE_URL', env('SITE_URL', 'https://clair-obscur-editions.com/'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'contact@clair-obscur-editions.com'));

// PayPal configuration
define('PAYPAL_CLIENT_ID', env('PAYPAL_CLIENT_ID', ''));
define('PAYPAL_SECRET', env('PAYPAL_SECRET', ''));
define('PAYPAL_MODE', env('PAYPAL_MODE', 'sandbox'));

/// Session start avec paramètres de sécurité
if (session_status() === PHP_SESSION_NONE) {
    // Paramètres de session sécurisés
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
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