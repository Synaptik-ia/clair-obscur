<?php
require_once 'includes/config.php';

// Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

// Nettoyer et valider les entrées
$nom = isset($_POST['nom']) ? clean_input($_POST['nom']) : '';
$entreprise = isset($_POST['entreprise']) ? clean_input($_POST['entreprise']) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$telephone = isset($_POST['telephone']) ? clean_input($_POST['telephone']) : '';
$sujet = isset($_POST['sujet']) ? clean_input($_POST['sujet']) : '';
$message = isset($_POST['message']) ? clean_input($_POST['message']) : '';
$recaptcha_token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

// Sauvegarder les anciennes valeurs pour pré-remplir en cas d'erreur
$_SESSION['old_nom'] = $nom;
$_SESSION['old_entreprise'] = $entreprise;
$_SESSION['old_email'] = $email;
$_SESSION['old_telephone'] = $telephone;
$_SESSION['old_sujet'] = $sujet;
$_SESSION['old_message'] = $message;

// Validation des champs obligatoires
$errors = [];

if (empty($nom)) {
    $errors[] = "Le nom est obligatoire.";
}
if (empty($email)) {
    $errors[] = "L'email est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide.";
}
if (empty($sujet)) {
    $errors[] = "Le sujet est obligatoire.";
}
if (empty($message)) {
    $errors[] = "Le message est obligatoire.";
}

// Vérification reCAPTCHA
if (empty($recaptcha_token)) {
    $errors[] = "Veuillez vérifier que vous n'êtes pas un robot.";
} else {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_token
    ];
    
    $recaptcha_options = [
        'http' => [
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        ]
    ];
    
    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
    $recaptcha_json = json_decode($recaptcha_result);
    
    if (!$recaptcha_json->success || $recaptcha_json->score < 0.5) {
        $errors[] = "La vérification anti-robot a échoué. Veuillez réessayer.";
    }
}

// Si erreurs, rediriger avec message
if (!empty($errors)) {
    $_SESSION['contact_error'] = implode(' ', $errors);
    header('Location: contact.php');
    exit;
}

// Préparer le sujet de l'email en fonction du choix
$sujet_options = [
    'ia-creation' => 'IA Création & Marketing',
    'ia-automatisation' => 'IA Automatisation',
    'ia-agents' => 'IA Installation d\'Agents',
    'ia-apps' => 'IA Création d\'Applications',
    'ia-sites' => 'IA Création de Sites Web',
    'it-support' => 'IT - Dépannage & Support',
    'it-conseil' => 'IT - Conseil Stratégique',
    'it-admin' => 'IT - Admin Systèmes & Réseaux',
    'it-hebergement' => 'IT - Hébergement Web',
    'autre' => 'Autre projet'
];
$sujet_libelle = isset($sujet_options[$sujet]) ? $sujet_options[$sujet] : $sujet;

// Construction de l'email HTML
$email_subject = "[Synaptik IA] Demande de contact - $sujet_libelle";
$email_message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0f172a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8fafc; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #0f172a; }
        .footer { background: #e2e8f0; padding: 10px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Nouvelle demande de contact</h2>
        </div>
        <div class='content'>
            <div class='field'><span class='label'>Nom :</span> $nom</div>
            <div class='field'><span class='label'>Entreprise :</span> " . ($entreprise ?: 'Non renseigné') . "</div>
            <div class='field'><span class='label'>Email :</span> $email</div>
            <div class='field'><span class='label'>Téléphone :</span> " . ($telephone ?: 'Non renseigné') . "</div>
            <div class='field'><span class='label'>Sujet :</span> $sujet_libelle</div>
            <div class='field'><span class='label'>Message :</span></div>
            <div class='field'>" . nl2br($message) . "</div>
        </div>
        <div class='footer'>
            Message envoyé depuis le formulaire de contact de synaptik-ia.fr
        </div>
    </div>
</body>
</html>
";

// Envoi de l'email
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: $nom <$email>\r\n";
$headers .= "Reply-To: $email\r\n";

$mail_sent = mail(CONTACT_EMAIL, $email_subject, $email_message, $headers);

// Envoi d'un accusé de réception au client
$ack_subject = "Synaptik IA Solutions - Accusé de réception";
$ack_message = "
<html>
<head><style>body{font-family:Arial;line-height:1.6;color:#333;}</style></head>
<body>
    <h2>Bonjour $nom,</h2>
    <p>Nous avons bien reçu votre demande concernant <strong>$sujet_libelle</strong>.</p>
    <p>Notre équipe vous répondra dans les plus brefs délais (sous 24h ouvrées).</p>
    <p>Cordialement,<br><strong>Synaptik IA Solutions</strong></p>
</body>
</html>
";
$ack_headers = "MIME-Version: 1.0\r\n";
$ack_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$ack_headers .= "From: " . CONTACT_EMAIL . "\r\n";
mail($email, $ack_subject, $ack_message, $ack_headers);

// Nettoyer la session
unset($_SESSION['old_nom']);
unset($_SESSION['old_entreprise']);
unset($_SESSION['old_email']);
unset($_SESSION['old_telephone']);
unset($_SESSION['old_sujet']);
unset($_SESSION['old_message']);

// Redirection avec succès
$_SESSION['contact_success'] = "Votre message a bien été envoyé ! Nous vous répondrons sous 24h.";
header('Location: contact.php');
exit;
?>