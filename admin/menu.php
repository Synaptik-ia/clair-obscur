<?php
// admin/menu.php - Menu latéral de l'administration (centralisé)

// Ne pas inclure ce fichier directement
if (basename($_SERVER['PHP_SELF']) == 'menu.php') {
    die('Accès direct interdit');
}

// Récupérer les statistiques pour les badges
$stats_menu = [];
if (isset($conn)) {
    try {
        $sql = "SELECT COUNT(*) as total FROM commandes WHERE statut = 'en_attente'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stats_menu['commandes_attente'] = $stmt->fetch()['total'];
        
        $sql = "SELECT COUNT(*) as total FROM commentaires WHERE status = 'en_attente'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stats_menu['commentaires_attente'] = $stmt->fetch()['total'];
        
        $sql = "SELECT COUNT(*) as total FROM liseuse_livres";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stats_menu['liseuse'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM unanswered_questions WHERE status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stats_menu['questions_pending'] = $stmt->fetch()['total'];
    } catch (Exception $e) {
        $stats_menu = [];
    }
}

// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$full_path = $current_dir . '/' . $current_page;
?>

<div class="list-group shadow-sm">
    <a href="index.php" class="list-group-item list-group-item-action <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Tableau de bord
    </a>
    <a href="livres.php" class="list-group-item list-group-item-action <?php echo $current_page == 'livres.php' || $current_page == 'livre_form.php' ? 'active' : ''; ?>">
        <i class="fas fa-book"></i> Livres
    </a>
    <a href="auteurs.php" class="list-group-item list-group-item-action <?php echo $current_page == 'auteurs.php' || $current_page == 'auteur_form.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Auteurs
    </a>
    <a href="nouvelles.php" class="list-group-item list-group-item-action <?php echo $current_page == 'nouvelles.php' || $current_page == 'nouvelle_form.php' ? 'active' : ''; ?>">
        <i class="fas fa-newspaper"></i> Nouvelles
    </a>
    <a href="extraits.php" class="list-group-item list-group-item-action <?php echo $current_page == 'extraits.php' ? 'active' : ''; ?>">
        <i class="fas fa-quote-right"></i> Extraits
    </a>
    <a href="liseuse_config.php" class="list-group-item list-group-item-action <?php echo strpos($current_page, 'liseuse') !== false ? 'active' : ''; ?>">
        <i class="fas fa-book-open"></i> Liseuse
        <?php if (!empty($stats_menu['liseuse']) && $stats_menu['liseuse'] > 0): ?>
            <span class="badge bg-info float-end"><?php echo $stats_menu['liseuse']; ?></span>
        <?php endif; ?>
    </a>
    <a href="clients.php" class="list-group-item list-group-item-action <?php echo $current_page == 'clients.php' || $current_page == 'client_detail.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-friends"></i> Clients
    </a>
    <a href="commandes.php" class="list-group-item list-group-item-action <?php echo $current_page == 'commandes.php' ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart"></i> Commandes
        <?php if (!empty($stats_menu['commandes_attente']) && $stats_menu['commandes_attente'] > 0): ?>
            <span class="badge bg-warning float-end"><?php echo $stats_menu['commandes_attente']; ?></span>
        <?php endif; ?>
    </a>
    <a href="commentaires.php" class="list-group-item list-group-item-action <?php echo $current_page == 'commentaires.php' ? 'active' : ''; ?>">
        <i class="fas fa-comments"></i> Commentaires
        <?php if (!empty($stats_menu['commentaires_attente']) && $stats_menu['commentaires_attente'] > 0): ?>
            <span class="badge bg-danger float-end"><?php echo $stats_menu['commentaires_attente']; ?></span>
        <?php endif; ?>
    </a>
    <a href="newsletter.php" class="list-group-item list-group-item-action <?php echo $current_page == 'newsletter.php' ? 'active' : ''; ?>">
        <i class="fas fa-envelope-open-text"></i> Newsletter
    </a>
    <a href="questions.php" class="list-group-item list-group-item-action <?php echo $current_page == 'questions.php' ? 'active' : ''; ?>">
        <i class="fas fa-question-circle"></i> Questions
        <?php if (!empty($stats_menu['questions_pending']) && $stats_menu['questions_pending'] > 0): ?>
            <span class="badge bg-warning float-end"><?php echo $stats_menu['questions_pending']; ?></span>
        <?php endif; ?>
    </a>
    <a href="pages_site.php" class="list-group-item list-group-item-action <?php echo $current_page == 'pages_site.php' ? 'active' : ''; ?>">
        <i class="fas fa-sitemap"></i> Pages du site
    </a>
    <a href="seo.php" class="list-group-item list-group-item-action <?php echo $current_page == 'seo.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Référencement SEO
    </a>
    <hr class="my-2">
    <a href="<?php echo SITE_URL; ?>" target="_blank" class="list-group-item list-group-item-action text-primary">
        <i class="fas fa-globe"></i> Voir le site
    </a>
    <a href="../compte/deconnexion.php" class="list-group-item list-group-item-action text-danger">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</div>