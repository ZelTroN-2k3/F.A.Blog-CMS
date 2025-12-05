<?php
include "header.php";

// 1. Vérification ID
if (!isset($_GET['id']) && !isset($_POST['page_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=footer_pages.php">';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['page_id'];

// 2. Récupération Données
$stmt = mysqli_prepare($connect, "SELECT * FROM footer_pages WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$page_data) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">Page not found.</div></div></div>';
    include "footer.php"; exit;
}

// 3. Traitement Formulaire
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title   = $_POST['title'];
    $content = $_POST['content'];
    $active  = $_POST['active'];
    
    // Mise à jour
    $stmt_up = mysqli_prepare($connect, "UPDATE footer_pages SET title=?, content=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "sssi", $title, $content, $active, $id);
    
    if(mysqli_stmt_execute($stmt_up)) {
        echo '<div class="alert alert-success m-3">Page updated successfully! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="1; url=footer_pages.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error updating page.</div>';
    }
    mysqli_stmt_close($stmt_up);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Footer Page</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="footer_pages.php">Footer Pages</a></li>
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
            <input type="hidden" name="page_id" value="<?php echo $id; ?>">
            
            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($page_data['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Content</label>
                                <textarea id="summernote" name="content" class="form-control"><?php echo html_entity_decode($page_data['content']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($page_data['active'] == 'Yes') echo 'selected'; ?>>Active (Published)</option>
                                    <option value="No" <?php if($page_data['active'] == 'No') echo 'selected'; ?>>Inactive (Draft)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($page_data['slug']); ?>" readonly>
                                <small class="text-muted">Generated automatically.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Page
                            </button>
                            <a href="footer_pages.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>