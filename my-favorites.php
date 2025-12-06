<?php
include "core.php";
head();

if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

$user_id = $rowu['id'];

// --- GESTION SUPPRESSION ---
if (isset($_GET['remove-favorite'])) {
    $id_to_remove = (int)$_GET["remove-favorite"];
    $type = $_GET['type'] ?? 'post'; // Par défaut 'post'
    
    if ($type == 'project') {
        // Suppression PROJET
        $stmt = mysqli_prepare($connect, "DELETE FROM user_project_favorites WHERE user_id=? AND project_id=?");
    } else {
        // Suppression ARTICLE
        $stmt = mysqli_prepare($connect, "DELETE FROM user_favorites WHERE user_id=? AND post_id=?");
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $id_to_remove); 
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0;url=my-favorites.php">';
    exit;
}

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
    <div class="col-md-8 mb-3">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white"><i class="fas fa-bookmark"></i> Favorite Articles</div>
            <div class="card-body">

            <?php
            $stmt_favs = mysqli_prepare($connect, "
                SELECT p.* FROM `posts` p
                JOIN `user_favorites` uf ON p.id = uf.post_id
                WHERE uf.user_id = ?
                ORDER BY uf.created_at DESC
            ");
            mysqli_stmt_bind_param($stmt_favs, "i", $user_id);
            mysqli_stmt_execute($stmt_favs);
            $query = mysqli_stmt_get_result($stmt_favs);

            if (mysqli_num_rows($query) <= 0) {
                echo '<div class="alert alert-info">No favorite articles yet.</div>';
            } else {
                while ($row = mysqli_fetch_array($query)) {
                    
                    // Image Robuste
                    $img_src = 'assets/img/no-image.png';
                    if (!empty($row['image'])) {
                        $clean = str_replace('../', '', $row['image']);
                        if (file_exists($clean)) { $img_src = $clean; }
                    }
                    
                    $image = '<img src="' . htmlspecialchars($img_src) . '" alt="' . htmlspecialchars($row['title']) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover;" onerror="this.src=\'assets/img/no-image.png\';">';

                    echo '
                        <div class="card mb-3 border hover-shadow">
                          <div class="row g-0">
                            <div class="col-md-4">
                              <a href="post?name=' . htmlspecialchars($row['slug']) . '">
                                ' . $image . '
                              </a>
                            </div>
                            <div class="col-md-8">
                              <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none fw-bold">' . htmlspecialchars($row['title']) . '</a>
                                    </h6>
                                    <a href="?remove-favorite=' . $row['id'] . '&type=post" class="btn btn-outline-danger btn-sm" title="Remove" onclick="return confirm(\'Remove form favorites?\');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                                <p class="card-text small text-muted">' . short_text(strip_tags(html_entity_decode($row['content'])), 100) . '</p>
                                <div class="card-text">
                                    <small class="text-muted"><i class="far fa-calendar-alt"></i> ' . date($settings['date_format'], strtotime($row['created_at'])) . '</small>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>          
                    ';
                }
            }
            mysqli_stmt_close($stmt_favs);
            ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><i class="fas fa-microchip"></i> Favorite Projects</div>
            <div class="card-body">

            <?php
            $stmt_proj = mysqli_prepare($connect, "
                SELECT p.* FROM `projects` p
                JOIN `user_project_favorites` upf ON p.id = upf.project_id
                WHERE upf.user_id = ?
                ORDER BY upf.created_at DESC
            ");
            mysqli_stmt_bind_param($stmt_proj, "i", $user_id);
            mysqli_stmt_execute($stmt_proj);
            $q_proj = mysqli_stmt_get_result($stmt_proj);

            if (mysqli_num_rows($q_proj) <= 0) {
                echo '<div class="alert alert-info">No favorite projects yet.</div>';
            } else {
                while ($row = mysqli_fetch_array($q_proj)) {
                    
                    // Image Robuste Projet
                    $img_src = 'assets/img/project-no-image.png';
                    if (!empty($row['image'])) {
                        $clean = str_replace('../', '', $row['image']);
                        if (file_exists($clean)) { $img_src = $clean; }
                    }
                    
                    // Badge Difficulté
                    $diff_color = 'secondary';
                    if($row['difficulty']=='Easy') $diff_color='success';
                    if($row['difficulty']=='Intermediate') $diff_color='primary';
                    if($row['difficulty']=='Advanced') $diff_color='warning';
                    if($row['difficulty']=='Expert') $diff_color='danger';

                    echo '
                        <div class="card mb-3 border hover-shadow">
                          <div class="row g-0">
                            <div class="col-md-4">
                              <a href="project?name=' . htmlspecialchars($row['slug']) . '">
                                <img src="' . htmlspecialchars($img_src) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover; min-height:120px;" onerror="this.src=\'assets/img/project-no-image.png\';">
                              </a>
                            </div>
                            <div class="col-md-8">
                              <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <span class="badge bg-' . $diff_color . ' mb-1" style="font-size:0.6rem;">' . htmlspecialchars($row['difficulty']) . '</span>
                                        <h6 class="card-title mb-0">
                                            <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none fw-bold">' . htmlspecialchars($row['title']) . '</a>
                                        </h6>
                                    </div>
                                    <a href="?remove-favorite=' . $row['id'] . '&type=project" class="btn btn-outline-danger btn-sm" title="Remove" onclick="return confirm(\'Remove form favorites?\');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                                
                                <p class="card-text small text-muted mb-2">' . htmlspecialchars(short_text($row['pitch'], 80)) . '</p>
                                
                                <div class="card-text d-flex align-items-center">
                                    <small class="text-muted me-3"><i class="far fa-clock"></i> ' . htmlspecialchars($row['duration']) . '</small>
                                    <small class="text-muted"><i class="fas fa-eye"></i> ' . $row['views'] . '</small>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>          
                    ';
                }
            }
            mysqli_stmt_close($stmt_proj);
            ?>
            </div>
        </div>
        
    </div>
<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>