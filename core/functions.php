<?php
// --- AJOUT OBLIGATOIRE POUR PHPMAILER ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// ----------------------------------------

// --- FONCTIONS UTILITAIRES ---

function short_text($text, $length)
{
    $maxTextLenght = $length;
    $aspace        = " ";
    if (strlen($text) > $maxTextLenght) {
        $text = substr(trim($text), 0, $maxTextLenght);
        $text = substr($text, 0, strlen($text) - strpos(strrev($text), $aspace));
        $text = $text . '...';
    }
    return $text;
}

function emoticons($text)
{
    // ... (votre fonction emoticons reste inchangée) ...
    $icons = array(
        ':)' => '🙂',
        ':-)' => '🙂',
        ':}' => '🙂',
        ':D' => '😀',
        ':d' => '😁',
        ':-D ' => '😂',
        ';D' => '😂',
        ';d' => '😂',
        ';)' => '😉',
        ';-)' => '😉',
        ':P' => '😛',
        ':-P' => '😛',
        ':-p' => '😛',
        ':p' => '😛',
        ':-b' => '😛',
        ':-Þ' => '😛',
        ':(' => '🙁',
        ';(' => '😓',
        ':\'(' => '😓',
        ':o' => '😮',
        ':O' => '😮',
        ':0' => '😮',
        ':-O' => '😮',
        ':|' => '😐',
        ':-|' => '😐',
        ' :/' => ' 😕',
        ':-/' => '😕',
        ':X' => '😷',
        ':x' => '😷',
        ':-X' => '😷',
        ':-x' => '😷',
        '8)' => '😎',
        '8-)' => '😎',
        'B-)' => '😎',
        ':3' => '😊',
        '^^' => '😊',
        '^_^' => '😊',
        '<3' => '😍',
        ':*' => '😘',
        'O:)' => '😇',
        '3:)' => '😈',
        'o.O' => '😵',
        'O_o' => '😵',
        'O_O' => '😵',
        'o_o' => '😵',
        '0_o' => '😵',
        'T_T' => '😵',
        '-_-' => '😑',
        '>:O' => '😆',
        '><' => '😆',
        '>:(' => '😣',
        ':v' => '🙃',
        '(y)' => '👍',
        ':poop:' => '💩',
        ':|]' => '🤖'
    );
    // --- CORRECTION ---
    // On remplace les codes (clés) par les emojis (valeurs)
    return str_replace(array_keys($icons), array_values($icons), $text);    
}

function generateSeoURL($string, $random_numbers = 1, $wordLimit = 8) { 
    $separator = '-'; 
    $string = strip_tags($string);
    
    // Table de conversion des accents (inchangée)
    $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');
    $string = strtr($string, $unwanted_array);
    
    // --- CORRECTION ICI ---
    // 1. On remplace les tirets existants par des espaces pour préserver la séparation des mots
    $string = str_replace('-', ' ', $string);
    
    // 2. On nettoie (on ne garde que lettres, chiffres et espaces)
    $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    // ---------------------

    if($wordLimit != 0){
        $wordArr = explode(' ', $string); 
        $string = implode(' ', array_slice($wordArr, 0, $wordLimit)); 
    } 
    
    $string = preg_replace('/\s+/', $separator, $string);
    $string = strtolower(trim($string, $separator));
    
	if ($random_numbers == 1) { $string = $string . '-' . rand(10000, 99999); }
	
    return $string; 
}

function get_purifier() {
    static $purifier = null;
    if ($purifier === null) {
        // Le chemin autoload est déjà géré dans init.php, donc HTMLPurifier est dispo
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', __DIR__ . '/../cache'); // Attention au chemin du cache
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('HTML.Allowed', 'p[style|class],b,i[class|style],u,s,a[href|title|class|target|rel],ul[class],ol,li[class],br,img[src|alt|title|width|height|style|class],span[style|class],div[style|class],blockquote,pre,h1,h2,h3,h4,h5,h6,table[class],thead,tbody,tr,th,td,iframe[src|width|height|frameborder|allow|allowfullscreen|title|referrerpolicy]');
        $config->set('URI.AllowedSchemes', array('http' => true, 'https' => true, 'mailto' => true, 'ftp' => true, 'data' => true));
        $config->set('CSS.AllowedProperties', 'width,height,text-decoration,color,background-color,font-weight,font-style,text-align');
        $purifier = new HTMLPurifier($config);
    }
    return $purifier;
}

function format_comment_with_code($text)
{
    $code_blocks = []; $i = 0;
    $text = preg_replace_callback('/\[code=([a-zA-Z0-9_-]+)\](.*?)\[\/code\]/s', function ($matches) use (&$code_blocks, &$i) {
            $lang = htmlspecialchars($matches[1]); $code_content = htmlspecialchars($matches[2]);
            $placeholder = "---CODEBLOCK{$i}---"; $code_blocks[$placeholder] = '<pre><code class="language-' . $lang . '">' . $code_content . '</code></pre>';
            $i++; return $placeholder;
        }, $text);
    $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/s', function ($matches) use (&$code_blocks, &$i) {
            $code_content = htmlspecialchars($matches[1]); $placeholder = "---CODEBLOCK{$i}---";
            $code_blocks[$placeholder] = '<pre><code>' . $code_content . '</code></pre>';
            $i++; return $placeholder;
        }, $text);
    $text = htmlspecialchars($text);
    $text = emoticons($text);
    $text = nl2br($text);
    if (!empty($code_blocks)) { $text = str_replace(array_keys($code_blocks), array_values($code_blocks), $text); }
    return $text;
}

// Fonction Affichage Commentaires (Récursive)
function display_comments($post_id, $parent_id = 0, $level = 0) {
    global $connect, $settings, $logged, $rowu;
    $margin_left = ($level > 5) ? (5 * 30) : ($level * 30);
    $stmt_comments = mysqli_prepare($connect, "SELECT * FROM comments WHERE post_id=? AND parent_id = ? AND approved='Yes' ORDER BY created_at ASC");
    mysqli_stmt_bind_param($stmt_comments, "ii", $post_id, $parent_id);
    mysqli_stmt_execute($stmt_comments);
    $q = mysqli_stmt_get_result($stmt_comments);
    while ($comment = mysqli_fetch_array($q)) {
        echo render_comment_html_internal($comment, $margin_left); // Appel fonction interne helper
        display_comments($post_id, $comment['id'], $level + 1);
        echo '</div>';
    }
    mysqli_stmt_close($stmt_comments);
}

// Fonction Helper pour afficher un commentaire (utilisée par display_comments et ajax)
function render_comment_html_internal($comment, $margin_left = 0) {
    global $connect, $settings, $logged, $rowu;
    // ... (Code de rendu HTML du commentaire - voir core.php original) ...
    // J'ai extrait ce code pour éviter la duplication, c'est la même logique que "render_comment_html"
    return render_comment_html($comment['id'], $margin_left);
}

// Fonction principale pour rendre le HTML d'un commentaire
function render_comment_html($comment_id, $margin_left = 0) {
    global $connect, $settings, $logged, $rowu;
    
    $stmt_comment = mysqli_prepare($connect, "SELECT * FROM comments WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt_comment, "i", $comment_id);
    mysqli_stmt_execute($stmt_comment);
    $q = mysqli_stmt_get_result($stmt_comment);
    $comment = mysqli_fetch_array($q);
    mysqli_stmt_close($stmt_comment);

    if (!$comment) return "";

    $aauthor_id = $comment['user_id'];
    $aauthor_name = 'Guest'; $comment_badge = ''; $aavatar = 'assets/img/avatar.png'; $arole = '<span class="badge bg-secondary">Guest</span>';
    
    if ($comment['guest'] != 'Yes') {
        $stmt_user = mysqli_prepare($connect, "SELECT * FROM `users` WHERE id=? LIMIT 1");
        mysqli_stmt_bind_param($stmt_user, "i", $aauthor_id);
        mysqli_stmt_execute($stmt_user);
        $querych = mysqli_stmt_get_result($stmt_user);
        if (mysqli_num_rows($querych) > 0) {
            $rowch = mysqli_fetch_assoc($querych);
            $aavatar = $rowch['avatar'];
            $aauthor_name = $rowch['username'];
            if ($rowch['role'] == 'Admin') $arole = '<span class="badge bg-success">Admin</span>'; // Vert
            elseif ($rowch['role'] == 'Editor') $arole = '<span class="badge bg-primary">Editor</span>'; // Bleu
            else $arole = '<span class="badge bg-info">User</span>';
            $comment_badge = get_user_comment_badge($aauthor_id);
        }
        mysqli_stmt_close($stmt_user);
    } else {
        $aauthor_name = htmlspecialchars($comment['user_id']);
    }

    ob_start();
    ?>
    <div class="comment-container" style="margin-left: <?php echo $margin_left; ?>px;" id="comment-<?php echo $comment['id']; ?>">
        <div class="row d-flex justify-content-center bg-white rounded border mt-3 mb-3 ms-1 me-1">
            <div class="mb-2 d-flex flex-start align-items-center">
                <img class="rounded-circle shadow-1-strong mt-1 me-3" src="<?php echo htmlspecialchars($aavatar); ?>" width="50" height="50" />
                <div class="mt-1 mb-1">
                    <h6 class="fw-bold mt-1 mb-1"><i class="fa fa-user"></i> <?php echo htmlspecialchars($aauthor_name); ?> <?php echo $arole; ?> <?php echo $comment_badge; ?> </h6>
                    <p class="small mb-0"><i><i class="fas fa-calendar"></i> <?php echo date($settings['date_format'] . ' H:i', strtotime($comment['created_at'])); ?></i></p>
                </div>
            </div>
            <hr class="my-0" />
            <p class="mt-1 mb-1 pb-1"><?php echo format_comment_with_code(html_entity_decode($comment['comment'])); ?></p>
            <hr class="my-0" />
            <div class="p-2">
                <button class="btn btn-sm btn-link" onclick="replyToComment(<?php echo $comment['id']; ?>)"><i class="fas fa-reply"></i> Answer</button>
                <?php if ($logged == 'Yes' && $comment['guest'] == 'No' && $rowu['id'] == $comment['user_id']) { echo '<a href="edit-comment.php?id=' . $comment['id'] . '" class="btn btn-sm btn-link text-primary"><i class="fas fa-edit"></i> Modify</a>'; } ?>
            </div>
        </div>
    <?php
    return ob_get_clean();
}

// Autres getters simples (Author, Title, etc.)
function post_author($author_id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT username FROM `users` WHERE id=? LIMIT 1"); mysqli_stmt_bind_param($stmt, "i", $author_id); mysqli_stmt_execute($stmt); $result = mysqli_stmt_get_result($stmt); if ($row = mysqli_fetch_assoc($result)) { return '<a href="author.php?username=' . urlencode($row['username']) . '">' . htmlspecialchars($row['username']) . '</a>'; } mysqli_stmt_close($stmt); return '-'; }
function post_title($post_id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT title FROM `posts` WHERE id=? LIMIT 1"); mysqli_stmt_bind_param($stmt, "i", $post_id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $row = mysqli_fetch_assoc($res); return $row ? $row['title'] : '-'; }
function post_category($id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT category FROM `categories` WHERE id=? LIMIT 1"); mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $row = mysqli_fetch_assoc($res); return $row ? $row['category'] : '-'; }
function post_categoryslug($id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT slug FROM `categories` WHERE id=? LIMIT 1"); mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $row = mysqli_fetch_assoc($res); return $row ? $row['slug'] : ''; }
function post_commentscount($id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM `comments` WHERE post_id=?"); mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $row = mysqli_fetch_assoc($res); return $row['count']; }

function get_post_like_count($post_id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM `post_likes` WHERE post_id=?"); mysqli_stmt_bind_param($stmt, "i", $post_id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $row = mysqli_fetch_assoc($res); return $row['count']; }
function check_user_has_liked($post_id) { global $connect, $logged, $rowu; if($logged=='Yes'){$uid=$rowu['id']; $stmt=mysqli_prepare($connect,"SELECT id FROM post_likes WHERE post_id=? AND user_id=?"); mysqli_stmt_bind_param($stmt,"ii",$post_id,$uid);} else {$sid=session_id(); $stmt=mysqli_prepare($connect,"SELECT id FROM post_likes WHERE post_id=? AND session_id=?"); mysqli_stmt_bind_param($stmt,"is",$post_id,$sid);} mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); return mysqli_num_rows($res)>0; }

// --- FONCTIONS LIKES PROJETS ---
function get_project_like_count($project_id) {
    global $connect;
    $stmt = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM `project_likes` WHERE project_id=?");
    mysqli_stmt_bind_param($stmt, "i", $project_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res)['count'];
}

function check_user_has_liked_project($project_id) {
    global $connect, $logged, $rowu;
    if ($logged == 'Yes') {
        $uid = $rowu['id'];
        $stmt = mysqli_prepare($connect, "SELECT id FROM project_likes WHERE project_id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $project_id, $uid);
    } else {
        $sid = session_id();
        $stmt = mysqli_prepare($connect, "SELECT id FROM project_likes WHERE project_id=? AND session_id=?");
        mysqli_stmt_bind_param($stmt, "is", $project_id, $sid);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($res) > 0;
}

// --- FONCTION FAVORIS PROJETS ---
function check_user_has_favorited_project($project_id) {
    global $connect, $logged, $rowu;
    if ($logged == 'Yes') {
        $uid = $rowu['id'];
        $stmt = mysqli_prepare($connect, "SELECT id FROM user_project_favorites WHERE project_id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $project_id, $uid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        return mysqli_num_rows($res) > 0;
    }
    return false;
}

function get_user_comment_badge($user_id) { global $connect; $stmt = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM comments WHERE user_id=? AND guest='No' AND approved='Yes'"); mysqli_stmt_bind_param($stmt, "i", $user_id); mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $cnt = mysqli_fetch_assoc($res)['count']; if($cnt>=50) return '<span class="badge bg-warning text-dark ms-1"><i class="fas fa-star"></i> Veteran</span>'; if($cnt>=20) return '<span class="badge bg-success ms-1"><i class="fas fa-comments"></i> Loyal</span>'; if($cnt>=5) return '<span class="badge bg-info ms-1"><i class="fas fa-comment-dots"></i> Active</span>'; return ''; }
function get_reading_time($content) { $w = str_word_count(strip_tags(html_entity_decode($content))); $m = ceil($w/200); return '<i class="far fa-clock"></i> Read: '. ($m<1?1:$m) .' min'; }

// Fonctions Widget et Email et CSRF
function validate_csrf_token() { if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF Error.'); } }
function validate_csrf_token_get() { if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) { die('CSRF Error.'); } }

// Fonction render_widget (Inclure tout votre code render_widget ici - C'est long, je mets un placeholder)
function render_widget($widget_row) {
    // Globals nécessaires
    global $connect, $settings, $purifier, $quiz_id, $logged, $rowu;

    if (!isset($purifier)) {
        $purifier = get_purifier();
    }
    
    $position = $widget_row['position'];
    $type = $widget_row['widget_type'];
    $wrapper_open = false;

    // --- 1. DÉFINITION DE L'ICÔNE (Comme dans l'admin) ---
    $w_icon = 'fa-puzzle-piece'; // Défaut
    
    switch ($type) {
        case 'html':             $w_icon = 'fa-code'; break;
        case 'latest_posts':     $w_icon = 'fa-list'; break;
        case 'search':           $w_icon = 'fa-search'; break;
        case 'quiz_leaderboard': $w_icon = 'fa-trophy'; break;
        case 'faq_leaderboard':  $w_icon = 'fa-question-circle'; break;
        case 'testimonials':     $w_icon = 'fa-comments'; break;
        case 'newsletter':       $w_icon = 'fa-envelope'; break;
        case 'online_users':     $w_icon = 'fa-users'; break;
        case 'latest_projects':  $w_icon = 'fa-microchip'; break;
        case 'shop':             $w_icon = 'fa-shopping-cart'; break;
    }
    // -----------------------------------------------------

    // --- 2. OUVERTURE DU CONTENEUR (Avec l'icône ajoutée) ---
    
    // Cas Sidebar
    if ($position == 'Sidebar') {
        // La newsletter a son propre design (card spéciale), on ne met pas le header standard
        if ($type != 'newsletter') { 
            echo '
                <div class="card mb-3">
                      <div class="card-header bg-white fw-bold">
                          <i class="fas ' . $w_icon . ' me-2 text-primary"></i> ' . htmlspecialchars($widget_row['title']) . '
                      </div>
                      <div class="card-body">';
            $wrapper_open = true;
        }
    
    // Cas Header/Footer
    } else { 
        if ($type == 'latest_posts') {
             echo '<h5 class="mt-3"><i class="fas ' . $w_icon . ' me-2"></i> ' . htmlspecialchars($widget_row['title']) . '</h5>';
        } elseif ($type != 'html' && $type != 'newsletter') {
             echo '
                <div class="card mb-3">
                      <div class="card-header bg-white fw-bold">
                          <i class="fas ' . $w_icon . ' me-2 text-primary"></i> ' . htmlspecialchars($widget_row['title']) . '
                      </div>
                      <div class="card-body">';
             $wrapper_open = true;
        }
    }

    // --- 3. CONTENU DU WIDGET (Switch original...) ---
    switch ($type) {

        // CAS : UTILISATEURS EN LIGNE (CHATS)
        case 'online_users':
            // On considère "En ligne" si actif dans les 5 dernières minutes
            $time_limit = date("Y-m-d H:i:s", strtotime("-5 minutes"));
            $current_user_id = isset($rowu['id']) ? $rowu['id'] : 0;

            // Utilisation de 'last_activity'
            $stmt_online = mysqli_prepare($connect, "SELECT id, username, avatar, role FROM users WHERE last_activity > ? AND id != ? ORDER BY last_activity DESC LIMIT 10");
            
            mysqli_stmt_bind_param($stmt_online, "si", $time_limit, $current_user_id);
            mysqli_stmt_execute($stmt_online);
            $res_online = mysqli_stmt_get_result($stmt_online);
            
            echo '<ul class="list-group list-group-flush">';
            
            if (mysqli_num_rows($res_online) > 0) {
                while ($u_online = mysqli_fetch_assoc($res_online)) {
                    
                    // 1. Gestion Avatar Robuste
                    $u_avatar_src = 'assets/img/avatar.png';
                    if (!empty($u_online['avatar'])) {
                        $clean_path = str_replace('../', '', $u_online['avatar']);
                        if (strpos($clean_path, 'http') === 0 || file_exists($clean_path)) {
                            $u_avatar_src = $clean_path;
                        }
                    }

                    // 2. Gestion Badge Rôle (NOUVEAU)
                    $role_badge = 'bg-secondary'; // Gris par défaut (User)
                    if ($u_online['role'] == 'Admin') $role_badge = 'bg-danger'; // Rouge
                    if ($u_online['role'] == 'Editor') $role_badge = 'bg-primary'; // Bleu

                    // Lien vers le tchat
                    $chat_link = ($logged == 'Yes') ? 'chat.php?with=' . $u_online['id'] : 'login.php';
                    $tooltip = ($logged == 'Yes') ? 'Chat with ' . htmlspecialchars($u_online['username']) : 'Login to chat';

                    echo '
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <a href="' . $chat_link . '" class="d-flex align-items-center text-decoration-none text-dark" title="' . $tooltip . '">
                            <div class="position-relative me-3">
                                <img src="' . htmlspecialchars($u_avatar_src) . '" alt="User" class="rounded-circle" width="40" height="40" style="object-fit: cover;" onerror="this.src=\'assets/img/avatar.png\';">
                                <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                    <span class="visually-hidden">Online</span>
                                </span>
                            </div>
                            
                            <div>
                                <h6 class="mb-0 small fw-bold">' . htmlspecialchars($u_online['username']) . '</h6>
                                <span class="badge ' . $role_badge . '" style="font-size: 0.65rem;">' . htmlspecialchars($u_online['role']) . '</span>
                            </div>
                        </a>
                        
                        <a href="' . $chat_link . '" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-comment-dots"></i>
                        </a>
                    </li>';
                }
            } else {
                echo '<li class="list-group-item text-center text-muted small border-0">No one else is online right now.</li>';
            }
            
            echo '</ul>';
            
            mysqli_stmt_close($stmt_online);
            break;

        // CAS : DERNIERS PROJETS
        case 'latest_projects':
            $config = json_decode($widget_row['config_data'], true);
            $limit = isset($config['count']) ? (int)$config['count'] : 5;
            
            $q_projs = mysqli_query($connect, "SELECT id, title, slug, image, difficulty, created_at FROM projects WHERE active='Yes' ORDER BY created_at DESC LIMIT $limit");

            if (mysqli_num_rows($q_projs) == 0) {
                echo '<p class="text-muted small">No projects yet.</p>';
            } else {
                echo '<div class="list-group list-group-flush">';
                while ($proj = mysqli_fetch_assoc($q_projs)) {
                    
                    // Image Robuste
                    $img_src = 'assets/img/project-no-image.png';
                    if (!empty($proj['image'])) {
                        $clean = str_replace('../', '', $proj['image']);
                        if (file_exists($clean)) { $img_src = $clean; }
                    }

                    // Badge Difficulté
                    $badge_color = 'secondary';
                    if($proj['difficulty'] == 'Easy') $badge_color = 'success';
                    if($proj['difficulty'] == 'Intermediate') $badge_color = 'primary';
                    if($proj['difficulty'] == 'Advanced') $badge_color = 'warning';
                    if($proj['difficulty'] == 'Expert') $badge_color = 'danger';

                    echo '
                    <a href="project?name=' . htmlspecialchars($proj['slug']) . '" class="list-group-item list-group-item-action d-flex align-items-center p-2 border-0">
                        <div class="flex-shrink-0 me-3">
                            <img src="' . htmlspecialchars($img_src) . '" class="rounded" width="80" height="60" style="object-fit: cover;" onerror="this.src=\'assets/img/project-no-image.png\';">
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="mb-1 text-truncate small fw-bold text-dark">' . htmlspecialchars($proj['title']) . '</h6>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-' . $badge_color . ' me-2" style="font-size:0.6rem;">' . htmlspecialchars($proj['difficulty']) . '</span>
                                <small class="text-muted" style="font-size: 0.7rem;">' . date('d M', strtotime($proj['created_at'])) . '</small>
                            </div>
                        </div>
                    </a>';
                }
                echo '</div>';
            }
            break;

        // CAS : NEWSLETTER (NOUVEAU)
        case 'newsletter':
            ?>
            <div class="card mb-3 sidebar-subscribe shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 mx-auto bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-envelope-open-text fa-2x"></i>
                    </div>
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($widget_row['title']); ?></h5>
                    <p class="card-text small text-muted mb-4">Get the latest news and exclusive offers directly in your inbox.</p>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <input type="email" class="form-control text-center" placeholder="Your email address" name="email" required style="border-radius: 20px;">
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary btn-block" type="submit" name="subscribe" style="border-radius: 20px;">
                                Subscribe Now
                            </button>
                        </div>
                    </form>
                    
                    <?php
                    if (isset($_POST['subscribe'])) {
                        validate_csrf_token();
                        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); 
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            echo '<div class="alert alert-danger mt-2 small p-2">Invalid E-Mail Address</div>';
                        } else {
                            $stmt_sub_check = mysqli_prepare($connect, "SELECT email FROM `newsletter` WHERE email=? LIMIT 1");
                            mysqli_stmt_bind_param($stmt_sub_check, "s", $email);
                            mysqli_stmt_execute($stmt_sub_check);
                            $result_sub_check = mysqli_stmt_get_result($stmt_sub_check);
                            
                            if (mysqli_num_rows($result_sub_check) > 0) {
                                echo '<div class="alert alert-warning mt-2 small p-2">Already subscribed.</div>';
                            } else {
                                $stmt_sub_insert = mysqli_prepare($connect, "INSERT INTO `newsletter` (email) VALUES (?)");
                                mysqli_stmt_bind_param($stmt_sub_insert, "s", $email);
                                mysqli_stmt_execute($stmt_sub_insert);
                                mysqli_stmt_close($stmt_sub_insert);
                                echo '<div class="alert alert-success mt-2 small p-2">Successfully subscribed!</div>';
                            }
                            mysqli_stmt_close($stmt_sub_check);
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            break;

        // CAS : HTML
        case 'html':
            echo $purifier->purify($widget_row['content']);
            break;

        // CAS : SLIDER TÉMOIGNAGES
        case 'testimonials':
            // (Votre code Témoignages reste ici, assurez-vous de l'avoir gardé si vous l'aviez ajouté)
            $query_sql = "SELECT * FROM testimonials WHERE active = 'Yes' ORDER BY RAND() LIMIT 5";
            $stmt_testi = mysqli_prepare($connect, $query_sql);
            if ($stmt_testi) {
                mysqli_stmt_execute($stmt_testi);
                $res_testi = mysqli_stmt_get_result($stmt_testi);
                if (mysqli_num_rows($res_testi) > 0) {
                    $carousel_id = 'carouselTestimonials_' . $widget_row['id'];
                    echo '<div id="' . $carousel_id . '" class="carousel slide testimonial-widget" data-bs-ride="carousel"><div class="carousel-inner">';
                    $first = true;
                    while ($t = mysqli_fetch_assoc($res_testi)) {
                        $active_class = $first ? 'active' : '';
                        $avatar = !empty($t['avatar']) ? $t['avatar'] : 'assets/img/avatar.png';
                        echo '<div class="carousel-item ' . $active_class . '"><div class="text-center px-3 py-2"><div class="testimonial-quote-icon"><i class="fas fa-quote-left"></i></div><p class="testimonial-text mb-4">' . htmlspecialchars(strip_tags($t['content'])) . '</p><div class="testimonial-author d-flex align-items-center justify-content-center"><img src="' . htmlspecialchars($avatar) . '" alt="User" class="testimonial-avatar shadow-sm"><div class="text-start ms-3"><h6 class="fw-bold mb-0 text-dark">' . htmlspecialchars($t['name']) . '</h6><small class="text-muted">' . htmlspecialchars($t['position']) . '</small></div></div></div></div>';
                        $first = false;
                    }
                    echo '</div><button class="carousel-control-prev" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1);"></span><span class="visually-hidden">Previous</span></button><button class="carousel-control-next" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1);"></span><span class="visually-hidden">Next</span></button></div>';
                } else {
                    echo '<p class="text-muted small text-center">Aucun témoignage.</p>';
                }
                mysqli_stmt_close($stmt_testi);
            }
            break;

        // CAS : ARTICLES RÉCENTS
        case 'latest_posts':
            $config = json_decode($widget_row['config_data'], true);
            $limit = isset($config['count']) ? (int)$config['count'] : 4;
            $q_posts = mysqli_query($connect, "SELECT id, title, slug, image, created_at FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT $limit");

            if (mysqli_num_rows($q_posts) == 0) {
                echo '<p>Aucun article à afficher.</p>';
            } else {
                if ($position == 'Sidebar') {
                    while ($post = mysqli_fetch_assoc($q_posts)) {
                        $image = ($post['image'] != "") ? '<img class="rounded shadow-1-strong me-1" src="' . htmlspecialchars($post['image']) . '" width="70" height="70" style="object-fit: cover;" />' : '<div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white" style="width:70px; height:70px;"><i class="fas fa-image"></i></div>';
                        echo '<div class="mb-2 d-flex flex-start align-items-center bg-light rounded"><a href="post?name=' . htmlspecialchars($post['slug']) . '" class="ms-1">' . $image . '</a><div class="mt-2 mb-2 ms-1 me-1" style="min-width: 0;"> <h6 class="text-primary mb-1 text-truncate"> <a href="post?name=' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a></h6><p class="text-muted small mb-0"><i class="fas fa-calendar"></i> ' . date($settings['date_format'], strtotime($post['created_at'])) . '</p></div></div>';
                    }
                } else {
                    $col_class = ($limit == 3) ? 'col-md-4' : (($limit == 2) ? 'col-md-6' : 'col-md-3');
                    echo '<div class="row">';
                    while ($post = mysqli_fetch_assoc($q_posts)) {
                         $image = ($post['image'] != "") ? '<img src="' . htmlspecialchars($post['image']) . '" class="card-img-top" width="100%" height="150" style="object-fit: cover;"/>' : '<div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:150px;">No Image</div>';
                         echo '<div class="' . $col_class . ' mb-3"><div class="card shadow-sm h-100 d-flex flex-column"><a href="post?name=' . htmlspecialchars($post['slug']) . '">'. $image .'</a><div class="card-body d-flex flex-column flex-grow-1 p-3"><a href="post?name=' . htmlspecialchars($post['slug']) . '" class="text-decoration-none"><h6 class="card-title text-primary small">' . htmlspecialchars(short_text($post['title'], 50)) . '</h6></a><small class="text-muted d-block mt-auto"> <i class="far fa-calendar-alt"></i> ' . date($settings['date_format'], strtotime($post['created_at'])) . '</small></div></div></div>';
                    }
                    echo '</div>';
                }
            }
            break;
        
        // CAS : RECHERCHE
        case 'search':
            echo '<form action="search.php" method="GET"><div class="input-group"><input type="search" class="form-control" placeholder="Rechercher..." name="q" required /><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button></div></form>';
            break;

        // CAS : QUIZ LEADERBOARD
        case 'quiz_leaderboard':
            if (isset($quiz_id) && $quiz_id > 0) {
                // --- PAGE QUIZ SPÉCIFIQUE ---
                $stmt_avg = mysqli_prepare($connect, "SELECT AVG(score) AS avg_score, COUNT(DISTINCT user_id) AS total_players FROM quiz_attempts WHERE quiz_id = ?");
                mysqli_stmt_bind_param($stmt_avg, "i", $quiz_id);
                mysqli_stmt_execute($stmt_avg);
                $res_avg = mysqli_stmt_get_result($stmt_avg);
                $global_stats = mysqli_fetch_assoc($res_avg);
                mysqli_stmt_close($stmt_avg);

                $stmt_month = mysqli_prepare($connect, "SELECT COUNT(id) AS monthly_count FROM quiz_attempts WHERE quiz_id = ? AND attempt_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
                mysqli_stmt_bind_param($stmt_month, "i", $quiz_id);
                mysqli_stmt_execute($stmt_month);
                $res_month = mysqli_stmt_get_result($stmt_month);
                $monthly_plays = mysqli_fetch_assoc($res_month)['monthly_count'];
                mysqli_stmt_close($stmt_month);

                // Leaderboard spécifique
                $leaderboard = [];
                $stmt_lead = mysqli_prepare($connect, "SELECT u.username, t1.score, t1.time_seconds FROM quiz_attempts t1 JOIN users u ON t1.user_id = u.id WHERE t1.quiz_id = ? AND t1.id = (SELECT id FROM quiz_attempts t2 WHERE t2.quiz_id = t1.quiz_id AND t2.user_id = t1.user_id ORDER BY t2.score DESC, t2.time_seconds ASC, t2.id DESC LIMIT 1) ORDER BY t1.score DESC, t1.time_seconds ASC LIMIT 9");
                mysqli_stmt_bind_param($stmt_lead, "i", $quiz_id);
                mysqli_stmt_execute($stmt_lead);
                $res_lead = mysqli_stmt_get_result($stmt_lead);
                while($row = mysqli_fetch_assoc($res_lead)) { $leaderboard[] = $row; }
                mysqli_stmt_close($stmt_lead);
                
                echo '<div style="font-size: 0.9em; line-height: 1.6;"><p class="mb-1">Moyenne sur <strong>' . (int)$global_stats['total_players'] . '</strong> joueurs : <strong>' . round((float)$global_stats['avg_score'], 1) . '%</strong></p><p class="mb-2"><strong>' . (int)$monthly_plays . '</strong> tentatives ce mois-ci.</p>';
                
                if (empty($leaderboard)) {
                    echo '<small class="text-muted">Personne n\'a encore joué à ce quiz !</small>';
                } else {
                    // Affichage PROPRE avec Flexbox (Correction chevauchement)
                    echo '<div class="list-group list-group-flush mt-2">';
                    $rank = 1;
                    foreach ($leaderboard as $player) {
                        $rank_color = 'text-muted';
                        if ($rank == 1) $rank_color = 'text-warning';
                        if ($rank == 2) $rank_color = 'text-secondary';
                        if ($rank == 3) $rank_color = 'text-danger';
            
                        echo '<div class="list-group-item px-0 py-1 d-flex justify-content-between align-items-center border-0" style="background: transparent;">
                                <div class="d-flex align-items-center overflow-hidden me-2">
                                    <span class="fw-bold ' . $rank_color . ' me-1" style="min-width: 15px;">' . $rank++ . '.</span>
                                    <span class="text-truncate fw-bold text-dark" title="' . htmlspecialchars($player['username']) . '">' . htmlspecialchars($player['username']) . '</span>
                                </div>
                                <div class="text-end ms-1" style="white-space: nowrap; font-size: 0.85em;">
                                    <span class="badge bg-primary">' . $player['score'] . '%</span>
                                    <small class="text-muted ms-1">(' . $player['time_seconds'] . 's)</small>
                                </div>
                              </div>';
                    }
                    echo '</div>';
                }
                echo '</div>';

            } else {
                // --- HALL OF FAME GLOBAL ---
                $stmt_global = mysqli_prepare($connect, "SELECT u.username, u.avatar, AVG(a.score) AS avg_score, COUNT(DISTINCT a.quiz_id) AS quizzes_played, (SELECT q.image FROM quizzes q JOIN quiz_attempts qa ON q.id = qa.quiz_id WHERE qa.user_id = u.id ORDER BY qa.attempt_date DESC LIMIT 1) AS last_quiz_image FROM quiz_attempts a JOIN users u ON a.user_id = u.id GROUP BY u.id, u.username, u.avatar ORDER BY avg_score DESC, quizzes_played DESC LIMIT 10");
                
                mysqli_stmt_execute($stmt_global);
                $result_global = mysqli_stmt_get_result($stmt_global);

                if (mysqli_num_rows($result_global) == 0) {
                    echo '<p class="text-muted small">Aucun joueur n\'a encore terminé de quiz.</p>';
                } else {
                    echo '<ol class="list-unstyled mb-0 quiz-leaderboard">';
                    $rank = 1;
                    while ($player = mysqli_fetch_assoc($result_global)) {
                        $last_quiz_img_html = !empty($player['last_quiz_image']) ? '<img src="' . htmlspecialchars($player['last_quiz_image']) . '" class="rounded" width="60" height="45" style="object-fit: cover;">' : '<span class="rounded bg-light d-inline-block d-flex align-items-center justify-content-center" style="width: 60px; height: 45px;"><i class="fas fa-image text-muted"></i></span>';

                        echo '<li class="d-flex align-items-center mb-2 pb-2 border-bottom"><span class="fw-bold me-2" style="width: 20px;">' . $rank++ . '.</span><img src="' . htmlspecialchars($player['avatar']) . '" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;"><div class="flex-grow-1 me-2"><span class="fw-bold d-block" style="font-size: 0.9em;">' . htmlspecialchars($player['username']) . '<small class="text-muted"> (' . (int)$player['quizzes_played'] . ' quiz)</small></span><small class="text-muted">Score moyen: <span class="badge bg-primary">' . round($player['avg_score']) . '%</span></small></div>' . $last_quiz_img_html . '</li>';
                    }
                    echo '</ol>';
                }
                mysqli_stmt_close($stmt_global);
            }
            break;

        // CAS : FAQ LEADERBOARD
        case 'faq_leaderboard':
            $query_sql = "SELECT id, question FROM faqs WHERE active = 'Yes' ORDER BY position_order ASC LIMIT 10";
            $stmt_faq = mysqli_prepare($connect, $query_sql);
            if ($stmt_faq) {
                mysqli_stmt_execute($stmt_faq);
                $faqs = mysqli_stmt_get_result($stmt_faq);
                if (mysqli_num_rows($faqs) > 0) {
                    echo '<ul class="list-group list-group-flush faq-leaderboard">';
                    while ($faq = mysqli_fetch_assoc($faqs)) {
                        $faq_url = htmlspecialchars($settings['site_url']) . '/faq.php#faq-' . (int)$faq['id'];
                        echo '<li class="list-group-item px-0 py-1" style="font-size: 0.9em;"><a href="' . $faq_url . '" class="text-decoration-none d-block text-truncate"><i class="fas fa-question-circle fa-fw text-muted me-1"></i> ' . htmlspecialchars($faq['question']) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="text-muted small">Aucune question n\'est disponible.</p>';
                }
                mysqli_stmt_close($stmt_faq);
            }
            break;

        // CAS : SHOP / PRODUITS
        case 'shop':
            $config = json_decode($widget_row['config_data'], true);
            $limit = isset($config['count']) ? (int)$config['count'] : 2;
            
            // On sélectionne des produits (is_product='Yes') aléatoirement
            $q_shop = mysqli_query($connect, "SELECT id, title, slug, image, price FROM projects WHERE active='Yes' AND is_product='Yes' ORDER BY RAND() LIMIT $limit");

            if (mysqli_num_rows($q_shop) == 0) {
                echo '<p class="text-muted small p-3">No products available.</p>';
            } else {
                echo '<div class="list-group list-group-flush">';
                while ($prod = mysqli_fetch_assoc($q_shop)) {
                    
                    // Gestion Image (Même logique robuste que le reste de votre site)
                    $p_img = 'assets/img/project-no-image.png';
                    if (!empty($prod['image'])) {
                        $clean = str_replace('../', '', $prod['image']);
                        if (file_exists($clean)) { $p_img = $clean; }
                    }
                    
                    // Construction URL absolue
                    $link = $settings['site_url'] . '/project?name=' . htmlspecialchars($prod['slug']);
                    $img_url = $settings['site_url'] . '/' . $p_img;

                    echo '
                    <a href="' . $link . '" class="list-group-item list-group-item-action d-flex align-items-center p-3 border-0">
                        <div class="flex-shrink-0 me-3">
                            <img src="' . htmlspecialchars($img_url) . '" class="rounded" width="60" height="60" style="object-fit: cover;" onerror="this.src=\''.$settings['site_url'].'/assets/img/project-no-image.png\';">
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="mb-1 text-truncate small fw-bold text-dark" style="line-height: 1.2;">
                                ' . htmlspecialchars($prod['title']) . '
                            </h6>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-success fw-bold">$' . number_format($prod['price'], 2) . '</span>
                                <span class="badge bg-light text-success border" style="font-size: 0.65rem;">View</span>
                            </div>
                        </div>
                    </a>';
                }
                // Lien "Voir tout" en bas du widget
                echo '<div class="p-2"><a href="' . $settings['site_url'] . '/shop" class="btn btn-success btn-sm w-100 text-white shadow-sm">Visit Full Shop <i class="fas fa-arrow-right ms-1"></i></a></div>';
                echo '</div>';
            }
            break;
    } // Fin switch

    // --- 3. FERMETURE DU CONTENEUR (SÉCURISÉE) ---
    if ($wrapper_open) {
         echo '
              </div>
        </div>
        ';
    }
}

// Fonction send_email (Inclure tout votre code send_email ici)
function send_email($to, $subject, $body, $altBody = '') {
    global $settings; // On récupère vos réglages depuis la BDD

    $mail = new PHPMailer(true);
    $result = ['success' => false, 'message' => ''];

    try {
        // 1. Configuration du Serveur
        if ($settings['mail_protocol'] == 'smtp') {
            $mail->isSMTP();
            $mail->Host       = $settings['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtp_user'];
            $mail->Password   = $settings['smtp_pass'];
            
            // Gestion du cryptage (TLS/SSL)
            if ($settings['smtp_enc'] == 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($settings['smtp_enc'] == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port       = (int)$settings['smtp_port'];
        } else {
            // Fallback sur mail() classique si SMTP n'est pas choisi
            $mail->isMail();
        }

        // 2. Expéditeur et Destinataire
        $fromName = !empty($settings['mail_from_name']) ? $settings['mail_from_name'] : $settings['sitename'];
        $fromEmail = !empty($settings['mail_from_email']) ? $settings['mail_from_email'] : $settings['email'];
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($fromEmail, $fromName);

        // 3. Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        // Génération automatique du texte brut si non fourni
        if (empty($altBody)) {
            $mail->AltBody = strip_tags($body);
        } else {
            $mail->AltBody = $altBody;
        }

        $mail->send();
        $result['success'] = true;
        $result['message'] = 'Message has been sent';

    } catch (Exception $e) {
        $result['success'] = false;
        $result['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    return $result;
}

function optimize_and_save_image($temp_file, $output_file_base, $max_width = 1200, $quality = 85) {
    
    $image_info = @getimagesize($temp_file);
    if (!$image_info) {
        return false; // Ce n'est pas une image valide
    }
    
    $mime = $image_info['mime'];
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    
    // Charger l'image en mémoire
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($temp_file);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($temp_file);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($temp_file);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($temp_file);
            break;
        default:
            return false; // Type de fichier non supporté
    }
    
    if ($image === false) {
        return false; // Échec du chargement de l'image
    }
    
    // Calculer les nouvelles dimensions
    $new_width = $original_width;
    $new_height = $original_height;
    
    if ($original_width > $max_width) {
        $ratio = $max_width / $original_width;
        $new_width = $max_width;
        $new_height = $original_height * $ratio;
    }
    
    // Créer une nouvelle image (canvas)
    $new_image = imagecreatetruecolor((int)$new_width, (int)$new_height);
    
    // Gérer la transparence (pour PNG/GIF/WEBP) en remplissant avec un fond blanc
    $white = imagecolorallocate($new_image, 255, 255, 255);
    imagefill($new_image, 0, 0, $white);
    
    // Redimensionner et copier l'ancienne image sur la nouvelle
    imagecopyresampled(
        $new_image, $image,
        0, 0, 0, 0,
        (int)$new_width, (int)$new_height,
        (int)$original_width, (int)$original_height
    );
    
    // Détruire l'image originale de la mémoire
    imagedestroy($image);
    
    // Définir le chemin de sortie final avec l'extension .jpg
    $final_output_file = $output_file_base . '.jpg';
    
    // Sauvegarder la nouvelle image en tant que JPEG
    if (!@imagejpeg($new_image, $final_output_file, $quality)) {
         imagedestroy($new_image);
         return false;
    }
    
    // Libérer la mémoire
    imagedestroy($new_image);
    
    // Retourner le nouveau nom de fichier (avec l'extension .jpg)
    return $final_output_file;
}

// Fonction pour afficher une publicité
function render_ad($size, $wrapper = false) {
    global $connect, $settings;
    
    $stmt = mysqli_prepare($connect, "SELECT * FROM ads WHERE active='Yes' AND ad_size = ? ORDER BY RAND() LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $size);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Si une publicité est trouvée
    if ($row = mysqli_fetch_assoc($result)) {
        $tracking_url = $settings['site_url'] . '/click_ad.php?id=' . $row['id'];
        
        // 1. Si l'option Wrapper est activée, on ouvre la Card
        if ($wrapper) {
            echo '<div class="card mb-3"><div class="card-body text-center">';
        }

        // 2. Affichage de la publicité
        echo '
        <div class="ad-container text-center my-3">
            <a href="' . htmlspecialchars($tracking_url) . '" target="_blank" rel="nofollow">
                <img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '" class="img-fluid shadow-sm rounded" style="max-width:100%; height:auto;">
            </a>
        </div>';

        // 3. Si l'option Wrapper est activée, on ferme la Card
        if ($wrapper) {
            echo '</div></div>';
        }
    }
    // SINON (si pas de pub), on ne fait RIEN (donc pas de boîte vide)
    
    mysqli_stmt_close($stmt);
}

// --- TRACKER DE VISITES ---
function track_visitor() {
    global $connect;
    
    // --- EXCLUSION DES MEMBRES DE L'ÉQUIPE ---
    global $logged, $rowu; 
    
    // Si l'utilisateur est connecté ET qu'il est Admin OU Éditeur
    if (isset($logged) && $logged == 'Yes' && isset($rowu['role'])) {
        if ($rowu['role'] == 'Admin' || $rowu['role'] == 'Editor') {
            return; // On arrête tout, pas de tracking
        }
    }
    // ------------------------------------------

    // 1. Ne pas tracker les pages admin ou les fichiers AJAX
    $current_uri = $_SERVER['REQUEST_URI'];
    if (strpos($current_uri, '/admin/') !== false || strpos($current_uri, 'ajax') !== false) {
        return;
    }

    // ... (Le reste de la fonction reste identique : IP, User Agent, Insert...) ...
    $ip = $_SERVER['REMOTE_ADDR'];
    $page = $_SERVER['REQUEST_URI'];
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/bot|crawl|curl|dataprovider|search|get|spider|find|java|majesticsEO|google|yahoo|teoma|contaxe|yandex|libwww|weger|wget/i', $agent)) {
        return;
    }

    $stmt = mysqli_prepare($connect, "INSERT INTO visitor_analytics (ip_address, page_url, referrer, user_agent, visit_date) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssss", $ip, $page, $referrer, $agent);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// --- SYSTÈME DE LOGS (MOUCHARD) ---
function log_activity($action_type, $details) {
    global $connect, $rowu; // On utilise $rowu pour savoir qui est connecté
    
    // Si personne n'est connecté (ex: tentative de login échouée), on met 0 ou l'ID si dispo
    $user_id = isset($rowu['id']) ? $rowu['id'] : 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = mysqli_prepare($connect, "INSERT INTO activity_logs (user_id, action_type, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $action_type, $details, $ip);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// --- SYSTÈME DE CACHE FICHIER ---

// 1. Lire le cache
function get_cache($key, $duration = 3600) {
    // Clé hashée pour le nom de fichier
    $file = __DIR__ . '/../cache/' . md5($key) . '.html';
    
    // Si le fichier existe ET qu'il est récent (moins de $duration secondes)
    if (file_exists($file) && (time() - filemtime($file) < $duration)) {
        return file_get_contents($file);
    }
    return false;
}

// 2. Écrire le cache
function save_cache($key, $content) {
    $file = __DIR__ . '/../cache/' . md5($key) . '.html';
    file_put_contents($file, $content);
}

// 3. Vider le cache (Global ou Spécifique)
function clear_site_cache() {
    $files = glob(__DIR__ . '/../cache/*.html');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file); // Supprime le fichier
        }
    }
    // Log l'action pour la sécurité
    if(function_exists('log_activity')) { log_activity("System", "Cache cleared automatically."); }
}
?>