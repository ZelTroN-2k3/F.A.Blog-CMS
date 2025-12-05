<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
            <div class="col-md-8 mb-3">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white"><i class="far fa-file-alt"></i> Blog Posts</div>
                    <div class="card-body">
<?php
$postsperpage = 8;

$pageNum = 1;
if (isset($_GET['page'])) {
    $pageNum = $_GET['page'];
}
if (!is_numeric($pageNum)) {
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit();
}
$rows = ($pageNum - 1) * $postsperpage;

$run = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT $rows, $postsperpage");
$count = mysqli_num_rows($run);
if ($count <= 0) {
    echo '<div class="alert alert-info">There are no published posts</div>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
        $image_html = '';
        
        // Nettoyage
        $post_img_path = str_replace('../', '', $row['image']);
        $default_img_path = 'assets/img/no-image.png';

        // NIVEAU 1 : L'image de l'article existe
        if (!empty($row['image']) && file_exists($post_img_path)) {
            $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($row['title']) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        } 
        // NIVEAU 2 : Pas d'image article, mais no-image.png existe
        elseif (file_exists($default_img_path)) {
            $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        }
        // NIVEAU 3 : Rien n'existe -> SVG
        else {
            $image_html = '<svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>No Image</title><rect width="100%" height="100%" fill="#55595c"/>
            <text x="37%" y="50%" fill="#eceeef" dy=".3em">No Image</text></svg>';
        }
        
        // Assignation
        $image = $image_html;
        
        echo '
                        <div class="card mb-3 border-0 border-bottom">
                            <div class="row g-0">
								<div class="col-md-4">
									<a href="post?name=' . htmlspecialchars($row['slug']) . '">
										'. $image .'
									</a>
								</div>
								<div class="col-md-8">
									<div class="card-body py-3">
										<div class="d-flex justify-content-between align-items-start row">
											<div class="col-md-9">
												<a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none">
													<h5 class="card-title text-primary">' . htmlspecialchars($row['title']) . '</h5>
												</a>
											</div>
											<div class="col-md-3 text-end">
												<a href="category?name=' . htmlspecialchars(post_categoryslug($row['category_id'])) . '">
													<span class="badge bg-secondary">' . htmlspecialchars(post_category($row['category_id'])) . '</span>
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

												<i class="fas fa-comments ms-3"></i>
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
    
    // --- Pagination ---
    $query = "SELECT COUNT(id) AS numrows FROM posts WHERE active='Yes' AND publish_at <= NOW()";
    $result  = mysqli_query($connect, $query);
    $row     = mysqli_fetch_array($result);
    $numrows = $row['numrows'];
    $maxPage = ceil($numrows / $postsperpage);
    
    $pagenums = '';
    
    echo '<center class="mt-4">';
    
    // Ajout des boutons First/Previous
    if ($pageNum > 1) {
        $page     = $pageNum - 1;
        $previous = "<a href=\"?page=$page\" class='btn btn-outline-secondary m-1'><i class='fa fa-arrow-left'></i> Previous</a> ";
        $first = "<a href=\"?page=1\" class='btn btn-outline-secondary m-1'>First</a> ";
    } else {
        $previous = '';
        $first    = '';
    }
    
    echo $first . $previous;

    // Affichage des numéros de page
    for ($page = 1; $page <= $maxPage; $page++) {
        $active_class = ($page == $pageNum) ? 'btn-primary' : 'btn-outline-primary';
        $pagenums .= "<a href='?page=$page' class='btn $active_class m-1'>$page</a> ";
    }
    echo $pagenums;

    // Ajout des boutons Next/Last
    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        $next = "<a href=\"?page=$page\" class='btn btn-outline-secondary m-1'><i class='fa fa-arrow-right'></i> Next</a> ";
        $last = "<a href=\"?page=$maxPage\" class='btn btn-outline-secondary m-1'>Last</a> ";
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