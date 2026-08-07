<?php
// compte/mot-de-passe-oublie.php - Récupération de mot de passe

require_once '../config/database.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: ' . SITE_URL . 'compte/profil.php');
    exit();
}

$page_title = "Mot de passe oublié - Clair-Obscur";
$page_description = "Réinitialisez votre mot de passe pour accéder à votre compte client Clair-Obscur.";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';
$step = 1; // 1: demande email, 2: code validation, 3: nouveau mot de passe
$email = '';

// Étape 1 : Demande d'email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Étape 1 - Envoi du code
    if ($_POST['action'] === 'send_code') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = "Veuillez saisir votre adresse email.";
            $message_type = "danger";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Veuillez saisir une adresse email valide.";
            $message_type = "danger";
        } else {
            // Vérifier si l'email existe
            $sql = "SELECT id, nom, prenom FROM utilisateurs WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Générer un code de réinitialisation
                $reset_code = sprintf("%06d", mt_rand(0, 999999));
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Stocker le code en base
                $sql_insert = "INSERT INTO password_resets (email, token, expires_at) 
                               VALUES (:email, :token, :expires_at)
                               ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->execute([
                    ':email' => $email,
                    ':token' => $reset_code,
                    ':expires_at' => $expires
                ]);
                
                // Envoyer l'email (simulation - en production utiliser PHPMailer)
                $to = $email;
                $subject = "Réinitialisation de votre mot de passe - Clair-Obscur";
                $body = "Bonjour " . $user['prenom'] . ",\n\n";
                $body .= "Vous avez demandé la réinitialisation de votre mot de passe.\n\n";
                $body .= "Votre code de validation est : " . $reset_code . "\n\n";
                $body .= "Ce code est valable 15 minutes.\n\n";
                $body .= "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.\n\n";
                $body .= "Cordialement,\nL'équipe Clair-Obscur";
                
                $headers = "From: " . ADMIN_EMAIL . "\r\n";
                $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                
                // En production, utilisez PHPMailer pour un envoi fiable
                if (mail($to, $subject, $body, $headers)) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_code'] = $reset_code;
                    $step = 2;
                    $message = "Un code de validation a été envoyé à votre adresse email.";
                    $message_type = "success";
                } else {
                    $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                    $message_type = "danger";
                }
            } else {
                // Ne pas révéler que l'email n'existe pas pour des raisons de sécurité
                $_SESSION['reset_email'] = $email;
                $step = 2;
                $message = "Si cette adresse email existe dans notre base, vous allez recevoir un code de validation.";
                $message_type = "info";
            }
        }
    }
    
    // Étape 2 - Vérification du code
    elseif ($_POST['action'] === 'verify_code') {
        $code = trim($_POST['code'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';
        
        if (empty($code)) {
            $message = "Veuillez saisir le code de validation.";
            $message_type = "danger";
        } else {
            // Vérifier le code
            $sql = "SELECT * FROM password_resets 
                    WHERE email = :email AND token = :token AND expires_at > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email, ':token' => $code]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                $_SESSION['reset_verified'] = true;
                $step = 3;
                $message = "Code vérifié. Veuillez choisir un nouveau mot de passe.";
                $message_type = "success";
            } else {
                $message = "Code invalide ou expiré. Veuillez recommencer.";
                $message_type = "danger";
            }
        }
    }
    
    // Étape 3 - Nouveau mot de passe
    elseif ($_POST['action'] === 'reset_password') {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $email = $_SESSION['reset_email'] ?? '';
        
        if (empty($password)) {
            $message = "Le mot de passe est requis.";
            $message_type = "danger";
        } elseif (strlen($password) < 6) {
            $message = "Le mot de passe doit contenir au moins 6 caractères.";
            $message_type = "danger";
        } elseif ($password !== $password_confirm) {
            $message = "Les mots de passe ne correspondent pas.";
            $message_type = "danger";
        } elseif (!isset($_SESSION['reset_verified']) || !$_SESSION['reset_verified']) {
            $message = "Session invalide. Veuillez recommencer la procédure.";
            $message_type = "danger";
        } else {
            // Mettre à jour le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE utilisateurs SET password = :password WHERE email = :email";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':password' => $hashed_password,
                ':email' => $email
            ]);
            
            // Supprimer le token de réinitialisation
            $sql_delete = "DELETE FROM password_resets WHERE email = :email";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->execute([':email' => $email]);
            
            // Nettoyer la session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_verified']);
            
            $_SESSION['flash_message'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
            $_SESSION['flash_type'] = "success";
            header('Location: connexion.php');
            exit();
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
                    <h3 class="mb-0"><i class="fas fa-key"></i> Mot de passe oublié</h3>
                </div>
                <div class="card-body">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Étape 1 : Demande d'email -->
                    <?php if ($step == 1): ?>
                        <p class="text-muted mb-4">
                            Saisissez votre adresse email. Vous recevrez un code de validation pour réinitialiser votre mot de passe.
                        </p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="send_code">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Envoyer le code
                                </button>
                                <a href="connexion.php" class="btn btn-link">
                                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Étape 2 : Saisie du code -->
                    <?php if ($step == 2): ?>
                        <p class="text-muted mb-4">
                            Un code de validation a été envoyé à <strong><?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?></strong>.<br>
                            Saisissez-le ci-dessous (valable 15 minutes).
                        </p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="verify_code">
                            
                            <div class="mb-3">
                                <label for="code" class="form-label">Code de validation</label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="6 chiffres" maxlength="6" required autofocus>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Vérifier le code
                                </button>
                                <button type="submit" name="action" value="send_code" class="btn btn-link">
                                    <i class="fas fa-redo"></i> Renvoyer le code
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Étape 3 : Nouveau mot de passe -->
                    <?php if ($step == 3): ?>
                        <p class="text-muted mb-4">
                            Choisissez un nouveau mot de passe pour votre compte.
                        </p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="reset_password">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Minimum 6 caractères</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Réinitialiser le mot de passe
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>