<?php
include "core.php";

// 1. Récupération du Projet
$slug = $_GET['name'] ?? '';
if (empty($slug)) {
    echo '<meta http-equiv="refresh" content="0; url=projects">';
    exit();
}

$stmt = mysqli_prepare($connect, "SELECT p.*, u.username, u.avatar FROM projects p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug=? AND p.active='Yes' LIMIT 1");
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
                            <span class="badge bg-<?php echo $diff_color; ?> mb-2"><?php echo htmlspecialchars($project['difficulty']); ?></span>
                            <?php if(!empty($project['duration'])): ?>
                                <span class="badge bg-light text-dark border mb-2"><i class="far fa-clock"></i> <?php echo htmlspecialchars($project['duration']); ?></span>
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
                                <span class="fw-bold"><?php echo htmlspecialchars($project['username']); ?></span>
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
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tools text-secondary me-2"></i> Things used</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($project['hardware_parts'])): ?>
                        <h6 class="text-uppercase text-muted small fw-bold mt-2">Hardware components</h6>
                        <div class="mb-4">
                            <?php echo $purifier->purify(html_entity_decode($project['hardware_parts'])); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($project['software_apps'])): ?>
                        <h6 class="text-uppercase text-muted small fw-bold border-top pt-3">Software apps</h6>
                        <div>
                            <?php echo $purifier->purify(html_entity_decode($project['software_apps'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(empty($project['hardware_parts']) && empty($project['software_apps'])): ?>
                        <p class="text-muted small fst-italic">No components listed.</p>
                    <?php endif; ?>
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

<?php footer(); ?>