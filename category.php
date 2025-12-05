<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

$slug = $_GET['name'] ?? '';
if (empty($slug)) {
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit();
}

// 1. Trouver la catégorie
$stmt = mysqli_prepare($connect, "SELECT * FROM `categories` WHERE slug=?");
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit();
}
$row_cat = mysqli_fetch_assoc($result);
$cat_id   = $row_cat['id'];
$cat_name = $row_cat['category'];
$cat_desc = $row_cat['description'];
// Image de la catégorie (Optionnelle)
$cat_img = !empty($row_cat['image']) ? $row_cat['image'] : '';

mysqli_stmt_close($stmt);
?>
            <div class="col-md-8 mb-3">

                <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                    <?php if (!empty($cat_img) && file_exists($cat_img)): ?>
                        <div style="height: 200px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($cat_img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($cat_name); ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body bg-primary text-white">
                        <h2 class="card-title mb-0"><i class="far fa-folder-open me-2"></i> <?php echo htmlspecialchars($cat_name); ?></h2>
                        <?php if (!empty($cat_desc)): ?>
                            <hr class="my-2" style="opacity: 0.3;">
                            <p class="card-text" style="opacity: 0.9;">
                                <?php echo nl2br(htmlspecialchars($cat_desc)); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 text-muted">Latest articles in <b><?php echo htmlspecialchars($cat_name); ?></b></h5>
                    </div>
                    <div class="card-body">

<?php
$postsperpage = 8;

$pageNum = 1;
if (isset($_GET['page'])) {
    $pageNum = (int)$_GET['page'];
}
if (!is_numeric($pageNum) || $pageNum < 1) {
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit();
}
$rows = ($pageNum - 1) * $postsperpage;

// 2. Compter le nombre total d'articles pour cette catégorie
$stmt_count = mysqli_prepare($connect, "SELECT COUNT(id) AS numrows FROM posts WHERE category_id=? AND active='Yes' AND publish_at <= NOW()");
mysqli_stmt_bind_param($stmt_count, "i", $cat_id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$numrows = $row_count['numrows'];
mysqli_stmt_close($stmt_count);

if ($numrows <= 0) {
    echo '<div class="alert alert-info">There are no articles published in this category yet.</div>';
} else {
    
    // 3. Récupérer les articles
    $stmt_posts = mysqli_prepare($connect, "SELECT * FROM posts WHERE category_id=? AND active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT ?, ?");
    mysqli_stmt_bind_param($stmt_posts, "iii", $cat_id, $rows, $postsperpage);
    mysqli_stmt_execute($stmt_posts);
    $run = mysqli_stmt_get_result($stmt_posts);

    while ($row = mysqli_fetch_assoc($run)) {
        
        // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
        $image_html = '';
        $post_img_path = str_replace('../', '', $row['image']);
        $default_img_path = 'assets/img/no-image.png';

        // NIVEAU 1
        if (!empty($row['image']) && file_exists($post_img_path)) {
            $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($row['title']) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        } 
        // NIVEAU 2
        elseif (file_exists($default_img_path)) {
            $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        }
        // NIVEAU 3
        else {
            $image_html = '<svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>No Image</title><rect width="100%" height="100%" fill="#55595c"/>
            <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle">No Image</text></svg>';
        }
        
        echo '
                        <div class="card mb-3 border-0 border-bottom">
                            <div class="row g-0">
								<div class="col-md-4">
									<a href="post?name=' . htmlspecialchars($row['slug']) . '">
										'. $image_html .'
									</a>
								</div>
								<div class="col-md-8">
									<div class="card-body py-3">
										<div class="d-flex justify-content-between align-items-start row">
											<div class="col-md-12">
												<a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none">
													<h5 class="card-title text-primary">' . htmlspecialchars($row['title']) . '</h5>
												</a>
											</div>
										</div>
										
										<div class="d-flex justify-content-between align-items-center mb-2">
											<small class="text-muted">
												Posted by <b><i><i class="fas fa-user"></i> ' . post_author($row['author_id']) . '</i></b>
												on <b><i><i class="far fa-calendar-alt"></i> ' . date($settings['date_format'] . ' H:i', strtotime($row['created_at'])) . '</i></b>
                                                
                                                <span class="ms-3">
                                                    <b><i>' . get_reading_time($row['content']) . '</i></b>
                                                </span>
                                            </small>
											<small class="text-muted">
                                                <i class="fas fa-thumbs-up me-2"></i><b>' . get_post_like_count($row['id']) . '</b>
                                                <i class="fas fa-comments ms-2"></i>
												<a href="post?name=' . htmlspecialchars($row['slug']) . '#comments" class="blog-comments text-decoration-none"><b>' . post_commentscount($row['id']) . '</b></a>
											</small>
										</div>
										
										<p class="card-text">' . htmlspecialchars(short_text(strip_tags(html_entity_decode($row['content'])), 200)) . '</p>
                                        
                                        <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-outline-primary mt-2">
									        Read more
								        </a>
									</div>
								</div>
							</div>
						</div>
';
    }
    mysqli_stmt_close($stmt_posts);
    
    // 4. Pagination
    $maxPage = ceil($numrows / $postsperpage);
    $pagenums = '';
    $safe_slug = urlencode($slug);
    
    echo '<center class="mt-4">';
    
    // Ajout des boutons First/Previous
    if ($pageNum > 1) {
        $page     = $pageNum - 1;
        $previous = "<a href=\"?name=$safe_slug&page=$page\" class='btn btn-outline-secondary m-1'><i class='fa fa-arrow-left'></i> Previous</a> ";
        $first = "<a href=\"?name=$safe_slug&page=1\" class='btn btn-outline-secondary m-1'>First</a> ";
    } else {
        $previous = '';
        $first    = '';
    }
    
    echo $first . $previous;

    // Affichage des numéros de page
    for ($page = 1; $page <= $maxPage; $page++) {
        $active_class = ($page == $pageNum) ? 'btn-primary' : 'btn-outline-primary';
        $pagenums .= "<a href='?name=$safe_slug&page=$page' class='btn $active_class m-1'>$page</a> ";
    }
    echo $pagenums;

    // Ajout des boutons Next/Last
    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        $next = "<a href=\"?name=$safe_slug&page=$page\" class='btn btn-outline-secondary m-1'><i class='fa fa-arrow-right'></i> Next</a> ";
        $last = "<a href=\"?name=$safe_slug&page=$maxPage\" class='btn btn-outline-secondary m-1'>Last</a> ";
    } else {
        $next = '';
        $last = '';
    }
    
    echo $next . $last;
    
    echo '</center>';
}
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