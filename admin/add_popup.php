<?php
include "header.php";

$message = '';

if (isset($_POST['save_popup'])) {
    validate_csrf_token();

    $title = $_POST['title'];
    $active = $_POST['active'];
    $display_pages = $_POST['display_pages'];
    $show_once = $_POST['show_once_per_session'];
    $delay = (int)$_POST['delay_seconds'];
    $author_id = $user['id'];
    
    $popup_type = $_POST['popup_type'];
    $main_title = $_POST['main_title'];
    $subtitle = $_POST['subtitle'];
    $footer_text = $_POST['footer_text'];
    $newsletter_active = $_POST['newsletter_active'];
    
    $content = ($popup_type == 'Standard') ? $_POST['content'] : '';

    // --- MODIFICATION : Image par défaut ---
    // Si aucune image n'est envoyée, on utilise l'image par défaut
    $background_image = 'assets/img/popup_default.jpg'; 
    
    if ($popup_type == 'Design' && !empty($_FILES['background_image']['name'])) {
        $target_dir = "../uploads/popups/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = "bg_" . time() . "_" . basename($_FILES["background_image"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $target_file)) {
            $background_image = "uploads/popups/" . $filename;
        }
    }
    // ---------------------------------------

    if (empty($title)) {
        $message = '<div class="alert alert-danger m-3">Internal Title is required.</div>';
    } else {
        $stmt = mysqli_prepare($connect, "
            INSERT INTO popups (title, content, active, display_pages, show_once_per_session, delay_seconds, author_id, popup_type, background_image, main_title, subtitle, newsletter_active, footer_text) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        mysqli_stmt_bind_param($stmt, "sssssiissssss", 
            $title, $content, $active, $display_pages, $show_once, $delay, $author_id,
            $popup_type, $background_image, $main_title, $subtitle, $newsletter_active, $footer_text
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo '<meta http-equiv="refresh" content="0;url=popups.php?success=added">';
            exit;
        } else {
            $message = '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Add Popup</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="popups.php">Popups</a></li><li class="breadcrumb-item active">Add</li></ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $message; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title">Content & Design</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Internal Name</label>
                                <input type="text" name="title" class="form-control" placeholder="Ex: Promo Hiver" required>
                            </div>

                            <div class="form-group">
                                <label>Popup Type</label>
                                <select name="popup_type" class="form-control" id="typeSelector">
                                    <option value="Standard">Standard (Classic Editor)</option>
                                    <option value="Design" selected>Modern Design (Image Background)</option>
                                </select>
                            </div>

                            <div id="designFields">
                                <div class="form-group">
                                    <label>Background Image</label>
                                    
                                    <div class="mb-2">
                                        <img src="../assets/img/popup_default.jpg" class="img-thumbnail img-preview" style="height: 100px; object-fit: cover;">
                                        <small class="d-block text-muted">Default image used if none selected.</small>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="background_image" id="bgFile">
                                        <label class="custom-file-label" for="bgFile">Choose custom image...</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Main Title</label>
                                    <input type="text" name="main_title" class="form-control" placeholder="Titre principal...">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="subtitle" class="form-control" rows="4" placeholder="Votre texte ici..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Newsletter Form?</label>
                                    <select name="newsletter_active" class="form-control">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Footer Text</label>
                                    <input type="text" name="footer_text" class="form-control" placeholder="Petit texte du bas...">
                                </div>
                            </div>

                            <div id="standardFields" style="display:none;">
                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea id="summernote" name="content"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-success card-outline">
                        <div class="card-header"><h3 class="card-title">Settings</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes">Active</option>
                                    <option value="No">Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Display On</label>
                                <select name="display_pages" class="form-control">
                                    <option value="home">Home Page</option>
                                    <option value="all">All Pages</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Frequency</label>
                                <select name="show_once_per_session" class="form-control">
                                    <option value="Yes">Once per session</option>
                                    <option value="No">Always</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Delay (sec)</label>
                                <input type="number" name="delay_seconds" class="form-control" value="2">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="save_popup" class="btn btn-primary btn-block">Save Popup</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>
<script>
$(function() {
    $('#summernote').summernote({height: 200});
    
    function toggleFields() {
        if($('#typeSelector').val() === 'Standard') { $('#standardFields').show(); $('#designFields').hide(); }
        else { $('#standardFields').hide(); $('#designFields').show(); }
    }
    $('#typeSelector').change(toggleFields);
    toggleFields();
    
    // Script de prévisualisation immédiate de l'image
    $("#bgFile").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        
        // Prévisualisation
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.img-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>