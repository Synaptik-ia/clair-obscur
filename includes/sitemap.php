<?php
// includes/sitemap.php - Génération du sitemap.xml

if (basename($_SERVER['PHP_SELF']) == 'sitemap.php') {
    die('Accès direct interdit');
}

function generateSitemap($conn) {
    $urls = [];

    // Page d'accueil
    $urls[] = ['loc' => SITE_URL, 'priority' => '1.0', 'changefreq' => 'weekly'];

    // Livres actifs uniquement
    $sql = "SELECT id, date_parution FROM livres WHERE statut_vente != 'non_vendable' ORDER BY id";
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

    // Tous les auteurs
    $sql = "SELECT id FROM auteurs ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $urls[] = [
            'loc' => SITE_URL . 'auteurs/fiche.php?id=' . $row['id'],
            'priority' => '0.6',
            'changefreq' => 'monthly'
        ];
    }

    // Toutes les nouvelles
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

    // Pages statiques
    $static_pages = [
        ['loc' => SITE_URL . 'auteurs/', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => SITE_URL . 'livres/liste.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => SITE_URL . 'nouvelles/', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => SITE_URL . 'contact/', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => SITE_URL . 'cgv/', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];
    $urls = array_merge($urls, $static_pages);

    // Génération XML
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

    $sitemap_path = dirname(__DIR__) . '/sitemap.xml';
    file_put_contents($sitemap_path, $xml);
}
