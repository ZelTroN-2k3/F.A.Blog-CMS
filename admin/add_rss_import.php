<?php
include "header.php";

if (isset($_POST['add_feed'])) {
    validate_csrf_token();
    
    $feed_url    = $_POST['feed_url'];
    $user_id     = (int)$_POST['user_id'];
    $category_id = (int)$_POST['category_id'];
    $is_active   = $_POST['is_active'];

    if (empty($feed_url)) {
        echo '<div class="alert alert-danger m-3">URL is required.</div>';
    } elseif (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
        echo '<div class="alert alert-danger m-3">Invalid URL format.</div>';
    } else {
        $stmt = mysqli_prepare($connect, "INSERT INTO rss_imports (feed_url, import_as_user_id, import_as_category_id, is_active) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siii", $feed_url, $user_id, $category_id, $is_active);
        
        if(mysqli_stmt_execute($stmt)) {
            echo '<div class="alert alert-success m-3">Feed added successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=rss_imports.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error adding feed.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add RSS Feed</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="rss_imports.php">RSS Imports</a></li>
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
                            <h3 class="card-title">Feed Source</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>RSS Feed URL</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-rss"></i></span>
                                    </div>
                                    <input type="url" name="feed_url" class="form-control form-control-lg" placeholder="https://example.com/feed.xml" required>
                                </div>
                                <small class="text-muted">The XML/RSS feed address.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Import Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Import as User</label>
                                <select name="user_id" class="form-control select2">
                                    <?php
                                    $q_u = mysqli_query($connect, "SELECT id, username FROM users WHERE role IN ('Admin', 'Editor')");
                                    while($u = mysqli_fetch_assoc($q_u)){
                                        echo '<option value="'.$u['id'].'" '.($u['id']==$user['id']?'selected':'').'>'.$u['username'].'</option>';
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
                                        echo '<option value="'.$c['id'].'">'.$c['category'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" selected>Active (Auto-import)</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_feed" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Feed
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