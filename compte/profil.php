<?php
// compte/profil.php - Informations client et modification

require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
redirigerSiNonConnecte();

$page_title = "Mon profil - Clair-Obscur";
$page_description = "Gérez vos informations personnelles, votre adresse de livraison et vos préférences.";

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Récupération des informations actuelles
$sql = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: ' . SITE_URL . 'compte/connexion.php');
    exit();
}

$succes = '';
$erreurs = [];

// Traitement de la modification du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_profil') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $pays = trim($_POST['pays'] ?? 'France');
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        
        if (empty($nom)) {
            $erreurs[] = "Le nom est requis.";
        }
        if (empty($prenom)) {
            $erreurs[] = "Le prénom est requis.";
        }
        
        if (empty($erreurs)) {
            $sql_update = "UPDATE utilisateurs 
                           SET nom = :nom, prenom = :prenom, telephone = :telephone, 
                               adresse = :adresse, code_postal = :code_postal, 
                               ville = :ville, pays = :pays, newsletter = :newsletter 
                           WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone ?: null,
                ':adresse' => $adresse ?: null,
                ':code_postal' => $code_postal ?: null,
                ':ville' => $ville ?: null,
                ':pays' => $pays,
                ':newsletter' => $newsletter,
                ':id' => $user_id
            ]);
            
            // Mise à jour de la session
            $_SESSION['user_nom'] = $prenom . ' ' . $nom;
            
            $succes = "Vos informations ont été mises à jour.";
            
            // Recharger les données
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();
        }
    }
    
    // Changement de mot de passe
    elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Vérification du mot de passe actuel
        if (!password_verify($current_password, $user['password'])) {
            $erreurs[] = "Le mot de passe actuel est incorrect.";
        }
        
        if (strlen($new_password) < 6) {
            $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        }
        
        if ($new_password !== $confirm_password) {
            $erreurs[] = "Les nouveaux mots de passe ne correspondent pas.";
        }
        
        if (empty($erreurs)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE utilisateurs SET password = :password WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':password' => $hashed_password,
                ':id' => $user_id
            ]);
            
            $succes = "Votre mot de passe a été modifié avec succès.";
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Menu latéral -->
            <div class="list-group shadow-sm">
                <a href="profil.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-user"></i> Mon profil
                </a>
                <a href="commandes.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-bag"></i> Mes commandes
                </a>
                <a href="deconnexion.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if ($succes): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $succes; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?php echo $erreur; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Informations personnelles -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card"></i> Mes informations personnelles</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="update_profil">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email_info" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email_info" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-muted">L'adresse email ne peut pas être modifiée.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Adresse de livraison par défaut</h6>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($user['code_postal'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($user['ville'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <select class="form-select" id="pays" name="pays">
                                    <option value="France" <?php echo ($user['pays'] ?? 'France') == 'France' ? 'selected' : ''; ?>>France</option>
                                    <option value="Belgique" <?php echo ($user['pays'] ?? '') == 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                    <option value="Suisse" <?php echo ($user['pays'] ?? '') == 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                    <option value="Canada" <?php echo ($user['pays'] ?? '') == 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Autre" <?php echo ($user['pays'] ?? '') == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr>

                        <h6 class="mb-3">Préférences</h6>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" <?php echo ($user['newsletter'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="newsletter">
                                Je souhaite recevoir la newsletter et être informé des nouvelles parutions
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Changement de mot de passe -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-key"></i> Changer mon mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Minimum 6 caractères</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Date d'inscription -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    Membre depuis le <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>