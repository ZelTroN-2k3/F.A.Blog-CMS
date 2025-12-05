<?php
include "header.php";

// 1. Vérification de l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
    exit;
}

$id = (int)$_GET['id'];

// 2. Récupération des données actuelles
$stmt = mysqli_prepare($connect, "SELECT * FROM `gallery` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">Image not found.</div></div></div>';
    include "footer.php";
    exit;
}

// --- SÉCURITÉ AJOUTÉE ---
if ($user['role'] != 'Admin' && $row['author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only edit your own images.
          </div></div></div>';
    include "footer.php";
    exit;
}
// --- FIN SÉCURITÉ ---

// 3. Traitement du formulaire
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $album_id    = $_POST['album_id'];
    $active      = $_POST['active'];
    
    $image_path = $row['image']; // Par défaut, on garde l'ancienne image

    // Gestion de l'upload d'une NOUVELLE image
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
        finfo_close($finfo);

        if (in_array($file_extension, $allowed_extensions) && in_array($mime_type, $allowed_mime_types)) {
            
            // Création dossier si inexistant
            $target_dir = "../uploads/gallery/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            // Nom unique
            $new_filename = "img_" . time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Suppression de l'ancienne image physique pour nettoyer
                if (!empty($row['image']) && file_exists("../" . $row['image'])) {
                    unlink("../" . $row['image']);
                }
                $image_path = "uploads/gallery/" . $new_filename;
            } else {
                echo '<div class="alert alert-danger m-3">Error moving uploaded file.</div>';
            }
        } else {
            echo '<div class="alert alert-danger m-3">Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.</div>';
        }
    }

    // Mise à jour en base de données
    $stmt = mysqli_prepare($connect, "UPDATE gallery SET title=?, description=?, album_id=?, active=?, image=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssissi", $title, $description, $album_id, $active, $image_path, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<div class="alert alert-success m-3">Image updated successfully! Redirecting...</div>';
    echo '<meta http-equiv="refresh" content="1; url=gallery.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Gallery Image</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="gallery.php">Gallery</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Image Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input class="form-control form-control-lg" name="title" type="text" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" id="summernote" name="description" rows="10"><?php echo html_entity_decode($row['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Current Image</h3>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($row['image'] != ''): ?>
                                <img src="../<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                            <?php else: ?>
                                <span class="text-muted">No image uploaded.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control" required>
                                    <option value="Yes" <?php if ($row['active'] == 'Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if ($row['active'] == 'No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Album</label>
                                <select name="album_id" class="form-control" required>
                                    <?php
                                    $crun = mysqli_query($connect, "SELECT * FROM `albums`");
                                    while ($rw = mysqli_fetch_assoc($crun)) {
                                        $selected = ($row['album_id'] == $rw['id']) ? 'selected' : '';
                                        echo '<option value="' . $rw['id'] . '" ' . $selected . '>' . htmlspecialchars($rw['title']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group">
                                <label>Replace Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="imageInput" name="image">
                                    <label class="custom-file-label" for="imageInput">Choose file</label>
                                </div>
                                <small class="text-muted">Leave empty to keep current.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Image
                            </button>
                            <a href="gallery.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>
</section>

<script>
$(document).ready(function() {
    // Afficher le nom du fichier sélectionné dans l'input custom
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    
    // Note: Summernote est activé automatiquement par footer.php s'il détecte #summernote
});
</script>

<?php include "footer.php"; ?>