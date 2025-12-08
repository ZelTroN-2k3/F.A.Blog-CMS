<?php
include "core.php";

// SEO Spécifique
$pagetitle = "Shop";
$description = "Buy our specialized kits, hardware modules and project files.";

head();

// Sidebar (si activée à gauche)
if ($settings['sidebar_position'] == 'Left') { sidebar(); }
?>

<div class="col-md-8 mb-3">

    <div class="card shadow-sm border-0 mb-4 bg-primary text-white overflow-hidden">
        <div class="card-body p-4 text-center position-relative">
            <i class="fas fa-shopping-cart position-absolute" style="font-size: 10rem; opacity: 0.1; right: -20px; top: -20px; transform: rotate(-15deg);"></i>
            
            <h1 class="display-5 fw-bold"><i class="fas fa-store me-2"></i> Official Shop</h1>
            <p class="lead mb-0 opacity-75">Hardware kits, 3D files and premium resources.</p>
        </div>
    </div>

    <div class="row">
<?php
// --- PAGINATION ---
$perpage = 9; // Plus de produits par page que d'articles
$pageNum = 1;
if (isset($_GET['page'])) { $pageNum = (int)$_GET['page']; }
if ($pageNum < 1) $pageNum = 1;
$offset = ($pageNum - 1) * $perpage;

// --- REQUÊTE : SEULEMENT LES PRODUITS ---
$sql = "SELECT p.*, c.category as cat_name 
        FROM projects p 
        LEFT JOIN project_categories c ON p.project_category_id = c.id
        WHERE p.active='Yes' AND p.is_product='Yes'
        ORDER BY p.created_at DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "ii", $offset, $perpage);
mysqli_stmt_execute($stmt);
$run = mysqli_stmt_get_result($stmt);
$count = mysqli_num_rows($run);

if ($count <= 0) {
    echo '<div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3"></i><br>
                <h4>No products available yet.</h4>
                <p>Check back later for new kits and supplies!</p>
            </div>
          </div>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // Image
        $img_src = 'assets/img/project-no-image.png';
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img_src = $clean; }
        }
        
        // État du stock (Badge visuel)
        $stock_badge = '';
        if ($row['stock_status'] == 'Out of Stock') {
            $stock_badge = '<span class="badge bg-secondary position-absolute top-0 start-0 m-2 shadow-sm">Out of Stock</span>';
        } elseif ($row['stock_status'] == 'Pre-order') {
            $stock_badge = '<span class="badge bg-info position-absolute top-0 start-0 m-2 shadow-sm">Pre-order</span>';
        }

        echo '
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm hover-shadow transition-300 border-0">
                <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="d-block position-relative text-decoration-none">
                    <img src="' . htmlspecialchars($img_src) . '" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Product">
                    ' . $stock_badge . '
                    <span class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 fw-bold shadow-sm" style="border-bottom-left-radius: 10px; font-size: 1.1rem;">
                        $' . number_format($row['price'], 2) . '
                    </span>
                </a>
                <div class="card-body d-flex flex-column text-center">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                        ' . htmlspecialchars($row['cat_name'] ?? 'Item') . '
                    </small>
                    
                    <h5 class="card-title fw-bold mt-1 mb-2">
                        <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="text-dark text-decoration-none">
                            ' . htmlspecialchars($row['title']) . '
                        </a>
                    </h5>
                    
                    <div class="mt-auto pt-3">
                        <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            View Details
                        </a>
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
$stmt_c = mysqli_query($connect, "SELECT COUNT(id) as c FROM projects WHERE active='Yes' AND is_product='Yes'");
$total_rows = mysqli_fetch_assoc($stmt_c)['c'];
$maxPage = ceil($total_rows / $perpage);

if ($maxPage > 1) {
    echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';
    if ($pageNum > 1) { echo '<li class="page-item"><a class="page-link" href="?page='.($pageNum-1).'">&laquo;</a></li>'; }
    for ($i = 1; $i <= $maxPage; $i++) {
        $active = ($i == $pageNum) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
    }
    if ($pageNum < $maxPage) { echo '<li class="page-item"><a class="page-link" href="?page='.($pageNum+1).'">&raquo;</a></li>'; }
    echo '</ul></nav>';
}
?>

</div>

<?php
// Sidebar (si activée à droite)
if ($settings['sidebar_position'] == 'Right') { sidebar(); }
footer();
?>