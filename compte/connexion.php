<?php
// compte/connexion.php - Connexion client

require_once '../config/database.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: ' . SITE_URL . 'compte/profil.php');
    exit();
}

$page_title = "Connexion - Clair-Obscur";
$page_description = "Connectez-vous à votre compte client Clair-Obscur pour accéder à vos commandes, votre profil et vos livres numériques.";

$db = new Database();
$conn = $db->getConnection();

$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email)) {
        $erreur = "Veuillez saisir votre email.";
    } elseif (empty($password)) {
        $erreur = "Veuillez saisir votre mot de passe.";
    } else {
        // Recherche de l'utilisateur
        $sql = "SELECT id, email, password, nom, prenom, is_admin FROM utilisateurs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Se souvenir de moi (cookie 30 jours)
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 3600, '/');
                // Ici vous pourriez stocker le token en base pour auto-login
            }
            
            // Redirection vers la page demandée ou profil
            $redirect = $_GET['redirect'] ?? SITE_URL . 'compte/profil.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-lg-5 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt"></i> Connexion</h3>
                </div>
                <div class="card-body">
                    
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="text-end mt-1">
                                <a href="mot-de-passe-oublie.php" class="small text-decoration-none">Mot de passe oublié ?</a>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">
                            Pas encore de compte ? 
                            <a href="inscription.php" class="text-decoration-none">
                                <i class="fas fa-user-plus"></i> Créer un compte
                            </a>
                        </p>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> Connexion sécurisée
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Informations supplémentaires -->
            <div class="text-center mt-4">
                <p class="small text-muted">
                    En vous connectant, vous acceptez nos 
                    <a href="<?php echo SITE_URL; ?>cgv/">conditions générales de vente</a> 
                    et notre politique de confidentialité.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>