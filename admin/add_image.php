<?php
include "header.php";

if (isset($_POST['add'])) {
    
    // Validation CSRF
    validate_csrf_token();

    $title       = $_POST['title'];
    $active      = $_POST['active'];
    $album_id    = $_POST['album_id'];
    $description = $_POST['description'];
    
    $image = '';
    $uploadOk = 1;
    
    // Gestion de l'upload
    if (isset($_FILES['avafile']['name']) && $_FILES['avafile']['name'] != '') {

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $file_extension = strtolower(pathinfo($_FILES["avafile"]["name"], PATHINFO_EXTENSION));
        
        // Vérification MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES["avafile"]["tmp_name"]);
        finfo_close($finfo);

        if (!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)) {
            echo '<div class="alert alert-danger m-3">Invalid file type. Only JPG, PNG, GIF and WEBP are allowed.</div>';
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            $target_dir = "../uploads/gallery/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Renommage sécurisé
            $new_name = "img_" . time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_name;
            
            if (move_uploaded_file($_FILES["avafile"]["tmp_name"], $target_file)) {
                $image = "uploads/gallery/" . $new_name;
            } else {
                echo '<div class="alert alert-danger m-3">Sorry, there was an error uploading your file.</div>';
                $uploadOk = 0;
            }
        }
    } else {
        // Si l'image est obligatoire, décommentez ceci :
        // echo '<div class="alert alert-warning m-3">Please select an image.</div>';
        // $uploadOk = 0;
    }
    
if ($uploadOk == 1) {
        // MODIFICATION : Ajout de author_id
        $author_id = $user['id'];
        
        $stmt = mysqli_prepare($connect, "INSERT INTO gallery (title, description, album_id, active, image, author_id) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssissi", $title, $description, $album_id, $active, $image, $author_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo '<div class="alert alert-success m-3">Image added successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1;url=gallery.php">';
        exit;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Image</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="gallery.php">Gallery</a></li>
                    <li class="breadcrumb-item active">Add Image</li>
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
                                <input class="form-control form-control-lg" name="title" type="text" placeholder="Enter image title" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" id="summernote" name="description" rows="10"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Upload File</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Choose Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="avafile" name="avafile" required>
                                    <label class="custom-file-label" for="avafile">Choose file</label>
                                </div>
                                <small class="text-muted">Allowed: JPG, PNG, GIF, WEBP.</small>
                            </div>
                            
                            <div id="image-preview-container" class="mt-3 text-center" style="display:none;">
                                <label>Preview:</label><br>
                                <img id="image-preview" src="#" alt="Image Preview" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-3 col-md-12">
                    
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Publish</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control" required>
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Album</label>
                                <select name="album_id" class="form-control" required>
                                    <?php
                                    $crun = mysqli_query($connect, "SELECT * FROM `albums`");
                                    while ($rw = mysqli_fetch_assoc($crun)) {
                                        echo '<option value="' . $rw['id'] . '">' . htmlspecialchars($rw['title']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Add Image
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
    
    // Afficher le nom du fichier et la prévisualisation
    $("#avafile").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);

        // Prévisualisation de l'image
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#image-preview-container').slideDown();
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Note: Summernote est activé automatiquement par footer.php via #summernote
});
</script>

<?php include "footer.php"; ?>