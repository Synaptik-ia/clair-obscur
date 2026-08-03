<?php
// contact/index.php - Page de contact avec Google reCAPTCHA v3

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$page_title = "Contact - Clair-Obscur";
$page_description = "Contactez la maison d'édition Clair-Obscur. Posez-nous vos questions, suggestions ou demandes de renseignements.";

$message_envoye = false;
$erreurs = [];

// Configuration reCAPTCHA v3
define('RECAPTCHA_SITE_KEY', env('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY', ''));

// Rate limiting pour le formulaire de contact
if (!rateLimit('contact_form', 5, 300)) {
    $erreurs[] = "Trop de tentatives. Veuillez attendre 5 minutes avant de réessayer.";
}

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $erreurs[] = "Erreur de sécurité. Veuillez rafraîchir la page et réessayer.";
    } else {
        // Nettoyage des entrées
        $nom = cleanSQL(trim($_POST['nom'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $sujet = cleanSQL(trim($_POST['sujet'] ?? ''));
        $message = cleanSQL(trim($_POST['message'] ?? ''));
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        
        // Validations
        if (empty($nom)) {
            $erreurs[] = "Veuillez saisir votre nom.";
        } elseif (strlen($nom) > 100) {
            $erreurs[] = "Le nom est trop long.";
        }
        
        if (empty($email)) {
            $erreurs[] = "Veuillez saisir votre email.";
        } elseif (!validateEmail($email)) {
            $erreurs[] = "Veuillez saisir un email valide.";
        }
        
        if (empty($sujet)) {
            $erreurs[] = "Veuillez saisir un sujet.";
        } elseif (strlen($sujet) > 200) {
            $erreurs[] = "Le sujet est trop long.";
        }
        
        if (empty($message)) {
            $erreurs[] = "Veuillez saisir votre message.";
        } elseif (strlen($message) < 10) {
            $erreurs[] = "Votre message est trop court (minimum 10 caractères).";
        } elseif (strlen($message) > 5000) {
            $erreurs[] = "Votre message est trop long (maximum 5000 caractères).";
        }
        
        // Vérification reCAPTCHA v3
        if (empty($recaptcha_response)) {
            $erreurs[] = "Veuillez vérifier que vous n'êtes pas un robot.";
        } else {
            // Appel à l'API Google reCAPTCHA
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => RECAPTCHA_SECRET_KEY,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];
            
            $recaptcha_options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($recaptcha_data)
                ]
            ];
            
            $recaptcha_context = stream_context_create($recaptcha_options);
            $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
            $recaptcha_json = json_decode($recaptcha_result, true);
            
            // Score minimum acceptable (0.5 est un bon seuil, ajustable)
            if (!$recaptcha_json['success'] || $recaptcha_json['score'] < 0.5) {
                $erreurs[] = "La vérification anti-robot a échoué. Veuillez réessayer.";
                logAction('RECAPTCHA_FAIL', "Score: " . ($recaptcha_json['score'] ?? 'inconnu') . " - IP: " . $_SERVER['REMOTE_ADDR']);
            }
        }
        
        // Détection d'injection dans le message
        if (hasInjectionAttempt($message)) {
            $erreurs[] = "Votre message contient des caractères non autorisés.";
            logAction('CONTACT_INJECTION_ATTEMPT', "Tentative d'injection depuis le formulaire de contact par $email");
        }
        
        // Envoi de l'email
        if (empty($erreurs)) {
            $to = ADMIN_EMAIL;
            $headers = "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
            
            $corps_message = "Nom: $nom\n";
            $corps_message .= "Email: $email\n";
            $corps_message .= "Sujet: $sujet\n";
            $corps_message .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            $corps_message .= "reCAPTCHA Score: " . ($recaptcha_json['score'] ?? 'N/A') . "\n\n";
            $corps_message .= "Message:\n$message\n";
            
            if (mail($to, "[Clair-Obscur] " . $sujet, $corps_message, $headers)) {
                $message_envoye = true;
                logAction('CONTACT_FORM', "Message envoyé par $email - reCAPTCHA score: " . ($recaptcha_json['score'] ?? 'N/A'));
            } else {
                $erreurs[] = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
                error_log("Erreur d'envoi email depuis formulaire de contact: $email");
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4 text-center"><i class="fas fa-envelope"></i> Contactez-nous</h1>
            
            <?php if ($message_envoye): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                    <h5>Message envoyé !</h5>
                    <p>Nous vous répondrons dans les plus brefs délais.</p>
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary mt-2">Retour à l'accueil</a>
                </div>
            <?php else: ?>
                
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?php echo cleanXSS($erreur); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Informations de contact -->
                    <div class="col-md-5 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-address-card"></i> Nos coordonnées</h5>
                                <hr>
                                <p>
                                    <i class="fas fa-envelope text-primary me-2"></i> 
                                    <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a>
                                </p>
                                <hr>
                                <p class="small text-muted">
                                    <i class="fas fa-shield-alt"></i> Formulaire sécurisé - Protection reCAPTCHA
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulaire de contact -->
                    <div class="col-md-7 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-paper-plane"></i> Envoyez-nous un message</h5>
                                <hr>
                                
                                <form method="POST" action="" id="contactForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                    
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom complet *</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo cleanXSS($_POST['nom'] ?? ''); ?>" maxlength="100" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Adresse email *</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo cleanXSS($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="sujet" class="form-label">Sujet *</label>
                                        <select class="form-select" id="sujet" name="sujet" required>
                                            <option value="">-- Choisissez un sujet --</option>
                                            <option value="Information sur un livre" <?php echo (($_POST['sujet'] ?? '') == 'Information sur un livre') ? 'selected' : ''; ?>>Information sur un livre</option>
                                            <option value="Commande / Paiement" <?php echo (($_POST['sujet'] ?? '') == 'Commande / Paiement') ? 'selected' : ''; ?>>Commande / Paiement</option>
                                            <option value="Soumettre un manuscrit" <?php echo (($_POST['sujet'] ?? '') == 'Soumettre un manuscrit') ? 'selected' : ''; ?>>Soumettre un manuscrit</option>
                                            <option value="Partenariat" <?php echo (($_POST['sujet'] ?? '') == 'Partenariat') ? 'selected' : ''; ?>>Partenariat</option>
                                            <option value="Autre" <?php echo (($_POST['sujet'] ?? '') == 'Autre') ? 'selected' : ''; ?>>Autre</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message *</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" maxlength="5000" required><?php echo cleanXSS($_POST['message'] ?? ''); ?></textarea>
                                        <small class="text-muted">Maximum 5000 caractères</small>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="cgv_accept" name="cgv_accept" required>
                                        <label class="form-check-label" for="cgv_accept">
                                            J'accepte que mes données soient traitées pour ma demande. 
                                            Voir nos <a href="<?php echo SITE_URL; ?>cgv/" target="_blank">conditions générales</a>.
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                        <i class="fas fa-paper-plane"></i> Envoyer le message
                                    </button>
                                </form>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt"></i> Ce site est protégé par reCAPTCHA v3 de Google
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Google reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification...';
    
    grecaptcha.ready(function() {
        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'contact'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
            document.getElementById('contactForm').submit();
        });
    });
});
</script>

<style>
/* Style pour le bouton pendant le chargement */
.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>

<?php include '../includes/footer.php'; ?>