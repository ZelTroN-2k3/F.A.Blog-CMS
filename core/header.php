<?php
function head()
{
    // Rendre $connect, $logged, $rowu, $settings accessibles
    // AJOUT DES VARIABLES GLOBALES DE THÈME
    global $connect, $logged, $rowu, $settings, $light_theme_url, $dark_theme_url;
    
    // --- DÉBUT DE LA LOGIQUE DE TITRE ET DESCRIPTION ---
    global $current_page, $pagetitle, $description;

    $display_title = '';
    $display_description = '';
    
    if ($current_page == 'index.php') {
        // Page d'accueil : utiliser le titre SEO global et la description globale
        $display_title = $settings['meta_title'];
        $display_description = $settings['description'];
    } else {
        // Autres pages : utiliser le titre et la description spécifiques à la page
        // S'assurer que les variables existent pour éviter les erreurs
        $display_title = (isset($pagetitle) ? $pagetitle : 'Page') . ' - ' . $settings['sitename'];
        $display_description = isset($description) ? $description : $settings['description'];
    }
    
    // Construction de l'URL canonique (BEAUCOUP mieux pour le SEO)
    $current_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    // --- FIN DE LA LOGIQUE ---
?>
<!DOCTYPE html>
<html lang="en">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script>
        (function() {
            // Ces variables sont maintenant globales et seront correctement "echo"
            const lightThemeUrl = '<?php echo $light_theme_url; ?>';
            const darkThemeUrl = '<?php echo $dark_theme_url; ?>';
            let currentTheme = localStorage.getItem('theme');

            // Si aucune préférence n'est sauvegardée, vérifier la préférence du système
            if (!currentTheme) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    currentTheme = 'dark';
                } else {
                    currentTheme = 'light';
                }
            }

            // Appliquer le thème en écrivant la balise <link> appropriée
            const themeUrl = (currentTheme === 'dark') ? darkThemeUrl : lightThemeUrl;
            document.write('<link id="theme-link" rel="stylesheet" href="' + themeUrl + '">');
            
            // Sauvegarder le choix (surtout si c'était la détection auto)
            localStorage.setItem('theme', currentTheme);
        })();
    </script>
    <?php
	$current_page = basename($_SERVER['SCRIPT_NAME']);
    $pagetitle   = '';
    $description = '';

    // SEO Titles, Descriptions and Sharing Tags
    if ($current_page == 'contact.php') {
        $pagetitle   = 'Contact';
		$description = 'If you have any questions do not hestitate to send us a message.';
		
    } else if ($current_page == 'gallery.php') {
        $pagetitle   = 'Gallery';
		$description = 'View all images from the Gallery.';
		
    } else if ($current_page == 'blog.php') {
        $pagetitle   = 'Blog';
		$description = 'View all blog posts.';
        
    } else if ($current_page == 'profile.php') {
        $pagetitle   = 'Profile';
		$description = 'Manage your account settings.';
		
    } else if ($current_page == 'my-comments.php') {
        $pagetitle   = 'My Comments';
		$description = 'Manage your comments.';
		
    } else if ($current_page == 'my-favorites.php') {
        $pagetitle   = 'My Favorites';
		$description = 'Manage your favorite posts.';
		
    } else if ($current_page == 'author.php') {
        $pagetitle   = 'Author Profile';
		$description = 'View author profile.';
		
    } else if ($current_page == 'edit-comment.php') {
        $pagetitle   = 'Edit Comment';
		$description = 'Edit your comment.';
		
    } else if ($current_page == 'login.php') {
        $pagetitle   = 'Sign In';
		$description = 'Login into your account.';
		
    } else if ($current_page == 'unsubscribe.php') {
        $pagetitle   = 'Unsubscribe';
		$description = 'Unsubscribe from Newsletter.';
		
    } else if ($current_page == 'error404.php') {
        $pagetitle   = 'Error 404';
		$description = 'Page is not found.';
		
    } else if ($current_page == 'search.php') {
		
		if (!isset($_GET['q'])) {
			echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
		}
		
		$word        = $_GET['q']; // Déjà filtré par FILTER_SANITIZE_SPECIAL_CHARS au début
        $pagetitle   = 'Search';
		$description = 'Search results for ' . $word . '.';
		
    } else if ($current_page == 'post.php') {
        $slug = $_GET['name'] ?? ''; // Utiliser l'opérateur Null Coalescing
        
        if (empty($slug)) {
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        
        // --- MODIFICATION ICI : Ajout de meta_title et meta_description dans le SELECT ---
        $stmt_post_seo = mysqli_prepare($connect, "SELECT title, slug, image, content, meta_title, meta_description FROM `posts` WHERE slug=?");
        mysqli_stmt_bind_param($stmt_post_seo, "s", $slug);
        mysqli_stmt_execute($stmt_post_seo);
        $runpt = mysqli_stmt_get_result($stmt_post_seo);
        
        if (mysqli_num_rows($runpt) == 0) {
            mysqli_stmt_close($stmt_post_seo);
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        $rowpt = mysqli_fetch_assoc($runpt);
        mysqli_stmt_close($stmt_post_seo);
        
        // --- LOGIQUE SEO AVANCÉE ---
        
        // 1. Titre : Priorité au meta_title personnalisé, sinon titre de l'article
        if (!empty($rowpt['meta_title'])) {
            $pagetitle = $rowpt['meta_title'];
        } else {
            $pagetitle = $rowpt['title'];
        }

        // 2. Description : Priorité à la meta_description personnalisée, sinon extrait automatique
        if (!empty($rowpt['meta_description'])) {
            $description = $rowpt['meta_description'];
        } else {
            $description = short_text(strip_tags(html_entity_decode($rowpt['content'])), 150);
        }
        
        // Utiliser htmlspecialchars pour la sécurité dans les balises meta
        // Note: Pour OG (Open Graph / Facebook), on garde souvent le titre original et l'image
		echo '
		<meta property="og:title" content="' . htmlspecialchars($pagetitle) . '" />
		<meta property="og:description" content="' . htmlspecialchars($description) . '" />
		<meta property="og:image" content="' . htmlspecialchars($rowpt['image']) . '" />
		<meta property="og:type" content="article"/>
		<meta property="og:url" content="' . htmlspecialchars($settings['site_url'] . '/post?name=' . $rowpt['slug']) . '" />
		<meta name="twitter:card" content="summary_large_image"></meta>
		<meta name="twitter:title" content="' . htmlspecialchars($pagetitle) . '" />
		<meta name="twitter:description" content="' . htmlspecialchars($description) . '" />
		<meta name="twitter:image" content="' . htmlspecialchars($rowpt['image']) . '" />
		<meta name="twitter:url" content="' . htmlspecialchars($settings['site_url'] . '/post?name=' . $rowpt['slug']) . '" />
		';
		
    } else if ($current_page == 'page.php') {
        $slug = $_GET['name'] ?? '';
        
        if (empty($slug)) {
            echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
            exit;
        }
        
        // Requête préparée
        $stmt_page_seo = mysqli_prepare($connect, "SELECT title, content FROM `pages` WHERE slug=?");
        mysqli_stmt_bind_param($stmt_page_seo, "s", $slug);
        mysqli_stmt_execute($stmt_page_seo);
        $runpp = mysqli_stmt_get_result($stmt_page_seo);
        
        if (mysqli_num_rows($runpp) == 0) {
            mysqli_stmt_close($stmt_page_seo);
            echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
            exit;
        }
        $rowpp = mysqli_fetch_assoc($runpp);
        mysqli_stmt_close($stmt_page_seo);
        
        $pagetitle   = $rowpp['title'];
		$description = short_text(strip_tags(html_entity_decode($rowpp['content'])), 150);
		
    } else if ($current_page == 'category.php') {
        $slug = $_GET['name'] ?? '';
        
        if (empty($slug)) {
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        
        // Requête préparée
        $stmt_cat_seo = mysqli_prepare($connect, "SELECT category FROM `categories` WHERE slug=?");
        mysqli_stmt_bind_param($stmt_cat_seo, "s", $slug);
        mysqli_stmt_execute($stmt_cat_seo);
        $runct = mysqli_stmt_get_result($stmt_cat_seo);
        
        if (mysqli_num_rows($runct) == 0) {
            mysqli_stmt_close($stmt_cat_seo);
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        $rowct = mysqli_fetch_assoc($runct);
        mysqli_stmt_close($stmt_cat_seo);
        
        $pagetitle   = $rowct['category'];
		$description = 'View all blog posts from ' . $rowct['category'] . ' category.';
    
    } else if ($current_page == 'tag.php') {
        $slug = $_GET['name'] ?? '';
        
        if (empty($slug)) {
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        
        // Requête préparée
        $stmt_tag_seo = mysqli_prepare($connect, "SELECT name FROM `tags` WHERE slug=?");
        mysqli_stmt_bind_param($stmt_tag_seo, "s", $slug);
        mysqli_stmt_execute($stmt_tag_seo);
        $runtag = mysqli_stmt_get_result($stmt_tag_seo);
        
        if (mysqli_num_rows($runtag) == 0) {
            mysqli_stmt_close($stmt_tag_seo);
            echo '<meta http-equiv="refresh" content="0; url=blog">';
            exit;
        }
        $rowtag = mysqli_fetch_assoc($runtag);
        mysqli_stmt_close($stmt_tag_seo);
        
        $pagetitle   = 'Articles tagged: ' . $rowtag['name'];
		$description = 'See all articles tagged ' . $rowtag['name'];

    } else if ($current_page == 'project.php') {
        $slug = $_GET['name'] ?? '';
        if (!empty($slug)) {
            $stmt = mysqli_prepare($connect, "SELECT title, pitch, image FROM projects WHERE slug=?");
            mysqli_stmt_bind_param($stmt, "s", $slug);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $pagetitle = $row['title'];
                $description = short_text($row['pitch'], 150);
                // Vous pouvez aussi ajouter les balises OG:Image ici avec $row['image']
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Utiliser htmlspecialchars pour le titre et la description
    if ($current_page == 'index.php') {
        echo '
		<title>' . htmlspecialchars($settings['sitename']) . '</title>
		<meta name="description" content="' . htmlspecialchars($settings['description']) . '" />';
    } else {
        echo '
		<title>' . htmlspecialchars($pagetitle) . ' - ' . htmlspecialchars($settings['sitename']) . '</title>
		<meta name="description" content="' . htmlspecialchars($description) . '" />';
    }
?>

        <title><?php echo htmlspecialchars($display_title); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($display_description); ?>" />
        
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
        
        <meta property="og:title" content="<?php echo htmlspecialchars($display_title); ?>" />
        <meta property="og:description" content="<?php echo htmlspecialchars($display_description); ?>" />
        <meta property="og:site_name" content="<?php echo htmlspecialchars($settings['sitename']); ?>" />
        <meta property="og:type" content="website" />
        
        <meta property="og:url" content="<?php echo htmlspecialchars($current_page_url); ?>" />
        <link rel="canonical" href="<?php echo htmlspecialchars($current_page_url); ?>" />

<?php 
    $fav_url = $settings['favicon_url'];
    if (strpos($fav_url, 'http') === false) {
        // Si c'est un chemin relatif, on ajoute l'URL du site
        $fav_url = $settings['site_url'] . '/' . $fav_url;
    }
?>
        <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($fav_url); ?>" />
        <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($settings['apple_touch_icon_url']); ?>" />

        <meta name="author" content="<?php echo htmlspecialchars($settings['meta_author']); ?>" />
        <meta name="generator" content="<?php echo htmlspecialchars($settings['meta_generator']); ?>" />
        <meta name="robots" content="<?php echo htmlspecialchars($settings['meta_robots']); ?>" />
        
        <link rel="stylesheet" id="theme-light" href="<?php echo htmlspecialchars($light_theme_url); ?>">
        <link rel="stylesheet" id="theme-dark" href="<?php echo htmlspecialchars($dark_theme_url); ?>" disabled>
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?php echo htmlspecialchars($settings['site_url']); ?>/assets/css/phpblog.css?v=<?php echo time(); ?>">


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" type="text/css" rel="stylesheet"/>
		<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
		<script src="<?php echo htmlspecialchars($settings['site_url']); ?>/assets/js/phpblog.js"></script>
<?php
if ($current_page == 'post.php') {
?>
        <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.5.0/jssocials.css" />
        <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.5.0/jssocials-theme-classic.css" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.5.0/jssocials.min.js"></script>
<?php
}
?>
<?php
if ($current_page == 'post.php' || $current_page == 'tag.php') { // MODIF : Ajout de tag.php
?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/css.min.js"></script>
        <script>
            // Initialise la coloration après le chargement de la page
            document.addEventListener('DOMContentLoaded', (event) => {
                hljs.highlightAll();
            });
        </script>
        <?php
}
?>
	
        <style>
<?php
if($settings['background_image'] != "") {
    // Échapper l'URL pour la sécurité dans le CSS
    echo 'body {
        background: url("' . htmlspecialchars($settings['background_image']) . '") no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }';
}
?>
/* --- CSS MEGA MENU RESPONSIVE --- */

/* 1. Par défaut (Mobile) : Le menu prend 100% de la largeur et s'empile */
.mega-menu-custom {
    width: 100%;
    border: none;
    box-shadow: none;
    margin-top: 0;
    padding: 0;
}

/* 2. Sur PC (Écrans > 992px) : On applique le style "Mega Menu Centré" */
@media (min-width: 992px) {
    .nav-item.dropdown {
        position: relative; /* Le parent redevient la référence */
    }
    
    .mega-menu-custom {
        position: absolute;
        min-width: 900px; /* Largeur fixe pour PC */
        left: 50%;
        transform: translateX(-30%); /* Centrage parfait */
        border-top: 3px solid #007bff;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); /* Ombre uniquement sur PC */
        border-radius: 0.25rem;
        padding: 1rem 0;
    }
}
        </style>
        
<?php
    // Code personnalisé de l'admin (Google Analytics, etc.)
    // SÉCURITÉ RGPD : On n'affiche le code QUE si l'utilisateur a accepté les cookies
    if (isset($_COOKIE['cookieConsentAccepted']) && $_COOKIE['cookieConsentAccepted'] == 'true') {
        
        if ($settings['head_customcode_enabled'] == 'On' && !empty($settings['head_customcode'])) {
            echo base64_decode($settings['head_customcode']);
        }
        
    }
?>

<?php if ($settings['event_effect'] == 'Grayscale'): ?>
    <style>
        html { filter: grayscale(100%); }
        /* On garde les images produits/projets en couleur au survol si on veut */
        img:hover { filter: grayscale(0%); transition: filter 0.3s; }
    </style>
    <?php endif; ?>

    <?php if ($settings['event_effect'] == 'Snow'): ?>
    <style>
        /* Simple CSS Snow Effect */
        .snowflake { color: #fff; font-size: 1em; font-family: Arial; text-shadow: 0 0 1px #000; position: fixed; top: -10%; z-index: 9999; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; cursor: default; animation-name: snowflakes-fall,snowflakes-shake; animation-duration: 10s,3s; animation-timing-function: linear,ease-in-out; animation-iteration-count: infinite,infinite; }
        @keyframes snowflakes-fall { 0% { top: -10% } 100% { top: 100% } }
        @keyframes snowflakes-shake { 0% { transform: translateX(0px) } 50% { transform: translateX(80px) } 100% { transform: translateX(0px) } }
        .snowflake:nth-of-type(0) { left: 1%; animation-delay: 0s,0s }
        .snowflake:nth-of-type(1) { left: 10%; animation-delay: 1s,1s }
        .snowflake:nth-of-type(2) { left: 20%; animation-delay: 6s,.5s }
        .snowflake:nth-of-type(3) { left: 30%; animation-delay: 4s,2s }
        .snowflake:nth-of-type(4) { left: 40%; animation-delay: 2s,2s }
        .snowflake:nth-of-type(5) { left: 50%; animation-delay: 8s,3s }
        .snowflake:nth-of-type(6) { left: 60%; animation-delay: 6s,2s }
        .snowflake:nth-of-type(7) { left: 70%; animation-delay: 2.5s,1s }
        .snowflake:nth-of-type(8) { left: 80%; animation-delay: 1s,0s }
        .snowflake:nth-of-type(9) { left: 90%; animation-delay: 3s,1.5s }
    </style>
    <?php endif; ?>

<?php
    // 1. Chargement de la Police Google
    if (!empty($settings['design_font'])) {
        echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . urlencode($settings['design_font']) . ':wght@300;400;600;700&display=swap">';
    }
    
    // 2. Application des Variables CSS
    $font_family = !empty($settings['design_font']) ? "'" . $settings['design_font'] . "', sans-serif" : "sans-serif";
    $col_primary = !empty($settings['design_color_primary']) ? $settings['design_color_primary'] : '#0d6efd';
    $col_secondary = !empty($settings['design_color_secondary']) ? $settings['design_color_secondary'] : '#6c757d';
    ?>
    
    <style>
        :root {
            /* Surcharge des variables Bootstrap 5 */
            --bs-primary: <?php echo $col_primary; ?>;
            --bs-secondary: <?php echo $col_secondary; ?>;
            
            /* Pour les liens et boutons outline */
            --bs-link-color: <?php echo $col_primary; ?>;
            --bs-btn-primary-bg: <?php echo $col_primary; ?>;
            --bs-btn-primary-border-color: <?php echo $col_primary; ?>;
        }
        
        body {
            font-family: <?php echo $font_family; ?>;
        }
        
        /* Forçage spécifique pour certains éléments récalcitrants */
        .text-primary { color: <?php echo $col_primary; ?> !important; }
        .bg-primary { background-color: <?php echo $col_primary; ?> !important; }
        .btn-primary { background-color: <?php echo $col_primary; ?> !important; border-color: <?php echo $col_primary; ?> !important; }
        .btn-outline-primary { color: <?php echo $col_primary; ?> !important; border-color: <?php echo $col_primary; ?> !important; }
        .btn-outline-primary:hover { background-color: <?php echo $col_primary; ?> !important; color: #fff !important; }
        
        /* Custom CSS Admin */
        <?php echo $settings['design_custom_css']; ?>
    </style>
    
</head>

<body <?php 
if ($settings['rtl'] == "Yes") {
	echo 'dir="rtl"';
}
?>>

<?php 
// --- BANNIÈRE ÉVÉNEMENTIELLE ---
// 1. On initialise le purificateur s'il n'existe pas encore
if (!isset($purifier)) {
    $purifier = get_purifier();
}

// 2. On vérifie si l'option existe et est activée (avec sécurité isset)
if (isset($settings['event_banner_active']) && $settings['event_banner_active'] == 'Yes'): 
?>
    <div style="background-color: <?php echo htmlspecialchars($settings['event_banner_color']); ?>; color: #fff; text-align: center; padding: 10px; font-size: 1.1rem; position: relative; z-index: 10000;">
        <div class="container">
            <?php echo $purifier->purify(html_entity_decode($settings['event_banner_content'])); ?>
        </div>
    </div>
<?php endif; ?>

<!-- NAVIGATION ADMIN -->
<?php
if ($logged == 'Yes' && ($rowu['role'] == 'Admin' || $rowu['role'] == 'Editor')) {
    
    // Calcul des messages non lus (Admin uniquement)
    $unread_messages = 0;
    if ($rowu['role'] == 'Admin') {
        $msgcount_query  = mysqli_query($connect, "SELECT id FROM messages WHERE viewed = 'No'");
        $unread_messages = mysqli_num_rows($msgcount_query);
    }
    
    // Logique Maintenance
    $maintenance_status = $settings['maintenance_mode'] ?? 'Off';
    $status_icon = ($maintenance_status == 'On') ? 'text-danger' : 'text-success';
    $status_text = ($maintenance_status == 'On') ? 'Maintenance ON' : 'Maintenance OFF';
?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($settings['site_url']); ?>/admin/css/admin-sidebar.css">
    
    <style>
        .admin-header[data-bs-toggle="collapse"] { cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .admin-header[data-bs-toggle="collapse"]::after { content: '\f107'; font-family: "Font Awesome 5 Free"; font-weight: 900; transition: transform 0.3s ease; }
        .admin-header[data-bs-toggle="collapse"].collapsed::after { transform: rotate(-90deg); }
    </style>

    <div id="admin-floating-sidebar">
        
        <div class="admin-sidebar-content">
            
            <div class="text-center text-white mb-3 pb-2 border-bottom border-secondary">
                <?php
                // Gestion Avatar Sidebar Admin (Correction Chemins Absolus)
                $sb_avatar = $settings['site_url'] . '/assets/img/avatar.png'; // Par défaut (Absolu)
                
                if (!empty($rowu['avatar'])) {
                    $clean_path = str_replace('../', '', $rowu['avatar']);
                    if (strpos($clean_path, 'http') === 0) {
                        $sb_avatar = $clean_path; // URL externe (Google)
                    } else {
                        $sb_avatar = $settings['site_url'] . '/' . $clean_path; // Image locale
                    }
                }
                ?>
                <img src="<?php echo htmlspecialchars($sb_avatar); ?>" class="rounded-circle mb-2" width="80" height="80" alt="Avatar" style="object-fit: cover;" onerror="this.src='<?php echo $settings['site_url']; ?>/assets/img/avatar.png';">                <h5 class="mb-0"><?php echo htmlspecialchars($rowu['username']); ?></h5>
                <span>
                    <?php echo ($rowu['role'] == 'Admin') ? '<span class="badge bg-success"><i class="fas fa-user-shield"></i> Admin</span>' : '<span class="badge bg-primary"><i class="fas fa-user-edit"></i> Editor</span>'; ?>
                </span>
            </div>

            <a class="admin-link text-white" href="<?php echo $settings['site_url']; ?>/admin/dashboard.php">
                <i class="fas fa-columns fa-fw me-2 text-primary"></i> Dashboard
            </a>

            <?php if ($rowu['role'] == 'Admin') { ?>
            <a class="admin-link text-white" href="<?php echo $settings['site_url']; ?>/admin/stats.php">
                <i class="fas fa-chart-line fa-fw me-2 text-warning"></i> Analytics
            </a>
            <?php } ?>

            <?php if ($rowu['role'] == 'Editor') { ?>
                <a class="admin-link text-warning" href="<?php echo $settings['site_url']; ?>/admin/edit_user.php?id=<?php echo $rowu['id']; ?>">
                    <i class="fas fa-user-circle fa-fw me-2"></i> My Profile
                </a>
            <?php } ?>

            <div class="admin-header" data-bs-toggle="collapse" data-bs-target="#collapseManage" aria-expanded="true">
                Manage
            </div>
            <!-- AJOUT DE LA CLASSE class="collapse show" POUR AVOIR LE MENU ouvert PAR DÉFAUT -->
            <div class="collapse" id="collapseManage">
                <?php if ($rowu['role'] == 'Admin') { ?>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/settings.php"><i class="fas fa-cogs fa-fw me-2"></i> Settings</a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/menu_editor.php"><i class="fas fa-bars fa-fw me-2"></i> Menu</a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/widgets.php"><i class="fas fa-th-large fa-fw me-2"></i> Widgets</a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/users.php"><i class="fas fa-users fa-fw me-2"></i> Users</a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/newsletter.php"><i class="fas fa-envelope-open-text fa-fw me-2"></i> Newsletter</a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/chats.php"><i class="fas fa-comments fa-fw me-2"></i> Chats</a>
                <?php } ?>
                
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/files.php"><i class="fas fa-folder-open fa-fw me-2"></i> Files</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/posts.php"><i class="fas fa-file-alt fa-fw me-2"></i> Posts</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/categorys.php"><i class="fas fa-list-alt fa-fw me-2"></i> Categories</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/gallery.php"><i class="fas fa-images fa-fw me-2"></i> Gallery</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/albums.php"><i class="fas fa-list-ol fa-fw me-2"></i> Albums</a>
                
                <?php if ($rowu['role'] == 'Admin') { ?>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/pages.php"><i class="fas fa-file fa-fw me-2"></i> Pages</a>
                <?php } ?>
            </div>

            <?php if ($rowu['role'] == 'Admin') { ?>
                <div class="admin-header mt-2" data-bs-toggle="collapse" data-bs-target="#collapseSystem" aria-expanded="true">
                    System
                </div>
                <!-- AJOUT DE LA CLASSE class="collapse show" POUR AVOIR LE MENU ouvert PAR DÉFAUT -->
                <div class="collapse" id="collapseSystem">
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/messages.php">
                        <i class="fas fa-envelope fa-fw me-2"></i> Messages
                        <?php if($unread_messages > 0): ?>
                            <span class="badge bg-danger admin-badge"><?php echo $unread_messages; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/system-information.php">
                        <i class="fas fa-server fa-fw me-2"></i> System Info
                    </a>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/logs.php">
                        <i class="fas fa-history fa-fw me-2"></i> Activity Logs
                    </a>
                </div>
            <?php } ?>
            
            <div class="admin-header mt-2" data-bs-toggle="collapse" data-bs-target="#collapseModeration" aria-expanded="true">
                Moderation
            </div>
            <!-- AJOUT DE LA CLASSE class="collapse show" POUR AVOIR LE MENU ouvert PAR DÉFAUT -->
            <div class="collapse" id="collapseModeration">
                 <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/comments.php"><i class="fas fa-comment-dots fa-fw me-2"></i> Comments</a>
            </div>

            <div class="admin-header mt-2" data-bs-toggle="collapse" data-bs-target="#collapseCreate" aria-expanded="true">
                Create New
            </div>
            <!-- AJOUT DE LA CLASSE class="collapse show" POUR AVOIR LE MENU ouvert PAR DÉFAUT -->
            <div class="collapse" id="collapseCreate">
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/add_post.php"><i class="fas fa-plus-circle fa-fw me-2 text-success"></i> Post</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/add_category.php"><i class="fas fa-plus fa-fw me-2"></i> Category</a>
                <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/add_image.php"><i class="fas fa-camera fa-fw me-2"></i> Image</a>
                
                <?php if ($rowu['role'] == 'Admin') { ?>
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/add_page.php"><i class="fas fa-plus-square fa-fw me-2"></i> Page</a>
                <?php } ?>
            </div>
            <?php if ($rowu['role'] == 'Admin') { ?>
                <div class="mt-3 pt-2 border-top border-secondary">
                    <a class="admin-link" href="<?php echo $settings['site_url']; ?>/admin/maintenance.php">
                        <i class="fas fa-circle <?php echo $status_icon; ?> fa-fw me-2"></i> <?php echo $status_text; ?>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="admin-sidebar-trigger">
            <i class="fas fa-cog fa-spin text-warning"></i>
        </div>

    </div>
<?php
}
?>
<!-- FIN NAVIGATION ADMIN -->

<!-- HEADER ET NAVIGATION PRINCIPALE -->
	<header class="py-3 border-bottom bg-primary">
		<div class="<?php
if ($settings['layout'] == 'Wide') {
	echo 'container-fluid';
} else {
	echo 'container';
}
?> d-flex flex-wrap justify-content-center">
            <a href="<?php echo htmlspecialchars($settings['site_url']); ?>" class="d-flex align-items-center text-white mb-3 mb-md-0 me-md-auto text-decoration-none">
                <?php if (!empty($settings['site_logo']) && file_exists($settings['site_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="<?php echo htmlspecialchars($settings['sitename']); ?>" height="44" style="max-width: 200px; object-fit: contain;">
                <?php else: ?>
                    <span class="fs-4"><b><i class="far fa-newspaper"></i> <?php echo htmlspecialchars($settings['sitename']); ?></b></span>
                <?php endif; ?>
            </a>
			
			<form class="col-12 col-lg-auto mb-3 mb-lg-0" action="<?php echo htmlspecialchars($settings['site_url']); ?>/search" method="GET">
				<div class="input-group">
					<input type="search" class="form-control" placeholder="Search" name="q" value="<?php
if (isset($_GET['q'])) {
    // Utiliser htmlspecialchars pour la valeur de l'input
	echo htmlspecialchars($_GET['q']);
}
?>" required />
					<span class="input-group-btn">
						<button class="btn btn-dark" type="submit"><i class="fa fa-search"></i></button>
					</span>
				</div>
			</form>
		</div>
	</header>

<!-- NAVIGATION PRINCIPALE -->	
	<nav class="navbar nav-underline navbar-expand-lg py-2 bg-light <?php echo ($settings['sticky_header'] == 'On' ? 'sticky-top shadow-sm' : 'border-bottom'); ?>">
		<div class="<?php
if ($settings['layout'] == 'Wide') {
	echo 'container-fluid';
} else {
	echo 'container';
}
?>">
			<button class="navbar-toggler mx-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span> Navigation
			</button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav me-auto">
<?php
// --- DÉBUT CACHE MENU ---
// On essaie de lire le cache 'main_menu' (valable 1 heure = 3600s)
// Assurez-vous que la fonction get_cache() existe dans core/functions.php
$menu_cache = false;
if(function_exists('get_cache')) {
    $menu_cache = get_cache('main_menu', 3600);
}

if ($menu_cache) {
    // HIT : On affiche le HTML stocké
    echo $menu_cache;
} else {
    // MISS : On génère le menu et on le stocke
    ob_start(); // Démarre la mémoire tampon

        // Requête simple sans variable externe
        $runq = mysqli_query($connect, "SELECT * FROM `menu` WHERE active = 'Yes' ORDER BY id ASC"); 
        
        // On prépare l'URL de base pour simplifier le code
        $base_url = htmlspecialchars($settings['site_url']); 

while ($row = mysqli_fetch_assoc($runq)) {

        // --- 1. MENU BLOG ---
        if ($row['path'] == 'blog') {
            echo '<li class="nav-item link-body-emphasis dropdown">
                    <a href="' . $base_url . '/blog" class="nav-link link-dark dropdown-toggle px-2';
            if ($current_page == 'blog.php' || $current_page == 'category.php' || $current_page == 'tag.php') { echo ' active'; }
            echo '" data-bs-toggle="dropdown">
                        <i class="' . htmlspecialchars($row['fa_icon']) . '"></i> ' . htmlspecialchars($row['page']) . ' 
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="' . $base_url . '/blog">View all posts</a></li>
                        <li><a class="dropdown-item" href="' . $base_url . '/categories">View all Categories</a></li>';
            
            $run2 = mysqli_query($connect, "SELECT * FROM `categories` ORDER BY category ASC");
            while ($row2 = mysqli_fetch_array($run2)) {
                echo '<li><a class="dropdown-item" href="' . $base_url . '/category?name=' . htmlspecialchars($row2['slug']) . '"><i class="fas fa-chevron-right"></i> ' . htmlspecialchars($row2['category']) . '</a></li>';
            }
            echo '</ul></li>';

        // --- 2. MENU PROJETS ---
        } else if ($row['path'] == 'projects') {
            echo '<li class="nav-item link-body-emphasis dropdown">
                    <a href="' . $base_url . '/projects" class="nav-link link-dark dropdown-toggle px-2';
            
            if ($current_page == 'projects.php' || $current_page == 'project.php') { echo ' active'; }
            
            echo '" data-bs-toggle="dropdown">
                        <i class="' . htmlspecialchars($row['fa_icon']) . '"></i> ' . htmlspecialchars($row['page']) . ' 
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="' . $base_url . '/projects"><i class="fas fa-th-large me-2"></i> View all projects</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">By Category</h6></li>';
                        
                        $q_p_cats = mysqli_query($connect, "SELECT category, slug FROM project_categories ORDER BY category ASC");
                        if(mysqli_num_rows($q_p_cats) > 0) {
                            while($pc = mysqli_fetch_assoc($q_p_cats)) {
                                echo '<li><a class="dropdown-item" href="' . $base_url . '/projects?category='.htmlspecialchars($pc['slug']).'"><i class="fas fa-angle-right me-2 text-muted"></i> '.htmlspecialchars($pc['category']).'</a></li>';
                            }
                        } else {
                            echo '<li><span class="dropdown-item text-muted small">No categories yet</span></li>';
                        }

            echo '      <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">By Difficulty</h6></li>
                        <li><a class="dropdown-item" href="' . $base_url . '/projects?difficulty=Easy"><span class="badge bg-success me-2">Easy</span> Beginner</a></li>
                        <li><a class="dropdown-item" href="' . $base_url . '/projects?difficulty=Intermediate"><span class="badge bg-primary me-2">Medium</span> Intermediate</a></li>
                        <li><a class="dropdown-item" href="' . $base_url . '/projects?difficulty=Advanced"><span class="badge bg-warning me-2">Hard</span> Advanced</a></li>
                        <li><a class="dropdown-item" href="' . $base_url . '/projects?difficulty=Expert"><span class="badge bg-danger me-2">Expert</span> Master</a></li>
                    </ul>
                </li>';
        
        // --- 3. MENU STANDARD ---
        } else {
            // On gère le cas où le chemin contient déjà http (lien externe)
            $href = (strpos($row['path'], 'http') === 0) ? $row['path'] : $base_url . '/' . $row['path'];

            echo '<li class="nav-item link-body-emphasis">
                    <a href="' . htmlspecialchars($href) . '" class="nav-link link-dark px-2';
            
            $current_slug = $_GET['name'] ?? '';
            if ($current_page == 'page.php' && ($current_slug == ltrim(strstr($row['path'], '='), '='))) {
                echo ' active';
            } else if ($current_page != 'page.php' && $current_page == $row['path'] . '.php') {
                echo ' active';
            }
            echo '">
                        <i class="' . htmlspecialchars($row['fa_icon']) . '"></i> ' . htmlspecialchars($row['page']) . '
                    </a>
                </li>';
        }
    }

    // --- 4. MEGA MENUS DYNAMIQUES ---
    $purifier = get_purifier();
    $mm_query = mysqli_query($connect, "SELECT * FROM mega_menus WHERE active='Yes' ORDER BY position_order ASC");
    
    while ($mm = mysqli_fetch_assoc($mm_query)) {
        
        // Vérifier la visibilité des colonnes
        $show_col_2 = ($mm['col_2_type'] != 'none');
        $show_col_3 = ($mm['col_3_type'] != 'none');

        // Calculer la largeur idéale du menu (PC uniquement)
        $custom_width = '900px'; 
        if (!$show_col_2 && !$show_col_3) { $custom_width = '250px'; } 
        elseif (!$show_col_2 || !$show_col_3) { $custom_width = '600px'; }

        echo '<li class="nav-item dropdown">
                <a href="' . htmlspecialchars($mm['trigger_link']) . '" class="nav-link dropdown-toggle px-2" data-bs-toggle="dropdown">
                    <i class="' . htmlspecialchars($mm['trigger_icon']) . '"></i> ' . htmlspecialchars($mm['trigger_text']) . ' 
                </a>
                
                <div class="dropdown-menu mega-menu-custom bg-white" style="min-width: ' . $custom_width . ';">
                    <div class="px-4 py-3">
                        <div class="row g-4">
                            
                            <div class="col-12 col-lg-2 border-end-lg">
                                <h6 class="text-uppercase fw-bold text-primary mb-3 pt-2" style="font-size: 0.85rem;">
                                    ' . htmlspecialchars($mm['col_1_title']) . '
                                </h6>
                                <div class="text-small">
                                    ' . $purifier->purify($mm['col_1_content']) . ' 
                                </div>
                            </div>';

                            // Colonne 2
                            if ($show_col_2) {
                                echo '<div class="col-12 col-lg-4 border-end-lg">
                                        <h6 class="text-uppercase fw-bold text-secondary mb-3 pt-2" style="font-size: 0.85rem;">
                                            ' . htmlspecialchars($mm['col_2_title']) . '
                                        </h6>
                                        <div class="row">';
                                if ($mm['col_2_type'] == 'categories') {
                                    $run_cats = mysqli_query($connect, "SELECT * FROM `categories` ORDER BY category ASC");
                                    while ($rc = mysqli_fetch_assoc($run_cats)) {
                                        echo '<div class="col-6 mb-1"><a class="dropdown-item rounded px-2 py-1 small text-truncate" href="category?name=' . htmlspecialchars($rc['slug']) . '"><i class="fas fa-angle-right text-muted me-1"></i> ' . htmlspecialchars($rc['category']) . '</a></div>';
                                    }
                                } elseif ($mm['col_2_type'] == 'custom') {
                                    echo '<div class="col-12">' . $purifier->purify($mm['col_2_content']) . '</div>';
                                }
                                echo '</div></div>';
                            }

                            // Colonne 3
                            if ($show_col_3) {
                                echo '<div class="col-12 col-lg-6">
                                        <h6 class="text-uppercase fw-bold text-success mb-3 pt-2" style="font-size: 0.85rem;">
                                            ' . htmlspecialchars($mm['col_3_title']) . '
                                        </h6>
                                        <div class="row g-3">';
                                if ($mm['col_3_type'] == 'latest_posts') {
                                    $recent_q = mysqli_query($connect, "SELECT title, slug, image, created_at FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT 4");
                                    if(mysqli_num_rows($recent_q) > 0){
                                        while($post = mysqli_fetch_assoc($recent_q)){
                                            $img_src = $post['image'] != '' ? htmlspecialchars($post['image']) : 'assets/img/no-image.png';
                                            if($post['image'] == '') { $img_display = '<div class="bg-light d-flex align-items-center justify-content-center text-muted small" style="height: 60px; width: 80px; border-radius: 4px;"><i class="fas fa-image"></i></div>'; } 
                                            else { $img_display = '<img src="' . $img_src . '" class="img-fluid rounded" style="height: 60px; width: 80px; object-fit: cover;" alt="Post">'; }
                                            
                                            echo '<div class="col-12 col-md-6">
                                                <a href="post?name=' . htmlspecialchars($post['slug']) . '" class="text-decoration-none link-dark d-flex align-items-center p-1 rounded hover-bg-light">
                                                    <div class="flex-shrink-0 me-2">' . $img_display . '</div>
                                                    <div class="flex-grow-1" style="min-width: 0;">
                                                        <h6 class="mb-0 small fw-bold text-truncate" style="line-height: 1.4;">' . htmlspecialchars($post['title']) . '</h6>
                                                        <small class="text-muted" style="font-size: 0.75rem;">' . date('M d, Y', strtotime($post['created_at'])) . '</small>
                                                    </div>
                                                </a>
                                            </div>';
                                        }
                                    } else { echo '<div class="col-12 text-muted">No posts.</div>'; }
                                } elseif ($mm['col_3_type'] == 'custom') {
                                    echo '<div class="col-12">' . $purifier->purify($mm['col_3_content']) . '</div>';
                                }
                                echo '</div></div>';
                            }

        echo '          </div> 
                    </div>
                </div>
              </li>';
    } 

    // --- FIN DU CACHE ---
    // Sauvegarde du HTML généré dans le fichier cache
    $generated_html = ob_get_clean();
    if(function_exists('save_cache')) {
        save_cache('main_menu', $generated_html);
    }
    echo $generated_html;
}
?>
</ul>

                <!-- Right side of navbar -->
                <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <li class="nav-item me-2">
                        <button class="btn btn-link nav-link theme-switcher" id="theme-switcher-btn" type="button" aria-label="Toggle theme">
                            <i class="fas fa-moon" id="theme-icon-moon"></i>
                            <i class="fas fa-sun" id="theme-icon-sun" style="display: none;"></i>
                        </button>
                    </li>
                <?php
    if ($logged == 'No') {
?> 
					<li class="nav-item">
						<a href="login" class="btn btn-primary px-2">
							<i class="fas fa-sign-in-alt"></i> Sign In &nbsp;|&nbsp; Register
						</a>
					</li>
<?php
            } else {
                    // --- AJOUT : Compter les messages non lus dans le Tchat ---
                    $unread_chat = 0;
                    $my_id = $rowu['id'];
                    // On compte les messages qui ne sont PAS de nous (sender_id != my_id) 
                    // ET qui sont dans nos conversations (user_1 ou user_2 = my_id)
                    $stmt_count_chat = mysqli_prepare($connect, "
                        SELECT COUNT(m.id) 
                        FROM chat_messages m 
                        JOIN chat_conversations c ON m.conversation_id = c.id 
                        WHERE m.is_read = 'No' 
                        AND m.sender_id != ? 
                        AND (c.user_1 = ? OR c.user_2 = ?)
                    ");
                    mysqli_stmt_bind_param($stmt_count_chat, "iii", $my_id, $my_id, $my_id);
                    mysqli_stmt_execute($stmt_count_chat);
                    mysqli_stmt_bind_result($stmt_count_chat, $unread_chat);
                    mysqli_stmt_fetch($stmt_count_chat);
                    mysqli_stmt_close($stmt_count_chat);

                    // Définir la couleur : Vert si message, Gris sinon
                    $dot_color = ($unread_chat > 0) ? '#2ecc71' : '#95a5a6'; // Vert Émeraude / Gris
                    $dot_title = ($unread_chat > 0) ? $unread_chat . ' unread message(s)' : 'No new messages';
            ?>

<?php
                    // --- GESTION AVATAR NAVBAR (Correction Chemins Absolus) ---
                    $nav_avatar = $settings['site_url'] . '/assets/img/avatar.png'; // Par défaut
                    
                    if (!empty($rowu['avatar'])) {
                        $clean_path = str_replace('../', '', $rowu['avatar']);
                        if (strpos($clean_path, 'http') === 0) {
                            $nav_avatar = $clean_path;
                        } else {
                            $nav_avatar = $settings['site_url'] . '/' . $clean_path;
                        }
                    }
            ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link link-dark dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                            <img src="<?php echo htmlspecialchars($nav_avatar); ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 5px;" onerror="this.src='<?php echo $settings['site_url']; ?>/assets/img/avatar.png';">                            
                            <span style="width: 10px; height: 10px; background-color: <?php echo $dot_color; ?>; border-radius: 50%; display: inline-block; margin-right: 8px; box-shadow: 0 0 3px rgba(0,0,0,0.3);" title="<?php echo $dot_title; ?>"></span>
                            
                            Profile <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?php if ($current_page == 'profile.php') { echo ' active'; } ?>" href="profile">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item <?php if ($current_page == 'chat.php') { echo ' active'; } ?>" href="chat.php">
                                    <i class="fas fa-comments"></i> Tchat 
                                    <span id="nav-chat-badge" class="badge bg-danger ms-2" style="display: <?php echo ($unread_chat > 0) ? 'inline-block' : 'none'; ?>">
                                        <?php echo $unread_chat; ?>
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item <?php if ($current_page == 'my-posts.php') { echo ' active'; } ?>" href="my-posts.php"> 
                                    <i class="fas fa-file-alt"></i> My submitted articles
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item <?php if ($current_page == 'my-projects.php') { echo ' active'; } ?>" href="my-projects.php"> 
                                    <i class="fas fa-microchip"></i> My submitted projects
                                </a>
                            </li>

                            
                            <li>
								<a class="dropdown-item <?php
if ($current_page == 'submit_post.php') { 
	echo ' active';
}
?>" href="submit_post.php"> 
                                    <i class="fas fa-pen-square"></i> Submit an article
								</a>
							</li>
                            <li>
                                <a class="dropdown-item <?php 
if ($current_page == 'submit_testimonial.php'){ 
	echo ' active';
}
?>" href="submit_testimonial.php"> 
                                    <i class="fas fa-star"></i> Add Testimonial
                                </a>
                            </li>                            
							<li>
								<a class="dropdown-item <?php
if ($current_page == 'my-favorites.php') { 
	echo ' active';
}
?>" href="my-favorites.php"> 
                                    <i class="fa fa-bookmark"></i> My favorites
								</a>
							</li>
							<li>
								<a class="dropdown-item <?php
if ($current_page == 'my-comments.php') {
	echo ' active';
}
?>" href="my-comments">
									<i class="fa fa-comments"></i> My Comments
								</a>
							</li>
                            <li role="separator" class="divider"></li>
							<li>
								<a class="dropdown-item" href="logout">
									<i class="fas fa-sign-out-alt"></i> Logout
								</a>
							</li>
						</ul>
					</li>
<?php
    }
?>
				</ul><!-- End right side of navbar -->
			</div><!-- End navbar collapse -->
		</div><!-- End container -->
	</nav> <!-- End nav -->
    
<?php
if ($settings['latestposts_bar'] == 'Enabled') {
?>
    <div class="latest-news-bar bg-white border-bottom shadow-sm" style="height: 50px; overflow: hidden;">
        <div class="<?php echo ($settings['layout'] == 'Wide') ? 'container-fluid' : 'container'; ?> h-100">
            <div class="row h-100 g-0">
                
                <div class="col-auto d-flex align-items-center bg-danger text-white px-3 position-relative" style="z-index: 10;">
                    <i class="fas fa-bolt me-2"></i> 
                    <span class="fw-bold text-uppercase" style="font-size: 0.9rem;">Latest</span>
                    <div style="position: absolute; right: -10px; top: 0; width: 0; height: 0; border-top: 50px solid #dc3545; border-right: 10px solid transparent;"></div>
                </div>

                <div class="col d-flex align-items-center overflow-hidden position-relative bg-light">
                    <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();" style="line-height: 50px;">
                        <?php
                        // Récupérer les 6 derniers articles
                        $run = mysqli_query($connect, "SELECT title, slug, image, created_at FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT 6");
                        
                        if (mysqli_num_rows($run) > 0) {
                            while ($row = mysqli_fetch_assoc($run)) {
                                // Gestion de l'image (Absolue)
                                $raw_img = !empty($row['image']) ? $row['image'] : 'assets/img/no-image.png';
                                $clean_img = str_replace('../', '', $raw_img);
                                $img_url = $settings['site_url'] . '/' . $clean_img;
                                $date = date('d M', strtotime($row['created_at']));
                                
                            echo '
                                <span class="d-inline-flex align-items-center me-5">
                                    <img src="' . htmlspecialchars($img_url) . '" class="rounded border" style="width: 35px; height: 35px; object-fit: cover; margin-right: 4px;" onerror="this.src=\'' . $settings['site_url'] . '/assets/img/no-image.png\';">                                    <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none fw-bold" style="font-size: 0.9rem;">
                                        ' . htmlspecialchars($row['title']) . '
                                    </a>
                                    <span class="badge bg-secondary ms-2" style="font-size: 0.7em;">' . $date . '</span>
                                </span>';
                            }
                        }
                        ?>
                    </marquee>
                </div>

            </div>
        </div>
    </div>
<?php
}
?>
	
    <div class="<?php
if ($settings['layout'] == 'Wide') {
	echo 'container-fluid';
} else {
	echo 'container';
}
?> mt-3">
	
<?php
// --- ✨✨ MODIFICATION ICI ✨✨ ---
// Requête pour les widgets de type "header"
$run = mysqli_query($connect, "SELECT * FROM widgets WHERE position = 'header' AND active = 'Yes' ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($run)) {
    // Appelle la nouvelle fonction d'affichage
    render_widget($row);
}
// --- ✨✨ FIN DE LA MODIFICATION ✨✨ ---
?>
	
        <div class="row">
<?php
}
?>