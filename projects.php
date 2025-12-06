<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// --- GESTION DES FILTRES (Catégorie & Difficulté) ---
$page_title_display = "Projects & Tutorials";
$page_desc_display = "Explore our latest hardware and software projects.";

$where_clause = "WHERE p.active='Yes'";
$params = [];
$types = "";

// 1. Filtre par Catégorie
$cat_header_img = ''; // Variable pour stocker l'image

// 1. Filtre par Catégorie
$cat_header_img = ''; // Variable pour stocker l'image

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $cat_slug = $_GET['category'];
    
    $stmt_cat = mysqli_prepare($connect, "SELECT * FROM project_categories WHERE slug = ?");
    mysqli_stmt_bind_param($stmt_cat, "s", $cat_slug);
    mysqli_stmt_execute($stmt_cat);
    $res_cat = mysqli_stmt_get_result($stmt_cat);
    
    if ($row_cat = mysqli_fetch_assoc($res_cat)) {
        $where_clause .= " AND p.project_category_id = ?";
        $params[] = $row_cat['id'];
        $types .= "i";
        
        $page_title_display = '<i class="fas fa-tag me-2"></i> ' . htmlspecialchars($row_cat['category']);
        if(!empty($row_cat['description'])) {
            $page_desc_display = nl2br(htmlspecialchars($row_cat['description']));
        }
        
        // --- GESTION IMAGE CATÉGORIE (Correction) ---
        // 1. Par défaut, on met l'image générique
        $cat_header_img = 'assets/img/projects_category_default.jpg';

        // 2. Si une image personnalisée existe, on l'utilise
        if (!empty($row_cat['image'])) {
            $clean_path = str_replace('../', '', $row_cat['image']);
            if (file_exists($clean_path)) {
                $cat_header_img = $clean_path;
            }
        }
        // ---------------------------------------------
    }
    mysqli_stmt_close($stmt_cat);
}

// 2. Filtre par Difficulté
if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
    $diff = $_GET['difficulty'];
    $where_clause .= " AND p.difficulty = ?";
    $params[] = $diff;
    $types .= "s";
    
    $diff_label = htmlspecialchars($diff);
    if ($page_title_display == "Projects & Tutorials") {
        $page_title_display = "Difficulty: " . $diff_label;
    } else {
        $page_title_display .= " <small class='text-white-50'>(" . $diff_label . ")</small>";
    }
}
?>

<div class="col-md-8 mb-3">

    <div class="card shadow-sm border-0 mb-4 overflow-hidden"> <?php if (!empty($cat_header_img)): ?>
            <div style="height: 200px; overflow: hidden;">
                <img src="<?php echo htmlspecialchars($cat_header_img); ?>" class="w-100 h-100" style="object-fit: cover; object-position: center;" alt="Category Cover">
            </div>
        <?php endif; ?>
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><?php echo $page_title_display; ?></h5>
        </div>
        <div class="card-body">
            <p class="text-muted"><?php echo $page_desc_display; ?></p>
            
            <div class="row">
<?php
// --- PAGINATION ---
// Utilisation du paramètre admin, ou 3 par défaut si vide
$perpage = !empty($settings['projects_per_page']) ? (int)$settings['projects_per_page'] : 3;
$pageNum = 1;
if (isset($_GET['page'])) { $pageNum = (int)$_GET['page']; }
if ($pageNum < 1) $pageNum = 1;
$offset = ($pageNum - 1) * $perpage;

// --- REQUÊTE PRINCIPALE ---
// Note: On ajoute LEFT JOIN project_categories pour récupérer le nom de la cat
$sql = "SELECT p.*, u.username, u.avatar, c.category as cat_name, c.slug as cat_slug 
        FROM projects p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN project_categories c ON p.project_category_id = c.id
        $where_clause
        ORDER BY p.created_at DESC 
        LIMIT ?, ?";

// Ajout des paramètres de pagination
$params[] = $offset;
$params[] = $perpage;
$types .= "ii";

$stmt = mysqli_prepare($connect, $sql);
if(!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$run = mysqli_stmt_get_result($stmt);
$count = mysqli_num_rows($run);

if ($count <= 0) {
    echo '<div class="col-12"><div class="alert alert-info">No projects found here yet.</div></div>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // Image Robuste
        $img_src = 'assets/img/project-no-image.png'; // Défaut
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img_src = $clean; }
        }
        
        // Badge Difficulté
        $diff_color = 'secondary';
        switch($row['difficulty']) {
            case 'Easy': $diff_color = 'success'; break;
            case 'Intermediate': $diff_color = 'primary'; break;
            case 'Advanced': $diff_color = 'warning'; break;
            case 'Expert': $diff_color = 'danger'; break;
        }
        
        // Nom Catégorie
        $cat_badge = '';
        if(!empty($row['cat_name'])) {
            $cat_badge = '<a href="projects?category='.htmlspecialchars($row['cat_slug']).'" class="badge bg-light text-secondary border text-decoration-none me-1">'.htmlspecialchars($row['cat_name']).'</a>';
        }
        
        echo '
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm hover-shadow transition-300 border">
                <a href="project?name=' . htmlspecialchars($row['slug']) . '">
                    <img src="' . htmlspecialchars($img_src) . '" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Project Cover" onerror="this.src=\'assets/img/project-no-image.png\';">
                </a>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        ' . $cat_badge . '
                        <span class="badge bg-' . $diff_color . '">' . htmlspecialchars($row['difficulty']) . '</span>
                    </div>
                    
                    <h6 class="card-title fw-bold">
                        <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none">
                            ' . htmlspecialchars($row['title']) . '
                        </a>
                    </h6>
                    
                    <p class="card-text small text-muted mb-3 flex-grow-1">
                        ' . htmlspecialchars(short_text($row['pitch'], 80)) . '
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                        <div>
                            <small class="text-muted me-2" title="Views">
                                <i class="fas fa-eye"></i> ' . $row['views'] . '
                            </small>
                            <small class="text-muted" title="Likes">
                                <i class="fas fa-thumbs-up"></i> ' . get_project_like_count($row['id']) . '
                            </small>
                        </div>
                        <small class="text-muted"><i class="fas fa-user-circle"></i> ' . htmlspecialchars($row['username']) . '</small>
                        <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-outline-success /*rounded-pill*/">View</a>
                    </div>                    
                </div>
            </div>
        </div>';
    }
}
?>
            </div>

<?php
// --- PAGINATION LINKS ---
// On doit refaire la requête COUNT avec les mêmes filtres ($where_clause) mais sans LIMIT
// Attention : il faut reconstruire les params sans offset/limit
$sql_count = "SELECT COUNT(p.id) as c FROM projects p $where_clause";
// On enlève les 2 derniers params (offset, limit) pour le count
array_pop($params); 
array_pop($params);
$types_count = substr($types, 0, -2);

$stmt_c = mysqli_prepare($connect, $sql_count);
if(!empty($types_count)) {
    mysqli_stmt_bind_param($stmt_c, $types_count, ...$params);
}
mysqli_stmt_execute($stmt_c);
$total_rows = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_c))['c'];
$maxPage = ceil($total_rows / $perpage);

if ($maxPage > 1) {
    // Reconstitution de l'URL de base pour la pagination
    $base_url = '?';
    if(isset($_GET['category'])) $base_url .= 'category='.urlencode($_GET['category']).'&';
    if(isset($_GET['difficulty'])) $base_url .= 'difficulty='.urlencode($_GET['difficulty']).'&';

    echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';
    // Prev
    if ($pageNum > 1) {
        echo '<li class="page-item"><a class="page-link" href="'.$base_url.'page='.($pageNum-1).'">&laquo; Prev</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>';
    }
    // Numbers
    for ($i = 1; $i <= $maxPage; $i++) {
        $active = ($i == $pageNum) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="'.$base_url.'page='.$i.'">'.$i.'</a></li>';
    }
    // Next
    if ($pageNum < $maxPage) {
        echo '<li class="page-item"><a class="page-link" href="'.$base_url.'page='.($pageNum+1).'">Next &raquo;</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
    }
    echo '</ul></nav>';
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