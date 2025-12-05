<?php
include "header.php";

// 1. Validation ID
if (!isset($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=users.php" />'; exit;
}
$edit_id = (int)$_GET['id'];

// --- SÉCURITÉ CRITIQUE : Restriction d'accès ---
// Si l'utilisateur n'est PAS Admin, il ne peut modifier QUE son propre compte.
if ($user['role'] != 'Admin' && $edit_id != $user['id']) {
    echo '<div class="content-header"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only edit your own profile.
          </div></div>';
    include "footer.php";
    exit;
}
// -----------------------------------------------

// Récupérer les infos utilisateur à éditer
$stmt = mysqli_prepare($connect, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $edit_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user_data) {
    echo '<div class="alert alert-danger m-3">User not found.</div>';
    include "footer.php"; exit;
}

// Traitement du formulaire
if (isset($_POST['update_user'])) {
    validate_csrf_token();

    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $avatar   = $user_data['avatar']; // Garder l'ancien par défaut
    
    // --- SÉCURITÉ RÔLE (Backend) ---
    // Seul un Admin peut changer le rôle via le formulaire.
    // Si un Éditeur tente de changer le POST['role'], on l'ignore et on garde l'ancien.
    if ($user['role'] == 'Admin') {
        $role = $_POST['role'];
    } else {
        $role = $user_data['role'];
    }
    // --------------------------------

    // Validation basique
    if (strlen($username) < 3) {
        echo '<div class="alert alert-danger m-3">Username too short.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger m-3">Invalid email.</div>';
    } else {
        
        // Gestion Avatar
        if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != "") {
            $target_dir = "../uploads/avatars/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            
            if (function_exists('optimize_and_save_image')) {
                 $optimized = optimize_and_save_image($_FILES["avatar"]["tmp_name"], $target_dir . "user_" . uniqid());
                 if ($optimized) {
                     if (!empty($user_data['avatar']) && file_exists("../" . $user_data['avatar'])) { @unlink("../" . $user_data['avatar']); }
                     $avatar = str_replace("../", "", $optimized);
                 }
            } else {
                $new_name = "user_" . time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_dir . $new_name)) {
                    if (!empty($user_data['avatar']) && file_exists("../" . $user_data['avatar'])) { @unlink("../" . $user_data['avatar']); }
                    $avatar = "uploads/avatars/" . $new_name;
                }
            }
        }

        // Mise à jour
        if (!empty($password)) {
            // Si mot de passe fourni, on le hash et on update tout
            if (strlen($password) < 5) {
                echo '<div class="alert alert-danger m-3">New password is too short (min 5 chars).</div>';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_up = mysqli_prepare($connect, "UPDATE users SET username=?, email=?, password=?, role=?, avatar=? WHERE id=?");
                mysqli_stmt_bind_param($stmt_up, "sssssi", $username, $email, $hash, $role, $avatar, $edit_id);
                mysqli_stmt_execute($stmt_up);
                // --- LOG ACTIVITY ---
                log_activity($user['id'], "Update Profile", "Updated profile (Password changed) for User ID: " . $edit_id);
                // -----------
                mysqli_stmt_close($stmt_up);
                echo '<div class="alert alert-success m-3">Profile updated (password changed)! Redirecting...</div>';
                
                // Redirection intelligente : Si Admin -> Liste users, Si Editor -> Dashboard ou Profile
                if ($user['role'] == 'Admin') {
                    echo '<meta http-equiv="refresh" content="1; url=users.php">';
                } else {
                    echo '<meta http-equiv="refresh" content="1; url=dashboard.php">';
                }
                exit;
            }
        } else {
            // Sinon on garde l'ancien mot de passe
            $stmt_up = mysqli_prepare($connect, "UPDATE users SET username=?, email=?, role=?, avatar=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_up, "ssssi", $username, $email, $role, $avatar, $edit_id);
            mysqli_stmt_execute($stmt_up);
            // --- LOG ACTIVITY ---
            log_activity($user['id'], "Update Profile", "Updated profile details for User ID: " . $edit_id);
            // -----------
            mysqli_stmt_close($stmt_up);
            echo '<div class="alert alert-success m-3">Profile updated! Redirecting...</div>';
            
            if ($user['role'] == 'Admin') {
                echo '<meta http-equiv="refresh" content="1; url=users.php">';
            } else {
                echo '<meta http-equiv="refresh" content="1; url=dashboard.php">';
            }
            exit;
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-edit"></i> Edit User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <?php if ($user['role'] == 'Admin'): ?>
                        <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">Edit</li>
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
                            <h3 class="card-title">Account Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Username</label>
                                <input class="form-control" name="username" type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input class="form-control" name="email" type="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <input class="form-control" name="password" type="password" placeholder="Leave empty to keep current password">
                                <small class="text-muted">Only fill this if you want to change the password.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Profile & Role</h3>
                        </div>
                        <div class="card-body">
                            
                            <div class="form-group">
                                <label>Role</label>
                                <?php if ($user['role'] == 'Admin'): ?>
                                    <select name="role" class="form-control" required>
                                        <option value="User" <?php if($user_data['role']=='User') echo 'selected'; ?>>User</option>
                                        <option value="Editor" <?php if($user_data['role']=='Editor') echo 'selected'; ?>>Editor</option>
                                        <option value="Admin" <?php if($user_data['role']=='Admin') echo 'selected'; ?>>Admin</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['role']); ?>" disabled>
                                    <small class="text-muted">Only Admins can change roles.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group text-center">
                                <label class="text-left w-100">Avatar</label>
                                <?php 
                                $display_avatar = !empty($user_data['avatar']) ? '../' . $user_data['avatar'] : '../assets/img/avatar.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($display_avatar); ?>" class="img-circle elevation-2 mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                
                                <div class="custom-file text-left">
                                    <input type="file" class="custom-file-input" id="avatarUpload" name="avatar">
                                    <label class="custom-file-label" for="avatarUpload">Change file</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="update_user" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <?php if ($user['role'] == 'Admin'): ?>
                                <a href="users.php" class="btn btn-default btn-block">Cancel</a>
                            <?php else: ?>
                                <a href="dashboard.php" class="btn btn-default btn-block">Cancel</a>
                            <?php endif; ?>
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
    $('#avatarUpload').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>