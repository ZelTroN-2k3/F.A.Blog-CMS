<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>

<div class="col-md-8 mb-3">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-microchip me-2"></i> Projects & Tutorials</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Explore our latest hardware and software projects, complete with schematics and code.</p>
            
            <div class="row">
<?php

// --- PAGINATION ---
$perpage = 9; // 9 projets par page (Grille 3x3)
$pageNum = 1;
if (isset($_GET['page'])) { $pageNum = (int)$_GET['page']; }
if ($pageNum < 1) $pageNum = 1;
$offset = ($pageNum - 1) * $perpage;

// --- REQUÊTE FILTRÉE ---
$where_clause = "WHERE p.active='Yes'";
$params = [];
$types = "";

// Filtre Difficulté
if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
    $diff = $_GET['difficulty'];
    $where_clause .= " AND p.difficulty = ?";
    $params[] = $diff;
    $types .= "s";
}

$sql = "SELECT p.*, u.username, u.avatar 
        FROM projects p 
        LEFT JOIN users u ON p.author_id = u.id 
        $where_clause
        ORDER BY p.created_at DESC 
        LIMIT ?, ?";

// Ajout des paramètres de pagination
$params[] = $offset;
$params[] = $perpage;
$types .= "ii";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$run = mysqli_stmt_get_result($stmt);
$count = mysqli_num_rows($run);

if ($count <= 0) {
    echo '<div class="col-12"><div class="alert alert-info">No projects published yet.</div></div>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // 1. Image Robuste
        $img_src = 'assets/img/project-no-image.png';
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img_src = $clean; }
        }
        
        // 2. Badge Difficulté
        $diff_color = 'secondary';
        switch($row['difficulty']) {
            case 'Easy': $diff_color = 'success'; break;
            case 'Intermediate': $diff_color = 'primary'; break;
            case 'Advanced': $diff_color = 'warning'; break;
            case 'Expert': $diff_color = 'danger'; break;
        }
        
        echo '
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm hover-shadow transition-300 border">
                <a href="project?name=' . htmlspecialchars($row['slug']) . '">
                    <img src="' . htmlspecialchars($img_src) . '" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Project Cover" onerror="this.src=\'assets/img/project-no-image.png\';">
                </a>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <span class="badge bg-' . $diff_color . '">' . htmlspecialchars($row['difficulty']) . '</span>
                        ' . (!empty($row['duration']) ? '<span class="badge bg-light text-dark border"><i class="far fa-clock"></i> ' . htmlspecialchars($row['duration']) . '</span>' : '') . '
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
                        <small class="text-muted"><i class="fas fa-user-circle"></i> ' . htmlspecialchars($row['username']) . '</small>
                        <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-sm btn-outline-success rounded-pill">View</a>
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
$q_count = mysqli_query($connect, "SELECT COUNT(id) as c FROM projects WHERE active='Yes'");
$total_rows = mysqli_fetch_assoc($q_count)['c'];
$maxPage = ceil($total_rows / $perpage);

if ($maxPage > 1) {
    echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';
    // Prev
    if ($pageNum > 1) {
        echo '<li class="page-item"><a class="page-link" href="?page='.($pageNum-1).'">&laquo; Prev</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>';
    }
    // Numbers
    for ($i = 1; $i <= $maxPage; $i++) {
        $active = ($i == $pageNum) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
    }
    // Next
    if ($pageNum < $maxPage) {
        echo '<li class="page-item"><a class="page-link" href="?page='.($pageNum+1).'">Next &raquo;</a></li>';
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