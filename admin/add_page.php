<?php
include "header.php";

if (isset($_POST['add'])) {
    
    // --- Validation CSRF ---
    validate_csrf_token();

    $title   = $_POST['title'];
    $content = $_POST['content'];
    $active  = $_POST['active']; 
    
    // --- GESTION DU SLUG ---
    // Si un slug est fourni manuellement, on l'utilise, sinon on le génère depuis le titre
    // Le "0" désactive les chiffres aléatoires pour avoir des URLs propres (ex: /about-us)
    $slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug'], 0) : generateSeoURL($title, 0);

    // --- SEO ---
    $meta_title       = !empty($_POST['meta_title']) ? $_POST['meta_title'] : $title;
    $meta_description = $_POST['meta_description'];

    // --- GESTION IMAGE (Upload OU Bibliothèque) ---
    $image = isset($_POST['selected_image']) ? $_POST['selected_image'] : '';

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/pages/"; // Dossier spécifique pour les pages
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "page_" . uniqid() . "." . $ext;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            // Utilisation de votre fonction d'optimisation
            if (function_exists('optimize_and_save_image')) {
                $optimized_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $target_dir . "page_" . uniqid());
                if ($optimized_path) {
                    $image = str_replace("../", "", $optimized_path);
                }
            } else {
                // Fallback
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_name)){
                    $image = "uploads/pages/" . $new_name;
                }
            }
        }
    }

    // Vérification doublon (Slug ou Titre)
    $stmt = mysqli_prepare($connect, "SELECT id FROM `pages` WHERE slug=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $queryvalid = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($queryvalid) > 0) {
        echo '
            <div class="alert alert-warning alert-dismissible m-3">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
                A page with this URL/Title already exists.
            </div>';
    } else {
        $author_id = $user['id'];
        
        // Insertion avec les nouveaux champs
        $stmt = mysqli_prepare($connect, "INSERT INTO pages (title, slug, content, meta_title, meta_description, image, active, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "sssssssi", $title, $slug, $content, $meta_title, $meta_description, $image, $active, $author_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $page_id = mysqli_insert_id($connect);
            log_activity($user['id'], "Create Page", "Created page: " . $title . " (ID: $page_id)");
            
            echo '<div class="alert alert-success m-3">Page created successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1;url=pages.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Database error: ' . mysqli_error($connect) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-alt"></i> Add New Page</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="pages.php">Pages</a></li>
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
                            <h3 class="card-title">Page Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input class="form-control form-control-lg" name="title" id="title" type="text" placeholder="Enter page title" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="form-control" id="summernote" name="content" rows="15" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card card-purple card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-search"></i> SEO Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>URL Slug (Friendly URL)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">/page?name=</span></div>
                                    <input class="form-control" name="slug" type="text" placeholder="Auto-generated if empty">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input class="form-control" name="meta_title" type="text" placeholder="Custom title for search engines">
                                <small class="text-muted">Leave empty to use page title.</small>
                            </div>
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea class="form-control" name="meta_description" rows="3" placeholder="Summary for search results"></textarea>
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
                                    <option value="Yes" selected>Published</option>
                                    <option value="No">Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Create Page
                            </button>
                            <a href="pages.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Featured Image</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <img src="../assets/img/no-image.png" id="preview_image_box" class="img-fluid rounded shadow-sm" style="max-height: 150px; border: 1px solid #ddd;">
                                <small id="default_image_msg" class="d-block mt-2 text-muted" style="font-style: italic;">
                                    Optional. Used for social sharing.
                                </small>
                            </div>

                            <div class="custom-file text-left mb-2">
                                <input type="file" name="image" class="custom-file-input" id="postImage">
                                <label class="custom-file-label" for="postImage">Upload New</label>
                            </div>
                            
                            <div class="text-center text-muted mb-2 small">- OR -</div>

                            <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">
                                <i class="fas fa-images"></i> Select from Library
                            </button>

                            <input type="hidden" name="selected_image" id="selected_image_input" value="">
                        </div>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="filesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select an Image</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="files-gallery-content">
        <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    // --- GESTION IMAGE ---
    
    // Charger les fichiers Ajax
    $('#filesModal').on('show.bs.modal', function (e) {
        if($('#files-gallery-content').html().indexOf('fa-spinner') !== -1) {
            $.get('ajax_load_files.php', function(data) {
                $('#files-gallery-content').html(data);
            });
        }
    });

    // Aperçu Upload (Input File)
    $("#postImage").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        $('#selected_image_input').val(''); // Reset lib
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { 
                $('#preview_image_box').attr('src', e.target.result);
                $('#default_image_msg').slideUp(); 
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Fonction appelée quand on clique sur une image dans le modal
function selectFile(dbValue, fullPath) {
    document.getElementById('selected_image_input').value = dbValue;
    document.getElementById('preview_image_box').src = fullPath;
    document.getElementById('postImage').value = ""; // Reset upload
    $('.custom-file-label').html('Upload New');
    
    $('#default_image_msg').slideUp(); 
    $('#filesModal').modal('hide');
}
</script>