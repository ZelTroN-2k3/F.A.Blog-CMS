<?php
include "header.php";

// 1. Vérification ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=pages.php">';
    exit;
}

$id = (int)$_GET['id'];

// 2. Récupération des données
$stmt = mysqli_prepare($connect, "SELECT * FROM `pages` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">Page not found.</div></div></div>';
    include "footer.php"; exit;
}

// --- SÉCURITÉ : Un éditeur ne peut modifier que ses pages ---
if ($user['role'] == 'Editor' && $row['author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only edit your own pages.
          </div></div></div>';
    include "footer.php"; exit;
}

// 3. Traitement du formulaire
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title   = $_POST['title'];
    $content = $_POST['content'];
    $active  = $_POST['active'];
    
    // --- GESTION DU SLUG ---
    // Si modifié manuellement, on prend la valeur, sinon on régénère depuis le titre
    if (!empty($_POST['slug'])) {
        $slug = generateSeoURL($_POST['slug'], 0);
    } else {
        $slug = generateSeoURL($title, 0);
    }

    // --- SEO ---
    $meta_title       = !empty($_POST['meta_title']) ? $_POST['meta_title'] : $title;
    $meta_description = $_POST['meta_description'];

    // --- GESTION IMAGE (Upload OU Bibliothèque) ---
    // 1. Par défaut, on garde l'ancienne
    $image = $row['image'];

    // 2. Si sélection depuis bibliothèque
    if (!empty($_POST['selected_image'])) {
        $image = $_POST['selected_image'];
    }

    // 3. Si upload manuel (Prioritaire)
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/pages/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "page_" . uniqid() . "." . $ext;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (function_exists('optimize_and_save_image')) {
                $optimized_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $target_dir . "page_" . uniqid());
                if ($optimized_path) {
                    $image = str_replace("../", "", $optimized_path);
                }
            } else {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_name)){
                    $image = "uploads/pages/" . $new_name;
                }
            }
        }
    }
    
    // Mise à jour SQL
    $stmt = mysqli_prepare($connect, "UPDATE pages SET title=?, slug=?, content=?, meta_title=?, meta_description=?, image=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssi", $title, $slug, $content, $meta_title, $meta_description, $image, $active, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        log_activity($user['id'], "Update Page", "Updated page ID: " . $id . " - Title: " . $title);
        echo '<div class="alert alert-success m-3">Page updated successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=pages.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error updating page: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Page</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="pages.php">Pages</a></li>
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
                            <h3 class="card-title">Page Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Page Title</label>
                                <input class="form-control form-control-lg" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" type="text" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="form-control" id="summernote" name="content" rows="15" required><?php echo html_entity_decode($row['content']); ?></textarea>
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
                                    <input class="form-control" name="slug" type="text" value="<?php echo htmlspecialchars($row['slug']); ?>">
                                </div>
                                <small class="text-muted">Modify to remove random numbers if needed.</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input class="form-control" name="meta_title" type="text" value="<?php echo htmlspecialchars($row['meta_title'] ?? ''); ?>" placeholder="Custom title for search engines">
                                <small class="text-muted">Leave empty to use page title.</small>
                            </div>
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea class="form-control" name="meta_description" rows="3" placeholder="Summary for search results"><?php echo htmlspecialchars($row['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Publishing</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control" required>
                                    <option value="Yes" <?php if ($row['active'] == 'Yes') echo 'selected'; ?>>Published</option>
                                    <option value="No" <?php if ($row['active'] == 'No') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Page
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
                                <?php 
                                $current_img = (!empty($row['image'])) ? '../' . $row['image'] : '../assets/img/no-image.png';
                                $display_msg = (!empty($row['image'])) ? 'display:none;' : '';
                                ?>
                                <img src="<?php echo htmlspecialchars($current_img); ?>" id="preview_image_box" class="img-fluid rounded shadow-sm" style="max-height: 150px; border: 1px solid #ddd;">
                                
                                <small id="default_image_msg" class="d-block mt-2 text-muted" style="font-style: italic; <?php echo $display_msg; ?>">
                                    Optional. Used for social sharing.
                                </small>
                            </div>

                            <div class="custom-file text-left mb-2">
                                <input type="file" name="image" class="custom-file-input" id="postImage">
                                <label class="custom-file-label" for="postImage">Change File</label>
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
    $('.custom-file-label').html('Change File');
    
    $('#default_image_msg').slideUp(); 
    $('#filesModal').modal('hide');
}
</script>