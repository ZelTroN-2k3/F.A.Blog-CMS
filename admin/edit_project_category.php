<?php
include "header.php";
$id = (int)$_GET['id'];
$q = mysqli_query($connect, "SELECT * FROM project_categories WHERE id=$id");
$row = mysqli_fetch_assoc($q);

if (isset($_POST['submit'])) {
    validate_csrf_token();
    $category = $_POST['category'];
    $slug = generateSeoURL($category, 0);
    $desc = $_POST['description'];
    
    // --- GESTION IMAGE ---
    $image = $row['image'];
    if (!empty($_POST['selected_image'])) { $image = $_POST['selected_image']; }

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/categories/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "pcat_" . uniqid() . "." . $ext;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (function_exists('optimize_and_save_image')) {
                $optimized_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $target_dir . "pcat_" . uniqid());
                if ($optimized_path) { $image = str_replace("../", "", $optimized_path); }
            } else {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_name)){ $image = "uploads/categories/" . $new_name; }
            }
        }
    }
    
    $stmt = mysqli_prepare($connect, "UPDATE project_categories SET category=?, slug=?, description=?, image=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $category, $slug, $desc, $image, $id);
    mysqli_stmt_execute($stmt);
    echo '<meta http-equiv="refresh" content="0; url=project_categories.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid"><h1 class="m-0">Edit Project Category</h1></div>
</div>
<section class="content">
    <div class="container-fluid">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($row['category']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title">Featured Image</h3></div>
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <?php 
                                $img_src = !empty($row['image']) ? '../' . $row['image'] : '../assets/img/projects_category_default.jpg'; // Défaut
                                if (strpos($row['image'], '../') === 0) $img_src = $row['image'];
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" id="preview_image_box" class="img-fluid rounded border" style="max-height:150px;" onerror="this.src='../assets/img/projects_category_default.jpg';">
                            </div>
                            <div class="custom-file text-left mb-2">
                                <input type="file" name="image" class="custom-file-input" id="postImage">
                                <label class="custom-file-label" for="postImage">Change File</label>
                            </div>
                            <div class="text-center text-muted mb-2 small">- OR -</div>
                            <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">Select from Library</button>
                            <input type="hidden" name="selected_image" id="selected_image_input">
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Update Category</button>
                            <a href="project_categories.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="filesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Select Image</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
      <div class="modal-body" id="files-gallery-content">Loading...</div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#filesModal').on('show.bs.modal', function() {
        if($('#files-gallery-content').html().indexOf('Loading') !== -1) {
            $.get('ajax_load_files.php', function(data) { $('#files-gallery-content').html(data); });
        }
    });

    $("#postImage").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        $('#selected_image_input').val('');
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#preview_image_box').attr('src', e.target.result); }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
function selectFile(dbValue, fullPath) {
    $('#selected_image_input').val(dbValue);
    $('#preview_image_box').attr('src', fullPath);
    $('#filesModal').modal('hide');
}
</script>

<?php include "footer.php"; ?>