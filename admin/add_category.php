<?php
include "header.php";

$msg = ''; 

if (isset($_POST['add'])) {
    validate_csrf_token();

    $category = trim($_POST['category']);
    $description = $_POST['description'];
    $slug = generateSeoURL($category, 0);
    
    // --- MODIFICATION : Récupération de l'ID de l'auteur ---
    $author_id = $user['id'];
    // -------------------------------------------------------

    // --- Gestion de l'image ---
    $image_path = ""; // Par défaut vide
    
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $target_dir = "../uploads/categories/";
        // Créer le dossier si inexistant
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); } 
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "cat_" . time() . "_" . uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_name;
        
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/categories/" . $new_name; 
            } else {
                $msg = '<div class="alert alert-danger">Sorry, there was an error uploading your file.</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">File is not an image.</div>';
        }
    }

    if (empty($msg)) {
        // --- MODIFICATION : Insertion avec author_id ---
        $stmt = mysqli_prepare($connect, "INSERT INTO categories (category, slug, description, image, author_id) VALUES (?, ?, ?, ?, ?)");
        // Notez le "ssssi" -> 4 strings + 1 integer
        mysqli_stmt_bind_param($stmt, "ssssi", $category, $slug, $description, $image_path, $author_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // --- LOG ACTIVITY ---
            log_activity($user['id'], "Create Category", "Created category: " . $category);
            // -----------
            $msg = '<div class="alert alert-success m-3">Category added successfully.</div>';
            echo '<meta http-equiv="refresh" content="1; url=categorys.php">';
        } else {
            $msg = '<div class="alert alert-danger m-3">Error adding category: ' . mysqli_error($connect) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Add Category</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="categorys.php">Categories</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <?php echo $msg; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Category Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" class="form-control" name="category" placeholder="Enter category name" required autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label>Slug (Auto-generated)</label>
                                <input type="text" class="form-control" placeholder="Will be generated automatically" disabled>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="5" placeholder="Short description for this category..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Featured Image</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="form-group mb-3">
                                <label class="d-block text-left">Default Preview</label>
                                <img src="../assets/img/category_default.jpg" class="img-fluid img-thumbnail" style="max-height: 200px;" alt="Default Preview">
                                <p class="text-muted small mt-2"><em>This default image will be used if no file is uploaded.</em></p>
                            </div>
                            
                            <div class="form-group text-left">
                                <label>Upload Image</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input" id="exampleInputFile">
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                </div>
                                <small class="text-muted">Recommended size: 800x400px (JPG/PNG)</small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" name="add" class="btn btn-primary btn-block">
                                <i class="fas fa-plus-circle"></i> Create Category
                            </button>
                            <a href="categorys.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</section>

<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
  bsCustomFileInput.init();
  
  // Petit ajout : changer l'image si l'utilisateur en sélectionne une
  $('#exampleInputFile').change(function(event){
    if(event.target.files.length > 0){
        var tmppath = URL.createObjectURL(event.target.files[0]);
        $(".img-thumbnail").fadeIn("fast").attr('src', tmppath);
    }
  });
});
</script>

<?php include "footer.php"; ?>