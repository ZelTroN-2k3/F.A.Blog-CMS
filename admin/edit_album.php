<?php
include "header.php";

// Validation ID
if (!isset($_GET['id']) && !isset($_POST['album_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=albums.php">'; exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['album_id'];

// Récupération
$stmt = mysqli_prepare($connect, "SELECT * FROM albums WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$album = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$album) {
    echo '<div class="alert alert-danger m-3">Album not found.</div>';
    include "footer.php"; exit;
}

// --- SÉCURITÉ AJOUTÉE ---
// Si pas Admin ET pas l'auteur de l'album -> Dehors
if ($user['role'] != 'Admin' && $album['author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only edit your own albums.
          </div></div>';
    include "footer.php"; 
    exit;
}
// --- FIN SÉCURITÉ ---

// Traitement Update
if (isset($_POST['edit_album'])) {
    validate_csrf_token();
    $title = $_POST['title'];

    if (empty($title)) {
        echo '<div class="alert alert-danger m-3">Title cannot be empty.</div>';
    } else {
        $stmt_up = mysqli_prepare($connect, "UPDATE albums SET title=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_up, "si", $title, $id);
        
        if(mysqli_stmt_execute($stmt_up)) {
            echo '<div class="alert alert-success m-3">Album updated successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=albums.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error updating album.</div>';
        }
        mysqli_stmt_close($stmt_up);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Album</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="albums.php">Albums</a></li>
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
            <input type="hidden" name="album_id" value="<?php echo $id; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Album Title</label>
                                <input class="form-control form-control-lg" name="title" type="text" value="<?php echo htmlspecialchars($album['title']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Action</h3>
                        </div>
                        <div class="card-body">
                            <button type="submit" name="edit_album" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Album
                            </button>
                            <a href="albums.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>