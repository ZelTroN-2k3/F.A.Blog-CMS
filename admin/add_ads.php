<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $name     = $_POST['name'];
    $ad_size  = $_POST['ad_size'];
    $link_url = $_POST['link_url'];
    $active   = $_POST['active'];
    $image_path = '';

    // Upload Image
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        // Créer le dossier s'il n'existe pas
        if (!is_dir("../uploads/ads")) { mkdir("../uploads/ads", 0777, true); }
        
        $target_dir = "../uploads/ads/";
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "ad_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/ads/" . $new_name;
            }
        }
    }

    if ($image_path != '') {
        $stmt = mysqli_prepare($connect, "INSERT INTO ads (name, ad_size, image_url, link_url, active) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $name, $ad_size, $image_path, $link_url, $active);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<meta http-equiv="refresh" content="0; url=ads.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger">Please select a valid image.</div>';
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Add New Ad</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="card card-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Campaign Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Ex: Winter Promotion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Destination Link (URL)</label>
                                <input type="text" name="link_url" class="form-control" required placeholder="Ex: https://example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Image Format</label>
                                <select name="ad_size" class="form-control" id="ad_size_select" required>
                                    <option value="728x90" data-img="../assets/images/ad_preview/728x90.png">728x90 (Leaderboard - Header)</option>
                                    <option value="970x90" data-img="../assets/images/ad_preview/970x90.png">970x90 (Large Leaderboard)</option>
                                    <option value="468x60" data-img="../assets/images/ad_preview/468x60.png">468x60 (Banner - Content)</option>
                                    <option value="234x60" data-img="../assets/images/ad_preview/234x60.png">234x60 (Half Banner)</option>
                                    <option value="300x250" data-img="../assets/images/ad_preview/300x250.png">300x250 (Rectangle - Sidebar)</option>
                                    <option value="300x600" data-img="../assets/images/ad_preview/300x600.png">300x600 (Skyscraper - Sidebar)</option>
                                    <option value="150x150" data-img="../assets/images/ad_preview/150x150.png">150x150 (Small Square)</option>
                                </select>
                            </div>
                            <div class="ad-preview-container border p-2 mb-3 bg-light rounded text-center">
                                <img id="ad_preview_img" src="../assets/images/ad_preview/300x250.png" class="img-fluid" style="max-width: 100%; height: auto; max-height: 150px;">
                                <small class="text-muted d-block mt-1">Format Preview</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes">Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Image (JPG, PNG, GIF)</label>
                        <input type="file" name="image" class="form-control-file" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Save</button>
                    <a href="ads.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectElement = document.getElementById('ad_size_select');
    var previewImage = document.getElementById('ad_preview_img');

    // Fonction pour mettre à jour l'aperçu
    function updateAdPreview() {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var imageUrl = selectedOption.getAttribute('data-img');
        if (imageUrl) {
            previewImage.src = imageUrl;
        }
    }

    // Écouter les changements sur le select
    selectElement.addEventListener('change', updateAdPreview);

    // Initialiser l'aperçu au chargement de la page
    updateAdPreview();
});
</script>

<?php include "footer.php"; ?>