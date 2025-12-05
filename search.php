<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Fonction locale pour surligner le mot recherché
function highlight_term($text, $word) {
    $word = preg_quote($word, '/');
    return preg_replace("/($word)/i", '<mark class="bg-warning text-dark rounded px-1">$1</mark>', $text);
}
?>
            <div class="col-md-8 mb-3">
                
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <h2 class="h4 m-0"><i class="fas fa-search text-primary me-2"></i> Search Results</h2>
                    <?php if (isset($_GET['q'])): ?>
                        <span class="badge bg-light text-dark border">For : "<?php echo htmlspecialchars($_GET['q']); ?>"</span>
                    <?php endif; ?>
                </div>

<?php
if (isset($_GET['q'])) {
    $word = $_GET['q'];
    
    if (strlen($word) < 2) {
        echo '<div class="alert alert-warning shadow-sm border-0"><i class="fas fa-exclamation-triangle me-2"></i> Please enter at least 2 characters.</div>';
    } else {
        
        $search_word = '%' . $word . '%';

        // ============================================================
        // 1. RECHERCHE DANS LES PROJETS (NOUVEAU)
        // ============================================================
        $stmt_proj = mysqli_prepare($connect, "SELECT * FROM projects WHERE active='Yes' AND (title LIKE ? OR pitch LIKE ?) ORDER BY created_at DESC LIMIT 6");
        mysqli_stmt_bind_param($stmt_proj, "ss", $search_word, $search_word);
        mysqli_stmt_execute($stmt_proj);
        $run_proj = mysqli_stmt_get_result($stmt_proj);
        $count_proj = mysqli_num_rows($run_proj);
        
        if ($count_proj > 0) {
            echo '<h5 class="text-success mb-3"><i class="fas fa-microchip me-2"></i> Projects found (' . $count_proj . ')</h5>';
            echo '<div class="row mb-4">';
            
            while ($row = mysqli_fetch_assoc($run_proj)) {
                // Image
                $img_src = 'assets/img/project-no-image.png';
                if (!empty($row['image'])) {
                    $clean = str_replace('../', '', $row['image']);
                    if (file_exists($clean)) { $img_src = $clean; }
                }
                
                // Difficulté
                $diff_color = 'secondary';
                if($row['difficulty']=='Easy') $diff_color='success';
                if($row['difficulty']=='Intermediate') $diff_color='primary';
                if($row['difficulty']=='Advanced') $diff_color='warning';
                if($row['difficulty']=='Expert') $diff_color='danger';

                // Surlignage
                $title_display = highlight_term(htmlspecialchars($row['title']), $word);
                
                echo '
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm border hover-shadow">
                        <div class="row g-0 h-100">
                            <div class="col-4">
                                <img src="' . htmlspecialchars($img_src) . '" class="img-fluid rounded-start h-100" style="object-fit: cover;" onerror="this.src=\'assets/img/project-no-image.png\';">
                            </div>
                            <div class="col-8">
                                <div class="card-body p-2 d-flex flex-column h-100">
                                    <h6 class="card-title mb-1"><a href="project?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none">' . $title_display . '</a></h6>
                                    <div class="mb-1">
                                        <span class="badge bg-' . $diff_color . '" style="font-size:0.6rem;">' . htmlspecialchars($row['difficulty']) . '</span>
                                    </div>
                                    <p class="card-text small text-muted mb-0 flex-grow-1" style="line-height:1.2;">
                                        ' . highlight_term(htmlspecialchars(short_text($row['pitch'], 50)), $word) . '
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
            }
            echo '</div><hr class="my-4">';
        }
        mysqli_stmt_close($stmt_proj);


        // ============================================================
        // 2. RECHERCHE DANS LES ARTICLES (BLOG)
        // ============================================================
        
        // A. Compter le total
        $stmt_count = mysqli_prepare($connect, "SELECT COUNT(id) AS numrows FROM posts WHERE active='Yes' AND publish_at <= NOW() AND (title LIKE ? OR content LIKE ?)");
        mysqli_stmt_bind_param($stmt_count, "ss", $search_word, $search_word);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count    = mysqli_fetch_assoc($result_count);
        $numrows      = $row_count['numrows'];
        mysqli_stmt_close($stmt_count);

        if ($numrows > 0) {
            echo '<h5 class="text-primary mb-3"><i class="far fa-file-alt me-2"></i> Articles found (' . $numrows . ')</h5>';
            
            // Pagination
            $postsperpage = 8;
            $pageNum = 1;
            if (isset($_GET['page'])) { $pageNum = (int)$_GET['page']; }
            if ($pageNum < 1) $pageNum = 1;
            $rows = ($pageNum - 1) * $postsperpage;

            // B. Requête
            $stmt_results = mysqli_prepare($connect, "SELECT * FROM `posts` WHERE (title LIKE ? OR content LIKE ?) AND active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT ?, ?");
            mysqli_stmt_bind_param($stmt_results, "ssii", $search_word, $search_word, $rows, $postsperpage);
            mysqli_stmt_execute($stmt_results);
            $run = mysqli_stmt_get_result($stmt_results);
            
            echo '<div class="row">'; 
            
            while ($row = mysqli_fetch_assoc($run)) {
                
                // Gestion Image Robuste
                $img_src = 'assets/img/no-image.png';
                if (!empty($row['image'])) {
                    $clean = str_replace('../', '', $row['image']);
                    if (file_exists($clean)) { $img_src = $clean; }
                }
                
                // Surlignage
                $title_display = highlight_term(htmlspecialchars($row['title']), $word);
                $excerpt_raw = short_text(strip_tags(html_entity_decode($row['content'])), 100);
                $excerpt_display = highlight_term(htmlspecialchars($excerpt_raw), $word);

                echo '
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow transition-300">
                        <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none">
                            <img src="' . htmlspecialchars($img_src) . '" class="card-img-top" style="height: 180px; object-fit: cover;" onerror="this.src=\'assets/img/no-image.png\';">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <a href="category?name=' . htmlspecialchars(post_categoryslug($row['category_id'])) . '" class="badge bg-light text-primary border text-decoration-none">
                                    ' . htmlspecialchars(post_category($row['category_id'])) . '
                                </a>
                            </div>
                            
                            <h5 class="card-title mb-2">
                                <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none fw-bold">
                                    ' . $title_display . '
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted small mb-3 flex-grow-1">
                                ' . $excerpt_display . '...
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <small class="text-muted"><i class="far fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($row['created_at'])) . '</small>
                                <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-outline-primary rounded-pill px-3">Read</a>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
            echo '</div>'; 
            mysqli_stmt_close($stmt_results);
            
            // C. Pagination Links
            $maxPage = ceil($numrows / $postsperpage);
            $safe_word = urlencode($word);
            
            if ($maxPage > 1) {
                echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';
                if ($pageNum > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?q='.$safe_word.'&page='.($pageNum-1).'"><i class="fas fa-chevron-left"></i></a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
                }
                for ($page = 1; $page <= $maxPage; $page++) {
                    $active = ($page == $pageNum) ? 'active' : '';
                    echo '<li class="page-item '.$active.'"><a class="page-link" href="?q='.$safe_word.'&page='.$page.'">'.$page.'</a></li>';
                }
                if ($pageNum < $maxPage) {
                    echo '<li class="page-item"><a class="page-link" href="?q='.$safe_word.'&page='.($pageNum+1).'"><i class="fas fa-chevron-right"></i></a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>';
                }
                echo '</ul></nav>';
            }
        }

        // 3. Si RIEN n'est trouvé dans les deux tables
        if ($count_proj == 0 && $numrows == 0) {
             echo '
            <div class="text-center py-5 text-muted">
                <i class="far fa-folder-open fa-4x mb-3 opacity-50"></i>
                <h4>No results found</h4>
                <p>Try different keywords or check spelling.</p>
                <a href="blog" class="btn btn-primary mt-2">View all posts</a>
            </div>';
        }
    }
} else {
    echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
    exit();
}
?>
            </div> <?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>