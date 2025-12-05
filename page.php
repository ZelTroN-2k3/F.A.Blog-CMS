<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Obtenir l'instance de HTML Purifier
$purifier = get_purifier();

$slug = $_GET['name'] ?? '';
if (empty($slug)) {
    echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
    exit;
}

// Utiliser une requête préparée pour la sécurité
$stmt = mysqli_prepare($connect, "SELECT * FROM `pages` WHERE slug=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$run = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($run) == 0) {
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
    exit;
}

$row = mysqli_fetch_assoc($run);
mysqli_stmt_close($stmt);
?>

<div class="col-md-8 mb-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($row['title']); ?>
        </div>
        <div class="card-body">
           <?php echo $purifier->purify($row['content']); // Nettoyer le contenu avec HTML Purifier ?>
        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>