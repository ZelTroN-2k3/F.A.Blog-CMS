<?php
function sidebar() {
	
    global $connect, $settings;
?>
	<div id="sidebar" class="col-md-4">

<?php
    // --- WIDGET SONDAGE (POLL) ---
    // (Votre code original pour les SONDAGES reste ici, inchangé)
    $poll_q = mysqli_query($connect, "SELECT * FROM polls WHERE active='Yes' ORDER BY id DESC LIMIT 1");
    
    if (mysqli_num_rows($poll_q) > 0) {
        $poll = mysqli_fetch_assoc($poll_q);
        $poll_id = $poll['id'];
        
        // Vérifier si l'utilisateur a déjà voté (Cookie ou IP)
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $has_voted = false;
        if (isset($_COOKIE['poll_voted_' . $poll_id])) {
            $has_voted = true;
        } else {
            // Requête préparée pour la vérification des votes
            $stmt_check_vote = mysqli_prepare($connect, "SELECT id FROM poll_voters WHERE poll_id=? AND ip_address=?");
            mysqli_stmt_bind_param($stmt_check_vote, "is", $poll_id, $user_ip);
            mysqli_stmt_execute($stmt_check_vote);
            $result_check_vote = mysqli_stmt_get_result($stmt_check_vote);
            if (mysqli_num_rows($result_check_vote) > 0) {
                $has_voted = true;
            }
            mysqli_stmt_close($stmt_check_vote);
        }
?>
    
    <div class="card mb-3"> <!-- Poll Widget -->
        <div class="card-header">
            <i class="fas fa-poll-h"></i> Poll of the week
        </div>
        <div class="card-body" id="poll-container-<?php echo $poll_id; ?>">
            <h6 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($poll['question']); ?></h6>
            
            <?php if (!$has_voted): ?>
                <form id="poll-form-<?php echo $poll_id; ?>">
                    <input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="poll-options custom-poll-options mb-3">
                        <?php
                        $opts_q = mysqli_query($connect, "SELECT * FROM poll_options WHERE poll_id='$poll_id' ORDER BY id ASC");
                        while ($opt = mysqli_fetch_assoc($opts_q)) {
                            echo '
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="option_id" id="opt-'.$opt['id'].'" value="'.$opt['id'].'">
                                <label class="form-check-label" for="opt-'.$opt['id'].'">
                                    '.htmlspecialchars($opt['title']).'
                                </label>
                            </div>';
                        }
                        ?>
                    </div>
                    <div id="poll-msg-<?php echo $poll_id; ?>" class="text-danger small mb-2"></div>
                    <button type="button" onclick="submitPoll(<?php echo $poll_id; ?>)" class="btn btn-sm btn-primary w-100">Vote</button>
                </form>
            <?php endif; ?>

            <div id="poll-results-<?php echo $poll_id; ?>" style="<?php echo ($has_voted ? '' : 'display:none;'); ?>">
                <?php
                // Calcul initial (si déjà voté, on affiche direct)
                if ($has_voted) {
                    $total_v = 0;
                    $res_data = [];
                    $res_q = mysqli_query($connect, "SELECT * FROM poll_options WHERE poll_id='$poll_id'");
                    while($r = mysqli_fetch_assoc($res_q)) { 
                        $res_data[] = $r; 
                        $total_v += $r['votes']; 
                    }
                    
                    foreach ($res_data as $row) {
                        $percent = ($total_v > 0) ? round(($row['votes'] / $total_v) * 100) : 0;
                        echo '
                        <small>'.htmlspecialchars($row['title']).' ('.$percent.'%)</small>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: '.$percent.'%;" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>';
                    }
                    echo '<div class="text-center small text-muted mt-2">Total votes: '.$total_v.'</div>';
                    echo '<div class="alert alert-success py-1 px-2 mt-2 small text-center"><i class="fas fa-check"></i> You have voted!</div>';
                }
                ?>
            </div>
        </div>
    </div> <!-- End Poll Widget -->

    <script>
    function submitPoll(pollId) {
        const form = document.getElementById('poll-form-' + pollId);
        const formData = new FormData(form);
        const msgDiv = document.getElementById('poll-msg-' + pollId);
        
        // Validation simple côté client
        if(!formData.get('option_id')) {
            msgDiv.innerText = "Please select an option.";
            return;
        }
        msgDiv.innerText = "Sending...";

        fetch('ajax_vote_poll.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Cacher le formulaire
                form.style.display = 'none';
                
                // Générer le HTML des résultats
                let html = '';
                let total = data.total_votes;
                
                data.results.forEach(opt => {
                    let percent = (total > 0) ? Math.round((opt.votes / total) * 100) : 0;
                    html += `<small>${opt.title} (${percent}%)</small>
                             <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: ${percent}%;"></div>
                             </div>`;
                });
                
                html += `<div class="text-center small text-muted mt-2">Total votes: ${total}</div>`;
                html += `<div class="alert alert-success py-1 px-2 mt-2 small text-center"><i class="fas fa-check"></i> ${data.message}</div>`;
                
                const resDiv = document.getElementById('poll-results-' + pollId);
                resDiv.innerHTML = html;
                $(resDiv).fadeIn(); // Effet jQuery doux
                
            } else {
                msgDiv.innerText = data.message;
            }
        })
        .catch(error => {
            msgDiv.innerText = "Error connecting to server.";
            console.error(error);
        });
    }
    </script>
<?php
    }
    // --- FIN WIDGET SONDAGE ---
?>
            <!-- ADVERTISEMENT WIDGET -->
            <?php render_ad('300x250', true); ?> <!-- Affichage de la publicité 300x250 -->
            <?php render_ad('300x600', true); ?> <!-- Affichage de la publicité 300x600 -->
            <?php render_ad('150x150', true); ?> <!-- Affichage de la publicité 150x150 -->
            <!-- FIN ADVERTISEMENT WIDGET -->

                <div class="card mb-3"> <!-- Categories Widget -->
                    <div class="card-header"><i class="fas fa-list"></i> Categories</div>
                    
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush sidebar-categories">
                            <?php
                            $categories_query = mysqli_query($connect, "
                                SELECT 
                                    c.category, c.slug, COUNT(p.id) AS posts_count
                                FROM `categories` c
                                LEFT JOIN posts p ON c.id = p.category_id AND p.active = 'Yes' AND p.publish_at <= NOW()
                                GROUP BY c.id
                                ORDER BY c.category ASC
                            ");
                            
                            while ($row = mysqli_fetch_assoc($categories_query)) {
                                echo '
                                    <a href="category?name=' . htmlspecialchars($row['slug']) . '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-chevron-right small me-2 text-muted category-icon"></i> 
                                            ' . htmlspecialchars($row['category']) . '
                                        </span>
                                        <span class="badge bg-light text-dark border rounded-pill">' . $row['posts_count'] . '</span>
                                    </a>
                                ';
                            }
?>
                        </div>
                    </div>
                </div> <!-- End Categories Widget -->
				
				<div class="card mb-3"> <!-- Popular Tags Widget -->
					<div class="card-header"><i class="fas fa-tags"></i> Popular Tags</div>
                        <div class="card-body">
                                                <div class="d-flex flex-wrap sidebar-tags">
                        <?php
                            // Requête pour récupérer les tags les plus utilisés
                            $stmt_tags = mysqli_prepare($connect, "
                                SELECT 
                                    t.name, t.slug, COUNT(pt.tag_id) AS tag_count
                                FROM tags t
                                JOIN post_tags pt ON t.id = pt.tag_id
                                JOIN posts p ON pt.post_id = p.id
                                WHERE p.active = 'Yes' AND p.publish_at <= NOW()
                                GROUP BY pt.tag_id
                                ORDER BY tag_count DESC, t.name ASC
                                LIMIT 15
                            ");
                            mysqli_stmt_execute($stmt_tags);
                            $result_tags = mysqli_stmt_get_result($stmt_tags);

                            if (mysqli_num_rows($result_tags) == 0) {
                                echo '<div class="alert alert-info p-2 small w-100">No tags found.</div>';
                            } else {
                                while ($row_tag = mysqli_fetch_assoc($result_tags)) {
                                    echo '
                                        <a href="tag.php?name=' . htmlspecialchars($row_tag['slug']) . '" class="tag-link shadow-sm">
                                            <i class="fas fa-hashtag text-muted small"></i> ' . htmlspecialchars($row_tag['name']) . '
                                        </a>
                                    ';
                                }
                            }
                            mysqli_stmt_close($stmt_tags);
?>
						</div>
					</div>
				</div> <!-- End Popular Tags Widget -->
				
<div class="card mb-3 sidebar-tabs-card"> <!-- Sidebar Tabs Widget -->
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs nav-justified" id="sidebarTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="popular-tab" data-bs-toggle="tab" data-bs-target="#popular" type="button" role="tab" aria-selected="true">
                    <i class="fas fa-bolt text-warning"></i> Popular
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#commentss" type="button" role="tab" aria-selected="false">
                    <i class="fas fa-comments text-info"></i> Comments
                </button>
            </li>
        </ul>
    </div>
        <div class="card-body p-0"> <div class="tab-content" id="sidebarTabsContent">
            
            <div class="tab-pane fade show active" id="popular" role="tabpanel" aria-labelledby="popular-tab">
                <div class="list-group list-group-flush">
                <?php
                $run = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY views DESC, id DESC LIMIT 4");
                if (mysqli_num_rows($run) <= 0) {
                    echo '<div class="p-3 text-muted small">No posts found.</div>';
                } else {
                    while ($row = mysqli_fetch_assoc($run)) {
                            
                            // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
                            $image_html = '';
                            $post_img_path = str_replace('../', '', $row['image']);
                            $default_img_path = 'assets/img/no-image.png';

                            // NIVEAU 1 : L'image existe
                            if (!empty($row['image']) && file_exists($post_img_path)) {
                                $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="Img" class="rounded" width="80" height="60" style="object-fit: cover;">';
                            } 
                            // NIVEAU 2 : Pas d'image, mais no-image.png existe
                            elseif (file_exists($default_img_path)) {
                                $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="rounded" width="80" height="60" style="object-fit: cover;">';
                            }
                            // NIVEAU 3 : Rien n'existe -> Icône FontAwesome (Design Sidebar)
                            else {
                                $image_html = '<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:60px; height:60px;"><i class="fas fa-image"></i></div>';
                            }

                            echo '
                            <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="list-group-item list-group-item-action d-flex align-items-center p-3 sidebar-post-item">
                                <div class="flex-shrink-0 me-3">
                                    ' . $image_html . '
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="mb-1 text-truncate small fw-bold text-dark title-hover">' . htmlspecialchars($row['title']) . '</h6>
                                    <small class="text-muted d-block">
                                        <i class="far fa-clock me-1"></i> ' . date($settings['date_format'], strtotime($row['created_at'])) . '
                                    </small>
                                </div>
                            </a>';
                        }
                    }
                ?>
                </div>
            </div>

            <div class="tab-pane fade" id="commentss" role="tabpanel" aria-labelledby="comments-tab">
                <div class="list-group list-group-flush">
                    <?php
                    $comments_query = mysqli_query($connect, "
                        SELECT c.id, c.user_id, c.guest, c.created_at, p.title AS post_title, p.slug AS post_slug, u.username AS user_username, u.avatar AS user_avatar
                        FROM `comments` c JOIN `posts` p ON c.post_id = p.id LEFT JOIN `users` u ON c.user_id = u.id AND c.guest = 'No'
                        WHERE c.approved='Yes' AND p.active='Yes' ORDER BY c.id DESC LIMIT 4
                    ");
                    
                    if (mysqli_num_rows($comments_query) == 0) {
                        echo '<div class="p-3 text-muted small">No comments yet.</div>';
                    } else {
                        while ($row = mysqli_fetch_assoc($comments_query)) {
                            $acavatar = 'assets/img/avatar.png'; // Avatar par défaut
                            $acuthor_name = 'Guest';
                            
                            // 1. Déterminer le nom et l'image brute
                            if ($row['guest'] == 'Yes') {
                                $acuthor_name = $row['user_id'];
                            } else if ($row['user_username']) {
                                if (!empty($row['user_avatar'])) {
                                    $acavatar = $row['user_avatar'];
                                }
                                $acuthor_name = $row['user_username'];
                            }
                            
                            // 2. Nettoyage du chemin (Retrait des ../)
                            // Si c'est une URL externe (Google login), on ne touche pas
                            if (strpos($acavatar, 'http') !== 0) {
                                $acavatar = str_replace('../', '', $acavatar);
                            }
                            
                            // 3. Affichage avec sécurité onerror
                            echo '
                            <a href="post?name=' . htmlspecialchars($row['post_slug']) . '#comments" class="list-group-item list-group-item-action d-flex align-items-start p-3 sidebar-comment-item">
                                
                                <div class="position-relative me-3 flex-shrink-0" style="width: 44px; height: 44px;">
                                    
                                    <div style="
                                        position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; z-index: 1;
                                        background: conic-gradient(#ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3, #ff0000);
                                    "></div>
                                    
                                    <img src="' . htmlspecialchars($acavatar) . '" 
                                         alt="Avatar"
                                         class="rounded-circle"
                                         style="
                                            position: absolute; top: 2px; left: 2px; width: 40px; height: 40px; object-fit: cover; z-index: 2;
                                            border: 2px solid #fff; background-color: #fff;
                                         "
                                         onerror="this.src=\'assets/img/avatar.png\';">
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small text-dark">' . htmlspecialchars($acuthor_name) . '</span>
                                        <small class="text-muted" style="font-size: 0.7rem;">' . date('d/m', strtotime($row['created_at'])) . '</small>
                                    </div>
                                    <p class="mb-0 small text-muted text-truncate">
                                        on <span class="text-primary">' . htmlspecialchars($row['post_title']) . '</span>
                                    </p>
                                </div>
                            </a>';
                        }
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
</div> <!-- End Sidebar Tabs Widget -->

<!-- --- CODE --- -->

<?php
// --- CACHE SIDEBAR WIDGETS ---
// On met en cache uniquement la zone des widgets dynamiques
// Clé 'sidebar_widgets', durée 10 minutes (600s) pour garder un peu de fraîcheur (ex: Shop aléatoire)
$sidebar_cache = get_cache('sidebar_widgets', 600);

if ($sidebar_cache) {
    echo $sidebar_cache;
} else {
    ob_start();
    
    // Requête Widgets
    $run = mysqli_query($connect, "SELECT * FROM widgets WHERE position = 'sidebar' AND active = 'Yes' ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($run)) {
        // EXCEPTION : On ne cache PAS le widget "Utilisateurs en ligne" car il doit être temps réel
        if ($row['widget_type'] == 'online_users') {
            // On ferme le buffer temporairement pour afficher ce widget en direct
            $cached_part = ob_get_contents();
            ob_clean(); // On vide le buffer actuel
            echo $cached_part; // On affiche ce qu'on avait
            
            // On affiche le widget live
            render_widget($row);
            
            // On redémarre le buffer pour la suite
            ob_start();
        } else {
            // Les autres widgets (Shop, Posts, HTML) vont dans le cache
            render_widget($row);
        }
    }
    
    $content = ob_get_clean();
    save_cache('sidebar_widgets', $content);
    echo $content;
}
// -----------------------------
?>
</div>
		
<?php
}
?>