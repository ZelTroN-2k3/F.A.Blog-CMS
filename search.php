<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Fonction locale pour surligner le mot recherché
function highlight_term($text, $word) {
    // Échapper les caractères spéciaux regex
    $word = preg_quote($word, '/');
    // Remplacer le mot (insensible à la casse) par <mark>mot</mark>
    return preg_replace("/($word)/i", '<mark class="bg-warning text-dark rounded px-1">$1</mark>', $text);
}
?>
            <div class="col-md-8 mb-3">
                
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <h2 class="h4 m-0"><i class="fas fa-search text-primary me-2"></i> Résultats de recherche</h2>
                    <?php if (isset($_GET['q'])): ?>
                        <span class="badge bg-light text-dark border">Pour : "<?php echo htmlspecialchars($_GET['q']); ?>"</span>
                    <?php endif; ?>
                </div>

<?php
if (isset($_GET['q'])) {
    $word = $_GET['q'];
    
    if (strlen($word) < 2) {
        echo '<div class="alert alert-warning shadow-sm border-0"><i class="fas fa-exclamation-triangle me-2"></i> Veuillez entrer au moins 2 caractères.</div>';
    } else {
        
        $search_word = '%' . $word . '%';

        // 1. Compter le total
        $stmt_count = mysqli_prepare($connect, "SELECT COUNT(id) AS numrows FROM posts WHERE active='Yes' AND publish_at <= NOW() AND (title LIKE ? OR content LIKE ?)");
        mysqli_stmt_bind_param($stmt_count, "ss", $search_word, $search_word);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count    = mysqli_fetch_assoc($result_count);
        $numrows      = $row_count['numrows'];
        mysqli_stmt_close($stmt_count);

        if ($numrows == 0) {
            echo '
            <div class="text-center py-5 text-muted">
                <i class="far fa-folder-open fa-4x mb-3 opacity-50"></i>
                <h4>Aucun résultat trouvé</h4>
                <p>Essayez avec d\'autres mots-clés ou vérifiez l\'orthographe.</p>
                <a href="blog" class="btn btn-primary mt-2">Voir tous les articles</a>
            </div>';
        } else {
        
            // 2. Pagination
            $postsperpage = 8;
            $pageNum = 1;
            if (isset($_GET['page'])) { $pageNum = (int)$_GET['page']; }
            if (!is_numeric($pageNum) || $pageNum < 1) { echo '<meta http-equiv="refresh" content="0; url=blog">'; exit(); }
            $rows = ($pageNum - 1) * $postsperpage;

            // 3. Requête des résultats
            $stmt_results = mysqli_prepare($connect, "SELECT * FROM `posts` WHERE (title LIKE ? OR content LIKE ?) AND active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT ?, ?");
            mysqli_stmt_bind_param($stmt_results, "ssii", $search_word, $search_word, $rows, $postsperpage);
            mysqli_stmt_execute($stmt_results);
            $run = mysqli_stmt_get_result($stmt_results);
            
            echo '<div class="row">'; // Début grille
            
            while ($row = mysqli_fetch_assoc($run)) {
                
                // --- GESTION IMAGE SÉCURISÉE ---
                $image_html = '';
                $post_img_path = str_replace('../', '', $row['image']);
                $default_img_path = 'assets/img/no-image.png';

                // NIVEAU 1
                if (!empty($row['image']) && file_exists($post_img_path)) {
                    $image_html = '<img src="' . htmlspecialchars($post_img_path) . '" alt="' . htmlspecialchars($row['title']) . '" class="card-img-top" style="height: 180px; object-fit: cover;">';
                } 
                // NIVEAU 2
                elseif (file_exists($default_img_path)) {
                    $image_html = '<img src="' . $default_img_path . '" alt="No Image" class="card-img-top" style="height: 180px; object-fit: cover;">';
                }
                // NIVEAU 3
                else {
                    $image_html = '<div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;"><i class="fas fa-image fa-2x opacity-50"></i></div>';
                }
                
                $image = $image_html;
                
                // Préparation du texte avec surlignage
                $title_display = highlight_term(htmlspecialchars($row['title']), $word);
                $excerpt_raw = short_text(strip_tags(html_entity_decode($row['content'])), 120);
                $excerpt_display = highlight_term(htmlspecialchars($excerpt_raw), $word);

                echo '
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow transition-300">
                        <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none">
                            '. $image .'
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
                                <a href="post?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lire</a>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
            echo '</div>'; // Fin grille
            mysqli_stmt_close($stmt_results);
            
            // 4. Pagination (Style Bootstrap)
            $maxPage = ceil($numrows / $postsperpage);
            $safe_word = urlencode($word);
            
            if ($maxPage > 1) {
                echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';
                
                // Prev
                if ($pageNum > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?q='.$safe_word.'&page='.($pageNum-1).'"><i class="fas fa-chevron-left"></i></a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
                }
                
                // Numbers
                for ($page = 1; $page <= $maxPage; $page++) {
                    $active = ($page == $pageNum) ? 'active' : '';
                    echo '<li class="page-item '.$active.'"><a class="page-link" href="?q='.$safe_word.'&page='.$page.'">'.$page.'</a></li>';
                }
                
                // Next
                if ($pageNum < $maxPage) {
                    echo '<li class="page-item"><a class="page-link" href="?q='.$safe_word.'&page='.($pageNum+1).'"><i class="fas fa-chevron-right"></i></a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>';
                }
                
                echo '</ul></nav>';
            }
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