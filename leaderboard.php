<?php
include "core.php";
$pagetitle = "Leaderboard & Badges";
head();

// Fonction helper pour afficher le tableau
function render_game_table($game_key, $game_title, $icon) {
    global $connect, $settings;
    echo '
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 text-primary"><i class="'.$icon.' me-2"></i> '.$game_title.' Top 10</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width:50px;">#</th>
                            <th scope="col">Player</th>
                            <th scope="col" class="text-end">Score</th>
                            <th scope="col" class="text-end">Date</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Requête : On prend le MEILLEUR score de chaque utilisateur
    $sql = "SELECT u.username, u.avatar, MAX(s.score) as best_score, MAX(s.created_at) as date
            FROM game_scores s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.game_name = '$game_key'
            GROUP BY s.user_id 
            ORDER BY best_score DESC 
            LIMIT 10";
            
    $q = mysqli_query($connect, $sql);
    
    if (mysqli_num_rows($q) > 0) {
        $rank = 1;
        while ($row = mysqli_fetch_assoc($q)) {
            // Médaille pour le top 3
            $medal = '';
            if($rank == 1) $medal = '🥇';
            if($rank == 2) $medal = '🥈';
            if($rank == 3) $medal = '🥉';
            
            // Avatar
            $avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
            $avatar = str_replace('../', '', $avatar); // Nettoyage chemin
            if(strpos($avatar, 'http') !== 0) $avatar = $settings['site_url'].'/'.$avatar;

            echo '<tr>
                    <td class="text-center fw-bold">'.$rank.'</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="'.$avatar.'" class="rounded-circle me-2" width="30" height="30" style="object-fit:cover;">
                            <span class="fw-bold">'.$row['username'].' '.$medal.'</span>
                        </div>
                    </td>
                    <td class="text-end fw-bold text-success">'.number_format($row['best_score']).'</td>
                    <td class="text-end small text-muted">'.date('d M', strtotime($row['date'])).'</td>
                  </tr>';
            $rank++;
        }
    } else {
        echo '<tr><td colspan="4" class="text-center text-muted p-3">No scores yet. Be the first!</td></tr>';
    }
    
    echo '      </tbody>
                </table>
            </div>
        </div>
    </div>';
}
?>

<div class="container mt-4 mb-5">
    
    <div class="text-center mb-5">
        <h1 class="fw-bold"><i class="fas fa-trophy text-warning"></i> Hall of Fame</h1>
        <p class="lead text-muted">Compete with other players and earn exclusive badges.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php 
            render_game_table('snake', 'Snake Deluxe', 'fas fa-worm'); 
            render_game_table('space', 'Space Invaders', 'fas fa-rocket'); 
            render_game_table('tetris', 'Tetris', 'fas fa-th-large'); 
            ?>
        </div>

        <div class="col-lg-4">
            
            <?php if($logged == 'Yes'): ?>
            <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                <div class="card-body text-center">
                    <?php
                    // --- CORRECTION AVATAR ---
                    // On redéfinit le chemin car $nav_avatar n'existe pas ici
                    $my_avatar = $settings['site_url'] . '/assets/img/avatar.png'; // Image par défaut
                    
                    if (!empty($rowu['avatar'])) {
                        $clean_path = str_replace('../', '', $rowu['avatar']);
                        if (strpos($clean_path, 'http') === 0) {
                            $my_avatar = $clean_path; // URL Google/Externe
                        } else {
                            $my_avatar = $settings['site_url'] . '/' . $clean_path; // URL Locale
                        }
                    }
                    ?>
                    
                    <img src="<?php echo htmlspecialchars($my_avatar); ?>" class="rounded-circle border border-3 border-white mb-2" width="80" height="80" style="object-fit:cover; background-color: #fff;" onerror="this.src='<?php echo $settings['site_url']; ?>/assets/img/avatar.png';">
                    <h4><?php echo htmlspecialchars($rowu['username']); ?></h4>
                    
                    <?php
                    // Compter mes badges
                    $my_badges_count = mysqli_query($connect, "SELECT COUNT(*) as c FROM user_badges WHERE user_id = {$rowu['id']}");
                    $cnt = mysqli_fetch_assoc($my_badges_count)['c'];
                    ?>
                    <div class="badge bg-white text-primary rounded-pill px-3 py-2 mt-2">
                        <i class="fas fa-medal"></i> <?php echo $cnt; ?> Badges unlocked
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold"><i class="fas fa-certificate text-warning me-2"></i> My Collection</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <?php
                        $q_my_badges = mysqli_query($connect, "SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = {$rowu['id']}");
                        if(mysqli_num_rows($q_my_badges) > 0) {
                            while($b = mysqli_fetch_assoc($q_my_badges)) {
                                echo '<span class="badge bg-'.$b['color'].' p-2" title="'.htmlspecialchars($b['description']).'">
                                        <i class="'.$b['icon'].'"></i> '.$b['name'].'
                                      </span>';
                            }
                        } else {
                            echo '<p class="text-muted small text-center">Play games to unlock badges!</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Available Badges</div>
                <ul class="list-group list-group-flush">
                    <?php
                    $q_all_badges = mysqli_query($connect, "SELECT * FROM badges ORDER BY name ASC");
                    while($b = mysqli_fetch_assoc($q_all_badges)) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="'.$b['icon'].' text-'.$b['color'].' me-2"></i> 
                                    <strong>'.$b['name'].'</strong>
                                    <div class="text-muted small" style="font-size:0.75rem;">'.$b['description'].'</div>
                                </div>
                              </li>';
                    }
                    ?>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php footer(); ?>