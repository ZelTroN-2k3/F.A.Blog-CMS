<?php
include "core.php";
head();

if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

$user_id = $rowu['id'];

// Gérer la suppression d'un favori (optionnel, mais pratique)
if (isset($_GET['remove-favorite'])) {
    $post_id_to_remove = (int)$_GET["remove-favorite"];
    
    $stmt_delete = mysqli_prepare($connect, "DELETE FROM `user_favorites` WHERE user_id=? AND post_id=?");
    mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $post_id_to_remove); 
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);
    
    // Recharger la page sans le paramètre GET
    echo '<meta http-equiv="refresh" content="0;url=my-favorites.php">';
    exit;
}

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
    <div class="col-md-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><i class="fas fa-bookmark"></i> My favorites</div>
            <div class="card-body">

<?php
// 2. Récupérer les articles favoris
$stmt_favs = mysqli_prepare($connect, "
    SELECT p.* FROM `posts` p
    JOIN `user_favorites` uf ON p.id = uf.post_id
    WHERE uf.user_id = ?
    ORDER BY uf.created_at DESC
");
mysqli_stmt_bind_param($stmt_favs, "i", $user_id);
mysqli_stmt_execute($stmt_favs);
$query = mysqli_stmt_get_result($stmt_favs);

$count = mysqli_num_rows($query);
if ($count <= 0) {
    echo '<div class="alert alert-info">You don\'t have any favorite items yet.</div>';
} else {
    // 3. Afficher chaque article (similaire à blog.php)
    while ($row = mysqli_fetch_array($query)) {
        
        // --- GESTION IMAGE SÉCURISÉE (3 NIVEAUX) ---
        $image = "";
        
        // Nettoyage du chemin de l'image de l'article
        $post_img_path = str_replace('../', '', $row['image']);
        $default_img_path = 'assets/img/no-image.png';

        // NIVEAU 1 : L'image de l'article existe physiquement
        if (!empty($row['image']) && file_exists($post_img_path)) {
            $image = '<img src="' . htmlspecialchars($post_img_path) . '" 
                           alt="' . htmlspecialchars($row['title']) . '" 
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
            $image = '<svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>No Image</title><rect width="100%" height="100%" fill="#55595c"/>
            <text x="50%" y="50%" fill="#eceeef" dy=".3em" text-anchor="middle">No Image</text></svg>';
        }

        echo '
			<div class="card mb-3">
			  <div class="row g-0">
				<div class="col-md-4">
                  <a href="post?name=' . htmlspecialchars($row['slug']) . '">
                    ' . $image . '
                  </a>
                </div>
				<div class="col-md-8">
				  <div class="card-body py-3">
					<h6 class="card-title mb-2">
						<div class="row">
							<div class="col-md-9">
								<a href="post?name=' . htmlspecialchars($row['slug']) . '" class="text-primary">' . htmlspecialchars($row['title']) . '</a>
							</div>
							<div class="col-md-3 d-flex justify-content-end">
								<a href="?remove-favorite=' . $row['id'] . '" class="btn btn-danger btn-sm" title="Retirer des favoris" onclick="return confirm(\'Remove this post from your favorites?\');">
									<i class="fa fa-trash"></i>
								</a>
							</div>
						</div>
					</h6>
					<p class="card-text">' . short_text(strip_tags(html_entity_decode($row['content'])), 150) . '</p>
					<p class="card-text">
                        <small class="text-muted">
                            <i class="far fa-calendar-alt"></i> Added: ' . date($settings['date_format'], strtotime($row['created_at'])) . '
                        </small>
                    </p>
				  </div>
				</div>
			  </div>
			</div>			
	    ';
	}
}
mysqli_stmt_close($stmt_favs);
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