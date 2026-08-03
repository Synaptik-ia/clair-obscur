<?php
// admin/seo.php - Gestion du référencement SEO global

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Vérifier que l'utilisateur est admin
redirigerSiNonAdmin();

$page_title = "Référencement SEO - Administration";

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Initialisation des paramètres par défaut
$default_settings = [
    'site_title' => 'Clair-Obscur - Maison d\'édition',
    'site_description' => 'Maison d\'édition indépendante de littérature pour adultes. Découvrez nos livres, romans et nouvelles.',
    'site_keywords' => 'livres, édition, littérature adulte, clair-obscur, romans',
    'og_image' => '',
    'twitter_card' => 'summary_large_image',
    'robots' => 'index, follow',
    'google_analytics' => '',
    'meta_author' => 'Clair-Obscur Éditions'
];

foreach ($default_settings as $key => $value) {
    $sql_check = "SELECT id FROM seo_settings WHERE setting_key = :key";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':key' => $key]);
    if (!$stmt_check->fetch()) {
        $sql_insert = "INSERT INTO seo_settings (setting_key, setting_value) VALUES (:key, :value)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':key' => $key, ':value' => $value]);
    }
}

// Récupération des paramètres actuels
$sql = "SELECT setting_key, setting_value FROM seo_settings";
$stmt = $conn->prepare($sql);
$stmt->execute();
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (array_key_exists($key, $default_settings)) {
            $sql_update = "UPDATE seo_settings SET setting_value = :value WHERE setting_key = :key";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':value' => cleanSQL(trim($value)),
                ':key' => $key
            ]);
        }
    }
    
    // Upload de l'image OG
    if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['og_image']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed)) {
            $message = "Format d'image non autorisé pour l'image OG.";
            $message_type = "danger";
        } else {
            $ext = pathinfo($_FILES['og_image']['name'], PATHINFO_EXTENSION);
            $filename = 'og_image_' . time() . '.' . $ext;
            $destination = '../assets/images/' . $filename;
            
            if (!is_dir('../assets/images/')) {
                mkdir('../assets/images/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['og_image']['tmp_name'], $destination)) {
                if (!empty($settings['og_image']) && file_exists('../assets/images/' . $settings['og_image'])) {
                    unlink('../assets/images/' . $settings['og_image']);
                }
                
                $sql_update = "UPDATE seo_settings SET setting_value = :value WHERE setting_key = 'og_image'";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->execute([':value' => $filename]);
                $settings['og_image'] = $filename;
                $message = "Image Open Graph mise à jour.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de l'upload de l'image.";
                $message_type = "danger";
            }
        }
    }
    
    if (empty($message)) {
        $message = "Paramètres SEO mis à jour avec succès.";
        $message_type = "success";
    }
    
    $stmt->execute();
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Génération du sitemap.xml
if (isset($_GET['generate_sitemap'])) {
    generateSitemap($conn);
    $message = "Sitemap.xml généré avec succès.";
    $message_type = "success";
}

// Fonction de génération du sitemap
function generateSitemap($conn) {
    $urls = [];
    
    $urls[] = ['loc' => SITE_URL, 'priority' => '1.0', 'changefreq' => 'weekly'];
    
    $sql = "SELECT id, date_parution FROM livres ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $urls[] = [
            'loc' => SITE_URL . 'livres/fiche.php?id=' . $row['id'],
            'priority' => '0.8',
            'changefreq' => 'monthly',
            'lastmod' => date('Y-m-d', strtotime($row['date_parution']))
        ];
    }
    
    $sql = "SELECT id, date_creation FROM auteurs ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $urls[] = [
            'loc' => SITE_URL . 'auteurs/fiche.php?id=' . $row['id'],
            'priority' => '0.6',
            'changefreq' => 'monthly'
        ];
    }
    
    $sql = "SELECT id, date_publication FROM nouvelles ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $urls[] = [
            'loc' => SITE_URL . 'nouvelles/article.php?id=' . $row['id'],
            'priority' => '0.7',
            'changefreq' => 'weekly',
            'lastmod' => date('Y-m-d', strtotime($row['date_publication']))
        ];
    }
    
    $static_pages = [
        ['loc' => SITE_URL . 'livres/liste.php', 'priority' => '0.9'],
        ['loc' => SITE_URL . 'nouvelles/', 'priority' => '0.7'],
        ['loc' => SITE_URL . 'contact/', 'priority' => '0.5'],
        ['loc' => SITE_URL . 'cgv/', 'priority' => '0.3']
    ];
    $urls = array_merge($urls, $static_pages);
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    foreach ($urls as $url) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
        if (isset($url['lastmod'])) {
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
        }
        $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    file_put_contents('../sitemap.xml', $xml);
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar admin -->
        <div class="col-md-3 col-lg-2 mb-4">
            <?php include 'menu.php'; ?>
        </div>
        
        <!-- Contenu -->
        <div class="col-md-9 col-lg-10">
            <h1 class="mb-4"><i class="fas fa-chart-line"></i> Référencement SEO</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-globe"></i> Paramètres généraux</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Titre du site (Meta Title)</label>
                                    <input type="text" name="site_title" class="form-control" value="<?php echo cleanXSS($settings['site_title']); ?>">
                                    <small class="text-muted">Apparaît dans l'onglet du navigateur et dans les résultats de recherche.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="site_description" rows="3" class="form-control"><?php echo cleanXSS($settings['site_description']); ?></textarea>
                                    <small class="text-muted">Description du site pour les moteurs de recherche.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="site_keywords" class="form-control" value="<?php echo cleanXSS($settings['site_keywords']); ?>">
                                    <small class="text-muted">Mots-clés séparés par des virgules.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Auteur (Meta Author)</label>
                                    <input type="text" name="meta_author" class="form-control" value="<?php echo cleanXSS($settings['meta_author']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Robots</label>
                                    <select name="robots" class="form-select">
                                        <option value="index, follow" <?php echo $settings['robots'] == 'index, follow' ? 'selected' : ''; ?>>index, follow (recommandé)</option>
                                        <option value="index, nofollow" <?php echo $settings['robots'] == 'index, nofollow' ? 'selected' : ''; ?>>index, nofollow</option>
                                        <option value="noindex, follow" <?php echo $settings['robots'] == 'noindex, follow' ? 'selected' : ''; ?>>noindex, follow</option>
                                        <option value="noindex, nofollow" <?php echo $settings['robots'] == 'noindex, nofollow' ? 'selected' : ''; ?>>noindex, nofollow</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fab fa-facebook"></i> Réseaux sociaux (Open Graph)</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Twitter Card</label>
                                    <select name="twitter_card" class="form-select">
                                        <option value="summary" <?php echo $settings['twitter_card'] == 'summary' ? 'selected' : ''; ?>>Summary</option>
                                        <option value="summary_large_image" <?php echo $settings['twitter_card'] == 'summary_large_image' ? 'selected' : ''; ?>>Summary with Large Image</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fab fa-google"></i> Google Analytics</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Code Google Analytics (GA4)</label>
                                    <textarea name="google_analytics" rows="4" class="form-control" placeholder="Coller ici votre code de suivi Google Analytics..."><?php echo cleanXSS($settings['google_analytics']); ?></textarea>
                                    <small class="text-muted">Le code sera inséré automatiquement dans le &lt;head&gt; du site.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-image"></i> Image Open Graph</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($settings['og_image'])): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo SITE_URL . 'assets/images/' . cleanXSS($settings['og_image']); ?>" class="img-fluid rounded" alt="OG Image">
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3 bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Changer l'image</label>
                                    <input type="file" name="og_image" class="form-control" accept="image/*">
                                    <small class="text-muted">Format recommandé : 1200x630 pixels</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-sitemap"></i> Sitemap</h5>
                            </div>
                            <div class="card-body text-center">
                                <p>Générez un fichier sitemap.xml pour aider les moteurs de recherche à indexer votre site.</p>
                                <a href="?generate_sitemap=1" class="btn btn-primary w-100">
                                    <i class="fas fa-download"></i> Générer le sitemap.xml
                                </a>
                                <?php if (file_exists('../sitemap.xml')): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo SITE_URL; ?>sitemap.xml" target="_blank" class="small">
                                            Voir le sitemap actuel <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-question-circle"></i> Aide SEO</h5>
                            </div>
                            <div class="card-body">
                                <ul class="small">
                                    <li>Chaque livre, auteur et nouvelle a ses propres meta tags.</li>
                                    <li>Le sitemap.xml aide Google à mieux indexer votre contenu.</li>
                                    <li>Pensez à soumettre votre sitemap dans Google Search Console.</li>
                                    <li>Les balises Open Graph améliorent le partage sur les réseaux sociaux.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer les paramètres SEO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>