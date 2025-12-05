<?php
include "header.php";

if (isset($_POST['upload'])) {
    validate_csrf_token();
    
    // Configuration
    $target_dir = "../uploads/files/"; // Assurez-vous que ce dossier existe
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $file = $_FILES['file'];
    $filename = basename($file['name']);
    // Nettoyage du nom de fichier (sécurité)
    $filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $filename);
    
    $target_file = $target_dir . $filename;
    $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Liste complète des extensions autorisées (selon votre demande)
    $allowed_extensions = [
        'png', 'gif', 'jpg', 'jpeg', 'bmp', 'webp', // Images
        'doc', 'docx', 'pdf', 'txt', 'odt', 'rtf', 'csv', 'ods', 'xls', 'xlsx', 'odp', 'ppt', 'pptx', // Docs
        'rar', 'zip', // Archives
        'mp3', 'flac', 'wav', 'wma', 'aac', 'm4a', // Audio
        'mov', 'avi', 'mkv', 'mp4', 'wmv', 'webm', 'ts' // Video
    ];

    if (!in_array($file_ext, $allowed_extensions)) {
        echo '<div class="alert alert-danger m-3">Error: File type <b>.' . $file_ext . '</b> is not allowed.</div>';
    } else {
        // Gestion des doublons : ajouter un timestamp si le fichier existe
        if (file_exists($target_file)) {
            $filename = time() . "_" . $filename;
            $target_file = $target_dir . $filename;
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $db_path = "uploads/files/" . $filename;

            // --- MODIFICATION : Ajout de l'auteur ---
            $author_id = $user['id'];
            
            // Insertion BDD
            $stmt = mysqli_prepare($connect, "INSERT INTO files (filename, path, created_at, author_id) VALUES (?, ?, NOW(), ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $filename, $db_path, $author_id);
            // ----------------------------------------
            
            if (mysqli_stmt_execute($stmt)) {
                // --- LOG ACTIVITY ---
                log_activity($user['id'], "Upload File", "Uploaded file: " . $filename);
                // -----------
                echo '<div class="alert alert-success m-3">File uploaded successfully! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="1; url=files.php">';
                exit;
            } else {
                echo '<div class="alert alert-danger m-3">Database error: ' . mysqli_error($connect) . '</div>';
            }
            mysqli_stmt_close($stmt);
        } else {
            echo '<div class="alert alert-danger m-3">Sorry, there was an error moving your file. Check folder permissions.</div>';
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cloud-upload-alt"></i> Upload File</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="files.php">Files</a></li>
                    <li class="breadcrumb-item active">Upload</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Select File</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Choose File from Computer</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file" required>
                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                </div>
                            </div>
                            
                            <div class="alert alert-light border mt-3">
                                <i class="fas fa-info-circle text-info"></i> 
                                Files will be saved in <code>/uploads/files/</code> folder.
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="upload" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Now
                            </button>
                            <a href="files.php" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-check-circle"></i> Allowed Formats</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item">
                                    <strong>Images:</strong> png, gif, jpg, jpeg, bmp, webp
                                </li>
                                <li class="list-group-item">
                                    <strong>Documents:</strong> doc, docx, pdf, txt, odt, rtf, csv, ods, xls, xlsx, odp, ppt, pptx
                                </li>
                                <li class="list-group-item">
                                    <strong>Archives:</strong> rar, zip
                                </li>
                                <li class="list-group-item">
                                    <strong>Audio:</strong> mp3, flac, wav, wma, aac, m4a
                                </li>
                                <li class="list-group-item">
                                    <strong>Video:</strong> mov, avi, mkv, mp4, wmv, webm, ts
                                </li>
                            </ul>
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
    // Afficher le nom du fichier sélectionné
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>