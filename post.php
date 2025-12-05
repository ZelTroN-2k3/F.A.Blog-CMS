<?php
include "core.php";
// require_once 'vendor/htmlpurifier/library/HTMLPurifier.auto.php';

// =================================================================================
// 1. LOGIQUE SEO & RECUPERATION ARTICLE (Déplacé au début)
// =================================================================================
$slug = $_GET['name'] ?? '';

// Variables par défaut (si aucun article trouvé ou page d'accueil)
$page_title = $settings['sitename'];
$page_desc  = $settings['description'];
$row = null; // Contiendra les données de l'article

if (!empty($slug)) {
    // On récupère l'article + infos auteur
    $query_post = "
        SELECT p.*, u.username, u.avatar, u.role 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.active='Yes' AND p.publish_at <= NOW() AND p.slug=?
    ";
    $stmt = mysqli_prepare($connect, $query_post);
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $runq = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($runq) > 0) {
        $row = mysqli_fetch_assoc($runq);
        
        // --- LOGIQUE SEO PRÉCISE ---
        // 1. Titre : Utilise meta_title si dispo, sinon le titre de l'article
        $page_title = !empty($row['meta_title']) ? $row['meta_title'] : $row['title'];
        
        // 2. Description : Utilise meta_description si dispo, sinon extrait du contenu
        if (!empty($row['meta_description'])) {
            $page_desc = $row['meta_description'];
        } else {
            // Nettoyage du contenu pour créer un extrait propre
            $clean_content = strip_tags(html_entity_decode($row['content']));
            // On coupe à 160 caractères (standard SEO) sans couper un mot
            $page_desc = substr($clean_content, 0, 160) . '...';
        }

        // UPDATE VUES
        $stmt_update = mysqli_prepare($connect, "UPDATE `posts` SET views = views + 1 WHERE active='Yes' AND slug=?");
        mysqli_stmt_bind_param($stmt_update, "s", $slug);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

    } else {
        // Article introuvable
        echo '<meta http-equiv="refresh" content="0; url=blog">';
        exit;
    }
} else {
    // Pas de slug
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit;
}

// =================================================================================
// 2. AFFICHAGE (HEAD & HEADER)
// =================================================================================

// On passe les variables SEO à la fonction head()
// NOTE: Vous devrez peut-être adapter votre fonction head() dans core.php pour accepter ces arguments
head($page_title, $page_desc);

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Initialiser HTML Purifier
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
?>

    <div class="col-md-8 mb-3">
<?php
// On utilise les données récupérées tout en haut ($row)
$post_id     = $row['id'];
$post_slug   = $row['slug'];

// --- VERIF FAVORIS ---
$user_has_favorited = false;
if ($logged == 'Yes') {
    $stmt_fav_check = mysqli_prepare($connect, "SELECT id FROM user_favorites WHERE user_id = ? AND post_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_fav_check, "ii", $rowu['id'], $post_id);
    mysqli_stmt_execute($stmt_fav_check);
    $result_fav_check = mysqli_stmt_get_result($stmt_fav_check);
    if (mysqli_num_rows($result_fav_check) > 0) {
        $user_has_favorited = true;
    }
    mysqli_stmt_close($stmt_fav_check);
}

echo '
        <div class="card shadow-sm">
            <div class="col-md-12">
                ';
            
            // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
            $image_html = "";
            
            // Nettoyage des chemins
            $post_img_path = str_replace('../', '', $row['image']);
            $default_img_path = 'assets/img/no-image.png';

            // NIVEAU 1 : L'image de l'article existe physiquement
            if (!empty($row['image']) && file_exists($post_img_path)) {
                $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" 
                               alt="' . htmlspecialchars($row['title']) . '" 
                               class="card-img-top" 
                               width="100%" height="auto" 
                               style="max-height: 400px; object-fit: cover;">';
            } 
            // NIVEAU 2 : Pas d'image article, mais no-image.png existe
            elseif (file_exists($default_img_path)) {
                $image_html = '<img src="' . $default_img_path . '" 
                               alt="No Image" 
                               class="card-img-top" 
                               width="100%" height="auto" 
                               style="max-height: 400px; object-fit: cover;">';
            }
            // NIVEAU 3 : Rien n'existe -> Affichage du SVG de secours
            else {
                // Note : J\'ai mis height="400" pour que le bloc gris ait une belle taille sur la page article
                $image_html = '<svg class="bd-placeholder-img card-img-top" width="100%" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
                <rect width="100%" height="100%" fill="#55595c"/>
                <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle" font-size="2rem">No Image</text></svg>';
            }

            echo $image_html;
            
            echo '
            ';
echo '
            <div class="card-body">
                
				<div class="mb-3">
					<i class="fas fa-chevron-right"></i> Category: <a href="category?name=' . post_categoryslug($row['category_id']) . '" class="text-primary">' . post_category($row['category_id']) . '</a>
				</div>
				
				<div class="d-flex justify-content-between align-items-center mb-3">
				    <h3 class="card-title fw-bold mb-0">' . strip_tags(html_entity_decode($row['title'])) . '</h3>';
				    
				    // --- BOUTON MODIFIER (Visible pour Admin ou Auteur) ---
                    $is_mine = ($logged == 'Yes' && $row['author_id'] == $rowu['id']);
                    $is_admin = ($logged == 'Yes' && $rowu['role'] == 'Admin');
                    
                    if ($is_admin || $is_mine) {
                        echo '<a href="admin/edit_post.php?id=' . $post_id . '" class="btn btn-outline-secondary btn-sm ms-3" title="Edit this post"><i class="fas fa-edit"></i> Edit</a>';
                    }
                    // ----------------------------------------------------
                    
                echo '</div>
				
				<div class="d-flex align-items-center mb-3 text-muted border-bottom pb-3">
				    ';
				    
				    // --- LOGIQUE AUTEUR (Avatar + Badge) ---
				    $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
				    $avatar_src = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
				    
				    // Badge Rôle
				    $role_badge = '';
				    if (isset($row['role'])) {
				        if ($row['role'] == 'Admin') $role_badge = '<span class="badge bg-success ms-1" style="font-size: 0.7em;">Admin</span>';
				        elseif ($row['role'] == 'Editor') $role_badge = '<span class="badge bg-primary ms-1" style="font-size: 0.7em;">Editor</span>';
				    }
				    
				    echo '
				    <img src="' . htmlspecialchars($avatar_src) . '" class="rounded-circle me-2" width="40" height="40" style="object-fit:cover;">
				    
					<small>
						Posted by <b><a href="author.php?username=' . urlencode($author_name) . '" class="text-decoration-none text-dark">' . $author_name . '</a></b> ' . $role_badge . '
						<span class="mx-2 text-muted">|</span>
						<i class="far fa-calendar-alt"></i> ' . date($settings['date_format'], strtotime($row['created_at'])) . '
                        
                        <span class="ms-3 border-start ps-3">
                           ' . get_reading_time($row['content']) . '
                        </span>
                    </small>
                    
					<small class="ms-auto"> 	
						<i class="fa fa-eye me-1"></i> ' . $row['views'] . ' views
					</small>
				</div>
				
                <div class="blog-content">
                    ' . $purifier->purify(html_entity_decode($row['content'])) . '
                </div>
				<hr />
				
				';
                // Affichage des liens de téléchargement
                if (!empty($row['download_link']) || !empty($row['github_link'])) {
                    echo '<h5><i class="fas fa-download"></i> Downloads</h5>';
                    
                    if (!empty($row['download_link'])) {
                        echo '
                        <a href="' . htmlspecialchars($row['download_link']) . '" class="btn btn-primary me-2 mb-2" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-file-archive"></i> Download (.zip/.rar)
                        </a>';
                    }
                    
                    if (!empty($row['github_link'])) {
                        echo '
                        <a href="' . htmlspecialchars($row['github_link']) . '" class="btn btn-dark me-2 mb-2" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-github"></i> View on GitHub
                        </a>';
                    }
                    echo '<hr />';
                }
                
                // --- TAGS ---
                $stmt_get_tags = mysqli_prepare($connect, "
                    SELECT DISTINCT t.name, t.slug 
                    FROM tags t
                    JOIN post_tags pt ON t.id = pt.tag_id
                    WHERE pt.post_id = ?
                ");
                mysqli_stmt_bind_param($stmt_get_tags, "i", $post_id);
                mysqli_stmt_execute($stmt_get_tags);
                $result_tags = mysqli_stmt_get_result($stmt_get_tags);
                
                if (mysqli_num_rows($result_tags) > 0) {
                    echo '<h5><i class="fas fa-tags"></i> Tags</h5>';
                    echo '<div class="mb-3">';
                    while ($row_tag = mysqli_fetch_assoc($result_tags)) {
                        echo '<a href="tag.php?name=' . htmlspecialchars($row_tag['slug']) . '" class="btn btn-outline-secondary btn-sm me-1 mb-1">
                                <i class="fas fa-tag"></i> ' . htmlspecialchars($row_tag['name']) . '
                              </a>';
                    }
                    echo '</div><hr />';
                }
                mysqli_stmt_close($stmt_get_tags);
                
                echo '
                <h5 class="mt-2"><i class="fas fa-share-alt-square"></i> Share</h5>
				<div id="share" class="mb-3" style="font-size: 14px;"></div>
				
				';

				// Like / Favoris
				$total_likes = get_post_like_count($post_id);
				$user_has_liked = check_user_has_liked($post_id);
				
				$like_class = $user_has_liked ? 'btn-primary' : 'btn-outline-primary';
				$like_text = $user_has_liked ? 'Aimé' : 'J\'aime';
				?>
				
				<button class="btn <?php echo $like_class; ?> mt-2" id="like-button" data-post-id="<?php echo $post_id; ?>">
					<i class="fas fa-thumbs-up"></i>
					<span id="like-text"><?php echo $like_text; ?></span>
					(<span id="like-count"><?php echo $total_likes; ?></span>)
				</button>
				
				<?php
                if ($logged == 'Yes'):
                    $fav_class = $user_has_favorited ? 'btn-warning' : 'btn-outline-warning';
                    $fav_icon = $user_has_favorited ? 'fas fa-bookmark' : 'far fa-bookmark';
                    $fav_text = $user_has_favorited ? 'Enregistré' : 'Enregistrer';
                ?>
                    <button class="btn <?php echo $fav_class; ?> mt-2 ms-2" id="favorite-button" data-post-id="<?php echo $post_id; ?>">
                        <i class="<?php echo $fav_icon; ?>"></i>
                        <span id="favorite-text"><?php echo $fav_text; ?></span>
                    </button>
                <?php endif; ?>
                <hr />

				<?php
				
				// Articles liés
                $stmt_find_tags = mysqli_prepare($connect, "SELECT tag_id FROM post_tags WHERE post_id = ?");
                mysqli_stmt_bind_param($stmt_find_tags, "i", $post_id);
                mysqli_stmt_execute($stmt_find_tags);
                $result_find_tags = mysqli_stmt_get_result($stmt_find_tags);
                
                $tag_ids = [];
                while ($tag_row = mysqli_fetch_assoc($result_find_tags)) {
                    $tag_ids[] = $tag_row['tag_id'];
                }
                mysqli_stmt_close($stmt_find_tags);

                if (!empty($tag_ids)) {
                    $tag_placeholders = implode(',', array_fill(0, count($tag_ids), '?')); 
                    $types = str_repeat('i', count($tag_ids)); 
                    $params = $tag_ids;
                    
                    $types .= 'ii';
                    $params[] = $post_id;
                    $params[] = 4;
                    
                    $sql_related = "
                        SELECT p.*, COUNT(pt.tag_id) AS common_tags
                        FROM post_tags pt
                        JOIN posts p ON pt.post_id = p.id
                        WHERE pt.tag_id IN ($tag_placeholders)
                          AND p.id != ?
                          AND p.active = 'Yes' AND p.publish_at <= NOW()
                        GROUP BY p.id
                        ORDER BY common_tags DESC, p.created_at DESC
                        LIMIT ?
                    ";
                    
                    $stmt_related = mysqli_prepare($connect, $sql_related);
                    $bind_params = array();
                    $bind_params[] = $types;
                    foreach ($params as $key => $value) {
                        $bind_params[] = &$params[$key];
                    }
                    call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt_related), $bind_params));
                    mysqli_stmt_execute($stmt_related);
                    $result_related = mysqli_stmt_get_result($stmt_related);

                    if (mysqli_num_rows($result_related) > 0) {
                        echo '<h5 class="mt-3"><i class="fas fa-stream"></i> Related Articles</h5>';
                        echo '<div class="row">';
                        while ($related_post = mysqli_fetch_assoc($result_related)) {
                            
                            // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
                            $image_html = '';
                            $post_img_path = str_replace('../', '', $related_post['image']);
                            $default_img_path = 'assets/img/no-image.png';

                            // NIVEAU 1
                            if (!empty($related_post['image']) && file_exists($post_img_path)) {
                                $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($related_post['title']) . '" class="card-img-top" width="100%" height="150" style="object-fit: cover;">';
                            } 
                            // NIVEAU 2
                            elseif (file_exists($default_img_path)) {
                                $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="card-img-top" width="100%" height="150" style="object-fit: cover;">';
                            }
                            // NIVEAU 3
                            else {
                                $image_html = '<svg class="bd-placeholder-img card-img-top" width="100%" height="150" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
                                <rect width="100%" height="100%" fill="#55595c"/>
                                <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle">No Image</text></svg>';
                            }
                            
                            echo '
                                <div class="col-md-6 mb-3"> 
                                    <div class="card shadow-sm h-100 d-flex flex-column">
                                        <a href="post?name=' . htmlspecialchars($related_post['slug']) . '">'. $image_html .'</a>
                                        <div class="card-body d-flex flex-column flex-grow-1 py-3">
                                            <a href="post?name=' . htmlspecialchars($related_post['slug']) . '" class="text-decoration-none"><h6 class="card-title text-primary">' . htmlspecialchars($related_post['title']) . '</h6></a>
                                            <small class="text-muted mb-2"><i class="far fa-calendar-alt"></i> ' . date($settings['date_format'], strtotime($related_post['created_at'])) . '</small>
                                            <p class="card-text mt-2">' . short_text(strip_tags(html_entity_decode($related_post['content'])), 80) . '</p>
                                            <a href="post?name=' . htmlspecialchars($related_post['slug']) . '" class="btn btn-sm btn-outline-primary col-12 mt-auto">Read more</a>
                                        </div>
                                    </div>
                                </div>';
                        }
                        echo '</div><hr />';
                    }
                    mysqli_stmt_close($stmt_related);
                }
                echo '
                <h5 class="mt-2" id="comments">
					<i class="fa fa-comments"></i> Comments (<span id="comments-count">' . post_commentscount($row['id']) . '</span>)
				</h5>
';
?>

<?php
echo '<div id="comment-list-container">';
$stmt_count_main = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM comments WHERE post_id=? AND approved='Yes' AND parent_id = 0");
mysqli_stmt_bind_param($stmt_count_main, "i", $post_id);
mysqli_stmt_execute($stmt_count_main);
$q_count = mysqli_stmt_get_result($stmt_count_main);
$count_row = mysqli_fetch_assoc($q_count);
$count_main = $count_row['count'];
mysqli_stmt_close($stmt_count_main);

if ($count_main <= 0) {
    echo '<div class="alert alert-info" id="no-comments-alert">There are no comments yet.</div>';
} else {
    display_comments($post_id, 0, 0);
}
echo '</div>'; 
?>                                  
                    
<?php
$is_guest = ($logged == 'No' && $settings['comments'] == 'guests');
?>
<div id="comment-form-container" class="mt-4 border-top pt-4" data-post-id="<?php echo $post_id; ?>" data-is-guest-commenting="<?php echo $is_guest ? 'true' : 'false'; ?>"> 
    <h5 class="leave-comment-title"><i class="fas fa-reply"></i> Leave A Comment</h5>
    <div id="comment-form-messages" class="mb-3"></div>
                        
<?php
$guest = 'No';
$cancomment = ($logged == 'Yes' || ($logged == 'No' && $settings['comments'] == 'guests')) ? 'Yes' : 'No';

if ($cancomment == 'Yes') {
?>
    <form name="comment_form" id="main-comment-form" method="post" action="ajax_submit_comment.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="parent_id" id="parent_id" value="0">
        <input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>">
        
        <?php if ($logged == 'No') { $guest = 'Yes'; ?>
            <div class="form-group mb-3">
                <label for="comment-author"><i class="fa fa-user"></i> Name:</label>
                <input type="text" name="author" id="comment-author" value="" class="form-control" required />
            </div>
        <?php } ?>
        
        <div id="comment-message" class="form-group mb-3">
            <label for="comment"><i class="fa fa-comment"></i> Comment:</label>
            <textarea name="comment" id="comment" rows="5" class="form-control" maxlength="1000" oninput="countText()" required></textarea>
            <small class="form-text text-muted"><i>Characters left: </i><span id="characters">1000</span></small>
        </div>
        
        <?php if ($logged == 'No') { ?>
            <center><div class="g-recaptcha mb-3" data-sitekey="<?php echo $settings['gcaptcha_sitekey']; ?>" id="recaptcha-widget"></div></center>
        <?php } ?>
        
        <input type="submit" name="post" id="submit-comment-btn" class="btn btn-primary col-12" value="Post" />
        <button type="button" class="btn btn-secondary col-12 mt-2" id="cancel-reply-btn" style="display:none;" onclick="cancelReply()">
            <i class="fas fa-times"></i> Cancel Reply
        </button>
    </form>
<?php
} else {
    echo '<div class="alert alert-info">Please <strong><a href="login"><i class="fas fa-sign-in-alt"></i> Sign In</a></strong> to be able to post a comment.</div>';
}
?>

        <?php render_ad('468x60'); ?> <?php render_ad('728x90'); ?> 
    </div>
    </div></div></div></div>

<style>
#like-button, #favorite-button { transition: all 0.3s ease; min-width: 100px; }
#like-button .fa-thumbs-up, #favorite-button .fa-bookmark { margin-right: 8px; }
.blog-content pre code { background-color: #282c34 !important; padding: 1em; border-radius: 5px; display: block; overflow-x: auto; color: #abb2bf; }
</style>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>