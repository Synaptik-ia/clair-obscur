<?php
// compte/inscription.php - Inscription client

require_once '../config/database.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: ' . SITE_URL . 'compte/profil.php');
    exit();
}

$page_title = "Inscription - Clair-Obscur";
$page_description = "Créez votre compte client Clair-Obscur pour passer commande, suivre vos achats et laisser des commentaires.";

$db = new Database();
$conn = $db->getConnection();

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    // Validations
    if (empty($email)) {
        $erreurs[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Veuillez saisir une adresse email valide.";
    } else {
        // Vérifier si l'email existe déjà
        $sql_check = "SELECT id FROM utilisateurs WHERE email = :email";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':email' => $email]);
        if ($stmt_check->fetch()) {
            $erreurs[] = "Cette adresse email est déjà utilisée.";
        }
    }
    
    if (empty($password)) {
        $erreurs[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 8) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $erreurs[] = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $erreurs[] = "Le mot de passe doit contenir au moins un chiffre.";
    }
    
    if ($password !== $password_confirm) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($nom)) {
        $erreurs[] = "Le nom est requis.";
    }
    
    if (empty($prenom)) {
        $erreurs[] = "Le prénom est requis.";
    }
    
    // Vérification des CGV
    if (!isset($_POST['cgv_accept'])) {
        $erreurs[] = "Vous devez accepter les conditions générales de vente.";
    }
    
    // Vérification anti-spam
    if (!isset($_POST['captcha']) || $_POST['captcha'] != 8) {
        $erreurs[] = "Réponse au calcul anti-spam incorrecte.";
    }
    
    // Inscription
    if (empty($erreurs)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql_insert = "INSERT INTO utilisateurs (email, password, nom, prenom, telephone, date_inscription) 
                       VALUES (:email, :password, :nom, :prenom, :telephone, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        
        try {
            $stmt_insert->execute([
                ':email' => $email,
                ':password' => $hashed_password,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone ?: null
            ]);
            
            // Connexion automatique après inscription
            $user_id = $conn->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_nom'] = $prenom . ' ' . $nom;
            $_SESSION['is_admin'] = 0;
            
            $_SESSION['flash_message'] = "Inscription réussie ! Bienvenue chez Clair-Obscur.";
            $_SESSION['flash_type'] = "success";
            header('Location: ' . SITE_URL . 'compte/profil.php');
            exit();
            
        } catch (PDOException $e) {
            $erreurs[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h3 class="mb-0"><i class="fas fa-user-plus"></i> Créer un compte</h3>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erreurs as $erreur): ?>
                                    <li><?php echo $erreur; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <!-- Informations de connexion -->
                        <h5 class="mb-3"><i class="fas fa-lock"></i> Informations de connexion</h5>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Minimum 6 caractères</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Informations personnelles -->
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informations personnelles</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                            <small class="text-muted">Optionnel, utilisé uniquement pour le suivi de commande</small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Adresse de livraison (optionnelle à l'inscription) -->
                        <h5 class="mb-3"><i class="fas fa-home"></i> Adresse (optionnelle)</h5>
                        <p class="small text-muted">Vous pourrez ajouter ou modifier votre adresse plus tard dans votre profil.</p>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($_POST['code_postal'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <select class="form-select" id="pays" name="pays">
                                    <option value="France" selected>France</option>
                                    <option value="Belgique">Belgique</option>
                                    <option value="Suisse">Suisse</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Validation -->
                        <div class="mb-3">
                            <label class="form-label">Anti-spam : Combien font 5 + 3 ? *</label>
                            <input type="number" class="form-control" name="captcha" style="width: 100px;" required>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="cgv_accept" name="cgv_accept" required>
                            <label class="form-check-label" for="cgv_accept">
                                J'accepte les <a href="<?php echo SITE_URL; ?>cgv/" target="_blank">conditions générales de vente</a> *
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-check"></i> S'inscrire
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Déjà inscrit ? 
                            <a href="connexion.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt"></i> Connectez-vous
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>