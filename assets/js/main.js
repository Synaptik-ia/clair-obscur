// assets/js/main.js - Scripts JavaScript pour Clair-Obscur

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================
    // 1. Gestion du panier (AJAX)
    // ========================
    
    // Mise à jour du compteur du panier
    function updateCartCount() {
        fetch(SITE_URL + 'panier/ajax-count.php')
            .then(response => response.json())
            .then(data => {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    if (data.count > 0) {
                        cartCount.textContent = data.count;
                        cartCount.style.display = 'inline-block';
                    } else {
                        cartCount.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
    
    // Ajouter au panier sans rechargement (si boutons AJAX)
    const ajaxCartButtons = document.querySelectorAll('.ajax-add-to-cart');
    ajaxCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const livreId = this.dataset.id;
            const quantite = this.dataset.quantite || 1;
            
            fetch(SITE_URL + 'panier/ajouter_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'livre_id=' + livreId + '&quantite=' + quantite
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Article ajouté au panier !', 'success');
                    updateCartCount();
                } else {
                    showNotification('Erreur : ' + data.message, 'error');
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });
    
    // ========================
    // 2. Notifications toast
    // ========================
    
    function showNotification(message, type = 'info') {
        // Créer l'élément toast
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i>
                <span>${message}</span>
            </div>
            <div class="toast-progress"></div>
        `;
        
        // Styles du toast
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#27ae60' : (type === 'error' ? '#e74c3c' : '#3498db')};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 14px;
            min-width: 250px;
        `;
        
        document.body.appendChild(toast);
        
        // Auto-suppression après 3 secondes
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Styles d'animation pour les toasts
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .toast-notification {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast-content i {
            font-size: 18px;
        }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255,255,255,0.5);
            width: 100%;
            animation: progress 3s linear;
        }
        @keyframes progress {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }
    `;
    document.head.appendChild(style);
    
    // ========================
    // 3. Validation des formulaires
    // ========================
    
    // Validation du formulaire d'inscription
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirm');
            
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                showNotification('Les mots de passe ne correspondent pas', 'error');
                confirm.style.borderColor = '#e74c3c';
            }
        });
    }
    
    // Validation du formulaire de contact
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
            
            if (email && !emailRegex.test(email.value)) {
                e.preventDefault();
                showNotification('Veuillez entrer un email valide', 'error');
                email.style.borderColor = '#e74c3c';
            }
        });
    }
    
    // ========================
    // 4. Système de likes (AJAX)
    // ========================
    
    const likeButtons = document.querySelectorAll('.like-button');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const livreId = this.dataset.id;
            const likeCountSpan = document.querySelector(`.like-count-${livreId}`);
            
            fetch(SITE_URL + 'ajax/like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'livre_id=' + livreId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (likeCountSpan) {
                        likeCountSpan.textContent = data.likes;
                    }
                    // Changer l'apparence du bouton
                    if (data.liked) {
                        this.classList.add('liked');
                        this.innerHTML = '<i class="fas fa-heart"></i> Je n\'aime plus';
                    } else {
                        this.classList.remove('liked');
                        this.innerHTML = '<i class="fas fa-heart"></i> J\'aime';
                    }
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });
    
    // ========================
    // 5. Filtres et tri dynamiques
    // ========================
    
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const filters = filterForm.querySelectorAll('select, input');
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
    
    // ========================
    // 6. Confirmation de suppression
    // ========================
    
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // ========================
    // 7. Gestion du mode sombre
    // ========================
    
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Vérifier le thème sauvegardé
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('theme', 'light');
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
    }
    
    // ========================
    // 8. Back to top button
    // ========================
    
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.display = 'flex';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    // ========================
    // 9. Aperçu avant téléchargement
    // ========================
    
    const downloadLinks = document.querySelectorAll('.download-preview');
    downloadLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            // Ouvrir dans un nouvel onglet ou afficher une modale
            window.open(url, '_blank');
        });
    });
    
    // ========================
    // 10. Masquer les alertes automatiquement
    // ========================
    
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // ========================
    // 11. Gestion des cookies
    // ========================
    
    const cookieBanner = document.getElementById('cookie-banner');
    if (cookieBanner && !localStorage.getItem('cookies_accepted') && !localStorage.getItem('cookies_declined')) {
        cookieBanner.style.display = 'block';
    }
    
    const acceptCookies = document.getElementById('accept-cookies');
    if (acceptCookies) {
        acceptCookies.addEventListener('click', function() {
            localStorage.setItem('cookies_accepted', 'true');
            cookieBanner.style.display = 'none';
            // Charger Google Analytics si présent
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
            }
        });
    }
    
    const declineCookies = document.getElementById('decline-cookies');
    if (declineCookies) {
        declineCookies.addEventListener('click', function() {
            localStorage.setItem('cookies_declined', 'true');
            cookieBanner.style.display = 'none';
        });
    }
    
    // ========================
    // 12. Scroll animations
    // ========================
    
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkScroll() {
        animateElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    }
    
    window.addEventListener('scroll', checkScroll);
    checkScroll();
    
    // ========================
    // 13. Compteur de caractères pour textarea
    // ========================
    
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0 / ${maxLength}`;
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = `${this.value.length} / ${maxLength}`;
            if (remaining < 0) {
                counter.style.color = 'red';
            } else {
                counter.style.color = '';
            }
        });
    });
    
    // ========================
    // 14. Validation des notes (étoiles)
    // ========================
    
    const ratingInputs = document.querySelectorAll('.rating-input');
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const value = this.value;
            const stars = this.closest('.rating').querySelectorAll('.star');
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        });
    });
    
    // ========================
    // 15. Gestion des frais de port dynamiques
    // ========================
    
    const countrySelect = document.getElementById('pays');
    const fraisPortSpan = document.getElementById('frais-port');
    
    if (countrySelect && fraisPortSpan) {
        countrySelect.addEventListener('change', function() {
            const pays = this.value;
            fetch(SITE_URL + 'ajax/frais_port.php?pays=' + encodeURIComponent(pays))
                .then(response => response.json())
                .then(data => {
                    fraisPortSpan.textContent = data.frais_port + ' €';
                })
                .catch(error => console.error('Erreur:', error));
        });
    }
    
    // ========================
    // 16. Initialisation des tooltips Bootstrap
    // ========================
    
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    console.log('Clair-Obscur - Site initialisé');
});