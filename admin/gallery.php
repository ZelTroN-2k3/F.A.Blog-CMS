<?php
include "header.php";

// --- LOGIQUE SUPPRESSION (SÉCURISÉE : ADMIN + AUTEUR) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];
    
    // 1. Récupérer les infos de l'image (Chemin + Auteur)
    $stmt_check = mysqli_prepare($connect, "SELECT image, author_id FROM gallery WHERE id=?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $img_data = mysqli_fetch_assoc($res_check);
    mysqli_stmt_close($stmt_check);

    if ($img_data) {
        // 2. Vérification des permissions : Est-ce l'Admin OU l'Auteur ?
        if ($user['role'] == 'Admin' || $img_data['author_id'] == $user['id']) {

            // A. Supprimer le fichier physique s'il existe
            if (!empty($img_data['image']) && file_exists("../" . $img_data['image'])) {
                unlink("../" . $img_data['image']);
            }

            // B. Supprimer l'entrée en base de données
            $stmt_del = mysqli_prepare($connect, "DELETE FROM `gallery` WHERE id=?");
            mysqli_stmt_bind_param($stmt_del, "i", $id);
            mysqli_stmt_execute($stmt_del);
            mysqli_stmt_close($stmt_del);
            
            echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
            exit;

        } else {
            echo '<div class="alert alert-danger m-3">Access Denied. You can only delete your own images.</div>';
        }
    } else {
        echo '<meta http-equiv="refresh" content="0; url=gallery.php">';
        exit;
    }
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
        
        <div class="row">
            <div class="col-12">
                
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <a href="add_image.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Image
                            </a>
                        </h3>

                        <!-- AJOUT DES FILTRES ICI -->
                        <div class="card-tools">
                            <!--<div class="btn-group">-->
                                <?php
                                // Gestion de l'état actif des boutons
                                $st = $_GET['status'] ?? 'all';
                                ?>
                                <a href="gallery.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                                <a href="gallery.php?status=published" class="btn btn-sm <?php echo ($st == 'published') ? 'btn-success' : 'btn-default text-success'; ?>">Published</a>
                                <a href="gallery.php?status=draft" class="btn btn-sm <?php echo ($st == 'draft') ? 'btn-danger' : 'btn-default text-danger'; ?>">Drafts</a>
                            <!--</div>-->
                        </div>
                        <!-- FIN AJOUT -->
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="dt-gallery" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 120px;" class="text-center">Preview</th>
                                    <th>Title</th>
                                    <th style="min-width: 180px;">Author</th> <th>Album</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // --- LOGIQUE DE FILTRE SQL ---
                            $where_clause = "";
                            if (isset($_GET['status'])) {
                                if ($_GET['status'] == 'published') {
                                    $where_clause = "WHERE g.active = 'Yes'";
                                } elseif ($_GET['status'] == 'draft') {
                                    $where_clause = "WHERE g.active = 'No'";
                                }
                            }

                            // Requête mise à jour avec le WHERE dynamique
                            $query = "
                                SELECT g.*, a.title as album_title, u.username as author_name, u.role as author_role, u.avatar
                                FROM gallery g 
                                LEFT JOIN albums a ON g.album_id = a.id 
                                LEFT JOIN users u ON g.author_id = u.id
                                $where_clause
                                ORDER BY g.id DESC
                            ";
                            $sql = mysqli_query($connect, $query);

                            while ($row = mysqli_fetch_assoc($sql)) {
                                
                                $album_name = !empty($row['album_title']) ? htmlspecialchars($row['album_title']) : '<span class="text-muted">Uncategorized</span>';
                                
                                // --- LOGIQUE AUTEUR ---
                                $author_name = htmlspecialchars($row['author_name'] ?? 'Unknown');
                                $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
                                
                                // --- CORRECTION AVATAR ROBUSTE ---
                                $clean_avatar = str_replace('../', '', $author_avatar);
                                if (strpos($clean_avatar, 'http') !== 0) {
                                    $clean_avatar = '../' . $clean_avatar;
                                }

                                // --- LOGIQUE BADGE RÔLE ---
                                $role_badge = '';
                                if (isset($row['author_role'])) {
                                    if ($row['author_role'] == 'Admin') {
                                        $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
                                    } elseif ($row['author_role'] == 'Editor') {
                                        $role_badge = '<small class="badge badge-primary" style="font-size: 0.7em;">Editor</small>';
                                    } else {
                                        $role_badge = '<small class="badge badge-secondary" style="font-size: 0.7em;">User</small>';
                                    }
                                }
                                
                                // --- PERMISSIONS ---
                                $is_mine = ($row['author_id'] == $user['id']);
                                $is_admin = ($user['role'] == 'Admin');

                                echo '
                                    <tr>
                                        <td class="text-center align-middle">';
                                        if ($row['image'] != '') {
                                            // Nettoyage chemin image
                                            $gallery_img_src = '../' . str_replace('../', '', $row['image']);
                                            
                                            echo '<a href="' . htmlspecialchars($gallery_img_src) . '" data-toggle="lightbox" data-title="' . htmlspecialchars($row['title']) . '">
                                                    <img src="' . htmlspecialchars($gallery_img_src) . '" width="80" height="60" style="object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src=\'../assets/img/no-image.png\';" />
                                                </a>';
                                        } else {
                                            echo '<span class="text-muted">No Image</span>';
                                        }
                                echo '  </td>
                                        <td class="align-middle">' . htmlspecialchars($row['title']) . '</td>
                                        
                                        <td class="align-middle">
                                            <div class="user-block">
                                                <img src="' . htmlspecialchars($clean_avatar) . '" 
                                                     class="img-circle elevation-1" 
                                                     width="40" height="40" 
                                                     style="float:left; margin-right:10px; object-fit:cover;"
                                                     onerror="this.src=\'../assets/img/avatar.png\';">
                                                <span class="username" style="font-size:14px;">' . $author_name . '</span>
                                                <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                                            </div>
                                        </td>
                                        
                                        <td class="align-middle">' . $album_name . '</td>
                                        
                                        <td class="text-center align-middle">';
                                        
                                        if ($row['active'] == "Yes") {
                                            echo '<span class="badge badge-success">Active</span>';
                                        } else {
                                            echo '<span class="badge badge-danger">Inactive</span>';
                                        }
                                        
                                echo '  </td>
                                        <td class="text-center align-middle">';
                                            
                                            if ($is_admin || $is_mine) {
                                                echo '<a href="edit_gallery.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>';
                                                
                                                echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this image?\');" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>';
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

<script>
$(document).ready(function() {
    $('#dt-gallery').DataTable({
        "responsive": true, 
        "autoWidth": false,
        "order": [[ 1, "asc" ]]
    });
    
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({
            alwaysShowClose: true
        });
    });
});
</script>

<?php include "footer.php"; ?>