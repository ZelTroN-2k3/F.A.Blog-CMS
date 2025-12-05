<?php
include "header.php";

// --- SUPPRESSION ---
if (isset($_GET['delete-id'])) {
    // AJOUT SÉCURITÉ : Vérification CSRF
    validate_csrf_token_get();
    
    $id = (int) $_GET["delete-id"];
    
    // 1. Récupérer l'image pour la supprimer physiquement du dossier
    $stmt_img = mysqli_prepare($connect, "SELECT image FROM `gallery` WHERE id=?");
    mysqli_stmt_bind_param($stmt_img, "i", $id);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    $row_img = mysqli_fetch_assoc($res_img);
    mysqli_stmt_close($stmt_img);
    
    if ($row_img && !empty($row_img['image']) && file_exists("../" . $row_img['image'])) {
        @unlink("../" . $row_img['image']);
    }
    
    // 2. Supprimer de la base de données
    $stmt = mysqli_prepare($connect, "DELETE FROM `gallery` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Rediriger
    echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-images"></i> Gallery</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Gallery</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
	  
<?php
if (isset($_GET['edit-id'])) {
    $id  = (int) $_GET["edit-id"];

    // Use prepared statement for SELECT
    $stmt = mysqli_prepare($connect, "SELECT * FROM `gallery` WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($sql);
    mysqli_stmt_close($stmt);

    if (empty($id) || !$row) {
        echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
        exit;
    }
    
    if (isset($_POST['edit'])) {
        // --- NOUVEL AJOUT : Validation CSRF ---
        validate_csrf_token();
        // --- FIN AJOUT ---
        
        $title       = $_POST['title'];
        $image       = $row['image'];
        $active      = $_POST['active'];
        $album_id    = $_POST['album_id'];
        $description = $_POST['description'];
        
        if (@$_FILES['avafile']['name'] != '') {

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            $file_extension = strtolower(pathinfo($_FILES["avafile"]["name"], PATHINFO_EXTENSION));
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES["avafile"]["tmp_name"]);
            finfo_close($finfo);

            $uploadOk = 1;

            if (!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)) {
                echo '<div class="alert alert-danger">Invalid file type. Only JPG, PNG, GIF and WEBP are allowed.</div>';
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["avafile"]["size"] > 10000000) { // 10 MB
                echo '<div class="alert alert-warning">Sorry, your file is too large (Limit: 10MB).</div>';
                $uploadOk = 0;
            }
            
            if ($uploadOk == 1) {
                // --- MODIFICATION ---
                $string     = "0123456789wsderfgtyhjuk";
                $new_string = str_shuffle($string);
                $destination_path = "../uploads/gallery/image_$new_string";

                // Utiliser une largeur max plus grande pour la galerie, ex: 1920px
                $optimized_path = optimize_and_save_image($_FILES["avafile"]["tmp_name"], $destination_path, 1920);
                
                if ($optimized_path) {
                    $image = str_replace('../', '', $optimized_path);
                } else {
                     $uploadOk = 0; 
                }
                // --- FIN MODIFICATION ---
            }
        }
        
        // Use prepared statement for UPDATE
        $stmt = mysqli_prepare($connect, "UPDATE gallery SET album_id=?, title=?, image=?, active=?, description=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "issssi", $album_id, $title, $image, $active, $description, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Redirection uniquement si l'upload n'a pas échoué, ou si aucune nouvelle image n'était présente
        if (!isset($uploadOk) || $uploadOk == 1) {
             echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
             exit;
        }
    }
?>
    <div class="card card-primary card-outline mb-3">
		<div class="card-header">
            <h3 class="card-title">Edit Image</h3>
        </div>         
        <form action="" method="post" enctype="multipart/form-data">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
					<label>Title</label>
					<input class="form-control" name="title" type="text" value="<?php
echo htmlspecialchars($row['title']);
?>" required>
				</div>
				<div class="form-group">
					<label>Image</label><br />
					<img src="../<?php
echo htmlspecialchars($row['image']);
?>" width="50px" height="50px" class="img-circle elevation-2 mb-2" /><br />
					<input type="file" name="avafile" class="form-control" />
				</div>
				<div class="form-group">
					<label>Active</label>
					<select name="active" class="form-control">
						<option value="Yes" <?php
if ($row['active'] == "Yes") {
    echo 'selected';
}
?>>Yes</option>
						<option value="No" <?php
if ($row['active'] == "No") {
    echo 'selected';
}
?>>No</option>
                    </select>
				</div>
				<div class="form-group">
					<label>Album</label>
					<select name="album_id" class="form-control" required>
<?php
$crun = mysqli_query($connect, "SELECT * FROM `albums`");
while ($rw = mysqli_fetch_assoc($crun)) {
	$selected = "";
	if ($row['album_id'] == $rw['id']) {
		$selected = "selected";
	}
    echo '<option value="' . $rw['id'] . '" ' . $selected . '>' . htmlspecialchars($rw['title']) . '</option>';
}
?>
					</select>
				</div>
				<div class="form-group">
					<label>Description</label>
					<textarea class="form-control" id="summernote" name="description"><?php
echo html_entity_decode($row['description']);
?></textarea>
				</div>
            </div>
            <div class="card-footer">
			    <input type="submit" class="btn btn-primary" name="edit" value="Save" />
                <a href="gallery.php" class="btn btn-secondary">Cancel</a>
            </div>
		</form>
	</div>
<?php
}
?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <a href="add_image.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add Image</a>
                </h3>
            </div>         
            <div class="card-body">
                <table class="table table-bordered table-hover" id="dt-basic" style="width:100%">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Active</th>
                        <th>Album</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
<?php
$sql = mysqli_query($connect, "SELECT * FROM gallery ORDER BY id DESC");
while ($row = mysqli_fetch_assoc($sql)) {
	$album_id = $row['album_id'];
    
    // Use prepared statement to get album title
    $stmt = mysqli_prepare($connect, "SELECT title FROM `albums` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $album_id);
    mysqli_stmt_execute($stmt);
    $runq2 = mysqli_stmt_get_result($stmt);
    $cat = mysqli_fetch_assoc($runq2);
    mysqli_stmt_close($stmt);
    
    $album_title = $cat ? $cat['title'] : 'N/A';

    echo '
            <tr>
                <td><center><img src="../' . htmlspecialchars($row['image']) . '" width="100px" height="75px" class="img-thumbnail elevation-1" /></center></td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td><span class="badge bg-' . ($row['active'] == "Yes" ? 'success' : 'danger') . '">' . htmlspecialchars($row['active']) . '</span></td>
                <td>' . htmlspecialchars($album_title) . '</td>
                <td>
                    <a href="?edit-id=' . $row['id'] . '" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                    <a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this image?\');"><i class="fa fa-trash"></i> Delete</a>
                </td>
            </tr>
';
}
?>
                    </tbody>
                </table>
            </div>
        </div>

    </div></section>
<script>
$(document).ready(function() {
    // Le script de datatables est dans footer.php, nous surchargeons ici l'ordre
	$('#dt-basic').DataTable({
        "responsive": true,
        "lengthChange": false, 
        "autoWidth": false,
		"order": [[ 1, "asc" ]] // Ordonner par titre d'image (colonne 1)
	});
});
</script>
<?php
include "footer.php";
?>