<?php
include "core.php";
head();

if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

$user_id = $rowu['id']; // ID de l'utilisateur connecté
$is_admin_or_editor = ($rowu['role'] == 'Admin' || $rowu['role'] == 'Editor');

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
    <div class="col-md-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="fas fa-microchip"></i> My submitted projects
            </div>
            <div class="card-body">

<?php
// REQUÊTE : Récupérer les projets de l'utilisateur
$query = "
    SELECT p.*, c.category, c.slug AS cat_slug 
    FROM projects p
    LEFT JOIN project_categories c ON p.project_category_id = c.id
    WHERE p.author_id = ? 
    ORDER BY p.created_at DESC
";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) <= 0) {
    echo '<div class="alert alert-info">You have not submitted any projects yet.</div>';
} else {
    while ($row = mysqli_fetch_assoc($res)) {
        
        // 1. GESTION IMAGE ROBUSTE
        $img_src = 'assets/img/project-no-image.png'; // Image par défaut spécifique projets
        if (!empty($row['image'])) {
            $clean = str_replace('../', '', $row['image']);
            if (file_exists($clean)) { $img_src = $clean; }
        }
        
        // 2. STATUT
        $status_badge = '';
        if ($row['active'] == 'Yes') {
            $status_badge = '<span class="badge bg-success ms-2" style="font-size: 0.7em;">Published</span>';
        } else {
            $status_badge = '<span class="badge bg-warning ms-2" style="font-size: 0.7em;">Draft</span>';
        }
        
        // 3. DIFFICULTÉ
        $diff_color = 'secondary';
        if($row['difficulty']=='Easy') $diff_color='success';
        if($row['difficulty']=='Intermediate') $diff_color='primary';
        if($row['difficulty']=='Advanced') $diff_color='warning';
        if($row['difficulty']=='Expert') $diff_color='danger';
        
        // 4. CATEGORIE
        $category_name = $row['category'] ? htmlspecialchars($row['category']) : 'Uncategorized';

        // 5. BOUTON ÉDITER
        $edit_button = '';
        if ($is_admin_or_editor) {
            $edit_button = '
                <a href="admin/edit_project.php?id=' . $row['id'] . '" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>
            ';
        }

        // AFFICHAGE CARTE
        echo '
			<div class="card mb-3 shadow-sm border hover-shadow">
			  <div class="row g-0">
				<div class="col-md-4">
                  <a href="project?name=' . htmlspecialchars($row['slug']) . '">
                    <img src="' . htmlspecialchars($img_src) . '" class="rounded-start" width="100%" height="100%" style="object-fit: cover; min-height:160px;" onerror="this.src=\'assets/img/project-no-image.png\';">
                  </a>
                </div>
				<div class="col-md-8">
				  <div class="card-body py-3 d-flex flex-column h-100">
					<div class="row mb-2">
                        <div class="col-md-9">
							<h5 class="card-title mb-1">
                                <a href="project?name=' . htmlspecialchars($row['slug']) . '" class="text-decoration-none text-dark fw-bold">' . htmlspecialchars($row['title']) . '</a>
                            </h5>
                            <div class="mb-2">
                                ' . $status_badge . '
                                <span class="badge bg-' . $diff_color . ' ms-1" style="font-size: 0.7em;">' . htmlspecialchars($row['difficulty']) . '</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size: 0.7em;">' . $category_name . '</span>
                            </div>
						</div>
                        <div class="col-md-3 d-flex justify-content-end align-items-start">
                            ' . $edit_button . '
						</div>
					</div>
                    
                    <p class="card-text text-muted small flex-grow-1">' . htmlspecialchars(short_text($row['pitch'], 120)) . '</p>
					
                    <div class="card-text d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                        <small class="text-muted">
                            <i class="far fa-clock text-success"></i> ' . htmlspecialchars($row['duration']) . '
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-eye text-success"></i> ' . $row['views'] . ' views
                        </small>
                    </div>
				  </div>
				</div>
			  </div>
			</div>			
	    ';
    }
}
mysqli_stmt_close($stmt);
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