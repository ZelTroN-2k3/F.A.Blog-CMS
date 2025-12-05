<?php
include "header.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=slides.php">';
    exit;
}

$id = (int)$_GET['id'];

// Récupérer les infos
$q = mysqli_prepare($connect, "SELECT * FROM slides WHERE id = ?");
mysqli_stmt_bind_param($q, "i", $id);
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);
$slide = mysqli_fetch_assoc($res);
mysqli_stmt_close($q);

if (!$slide) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">Slide not found.</div></div></div>';
    include "footer.php";
    exit;
}

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title      = $_POST['title'];
    $description= $_POST['description'];
    $link_url   = $_POST['link_url'];
    $active     = $_POST['active'];
    $order      = (int)$_POST['position_order'];
    
    $image_path = $slide['image_url']; // Garder l'ancienne par défaut

    // --- Upload Nouvelle Image ---
    if (isset($_FILES['image_url']['name']) && $_FILES['image_url']['name'] != "") {
        $target_dir = "../uploads/slider/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

        $ext = strtolower(pathinfo($_FILES["image_url"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if(in_array($ext, $allowed)) {
            $new_name_base = "slide_" . uniqid();
            
            // Optimisation ou upload standard
            if (function_exists('optimize_and_save_image')) {
                 $full_dest = optimize_and_save_image($_FILES["image_url"]["tmp_name"], $target_dir . $new_name_base);
                 if ($full_dest) {
                     // Supprimer l'ancienne
                     if(!empty($slide['image_url']) && file_exists("../".$slide['image_url'])) {
                         @unlink("../".$slide['image_url']);
                     }
                     $image_path = str_replace("../", "", $full_dest);
                 }
            } else {
                $target_file = $target_dir . $new_name_base . "." . $ext;
                if(move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)){
                     if(!empty($slide['image_url']) && file_exists("../".$slide['image_url'])) {
                         @unlink("../".$slide['image_url']);
                     }
                     $image_path = "uploads/slider/" . $new_name_base . "." . $ext;
                }
            }
        }
    }

    // Update BDD
    $stmt = mysqli_prepare($connect, "UPDATE slides SET title=?, description=?, image_url=?, link_url=?, position_order=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssisi", $title, $description, $image_path, $link_url, $order, $active, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<div class="alert alert-success m-3">Slide updated successfully! Redirecting...</div>';
    echo '<meta http-equiv="refresh" content="1;url=slides.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Slide</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="slides.php">Slider</a></li>
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
                            <h3 class="card-title">Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($slide['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" id="summernote" rows="3"><?php echo html_entity_decode($slide['description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Button Link</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="url" name="link_url" class="form-control" value="<?php echo htmlspecialchars($slide['link_url']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Image</h3>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($slide['image_url'])): ?>
                                <div class="mb-3 text-center">
                                    <label>Current Image:</label><br>
                                    <img src="../<?php echo htmlspecialchars($slide['image_url']); ?>" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Replace Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="imageInput" name="image_url">
                                    <label class="custom-file-label" for="imageInput">Choose new file (optional)</label>
                                </div>
                            </div>
                            
                            <div id="preview-container" class="mt-3 text-center" style="display:none;">
                                <label>New Selection Preview:</label><br>
                                <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            </div>
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
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($slide['active']=='Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($slide['active']=='No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Order</label>
                                <input type="number" name="position_order" class="form-control" value="<?php echo (int)$slide['position_order']; ?>">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Slide
                            </button>
                            <a href="slides.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);

        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#preview-container').slideDown();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php include "footer.php"; ?>