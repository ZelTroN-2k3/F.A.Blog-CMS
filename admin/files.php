<?php
include "header.php";

// --- LOGIQUE SUPPRESSION SÉCURISÉE ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];

    // 1. Récupérer les infos du fichier (Chemin ET Auteur)
    $stmt = mysqli_prepare($connect, "SELECT path, author_id FROM `files` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        // 2. VÉRIFICATION DES PERMISSIONS
        if ($user['role'] == 'Admin' || $row['author_id'] == $user['id']) {
            
            $file_path = "../" . $row['path'];
            if (file_exists($file_path) && is_file($file_path)) {
                unlink($file_path);
            }

        // 3. Supprimer de la BDD
        $stmt_delete = mysqli_prepare($connect, "DELETE FROM `files` WHERE id=?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        mysqli_stmt_execute($stmt_delete);
        
        // --- LOG ACTIVITY ---
        log_activity($user['id'], "Delete File", "Deleted file ID: " . $id);
        
        mysqli_stmt_close($stmt_delete);
        } else {
            echo '<div class="alert alert-danger m-3">Access Denied. You can only delete your own files.</div>';
            echo '<meta http-equiv="refresh" content="2; url=files.php">';
            exit;
        }
    }
    
    echo '<meta http-equiv="refresh" content="0; url=files.php">';
    exit;
}

// Fonction utilitaire pour formater la taille
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
                <h1 class="m-0"><i class="fas fa-folder-open"></i> File Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Files</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <a href="upload_file.php" class="btn btn-success btn-sm">
                                <i class="fas fa-cloud-upload-alt"></i> Upload New File
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-files" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 30px;" class="text-center">ID</th>
                                    <th style="width: 60px;" class="text-center">Preview</th>
                                    <th>Filename & Details</th>
                                    <th style="min-width: 180px;">Author</th> <th class="text-center">Type</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
// REQUÊTE : On récupère l'avatar (u.avatar)
$query = "
    SELECT f.*, u.username, u.role, u.avatar 
    FROM files f 
    LEFT JOIN users u ON f.author_id = u.id 
    ORDER BY f.id DESC
";
$sql = mysqli_query($connect, $query);

while ($row = mysqli_fetch_assoc($sql)) {
    
    $filename = htmlspecialchars($row['filename']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $path = htmlspecialchars($row['path']); 
    $full_url = $settings['site_url'] . '/' . $path;
    
    $full_path = '../' . $row['path'];
    $file_size = file_exists($full_path) ? formatBytes(filesize($full_path)) : 'N/A';

    // --- LOGIQUE AUTEUR STYLE POSTS.PHP ---
    $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
    $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png'; // Avatar par défaut

    $role_badge = '';
    if (isset($row['role'])) {
        // Ajout de style="font-size: 0.7em;" pour correspondre aux autres pages
        if ($row['role'] == 'Admin') {
            $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
        } elseif ($row['role'] == 'Editor') {
            $role_badge = '<small class="badge badge-primary" style="font-size: 0.7em;">Editor</small>';
        } else {
            $role_badge = '<small class="badge badge-secondary" style="font-size: 0.7em;">User</small>';
        }
    }
    
    // --- LOGIQUE D'ICÔNES ROBUSTE ---
    $icon = '<i class="fas fa-file fa-2x text-secondary"></i>';
    // Nettoyage du chemin pour l'affichage
    $clean_path = str_replace('../', '', $path);
    $display_path = '../' . $clean_path;
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'])) {
        // Ajout de onerror pour la robustesse
        $icon = '<img src="' . htmlspecialchars($display_path) . '" 
                      width="50" height="50" 
                      style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                      onerror="this.onerror=null; this.parentNode.innerHTML=\'<i class=\\\'fas fa-image fa-2x text-muted\\\'></i>\';">';
    } elseif (in_array($ext, ['pdf'])) {
        $icon = '<i class="fas fa-file-pdf fa-2x text-danger"></i>';
    } elseif (in_array($ext, ['doc', 'docx', 'odt', 'rtf', 'txt'])) {
        $icon = '<i class="fas fa-file-word fa-2x text-primary"></i>';
    } elseif (in_array($ext, ['xls', 'xlsx', 'ods', 'csv'])) {
        $icon = '<i class="fas fa-file-excel fa-2x text-success"></i>';
    } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
        $icon = '<i class="fas fa-file-archive fa-2x text-warning"></i>';
    } elseif (in_array($ext, ['mp3', 'wav'])) {
        $icon = '<i class="fas fa-file-audio fa-2x text-info"></i>';
    } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
        $icon = '<i class="fas fa-file-video fa-2x text-purple"></i>';
    }

    // --- PERMISSIONS ---
    $is_mine = ($row['author_id'] == $user['id']);
    $is_admin = ($user['role'] == 'Admin');

    echo '
        <tr>
            <td class="text-center align-middle">' . $row['id'] . '</td>
            <td class="text-center align-middle">' . $icon . '</td>
            <td class="align-middle">
                <strong>' . $filename . '</strong><br>
                <small class="text-muted"><i class="fas fa-link"></i> ' . $path . '</small>
            </td>
            
            <td class="align-middle">
                <div class="user-block">
                    <img src="../' . htmlspecialchars($author_avatar) . '" width="40" height="40" class="img-circle elevation-1" alt="User" style="float:left; margin-right:10px; object-fit:cover;">
                    <span class="username" style="font-size:14px;">' . $author_name . '</span>
                    <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                </div>
            </td>
            
            <td class="text-center align-middle"><span class="badge badge-light border">' . strtoupper($ext) . '</span></td>
            <td class="text-center align-middle">' . $file_size . '</td> 
            <td class="text-center align-middle" data-sort="' . strtotime($row['created_at']) . '">' . date('d M Y', strtotime($row['created_at'])) . '</td>
            
            <td class="text-center align-middle">
                <a href="../' . $path . '" target="_blank" class="btn btn-info btn-sm" title="View/Download"><i class="fas fa-eye"></i></a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="copyLink(\'' . $full_url . '\')" title="Copy URL"><i class="fas fa-copy"></i></button>';
                
                if ($is_admin || $is_mine) {
                    echo ' <a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this file permanently?\');" title="Delete"><i class="fas fa-trash"></i></a>';
                }
                
    echo '  </td>
        </tr>';
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('#dt-files').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]] // Trier par ID descendant
    });
});

function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        $(document).Toasts('create', {
            class: 'bg-success',
            title: 'Copied!',
            body: 'File URL copied to clipboard.',
            autohide: true,
            delay: 2000
        });
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
}
</script>