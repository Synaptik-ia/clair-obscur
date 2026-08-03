<?php
// includes/security.php - Système de sécurité global

/**
 * Nettoyage des entrées contre les injections SQL et XSS
 */

// Empêcher l'accès direct à ce fichier
if (basename($_SERVER['PHP_SELF']) == 'security.php') {
    die('Accès direct interdit');
}

/**
 * Nettoyer une chaîne contre les injections XSS
 */
function cleanXSS($data) {
    if (is_null($data)) return '';
    if (is_array($data)) {
        return array_map('cleanXSS', $data);
    }
    // Supprimer les balises HTML potentiellement dangereuses
    $data = strip_tags($data, '<p><br><strong><em><i><b><u><ul><li><ol><h1><h2><h3><h4><h5><h6><a><img>}<td><th><thead><tbody><blockquote><code><pre><span><div><figure><figcaption>');
    // Convertir les entités HTML
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $data = html_entity_decode($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

/**
 * Nettoyer une chaîne pour les requêtes SQL (protection supplémentaire)
 */
function cleanSQL($data) {
    if (is_null($data)) return '';
    if (is_array($data)) {
        return array_map('cleanSQL', $data);
    }
    // Supprimer les caractères potentiellement dangereux
    $data = trim($data);
    $data = str_replace(array("'", '"', ';', '--', '/*', '*/', '\\'), '', $data);
    return $data;
}

/**
 * Valider et nettoyer un email
 */
function validateEmail($email) {
    $email = trim($email);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return false;
}

/**
 * Valider une URL
 */
function validateURL($url) {
    $url = trim($url);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    return false;
}

/**
 * Générer un token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier le token CSRF
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Protéger les variables superglobales
 */
function sanitizeSuperGlobals() {
    // Nettoyer $_GET
    if (!empty($_GET)) {
        $_GET = cleanXSS($_GET);
    }
    
    // Nettoyer $_POST
    if (!empty($_POST)) {
        $_POST = cleanXSS($_POST);
    }
    
    // Nettoyer $_REQUEST
    if (!empty($_REQUEST)) {
        $_REQUEST = cleanXSS($_REQUEST);
    }
    
    // Nettoyer $_COOKIE
    if (!empty($_COOKIE)) {
        $_COOKIE = cleanXSS($_COOKIE);
    }
}

/**
 * Vérifier si une chaîne contient des tentatives d'injection
 */
function hasInjectionAttempt($string) {
    $patterns = [
        '/union\s+select/i',
        '/select\s+.*\s+from/i',
        '/insert\s+into/i',
        '/delete\s+from/i',
        '/drop\s+table/i',
        '/alter\s+table/i',
        '/create\s+table/i',
        '/update\s+.*\s+set/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/xp_cmdshell/i',
        '/<script/i',
        '/javascript:/i',
        '/onload=/i',
        '/onerror=/i',
        '/onclick=/i',
        '/onmouseover=/i',
        '/eval\s*\(/i',
        '/base64_decode/i',
        '/gzinflate/i',
        '/shell_exec/i',
        '/passthru/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $string)) {
            return true;
        }
    }
    return false;
}

/**
 * Scanner toutes les entrées pour détecter des injections
 */
function scanForInjections() {
    $sources = [
        'GET' => $_GET,
        'POST' => $_POST,
        'COOKIE' => $_COOKIE,
        'REQUEST' => $_REQUEST
    ];
    
    foreach ($sources as $source_name => $source) {
        foreach ($source as $key => $value) {
            if (is_string($value)) {
                if (hasInjectionAttempt($value)) {
                    // Journaliser la tentative d'injection
                    error_log("Injection détectée dans $source_name: $key = $value - IP: " . $_SERVER['REMOTE_ADDR'] . " - " . date('Y-m-d H:i:s'));
                    // Rediriger vers une page d'erreur
                    header('HTTP/1.0 403 Forbidden');
                    die('Accès interdit - Activité suspecte détectée');
                }
            } elseif (is_array($value)) {
                foreach ($value as $subkey => $subvalue) {
                    if (is_string($subvalue) && hasInjectionAttempt($subvalue)) {
                        error_log("Injection détectée dans $source_name[$key][$subkey]: $subvalue - IP: " . $_SERVER['REMOTE_ADDR']);
                        header('HTTP/1.0 403 Forbidden');
                        die('Accès interdit - Activité suspecte détectée');
                    }
                }
            }
        }
    }
}

/**
 * Générer un mot de passe sécurisé
 */
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Vérifier la force d'un mot de passe
 */
function checkPasswordStrength($password) {
    $strength = 0;
    if (strlen($password) >= 8) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[!@#$%^&*()_+]/', $password)) $strength++;
    return $strength;
}

/**
 * Journaliser les actions importantes
 */
function logAction($action, $details = '') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_email = $_SESSION['user_email'] ?? 'non connecté';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
    
    $log = date('Y-m-d H:i:s') . " | User: $user_id ($user_email) | IP: $ip | Action: $action | Details: $details\n";
    
    $log_file = dirname(__DIR__) . '/logs/actions.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log, FILE_APPEND);
}

/**
 * Limiter le taux de requêtes (Rate Limiting)
 */
function rateLimit($key, $maxAttempts = 10, $timeWindow = 60) {
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    $now = time();
    // Nettoyer les anciennes tentatives
    $_SESSION['rate_limit'][$key] = array_filter($_SESSION['rate_limit'][$key], function($timestamp) use ($now, $timeWindow) {
        return $timestamp > $now - $timeWindow;
    });
    
    // Vérifier le nombre de tentatives
    if (count($_SESSION['rate_limit'][$key]) >= $maxAttempts) {
        return false;
    }
    
    // Ajouter la tentative actuelle
    $_SESSION['rate_limit'][$key][] = $now;
    return true;
}

/**
 * Valider une adresse IP
 */
function validateIP($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

/**
 * Détecter les bots malveillants
 */
function isMaliciousBot() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $bad_bots = ['sqlmap', 'nikto', 'wpscan', 'curl', 'wget', 'python-requests', 'Go-http-client'];
    
    foreach ($bad_bots as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}


// Exécuter automatiquement la protection
sanitizeSuperGlobals();
scanForInjections();

// Bloquer les bots malveillants
if (isMaliciousBot()) {
    error_log("Bot malveillant bloqué: " . $_SERVER['HTTP_USER_AGENT'] . " - IP: " . $_SERVER['REMOTE_ADDR']);
    header('HTTP/1.0 403 Forbidden');
    die('Accès interdit');
}

// Ajouter cette fonction pour décoder le HTML sans le nettoyer
function decodeHTML($data) {
    if (is_null($data)) return '';
    if (is_array($data)) {
        return array_map('decodeHTML', $data);
    }
    return html_entity_decode($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>