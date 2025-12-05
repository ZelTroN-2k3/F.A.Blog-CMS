<?php
include "header.php";

$id = (int)$_GET['id'];
if (empty($id)) { echo '<meta http-equiv="refresh" content="0; url=tags.php">'; exit; }

// Récupérer le tag actuel
$stmt = mysqli_prepare($connect, "SELECT * FROM tags WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) { echo '<meta http-equiv="refresh" content="0; url=tags.php">'; exit; }

if (isset($_POST['update'])) {
    validate_csrf_token();
    
    $name = $_POST['name'];
    //$slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug']) : generateSeoURL($name);
    $slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug'], 0) : generateSeoURL($name, 0);
    // Vérifier doublon (sauf si c'est le même ID)
    $check = mysqli_prepare($connect, "SELECT id FROM tags WHERE slug = ? AND id != ?");
    mysqli_stmt_bind_param($check, "si", $slug, $id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    
    if (mysqli_stmt_num_rows($check) > 0) {
        echo '<div class="alert alert-warning m-3">This slug is already used by another tag.</div>';
    } else {
        $update = mysqli_prepare($connect, "UPDATE tags SET name = ?, slug = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "ssi", $name, $slug, $id);
        
        if (mysqli_stmt_execute($update)) {
            echo '<div class="alert alert-success m-3">Tag updated successfully.</div>';
            echo '<meta http-equiv="refresh" content="1; url=tags.php">';
        } else {
            echo '<div class="alert alert-danger m-3">Update failed.</div>';
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($check);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Edit Tag</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="tags.php">Tags</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Edit Tag: <?php echo htmlspecialchars($row['name']); ?></h3></div>
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Tag Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($row['slug']); ?>">
                        <small class="text-muted">Changing the slug will change the URL of the tag page.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Update Tag</button>
                    <a href="tags.php" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>