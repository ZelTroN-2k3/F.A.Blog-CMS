<?php
include "core.php";

// 1. RÉCUPÉRATION DU PROFIL
$username = isset($_GET['name']) ? mysqli_real_escape_string($connect, $_GET['name']) : '';

if(empty($username)) { echo '<meta http-equiv="refresh" content="0; url=index.php">'; exit; }

$stmt = mysqli_prepare($connect, "SELECT * FROM users WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);

if(!$profile) {
    $pagetitle = "User not found";
    head();
    echo '<div class="col-12 text-center py-5"><h2>User not found</h2><a href="index.php" class="btn btn-primary">Back Home</a></div>';
    footer();
    exit;
}

$pagetitle = $profile['username'] . "'s Profile";
head();

// Préparation Données
$avatar = !empty($profile['avatar']) ? $profile['avatar'] : 'assets/img/avatar.png';
if(strpos($avatar, 'http') !== 0) {
    $avatar = str_replace('../', '', $avatar);
    $avatar = $settings['site_url'].'/'.$avatar;
}

$role_badge = '<span class="badge bg-secondary">Member</span>';
if($profile['role'] == 'Admin') $role_badge = '<span class="badge bg-danger"><i class="fas fa-shield-alt"></i> Admin</span>';
if($profile['role'] == 'Editor') $role_badge = '<span class="badge bg-primary"><i class="fas fa-pen-nib"></i> Editor</span>';

$join_date = isset($profile['created_at']) ? date("d M Y", strtotime($profile['created_at'])) : "Unknown";

$is_online = false;
if(!empty($profile['last_activity']) && strtotime($profile['last_activity']) > strtotime("-5 minutes")) {
    $is_online = true;
}
?>

<style>
    /* Style ajusté pour rester dans le conteneur (comme Arcade) */
    .profile-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0 80px 0; /* 80px en bas pour l'avatar */
        border-radius: 15px 15px 50% 50% / 15px 15px 20px 20px; /* Coins arrondis en haut aussi */
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        margin-bottom: 0;
        position: relative;
        /* Suppression de width:100% et left:0 pour rester dans la grille */
    }
    
    .profile-avatar-container {
        margin-top: -75px; 
        margin-bottom: 40px; 
        position: relative;
        z-index: 10;
        text-align: center;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 6px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        background: #fff;
        object-fit: cover;
    }
</style>

<div class="col-12">
    <div class="profile-card-header text-center">
        <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($profile['username']); ?></h1>
        <div class="mb-3"><?php echo $role_badge; ?></div>
        
        <p class="opacity-75 mb-0">
            <i class="fas fa-clock me-1"></i> Joined: <?php echo $join_date; ?> &bull; 
            <?php if($is_online): ?>
                <span class="text-warning fw-bold"><i class="fas fa-circle me-1"></i> Online Now</span>
            <?php else: ?>
                <span class="opacity-75"><i class="fas fa-power-off me-1"></i> Offline</span>
            <?php endif; ?>
        </p>
        
        <?php if(!empty($profile['bio'])): ?>
            <p class="lead mx-auto mt-3" style="max-width: 600px; font-style: italic;">"<?php echo htmlspecialchars($profile['bio']); ?>"</p>
        <?php endif; ?>
    </div>
    
    <div class="profile-avatar-container">
        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="profile-avatar" onerror="this.src='assets/img/avatar.png';">
    </div>
</div>

<div class="row g-4"> <div class="col-lg-8 mb-4">
        
        <?php
        $q_posts = mysqli_query($connect, "SELECT title, slug, created_at, image FROM posts WHERE author_id = {$profile['id']} AND active='Yes' ORDER BY created_at DESC LIMIT 3");
        if(mysqli_num_rows($q_posts) > 0):
        ?>
        <h5 class="fw-bold mb-3"><i class="fas fa-pen-nib text-primary me-2"></i> Latest Articles</h5>
        <div class="row mb-4">
            <?php while($p = mysqli_fetch_assoc($q_posts)): 
                $p_img = !empty($p['image']) ? $p['image'] : 'assets/img/no-image.png';
                $p_img = str_replace('../', '', $p_img);
                if(strpos($p_img, 'http') !== 0) $p_img = $settings['site_url'].'/'.$p_img;
            ?>
            <div class="col-12 mb-3">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="<?php echo htmlspecialchars($p_img); ?>" class="w-100 h-100" style="object-fit: cover; min-height: 100px;" alt="Post">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body py-2">
                                <h6 class="card-title fw-bold mb-1">
                                    <a href="post?name=<?php echo $p['slug']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($p['title']); ?></a>
                                </h6>
                                <small class="text-muted"><?php echo date('d M Y', strtotime($p['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <?php
        $q_proj = mysqli_query($connect, "SELECT title, slug, created_at, image FROM projects WHERE author_id = {$profile['id']} AND active='Yes' ORDER BY created_at DESC LIMIT 3");
        if(mysqli_num_rows($q_proj) > 0):
        ?>
        <h5 class="fw-bold mb-3"><i class="fas fa-microchip text-success me-2"></i> Latest Projects</h5>
        <div class="row mb-4">
            <?php while($pr = mysqli_fetch_assoc($q_proj)): 
                $pr_img = !empty($pr['image']) ? $pr['image'] : 'assets/img/project-no-image.png';
                $pr_img = str_replace('../', '', $pr_img);
                if(strpos($pr_img, 'http') !== 0) $pr_img = $settings['site_url'].'/'.$pr_img;
            ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?php echo htmlspecialchars($pr_img); ?>" class="card-img-top" style="height:120px; object-fit:cover;">
                    <div class="card-body p-3">
                        <h6 class="card-title fw-bold" style="font-size:0.9rem;">
                            <a href="project?name=<?php echo $pr['slug']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($pr['title']); ?></a>
                        </h6>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <h5 class="fw-bold mb-3"><i class="fas fa-comments text-secondary me-2"></i> Recent Activity</h5>
        <div class="card shadow-sm border-0 mb-4">
            <?php
            $q_coms = mysqli_query($connect, "SELECT c.comment, c.created_at, p.title, p.slug 
                                              FROM comments c 
                                              JOIN posts p ON c.post_id = p.id 
                                              WHERE c.user_id = {$profile['id']} AND c.guest='No' AND c.approved='Yes' 
                                              ORDER BY c.created_at DESC LIMIT 5");
            
            if(mysqli_num_rows($q_coms) > 0) {
                echo '<div class="list-group list-group-flush">';
                while($c = mysqli_fetch_assoc($q_coms)) {
                    echo '<div class="list-group-item p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-bold">Commented on <a href="post?name='.$c['slug'].'">'.htmlspecialchars($c['title']).'</a></small>
                                <small class="text-muted">'.date('d M, H:i', strtotime($c['created_at'])).'</small>
                            </div>
                            <p class="mb-0 small text-muted fst-italic">"'.htmlspecialchars(short_text(strip_tags(html_entity_decode($c['comment'])), 120)).'"</p>
                          </div>';
                }
                echo '</div>';
            } else {
                echo '<div class="card-body text-center text-muted p-4">Has not commented recently.</div>';
            }
            ?>
        </div>

    </div>

    <div class="col-lg-4 mb-4">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold border-bottom-0 pt-3 ps-3">
                <i class="fas fa-trophy text-warning me-2"></i> Trophy Case
            </div>
            <div class="card-body bg-light rounded-bottom">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <?php
                    $q_badges = mysqli_query($connect, "SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = {$profile['id']}");
                    if(mysqli_num_rows($q_badges) > 0) {
                        while($b = mysqli_fetch_assoc($q_badges)) {
                            echo '<div class="text-center p-2" data-bs-toggle="tooltip" title="'.htmlspecialchars($b['description']).'">
                                    <i class="'.$b['icon'].' text-'.$b['color'].' fa-2x"></i><br>
                                    <small class="fw-bold text-muted" style="font-size:0.7rem;">'.htmlspecialchars($b['name']).'</small>
                                  </div>';
                        }
                    } else {
                        echo '<p class="text-muted small mb-0">No badges unlocked yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold"><i class="fas fa-gamepad text-primary me-2"></i> Arcade Highscores</div>
            <ul class="list-group list-group-flush">
                <?php
                $games = ['snake' => 'Snake', 'tetris' => 'Tetris', 'space' => 'Space Invaders'];
                foreach($games as $key => $label) {
                    $q_score = mysqli_query($connect, "SELECT MAX(score) as best FROM game_scores WHERE user_id = {$profile['id']} AND game_name = '$key'");
                    $best = mysqli_fetch_assoc($q_score)['best'] ?? 0;
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>'.$label.'</span>
                            <span class="badge bg-dark rounded-pill">'.number_format($best).'</span>
                          </li>';
                }
                ?>
            </ul>
        </div>
        
        <?php if(!empty($profile['website']) || !empty($profile['location'])): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <?php if(!empty($profile['location'])): ?>
                    <div class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2 fa-fw"></i> <?php echo htmlspecialchars($profile['location']); ?></div>
                <?php endif; ?>
                <?php if(!empty($profile['website'])): ?>
                    <div><i class="fas fa-link text-info me-2 fa-fw"></i> <a href="<?php echo htmlspecialchars($profile['website']); ?>" target="_blank" class="text-decoration-none">Website</a></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php 
// Script Tooltips
echo "<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>";

footer(); 
?>