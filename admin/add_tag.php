<?php
include "header.php";

if (isset($_POST['add'])) {
    validate_csrf_token();
    
    $name = $_POST['name'];
    // Génération du slug automatique si vide, ou nettoyage
    //$slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug']) : generateSeoURL($name);
    $slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug'], 0) : generateSeoURL($name, 0);

    // Vérifier si le tag existe déjà
    $check = mysqli_prepare($connect, "SELECT id FROM tags WHERE slug = ?");
    mysqli_stmt_bind_param($check, "s", $slug);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    
    if (mysqli_stmt_num_rows($check) > 0) {
        echo '<div class="alert alert-warning m-3">This tag/slug already exists.</div>';
    } else {
        $stmt = mysqli_prepare($connect, "INSERT INTO tags (name, slug) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $slug);
        
        if (mysqli_stmt_execute($stmt)) {
            echo '<meta http-equiv="refresh" content="0; url=tags.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error adding tag.</div>';
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Add Tag</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="tags.php">Tags</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">New Tag Details</h3></div>
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Tag Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Technology">
                    </div>
                    <div class="form-group">
                        <label>Slug (Optional)</label>
                        <input type="text" name="slug" class="form-control" placeholder="Leave empty to generate automatically">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="add" class="btn btn-success"><i class="fas fa-save"></i> Save Tag</button>
                    <a href="tags.php" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>