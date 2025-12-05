<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title      = $_POST['title'];
    $description= $_POST['description'];
    $link_url   = $_POST['link_url'];
    $active     = $_POST['active'];
    $order      = (int)$_POST['position_order'];
    $image_path = '';

    // --- Gestion de l'Upload ---
    if (isset($_FILES['image_url']['name']) && $_FILES['image_url']['name'] != "") {
        
        $target_dir = "../uploads/slider/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

        $ext = strtolower(pathinfo($_FILES["image_url"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if(in_array($ext, $allowed)) {
            $new_name_base = "slide_" . uniqid(); 
            
            // Si vous avez une fonction d'optimisation
            if (function_exists('optimize_and_save_image')) {
                 $full_dest = optimize_and_save_image($_FILES["image_url"]["tmp_name"], $target_dir . $new_name_base);
                 if ($full_dest) {
                     $image_path = str_replace("../", "", $full_dest);
                 }
            } else {
                // Fallback classique
                $target_file = $target_dir . $new_name_base . "." . $ext;
                if(move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)){
                     $image_path = "uploads/slider/" . $new_name_base . "." . $ext;
                }
            }
        } else {
            echo '<div class="alert alert-danger m-3">Invalid file format.</div>';
        }
    }

    if($image_path) {
        $stmt = mysqli_prepare($connect, "INSERT INTO slides (title, description, image_url, link_url, position_order, active) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssis", $title, $description, $image_path, $link_url, $order, $active);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo '<div class="alert alert-success m-3">Slide added successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1;url=slides.php">';
        exit;
    } else {
        echo '<div class="alert alert-warning m-3">Image upload failed or missing.</div>';
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Slide</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="slides.php">Slider</a></li>
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
                <div class="col-lg-9 col-md-12">
                    
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Slide Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control form-control-lg" placeholder="Slide Heading" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" id="summernote" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Button Link (URL)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="url" name="link_url" class="form-control" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Slide Image</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Choose Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="imageInput" name="image_url" required>
                                    <label class="custom-file-label" for="imageInput">Choose file</label>
                                </div>
                                <small class="text-muted">Recommended size: 1920x600px (JPG, PNG, WEBP)</small>
                            </div>
                            <div id="preview-container" class="mt-3 text-center" style="display:none;">
                                <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="position_order" class="form-control" value="0">
                                <small class="text-muted">Lower numbers appear first.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Slide
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
    // File Input Name & Preview
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