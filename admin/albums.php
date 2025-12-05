<?php
include "header.php";

// --- LOGIQUE DE SUPPRESSION (SÉCURISÉE : ADMIN + AUTEUR) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete-id'];

    // 1. Récupérer l'auteur de l'album AVANT de supprimer
    $stmt_check = mysqli_prepare($connect, "SELECT author_id FROM albums WHERE id = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $album = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    if ($album) {
        // 2. Vérification des permissions : Est-ce l'Admin OU l'Auteur ?
        if ($user['role'] == 'Admin' || $album['author_id'] == $user['id']) {
            
            // A. Supprimer les images liées dans la galerie
            $stmt_imgs = mysqli_prepare($connect, "DELETE FROM `gallery` WHERE album_id=?");
            mysqli_stmt_bind_param($stmt_imgs, "i", $id);
            mysqli_stmt_execute($stmt_imgs);
            mysqli_stmt_close($stmt_imgs);

            // B. Supprimer l'album lui-même
            $stmt_alb = mysqli_prepare($connect, "DELETE FROM `albums` WHERE id=?");
            mysqli_stmt_bind_param($stmt_alb, "i", $id);
            mysqli_stmt_execute($stmt_alb);
            mysqli_stmt_close($stmt_alb);
            
            echo '<meta http-equiv="refresh" content="0; url=albums.php">';
            exit;
            
        } else {
            // Refus si ni Admin ni Auteur
            echo '<div class="alert alert-danger m-3">Access Denied. You can only delete your own albums.</div>';
        }
    } else {
        // L'album n'existe pas ou a déjà été supprimé
        echo '<meta http-equiv="refresh" content="0; url=albums.php">';
        exit;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-folder-open"></i> Albums</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Albums</li>
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
                            <a href="add_album.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Album
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-albums" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">ID</th>
                                    <th>Album Title</th>
                                    <th style="min-width: 180px;">Author</th> <th class="text-center">Images Count</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Requête modifiée : Ajout de u.avatar
                                $query = "
                                    SELECT a.*, COUNT(g.id) as image_count, u.username as author_name, u.role as author_role, u.avatar
                                    FROM albums a 
                                    LEFT JOIN gallery g ON a.id = g.album_id 
                                    LEFT JOIN users u ON a.author_id = u.id
                                    GROUP BY a.id 
                                    ORDER BY a.id DESC
                                ";
                                $result = mysqli_query($connect, $query);
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    
                                    // --- LOGIQUE AUTEUR STYLE POSTS.PHP ---
                                    $author_name = htmlspecialchars($row['author_name'] ?? 'Unknown');
                                    $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png'; // Avatar par défaut

                                    // --- LOGIQUE BADGE RÔLE ---
                                    $role_badge = '';
                                    if (isset($row['author_role'])) {
                                        // Style font-size 0.7em pour alignement user-block
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

                                    echo '<tr>
                                        <td class="text-center">' . $row['id'] . '</td>
                                        <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>
                                        
                                        <td>
                                            <div class="user-block">
                                                <img src="../' . htmlspecialchars($author_avatar) . '" width="40" height="40" class="img-circle elevation-1" alt="User" style="float:left; margin-right:10px; object-fit:cover;">
                                                <span class="username" style="font-size:14px;">' . $author_name . '</span>
                                                <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                                            </div>
                                        </td>

                                        <td class="text-center"><span class="badge badge-info">' . $row['image_count'] . '</span></td>
                                        
                                        <td class="text-center">';
                                            
                                            // --- BOUTONS D'ACTION (SÉCURISÉS) ---
                                            if ($is_admin || $is_mine) {
                                                echo '<a href="edit_album.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>';
                                                
                                                echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'WARNING: Deleting this album will DELETE ALL linked images in the gallery database. Continue?\');" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>';
                                            }
                                            
                                    echo '</td>
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
    $('#dt-albums').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]] 
    });
});
</script>