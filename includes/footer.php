</main>

<!-- Flèche de retour en haut de page -->
<div id="back-to-top" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
</div>

<!-- Footer -->
<footer class="bg-dark text-light mt-5 pt-4 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-book-open"></i> Clair-Obscur</h5>
                <p class="small">Maison d'édition indépendante dédiée à la littérature pour adultes. Découvrez des univers uniques et des auteurs talentueux.</p>
                <div class="mt-2">
                    <a href="https://www.facebook.com/profile.php?id=61589894467968" class="text-light me-2" target="_blank"><i class="fab fa-facebook-f"></i></a>
					<a href="https://x.com/clairobeditions" class="text-light me-2" target="_blank"><i class="fab fa-x-twitter"></i></a>
					<a href="https://www.reddit.com/user/ClairObscurEditions/" class="text-light me-2" target="_blank"><i class="fab fa-reddit"></i></a>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <h6>Navigation</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?php echo SITE_URL; ?>" class="text-light text-decoration-none">Accueil</a></li>
                    <li><a href="<?php echo SITE_URL; ?>livres/liste.php" class="text-light text-decoration-none">Livres</a></li>
                    <li><a href="<?php echo SITE_URL; ?>nouvelles/" class="text-light text-decoration-none">Nouvelles</a></li>
                    <li><a href="<?php echo SITE_URL; ?>auteurs/" class="text-light text-decoration-none">Auteurs</a></li>
                    <li><a href="<?php echo SITE_URL; ?>contact/" class="text-light text-decoration-none">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 mb-3">
                <h6>Mon compte</h6>
                <ul class="list-unstyled small">
                    <?php if (estConnecte()): ?>
                        <li><a href="<?php echo SITE_URL; ?>compte/profil.php" class="text-light text-decoration-none">Mon profil</a></li>
                        <li><a href="<?php echo SITE_URL; ?>compte/commandes.php" class="text-light text-decoration-none">Mes commandes</a></li>
                        <li><a href="<?php echo SITE_URL; ?>compte/deconnexion.php" class="text-light text-decoration-none">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>compte/connexion.php" class="text-light text-decoration-none">Connexion</a></li>
                        <li><a href="<?php echo SITE_URL; ?>compte/inscription.php" class="text-light text-decoration-none">Inscription</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>panier/" class="text-light text-decoration-none">Panier</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 mb-3">
				<h6>Informations légales</h6>
					<ul class="list-unstyled small">
						<li><a href="<?php echo SITE_URL; ?>cgv/">Conditions Générales de Vente</a></li>
						<li><a href="<?php echo SITE_URL; ?>mentions-legales.php">Mentions légales</a></li>
						<li><a href="<?php echo SITE_URL; ?>politique-confidentialite.php">Politique de confidentialité</a></li>
					</ul>
                <hr class="my-2">
                <p class="small mb-0">
                    <i class="fas fa-envelope"></i> contact@clair-obscur.com<br>
                </p>
            </div>
        </div>
        
        <hr class="my-3">
        <div class="text-center small">
            &copy; <?php echo date('Y'); ?> Clair-Obscur - Tous droits réservés
        </div>
    </div>
</footer>

<!-- Scripts -->
<script>var SITE_URL = '<?php echo SITE_URL; ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>

<!-- Script gestion cookies -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si les cookies sont déjà acceptés
    if (!localStorage.getItem('cookies_accepted') && !localStorage.getItem('cookies_declined')) {
        document.getElementById('cookie-banner').style.display = 'block';
    }
    
    // Accepter les cookies
    document.getElementById('accept-cookies')?.addEventListener('click', function() {
        localStorage.setItem('cookies_accepted', 'true');
        document.getElementById('cookie-banner').style.display = 'none';
        // Ici vous pouvez charger Google Analytics ou autres scripts
    });
    
    // Refuser les cookies
    document.getElementById('decline-cookies')?.addEventListener('click', function() {
        localStorage.setItem('cookies_declined', 'true');
        document.getElementById('cookie-banner').style.display = 'none';
    });

    // ========================
    // Bandeau newsletter
    // ========================
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }
    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    }

    if (!getCookie('nl_banner_closed')) {
        document.getElementById('newsletter-banner').style.display = 'block';
    }

    document.getElementById('nl-banner-close')?.addEventListener('click', function() {
        setCookie('nl_banner_closed', 'true', 30);
        document.getElementById('newsletter-banner').style.display = 'none';
    });

    document.getElementById('nl-banner-submit')?.addEventListener('click', function() {
        var email = document.getElementById('nl-banner-email').value.trim();
        var msgEl = document.getElementById('nl-banner-msg');

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            msgEl.style.display = 'block';
            msgEl.className = 'small mt-1 text-warning';
            msgEl.textContent = 'Veuillez entrer une adresse email valide.';
            return;
        }

        var formData = new FormData();
        formData.append('action', 'subscribe');
        formData.append('email', email);

        fetch(SITE_URL + 'ajax/newsletter.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            msgEl.style.display = 'block';
            if (data.success) {
                msgEl.className = 'small mt-1 text-success';
                msgEl.textContent = data.message;
                document.getElementById('nl-banner-email').value = '';
            } else {
                msgEl.className = 'small mt-1 text-warning';
                msgEl.textContent = data.message;
            }
        })
        .catch(function() {
            msgEl.style.display = 'block';
            msgEl.className = 'small mt-1 text-danger';
            msgEl.textContent = 'Erreur. Veuillez réessayer.';
        });
    });

    // ========================
    // Bouton "Me prévenir" sur fiche livre
    // ========================
    document.getElementById('notify-submit')?.addEventListener('click', function() {
        var email = document.getElementById('notify-email').value.trim();
        var livreId = this.getAttribute('data-livre-id');
        var msgEl = document.getElementById('notify-msg');

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            msgEl.style.display = 'block';
            msgEl.className = 'small mt-2 text-danger';
            msgEl.textContent = 'Veuillez entrer une adresse email valide.';
            return;
        }

        var formData = new FormData();
        formData.append('action', 'notify');
        formData.append('email', email);
        formData.append('livre_id', livreId);

        fetch(SITE_URL + 'ajax/newsletter.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            msgEl.style.display = 'block';
            if (data.success) {
                msgEl.className = 'small mt-2 text-success';
                msgEl.textContent = data.message;
            } else {
                msgEl.className = 'small mt-2 text-danger';
                msgEl.textContent = data.message;
            }
        })
        .catch(function() {
            msgEl.style.display = 'block';
            msgEl.className = 'small mt-2 text-danger';
            msgEl.textContent = 'Erreur. Veuillez réessayer.';
        });
    });

    // ========================
    // Checkbox newsletter sur validation de commande
    // ========================
    document.getElementById('newsletter-optin')?.addEventListener('change', function() {
        if (this.checked) {
            var formData = new FormData();
            formData.append('action', 'subscribe');
            formData.append('email', '<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>');

            fetch(SITE_URL + 'ajax/newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var msgEl = document.getElementById('nl-optin-msg');
                msgEl.style.display = 'block';
                if (data.success) {
                    msgEl.className = 'small mt-2 text-success';
                    msgEl.textContent = data.message;
                } else {
                    msgEl.className = 'small mt-2 text-warning';
                    msgEl.textContent = data.message;
                }
            });
        }
    });

   // ========================
    // Flèche retour en haut de page
    // ========================
    
    var backToTop = document.getElementById('back-to-top');
    
    if (backToTop) {
        // Afficher/masquer la flèche selon le défilement
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        // Scroller vers le haut au clic
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Mise à jour dynamique du compteur panier
function updateCartCount() {
    fetch('<?php echo SITE_URL; ?>panier/ajax-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cart-count');
            if (cartCount && data.count > 0) {
                cartCount.textContent = data.count;
                cartCount.style.display = 'inline-block';
            } else if (cartCount) {
                cartCount.style.display = 'none';
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Appeler la mise à jour si on est sur une page du site
if (window.location.hostname !== '') {
    updateCartCount();
}
</script>

<link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
<script type="module">
	import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

	createChat({
		webhookUrl: '<?php echo env('N8N_WEBHOOK_URL', ''); ?>',
	webhookConfig: {
		method: 'POST',
		headers: {}
	},
	target: '#n8n-chat',
	mode: 'window',
	chatInputKey: 'chatInput',
	chatSessionKey: 'sessionId',
	loadPreviousSession: true,
	metadata: {},
	showWelcomeScreen: false,
	defaultLanguage: 'fr',
	initialMessages: [
		'Bonjour et bienvenue chez Clair-Obscur Éditions. 🖤',
		'Je suis **Julia**, l\'assistante virtuelle de **Clair-Obscur Éditions**.\n\nJe peux notamment vous aider à :\n\n📚 Découvrir nos ouvrages\n\n✍️ Vous présenter nos auteurs\n\n💕 Vous conseiller une lecture selon vos envies\n\n📖 Vous faire découvrir un extrait choisi au hasard parmi nos livres\n\n❓ Répondre à vos questions concernant notre maison d\'édition\n\nQue souhaitez-vous découvrir aujourd\'hui ? 😊
    	],
	i18n: {
		fr: {
			title: 'Bonjour 🖤',
			subtitle: "Julia, agent IA Clair-obscur éditions pour vous aidez. 24/7.",
			footer: '',
			getStarted: 'Nouvelle conversation',
			inputPlaceholder: 'Posez votre question..',
		},
	},
	enableStreaming: false,
});

// =====================================================
	// OUVERTURE AUTOMATIQUE UNE SEULE FOIS
	// =====================================================
const CHAT_OPENED_KEY = 'julia_chat_auto_opened';

	if (!sessionStorage.getItem(CHAT_OPENED_KEY)) {

		let attempts = 0;
		const maxAttempts = 30;

		const openChat = () => {

			attempts++;

			// Recherche des boutons créés par n8n Chat
			const buttons = document.querySelectorAll('button');

			for (const button of buttons) {

				const ariaLabel = (
					button.getAttribute('aria-label') || ''
				).toLowerCase();

				const title = (
					button.getAttribute('title') || ''
				).toLowerCase();

				// Recherche du bouton d'ouverture du chat
				if (
					ariaLabel.includes('chat') ||
					ariaLabel.includes('open') ||
					title.includes('chat') ||
					title.includes('open')
				) {

					button.click();

					sessionStorage.setItem(
						CHAT_OPENED_KEY,
						'true'
					);

					console.log(
						'Julia : ouverture automatique du chat'
					);

					return;
				}
			}

			// Si le bouton n'est pas encore présent,
			// on réessaie 500 ms plus tard
			if (attempts < maxAttempts) {
				setTimeout(openChat, 500);
			} else {
				console.log(
					'Julia : bouton du chat non trouvé'
				);
			}
		};

		// Première tentative après 1 seconde
		setTimeout(openChat, 1000);
	}</script>


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo env('GA_TRACKING_ID', ''); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo env('GA_TRACKING_ID', ''); ?>');
</script>

</body>
</html>