<?php
include "core.php";
head();

// L'utilisateur doit être connecté pour soumettre un article
if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login.php">';
    exit;
}

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

$message = '';

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $title   = $_POST['title'];
    $content = $_POST['content'];
    $category_id = (int)$_POST['category_id']; // Utiliser la catégorie sélectionnée
    $author_id = $rowu['id'];
    $image = ''; // Initialiser l'image

    // Vérifications de base
    if (empty($title) || empty($content) || empty($category_id)) {
        $message = '<div class="alert alert-danger">The title, content, and category are required.</div>';
    } else {
        // Gérer l'upload de l'image
        if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {
            $target_dir    = "uploads/posts/";
            $target_file   = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $uploadOk = 1;

            // Vérifier si le fichier est une image réelle
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = '<div class="alert alert-danger">The file is not a valid image.</div>';
                $uploadOk = 0;
            }

            // Vérifier la taille du fichier (10MB max)
            if ($_FILES["image"]["size"] > 10000000) {
                $message = '<div class="alert alert-danger">Sorry, your file is too large (max 10MB).</div>';
                $uploadOk = 0;
            }

            // Autoriser certains formats de fichiers
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $message = '<div class="alert alert-danger">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                // --- MODIFICATION ---
                $string     = "0123456789wsderfgtyhjuk";
                $new_string = str_shuffle($string);
                // Chemin de destination SANS extension
                $destination_path = $target_dir . "image_" . $new_string; 
                
                // Appeler la nouvelle fonction d'optimisation
                $optimized_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $destination_path);
                
                if ($optimized_path) {
                    // $optimized_path est déjà relatif (ex: uploads/posts/image_abc.jpg)
                    $image = $optimized_path;
                } else {
                    $uploadOk = 0;
                    $message = '<div class="alert alert-danger">An error occurred while processing the image.</div>';
                }
                // --- FIN MODIFICATION ---
            } else {
                 if(empty($message)) { // N'ajoutez ce message que s'il n'y a pas déjà une erreur
                     $message .= '<div class="alert alert-danger">Your image could not be uploaded.</div>';
                 }
            }
        }

        // Continuer seulement si l'upload s'est bien passé (ou s'il n'y avait pas d'image)
        if (empty($message)) {
            // Utiliser HTMLPurifier pour nettoyer le contenu
            // require_once 'vendor/htmlpurifier/library/HTMLPurifier.auto.php';
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
            $clean_content = $purifier->purify($content);

            // Insérer l'article avec le statut "En attente"
            $slug = generateSeoURL($title, 1);
            $active_status = 'Pending';

            $stmt = mysqli_prepare($connect, "INSERT INTO posts (title, slug, content, author_id, category_id, active, image) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
            mysqli_stmt_bind_param($stmt, "sssiis", $title, $slug, $clean_content, $author_id, $category_id, $image);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = '<div class="alert alert-success">Your post has been successfully submitted and is now pending approval.</div>';
            } else {
                $message = '<div class="alert alert-danger">An error occurred while submitting your post.</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="col-md-8 mb-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-pen-square"></i> Submit a Post
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group mb-3">
                    <label for="title">Post Title</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <?php
                        // Récupérer les catégories pour le dropdown
                        $cat_query = mysqli_query($connect, "SELECT * FROM `categories` ORDER BY category ASC");
                        while ($cat_row = mysqli_fetch_assoc($cat_query)) {
                            echo '<option value="' . $cat_row['id'] . '">' . htmlspecialchars($cat_row['category']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="image">Post Image (optional)</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <small class="form-text text-muted">Max size 10MB. Allowed formats: JPG, PNG, GIF.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="summernote">Content</label>
                    <textarea name="content" id="summernote" class="form-control" required></textarea>
                </div>

                <div class="form-actions mt-4">
                    <input type="submit" name="submit" class="btn btn-primary col-12" value="Submit for Approval" />
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>