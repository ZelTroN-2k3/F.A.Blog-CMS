<?php
include "header.php";

// Sécurité : Seuls les admins accèdent à cette page
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

// --- LOGIQUE DE SUPPRESSION ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete-id'];
    
    // Protection : Ne pas supprimer son propre compte
    if ($id == $user['id']) {
        echo '<script>alert("You cannot delete your own account!"); window.location="users.php";</script>';
        exit;
    }

    // 1. Récupérer l'avatar pour le supprimer si c'est un fichier local unique
    $stmt_img = mysqli_prepare($connect, "SELECT avatar FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt_img, "i", $id);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    if ($r = mysqli_fetch_assoc($res_img)) {
        // On ne supprime que si c'est dans le dossier uploads/avatars (pour ne pas supprimer l'image par défaut)
        if (!empty($r['avatar']) && strpos($r['avatar'], 'uploads/avatars/') !== false && file_exists("../" . $r['avatar'])) {
            @unlink("../" . $r['avatar']);
        }
    }
    mysqli_stmt_close($stmt_img);

    // 2. Supprimer les commentaires de l'utilisateur
    $stmt_comm = mysqli_prepare($connect, "DELETE FROM `comments` WHERE user_id=?");
    mysqli_stmt_bind_param($stmt_comm, "i", $id);
    mysqli_stmt_execute($stmt_comm);
    mysqli_stmt_close($stmt_comm);

    // 3. Supprimer l'utilisateur
    $stmt_del = mysqli_prepare($connect, "DELETE FROM `users` WHERE id=?");
    mysqli_stmt_bind_param($stmt_del, "i", $id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);
    
    echo '<meta http-equiv="refresh" content="0; url=users.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-users"></i> User Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
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
                            <a href="add_user.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-user-plus"></i> Add New User
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-users" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">Avatar</th>
                                    <th>Username</th>
                                    <th>Email Address</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = mysqli_query($connect, "SELECT * FROM `users` ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    
                                    // Gestion Avatar Robuste (3 niveaux)
                                    $avatar_path = '../assets/img/avatar.png'; // Défaut
                                    
                                    if (!empty($row['avatar'])) {
                                        // Nettoyage chemin
                                        $clean_path = str_replace('../', '', $row['avatar']);
                                        
                                        // 1. URL externe (Google, etc.)
                                        if (strpos($clean_path, 'http') === 0) {
                                            $avatar_path = $clean_path;
                                        } 
                                        // 2. Fichier local existant
                                        elseif (file_exists('../' . $clean_path)) {
                                            $avatar_path = '../' . $clean_path;
                                        }
                                    }
                                    
                                    // Badge Rôle
                                    $role_badge = 'badge-secondary';
                                    if ($row['role'] == 'Admin') $role_badge = 'badge-danger';
                                    if ($row['role'] == 'Editor') $role_badge = 'badge-success';
                                    if ($row['role'] == 'User')   $role_badge = 'badge-info';

                                    echo '<tr>
                                        <td class="text-center">
                                            <img src="' . htmlspecialchars($avatar_path) . '" 
                                                 class="img-circle elevation-2" 
                                                 width="40" height="40" 
                                                 style="object-fit: cover;"
                                                 onerror="this.src=\'../assets/img/avatar.png\';">
                                        </td>
                                        <td><strong>' . htmlspecialchars($row['username']) . '</strong></td>
                                        <td>' . htmlspecialchars($row['email']) . '</td>
                                        <td class="text-center"><span class="badge ' . $role_badge . '">' . $row['role'] . '</span></td>
                                        <td class="text-center">
                                            <a href="edit_user.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>';
                                            
                                    // Empêcher la suppression de soi-même
                                    if ($row['id'] != $user['id']) {
                                        echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete user ' . htmlspecialchars($row['username']) . '? All comments will be removed.\');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                              </a>';
                                    } else {
                                        echo '<button class="btn btn-secondary btn-sm disabled" title="Current User"><i class="fas fa-user"></i></button>';
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
    $('#dt-users').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 1, "asc" ]] // Tri par Username
    });
});
</script>