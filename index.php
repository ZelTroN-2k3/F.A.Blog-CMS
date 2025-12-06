<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
	<div class="col-md-8 mb-3">
<?php
$mt3_i = ""; // Variable pour la marge

// --- LOGIQUE DE SÉLECTION DU SLIDER ---
$slides_data = [];
$slider_id = "";

if ($settings['homepage_slider'] == 'Custom') {
    // --- CAS A : SLIDER PERSONNALISÉ (Table 'slides') ---
    $slider_id = "carouselCustom";
    $purifier = get_purifier();
    $run_slides = mysqli_query($connect, "SELECT * FROM slides WHERE active='Yes' ORDER BY position_order ASC");
    while ($row = mysqli_fetch_assoc($run_slides)) {
        $slides_data[] = [
            'image' => $row['image_url'],
            'title' => $row['title'],
            'desc'  => $purifier->purify($row['description']),
            'link'  => $row['link_url']
        ];
    }

} else {
    // --- CAS B : SLIDER MIXTE (Articles + Projets) ---
    $slider_id = "carouselFeatured";
    
    // Requête UNION pour mélanger Posts et Projets
    // On sélectionne les champs communs (titre, image, slug, date)
    $sql_union = "
        (SELECT 'post' as type, title, slug, image, created_at 
         FROM posts 
         WHERE active='Yes' AND featured='Yes' AND publish_at <= NOW())
        UNION
        (SELECT 'project' as type, title, slug, image, created_at 
         FROM projects 
         WHERE active='Yes' AND featured='Yes')
        ORDER BY created_at DESC 
        LIMIT 5
    ";
    
    $run_mixed = mysqli_query($connect, $sql_union);
    
    while ($row = mysqli_fetch_assoc($run_mixed)) {
        
        // Gestion Image Robuste
        $img = 'assets/img/no-image.png';
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img = $clean; }
        } else {
            // Image par défaut différente selon le type (Optionnel)
            if ($row['type'] == 'project') $img = 'assets/img/project-no-image.png';
        }

        // Lien différent selon le type
        $link = ($row['type'] == 'post') ? 'post?name=' . $row['slug'] : 'project?name=' . $row['slug'];
        
        // Petit badge dans la description pour distinguer
        $badge = ($row['type'] == 'post') ? '<span class="badge bg-primary me-1">Article</span>' : '<span class="badge bg-success me-1">Project</span>';

        $slides_data[] = [
            'image' => $img,
            'title' => $row['title'],
            'desc'  => $badge . ' <i class="fas fa-calendar ms-2"></i> ' . date($settings['date_format'], strtotime($row['created_at'])),
            'link'  => $link
        ];
    }
}

// --- AFFICHAGE DU SLIDER (Code inchangé) ---
if (!empty($slides_data)) {
    $mt3_i = "mt-4";
?>
    <div id="<?php echo $slider_id; ?>" class="carousel slide main-slider mb-4 shadow-sm overflow-hidden rounded" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($slides_data as $index => $slide): ?>
                <button type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo ($index === 0) ? 'active' : ''; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($slides_data as $index => $slide): $active_class = ($index === 0) ? 'active' : ''; ?>
                <div class="carousel-item <?php echo $active_class; ?>">
                    <a href="<?php echo htmlspecialchars($slide['link']); ?>">
                        <?php if(strpos($slide['image'], '<svg') !== false): echo $slide['image']; else: ?>
                            <img src="<?php echo htmlspecialchars($slide['image']); ?>" class="d-block w-100 slider-img" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($slide['title']) || !empty($slide['desc'])): ?>
                        <div class="carousel-caption d-none d-md-block">
                            <div class="caption-content">
                                <h5 class="fw-bold mb-1">
                                    <a href="<?php echo htmlspecialchars($slide['link']); ?>" class="text-white text-decoration-none">
                                        <?php echo htmlspecialchars($slide['title']); ?>
                                    </a>
                                </h5>
                                <?php if (!empty($slide['desc'])): ?><div class="slider-description small text-white-50"><?php echo $slide['desc']; ?></div><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
<?php
}
?>  
    <?php render_ad('728x90'); ?>
    <?php render_ad('970x90'); ?>

            <?php
            $limit_proj = ($settings['posts_per_row'] == 3) ? 3 : 4;
            $q_proj = mysqli_query($connect, "SELECT p.*, u.username FROM projects p LEFT JOIN users u ON p.author_id = u.id WHERE p.active='Yes' ORDER BY p.created_at DESC LIMIT $limit_proj");
            
            if (mysqli_num_rows($q_proj) > 0) {
            ?>
            <div class="row <?php echo $mt3_i; ?> mb-4">
                <h5 class="text-success"><i class="fas fa-microchip me-2"></i> Latest Projects</h5>
                <?php
                while ($proj = mysqli_fetch_assoc($q_proj)) {
                    $p_img = 'assets/img/project-no-image.png';
                    if (!empty($proj['image'])) {
                        $clean = str_replace('../', '', $proj['image']);
                        if (file_exists($clean)) { $p_img = $clean; }
                    }
                    
                    $diff_color = 'secondary';
                    if($proj['difficulty']=='Easy') $diff_color='success';
                    if($proj['difficulty']=='Intermediate') $diff_color='primary';
                    if($proj['difficulty']=='Advanced') $diff_color='warning';
                    if($proj['difficulty']=='Expert') $diff_color='danger';

                    $col_class = ($settings['posts_per_row'] == 3) ? 'col-md-4' : 'col-md-6';
                    
                    echo '
                    <div class="' . $col_class . ' mb-3">
                        <div class="card h-100 shadow-sm hover-shadow border">
                            <a href="project?name=' . htmlspecialchars($proj['slug']) . '">
                                <img src="' . htmlspecialchars($p_img) . '" class="card-img-top" style="height: 160px; object-fit: cover;" onerror="this.src=\'assets/img/project-no-image.png\';">
                            </a>
                            <div class="card-body d-flex flex-column p-3">
                                <div class="mb-2">
                                    <span class="badge bg-' . $diff_color . '" style="font-size:0.7rem;">' . htmlspecialchars($proj['difficulty']) . '</span>
                                </div>
                                <h6 class="card-title fw-bold mb-2">
                                    <a href="project?name=' . htmlspecialchars($proj['slug']) . '" class="text-dark text-decoration-none">
                                        ' . htmlspecialchars($proj['title']) . '
                                    </a>
                                </h6>
                                <p class="card-text small text-muted mb-3 flex-grow-1" style="line-height:1.3;">
                                    ' . htmlspecialchars(short_text($proj['pitch'], 80)) . '
                                </p>
                                <a href="project?name=' . htmlspecialchars($proj['slug']) . '" class="btn btn-sm btn-outline-success mt-auto">View Project</a>
                            </div>
                        </div>
                    </div>';
                }
                ?>
                <div class="col-12 text-end">
                    <!--<a href="projects" class="text-success text-decoration-none small fw-bold">View all projects <i class="fas fa-arrow-right"></i></a>-->
                    <a href="projects" class="btn btn-outline-success col-12 mt-3 mb-5"><i class="fas fa-arrow-alt-circle-right"></i> View all projects</a>
                </div>
            </div>
            <hr class="my-4 text-muted opacity-25">
            <?php } ?>


            <div class="row">
                <h5 class="text-primary"><i class="fa fa-list me-2"></i> Recent Posts</h5>
<?php
$limit_posts = (int)$settings['posts_per_page'];
$run = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT $limit_posts");
$count = mysqli_num_rows($run);

if ($count <= 0) {
    echo '<p class="text-muted">There are no published posts.</p>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // Gestion Image Robuste
        $img_src = 'assets/img/no-image.png'; 
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img_src = $clean; }
        }
        
        $image = '<img src="' . htmlspecialchars($img_src) . '" 
                       alt="' . htmlspecialchars($row['title']) . '" 
                       class="card-img-top" 
                       width="100%" height="200" 
                       style="object-fit: cover;" 
                       onerror="this.onerror=null; this.src=\'assets/img/no-image.png\';">';
        
        $col_class = ($settings['posts_per_row'] == 3) ? 'col-md-4' : 'col-md-6';

        echo '
        <div class="' . $col_class . ' mb-3"> 
            <div class="card shadow-sm h-100 d-flex flex-column">
                <a href="post?name=' . htmlspecialchars($row['slug']) . '">
                    '. $image .'
                </a>
                <div class="card-body d-flex flex-column flex-grow-1">
                    <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none"><h6 class="card-title text-primary">' . htmlspecialchars($row['title']) . '</h6></a>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="text-muted d-block"> <i class="far fa-calendar-alt"></i> ' . date($settings['date_format'], strtotime($row['created_at'])) . '</small>
                            <small class="text-muted d-block">' . get_reading_time($row['content']) . '</small>
                        </div>
                        <div class="text-end">
                            <small class="me-2 text-muted"><i class="fas fa-comments"></i> <strong>' . post_commentscount($row['id']) . '</strong></small>
                            <small class="text-muted"><i class="fas fa-thumbs-up"></i> <strong>' . get_post_like_count($row['id']) . '</strong></small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="category?name=' . post_categoryslug($row['category_id']) . '" class="text-decoration-none">
                            <span class="badge bg-secondary">' . post_category($row['category_id']) . '</span>
                        </a>
                    </div>

                    <p class="card-text mt-2 small text-muted">' . short_text(strip_tags(html_entity_decode($row['content'])), 100) . '</p>

                    <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-primary col-12 mt-auto">Read more</a>
                </div>
            </div>
        </div>';
    }
}
?>
            </div>
            
            <a href="blog" class="btn btn-outline-primary col-12 mt-3 mb-5">
				<i class="fas fa-arrow-alt-circle-right"></i> View all articles
			</a>

<?php
    $q_testi = mysqli_query($connect, "SELECT * FROM testimonials WHERE active='Yes' ORDER BY id DESC");
    if (mysqli_num_rows($q_testi) > 0) {
    ?>
    <div class="card mb-3 mt-4 shadow-sm border-0">
        <div class="card-body bg-light rounded text-center p-4">
            <h4 class="mb-4 text-primary"><i class="fas fa-quote-left"></i> Testimonials</h4>
            <div id="carouselTestimonials" class="carousel carousel-dark slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $t_count = 0;
                    while ($row_t = mysqli_fetch_assoc($q_testi)) {
                        $active_t = ($t_count == 0) ? 'active' : '';
                        $avatar_t = !empty($row_t['avatar']) ? htmlspecialchars($row_t['avatar']) : 'assets/img/avatar.png';
                    ?>
                    <div class="carousel-item <?php echo $active_t; ?>">
                        <img src="<?php echo $avatar_t; ?>" class="rounded-circle shadow-sm mb-2" width="80" height="80" style="object-fit:cover;" alt="User Avatar">
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($row_t['name']); ?></h5>
                        <?php if(!empty($row_t['position'])): ?><p class="text-muted small mb-3"><?php echo htmlspecialchars($row_t['position']); ?></p><?php else: ?><br><?php endif; ?>
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <p class="fst-italic text-secondary">"<?php echo emoticons(nl2br(htmlspecialchars($row_t['content']))); ?>"</p>
                            </div>
                        </div>
                    </div>
                    <?php $t_count++; } ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselTestimonials" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselTestimonials" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
            </div>
        </div>
    </div>
    <?php } ?>

    </div>
<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>