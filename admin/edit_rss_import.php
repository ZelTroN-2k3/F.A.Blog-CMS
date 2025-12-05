<?php
include "header.php";

// Validation ID
if (!isset($_GET['id']) && !isset($_POST['feed_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=rss_imports.php">'; exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['feed_id'];

// Récupération
$stmt = mysqli_prepare($connect, "SELECT * FROM rss_imports WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$feed = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$feed) {
    echo '<div class="alert alert-danger m-3">Feed not found.</div>'; include "footer.php"; exit;
}

// Traitement Update
if (isset($_POST['update_feed'])) {
    validate_csrf_token();
    
    $feed_url    = $_POST['feed_url'];
    $user_id     = (int)$_POST['user_id'];
    $category_id = (int)$_POST['category_id'];
    $is_active   = $_POST['is_active'];
    
    $stmt_up = mysqli_prepare($connect, "UPDATE rss_imports SET feed_url=?, import_as_user_id=?, import_as_category_id=?, is_active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "siiii", $feed_url, $user_id, $category_id, $is_active, $id);
    
    if(mysqli_stmt_execute($stmt_up)) {
        echo '<div class="alert alert-success m-3">Feed updated! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=rss_imports.php">';
        exit;
    }
    mysqli_stmt_close($stmt_up);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit RSS Feed</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="rss_imports.php">RSS Imports</a></li>
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
            <input type="hidden" name="feed_id" value="<?php echo $id; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Feed Source</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>RSS Feed URL</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-rss"></i></span>
                                    </div>
                                    <input type="url" name="feed_url" class="form-control form-control-lg" value="<?php echo htmlspecialchars($feed['feed_url']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Import as User</label>
                                <select name="user_id" class="form-control select2">
                                    <?php
                                    $q_u = mysqli_query($connect, "SELECT id, username FROM users WHERE role IN ('Admin', 'Editor')");
                                    while($u = mysqli_fetch_assoc($q_u)){
                                        $sel = ($u['id'] == $feed['import_as_user_id']) ? 'selected' : '';
                                        echo '<option value="'.$u['id'].'" '.$sel.'>'.$u['username'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Import to Category</label>
                                <select name="category_id" class="form-control select2">
                                    <?php
                                    $q_c = mysqli_query($connect, "SELECT id, category FROM categories");
                                    while($c = mysqli_fetch_assoc($q_c)){
                                        $sel = ($c['id'] == $feed['import_as_category_id']) ? 'selected' : '';
                                        echo '<option value="'.$c['id'].'" '.$sel.'>'.$c['category'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?php if($feed['is_active']==1) echo 'selected'; ?>>Active</option>
                                    <option value="0" <?php if($feed['is_active']==0) echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="update_feed" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Feed
                            </button>
                            <a href="rss_imports.php" class="btn btn-default btn-block">Cancel</a>
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
    $('.select2').select2();
});
</script>