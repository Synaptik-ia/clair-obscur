<?php
require_once 'includes/config.php';

$page_title = 'Contactez-nous';
$page_description = 'Contactez Synaptik IA Solutions pour discuter de votre projet IA ou IT. Audit gratuit, devis personnalisé, réponse sous 24h.';

// Récupérer les messages de session (après redirection)
$success_message = isset($_SESSION['contact_success']) ? $_SESSION['contact_success'] : null;
$error_message = isset($_SESSION['contact_error']) ? $_SESSION['contact_error'] : null;

// Nettoyer les messages de session
unset($_SESSION['contact_success']);
unset($_SESSION['contact_error']);

include 'header.php';
?>

<main>
    <section class="contact-page">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-form-col">
                    <h1>Parlons de Votre Projet</h1>
                    <p>Que ce soit pour un audit IA, une infrastructure IT, ou simplement un conseil, remplissez ce formulaire et nous vous répondrons sous 24h.</p>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    
                    <form class="contact-form" action="contact-process.php" method="POST" id="contactForm">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        
                        <div class="form-group">
                            <label for="nom">Nom complet *</label>
                            <input type="text" id="nom" name="nom" required value="<?php echo isset($_SESSION['old_nom']) ? htmlspecialchars($_SESSION['old_nom']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise">Entreprise / Organisation</label>
                            <input type="text" id="entreprise" name="entreprise" value="<?php echo isset($_SESSION['old_entreprise']) ? htmlspecialchars($_SESSION['old_entreprise']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email professionnel *</label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo isset($_SESSION['old_telephone']) ? htmlspecialchars($_SESSION['old_telephone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sujet">Sujet *</label>
                            <select id="sujet" name="sujet" required>
                                <option value="">Sélectionnez un domaine</option>
                                <option value="ia-creation" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'ia-creation') ? 'selected' : ''; ?>>IA Création & Marketing</option>
                                <option value="ia-automatisation" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'ia-automatisation') ? 'selected' : ''; ?>>IA Automatisation</option>
                                <option value="ia-agents" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'ia-agents') ? 'selected' : ''; ?>>IA Installation d'Agents</option>
                                <option value="ia-apps" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'ia-apps') ? 'selected' : ''; ?>>IA Création d'Applications</option>
                                <option value="ia-sites" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'ia-sites') ? 'selected' : ''; ?>>IA Création de Sites Web</option>
                                <option value="it-support" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'it-support') ? 'selected' : ''; ?>>IT - Dépannage & Support</option>
                                <option value="it-conseil" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'it-conseil') ? 'selected' : ''; ?>>IT - Conseil Stratégique</option>
                                <option value="it-admin" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'it-admin') ? 'selected' : ''; ?>>IT - Admin Systèmes & Réseaux</option>
                                <option value="it-hebergement" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'it-hebergement') ? 'selected' : ''; ?>>IT - Hébergement Web</option>
                                <option value="autre" <?php echo (isset($_SESSION['old_sujet']) && $_SESSION['old_sujet'] == 'autre') ? 'selected' : ''; ?>>Autre projet</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Décrivez brièvement votre besoin..."><?php echo isset($_SESSION['old_message']) ? htmlspecialchars($_SESSION['old_message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Envoyer ma demande →</button>
                        
                        <p class="form-note">* Champs obligatoires. Nous traitons vos données avec confidentialité.</p>
                    </form>
                </div>
                
                <div class="contact-info-col">
                    <h2>Nos Coordonnées</h2>
                    
                    <div class="info-block">
                        <div class="info-icon">📧</div>
                        <div>
                            <strong>Email</strong><br>
                            <a href="mailto:contact@synaptik-ia.fr">contact@synaptik-ia.fr</a>
                        </div>
                    </div>
                    
                    <div class="info-block">
                        <div class="info-icon">📞</div>
                        <div>
                            <strong>Téléphone</strong><br>
                            <a href="tel:+33123456789">+33 (0)1 23 45 67 89</a>
                        </div>
                    </div>
                    
                    <div class="info-block">
                        <div class="info-icon">📍</div>
                        <div>
                            <strong>Adresse</strong><br>
                            123 avenue de la Technologie<br>
                            75012 Paris, France
                        </div>
                    </div>
                    
                    <div class="info-block">
                        <div class="info-icon">⏰</div>
                        <div>
                            <strong>Horaires</strong><br>
                            Lundi - Vendredi : 9h - 18h<br>
                            Support urgence : 24/7
                        </div>
                    </div>
                    
                    <div class="badge-serieux">
                        <span>🏅</span> Réponse sous 24h ouvrées
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<script>
grecaptcha.ready(function() {
    grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'submit'}).then(function(token) {
        document.getElementById('recaptcha_token').value = token;
    });
});
</script>

<?php
// Nettoyer les anciennes valeurs de session
unset($_SESSION['old_nom']);
unset($_SESSION['old_entreprise']);
unset($_SESSION['old_email']);
unset($_SESSION['old_telephone']);
unset($_SESSION['old_sujet']);
unset($_SESSION['old_message']);

include 'footer.php';
?>