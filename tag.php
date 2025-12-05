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

// 1. Trouver le tag basé sur le slug
$stmt_tag = mysqli_prepare($connect, "SELECT * FROM `tags` WHERE slug=?");
mysqli_stmt_bind_param($stmt_tag, "s", $slug);
mysqli_stmt_execute($stmt_tag);
$result_tag = mysqli_stmt_get_result($stmt_tag);

if (mysqli_num_rows($result_tag) == 0) {
    echo '<meta http-equiv="refresh" content="0; url=blog">';
    exit();
}
$row_tag = mysqli_fetch_assoc($result_tag);
$tag_id   = $row_tag['id'];
$tag_name = $row_tag['name'];
mysqli_stmt_close($stmt_tag);
?>
            <div class="col-md-8 mb-3">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white"><i class="fas fa-tag"></i> Articles tagged: <?php
echo htmlspecialchars($tag_name);
?></div>
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

// 2. Compter le nombre total d'articles pour ce tag
$stmt_count = mysqli_prepare($connect, "
    SELECT COUNT(p.id) AS numrows 
    FROM posts p
    JOIN post_tags pt ON p.id = pt.post_id
    WHERE pt.tag_id = ? AND p.active='Yes' AND p.publish_at <= NOW()
");
mysqli_stmt_bind_param($stmt_count, "i", $tag_id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$numrows = $row_count['numrows'];
mysqli_stmt_close($stmt_count);

if ($numrows <= 0) {
    echo '<div class="alert alert-info">There are no articles published with this tag.</div>';
} else {
    
    // 3. Récupérer les articles paginés pour ce tag
    $stmt_posts = mysqli_prepare($connect, "
        SELECT p.* FROM posts p
        JOIN post_tags pt ON p.id = pt.post_id
        WHERE pt.tag_id = ? AND p.active='Yes' AND p.publish_at <= NOW()
        ORDER BY p.created_at DESC 
        LIMIT ?, ?
    ");
    mysqli_stmt_bind_param($stmt_posts, "iii", $tag_id, $rows, $postsperpage);
    mysqli_stmt_execute($stmt_posts);
    $run = mysqli_stmt_get_result($stmt_posts);

    while ($row = mysqli_fetch_assoc($run)) {
        
        // --- GESTION IMAGE SÉCURISÉE ---
        $image_html = '';
        $post_img_path = str_replace('../', '', $row['image']);
        $default_img_path = 'assets/img/no-image.png';

        if (!empty($row['image']) && file_exists($post_img_path)) {
            $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($row['title']) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        } elseif (file_exists($default_img_path)) {
            $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="rounded-start" width="100%" height="100%" style="object-fit: cover;">';
        } else {
            $image_html = '<svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>No Image</title><rect width="100%" height="100%" fill="#55595c"/>
            <text x="37%" y="50%" fill="#eceeef" dy=".3em">No Image</text></svg>';
        }
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
											<small class="text-muted"><i class="fas fa-comments"></i>
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