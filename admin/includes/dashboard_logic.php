<?php
// -------------------------------------------------------------------------
// includes/dashboard_logic.php
// Ce fichier gère toutes les requêtes SQL et les actions (GET/POST)
// -------------------------------------------------------------------------

// Initialisation des variables pour éviter les erreurs "Undefined variable" dans la vue
$count_posts_published = 0;
$count_posts_drafts = 0;
$count_comments_pending = 0;
$count_posts_pending = 0;
$count_messages_unread = 0;
$count_total_users = 0;
$count_messages_total = 0;
$count_testi_total = 0;
$count_testi_pending = 0;
$count_polls_total = 0;
$count_slides_total = 0;
$count_faq_total = 0;
$count_quiz_total = 0;
$count_bans = 0;
$backup_count = 0;
$last_backup_date = 'Never';

// Variables pour les graphiques (Admin)
$chart_top_posts_labels_json = '[]';
$chart_top_posts_data_json = '[]';
$chart_months_labels_json = '[]';
$chart_months_data_json = '[]';
$chart_cat_labels_json = '[]';
$chart_cat_data_json = '[]';
$chart_authors_labels_json = '[]';
$chart_authors_data_json = '[]';

// Variables pour l'éditeur
$my_published = 0;
$my_pending = 0;
$my_views = 0;
$my_comments = 0;

// --- NOUVEAU : Variables Engagement ---
$total_likes = 0;
$total_favorites = 0;


// =========================================================================
// 1. TRAITEMENT DES ACTIONS (ADMIN)
// =========================================================================

if ($user['role'] == "Admin") {
    
    // --- MODÉRATION COMMENTAIRES ---
    if (isset($_GET['approve-comment'])) {
        validate_csrf_token_get();
        $comment_id = (int)$_GET['approve-comment'];
        $stmt = mysqli_prepare($connect, "UPDATE `comments` SET approved='Yes' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: dashboard.php'); exit;
    }
    
    if (isset($_GET['delete-comment'])) {
        validate_csrf_token_get();
        $comment_id = (int)$_GET['delete-comment'];
        $stmt = mysqli_prepare($connect, "DELETE FROM `comments` WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: dashboard.php'); exit;
    }

    // --- MODÉRATION ARTICLES ---
    if (isset($_GET['approve-post'])) {
        validate_csrf_token_get();
        $post_id = (int)$_GET['approve-post'];
        $stmt = mysqli_prepare($connect, "UPDATE `posts` SET active='Yes' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: dashboard.php'); exit;
    }
    
    if (isset($_GET['reject-post'])) {
        validate_csrf_token_get();
        $post_id = (int)$_GET['reject-post'];
        
        // Suppressions en cascade
        $tables_cascade = ['comments', 'post_tags', 'post_likes', 'user_favorites'];
        foreach($tables_cascade as $table) {
            $stmt = mysqli_prepare($connect, "DELETE FROM `$table` WHERE post_id=?");
            mysqli_stmt_bind_param($stmt, "i", $post_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        $stmt = mysqli_prepare($connect, "DELETE FROM `posts` WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        header('Location: dashboard.php'); exit;
    }

    // --- MODÉRATION TÉMOIGNAGES ---
    if (isset($_GET['approve-testimonial'])) {
        validate_csrf_token_get();
        $stmt = mysqli_prepare($connect, "UPDATE `testimonials` SET active='Yes' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $_GET['approve-testimonial']);
        mysqli_stmt_execute($stmt);
        header('Location: dashboard.php'); exit;
    }
    if (isset($_GET['delete-testimonial'])) {
        validate_csrf_token_get();
        $stmt = mysqli_prepare($connect, "DELETE FROM `testimonials` WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $_GET['delete-testimonial']);
        mysqli_stmt_execute($stmt);
        header('Location: dashboard.php'); exit;
    }
}

// =========================================================================
// 2. RÉCUPÉRATION DES DONNÉES (ADMIN & GLOBAL)
// =========================================================================

// Stats globales simples
$query_posts_published = mysqli_query($connect, "SELECT COUNT(id) AS count FROM posts WHERE active='Yes'");
$count_posts_published = mysqli_fetch_assoc($query_posts_published)['count'];

$query_posts_drafts = mysqli_query($connect, "SELECT COUNT(id) AS count FROM posts WHERE active='Draft'");
$count_posts_drafts = mysqli_fetch_assoc($query_posts_drafts)['count'];
$count_drafts_pending = $count_posts_drafts;

$query_total_users = mysqli_query($connect, "SELECT COUNT(id) AS count FROM users");
$count_total_users = mysqli_fetch_assoc($query_total_users)['count'];

$query_messages_total = mysqli_query($connect, "SELECT COUNT(id) AS count FROM messages");
$count_messages_total = mysqli_fetch_assoc($query_messages_total)['count'];

// Widget "Contenu en un coup d'œil"
$query_pages_count = mysqli_query($connect, "SELECT COUNT(id) AS count FROM pages");
$count_pages = mysqli_fetch_assoc($query_pages_count)['count'];
$query_comments_total = mysqli_query($connect, "SELECT COUNT(id) AS count FROM comments");
$count_comments_total = mysqli_fetch_assoc($query_comments_total)['count'];
$query_categories_count = mysqli_query($connect, "SELECT COUNT(id) AS count FROM categories");
$count_categories = mysqli_fetch_assoc($query_categories_count)['count'];
$query_tags_count = mysqli_query($connect, "SELECT COUNT(id) AS count FROM tags");
$count_tags = mysqli_fetch_assoc($query_tags_count)['count'];

// --- DONNÉES SPÉCIFIQUES ADMIN ---
if ($user['role'] == "Admin") {
    
    // Compteurs pending/unread
    $query_comments_pending = mysqli_query($connect, "SELECT COUNT(id) AS count FROM comments WHERE approved='No'");
    $count_comments_pending = mysqli_fetch_assoc($query_comments_pending)['count'];

    $stmt_posts_pending = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM posts WHERE active='Pending'");
    mysqli_stmt_execute($stmt_posts_pending);
    $result_posts_pending = mysqli_stmt_get_result($stmt_posts_pending);
    $count_posts_pending = mysqli_fetch_assoc($result_posts_pending)['count'];
    mysqli_stmt_close($stmt_posts_pending);

    $query_messages_unread = mysqli_query($connect, "SELECT COUNT(id) AS count FROM messages WHERE viewed = 'No'");
    $count_messages_unread = mysqli_fetch_assoc($query_messages_unread)['count'];

    // Listes pour l'affichage (Boucles HTML)
    $query_latest_users = mysqli_query($connect, "SELECT id, username, avatar, bio, email, role, location FROM users ORDER BY id DESC LIMIT 5");
    
    // --- CORRECTION : AJOUT DE LA VARIABLE MANQUANTE ---
    $q_latest_proj = mysqli_query($connect, "SELECT * FROM projects ORDER BY id DESC LIMIT 5");
    // --------------------------------------------------
    
    $query_pending_posts = mysqli_query($connect, "SELECT p.*, u.username AS author_name, u.avatar AS author_avatar FROM `posts` p LEFT JOIN `users` u ON p.author_id = u.id WHERE p.active = 'Pending' ORDER BY p.created_at DESC LIMIT 5");
    $posts_pending_count = mysqli_num_rows($query_pending_posts);

    $query_pending_comments_list = mysqli_query($connect, "SELECT c.*, p.title AS post_title, p.slug AS post_slug, u.username AS user_username, u.avatar AS user_avatar FROM `comments` c JOIN `posts` p ON c.post_id = p.id LEFT JOIN `users` u ON c.user_id = u.id AND c.guest = 'No' WHERE c.approved = 'No' ORDER BY c.id DESC LIMIT 10");
    $cmnts_pending = mysqli_num_rows($query_pending_comments_list);

    $query_pending_testimonials_list = mysqli_query($connect, "SELECT * FROM testimonials WHERE active='Pending' ORDER BY id DESC LIMIT 10");

    // Compteurs Modules (Témoignages, etc.)
    $q_testi = mysqli_query($connect, "SELECT active, COUNT(id) as count FROM testimonials GROUP BY active");
    while ($r_testi = mysqli_fetch_assoc($q_testi)) {
        if ($r_testi['active'] == 'Pending') $count_testi_pending = $r_testi['count'];
        $count_testi_total += $r_testi['count'];
    }
    
    $count_polls_total = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id) as count FROM polls"))['count'];
    $count_slides_total = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id) as count FROM slides"))['count'];
    $count_faq_total = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id) as count FROM faqs"))['count'];
    $count_quiz_total = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id) as count FROM quizzes"))['count'];
    
    $q_bans = mysqli_query($connect, "SELECT COUNT(id) as count FROM bans WHERE active = 'Yes'");
    if ($q_bans) { $count_bans = mysqli_fetch_assoc($q_bans)['count']; }

    // Backups
    $backup_dir = '../backup-database/';
    $backup_files = glob($backup_dir . "*.sql");
    $backup_count = ($backup_files) ? count($backup_files) : 0;
    if ($backup_count > 0) {
        usort($backup_files, function($a, $b) { return filemtime($b) - filemtime($a); });
        $last_backup_date = date("d M Y, H:i", filemtime($backup_files[0]));
    }

    // --- Compteur Projets (Déjà présent dans votre version) ---
    $q_projects = mysqli_query($connect, "SELECT COUNT(id) as count FROM projects");
    $count_projects = mysqli_fetch_assoc($q_projects)['count'];

    // --- STATISTIQUES ENGAGEMENT (AMÉLIORÉ) ---
    
    // 1. Likes (Articles + Projets)
    $l_posts = 0;
    $q_lp = mysqli_query($connect, "SELECT COUNT(id) as c FROM post_likes");
    if($q_lp) { $l_posts = mysqli_fetch_assoc($q_lp)['c']; }
    
    $l_projs = 0;
    $q_check_pl = mysqli_query($connect, "SHOW TABLES LIKE 'project_likes'");
    if(mysqli_num_rows($q_check_pl) > 0) {
        $q_lpj = mysqli_query($connect, "SELECT COUNT(id) as c FROM project_likes");
        if($q_lpj) { $l_projs = mysqli_fetch_assoc($q_lpj)['c']; }
    }
    $total_likes = $l_posts + $l_projs;
    
    // Calcul du pourcentage (Part des Articles sur le total)
    $like_percent = ($total_likes > 0) ? round(($l_posts / $total_likes) * 100) : 0;


    // 2. Favoris (Articles + Projets)
    $f_posts = 0;
    $q_fp = mysqli_query($connect, "SELECT COUNT(id) as c FROM user_favorites");
    if($q_fp) { $f_posts = mysqli_fetch_assoc($q_fp)['c']; }
    
    $f_projs = 0;
    $q_check_pf = mysqli_query($connect, "SHOW TABLES LIKE 'user_project_favorites'");
    if(mysqli_num_rows($q_check_pf) > 0) {
        $q_fpj = mysqli_query($connect, "SELECT COUNT(id) as c FROM user_project_favorites");
        if($q_fpj) { $f_projs = mysqli_fetch_assoc($q_fpj)['c']; }
    }
    $total_favorites = $f_posts + $f_projs;
    
    // Calcul du pourcentage (Part des Articles sur le total)
    $fav_percent = ($total_favorites > 0) ? round(($f_posts / $total_favorites) * 100) : 0;
    // ------------------------------------------

    // --- GRAPHIQUES ADMIN (Données) ---
    $chart_top_posts_titles = []; $chart_top_posts_views = [];
    $chart_months_labels = []; $chart_months_data = [];
    $chart_cat_labels = []; $chart_cat_data = [];
    $chart_authors_labels = []; $chart_authors_data = [];

    // Top 5 Posts
    $query_top_posts = mysqli_query($connect, "SELECT title, views FROM posts WHERE active='Yes' AND views > 0 ORDER BY views DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($query_top_posts)) {
        $chart_top_posts_titles[] = short_text($row['title'], 30); 
        $chart_top_posts_views[] = $row['views'];
    }
    // Posts per Month
    $query_posts_per_month = mysqli_query($connect, "SELECT DATE_FORMAT(publish_at, '%Y-%m') AS post_month, COUNT(id) AS post_count FROM posts WHERE publish_at > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND active = 'Yes' GROUP BY post_month ORDER BY post_month ASC LIMIT 12");
    while ($row_month = mysqli_fetch_assoc($query_posts_per_month)) {
        $chart_months_labels[] = $row_month['post_month'];
        $chart_months_data[] = $row_month['post_count'];
    }
    // Categories
    $query_cats = mysqli_query($connect, "SELECT c.category, COUNT(p.id) AS post_count FROM categories c LEFT JOIN posts p ON c.id = p.category_id AND p.active = 'Yes' GROUP BY c.id HAVING post_count > 0 ORDER BY post_count DESC");
    while ($row_cat = mysqli_fetch_assoc($query_cats)) {
        $chart_cat_labels[] = $row_cat['category'];
        $chart_cat_data[] = $row_cat['post_count'];
    }
    // Top Authors
    $query_top_authors = mysqli_query($connect, "SELECT u.username, COUNT(p.id) AS post_count FROM posts p JOIN users u ON p.author_id = u.id WHERE p.active = 'Yes' AND p.publish_at <= NOW() GROUP BY p.author_id ORDER BY post_count DESC LIMIT 5");
    while ($row_author = mysqli_fetch_assoc($query_top_authors)) {
        $chart_authors_labels[] = $row_author['username'];
        $chart_authors_data[] = $row_author['post_count'];
    }

    // Encodage JSON
    $chart_top_posts_labels_json = json_encode($chart_top_posts_titles);
    $chart_top_posts_data_json = json_encode($chart_top_posts_views);
    $chart_months_labels_json = json_encode($chart_months_labels);
    $chart_months_data_json = json_encode($chart_months_data);
    $chart_cat_labels_json = json_encode($chart_cat_labels);
    $chart_cat_data_json = json_encode($chart_cat_data);
    $chart_authors_labels_json = json_encode($chart_authors_labels);
    $chart_authors_data_json = json_encode($chart_authors_data);

    // --- STATISTIQUES PROJETS (GRAPHIQUES) ---
    $chart_top_proj_titles = []; $chart_top_proj_views = [];
    $chart_pcat_labels = []; $chart_pcat_data = [];

    // 1. Top 5 Projets
    $q_top_proj = mysqli_query($connect, "SELECT title, views FROM projects WHERE active='Yes' AND views > 0 ORDER BY views DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($q_top_proj)) {
        $chart_top_proj_titles[] = short_text($row['title'], 20); 
        $chart_top_proj_views[] = $row['views'];
    }

    // 2. Projets par Catégorie
    $q_pcat = mysqli_query($connect, "SELECT c.category, COUNT(p.id) AS count FROM project_categories c JOIN projects p ON c.id = p.project_category_id WHERE p.active = 'Yes' GROUP BY c.id HAVING count > 0");
    while ($row = mysqli_fetch_assoc($q_pcat)) {
        $chart_pcat_labels[] = $row['category'];
        $chart_pcat_data[] = $row['count'];
    }

    // Encodage JSON Projets
    $chart_top_proj_labels_json = json_encode($chart_top_proj_titles);
    $chart_top_proj_data_json   = json_encode($chart_top_proj_views);
    $chart_pcat_labels_json     = json_encode($chart_pcat_labels);
    $chart_pcat_data_json       = json_encode($chart_pcat_data);
}

// =========================================================================
// 3. DONNÉES SPÉCIFIQUES (ÉDITEUR)
// =========================================================================

if ($user['role'] == "Editor") {
    $my_id = $user['id'];

    $stmt_my_pub = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM posts WHERE author_id = ? AND active = 'Yes'");
    mysqli_stmt_bind_param($stmt_my_pub, "i", $my_id);
    mysqli_stmt_execute($stmt_my_pub);
    $my_published = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_my_pub))['count'];
    mysqli_stmt_close($stmt_my_pub);

    $stmt_my_pend = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM posts WHERE author_id = ? AND active = 'Pending'");
    mysqli_stmt_bind_param($stmt_my_pend, "i", $my_id);
    mysqli_stmt_execute($stmt_my_pend);
    $my_pending = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_my_pend))['count'];
    mysqli_stmt_close($stmt_my_pend);

    $stmt_my_views = mysqli_prepare($connect, "SELECT SUM(views) as total_views FROM posts WHERE author_id = ?");
    mysqli_stmt_bind_param($stmt_my_views, "i", $my_id);
    mysqli_stmt_execute($stmt_my_views);
    $data_views = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_my_views));
    $my_views = $data_views['total_views'] ?? 0;
    mysqli_stmt_close($stmt_my_views);

    $stmt_my_comms = mysqli_prepare($connect, "SELECT COUNT(c.id) as count FROM comments c JOIN posts p ON c.post_id = p.id WHERE p.author_id = ?");
    mysqli_stmt_bind_param($stmt_my_comms, "i", $my_id);
    mysqli_stmt_execute($stmt_my_comms);
    $my_comments = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_my_comms))['count'];
    mysqli_stmt_close($stmt_my_comms);
}

// =========================================================================
// 4. ANALYTICS (V3.4.5 - TRAFIC RÉEL)
// =========================================================================

if ($user['role'] == "Admin") {
    
    // A. Visites des 7 derniers jours (Graphique Ligne)
    $analytics_visits_labels = [];
    $analytics_visits_data = [];
    
    // On génère les 7 derniers jours (y compris aujourd'hui)
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $analytics_visits_labels[] = date('d M', strtotime($date));
        
        // Requête comptage par jour
        $stmt_vis = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM visitor_analytics WHERE DATE(visit_date) = ?");
        mysqli_stmt_bind_param($stmt_vis, "s", $date);
        mysqli_stmt_execute($stmt_vis);
        $res_vis = mysqli_stmt_get_result($stmt_vis);
        $analytics_visits_data[] = mysqli_fetch_assoc($res_vis)['count'];
        mysqli_stmt_close($stmt_vis);
    }

    // B. Top Pages Vues (Graphique Barre Horizontale)
    $analytics_pages_labels = [];
    $analytics_pages_data = [];
    
    $q_top_pages = mysqli_query($connect, "SELECT page_url, COUNT(id) as count FROM visitor_analytics GROUP BY page_url ORDER BY count DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($q_top_pages)) {
        // Nettoyage URL pour affichage (ex: /post?name=abc -> abc)
        $clean_url = str_replace(['/post?name=', '/project?name=', '/'], ['', '', ''], $row['page_url']);
        if(empty($clean_url)) $clean_url = 'Home';
        
        $analytics_pages_labels[] = short_text($clean_url, 20);
        $analytics_pages_data[] = $row['count'];
    }

    // C. Top Référents (Graphique Pie)
    $analytics_ref_labels = [];
    $analytics_ref_data = [];
    
    $q_ref = mysqli_query($connect, "SELECT referrer, COUNT(id) as count FROM visitor_analytics GROUP BY referrer ORDER BY count DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($q_ref)) {
        $ref = $row['referrer'];
        if ($ref == 'Direct' || empty($ref)) $ref = 'Direct / Bookmark';
        elseif (strpos($ref, 'google') !== false) $ref = 'Google';
        elseif (strpos($ref, 'facebook') !== false) $ref = 'Facebook';
        elseif (strpos($ref, 'twitter') !== false || strpos($ref, 't.co') !== false) $ref = 'Twitter';
        else {
            // Extraire juste le domaine
            $parsed = parse_url($ref);
            $ref = isset($parsed['host']) ? $parsed['host'] : 'Other';
        }
        
        $analytics_ref_labels[] = $ref;
        $analytics_ref_data[] = $row['count'];
    }

    // Encodage JSON pour JS
    $json_visits_labels = json_encode($analytics_visits_labels);
    $json_visits_data = json_encode($analytics_visits_data);
    $json_pages_labels = json_encode($analytics_pages_labels);
    $json_pages_data = json_encode($analytics_pages_data);
    $json_ref_labels = json_encode($analytics_ref_labels);
    $json_ref_data = json_encode($analytics_ref_data);
}
?>