<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $page    = $_POST['page']; // Le titre du lien
    $path    = $_POST['path']; // Le lien
    $fa_icon = $_POST['fa_icon'];
    $active  = $_POST['active'];
    
    $stmt = mysqli_prepare($connect, "INSERT INTO menu (page, path, fa_icon, active) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $page, $path, $fa_icon, $active);
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<div class="alert alert-success m-3">Menu item added! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=menu_editor.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error adding menu item.</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Menu Item</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="menu_editor.php">Menu</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Link Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Link Label (Name)</label>
                                <input name="page" class="form-control form-control-lg" type="text" placeholder="e.g. About Us" required>
                            </div>

                            <div class="form-group">
                                <label>Destination URL (Path)</label>
                                <div class="input-group mb-2">
                                    <input name="path" id="menuPath" class="form-control" type="text" placeholder="e.g. page?name=about or http://google.com" required>
                                </div>
                                
                                <label class="text-muted small mt-2 mb-1">Or select an internal page:</label>
                                <select id="pageSelector" class="form-control form-control-sm custom-select">
                                    <option value="">-- Select a page to auto-fill path --</option>
                                    <option value="index.php">Home (index.php)</option>
                                    <option value="blog.php">Blog (blog.php)</option>
                                    <option value="contact.php">Contact (contact.php)</option>
                                    <option value="legal-notice.php">legal-notice (legal-notice.php)</option>
                                    <option value="privacy-policy.php">privacy-policy (privacy-policy.php)</option>
                                    <?php
                                    // Récupérer les pages statiques dynamiquement
                                    $q_pages = mysqli_query($connect, "SELECT title, slug FROM pages WHERE active='Yes'");
                                    while($pg = mysqli_fetch_assoc($q_pages)) {
                                        echo '<option value="page?name=' . $pg['slug'] . '">Page: ' . htmlspecialchars($pg['title']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Icon & Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="active">
                                    <option value="Yes" selected>Published</option>
                                    <option value="No">Draft</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Icon Class (FontAwesome)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="icon-preview"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input name="fa_icon" id="iconInput" class="form-control" type="text" value="fas fa-link">
                                </div>
                                <small class="form-text text-muted">e.g. <code>fa fa-home</code>, <code>fas fa-user</code></small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Item
                            </button>
                            <a href="menu_editor.php" class="btn btn-default btn-block">Cancel</a>
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
    // Prévisualisation de l'icône en temps réel
    $('#iconInput').on('input', function() {
        var iconClass = $(this).val();
        $('#icon-preview i').attr('class', iconClass);
    });

    // Remplissage automatique du Path via le sélecteur
    $('#pageSelector').on('change', function() {
        var selectedPath = $(this).val();
        if(selectedPath) {
            $('#menuPath').val(selectedPath);
        }
    });
});
</script>