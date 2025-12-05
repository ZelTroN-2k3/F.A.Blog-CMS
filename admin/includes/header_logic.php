<?php
// -------------------------------------------------------------------------
// includes/header_logic.php
// Gestion de la sécurité, authentification et calcul des badges du menu
// -------------------------------------------------------------------------

// MODIFICATION : Utilisation de include_once pour éviter les erreurs de "redeclare"
include_once '../core.php'; 
// session_start() est déjà dans core.php

// --- FONCTION DE LOG D'ACTIVITÉ ---
if (!function_exists('log_activity')) {
    function log_activity($user_id, $action, $details) {
        global $connect;
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // On nettoie les entrées au cas où
        $action = strip_tags($action);
        $details = strip_tags($details);
        
        $stmt = mysqli_prepare($connect, "INSERT INTO activity_logs (user_id, action_type, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $details, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// --- VERIFICATION AUTHENTIFICATION ---
if (isset($_SESSION['sec-username'])) {
    $uname = $_SESSION['sec-username'];
    
    // Use prepared statement for session check
    $stmt = mysqli_prepare($connect, "SELECT * FROM `users` WHERE username=? AND (role='Admin' OR role='Editor')");
    mysqli_stmt_bind_param($stmt, "s", $uname);
    mysqli_stmt_execute($stmt);
    $suser = mysqli_stmt_get_result($stmt);
    $count = mysqli_num_rows($suser);
    mysqli_stmt_close($stmt);

    if ($count <= 0) {
        header("Location: " . $settings['site_url']);
        exit;
    }
    $user = mysqli_fetch_assoc($suser);
} else {
    header("Location: ../login");
    exit;
}

// --- VALIDATION CSRF (GET) ---
$csrf_token = $_SESSION['csrf_token'] ?? '';
if (isset($_GET['delete-id']) || isset($_GET['up-id']) || isset($_GET['down-id']) || isset($_GET['delete_bgrimg']) || isset($_GET['unsubscribe']) || isset($_GET['approve-comment']) || isset($_GET['delete-comment'])) {
    validate_csrf_token_get();
}

// --- RESTRICTIONS D'ACCÈS (ROLE EDITOR) ---
if ($user['role'] == "Editor") {
    $allowed_pages = [
        'dashboard.php', 'add_post.php', 'posts.php', 'edit_post.php',
        'add_image.php', 'gallery.php', 'edit_gallery.php',
        'albums.php', 'add_album.php', 'edit_album.php',
        'upload_file.php', 'files.php',
        'categorys.php', 'add_category.php', 'edit_category.php',
        'comments.php', 'edit_comment.php', 'edit_user.php'
    ];
    
    if (!in_array(basename($_SERVER['SCRIPT_NAME']), $allowed_pages)) {
        header("Location: dashboard.php");
        exit;
    }
}

// --- FONCTION UTILITAIRE ---
if (!function_exists('byte_convert')) {
    function byte_convert($size) {
        if ($size < 1024) return $size . ' Byte';
        if ($size < 1048576) return sprintf("%4.2f KB", $size / 1024);
        if ($size < 1073741824) return sprintf("%4.2f MB", $size / 1048576);
        if ($size < 1099511627776) return sprintf("%4.2f GB", $size / 1073741824);
        else return sprintf("%4.2f TB", $size / 1073741824);
    }
}

// Variable pour la page active (utilisée dans la sidebar)
$current_page = basename($_SERVER['SCRIPT_NAME']);

// =========================================================================
// CALCUL DES BADGES ET COMPTEURS
// =========================================================================

// Initialisation des variables
$unread_messages_count = 0;
$pending_comments_count = 0;
$badge_backup_count = 0;
$posts_pending_count = 0;
$count_testi_pending = 0;
$maintenance_status = 'Off';

$badge_rss_count = 0;
$badge_popups_active = 0; $badge_popups_inactive = 0;
$badge_polls_active = 0; $badge_polls_inactive = 0;
$badge_faq_active = 0; $badge_faq_inactive = 0;
$badge_slides_active = 0; $badge_slides_inactive = 0;
$badge_mega_menus_active = 0; $badge_mega_menus_inactive = 0;
$badge_quiz_active = 0; $badge_quiz_inactive = 0;
$badge_footer_pages_active = 0; $badge_footer_pages_inactive = 0;
$badge_ads_active = 0; $badge_ads_inactive = 0;
$badge_bans_count = 0;

$total_users_count = 0;
$total_pages_count = 0;
$pages_published_count = 0; $pages_draft_count = 0;
$total_categories_count = 0;
$total_widgets_count = 0;
$widget_active_count = 0; $widget_inactive_count = 0;
$total_subscribers_count = 0;
$total_albums_count = 0;
$menu_published_count = 0; $menu_draft_count = 0;

// --- REQUÊTES ADMIN ---
if ($user['role'] == "Admin") {
    // Messages
    $stmt_msg = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM messages WHERE viewed='No'");
    mysqli_stmt_execute($stmt_msg);
    $unread_messages_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_msg))['count'];
    mysqli_stmt_close($stmt_msg);

    // Commentaires en attente
    $stmt_comm = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM comments WHERE approved='No'");
    mysqli_stmt_execute($stmt_comm);
    $pending_comments_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_comm))['count'];
    mysqli_stmt_close($stmt_comm);
    
    // Backups
    $backup_dir = __DIR__ . '/../../backup-database/'; // Ajustement chemin relatif depuis includes/
    // Si le dossier n'est pas trouvé avec le chemin ci-dessus, on tente le chemin original relatif à admin/
    if (!is_dir($backup_dir)) { $backup_dir = '../backup-database/'; }
    
    $backup_files = @glob($backup_dir . "*.sql");
    if ($backup_files) {
        $badge_backup_count = count($backup_files);
    }

    // Articles Pending
    $q_pp = mysqli_query($connect, "SELECT COUNT(id) as count FROM posts WHERE active='Pending'");
    if ($q_pp) $posts_pending_count = mysqli_fetch_assoc($q_pp)['count'];
    
    // Maintenance
    $maintenance_status = $settings['maintenance_mode'] ?? 'Off';
    
    // RSS
    $q_rss = mysqli_query($connect, "SELECT COUNT(id) as count FROM rss_imports");
    if($q_rss) $badge_rss_count = mysqli_fetch_assoc($q_rss)['count'];
    
    // Popups
    $q_pop_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM popups WHERE active='Yes'");
    if($q_pop_a) $badge_popups_active = mysqli_fetch_assoc($q_pop_a)['count'];
    $q_pop_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM popups WHERE active='No'");
    if($q_pop_i) $badge_popups_inactive = mysqli_fetch_assoc($q_pop_i)['count'];

    // Polls
    $q_poll_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM polls WHERE active='Yes'");
    if($q_poll_a) $badge_polls_active = mysqli_fetch_assoc($q_poll_a)['count'];
    $q_poll_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM polls WHERE active='No'");
    if($q_poll_i) $badge_polls_inactive = mysqli_fetch_assoc($q_poll_i)['count'];
    
    // FAQ
    $q_faq_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM faqs WHERE active='Yes'");
    if($q_faq_a) $badge_faq_active = mysqli_fetch_assoc($q_faq_a)['count'];
    $q_faq_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM faqs WHERE active='No'");
    if($q_faq_i) $badge_faq_inactive = mysqli_fetch_assoc($q_faq_i)['count'];
    
    // Slides
    $q_sli_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM slides WHERE active='Yes'");
    if($q_sli_a) $badge_slides_active = mysqli_fetch_assoc($q_sli_a)['count'];
    $q_sli_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM slides WHERE active='No'");
    if($q_sli_i) $badge_slides_inactive = mysqli_fetch_assoc($q_sli_i)['count'];

    // Mega Menus
    $q_mm_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM mega_menus WHERE active='Yes'");
    if($q_mm_a) $badge_mega_menus_active = mysqli_fetch_assoc($q_mm_a)['count'];
    $q_mm_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM mega_menus WHERE active='No'");
    if($q_mm_i) $badge_mega_menus_inactive = mysqli_fetch_assoc($q_mm_i)['count'];

    // Quiz
    $q_quiz_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM quizzes WHERE active='Yes'");
    if($q_quiz_a) $badge_quiz_active = mysqli_fetch_assoc($q_quiz_a)['count'];
    $q_quiz_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM quizzes WHERE active='No'");
    if($q_quiz_i) $badge_quiz_inactive = mysqli_fetch_assoc($q_quiz_i)['count'];

    // Footer Pages
    $q_fp_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM footer_pages WHERE active='Yes'");
    if($q_fp_a) $badge_footer_pages_active = mysqli_fetch_assoc($q_fp_a)['count'];
    $q_fp_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM footer_pages WHERE active='No'");
    if($q_fp_i) $badge_footer_pages_inactive = mysqli_fetch_assoc($q_fp_i)['count'];

    // Ads
    $q_ads_a = mysqli_query($connect, "SELECT COUNT(id) as count FROM ads WHERE active='Yes'");
    if($q_ads_a) $badge_ads_active = mysqli_fetch_assoc($q_ads_a)['count'];
    $q_ads_i = mysqli_query($connect, "SELECT COUNT(id) as count FROM ads WHERE active='No'");
    if($q_ads_i) $badge_ads_inactive = mysqli_fetch_assoc($q_ads_i)['count'];

    // Bans
    $q_bans = mysqli_query($connect, "SELECT COUNT(id) as count FROM bans");
    if($q_bans) $badge_bans_count = mysqli_fetch_assoc($q_bans)['count'];
    
    // Users
    $user_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `users`");
    $total_users_count = mysqli_fetch_assoc($user_count_query)['count'];
    
    // Pages
    $page_pub_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `pages` WHERE active='Yes'");
    $pages_published_count = mysqli_fetch_assoc($page_pub_query)['count'];
    $page_draft_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `pages` WHERE active='No'");
    $pages_draft_count = mysqli_fetch_assoc($page_draft_query)['count'];
    $total_pages_count = $pages_published_count + $pages_draft_count;
    
    // Categories
    $cat_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `categories`");
    $total_categories_count = mysqli_fetch_assoc($cat_count_query)['count'];
    
    // Widgets
    $widget_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `widgets`");
    $total_widgets_count = mysqli_fetch_assoc($widget_count_query)['count'];
    $widget_active_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `widgets` WHERE active='Yes'");
    $widget_active_count = mysqli_fetch_assoc($widget_active_query)['count'];
    $widget_inactive_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `widgets` WHERE active='No'");
    $widget_inactive_count = mysqli_fetch_assoc($widget_inactive_query)['count'];
    
    // Subscribers
    $sub_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `newsletter`");
    $total_subscribers_count = mysqli_fetch_assoc($sub_count_query)['count'];
    
    // Albums
    $album_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `albums`");
    $total_albums_count = mysqli_fetch_assoc($album_count_query)['count'];
    
    // Menus
    $menu_pub_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `menu` WHERE active='Yes'");
    $menu_published_count = mysqli_fetch_assoc($menu_pub_query)['count'];
    $menu_draft_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `menu` WHERE active='No'");
    $menu_draft_count = mysqli_fetch_assoc($menu_draft_query)['count'];
    
    // Testimonials
    $q_tp = mysqli_query($connect, "SELECT COUNT(id) as count FROM testimonials WHERE active='Pending'");
    if($q_tp) $count_testi_pending = mysqli_fetch_assoc($q_tp)['count'];

    // Tags
    $query_tags_count = mysqli_query($connect, "SELECT COUNT(id) AS count FROM tags");
    $count_tags = mysqli_fetch_assoc($query_tags_count)['count'];
}

// --- REQUÊTES GLOBALES (Tout Rôle) ---

// Total Posts
$stmt_total_posts = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM posts");
mysqli_stmt_execute($stmt_total_posts);
$total_posts_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total_posts))['count'];
mysqli_stmt_close($stmt_total_posts);

// Images & Files
$img_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `gallery`");
$total_images_count = mysqli_fetch_assoc($img_count_query)['count'];

$file_count_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `files`");
$total_files_count = mysqli_fetch_assoc($file_count_query)['count'];

// Status Posts
$posts_published_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `posts` WHERE active='Yes' AND publish_at <= NOW()");
$posts_published_count = mysqli_fetch_assoc($posts_published_query)['count'];

$posts_scheduled_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `posts` WHERE active='Yes' AND publish_at > NOW()");
$posts_scheduled_count = mysqli_fetch_assoc($posts_scheduled_query)['count'];

$posts_draft_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `posts` WHERE active='No'");
$posts_draft_count = mysqli_fetch_assoc($posts_draft_query)['count'];

$posts_featured_query = mysqli_query($connect, "SELECT COUNT(id) AS count FROM `posts` WHERE active='Yes' AND featured='Yes' AND publish_at <= NOW()");
$posts_featured_count = mysqli_fetch_assoc($posts_featured_query)['count'];

?>