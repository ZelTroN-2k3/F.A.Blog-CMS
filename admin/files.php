<?php
include "header.php";

// --- 1. LOGIQUE SUPPRESSION (Gardée et Sécurisée) ---
$msg = "";

if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];

    // Récupérer les infos
    $stmt = mysqli_prepare($connect, "SELECT path, author_id FROM `files` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($file_data) {
        // Vérification Permissions
        if ($rowu['role'] == 'Admin' || $file_data['author_id'] == $rowu['id']) {
            
            $file_path_disk = "../" . $file_data['path'];
            
            // Supprimer le fichier physique
            if (file_exists($file_path_disk) && is_file($file_path_disk)) {
                unlink($file_path_disk);
            }

            // Supprimer de la BDD
            $stmt_del = mysqli_prepare($connect, "DELETE FROM `files` WHERE id=?");
            mysqli_stmt_bind_param($stmt_del, "i", $id);
            if(mysqli_stmt_execute($stmt_del)){
                $msg = '<div class="alert alert-success"><i class="fas fa-check"></i> File deleted successfully.</div>';
                // Log
                if(function_exists('log_activity')) { log_activity($rowu['id'], "Delete File", "Deleted file ID: " . $id); }
            }
            mysqli_stmt_close($stmt_del);
            
        } else {
            $msg = '<div class="alert alert-danger"><i class="fas fa-ban"></i> Access Denied.</div>';
        }
    }
}

// Fonction utilitaire taille
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow); 
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-images text-purple"></i> Media Manager</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="upload_file.php" class="btn btn-success"><i class="fas fa-cloud-upload-alt"></i> Upload New</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <?php echo $msg; ?>

        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="mediaSearch" class="form-control" placeholder="Filter files by name...">
                </div>
            </div>
        </div>

        <div class="row" id="mediaGrid">
            <?php
            // Récupération depuis la BDD (comme avant, mais affichage différent)
            $query = "SELECT f.*, u.username FROM files f LEFT JOIN users u ON f.author_id = u.id ORDER BY f.id DESC";
            $sql = mysqli_query($connect, $query);
            
            if (mysqli_num_rows($sql) > 0) {
                while ($row = mysqli_fetch_assoc($sql)) {
                    
                    $filename = htmlspecialchars($row['filename']);
                    $path = $row['path']; // ex: uploads/image.jpg
                    $full_url = $settings['site_url'] . '/' . $path;
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    // Déterminer l'affichage (Image ou Icône)
                    $preview_html = '';
                    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                    
                    if ($is_image) {
                        $preview_html = '<img src="../' . htmlspecialchars($path) . '" alt="' . $filename . '" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">';
                    } else {
                        // Icônes FontAwesome selon l'extension
                        $fa_icon = 'fa-file';
                        $color = 'text-secondary';
                        
                        if ($ext == 'pdf') { $fa_icon = 'fa-file-pdf'; $color = 'text-danger'; }
                        if (in_array($ext, ['zip', 'rar', '7z'])) { $fa_icon = 'fa-file-archive'; $color = 'text-warning'; }
                        if (in_array($ext, ['doc', 'docx', 'txt'])) { $fa_icon = 'fa-file-word'; $color = 'text-primary'; }
                        if (in_array($ext, ['xls', 'xlsx', 'csv'])) { $fa_icon = 'fa-file-excel'; $color = 'text-success'; }
                        if (in_array($ext, ['mp3', 'wav'])) { $fa_icon = 'fa-music'; $color = 'text-info'; }
                        if (in_array($ext, ['mp4', 'avi'])) { $fa_icon = 'fa-video'; $color = 'text-purple'; }
                        
                        $preview_html = '<i class="fas ' . $fa_icon . ' fa-4x ' . $color . '"></i>';
                    }

                    // Taille du fichier (sur disque)
                    $disk_path = '../' . $path;
                    $size_str = file_exists($disk_path) ? formatBytes(filesize($disk_path)) : 'Unknown';

                    // Permissions
                    $can_delete = ($rowu['role'] == 'Admin' || $row['author_id'] == $rowu['id']);

                    ?>
                    <div class="col-lg-2 col-md-3 col-6 mb-4 media-item" data-name="<?php echo strtolower($filename); ?>">
                        <div class="card h-100 shadow-sm media-card border-0">
                            
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light position-relative" style="height: 140px; overflow: hidden;">
                                <?php echo $preview_html; ?>
                                <a href="<?php echo $full_url; ?>" target="_blank" class="stretched-link"></a>
                            </div>

                            <div class="card-body p-2 bg-white">
                                <h6 class="text-truncate font-weight-bold mb-0 text-dark" title="<?php echo $filename; ?>" style="font-size: 0.85rem;">
                                    <?php echo $filename; ?>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge bg-light text-dark border"><?php echo strtoupper($ext); ?></span>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo $size_str; ?></small>
                                </div>
                                <small class="d-block text-muted mt-1" style="font-size: 0.65rem;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($row['username']); ?>
                                </small>
                            </div>

                            <div class="card-footer p-1 bg-light text-center border-top-0">
                                <div class="btn-group btn-group-sm w-100">
                                    
                                    <button type="button" class="btn btn-default" onclick="copyLink('<?php echo $full_url; ?>')" title="Copy Link">
                                        <i class="fas fa-link"></i>
                                    </button>
                                    
                                    <a href="<?php echo $full_url; ?>" download class="btn btn-default" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>

                                    <?php if ($can_delete): ?>
                                    <a href="?delete-id=<?php echo $row['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-default text-danger" onclick="return confirm('Delete this file permanently?');" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center text-muted py-5"><h4><i class="fas fa-box-open fa-2x mb-3"></i><br>No files uploaded yet.</h4></div>';
            }
            ?>
        </div>
    </div>
</section>

<style>
.media-card { transition: transform 0.2s, box-shadow 0.2s; }
.media-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; z-index: 10; }
.media-card .btn-default:hover { background-color: #e9ecef; color: #007bff; }
</style>

<script>
// 1. Filtrage instantané
document.getElementById('mediaSearch').addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var items = document.querySelectorAll('.media-item');
    
    items.forEach(function(item) {
        var name = item.getAttribute('data-name');
        if (name.includes(filter)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// 2. Copier le lien
function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        // On utilise le Toast de AdminLTE s'il est dispo, sinon alert
        if (typeof $(document).Toasts === 'function') {
            $(document).Toasts('create', {
                class: 'bg-success',
                title: 'Copied!',
                body: 'Link copied to clipboard.',
                autohide: true,
                delay: 2000
            });
        } else {
            alert('Link copied: ' + url);
        }
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
}
</script>

<?php include "footer.php"; ?>