<?php
include "header.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=categorys.php">';
    exit;
}

$id = (int)$_GET['id'];

// Récupération des données de la catégorie
$stmt = mysqli_prepare($connect, "SELECT * FROM `categories` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="alert alert-danger m-3">Category not found.</div>';
    include "footer.php";
    exit;
}

// --- SÉCURITÉ AJOUTÉE ---
// Si l'utilisateur est Éditeur ET que l'auteur de la catégorie n'est pas lui -> DEHORS
if ($user['role'] == 'Editor' && $row['author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only edit your own categories.
          </div></div></div>';
    include "footer.php"; 
    exit;
}
// --- FIN SÉCURITÉ ---

// Traitement du formulaire
if (isset($_POST['submit'])) {
    validate_csrf_token();

    $category_name = $_POST['category'];
    $description = $_POST['description'];
    $slug = generateSeoURL($category_name); // Regénérer le slug
    
    // Gestion de l'image
    $image_path = $row['image']; // Par défaut, on garde l'ancienne
    $old_image_path_to_delete = null;

    // 1. Logique de suppression (case cochée)
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == 1) {
        if (!empty($row['image']) && file_exists("../" . $row['image'])) {
            $old_image_path_to_delete = "../" . $row['image'];
        }
        $image_path = ""; // On vide le chemin en BDD
    }

    // 2. Logique d'upload (nouveau fichier)
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/categories/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Si on upload une nouvelle, on marque l'ancienne pour suppression
                if (!empty($row['image']) && file_exists("../" . $row['image'])) {
                    $old_image_path_to_delete = "../" . $row['image'];
                }
                $image_path = "uploads/categories/" . $image_name;
            } else {
                $error_msg = "Error uploading file.";
            }
        } else {
             $error_msg = "File is not an image.";
        }
    }

    if (!isset($error_msg)) {
        $stmt_update = mysqli_prepare($connect, "UPDATE categories SET category=?, slug=?, description=?, image=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_update, "ssssi", $category_name, $slug, $description, $image_path, $id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Nettoyage physique de l'ancienne image seulement après succès BDD
            if ($old_image_path_to_delete && file_exists($old_image_path_to_delete)) {
                @unlink($old_image_path_to_delete);
            }
            
            echo '<div class="alert alert-success m-3">Category updated successfully.</div>';
            echo '<meta http-equiv="refresh" content="1; url=categorys.php">';
            // Recharger les données pour l'affichage immédiat
            // (code omis pour brièveté, la redirection suffit)
        } else {
            echo '<div class="alert alert-danger m-3">Error updating database.</div>';
        }
        mysqli_stmt_close($stmt_update);
    } else {
         echo '<div class="alert alert-danger m-3">' . $error_msg . '</div>';
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Edit Category</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Edit Category</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">General</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug (Auto-generated)</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['slug']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($row['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Featured Image</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="form-group mb-3">
                                <label class="d-block text-left">Current Image</label>
                                <?php 
                                    $has_custom_image = !empty($row['image']) && file_exists("../" . $row['image']);
                                    $img_src = $has_custom_image ? '../' . $row['image'] : '../assets/img/category_default.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                
                                <div class="mt-2">
                                    <?php if($has_custom_image): ?>
                                        <span class="badge badge-info">Custom Image</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Default Image</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if($has_custom_image): ?>
                                <div class="form-group text-left bg-light p-2 rounded border border-danger">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="deleteImageCheck" name="delete_image" value="1">
                                        <label class="custom-control-label text-danger font-weight-bold" for="deleteImageCheck">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete custom image (Revert to default)
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group text-left">
                                <label>Change Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="image" id="exampleInputFile">
                                    <label class="custom-file-label" for="exampleInputFile">Choose new file</label>
                                </div>
                                <small class="text-muted">JPG, PNG or GIF. Max size 2MB.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                             <button type="submit" name="submit" class="btn btn-primary btn-block">Save Changes</button>
                             <a href="categorys.php" class="btn btn-default btn-block mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
$(document).ready(function() {
    // Petit script pour afficher le nom du fichier sélectionné
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        
        // Si on choisit un fichier, on décoche la case "Supprimer l'image" si elle existe
        if(fileName) {
            $('#delete_image').prop('checked', false);
        }
    });
});
</script>

<?php include "footer.php"; ?>