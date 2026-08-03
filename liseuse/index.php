<?php
// liseuse/index.php - Lecteur de livre avec images pleine page

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$db = new Database();
$conn = $db->getConnection();

// Récupérer le slug depuis l'URL
$slug = isset($_GET['slug']) ? cleanSQL($_GET['slug']) : '';
$livre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si aucun slug n'est fourni, essayer de récupérer depuis l'URL
if (empty($slug) && $livre_id == 0) {
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    
    if (isset($segments[0]) && $segments[0] == 'liseuse' && isset($segments[1])) {
        $slug = cleanSQL($segments[1]);
    }
}

// Récupérer les infos du livre
if ($livre_id > 0) {
    $sql = "SELECT * FROM liseuse_livres WHERE id = :id AND statut = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $livre_id]);
} elseif (!empty($slug)) {
    $sql = "SELECT * FROM liseuse_livres WHERE slug = :slug AND statut = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':slug' => $slug]);
} else {
    header('Location: ' . SITE_URL);
    exit();
}

$livre = $stmt->fetch();

if (!$livre) {
    header('Location: ' . SITE_URL);
    exit();
}

// Récupérer les pages du livre
$sql_pages = "SELECT * FROM liseuse_pages WHERE livre_id = :livre_id ORDER BY page_num ASC";
$stmt_pages = $conn->prepare($sql_pages);
$stmt_pages->execute([':livre_id' => $livre['id']]);
$pages = $stmt_pages->fetchAll();

$page_title = $livre['titre'] . ' - Liseuse Clair-Obscur';
$page_description = $livre['description'] ?: 'Lisez ' . $livre['titre'] . ' sur Clair-Obscur';

include '../includes/header.php';
?>

<style>
    /* Container principal */
    .liseuse-container {
        background: linear-gradient(135deg, #2a1a0a 0%, #1a0f05 100%);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }
    
    .liseuse-container.active {
        display: flex;
    }
    
    /* Zone du livre - centrée */
    .book-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 0;
        position: relative;
        width: 100%;
        overflow: visible;
    }
    
    /* Conteneur du flipbook */
    .flipbook-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
    }
    
    #flipbook {
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    /* Styles des pages - image pleine page */
    #flipbook .page {
        background-color: #1a0f05;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden;
        position: relative;
        border-radius: 0;
        box-shadow: none;
    }
    
    /* Image pleine page */
    .page-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        background-color: #1a0f05;
    }
    
    /* Pour les pages sans image (fallback) */
    .page-content {
        padding: 40px 45px;
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 12px;
        line-height: 1.4;
        color: #2c1810;
        background-color: #fdf8f0;
        height: 100%;
        overflow-y: auto;
        box-sizing: border-box;
    }
    
    .page-content h1 {
        font-size: 20px;
        text-align: center;
        margin-bottom: 25px;
        color: #2c1810;
        font-family: 'Cormorant Garamond', serif;
    }
    
    .page-content p {
        margin-bottom: 12px;
        text-align: justify;
        font-size: 12px;
        line-height: 1.4;
    }
    
    /* Première page - couverture (image pleine page) */
    .cover-page {
        background: linear-gradient(135deg, #1a0f05 0%, #2a1a0a 100%) !important;
        padding: 0 !important;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .cover-page .cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
    }
    
    .cover-page h1 {
        position: relative;
        z-index: 2;
        color: #d4c5a9 !important;
        font-size: 32px !important;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        background: rgba(0,0,0,0.4);
        padding: 15px 30px;
        border-radius: 10px;
        text-align: center;
        max-width: 80%;
    }
    
    /* Dernière page */
    .back-page {
        background: linear-gradient(135deg, #1a0f05 0%, #2a1a0a 100%) !important;
        padding: 0 !important;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .back-page .cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
    }
    
    .back-page p {
        position: relative;
        z-index: 2;
        background: rgba(0,0,0,0.4);
        padding: 15px 30px;
        border-radius: 10px;
        color: #d4c5a9;
    }
    
    /* Numéros de page */
    .page-number {
        position: absolute;
        bottom: 15px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: rgba(255,255,255,0.6);
        font-family: 'Georgia', serif;
        z-index: 2;
        pointer-events: none;
    }
    
    /* Barre de contrôles */
    .book-controls {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(26, 15, 5, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 50px;
        padding: 8px 20px;
        display: flex;
        gap: 8px;
        z-index: 1001;
        border: 1px solid #d4c5a9;
    }
    
    .book-controls button {
        background: transparent;
        border: none;
        color: #d4c5a9;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
        padding: 8px 12px;
        border-radius: 40px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .book-controls button:hover {
        background-color: #d4c5a9;
        color: #2c1810;
    }
    
    .book-controls .page-info {
        background: rgba(212, 197, 169, 0.2);
        padding: 8px 15px;
        border-radius: 40px;
        font-size: 13px;
        margin: 0 5px;
    }
    
    /* Bouton plein écran */
    .fullscreen-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(26, 15, 5, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid #d4c5a9;
        border-radius: 40px;
        padding: 10px 18px;
        color: #d4c5a9;
        cursor: pointer;
        z-index: 1002;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .fullscreen-btn:hover {
        background-color: #d4c5a9;
        color: #2c1810;
    }
    
    /* Bouton fermeture */
    .close-liseuse {
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(26, 15, 5, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid #d4c5a9;
        border-radius: 40px;
        padding: 10px 18px;
        color: #d4c5a9;
        cursor: pointer;
        z-index: 1002;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .close-liseuse:hover {
        background-color: #d4c5a9;
        color: #2c1810;
    }
    
    /* Mode plein écran */
    .liseuse-container:-webkit-full-screen {
        background: linear-gradient(135deg, #2a1a0a 0%, #1a0f05 100%);
    }
    
    .liseuse-container:fullscreen {
        background: linear-gradient(135deg, #2a1a0a 0%, #1a0f05 100%);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .book-controls button span {
            display: none;
        }
        
        .book-controls button {
            padding: 8px 12px;
        }
        
        .fullscreen-btn span,
        .close-liseuse span {
            display: none;
        }
        
        .fullscreen-btn,
        .close-liseuse {
            padding: 10px 14px;
        }
    }
</style>

<!-- Overlay de la liseuse -->
<div id="liseuseOverlay" class="liseuse-container">
    <div class="close-liseuse" id="closeLiseuse">
        <i class="fas fa-times"></i> <span>Fermer</span>
    </div>
    
    <div class="fullscreen-btn" id="fullscreenBtn">
        <i class="fas fa-expand"></i> <span>Plein écran</span>
    </div>
    
    <div class="book-wrapper">
        <div class="flipbook-container">
            <div id="flipbook">
                <!-- Première page (couverture) - image pleine page -->
                <div class="page cover-page">
                    <?php if ($livre['image_couverture']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['image_couverture']); ?>" class="cover-image" alt="Couverture">
                    <?php endif; ?>
                    <h1><?php echo cleanXSS($livre['titre']); ?></h1>
                    <?php if ($livre['description']): ?>
                        <div class="subtitle"><?php echo cleanXSS($livre['description']); ?></div>
                    <?php endif; ?>
                    <div class="page-number">Page 1</div>
                </div>
                
                <!-- Pages de contenu - image pleine page -->
                <?php foreach ($pages as $index => $page): ?>
                <div class="page">
                    <?php if (!empty($page['image_page'])): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/liseuse/' . cleanXSS($page['image_page']); ?>" class="page-image" alt="<?php echo cleanXSS($page['titre']); ?>">
                    <?php else: ?>
                        <!-- Fallback au texte si pas d'image -->
                        <div class="page-content">
                            <?php if ($page['titre']): ?>
                                <h1><?php echo cleanXSS($page['titre']); ?></h1>
                            <?php endif; ?>
                            <?php echo html_entity_decode($page['contenu'] ?? ''); ?>
                        </div>
                    <?php endif; ?>
                    <div class="page-number">Page <?php echo $index + 2; ?></div>
                </div>
                <?php endforeach; ?>
                
                <!-- Dernière page (4ème de couverture) - image pleine page -->
                <div class="page back-page">
                    <?php if ($livre['image_4eme']): ?>
                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($livre['image_4eme']); ?>" class="cover-image" alt="Quatrième de couverture">
                    <?php endif; ?>
                    <p><i class="fas fa-book-open"></i><br>Clair-Obscur Éditions</p>
                    <div class="page-number">Dernière page</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="book-controls">
        <button id="prev-page"><i class="fas fa-chevron-left"></i> <span>Précédent</span></button>
        <span class="page-info" id="pageInfo">Page 1 / 1</span>
        <button id="next-page"><span>Suivant</span> <i class="fas fa-chevron-right"></i></button>
        <button id="go-to-start"><i class="fas fa-fast-backward"></i></button>
        <button id="go-to-end"><i class="fas fa-fast-forward"></i></button>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?php echo SITE_URL; ?>assets/turnjs/lib/turn.min.js"></script>

<script>
$(document).ready(function() {
    var flipbook = $('#flipbook');
    var totalPages = flipbook.find('.page').length;
    var container = $('#liseuseOverlay');
    var currentPage = 1;
    
    // Afficher la liseuse automatiquement
    container.addClass('active');
    document.body.style.overflow = 'hidden';
    
    // Fonction pour centrer le flipbook
    function centerFlipbook() {
        var containerWidth = $('.flipbook-container').width();
        var flipbookWidth = flipbook.width();
        var leftMargin = (containerWidth - flipbookWidth) / 2;
        
        flipbook.css({
            'margin-left': leftMargin + 'px',
            'margin-right': 'auto'
        });
    }
    
    // Calculer la taille optimale du livre
    function resizeBook() {
        var wrapper = $('.flipbook-container');
        var wrapperWidth = wrapper.width();
        var wrapperHeight = wrapper.height();
        
        var maxHeight = wrapperHeight - 40;
        var maxWidth = Math.min(wrapperWidth - 40, 1400);
        
        var pageHeight = maxHeight;
        var pageWidth = pageHeight * 0.7;
        var bookWidth = pageWidth * 2;
        
        if (bookWidth > maxWidth) {
            bookWidth = maxWidth;
            pageWidth = bookWidth / 2;
            pageHeight = pageWidth / 0.7;
        }
        
        flipbook.turn('size', bookWidth, pageHeight);
        
        setTimeout(function() {
            centerFlipbook();
        }, 50);
    }
    
    // Mettre à jour l'affichage de la page courante
    function updatePageInfo(pageNum) {
        currentPage = pageNum;
        $('#pageInfo').text('Page ' + pageNum + ' sur ' + totalPages);
    }
    
    // Initialisation de Turn.js
    flipbook.turn({
        width: 800,
        height: 550,
        elevation: 30,
        gradients: true,
        autoCenter: false,
        duration: 500,
        pages: totalPages,
        when: {
            turned: function(e, page) {
                updatePageInfo(page);
                setTimeout(function() {
                    centerFlipbook();
                }, 100);
            },
            start: function(e, page) {
                return true;
            }
        }
    });
    
    // Attendre que Turn.js soit prêt
    setTimeout(function() {
        resizeBook();
        flipbook.turn('page', 1);
        updatePageInfo(1);
        centerFlipbook();
    }, 200);
    
    $(window).resize(function() {
        resizeBook();
        setTimeout(function() {
            centerFlipbook();
        }, 100);
    });
    
    // Contrôles de navigation
    $('#prev-page').click(function() {
        flipbook.turn('previous');
        return false;
    });
    
    $('#next-page').click(function() {
        flipbook.turn('next');
        return false;
    });
    
    $('#go-to-start').click(function() {
        flipbook.turn('page', 1);
        return false;
    });
    
    $('#go-to-end').click(function() {
        flipbook.turn('page', totalPages);
        return false;
    });
    
    // Navigation au clavier
    $(document).keydown(function(e) {
        if (!container.hasClass('active')) return;
        
        if (e.key === 'ArrowLeft') {
            flipbook.turn('previous');
            e.preventDefault();
        } else if (e.key === 'ArrowRight') {
            flipbook.turn('next');
            e.preventDefault();
        } else if (e.key === 'Home') {
            flipbook.turn('page', 1);
            e.preventDefault();
        } else if (e.key === 'End') {
            flipbook.turn('page', totalPages);
            e.preventDefault();
        } else if (e.key === 'Escape') {
            closeLiseuse();
        }
    });
    
    // Fermeture de la liseuse
    function closeLiseuse() {
        container.removeClass('active');
        document.body.style.overflow = '';
        window.location.href = '<?php echo SITE_URL; ?>';
    }
    
    $('#closeLiseuse').click(closeLiseuse);
    
    // Plein écran
    function toggleFullscreen() {
        var elem = document.getElementById('liseuseOverlay');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => {
                console.log(err);
            });
            $('#fullscreenBtn').html('<i class="fas fa-compress"></i> <span>Quitter</span>');
        } else {
            document.exitFullscreen();
            $('#fullscreenBtn').html('<i class="fas fa-expand"></i> <span>Plein écran</span>');
        }
        setTimeout(function() {
            resizeBook();
            centerFlipbook();
        }, 300);
    }
    
    $('#fullscreenBtn').click(toggleFullscreen);
    
    document.addEventListener('fullscreenchange', function() {
        if (document.fullscreenElement) {
            $('#fullscreenBtn').html('<i class="fas fa-compress"></i> <span>Quitter</span>');
        } else {
            $('#fullscreenBtn').html('<i class="fas fa-expand"></i> <span>Plein écran</span>');
        }
        setTimeout(function() {
            resizeBook();
            centerFlipbook();
        }, 300);
    });
});
</script>

<?php include '../includes/footer.php'; ?>