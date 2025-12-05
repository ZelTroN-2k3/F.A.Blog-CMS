<?php
include "core.php";
head();

// Sidebar à gauche si activée
if ($settings['sidebar_position'] == 'Left') {
    sidebar();
}
?>

<div class="col-md-8 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-4 pl-4">
            <h3 class="text-primary"><i class="fas fa-th-large me-2"></i> All Categories</h3>
            <p class="text-muted">Explore our various topics.</p>
        </div>
        <div class="card-body">
            <div class="row">

<?php
// Récupération de toutes les catégories
// On trie par nom (ASC)
$sql = "SELECT * FROM categories ORDER BY category ASC";
$run = mysqli_query($connect, $sql);
$count = mysqli_num_rows($run);

if ($count <= 0) {
    echo '<div class="col-12"><div class="alert alert-info">No categories found.</div></div>';
} else {
    while ($row = mysqli_fetch_assoc($run)) {
        
        // Gestion de l'image
        $cat_img = !empty($row['image']) ? $row['image'] : 'assets/img/category_default.jpg'; // Image par défaut si vide
        
        // Si l'image n'existe pas physiquement, on met un placeholder gris
        if (!file_exists($cat_img) && empty($row['image'])) {
             $img_html = '<div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 150px; width: 100%;"><i class="fas fa-folder fa-3x"></i></div>';
        } else {
             $img_html = '<img src="' . htmlspecialchars($cat_img) . '" class="card-img-top" alt="' . htmlspecialchars($row['category']) . '" style="height: 150px; object-fit: cover;">';
        }

        // Comptage des articles dans cette catégorie (Optionnel mais sympa)
        $cat_id = $row['id'];
        $count_q = mysqli_query($connect, "SELECT COUNT(id) as total FROM posts WHERE category_id='$cat_id' AND active='Yes' AND publish_at <= NOW()");
        $count_r = mysqli_fetch_assoc($count_q);
        $articles_count = $count_r['total'];

        echo '
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100 border shadow-sm hover-shadow transition">
                <a href="category?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none text-dark">
                    ' . $img_html . '
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary font-weight-bold mb-2">' . htmlspecialchars($row['category']) . '</h5>
                        
                        '. ($row['description'] ? '<p class="card-text text-muted small mb-2">' . htmlspecialchars(short_text($row['description'], 80)) . '</p>' : '') .'
                        
                        <span class="badge bg-light text-dark border mt-2">
                            ' . $articles_count . ' article' . ($articles_count > 1 ? 's' : '') . '
                        </span>
                    </div>
                </a>
            </div>
        </div>';
    }
}
?>
            </div> </div>
    </div>
</div>

<?php
// Sidebar à droite si activée
if ($settings['sidebar_position'] == 'Right') {
    sidebar();
}
footer();
?>