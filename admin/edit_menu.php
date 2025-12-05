<?php
include "header.php";

// Validation ID
if (!isset($_GET['id']) && !isset($_POST['menu_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=menu_editor.php">'; exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['menu_id'];

// Récupération
$stmt = mysqli_prepare($connect, "SELECT * FROM menu WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$menu = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$menu) {
    echo '<div class="alert alert-danger m-3">Menu item not found.</div>';
    include "footer.php"; exit;
}

// Traitement Update
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $page    = $_POST['page'];
    $path    = $_POST['path'];
    $fa_icon = $_POST['fa_icon'];
    $active  = $_POST['active'];
    
    $stmt_up = mysqli_prepare($connect, "UPDATE menu SET page=?, path=?, fa_icon=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "ssssi", $page, $path, $fa_icon, $active, $id);
    
    if(mysqli_stmt_execute($stmt_up)) {
        echo '<div class="alert alert-success m-3">Menu item updated! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=menu_editor.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error updating item.</div>';
    }
    mysqli_stmt_close($stmt_up);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Menu Item</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="menu_editor.php">Menu</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Link Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Link Label</label>
                                <input name="page" class="form-control form-control-lg" type="text" value="<?php echo htmlspecialchars($menu['page']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Destination URL</label>
                                <div class="input-group mb-2">
                                    <input name="path" id="menuPath" class="form-control" type="text" value="<?php echo htmlspecialchars($menu['path']); ?>" required>
                                </div>
                                
                                <label class="text-muted small mt-2 mb-1">Or select an internal page:</label>
                                <select id="pageSelector" class="form-control form-control-sm custom-select">
                                    <option value="">-- Replace current path with... --</option>
                                    <option value="index.php">Home (index.php)</option>
                                    <option value="blog.php">Blog (blog.php)</option>
                                    <option value="contact.php">Contact (contact.php)</option>
                                    <option value="legal-notice.php">legal-notice (legal-notice.php)</option>
                                    <option value="privacy-policy.php">privacy-policy (privacy-policy.php)</option>                                    
                                    <?php
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
                                    <option value="Yes" <?php if($menu['active']=='Yes') echo 'selected'; ?>>Published</option>
                                    <option value="No" <?php if($menu['active']=='No') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Icon Class</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="icon-preview"><i class="<?php echo htmlspecialchars($menu['fa_icon']); ?>"></i></span>
                                    </div>
                                    <input name="fa_icon" id="iconInput" class="form-control" type="text" value="<?php echo htmlspecialchars($menu['fa_icon']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Item
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
    $('#iconInput').on('input', function() {
        var iconClass = $(this).val();
        $('#icon-preview i').attr('class', iconClass);
    });

    $('#pageSelector').on('change', function() {
        var selectedPath = $(this).val();
        if(selectedPath) {
            $('#menuPath').val(selectedPath);
        }
    });
});
</script>