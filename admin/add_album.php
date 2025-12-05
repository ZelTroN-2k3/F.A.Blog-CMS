<?php
include "header.php";

if (isset($_POST['add_album'])) {
    validate_csrf_token();

    $title = $_POST['title'];

if (empty($title)) {
        echo '<div class="alert alert-danger m-3">Title is required.</div>';
    } else {
        // MODIFICATION : Ajout de author_id
        $author_id = $user['id'];
        
        $stmt = mysqli_prepare($connect, "INSERT INTO albums (title, author_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $title, $author_id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo '<div class="alert alert-success m-3">Album created successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=albums.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error creating album.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-folder-plus"></i> Add Album</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="albums.php">Albums</a></li>
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
                            <h3 class="card-title">Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Album Title</label>
                                <input class="form-control form-control-lg" name="title" type="text" placeholder="e.g. Holidays 2023" required>
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
                            <button type="submit" name="add_album" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Create Album
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