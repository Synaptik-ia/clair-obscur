<?php
// admin/login.php - Page de connexion pour l'administration

require_once '../config/database.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté en tant qu'admin
if (estAdmin()) {
    header('Location: index.php');
    exit();
}

// Rediriger si simple utilisateur connecté (pas admin)
if (estConnecte()) {
    $_SESSION['flash_message'] = "Vous n'avez pas les droits d'accès à l'administration.";
    $_SESSION['flash_type'] = "danger";
    header('Location: ' . SITE_URL);
    exit();
}

// Initialisation des variables
$error = '';
$email = '';
$attempts = 0;

// Gestion des tentatives de connexion (stockées en session)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = 0;
}

// Vérifier si trop de tentatives (5 tentatives, bloqué 15 minutes)
$current_time = time();
if ($_SESSION['login_attempts'] >= 5 && ($current_time - $_SESSION['last_attempt_time']) < 900) {
    $remaining = 900 - ($current_time - $_SESSION['last_attempt_time']);
    $minutes = ceil($remaining / 60);
    $error = "Trop de tentatives de connexion. Veuillez réessayer dans " . $minutes . " minute(s).";
}

// Générer un token CSRF pour le formulaire
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$page_title = "Connexion Administration - Clair-Obscur";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    
    // Vérification CSRF (token)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        
        // Nettoyage et validation des entrées POST
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validation basique
        if (empty($email)) {
            $error = "Veuillez saisir votre email.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Veuillez saisir un email valide.";
        } elseif (empty($password)) {
            $error = "Veuillez saisir votre mot de passe.";
        } elseif (strlen($password) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            
            // Protection contre les injections SQL (via requête préparée)
            $db = new Database();
            $conn = $db->getConnection();
            
            // Requête préparée avec bindParam - protection automatique contre les injections SQL
            $sql = "SELECT id, email, password, nom, prenom, is_admin FROM utilisateurs WHERE email = :email AND is_admin = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérification du mot de passe
            if ($admin && password_verify($password, $admin['password'])) {
                // Connexion réussie - Réinitialiser les tentatives
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = 0;
                
                // Régénérer l'ID de session pour éviter la fixation de session
                session_regenerate_id(true);
                
                // Stocker les informations de l'admin
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_nom'] = $admin['prenom'] . ' ' . $admin['nom'];
                $_SESSION['is_admin'] = $admin['is_admin'];
                
                // Journaliser la connexion réussie
                error_log("Admin login success: " . $email . " - " . date('Y-m-d H:i:s'));
                
                $_SESSION['flash_message'] = "Bienvenue dans l'administration, " . htmlspecialchars($admin['prenom']) . " !";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php');
                exit();
            } else {
                // Incrémenter le compteur de tentatives
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                
                // Journaliser la tentative échouée
                error_log("Admin login failed: " . $email . " - " . date('Y-m-d H:i:s'));
                
                $error = "Email ou mot de passe incorrect. Vous devez posséder des droits d'administrateur.";
                if ($_SESSION['login_attempts'] >= 3) {
                    $error .= " Il vous reste " . (5 - $_SESSION['login_attempts']) . " tentative(s) avant blocage.";
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration - Clair-Obscur</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --clair: #f5efe7;
            --obscur: #1a1a2e;
            --accent-dore: #c9a03d;
            --bandeau: #b0a893;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--obscur) 0%, #2a2a3e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: var(--obscur);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .login-header i {
            font-size: 3rem;
            color: var(--accent-dore);
            margin-bottom: 15px;
        }
        .login-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            margin: 0;
        }
        .login-header p {
            margin: 5px 0 0;
            opacity: 0.7;
            font-size: 0.8rem;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: var(--accent-dore);
            box-shadow: 0 0 0 0.2rem rgba(201, 160, 61, 0.25);
        }
        .btn-login {
            background-color: var(--obscur);
            color: white;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--accent-dore);
            color: var(--obscur);
            transform: translateY(-2px);
        }
        .alert-custom {
            border-radius: 10px;
            border-left: 4px solid var(--accent-dore);
            background-color: #fff8e8;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: var(--clair);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .back-link a:hover {
            color: var(--accent-dore);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-crown"></i>
                <h1>Administration Clair-Obscur</h1>
                <p>Accès réservé aux administrateurs</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-custom" style="background-color: #f8d7da; border-left-color: #dc3545; color: #721c24;">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="this.querySelector('button').disabled=true;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
            </div>
        </div>
        <div class="back-link">
            <a href="<?php echo SITE_URL; ?>">
                <i class="fas fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </div>
    
    <script>
        // Éviter le double envoi du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';
            btn.disabled = true;
        });
    </script>
</body>
</html>