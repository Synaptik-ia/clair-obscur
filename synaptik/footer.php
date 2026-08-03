<script>
// Effet de brillance supplémentaire sur les éléments
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter un effet de lueur au survol des liens du footer
    const footerLinks = document.querySelectorAll('.footer-col a');
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function(e) {
            const glow = document.createElement('div');
            glow.style.position = 'absolute';
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
            glow.style.width = '100px';
            glow.style.height = '100px';
            glow.style.background = 'radial-gradient(circle, rgba(167,139,250,0.3) 0%, transparent 70%)';
            glow.style.pointerEvents = 'none';
            glow.style.transform = 'translate(-50%, -50%)';
            glow.style.zIndex = '9999';
            document.body.appendChild(glow);
            
            setTimeout(() => {
                glow.remove();
            }, 500);
        });
    });
});
</script>