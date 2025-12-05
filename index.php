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

// Initialisation des variables
$slides_data = [];
$slider_id = "";

if ($settings['homepage_slider'] == 'Custom') {
    // --- CAS A : SLIDER PERSONNALISÉ ---
    $slider_id = "carouselCustom";
    $purifier = get_purifier(); // Sécurité HTML
    
    $run_slides = mysqli_query($connect, "SELECT * FROM slides WHERE active='Yes' ORDER BY position_order ASC");
    while ($row = mysqli_fetch_assoc($run_slides)) {
        $slides_data[] = [
            'image' => $row['image_url'],
            'title' => $row['title'],
            'desc'  => $purifier->purify($row['description']), // Description HTML autorisée
            'link'  => $row['link_url']
        ];
    }

} else {
    // --- CAS B : SLIDER ARTICLES (Featured) ---
    $slider_id = "carouselFeatured";
    
    $run_posts = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND featured='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($run_posts)) {
        // Gestion de l'image par défaut si vide
        $img = ($row['image'] != "") ? $row['image'] : 'assets/img/no-image.png'; // Assurez-vous d'avoir une image par défaut ou un placeholder
        
        $slides_data[] = [
            'image' => $img,
            'title' => $row['title'],
            'desc'  => '<i class="fas fa-calendar"></i> ' . date($settings['date_format'], strtotime($row['created_at'])),
            'link'  => 'post?name=' . $row['slug']
        ];
    }
}

// --- AFFICHAGE DU SLIDER (Code Unique pour les deux cas) ---
if (!empty($slides_data)) {
    $mt3_i = "mt-4"; // Ajouter de l'espace après le slider
?>
    <div id="<?php echo $slider_id; ?>" class="carousel slide main-slider mb-4 shadow-sm overflow-hidden rounded" data-bs-ride="carousel">
        
        <div class="carousel-indicators">
            <?php foreach ($slides_data as $index => $slide): ?>
                <button type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo ($index === 0) ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php foreach ($slides_data as $index => $slide): 
                $active_class = ($index === 0) ? 'active' : '';
            ?>
                <div class="carousel-item <?php echo $active_class; ?>">
                    <a href="<?php echo htmlspecialchars($slide['link']); ?>">
                        <?php if(strpos($slide['image'], '<svg') !== false): ?>
                            <?php echo $slide['image']; // Si c'est le code SVG placeholder ?>
                        <?php else: ?>
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
                                <?php if (!empty($slide['desc'])): ?>
                                    <div class="slider-description small text-white-50">
                                        <?php echo $slide['desc']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $slider_id; ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
<?php
} // Fin if !empty($slides_data)
?>  
    <!-- ADVERTISEMENT WIDGET -->
    <?php render_ad('728x90'); ?> <!-- Affichage de la publicité 728x90 -->
    <?php render_ad('970x90'); ?> <!-- Affichage de la publicité 970x90 -->
    <!-- FIN ADVERTISEMENT WIDGET -->

            <div class="row <?php echo $mt3_i; ?>">
                <h5><i class="fa fa-list"></i> Recent Posts</h5>
<?php
// Récupère la limite depuis les paramètres de l'administration
$limit_posts = (int)$settings['posts_per_page'];
$run = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT $limit_posts");
$count = mysqli_num_rows($run);
if ($count <= 0) {
    echo '<p>There are no published posts</p>';
} else {
while ($row = mysqli_fetch_assoc($run)) {
        
        // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
        $image_html = '';
        
        // Nettoyage du chemin de l'image de l'article
        $post_img_path = str_replace('../', '', $row['image']);
        $default_img_path = 'assets/img/no-image.png';

        // NIVEAU 1 : L'image de l'article existe physiquement
        if (!empty($row['image']) && file_exists($post_img_path)) {
            $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($row['title']) . '" class="card-img-top" width="100%" height="250" style="object-fit: cover;">';
        } 
        // NIVEAU 2 : Pas d'image article, mais no-image.png existe
        elseif (file_exists($default_img_path)) {
            $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="card-img-top" width="100%" height="250" style="object-fit: cover;">';
        }
        // NIVEAU 3 : Rien n'existe -> Affichage du SVG de secours
        else {
            $image_html = '<svg class="bd-placeholder-img card-img-top" width="100%" height="250" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
            <rect width="100%" height="100%" fill="#55595c"/>
            <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle" font-size="1.2rem">No Image</text></svg>';
        }
        
        // Affichage (On remplace $image par $image_html)
        $image = $image_html;
        
        echo '
                    <div class="';
if ($settings['posts_per_row'] == 3) {
	echo 'col-md-4';
} else {
	echo 'col-md-6';
}
echo ' mb-3"> 
                        <div class="card shadow-sm h-100 d-flex flex-column">
                            <a href="post?name=' . htmlspecialchars($row['slug']) . '">
                                '. $image .'
                            </a>
                            <div class="card-body d-flex flex-column flex-grow-1">
                                <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none"><h6 class="card-title text-primary">' . htmlspecialchars($row['title']) . '</h6></a>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted d-block"> <i class="far fa-calendar-alt"></i> ' . date($settings['date_format'] . ' H:i', strtotime($row['created_at'])) . '
                                        </small>
                                        <small class="text-muted d-block">
                                            ' . get_reading_time($row['content']) . '
                                        </small>
                                        </div>
                                    <div class="text-end">
                                        <small class="me-2 text-muted"><i class="fas fa-comments"></i> 
                                            <a href="post?name=' . htmlspecialchars($row['slug']) . '#comments" class="blog-comments text-decoration-none">
                                                <strong>' . post_commentscount($row['id']) . '</strong>
                                            </a>
                                        </small>
                                        <small class="text-muted"><i class="fas fa-thumbs-up"></i> 
                                            <strong>' . get_post_like_count($row['id']) . '</strong>
                                        </small>
                                    </div>
                                </div>
								<div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="category?name=' . post_categoryslug($row['category_id']) . '" class="text-decoration-none">
										<span class="badge bg-secondary">' . post_category($row['category_id']) . '</span>
									</a>
                                </div>

                                <p class="card-text mt-2">' . short_text(strip_tags(html_entity_decode($row['content'])), 100) . '</p>

								<a href="post?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-primary col-12 mt-auto">
									Read more
								</a>
                            </div>
                        </div>
                    </div>
';
    }
}
?>
            </div>
            <a href="blog" class="btn btn-primary col-12 mt-3">
				<i class="fas fa-arrow-alt-circle-right"></i> All posts
			</a>
<!-- Affichage des témoignages -->
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
                        // Gestion de l'avatar avec fallback
                        $avatar_t = !empty($row_t['avatar']) ? htmlspecialchars($row_t['avatar']) : 'assets/img/avatar.png';
                    ?>
                    <div class="carousel-item <?php echo $active_t; ?>">
                        <img src="<?php echo $avatar_t; ?>" class="rounded-circle shadow-sm mb-2" width="80" height="80" style="object-fit:cover;" alt="User Avatar">
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($row_t['name']); ?></h5>
                        
                        <?php if(!empty($row_t['position'])): ?>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($row_t['position']); ?></p>
                        <?php else: ?>
                            <br>
                        <?php endif; ?>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <p class="fst-italic text-secondary">
                                    "<?php echo emoticons(nl2br(htmlspecialchars($row_t['content']))); ?>"
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php
                        $t_count++;
                    }
                    ?>
                </div>
                
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselTestimonials" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselTestimonials" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
        </div>
<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>