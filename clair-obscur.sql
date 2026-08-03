-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 03 août 2026 à 09:15
-- Version du serveur : 5.5.68-MariaDB
-- Version de PHP : 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `clair-obscur`
--

-- --------------------------------------------------------

--
-- Structure de la table `auteurs`
--

CREATE TABLE `auteurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `biographie` text,
  `photo` varchar(255) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text,
  `date_creation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `auteurs`
--

INSERT INTO `auteurs` (`id`, `nom`, `biographie`, `photo`, `seo_title`, `seo_description`, `date_creation`) VALUES
(2, 'Edouard De Saintes', 'Auteur érotomancien sensualiste, il explore depuis le début des années 2000 les multiples facettes du désir, de la séduction et des imaginaires libertins.\r\n\r\nLibertin assumé, ancien acteur amateur dans l’univers adulte, créateur de plateformes de rencontres coquines et webmaster spécialisé dans les contenus pour adultes, son parcours atypique s’est construit au croisement de l’expérience vécue, de la curiosité humaine et de la culture du fantasme. Peu d’aspects de la sensualité, des relations passionnelles ou des jeux de séduction lui sont étrangers.\r\n\r\nAu fil des années, ses échanges intimes, ses conversations complices avec ses amantes et ses récits de rencontres intenses ont donné naissance à un univers narratif singulier, nourri de tension émotionnelle, de sensualité assumée et d’observations sans filtre sur les dynamiques du désir.\r\n\r\nDe scénarios coquins improvisés en confidences nocturnes, l’écriture s’est imposée comme une évidence : compiler ces histoires, ces fantasmes, ces fragments de passion et ces expériences romancées afin de les partager avec un public adulte en quête d’émotions fortes, d’érotisme élégant et de récits profondément incarnés.', 'author_6a0d20e15791d_1779245281.png', 'Présentation de Edouard De Saintes auteur érotomancien sensualiste', 'Auteur érotomancien sensualiste, il explore depuis le début des années 2000 les multiples facettes du désir, de la séduction et des imaginaires libertins.\r\n\r\nLibertin assumé, ancien acteur amateur dans l’univers adulte, créateur de plateformes de rencontres coquines et webmaster spécialisé dans les contenus pour adultes, son parcours atypique s’est construit au croisement de l’expérience vécue, de la curiosité humaine et de la culture du fantasme. Peu d’aspects de la sensualité, des relations passionnelles ou des jeux de séduction lui sont étrangers.', '2026-05-19 22:00:53');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `date_commande` datetime DEFAULT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `type_commande` enum('ebook','physique','physique_dedicace') NOT NULL,
  `frais_port` decimal(10,2) DEFAULT '0.00',
  `statut` enum('en_attente','paye','livre','annule') DEFAULT 'en_attente',
  `lien_telechargement_unique` varchar(255) DEFAULT NULL,
  `lien_expire_le` datetime DEFAULT NULL,
  `adresse_livraison` text,
  `pays_livraison` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `note` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `status` enum('en_attente','valide','supprime') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `details_commandes`
--

CREATE TABLE `details_commandes` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT '1',
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_like` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `liseuse_livres`
--

CREATE TABLE `liseuse_livres` (
  `id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image_couverture` varchar(255) DEFAULT NULL,
  `image_4eme` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_modification` datetime DEFAULT NULL,
  `statut` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `liseuse_livres`
--

INSERT INTO `liseuse_livres` (`id`, `titre`, `slug`, `description`, `image_couverture`, `image_4eme`, `date_creation`, `date_modification`, `statut`) VALUES
(1, 'Bienvenue chez French-Bukkake - Chapitre 3', 'bienvenue-chez-french-bukkake', '', 'cover_1_1781060935.jpg', 'back_1_1781060935.jpg', '2026-06-09 23:08:55', '2026-06-10 19:56:17', 1);

-- --------------------------------------------------------

--
-- Structure de la table `liseuse_pages`
--

CREATE TABLE `liseuse_pages` (
  `id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `page_num` int(11) NOT NULL,
  `titre` varchar(200) DEFAULT NULL,
  `contenu` longtext NOT NULL,
  `image_page` varchar(255) DEFAULT NULL,
  `date_modification` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `liseuse_pages`
--

INSERT INTO `liseuse_pages` (`id`, `livre_id`, `page_num`, `titre`, `contenu`, `image_page`, `date_modification`) VALUES
(10, 1, 2, '', '', 'page_1_2_1781142974.jpg', NULL),
(11, 1, 3, '', '', 'page_1_3_1781142985.jpg', NULL),
(12, 1, 4, '', '', 'page_1_4_1781142998.jpg', NULL),
(13, 1, 5, '', '', 'page_1_5_1781143008.jpg', NULL),
(14, 1, 6, '', '', 'page_1_6_1781143018.jpg', NULL),
(15, 1, 7, '', '', 'page_1_7_1781143028.jpg', NULL),
(16, 1, 8, '', '', 'page_1_8_1781143038.jpg', NULL),
(17, 1, 1, '', '', 'page_1_1_1781143432.jpg', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `livres`
--

CREATE TABLE `livres` (
  `id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `sous_titre` varchar(200) DEFAULT NULL,
  `auteur_id` int(11) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `description` text,
  `prix_ebook` decimal(10,2) NOT NULL,
  `prix_physique` decimal(10,2) DEFAULT NULL,
  `fichier_pdf` varchar(255) DEFAULT NULL,
  `couverture` varchar(255) DEFAULT NULL,
  `date_parution` date DEFAULT NULL,
  `stock_physique` int(11) DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text,
  `date_creation` datetime DEFAULT NULL,
  `statut_vente` enum('non_vendable','precommande','en_vente') DEFAULT 'en_vente',
  `date_precommande` date DEFAULT NULL,
  `date_sortie` date DEFAULT NULL,
  `prix_precommande` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `nouvelles`
--

CREATE TABLE `nouvelles` (
  `id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_publication` datetime DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `nouvelles`
--

INSERT INTO `nouvelles` (`id`, `titre`, `contenu`, `image`, `date_publication`, `seo_title`, `seo_description`) VALUES
(1, 'Découvrez la couverture de notre premier livre - Jeux de chair', '&lt;p&gt;Un regard. Une idée. Une tentation. Découvrez la couverture de Jeux de chair, notre premier recueil d’histoires et scénarios coquins, par Edouard De Saintes. Un livre conçu comme un terrain d’exploration pour l’imagination : récits sensuels, situations troublantes, jeux de séduction, envies secrètes et scénarios capables d’éveiller la curiosité autant que l’inspiration. Ni simple lecture, ni simple collection d’idées. Jeux de chair est une invitation à voyager dans les territoires du fantasme, du jeu et du récit. La couverture n’est qu’un premier indice. Le reste… se découvre bientôt. Restez à l’écoute : extraits, révélations et lancement approchent.&lt;/p&gt;', 'news_6a61497a18e73_1784760698.png', '2026-05-20 01:24:55', 'Découvrez la couverture de Jeux de chair, notre premier recueil d’histoires et scénarios coquins.', 'Découvrez la couverture de Jeux de chair, notre premier recueil d’histoires et scénarios coquins. Un livre conçu comme un terrain d’exploration pour l’imagination : récits sensuels, situations troublantes, jeux de séduction, envies secrètes et scénarios capables d’éveiller la curiosité autant que l’inspiration.'),
(2, 'Suivez nous sur nos réseaux sociaux', '&lt;p&gt;Ne perdez pas le contact et recevez nos actualités et nouveaux via nos réseaux sociaux :&amp;nbsp;&lt;/p&gt;&lt;p&gt;&lt;a target=&quot;_blank&quot; rel=&quot;noopener noreferrer&quot; href=&quot;https://www.facebook.com/profile.php?id=61589894467968&quot;&gt;Facebook&amp;nbsp;&lt;/a&gt;&lt;/p&gt;&lt;p&gt;&lt;a target=&quot;_blank&quot; rel=&quot;noopener noreferrer&quot; href=&quot;https://x.com/clairobeditions&quot;&gt;X.com&lt;/a&gt;&lt;/p&gt;&lt;p&gt;&lt;a target=&quot;_blank&quot; rel=&quot;noopener noreferrer&quot; href=&quot;https://www.reddit.com/user/ClairObscurEditions/&quot;&gt;Reddit&lt;/a&gt;&lt;/p&gt;', 'news_6a14f0b5339d2_1779757237.png', '2026-05-23 22:00:30', 'Suivez nous sur nos réseaux sociaux', 'Ne perdez pas le contact et recevez nos actualités et nouveaux via nos réseaux sociaux facebook et instagram'),
(3, 'Premier extrait dévoilé — Jeux de chair', '&lt;p&gt;L’attente est récompensée.&lt;/p&gt;&lt;p&gt;Découvrez un premier aperçu de &lt;i&gt;&lt;strong&gt;Jeux de chair&lt;/strong&gt;&lt;/i&gt;, notre recueil d’histoires et scénarios coquins, entre tension, séduction, jeux d’influence et imaginaire sensuel.&lt;/p&gt;&lt;p&gt;Cette semaine, entrez dans l’univers de &lt;strong&gt;« La Maîtresse du plaisir »&lt;/strong&gt;.&lt;/p&gt;&lt;blockquote&gt;&lt;p&gt;&lt;i&gt;« …&lt;/i&gt;Avec douceur, elle défait les menottes qui entravaient mes poignets. Repassés debout au milieu du salon, désormais entièrement nus tous les deux, nous nous enlaçons pendant plusieurs minutes. Nos mains s’égarent sur nos corps tandis que nos lèvres et nos langues se mêlent, se démêlent et s’entremêlent avec passion.&lt;/p&gt;&lt;p&gt;Ce moment, suspendu dans le temps, prend fin quand elle me prend par le bras pour m’entraîner vers la chambre. Je me retrouve face au lit, la regardant s’adosser contre la tête de lit à l’autre extrémité. Ses jambes ouvertes sans pudeur me font découvrir le spectacle cru de son intimité, promesse muette de nouveaux plaisirs à venir. Son regard insatiable et gourmand m’indique que le jeu est loin d’être fini.&lt;/p&gt;&lt;p&gt;Je saisis délicatement l’un de ses pieds pour le masser doucement, avant de le porter à mes lèvres et de l’embrasser. Remontant le long de cette jambe gainée de nylon, ma langue glisse dessus en laissant une traînée légèrement humide.&lt;i&gt;… »&lt;/i&gt;&lt;/p&gt;&lt;/blockquote&gt;&lt;p&gt;Un instant suspendu.&lt;br&gt;Une invitation silencieuse.&lt;br&gt;Et la promesse que le jeu est loin d’être terminé…&lt;/p&gt;&lt;p&gt;Curieux de découvrir la suite ?&lt;/p&gt;&lt;p&gt;&lt;i&gt;Jeux de chair&lt;/i&gt; arrive chez &lt;strong&gt;Clair-Obscur Éditions&lt;/strong&gt;.&lt;/p&gt;', 'news_6a61495b8522c_1784760667.jpg', '2026-05-31 00:27:22', 'Premier extrait dévoilé — Jeux de chair', 'Découvrez un premier aperçu de Jeux de chair, notre recueil d’histoires et scénarios coquins, entre tension, séduction, jeux d’influence et imaginaire sensuel.'),
(8, 'Planing de sorties 2026-2027', '&lt;p&gt;L&apos;année 2026 marquera les premiers pas de Claire-Obscure Éditions dans l&apos;univers de la littérature sensuelle et des récits de passion.&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Automne 2026&lt;/strong&gt; : parution de &lt;i&gt;Jeux de Chairs&lt;/i&gt;, un premier recueil d&apos;histoires et de scénarios coquins explorant les multiples visages du désir, de la séduction et des fantasmes contemporains.&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Fin 2026 / Début 2027&lt;/strong&gt; : découvrez l&apos;autobiographie sans concession d&apos;une personnalité sulfureuse et controversée issue de l&apos;industrie pour adultes, un témoignage intime sur un parcours hors normes.&lt;/p&gt;&lt;p&gt;&lt;strong&gt;2027&lt;/strong&gt; : sortie d&apos;un roman passionnel relatant une aventure aussi improbable qu&apos;envoûtante dans les cercles libertins parisiens, suivi d&apos;un second recueil de récits et scénarios coquins mettant en scène des personnages travestis et des identités assumées.&lt;/p&gt;&lt;p&gt;Entre érotisme littéraire, romance sensuelle, témoignages atypiques et récits de désir, Claire-Obscure Éditions poursuit son ambition : publier des œuvres audacieuses, élégantes et profondément humaines.&lt;/p&gt;', 'news_6a3763dff41d8_1782014943.jpg', '2026-06-21 00:09:04', 'Planing de sorties 2026-2027 de Claire-Obscure Éditions', 'Automne 2026 : parution de Jeux de Chairs, un premier recueil d&ampampampampampampampaposhistoires et de scénarios coquins. Fin 2026 / Début 2027 : découvrez l&ampampampampampampampaposautobiographie d&ampampampampampampampaposune personnalité issue de l&ampampampampampampampaposindustrie pour adultes. 2027 : sortie d&ampampampampampampampaposun roman passionnel, suivi d&ampampampampampampampaposun second recueil de récits et scénarios coquins pour travestis'),
(9, 'Deuxième extrait dévoilé — Jeux de chair - La métamorphose', '&lt;p&gt;Après un premier aperçu, il est temps de poursuivre notre découverte de &lt;i&gt;&lt;strong&gt;La Métamorphose&lt;/strong&gt;&lt;/i&gt;.&lt;/p&gt;&lt;p&gt;Dans cette histoire, les apparences se fissurent peu à peu. Une rencontre, un regard, une atmosphère… et quelque chose commence à changer.&lt;/p&gt;&lt;p&gt;Jusqu’où peut-on aller lorsque l’on accepte de laisser derrière soi l’image que les autres ont de nous ?&lt;/p&gt;&lt;p&gt;Avec ce deuxième extrait, &lt;strong&gt;Clair-Obscur Éditions&lt;/strong&gt; vous invite à découvrir une nouvelle facette de &lt;i&gt;La Métamorphose&lt;/i&gt;, entre sensualité, désir et transformation.&lt;/p&gt;&lt;p&gt;Et si le véritable changement ne faisait que commencer ?&lt;/p&gt;&lt;p&gt;Découvrez bientôt la suite de &lt;i&gt;&lt;strong&gt;Jeux de chair&lt;/strong&gt;&lt;/i&gt;, notre recueil d’histoires et de scénarios coquins.&lt;/p&gt;&lt;blockquote&gt;&lt;p&gt;Elle se tenait à quelques pas de mon bureau, debout au milieu de la pièce à la lumière tamisée, dans la zone de lumière crue que j’avais aménagée à dessein, avec des spots méthodiquement dirigés vers cet emplacement précis. Plutôt mince, dans la fin de la trentaine, le regard un peu supérieur des petites bourgeoises parisiennes, tranchant avec sa position un peu voûtée, elle ne savait pas trop comment placer ses pieds et ses mains, qui trahissaient une légère anxiété et une timidité certaine. Ses cheveux, lissés au fer, d’une terne couleur de paille et coupés droit, tombaient sur ses épaules. Je devinais, sous sa banale robe fuseau à manches longues, gris métallique et arrivant aux genoux, une culotte brésilienne en dentelle ou quelque chose dans le même genre, mi-pratique, mi-sexy, ainsi que les jarretières de ses bas autofixants noirs.&lt;/p&gt;&lt;p&gt;J’entrevoyais néanmoins, dans le décolleté en V et la paire d’escarpins noirs vernis à semelles rouges et à bouts pointus, une tentative maladroite d’être sexy de la part d’une femme ne sachant pas vraiment se mettre en valeur. Un maquillage très léger et discret, sûrement le reflet de sa personnalité effacée, ainsi que quelques petits bijoux dorés complétaient le tableau. Malgré cela, elle était quand même plutôt jolie, avec ses traits fins et bien définis, dégageant cette sensualité particulière aux Parisiennes, ce qui n’était pas pour me déplaire.&lt;/p&gt;&lt;/blockquote&gt;', 'news_6a6533f717d71_1785017335.png', '2026-07-25 18:08:55', 'Deuxième extrait dévoilé — Jeux de chair - La métamorphose', 'Après un premier aperçu, il est temps de poursuivre notre découverte de La Métamorphose. Dans cette histoire, les apparences se fissurent peu à peu. Une rencontre, un regard, une atmosphère… et quelque chose commence à changer.');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paypal_transactions`
--

CREATE TABLE `paypal_transactions` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `txn_id` varchar(100) DEFAULT NULL,
  `payer_email` varchar(150) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `mc_gross` decimal(10,2) DEFAULT NULL,
  `mc_currency` varchar(3) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `pays_frais_port`
--

CREATE TABLE `pays_frais_port` (
  `id` int(11) NOT NULL,
  `pays` varchar(100) NOT NULL,
  `frais_port` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `pays_frais_port`
--

INSERT INTO `pays_frais_port` (`id`, `pays`, `frais_port`) VALUES
(1, 'France', 3.50),
(2, 'Belgique', 5.00),
(3, 'Suisse', 7.50),
(4, 'Canada', 12.00),
(5, 'Autre', 15.00);

-- --------------------------------------------------------

--
-- Structure de la table `prospects`
--

CREATE TABLE `prospects` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `entreprise` varchar(255) DEFAULT NULL,
  `site_web` varchar(500) DEFAULT NULL,
  `type_contact` enum('email','telephone') DEFAULT NULL,
  `besoin` text,
  `statut` varchar(50) DEFAULT 'nouveau',
  `date_rdv` datetime DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `seo_settings`
--

CREATE TABLE `seo_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `seo_settings`
--

INSERT INTO `seo_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_title', 'Clair-Obscur - Maison d\'édition', NULL),
(2, 'site_description', 'Maison d\'édition indépendante de littérature pour adultes. Découvrez nos livres, romans et nouvelles.', NULL),
(3, 'site_keywords', 'livres, édition, littérature adulte, clair-obscur, romans', NULL),
(4, 'og_image', '', NULL),
(5, 'twitter_card', 'summary_large_image', NULL),
(6, 'robots', 'index, follow', NULL),
(7, 'google_analytics', '', NULL),
(8, 'meta_author', 'Clair-Obscur Éditions', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `site_pages`
--

CREATE TABLE `site_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `url` varchar(2048) NOT NULL,
  `parsed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `site_pages`
--

INSERT INTO `site_pages` (`id`, `url`, `parsed`, `created_at`, `updated_at`) VALUES
(48, 'https://clair-obscur-editions.com/', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(49, 'https://clair-obscur-editions.com/auteurs/fiche.php?id=2', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(50, 'https://clair-obscur-editions.com/nouvelles/article.php?id=1', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(51, 'https://clair-obscur-editions.com/nouvelles/article.php?id=2', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(52, 'https://clair-obscur-editions.com/livres/liste.php', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(53, 'https://clair-obscur-editions.com/nouvelles/', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(54, 'https://clair-obscur-editions.com/contact/', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00'),
(55, 'https://clair-obscur-editions.com/cgv/', 1, '2026-08-02 18:24:59', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `unanswered_questions`
--

CREATE TABLE `unanswered_questions` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','answered','closed') NOT NULL DEFAULT 'pending',
  `answer` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `answered_at` timestamp NULL DEFAULT NULL,
  `crawled` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT 'France',
  `telephone` varchar(20) DEFAULT NULL,
  `date_inscription` datetime DEFAULT NULL,
  `is_admin` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `password`, `nom`, `prenom`, `adresse`, `code_postal`, `ville`, `pays`, `telephone`, `date_inscription`, `is_admin`) VALUES
(1, 'admin@clair-obscur.com', '$2y$10$TBkVUgisAldWF0CiL1qAnuMUzQivhYTRH4tyUv9KJmmMT8hfwU2JG', 'Admin', 'Super', NULL, NULL, NULL, 'France', NULL, '2026-05-18 23:31:14', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `auteurs`
--
ALTER TABLE `auteurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livre_id` (`livre_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `details_commandes`
--
ALTER TABLE `details_commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `livre_id` (`livre_id`);

--
-- Index pour la table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`livre_id`,`utilisateur_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `liseuse_livres`
--
ALTER TABLE `liseuse_livres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `liseuse_pages`
--
ALTER TABLE `liseuse_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_page` (`livre_id`,`page_num`);

--
-- Index pour la table `livres`
--
ALTER TABLE `livres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Index pour la table `nouvelles`
--
ALTER TABLE `nouvelles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Index pour la table `paypal_transactions`
--
ALTER TABLE `paypal_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`);

--
-- Index pour la table `pays_frais_port`
--
ALTER TABLE `pays_frais_port`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pays` (`pays`);

--
-- Index pour la table `prospects`
--
ALTER TABLE `prospects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `seo_settings`
--
ALTER TABLE `seo_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Index pour la table `site_pages`
--
ALTER TABLE `site_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_url` (`url`(255)),
  ADD KEY `idx_parsed` (`parsed`);

--
-- Index pour la table `unanswered_questions`
--
ALTER TABLE `unanswered_questions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `auteurs`
--
ALTER TABLE `auteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `details_commandes`
--
ALTER TABLE `details_commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `liseuse_livres`
--
ALTER TABLE `liseuse_livres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `liseuse_pages`
--
ALTER TABLE `liseuse_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `livres`
--
ALTER TABLE `livres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `nouvelles`
--
ALTER TABLE `nouvelles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paypal_transactions`
--
ALTER TABLE `paypal_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pays_frais_port`
--
ALTER TABLE `pays_frais_port`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `prospects`
--
ALTER TABLE `prospects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `seo_settings`
--
ALTER TABLE `seo_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `site_pages`
--
ALTER TABLE `site_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `unanswered_questions`
--
ALTER TABLE `unanswered_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `details_commandes`
--
ALTER TABLE `details_commandes`
  ADD CONSTRAINT `details_commandes_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `details_commandes_ibfk_2` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`);

--
-- Contraintes pour la table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `liseuse_pages`
--
ALTER TABLE `liseuse_pages`
  ADD CONSTRAINT `liseuse_pages_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `liseuse_livres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `livres`
--
ALTER TABLE `livres`
  ADD CONSTRAINT `livres_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `auteurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paypal_transactions`
--
ALTER TABLE `paypal_transactions`
  ADD CONSTRAINT `paypal_transactions_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
