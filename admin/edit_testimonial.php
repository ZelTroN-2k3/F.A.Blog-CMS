<?php
include "header.php";

// Validation ID
if (!isset($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=testimonials.php">'; exit;
}
$id = (int)$_GET['id'];

// Fetch info
$stmt = mysqli_prepare($connect, "SELECT * FROM testimonials WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="alert alert-danger m-3">Testimonial not found.</div>';
    include "footer.php"; exit;
}

// Traitement
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $name     = $_POST['name'];
    $position = $_POST['position'];
    $content  = $_POST['content'];
    $active   = $_POST['active'];
    $avatar_path = $row['avatar']; // Garder l'ancien par défaut

    // Gestion Upload Image
    if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != "") {
        $target_dir = "../uploads/testimonials/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $new_name = "review_" . uniqid() . "." . $ext;
        
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            
            if (function_exists('optimize_and_save_image')) {
                 $opt_path = optimize_and_save_image($_FILES["avatar"]["tmp_name"], $target_dir . "review_" . uniqid());
                 if ($opt_path) {
                     if (!empty($row['avatar']) && file_exists("../" . $row['avatar'])) { @unlink("../" . $row['avatar']); }
                     $avatar_path = str_replace("../", "", $opt_path);
                 }
            } else {
                $target_file = $target_dir . $new_name;
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    if (!empty($row['avatar']) && file_exists("../" . $row['avatar'])) { @unlink("../" . $row['avatar']); }
                    $avatar_path = "uploads/testimonials/" . $new_name;
                }
            }
        }
    }

    $stmt_up = mysqli_prepare($connect, "UPDATE testimonials SET name=?, position=?, content=?, avatar=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "sssssi", $name, $position, $content, $avatar_path, $active, $id);
    
    if(mysqli_stmt_execute($stmt_up)) {
        echo '<div class="alert alert-success m-3">Testimonial updated successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=testimonials.php">';
        exit;
    }
    mysqli_stmt_close($stmt_up);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Testimonial</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="testimonials.php">Testimonials</a></li>
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
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Author & Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Author Name</label>
                                <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($row['position']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Testimonial Content</label>
                                <textarea name="content" id="summernote" class="form-control" rows="4" required><?php echo html_entity_decode($row['content']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Avatar & Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($row['active']=='Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($row['active']=='No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group text-center">
                                <label class="text-left w-100">Avatar</label>
                                <?php if($row['avatar']): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($row['avatar']); ?>" class="img-circle elevation-2" style="width: 100px; height: 100px; object-fit: cover;">
                                        <div class="small text-muted mt-1">Current Avatar</div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="custom-file text-left">
                                    <input type="file" class="custom-file-input" id="avatarUpload" name="avatar">
                                    <label class="custom-file-label" for="avatarUpload">Change file</label>
                                </div>
                                
                                <div id="preview-container" style="display:none;" class="mt-3">
                                    <img id="image-preview" src="#" class="img-circle elevation-2 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    <div class="small text-success mt-1">New Selection</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Testimonial
                            </button>
                            <a href="testimonials.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>
<script>
$(document).ready(function() {
    $('#avatarUpload').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#preview-container').slideDown();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>