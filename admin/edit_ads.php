<?php
include "header.php";

// 1. Vérifier l'ID
$id = (int)$_GET['id'];
if (empty($id)) {
    echo '<meta http-equiv="refresh" content="0; url=ads.php">';
    exit;
}

// 2. Récupérer les infos actuelles de la pub
$stmt = mysqli_prepare($connect, "SELECT * FROM ads WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="alert alert-danger">Publicité introuvable.</div>';
    exit;
}

// 3. Traitement du formulaire
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $name     = $_POST['name'];
    $ad_size  = $_POST['ad_size'];
    $link_url = $_POST['link_url'];
    $active   = $_POST['active'];
    $image_path = $row['image_url']; // Par défaut, on garde l'ancienne image

    // Si une NOUVELLE image est envoyée
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $target_dir = "../uploads/ads/";
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "ad_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Supprimer l'ancienne image du serveur pour faire propre
                if (!empty($row['image_url']) && file_exists("../" . $row['image_url'])) {
                    unlink("../" . $row['image_url']);
                }
                $image_path = "uploads/ads/" . $new_name;
            }
        }
    }

    // Mise à jour BDD
    $stmt_update = mysqli_prepare($connect, "UPDATE ads SET name=?, ad_size=?, image_url=?, link_url=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_update, "sssssi", $name, $ad_size, $image_path, $link_url, $active, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
    
    echo '<meta http-equiv="refresh" content="0; url=ads.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Modifier la Publicité</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Détails #<?php echo $id; ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom de la campagne</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lien de destination</label>
                                <input type="text" name="link_url" class="form-control" value="<?php echo htmlspecialchars($row['link_url']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Format de l'image</label>
                                <select name="ad_size" class="form-control" required>
                                    <option value="728x90" <?php if($row['ad_size'] == '728x90') echo 'selected'; ?>>728x90 (Leaderboard)</option>
                                    <option value="970x90" <?php if($row['ad_size'] == '970x90') echo 'selected'; ?>>970x90 (Large Leaderboard)</option>
                                    <option value="468x60" <?php if($row['ad_size'] == '468x60') echo 'selected'; ?>>468x60 (Banner)</option>
                                    <option value="234x60" <?php if($row['ad_size'] == '234x60') echo 'selected'; ?>>234x60 (Half Banner)</option>
                                    <option value="300x250" <?php if($row['ad_size'] == '300x250') echo 'selected'; ?>>300x250 (Rectangle)</option>
                                    <option value="300x600" <?php if($row['ad_size'] == '300x600') echo 'selected'; ?>>300x600 (Skyscraper)</option>
                                    <option value="150x150" <?php if($row['ad_size'] == '150x150') echo 'selected'; ?>>150x150 (Petit Carré)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Statut</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($row['active'] == 'Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($row['active'] == 'No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label>Image Actuelle</label><br>
                            <div class="border p-2 bg-light text-center rounded">
                                <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" class="img-fluid" style="max-height: 150px;">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Remplacer l'image (Optionnel)</label>
                                <input type="file" name="image" class="form-control-file">
                                <small class="text-muted">Laissez vide pour conserver l'image actuelle.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="ads.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>