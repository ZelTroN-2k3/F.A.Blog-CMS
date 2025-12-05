<?php
include "header.php";

$id = (int)$_GET['id'] ?? 0;
if ($id === 0) { echo '<meta http-equiv="refresh" content="0;url=popups.php">'; exit; }

$message = '';

if (isset($_POST['update_popup'])) {
    validate_csrf_token();

    $title = $_POST['title'];
    $active = $_POST['active'];
    $display_pages = $_POST['display_pages'];
    $show_once = $_POST['show_once_per_session'];
    $delay = (int)$_POST['delay_seconds'];
    $popup_type = $_POST['popup_type'];
    
    $main_title = $_POST['main_title'];
    $subtitle = $_POST['subtitle'];
    $footer_text = $_POST['footer_text'];
    $newsletter_active = $_POST['newsletter_active'];
    $content = ($popup_type == 'Standard') ? $_POST['content'] : '';

    // Récupérer l'ancienne image
    $stmt_img = mysqli_prepare($connect, "SELECT background_image FROM popups WHERE id = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    $row_img = mysqli_fetch_assoc($res_img);
    $background_image = $row_img['background_image']; 
    mysqli_stmt_close($stmt_img);

    // Nouvelle image ?
    if ($popup_type == 'Design' && !empty($_FILES['background_image']['name'])) {
        $target_dir = "../uploads/popups/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = "bg_" . time() . "_" . basename($_FILES["background_image"]["name"]);
        if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $target_dir . $filename)) {
            $background_image = "uploads/popups/" . $filename;
        }
    }
    
    // Si vide (cas de vieux popups), on peut forcer la défaut ici si souhaité, 
    // mais mieux vaut garder ce qu'on a en base ou NULL.

    $stmt = mysqli_prepare($connect, "
        UPDATE popups SET 
        title=?, content=?, active=?, display_pages=?, show_once_per_session=?, delay_seconds=?,
        popup_type=?, background_image=?, main_title=?, subtitle=?, newsletter_active=?, footer_text=?
        WHERE id=?
    ");
    
    mysqli_stmt_bind_param($stmt, "sssssissssssi", 
        $title, $content, $active, $display_pages, $show_once, $delay,
        $popup_type, $background_image, $main_title, $subtitle, $newsletter_active, $footer_text, $id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo '<meta http-equiv="refresh" content="0;url=popups.php?success=updated">'; exit;
    } else {
        $message = '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}

// Chargement
$stmt = mysqli_prepare($connect, "SELECT * FROM popups WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$popup = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$popup) { echo '<meta http-equiv="refresh" content="0;url=popups.php">'; exit; }

if ($user['role'] == 'Editor' && $popup['author_id'] != $user['id']) {
    echo '<div class="alert alert-danger m-3">Access Denied.</div>'; include "footer.php"; exit;
}

$safe_content = json_encode($popup['content']);

// Déterminer l'image à afficher (BDD ou Défaut)
$display_img = !empty($popup['background_image']) ? $popup['background_image'] : 'assets/img/popup_default.jpg';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Edit Popup #<?php echo $id; ?></h1></div>
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
                        <div class="card-header"><h3 class="card-title">Content</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Internal Name</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($popup['title']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Type</label>
                                <select name="popup_type" class="form-control" id="typeSelector">
                                    <option value="Standard" <?php if($popup['popup_type']=='Standard') echo 'selected'; ?>>Standard</option>
                                    <option value="Design" <?php if($popup['popup_type']=='Design') echo 'selected'; ?>>Modern Design</option>
                                </select>
                            </div>

                            <div id="designFields">
                                <div class="form-group">
                                    <label>Background Image</label><br>
                                    
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($display_img); ?>" class="img-thumbnail img-preview" style="height: 100px; object-fit: cover;">
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="background_image" id="bgFile">
                                        <label class="custom-file-label">Change image...</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Main Title</label>
                                    <input type="text" name="main_title" class="form-control" value="<?php echo htmlspecialchars($popup['main_title']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="subtitle" class="form-control" rows="4"><?php echo htmlspecialchars($popup['subtitle']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Newsletter Form</label>
                                    <select name="newsletter_active" class="form-control">
                                        <option value="Yes" <?php if($popup['newsletter_active']=='Yes') echo 'selected'; ?>>Yes</option>
                                        <option value="No" <?php if($popup['newsletter_active']=='No') echo 'selected'; ?>>No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Footer Text</label>
                                    <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($popup['footer_text']); ?>">
                                </div>
                            </div>

                            <div id="standardFields">
                                <div class="form-group">
                                    <textarea id="summernote" name="content"><?php echo htmlspecialchars($popup['content']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-warning card-outline">
                        <div class="card-header"><h3 class="card-title">Settings</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($popup['active']=='Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($popup['active']=='No') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Display On</label>
                                <select name="display_pages" class="form-control">
                                    <option value="home" <?php if($popup['display_pages']=='home') echo 'selected'; ?>>Home Only</option>
                                    <option value="all" <?php if($popup['display_pages']=='all') echo 'selected'; ?>>All Pages</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Frequency</label>
                                <select name="show_once_per_session" class="form-control">
                                    <option value="Yes" <?php if($popup['show_once_per_session']=='Yes') echo 'selected'; ?>>Once/Session</option>
                                    <option value="No" <?php if($popup['show_once_per_session']=='No') echo 'selected'; ?>>Always</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Delay (sec)</label>
                                <input type="number" name="delay_seconds" class="form-control" value="<?php echo (int)$popup['delay_seconds']; ?>">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="update_popup" class="btn btn-primary btn-block">Update</button>
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
    $('#summernote').summernote({ height: 300 });
    $('#summernote').summernote('code', <?php echo $safe_content; ?>);
    
    function toggleFields() {
        if($('#typeSelector').val() === 'Standard') { $('#standardFields').show(); $('#designFields').hide(); }
        else { $('#standardFields').hide(); $('#designFields').show(); }
    }
    $('#typeSelector').change(toggleFields);
    toggleFields();
    
    // Preview image au changement
    $("#bgFile").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        
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