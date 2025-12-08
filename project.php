<?php
include "core.php";

// 1. Récupération du Projet
$slug = $_GET['name'] ?? '';
if (empty($slug)) {
    echo '<meta http-equiv="refresh" content="0; url=projects">';
    exit();
}

$stmt = mysqli_prepare($connect, "SELECT p.*, u.username, u.avatar, c.category as cat_name, c.slug as cat_slug 
    FROM projects p 
    LEFT JOIN users u ON p.author_id = u.id 
    LEFT JOIN project_categories c ON p.project_category_id = c.id
    WHERE p.slug=? AND p.active='Yes' LIMIT 1");

mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) == 0) {
    echo '<meta http-equiv="refresh" content="0; url=projects">';
    exit();
}

$project = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Mise à jour des vues
mysqli_query($connect, "UPDATE projects SET views = views + 1 WHERE id = " . $project['id']);

// Préparation des données
$page_title = $project['title']; // Pour le SEO (si header.php est adapté)
head(); 

// Nettoyage Image
$img_src = 'assets/img/project-no-image.png';
if (!empty($project['image'])) {
    $clean = str_replace('../', '', $project['image']);
    if (file_exists($clean)) { $img_src = $clean; }
}

// Badge Difficulté
$diff_color = 'secondary';
switch($project['difficulty']) {
    case 'Easy': $diff_color = 'success'; break;
    case 'Intermediate': $diff_color = 'primary'; break;
    case 'Advanced': $diff_color = 'warning'; break;
    case 'Expert': $diff_color = 'danger'; break;
}

// Initialiser HTML Purifier pour le contenu riche
$purifier = get_purifier();
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 overflow-hidden mb-4">
                <div class="row g-0">
                    <div class="col-md-8 position-relative bg-light" style="min-height: 300px;">
                         <img src="<?php echo htmlspecialchars($img_src); ?>" class="w-100 h-100" style="object-fit: cover; position: absolute; top:0; left:0;" alt="Cover">
                    </div>
                    
                    <div class="col-md-4 bg-white p-4 d-flex flex-column justify-content-center">
                        <div class="mb-2">
                            <?php if(!empty($project['cat_name'])): ?>
                                <a href="projects?category=<?php echo htmlspecialchars($project['cat_slug']); ?>" class="text-decoration-none badge bg-light text-primary border me-1">
                                    <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($project['cat_name']); ?>
                                </a>
                            <?php endif; ?>
                            
                            <span class="badge bg-<?php echo $diff_color; ?>"><?php echo htmlspecialchars($project['difficulty']); ?></span>
                            
                            <?php if(!empty($project['duration'])): ?>
                                <span class="badge bg-light text-dark border ms-1"><i class="far fa-clock"></i> <?php echo htmlspecialchars($project['duration']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($project['title']); ?></h1>
                        <p class="text-muted lead" style="font-size: 1.1rem;"><?php echo htmlspecialchars($project['pitch']); ?></p>
                        
                        <div class="d-flex align-items-center mt-auto pt-3 border-top">
                            <?php 
                            $avatar = !empty($project['avatar']) ? str_replace('../', '', $project['avatar']) : 'assets/img/avatar.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($avatar); ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                            <div>
                                <small class="text-muted d-block">Created by</small>
                                <span class="fw-bold">
                                    <a href="user.php?name=<?php echo urlencode($project['username']); ?>" class="text-dark text-decoration-none">
                                        <?php echo htmlspecialchars($project['username']); ?>
                                    </a>
                                </span>
                            </div>
                            <div class="ms-auto text-end">
                                <small class="text-muted d-block">Published</small>
                                <span class="fw-bold"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-8">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-book-reader text-primary me-2"></i> The Story</h4>
                </div>
                <div class="card-body blog-content">
                    <?php echo $purifier->purify(html_entity_decode($project['story'])); ?>
                </div>
            </div>

            <?php if(!empty($project['schematics_link']) || !empty($project['code_link'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-paperclip text-warning me-2"></i> Attachments</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if(!empty($project['schematics_link'])): ?>
                        <div class="col-md-6">
                            <a href="<?php echo htmlspecialchars($project['schematics_link']); ?>" target="_blank" class="btn btn-outline-dark w-100 p-3 text-start">
                                <i class="fas fa-microchip fa-2x float-end opacity-25"></i>
                                <strong>Schematics</strong><br>
                                <small>View Circuit / Diagrams</small>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($project['code_link'])): ?>
                        <div class="col-md-6">
                            <a href="<?php echo htmlspecialchars($project['code_link']); ?>" target="_blank" class="btn btn-outline-primary w-100 p-3 text-start">
                                <i class="fab fa-github fa-2x float-end opacity-25"></i>
                                <strong>Source Code</strong><br>
                                <small>View Repository</small>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="col-lg-4">

        <?php 
            // --- BLOC BOUTIQUE (E-COMMERCE) ---
            if (isset($project['is_product']) && $project['is_product'] == 'Yes'): 
                
                // Couleurs du stock
                $stock_color = 'success';
                $stock_icon = 'check-circle';
                $btn_state = '';
                
                if ($project['stock_status'] == 'Low Stock') { $stock_color = 'warning'; $stock_icon = 'exclamation-triangle'; }
                if ($project['stock_status'] == 'Out of Stock') { $stock_color = 'secondary'; $stock_icon = 'times-circle'; $btn_state = 'disabled'; }
                if ($project['stock_status'] == 'Pre-order') { $stock_color = 'info'; $stock_icon = 'clock'; }
            ?>
            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body p-4 text-center">
                    <h5 class="text-uppercase text-muted small fw-bold mb-3">Available for purchase</h5>
                    
                    <h2 class="display-4 fw-bold text-primary mb-3">
                        $<?php echo number_format($project['price'], 2); ?>
                    </h2>
                    
                    <div class="mb-4">
                        <span class="badge bg-<?php echo $stock_color; ?> px-3 py-2 rounded-pill">
                            <i class="fas fa-<?php echo $stock_icon; ?> me-1"></i> <?php echo $project['stock_status']; ?>
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?php echo htmlspecialchars($project['buy_link']); ?>" target="_blank" class="btn btn-success btn-lg fw-bold <?php echo $btn_state; ?> shadow-sm">
                            <i class="fas fa-shopping-cart me-2"></i> BUY NOW
                        </a>
                        <small class="text-muted mt-2">Secure payment via external platform</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- --- FIN BLOC BOUTIQUE (E-COMMERCE) --- -->

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-boxes text-secondary me-2"></i> Things used in this project</h5>
                </div>
                <div class="card-body">
                    
                    <?php
                    // Fonction locale pour afficher une liste JSON
                    function render_bom_list($title, $json) {
                        $items = json_decode($json, true);
                        if (!empty($items) && is_array($items)) {
                            echo '<h6 class="fw-bold mt-3 mb-2 border-bottom pb-2">'.$title.'</h6>';
                            echo '<ul class="list-group list-group-flush mb-3">';
                            foreach ($items as $item) {
                                $img = !empty($item['img']) ? $item['img'] : 'assets/img/no-image-icon.png';
                                $link_start = !empty($item['link']) ? '<a href="'.$item['link'].'" target="_blank" class="text-decoration-none text-dark">' : '';
                                $link_end = !empty($item['link']) ? '</a>' : '';
                                $cart_btn = !empty($item['link']) ? '<a href="'.$item['link'].'" target="_blank" class="btn btn-outline-secondary btn-sm ms-2"><i class="fas fa-shopping-cart"></i></a>' : '';
                                
                                echo '
                                <li class="list-group-item px-0 d-flex align-items-center justify-content-between border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded border p-1 me-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                            <img src="'.$img.'" style="max-width:100%; max-height:100%;" onerror="this.src=\'assets/img/no-image-icon.png\'">
                                        </div>
                                        <div>
                                            '.$link_start.'<span class="fw-bold">'.htmlspecialchars($item['name']).'</span>'.$link_end.'
                                            <div class="small text-muted">'.htmlspecialchars($item['qty']).'</div>
                                        </div>
                                    </div>
                                    '.$cart_btn.'
                                </li>';
                            }
                            echo '</ul>';
                        }
                    }

                    render_bom_list('Hardware components', $project['hardware_parts']);
                    render_bom_list('Software apps and online services', $project['software_apps']);
                    render_bom_list('Hand tools and fabrication machines', $project['hand_tools']);
                    ?>
                    
                </div>
            </div>

            <?php if(!empty($project['team_credits'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-users text-info me-2"></i> Team & Credits</h5>
                </div>
                <div class="card-body">
                    <?php echo $purifier->purify(html_entity_decode($project['team_credits'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <?php
                    $likes = get_project_like_count($project['id']);
                    $has_liked = check_user_has_liked_project($project['id']);
                    $like_class = $has_liked ? 'btn-primary' : 'btn-outline-primary';
                    $like_text = $has_liked ? 'Liked' : 'Like';
                    
                    $has_fav = check_user_has_favorited_project($project['id']);
                    $fav_class = $has_fav ? 'btn-warning' : 'btn-outline-warning';
                    $fav_icon = $has_fav ? 'fas' : 'far';
                    ?>
                    
                    <button class="btn <?php echo $like_class; ?> me-2" id="project-like-btn" data-id="<?php echo $project['id']; ?>">
                        <i class="fas fa-thumbs-up me-1"></i> <span id="like-text"><?php echo $like_text; ?></span> (<span id="like-count"><?php echo $likes; ?></span>)
                    </button>
                    
                    <?php if($logged == 'Yes'): ?>
                    <button class="btn <?php echo $fav_class; ?>" id="project-fav-btn" data-id="<?php echo $project['id']; ?>">
                        <i class="<?php echo $fav_icon; ?> fa-bookmark me-1"></i> <span id="fav-text"><?php echo $has_fav ? 'Saved' : 'Save'; ?></span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">Share this project</h6>
                    <div id="share"></div> <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_page_url); ?>" target="_blank" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-facebook-f"></i></a>
                     <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($project['title']); ?>&url=<?php echo urlencode($current_page_url); ?>" target="_blank" class="btn btn-sm btn-info text-white rounded-circle"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // LIKE
    $('#project-like-btn').click(function() {
        var btn = $(this);
        var id = btn.data('id');
        
        $.post('ajax_interactions.php', { action: 'like_project', id: id }, function(res) {
            if(res.status === 'success') {
                $('#like-count').text(res.count);
                if(res.liked) {
                    btn.removeClass('btn-outline-primary').addClass('btn-primary');
                    $('#like-text').text('Liked');
                } else {
                    btn.removeClass('btn-primary').addClass('btn-outline-primary');
                    $('#like-text').text('Like');
                }
            }
        }, 'json');
    });

    // FAVORITE
    $('#project-fav-btn').click(function() {
        var btn = $(this);
        var id = btn.data('id');
        var icon = btn.find('i');
        
        $.post('ajax_interactions.php', { action: 'favorite_project', id: id }, function(res) {
            if(res.status === 'success') {
                if(res.favorited) {
                    btn.removeClass('btn-outline-warning').addClass('btn-warning');
                    icon.removeClass('far').addClass('fas');
                    $('#fav-text').text('Saved');
                } else {
                    btn.removeClass('btn-warning').addClass('btn-outline-warning');
                    icon.removeClass('fas').addClass('far');
                    $('#fav-text').text('Save');
                }
            }
        }, 'json');
    });
});
</script>
<?php footer(); ?>