
-- --------------------------------------------------------
-- SUPPRESSION DES TABLES EXISTANTES (Nettoyage)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `ad_clicks`;
DROP TABLE IF EXISTS `ads`;
DROP TABLE IF EXISTS `albums`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `files`;
DROP TABLE IF EXISTS `footer_pages`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `mega_menus`;
DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `newsletter`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `poll_options`;
DROP TABLE IF EXISTS `poll_voters`;
DROP TABLE IF EXISTS `polls`;
DROP TABLE IF EXISTS `popups`;
DROP TABLE IF EXISTS `post_likes`;
DROP TABLE IF EXISTS `post_tags`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `quiz_attempts`;
DROP TABLE IF EXISTS `quiz_options`;
DROP TABLE IF EXISTS `quiz_questions`;
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `rss_imports`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `slides`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `user_favorites`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `widgets`;
DROP TABLE IF EXISTS `bans`;
DROP TABLE IF EXISTS `chat_conversations`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `chat_starred`;
DROP TABLE IF EXISTS `chat_status`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `visitor_analytics`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `project_categories`;
DROP TABLE IF EXISTS `project_likes`;
DROP TABLE IF EXISTS `user_project_favorites`;
DROP TABLE IF EXISTS `game_scores`;
DROP TABLE IF EXISTS `badges`;
DROP TABLE IF EXISTS `user_badges`;

-- --------------------------------------------------------
-- Base de données : `localhost`
-- --------------------------------------------------------

--
-- Structure de la table `ad_clicks`
--

CREATE TABLE `ad_clicks` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `clicked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Name to help you find your way',
  `ad_size` enum('728x90','970x90','468x60','234x60','300x250','300x600','150x150','custom') NOT NULL DEFAULT '300x250',
  `image_url` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT '#',
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `ads`
--

INSERT INTO `ads` (`id`, `name`, `ad_size`, `image_url`, `link_url`, `active`, `clicks`, `created_at`) VALUES
(1, 'Winter Sale', '728x90', 'uploads/ads/ad_691c4b01726db.jpg', 'http://localhost/phpBlog', 'No', 0, '2025-01-01 12:00:00'),
(2, 'Winter Sale', '300x250', 'uploads/ads/ad_691c4ef6959e5.jpg', 'http://localhost/phpBlog', 'No', 0, '2025-01-01 12:00:00'),
(3, 'Winter Sale', '468x60', 'uploads/ads/ad_691c4f77da9f7.jpg', 'http://localhost/phpBlog', 'No', 0, '2025-01-01 12:00:00'),
(4, 'Winter Sale', '300x600', 'uploads/ads/ad_691c512210a01.jpg', 'http://localhost/phpBlog', 'No', 0, '2025-01-01 12:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '1' -- Identifie le créateur de l'album
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` int(11) NOT NULL DEFAULT '1' -- Identifie le créateur de la catégorie
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `category`, `slug`, `description`, `image`) VALUES
(1, 'Site News', 'site-news', 'short description.', 'uploads/categories/Internet-News.jpeg');

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `guest` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL DEFAULT '1' 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `footer_pages`
--

CREATE TABLE `footer_pages` (
  `id` int(11) NOT NULL,
  `page_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique key (e.g., legal, contact)',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `footer_pages`
--

INSERT INTO `footer_pages` (`id`, `page_key`, `title`, `content`, `active`) VALUES
(1, 'legal', 'Legal Information', '<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"legal-notice\" class=\"btn btn-outline-light btn-sm\" title=\"legal-notice\">\r\n        <i class=\"fas fa-balance-scale fa-lg text-info\"></i> <span class=\"small\">Legal Notice</span>\r\n    </a>\r\n    <a href=\"privacy-policy\" class=\"btn btn-outline-light btn-sm\" title=\"privacy-policy\">\r\n        <i class=\"fas fa-user-shield fa-lg text-info\"></i> <span class=\"small\">Privacy Policy</span>\r\n    </a>\r\n</div>', 'Yes'),
(2, 'contact_methods', 'Contact Methods', '<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"contact\" class=\"btn btn-outline-light btn-sm\" title=\"Contact\">\r\n        <i class=\"fas fa-envelope fa-lg text-danger\"></i> <span class=\"small\">Contact</span>\r\n    </a>\r\n<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"chat\" class=\"btn btn-outline-light btn-sm\" title=\"Chats\">\r\n        <i class=\"fab fa-whatsapp fa-lg text-success\"></i> <span class=\"small\">Chats</span>\r\n    </a>\r\n</div></div>', 'Yes'),
(3, 'most_viewed', 'Viewed Pages', '<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"about\" class=\"btn btn-outline-light btn-sm\" title=\"About\">\r\n        <i class=\"fas fa-info-circle text-warning\"></i> <span class=\"small\">About</span>\r\n    </a>\r\n<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"manifest\" class=\"btn btn-outline-light btn-sm\" title=\"Manifest\">\r\n        <i class=\"fas fa-book-reader text-warning\"></i> <span class=\"small\">Manifest</span>\r\n    </a>\r\n</div></div>', 'Yes'),
(4, 'cta_buttons', 'Call-to-Action', '<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"newsletter\" class=\"btn btn-outline-light btn-sm\" title=\"Newsletter\">\r\n        <i class=\"fas fa-envelope fa-lg text-danger\"></i> <span class=\"small\">Newsletter</span>\r\n    </a>\r\n</div>', 'Yes'),
(5, 'trust_badges', 'Signs of Trust', '<div class=\"d-flex gap-2 justify-content-start flex-wrap\"> <a href=\"http://validator.w3.org/check?uri=referer\" target=\"_blank\" class=\"btn btn-outline-light btn-sm\" title=\"Valid HTML5 code\">\r\n        <i class=\"fab fa-html5 fa-lg text-warning\"></i> <span class=\"small\">Validated HTML</span>\r\n    </a>\r\n    <a href=\"http://jigsaw.w3.org/css-validator/check/referer\" target=\"_blank\" class=\"btn btn-outline-light btn-sm\" title=\"Valid CSS3 code\">\r\n        <i class=\"fab fa-css3-alt fa-lg text-info\"></i> <span class=\"small\">Valid CSS</span>\r\n    </a>\r\n    \r\n    <a href=\"https://www.ssllabs.com/ssltest/analyze.html?d={{SITE_URL}}\" class=\"btn btn-outline-light btn-sm\" title=\"Secure HTTPS Connection\">\r\n        <i class=\"fas fa-lock fa-lg text-success\"></i> <span class=\"small\">HTTPS Secure</span>\r\n    </a>\r\n\r\n    <a href=\"https://pagespeed.web.dev/analysis?url={{SITE_URL}}&amp;form_factor=mobile\" class=\"btn btn-outline-light btn-sm\" title=\"Design Responsive\">\r\n        <i class=\"fas fa-mobile-alt fa-lg text-info\"></i> <span class=\"small\">Responsive</span>\r\n    </a>\r\n</div>', 'Yes');

-- --------------------------------------------------------

--
-- Structure de la table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
`album_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `author_id` int(11) NOT NULL DEFAULT '1' -- Identifie qui a uploadé l'image
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mega_menus`
--

CREATE TABLE `mega_menus` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Internal name for the administration',
  `trigger_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Text displayed in the menu bar',
  `trigger_icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-bars' COMMENT 'FontAwesome icon',
  `trigger_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#' COMMENT 'Link when clicking on the parent',
  `col_1_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Explore',
  `col_1_content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'HTML content or Links',
  `col_2_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Categories',
  `col_2_type` enum('categories','custom','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'categories',
  `col_2_content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'If custom type',
  `col_3_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Newest',
  `col_3_type` enum('latest_posts','custom','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'latest_posts',
  `col_3_content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'If custom type',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mega_menus`
--

INSERT INTO `mega_menus` (`id`, `name`, `trigger_text`, `trigger_icon`, `trigger_link`, `col_1_title`, `col_1_content`, `col_2_title`, `col_2_type`, `col_2_content`, `col_3_title`, `col_3_type`, `col_3_content`, `position_order`, `active`, `created_at`) VALUES
(1, 'News', 'Blog', 'fa-bars', '#', 'Explore', '', 'Categories', 'categories', '', 'Newest', 'latest_posts', '', 0, 'No', '2025-01-01 12:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `page` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fa_icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`id`, `page`, `path`, `fa_icon`, `active`) VALUES
(1, 'Home', 'index', 'fas fa-home text-info', 'Yes'),
(2, 'About', 'about', 'fas fa-info-circle text-warning', 'Yes'),
(3, 'Gallery', 'gallery', 'fas fa-images', 'Yes'),
(4, 'Posts', 'blog', 'fas fa-list', 'Yes'),
(5, 'Projects', 'projects', 'fas fa-microchip text-primary', 'Yes'),
(6, 'Contact', 'contact', 'fas fa-envelope text-danger', 'Yes'),
(7, 'FAQ', 'faq', 'fas fa-question-circle text-success', 'Yes'),
(8, 'Quiz', 'quiz', 'fas fa-graduation-cap text-success', 'Yes'),
(9, 'Info', 'page?name=about', 'fas fa-info text-info', 'No'),
(10, 'Legal Notice', 'legal-notice', 'fas fa-balance-scale me-2 text-info', 'No'),
(11, 'Privacy Policy', 'privacy-policy', 'fas fa-user-shield me-2 text-info', 'No'),
(12, 'Newsletter', 'newsletter', 'fas fa-envelope text-danger', 'No'),
(13, 'Chats', 'chat', 'fab fa-whatsapp fa-lg text-success', 'Yes'),
(14, 'Games', 'games/index', 'fas fa-gamepad', 'No'),
(15, 'Shop', 'shop', 'fas fa-shopping-cart', 'Yes');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `viewed` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `author_id` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `meta_title`, `meta_description`, `content`, `image`, `active`, `author_id`) VALUES
(1, 'About', 'about', 'about', 'Page About', '<p></p>', NULL, 'Yes', 1);

-- --------------------------------------------------------

--
-- Structure de la table `poll_options`
--

CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `votes` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `poll_voters`
--

CREATE TABLE `poll_voters` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `popups`
--

CREATE TABLE `popups` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `display_pages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `show_once_per_session` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `delay_seconds` int(3) NOT NULL DEFAULT '2',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `popup_type` enum('Standard','Design') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Standard',
  `background_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci,
  `newsletter_active` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT 'No',
  `footer_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post_tags`
--

CREATE TABLE `post_tags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `imported_guid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `active` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `featured` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `download_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publish_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`id`, `category_id`, `title`, `slug`, `meta_title`, `meta_description`, `image`, `content`, `imported_guid`, `author_id`, `active`, `featured`, `download_link`, `github_link`, `publish_at`, `views`, `created_at`) VALUES
(1, 1, 'Demo Test Post', 'demo-test-post', 'demo test Post', 'demo meta description', '', '<p>demo test post 1</p>', NULL, 1, 'Yes', 'Yes', 'https://localhost/download/test1.zip', 'https://github.com/', '2025-01-01 12:00:00', 0, '2025-01-01 12:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL COMMENT 'Score as a percentage (e.g., 80)',
  `time_seconds` int(11) NOT NULL COMMENT 'Total time in seconds',
  `attempt_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_options`
--

CREATE TABLE `quiz_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` longtext COLLATE utf8mb4_unicode_ci,
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty` enum('FACILE','NORMAL','DIFFICILE','EXPERT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rss_imports`
--

CREATE TABLE `rss_imports` (
  `id` int(11) NOT NULL,
  `feed_url` varchar(255) NOT NULL,
  `import_as_user_id` int(11) NOT NULL,
  `import_as_category_id` int(11) NOT NULL,
  `last_import_time` datetime DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT '1',
  `site_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sitename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gcaptcha_sitekey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gcaptcha_secretkey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `head_customcode` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `head_customcode_enabled` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Off',
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discord` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rtl` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_format` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `layout` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latestposts_bar` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sidebar_position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posts_per_row` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `background_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posts_per_page` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `projects_per_page` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '9',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `favicon_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apple_touch_icon_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_author` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_generator` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_robots` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sticky_header` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Off',
  `maintenance_mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Off',
  `maintenance_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_message` text COLLATE utf8mb4_unicode_ci,
  `homepage_slider` enum('Featured','Custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Featured' COMMENT 'Choice between (Featured) articles or a (Custom) slider.',
  `google_maps_code` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maintenance_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ban_bg_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.jpg',
  `mail_protocol` enum('mail','smtp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mail',
  `mail_from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `mail_from_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_port` int(5) DEFAULT '587',
  `smtp_user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_enc` enum('tls','ssl','none') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `comments_approval` int(1) NOT NULL DEFAULT '0',
  `comments_blacklist` text COLLATE utf8mb4_unicode_ci,
  `cookie_consent_enabled` int(1) NOT NULL DEFAULT '1',
  `cookie_message` text COLLATE utf8mb4_unicode_ci,
  `event_mode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'None, Christmas, Halloween, BlackFriday...',
  `event_effect` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'Snow, Confetti, Bats...',
  `event_banner_active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `event_banner_content` text COLLATE utf8mb4_unicode_ci,
  `event_banner_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#dc3545'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_url`, `sitename`, `description`, `email`, `gcaptcha_sitekey`, `gcaptcha_secretkey`, `head_customcode`, `head_customcode_enabled`, `facebook`, `instagram`, `twitter`, `youtube`, `linkedin`, `discord`, `comments`, `rtl`, `date_format`, `layout`, `latestposts_bar`, `sidebar_position`, `posts_per_row`, `theme`, `background_image`, `posts_per_page`, `projects_per_page`, `meta_title`, `favicon_url`, `apple_touch_icon_url`, `meta_author`, `meta_generator`, `meta_robots`, `sticky_header`, `maintenance_mode`, `maintenance_title`, `maintenance_message`, `homepage_slider`, `google_maps_code`, `site_logo`, `maintenance_image`, `ban_bg_image`, `mail_protocol`, `mail_from_name`, `mail_from_email`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_enc`, `comments_approval`, `comments_blacklist`, `cookie_consent_enabled`, `cookie_message`, `event_mode`, `event_effect`, `event_banner_active`, `event_banner_content`, `event_banner_color`) VALUES
(1, 'http://localhost/F.A.Blog-', 'F.A-Blog', 'Don t miss a thing: Subscribe to our newsletter to get our best insights delivered straight to your inbox.', 'admin@exemple.com', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe', 'IDwhLS0gR29vZ2xlIEFuYWx5dGljcyA0IChHQTQpIFRyYWNraW5nIENvZGUgLS0+DQogPHNjcmlwdCBhc3luYyBzcmM9Imh0dHBzOi8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tL2d0YWcvanM/aWQ9Ry1YWFhYWFhYWFhYIj48L3NjcmlwdD4NCiA8c2NyaXB0Pg0KICAgd2luZG93LmRhdGFMYXllciA9IHdpbmRvdy5kYXRhTGF5ZXIgfHwgW107DQogICBmdW5jdGlvbiBndGFnKCl7ZGF0YUxheWVyLnB1c2goYXJndW1lbnRzKTt9DQogICBndGFnKCdqcycsIG5ldyBEYXRlKCkpOw0KICAgZ3RhZygnY29uZmlnJywgJ0ctWFhYWFhYWFhYWCcpOw0KIDwvc2NyaXB0Pg0KPCEtLSBSZXN0IG9mIHlvdXIgaGVhZCBjb250ZW50IC0tPg==', 'Off', 'https://www.facebook.com/', 'https://www.instagram.com/', '', 'https://www.youtube.com/', '', 'https://discord.com/', 'guests', 'No', 'd.m.Y', 'Fixed', 'Enabled', 'Right', '2', 'Bootstrap 5', '', '4', '3', 'F.A Blog - Titre SEO', 'assets/img/favicon.png', 'assets/img/favicon.png', 'ZelTroN2K3_WEB', 'faBlog', 'index, follow', 'Off', 'Off', 'Site Under Maintenance', '<p>Our website is currently undergoing maintenance. We apologize for the inconvenience. We will be back soon!</p>', 'Featured', 
'PGlmcmFtZSBzcmM9Imh0dHBzOi8vd3d3Lmdvb2dsZS5jb20vbWFwcy9lbWJlZD9wYj0hMW0xOCExbTEyITFtMyExZDI2MTcuMTA4MjAzNDg0ODA5ITJkMzEuMzg3NTAxMjc2NzYyNzghM2Q0OS4wMDg1MjYzOTAxMjg4OCEybTMhMWYwITJmMCEzZjAhM20yITFpMTAyNCEyaTc2OCE0ZjEzLjEhM20zITFtMiExczB4NDBkMTc3OWU5NTEwYjM5MyUzQTB4YWQyN2YwZTRkOTVmOWNjYiEyc0xlbmluYSUyMFN0JTJDJTIwMzUlMkMlMjBTaHBvbGElMkMlMjBDaGVya2FzJiMzOTtrYSUyMG9ibGFzdCUyQyUyMFVrcmFpbmUlMkMlMjAyMDYwMCE1ZTAhM20yITFzZnIhMnNmciE0djE3NjM1NjkyNTk5ODIhNW0yITFzZnIhMnNmciIgd2lkdGg9IjYwMCIgaGVpZ2h0PSI0NTAiIHN0eWxlPSJib3JkZXI6MDsiIGFsbG93ZnVsbHNjcmVlbj0iIiBsb2FkaW5nPSJsYXp5IiByZWZlcnJlcnBvbGljeT0ibm8tcmVmZXJyZXItd2hlbi1kb3duZ3JhZGUiPjwvaWZyYW1lPg==', 'uploads/other/logo_693034e757e31.png', 'assets/img/maintenance.jpg', 'default.jpg', 'smtp', '', '', '', 0, '', '', 'tls', 0, 'viagra,cialis,levitra,xanax,valium,tramadol,percocet,casino,poker,roulette,slots,gambling,betting,jackpot,bitcoin,crypto,ethereum,dogecoin,wallet,invest,investment,forex,trading,binary options,loan,lender,credit,debt,insurance,mortgage,passive income,whatsapp,telegram,dm me,cash app,paypal,marketing,seo,ranking,traffic,website,domain,merde,putain,salope,connard,connasse,encule,encule,fils de pute,batard,nique,niquer,bouffon,abruti,trou du cul,bite,couille,chatte,foutre,bordel,pede,pede,gouine,negre,bougnoule,youpin,raton,triso,fuck,shit,bitch,asshole,bastard,dick,cock,pussy,cunt,whore,slut,faggot,nigger,retard,idiot,stupid,suck,jerk,wanker,porn,porno,sexe,sex,hentai,xxx,nude,naked,camgirl,webcam,milf,orgy,incest,erotic,escort,viagra,pÃƒÂ©nis,penis,vagin,vagina,anal,oral,blowjob,tits,boobs,seins,fesses,ass,booty,viagra,cialis,levitra,xanax,valium,tramadol,percocet,casino,poker,roulette,slots,gambling,betting,jackpot,bitcoin,crypto,ethereum,dogecoin,wallet,invest,investment,forex,trading,binary options,loan,lender,credit,debt,insurance,mortgage,passive income,marketing,seo,porn,porno,sexe,sex,hentai,xxx,nude,naked,camgirl,webcam,milf,orgy,incest,erotic,escort,pÃƒÂ©nis,penis,vagin,vagina,anal,oral,blowjob,tits,boobs,seins,fesses,ass,booty,merde,putain,salope,connard,connasse,encule,encule,fils de pute,batard,nique,niquer,bouffon,abruti,trou du cul,bite,couille,chatte,foutre,bordel,pede,pede,gouine,negre,bougnoule,youpin,raton,triso,fuck,shit,bitch,asshole,bastard,dick,cock,pussy,cunt,whore,slut,faggot,nigger,retard,idiot,stupid,suck,jerk,wanker,suicide,kill yourself,die,http,https,www,.com,.net,.org,.biz,.info', 1, 'This site uses cookies to provide you with the best service. By continuing to browse the site, you agree to our use of cookies.', 'None', 'None', 'No', '0', '#dc3545');

-- --------------------------------------------------------

--
-- Structure de la table `slides`
--

CREATE TABLE `slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `active` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, 'post', 'post'),
(2, 'tuto', 'tuto'),
(4, 'Technology', 'technology');
-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ex: CEO of TechCorp',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` enum('Yes','No','Pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assets/img/avatar.png',
  `bio` text COLLATE utf8mb4_unicode_ci,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'User',
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `typing_in_chat_with` int(11) DEFAULT '0',
  `two_factor_secret` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widgets`
--

CREATE TABLE `widgets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widget_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'html',
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `config_data` text COLLATE utf8mb4_unicode_ci,
  `position` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sidebar',
  `active` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `widgets`
--

INSERT INTO `widgets` (`id`, `title`, `widget_type`, `content`, `config_data`, `position`, `active`) VALUES
(1, 'Chats On Line', 'online_users', NULL, NULL, 'Sidebar', 'Yes'),
(2, 'Latest Projects', 'latest_projects', NULL, '{\"count\":5}', 'Sidebar', 'Yes'),
(3, 'Shop Projects', 'shop', NULL, '{\"count\":2}', 'Sidebar', 'No'),
(4, 'The F.A Blog Spirit', 'html', '<p data-path-to-node=\"2\">Welcome to the world of <b>F.A Blog</b>.</p><p data-path-to-node=\"3\">Here, we take the time to decipher, analyze, and share insights on [Your Topic]. Far from the online noise, this space is dedicated to quality and authenticity.</p><p data-path-to-node=\"4\">My goal? To provide you with content that inspires and sparks reflection. Whether you are here out of curiosity or passion, you are now part of the journey.</p><p data-path-to-node=\"5\"><b>Don t miss a thing:</b>\r\nSubscribe to our newsletter to get our best insights delivered straight to your inbox.</p><blockquote data-path-to-node=\"6\"><p data-path-to-node=\"6,0\"><i>\"Understanding today to better build tomorrow.\"</i></p></blockquote>\r\n', NULL, 'Sidebar', 'Yes'),
(5, 'Quiz Leaderboard (Top 10)', 'quiz_leaderboard', NULL, NULL, 'Sidebar', 'No'),
(6, 'FAQ Leaderboard', 'faq_leaderboard', NULL, NULL, 'Sidebar', 'No'),
(7, 'Slider Testimonials', 'testimonials', NULL, NULL, 'Sidebar', 'No'),
(8, 'Newsletter', 'newsletter', NULL, NULL, 'Sidebar', 'No');

-- --------------------------------------------------------

--
-- Structure de la table `bans`
--
CREATE TABLE `bans` (
  `id` int(11) NOT NULL,
  `ban_type` enum('ip','username','email','user_agent') NOT NULL DEFAULT 'ip',
  `ban_value` varchar(255) NOT NULL,
  `reason` text,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------


--
-- Structure de la table `chat_conversations`
--
CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user_1` int(11) NOT NULL,
  `user_2` int(11) NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `archived_user_1` enum('No','Yes') DEFAULT 'No',
  `archived_user_2` enum('No','Yes') DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `chat_messages`
--
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('text','image','file') DEFAULT 'text',
  `is_read` enum('Yes','No') DEFAULT 'No',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `chat_starred`
--
CREATE TABLE `chat_starred` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `chat_status`
--
CREATE TABLE `chat_status` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('text','image') NOT NULL DEFAULT 'text',
  `content` text NOT NULL COMMENT 'Texte ou Chemin Image',
  `caption` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
--
-- Structure de la table `activity_logs`
--  
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL, -- Ex: 'Login', 'Delete Post', 'Update Settings'
  `details` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `visitor_analytics`
--

CREATE TABLE `visitor_analytics` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) NOT NULL,
  `visit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `project_category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pitch` text COLLATE utf8mb4_unicode_ci COMMENT 'Short summary',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` enum('Easy','Intermediate','Advanced','Expert') COLLATE utf8mb4_unicode_ci DEFAULT 'Intermediate',
  `duration` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ex: 2 hours',
  `team_credits` text COLLATE utf8mb4_unicode_ci COMMENT 'List of contributors',
  `hardware_parts` longtext COLLATE utf8mb4_unicode_ci COMMENT 'HTML list or JSON',
  `software_apps` longtext COLLATE utf8mb4_unicode_ci COMMENT 'HTML list or JSON',
  `hand_tools` longtext COLLATE utf8mb4_unicode_ci COMMENT 'JSON list',
  `story` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `schematics_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `files_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` enum('Yes','No','Draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `featured` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `is_product` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `price` decimal(10,2) DEFAULT '0.00',
  `stock_status` enum('In Stock','Low Stock','Out of Stock','Pre-order') COLLATE utf8mb4_unicode_ci DEFAULT 'In Stock',
  `buy_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link to PayPal, Stripe or External Shop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `author_id`, `project_category_id`, `title`, `slug`, `pitch`, `image`, `difficulty`, `duration`, `team_credits`, `hardware_parts`, `software_apps`, `hand_tools`, `story`, `schematics_link`, `code_link`, `files_link`, `active`, `featured`, `is_product`, `views`, `created_at`, `price`, `stock_status`, `buy_link`) VALUES
(1, 1, 1, 'Demo Projects', 'demo-projects', 'A brief explanation for testing the module.', '', 'Intermediate', '2 hours', '<p>Admin, User</p>', '[{\"name\":\"Arduino Uno\",\"qty\":\"1\",\"link\":\"https:\\/\\/www.amazon.fr\\/Arduino-A000066-M%C3%A9moire-flash-32\\/dp\\/B008GRTSV6\\/\",\"img\":\"\"}]', '[{\"name\":\"Arduino IDE\",\"qty\":\"1\",\"link\":\"https:\\/\\/www.arduino.cc\\/en\\/software\\/\",\"img\":\"\"}]', '[{\"name\":\"Fer \\u00e0 Souder\",\"qty\":\"1\",\"link\":\"\",\"img\":\"\"},{\"name\":\"Eteins\",\"qty\":\"1\",\"link\":\"\",\"img\":\"\"}]', '<p>Ceci est une histoire de test pour valider l affichage du projet.</p>', 'http://freelance-addons.net', 'https://github.com/', '', 'Yes', 'Yes', 'No', 1, '2025-01-01 12:00:00', 0.00, 'In Stock', NULL),
(2, 1, 1, 'Smart Weather Station', 'smart-weather-station', 'Create your own local weather station using ESP32.', '', 'Advanced', '5 hours', '<p>ZelTroN-2K3</p>', '<ul><li>ESP32 Board</li><li>DHT22 Sensor</li><li>OLED Display</li></ul>', '<ul><li>Visual Studio Code</li><li>PlatformIO</li></ul>', NULL, '<p>In this project, we will build a connected weather station...</p>', 'http://freelance-addons.net', 'https://github.com/', NULL, 'Draft', 'No', 'No', 0, '2025-01-02 14:30:00', 0.00, 'In Stock', NULL),
(3, 1, 1, 'Blinking LED for Beginners', 'blinking-led', 'The Hello World of hardware.', '', 'Easy', '30 mins', '<p>Open Source Community</p>', '<ol><li>Arduino Uno</li><li>LED Blue</li><li>Resistor 220ohm</li></ol>', '<ol><li>Arduino IDE</li></ol>', NULL, '<p>The classic blinking LED project to get started with electronics.</p>', 'http://freelance_addons.net', 'https://github.com/', NULL, 'Draft', 'No', 'No', 0, '2025-01-03 09:15:00', 0.00, 'In Stock', NULL),
(4, 1, 1, 'Test Projet Schop', 'test-projet-schop', 'Test Projet Schop', '', 'Expert', '4 hours', '<p>Test Projet Schop</p>', '[{\"name\":\"Arduino Uno\",\"qty\":\"1\",\"link\":\"https:\\/\\/www.amazon.fr\\/Arduino-A000066-M%C3%A9moire-flash-32\\/dp\\/B008GRTSV6\\/\",\"img\":\"\"}]', '[{\"name\":\"Arduino IDE\",\"qty\":\"1\",\"link\":\"https:\\/\\/www.arduino.cc\\/en\\/software\\/\",\"img\":\"\"}]', '[{\"name\":\"Fer \\u00e0 Souder\",\"qty\":\"1\",\"link\":\"\",\"img\":\"\"}]', '<p>Test Projet Schop</p>', 'http://freelance-addons.net', 'https://github.com/', '', 'Draft', 'Yes', 'Yes', 7, '2025-12-07 21:51:04', 10.00, 'In Stock', 'https://paypal.me/');

-- --------------------------------------------------------

--
-- Structure de la table `project_categories`
--

-- 1. Créer la table des catégories de projets
CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `project_categories`
--

INSERT INTO `project_categories` (`id`, `category`, `slug`, `description`, `image`, `author_id`, `created_at`) VALUES
(1, 'Table Projects', 'table-projects', 'Brief description of the project', '', 1, '2025-01-01 12:00:00');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `project_likes`
--

CREATE TABLE `project_likes` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `user_project_favorites`
--

CREATE TABLE `user_project_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


--
-- Déchargement des données de la table `game_scores`
--
CREATE TABLE `game_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_name` varchar(50) NOT NULL, -- 'snake', 'tetris', 'space-invaders'
  `score` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `badges`
--
CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL, -- FontAwesome ex: 'fas fa-trophy'
  `color` varchar(20) NOT NULL DEFAULT 'primary', -- bootstrap class: primary, warning, danger
  `trigger_type` varchar(50) NOT NULL, -- 'score_snake', 'score_tetris', 'manual'
  `trigger_value` int(11) NOT NULL DEFAULT '0' -- Score nécessaire pour obtenir le badge
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `user_badges`
--
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Insérer des badges par défaut
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `color`, `trigger_type`, `trigger_value`) VALUES
(1, 'Snake Novice', 'Score > 50 at Snake', 'fas fa-worm', 'success', 'score_snake', 50),
(2, 'Snake Master', 'Score > 200 at Snake', 'fas fa-crown', 'warning', 'score_snake', 200),
(3, 'Tetris Builder', 'Score > 1000 at Tetris', 'fas fa-cubes', 'info', 'score_tetris', 1000),
(4, 'Space Defender', 'Score > 500 at Space Invaders', 'fas fa-rocket', 'danger', 'score_space', 500),
(5, 'VIP Member', 'Premium Community Member', 'fas fa-gem', 'primary', 'manual', 0);

-- --------------------------------------------------------

-- --------------------------------------------------------
-- Index pour les tables déchargées
-- --------------------------------------------------------

--
-- Index pour la table `ad_clicks`
--
ALTER TABLE `ad_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_check` (`ad_id`,`ip_address`);

--
-- Index pour la table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `footer_pages`
--
ALTER TABLE `footer_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_key` (`page_key`);

--
-- Index pour la table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mega_menus`
--
ALTER TABLE `mega_menus`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `poll_options`
--
ALTER TABLE `poll_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- Index pour la table `poll_voters`
--
ALTER TABLE `poll_voters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_ip` (`poll_id`,`ip_address`);

--
-- Index pour la table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `popups`
--
ALTER TABLE `popups`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_like` (`post_id`,`user_id`),
  ADD UNIQUE KEY `session_like` (`post_id`,`session_id`(191)),
  ADD KEY `post_id` (`post_id`);

--
-- Index pour la table `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `imported_guid_unique` (`imported_guid`);

--
-- Index pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Index pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Index pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `rss_imports`
--
ALTER TABLE `rss_imports`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `slides`
--
ALTER TABLE `slides`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_favorite_post` (`user_id`,`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_starred`
--
ALTER TABLE `chat_starred`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_status`
--
ALTER TABLE `chat_status`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `visitor_analytics`
--
ALTER TABLE `visitor_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`visit_date`),
  ADD KEY `idx_url` (`page_url`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `project_likes`
--
ALTER TABLE `project_likes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_project_favorites`
--
ALTER TABLE `user_project_favorites`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `game_scores`
--
ALTER TABLE `game_scores`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT pour les tables déchargées
-- --------------------------------------------------------

--
-- AUTO_INCREMENT pour la table `ad_clicks`
--
ALTER TABLE `ad_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `footer_pages`
--
ALTER TABLE `footer_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mega_menus`
--
ALTER TABLE `mega_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `poll_options`
--
ALTER TABLE `poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `poll_voters`
--
ALTER TABLE `poll_voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `popups`
--
ALTER TABLE `popups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post_tags`
--
ALTER TABLE `post_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rss_imports`
--
ALTER TABLE `rss_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `slides`
--
ALTER TABLE `slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT pour la table `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `bans`
--
ALTER TABLE `bans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;  

--
-- AUTO_INCREMENT pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;  

--
-- AUTO_INCREMENT pour la table `chat_starred`
--
ALTER TABLE `chat_starred`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `chat_status`
--
ALTER TABLE `chat_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `visitor_analytics`
--
ALTER TABLE `visitor_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `project_likes`
--
ALTER TABLE `project_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_project_favorites`
--
ALTER TABLE `user_project_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `game_scores`
--
ALTER TABLE `game_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
