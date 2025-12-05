<?php
include "header.php";

$message = '';

// --- SUPPRESSION DE L'IMAGE ---
if (isset($_GET['delete_image'])) {
    validate_csrf_token_get();
    
    if (!empty($settings['maintenance_image']) && file_exists('../' . $settings['maintenance_image'])) {
        unlink('../' . $settings['maintenance_image']);
    }
    
    // Mise à jour BDD
    $stmt = mysqli_prepare($connect, "UPDATE settings SET maintenance_image = NULL WHERE id = 1");
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0;url=maintenance.php">';
    exit;
}

// --- SAUVEGARDE ---
if (isset($_POST['save_maintenance'])) {
    validate_csrf_token();

    $mode = $_POST['maintenance_mode'];
    $title = $_POST['maintenance_title'];
    $content = $_POST['maintenance_message'];
    $image_path = $settings['maintenance_image']; // Garder l'ancienne par défaut

    // GESTION UPLOAD IMAGE
    if (isset($_FILES['maintenance_image']['name']) && $_FILES['maintenance_image']['name'] != "") {
        $target_dir = "../uploads/other/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        
        $ext = strtolower(pathinfo($_FILES["maintenance_image"]["name"], PATHINFO_EXTENSION));
        $new_name = "maintenance_bg_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES["maintenance_image"]["tmp_name"], $target_file)) {
                // Supprimer l'ancienne
                if (!empty($settings['maintenance_image']) && file_exists('../' . $settings['maintenance_image'])) {
                    unlink('../' . $settings['maintenance_image']);
                }
                $image_path = "uploads/other/" . $new_name;
            }
        }
    }

    // Mise à jour BDD
    $stmt = mysqli_prepare($connect, "UPDATE settings SET maintenance_mode=?, maintenance_title=?, maintenance_message=?, maintenance_image=? WHERE id=1");
    mysqli_stmt_bind_param($stmt, "ssss", $mode, $title, $content, $image_path);
    
    if(mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Saved!</h5>
                        Settings updated.
                    </div>';
        // Recharger les settings
        $res = mysqli_query($connect, "SELECT * FROM settings WHERE id=1");
        $settings = mysqli_fetch_assoc($res);
    } else {
        $message = '<div class="alert alert-danger">Error saving settings.</div>';
    }
    mysqli_stmt_close($stmt);
}

$is_maintenance = ($settings['maintenance_mode'] == 'On');
$card_class = $is_maintenance ? 'card-danger' : 'card-success';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-tools"></i> Maintenance Mode</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $message; ?>

        <form action="maintenance.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card <?php echo $card_class; ?> card-outline">
                        <div class="card-header"><h3 class="card-title">Status</h3></div>
                        <div class="card-body text-center">
                            <div class="form-group">
                                <select name="maintenance_mode" class="form-control custom-select text-center fw-bold" style="font-size: 1.2rem;">
                                    <option value="Off" <?php if (!$is_maintenance) echo 'selected'; ?>>🟢 LIVE</option>
                                    <option value="On" <?php if ($is_maintenance) echo 'selected'; ?>>🔴 MAINTENANCE</option>
                                </select>
                            </div>
                            <hr>
                            <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-block"><i class="fas fa-eye"></i> Preview Site</a>
                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-image"></i> Background Image</h3></div>
                        <div class="card-body text-center">
                            <?php if (!empty($settings['maintenance_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($settings['maintenance_image']); ?>" class="img-fluid mb-2 rounded border" style="max-height: 150px;">
                                <a href="?delete_image=1&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-danger btn-sm btn-block" onclick="return confirm('Delete background image?');">
                                    <i class="fas fa-trash"></i> Remove Image
                                </a>
                            <?php else: ?>
                                <div class="alert alert-light border">No image set.</div>
                            <?php endif; ?>
                            
                            <div class="form-group mt-3">
                                <label class="text-left w-100">Upload New Image</label>
                                <div class="custom-file">
                                    <input type="file" name="maintenance_image" class="custom-file-input" id="bgFile">
                                    <label class="custom-file-label" for="bgFile">Choose file</label>
                                </div>
                                <small class="text-muted">Recommended: 1920x1080 (JPG/PNG)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Page Content</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="maintenance_title" class="form-control" value="<?php echo htmlspecialchars($settings['maintenance_title']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <textarea id="summernote" name="maintenance_message"><?php echo htmlspecialchars($settings['maintenance_message']); ?></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="save_maintenance" class="btn btn-primary float-right"><i class="fas fa-save"></i> Update Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(function () {
    $('#summernote').summernote({ height: 300 });
    
    // Afficher le nom du fichier sélectionné
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>