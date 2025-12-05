<?php
include "core.php";
head();

if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

$user_id = $rowu['id']; // Récupérer l'ID de l'utilisateur connecté

// --- Vérification du rôle de l'utilisateur connecté ---
$is_admin_or_editor = ($rowu['role'] == 'Admin' || $rowu['role'] == 'Editor');

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
    <div class="col-md-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><i class="fas fa-pen-square"></i> My submitted articles</div>
            <div class="card-body">

<?php
// Préparation de la requête avec Jointure pour avoir la catégorie
$query = "
    SELECT p.*, c.category, c.slug AS cat_slug 
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.author_id = ? 
    ORDER BY p.created_at DESC
";
$user_posts_query = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($user_posts_query, "i", $user_id);
mysqli_stmt_execute($user_posts_query);
$user_posts_result = mysqli_stmt_get_result($user_posts_query);

if (mysqli_num_rows($user_posts_result) <= 0) {
    echo '<div class="alert alert-info">You have not submitted any articles yet.</div>';
} else {
    // Boucle d'affichage en mode "Carte"
    while ($post_row = mysqli_fetch_assoc($user_posts_result)) {
        
        // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
        $image = "";
        
        // Nettoyage du chemin de l'image de l'article
        $post_img_path = str_replace('../', '', $post_row['image']);
        $default_img_path = 'assets/img/no-image.png';

        // NIVEAU 1 : L'image de l'article existe physiquement
        if (!empty($post_row['image']) && file_exists($post_img_path)) {
            $image = '<img src="' . htmlspecialchars($post_img_path) . '" 
                           alt="' . htmlspecialchars($post_row['title']) . '" 
                           class="rounded-start" 
                           width="100%" height="100%" 
                           style="object-fit: cover;">';
        } 
        // NIVEAU 2 : Pas d'image article, mais no-image.png existe
        elseif (file_exists($default_img_path)) {
            $image = '<img src="' . $default_img_path . '" 
                           alt="No Image" 
                           class="rounded-start" 
                           width="100%" height="100%" 
                           style="object-fit: cover;">';
        }
        // NIVEAU 3 : Rien n'existe -> Affichage du SVG de secours
        else {
            $image = '<svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>No Image</title><rect width="100%" height="100%" fill="#55595c"/>
            <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle" font-size="1.2rem">No Image</text></svg>';
        }

        // 2. Gestion du statut (Badge)
        $status_badge = '';
        if ($post_row['active'] == 'Yes') {
            $status_badge = '<span class="badge bg-success ms-2" style="font-size: 0.7em;">Published</span>';
        } else if ($post_row['active'] == 'Pending') {
            $status_badge = '<span class="badge bg-info ms-2" style="font-size: 0.7em;">Pending</span>';
        } else {
            $status_badge = '<span class="badge bg-warning ms-2" style="font-size: 0.7em;">Draft</span>';
        }
        
        // 3. Bouton Edit conditionnel (CORRIGÉ)
        $edit_button = '';
        if ($is_admin_or_editor) {
            // Modification du lien pour pointer vers edit_post.php
            $edit_button = '
                <a href="admin/edit_post.php?id=' . $post_row['id'] . '" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>
            ';
        }
        
        // 4. Catégorie
        $category_name = $post_row['category'] ? htmlspecialchars($post_row['category']) : 'Uncategorized';

        // 5. Affichage de la Carte
        echo '
			<div class="card mb-3 shadow-sm">
			  <div class="row g-0">
				<div class="col-md-4">
                  <a href="post?name=' . htmlspecialchars($post_row['slug']) . '">
                    ' . $image . '
                  </a>
                </div>
				<div class="col-md-8">
				  <div class="card-body py-3">
					<div class="row mb-2">
                        <div class="col-md-8">
							<h5 class="card-title mb-1">
                                <a href="post?name=' . htmlspecialchars($post_row['slug']) . '" class="text-decoration-none text-dark fw-bold">' . htmlspecialchars($post_row['title']) . '</a>
                            </h5>
                            <div class="mb-2">
                                ' . $status_badge . '
                                <span class="badge bg-secondary ms-1" style="font-size: 0.7em;">' . $category_name . '</span>
                            </div>
						</div>
                        <div class="col-md-4 d-flex justify-content-end align-items-start">
                            ' . $edit_button . '
						</div>
					</div>
                    
                    <p class="card-text text-muted small">' . short_text(strip_tags(html_entity_decode($post_row['content'])), 120) . '</p>
					
                    <div class="card-text d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="far fa-calendar-alt text-primary"></i> ' . date($settings['date_format'], strtotime($post_row['created_at'])) . '
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-eye text-primary"></i> ' . $post_row['views'] . ' views
                        </small>
                    </div>
				  </div>
				</div>
			  </div>
			</div>			
	    ';
    }
}
mysqli_stmt_close($user_posts_query);
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