<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $name     = $_POST['name'];
    $position = $_POST['position'];
    $content  = $_POST['content'];
    $active   = $_POST['active'];
    $avatar_path = '';

    // Gestion Upload Image
    if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != "") {
        $target_dir = "../uploads/testimonials/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $new_name = "review_" . uniqid() . "." . $ext;
        
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            // Utilisation de votre fonction d'optimisation si disponible
            if (function_exists('optimize_and_save_image')) {
                 $opt_path = optimize_and_save_image($_FILES["avatar"]["tmp_name"], $target_dir . "review_" . uniqid());
                 if ($opt_path) $avatar_path = str_replace("../", "", $opt_path);
            } else {
                // Fallback
                $target_file = $target_dir . $new_name;
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    $avatar_path = "uploads/testimonials/" . $new_name;
                }
            }
        }
    }

    $stmt = mysqli_prepare($connect, "INSERT INTO testimonials (name, position, content, avatar, active) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $name, $position, $content, $avatar_path, $active);
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<div class="alert alert-success m-3">Testimonial added successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=testimonials.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error adding testimonial.</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Testimonial</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="testimonials.php">Testimonials</a></li>
                    <li class="breadcrumb-item active">Add</li>
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
                                <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. John Doe" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Position / Company</label>
                                <input type="text" name="position" class="form-control" placeholder="e.g. CEO at Google">
                            </div>

                            <div class="form-group">
                                <label>Testimonial Content</label>
                                <textarea name="content" id="summernote" class="form-control" rows="4" required></textarea>
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
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group text-center">
                                <label class="text-left w-100">Avatar (Optional)</label>
                                <div class="custom-file text-left mb-3">
                                    <input type="file" class="custom-file-input" id="avatarUpload" name="avatar">
                                    <label class="custom-file-label" for="avatarUpload">Choose file</label>
                                </div>
                                
                                <div id="preview-container" style="display:none;">
                                    <img id="image-preview" src="#" class="img-circle elevation-2 shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                    <div class="mt-2 small text-muted">Preview</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Testimonial
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