<?php
include "header.php";

// Sécurité Admin
if ($user['role'] != "Admin") {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php" />'; exit;
}

if (isset($_POST['add_user'])) {
    validate_csrf_token();

    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $role     = $_POST['role'];
    
    // --- GESTION AVATAR ---
    // 1. Par défaut (ou sélection bibliothèque)
    $avatar = isset($_POST['selected_image']) ? $_POST['selected_image'] : 'assets/img/avatar.png';

    // 2. Si upload manuel
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "../uploads/avatars/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $new_name = "user_" . uniqid() . "." . $ext;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            // Utilisation de la fonction d'optimisation si dispo
            if (function_exists('optimize_and_save_image')) {
                $optimized_path = optimize_and_save_image($_FILES["avatar"]["tmp_name"], $target_dir . "user_" . uniqid());
                if ($optimized_path) $avatar = str_replace("../", "", $optimized_path);
            } else {
                // Fallback classique
                if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_dir . $new_name)){
                    $avatar = "uploads/avatars/" . $new_name;
                }
            }
        }
    }

    // Validations
    if (strlen($username) < 3) {
        echo '<div class="alert alert-danger m-3">The username must contain at least 3 characters.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger m-3">The email address is not valid.</div>';
    } elseif (strlen($password) < 5) {
        echo '<div class="alert alert-danger m-3">The password must contain at least 5 characters.</div>';
    } else {
        
        // Vérifier doublon
        $stmt_check = mysqli_prepare($connect, "SELECT id FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            echo '<div class="alert alert-warning m-3">This username is already taken.</div>';
        } else {
            // Insertion
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($connect, "INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password_hash, $role, $avatar);
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<div class="alert alert-success m-3">User created successfully! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="1; url=users.php">';
                exit;
            } else {
                echo '<div class="alert alert-danger m-3">Error creating user.</div>';
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-plus"></i> Create User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                    <li class="breadcrumb-item active">Add</li>
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
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input class="form-control" name="username" type="text" placeholder="Enter username" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input class="form-control" name="email" type="email" placeholder="email@example.com" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input class="form-control" name="password" type="password" placeholder="Min. 5 characters" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Permissions & Profile</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role" class="form-control" required>
                                    <option value="User" selected>User (Comment only)</option>
                                    <option value="Editor">Editor (Manage posts)</option>
                                    <option value="Admin">Admin (Full access)</option>
                                </select>
                            </div>

                            <div class="form-group text-center">
                                <label>Avatar</label>
                                <div class="mb-2">
                                    <img src="../assets/img/avatar.png" id="preview_image_box" class="img-circle elevation-2" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                
                                <div class="custom-file text-left mb-2">
                                    <input type="file" class="custom-file-input" id="avatarUpload" name="avatar">
                                    <label class="custom-file-label" for="avatarUpload">Upload New</label>
                                </div>
                                
                                <div class="text-muted small mb-2">- OR -</div>
                                
                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" data-toggle="modal" data-target="#filesModal">
                                    <i class="fas fa-images"></i> Select from Library
                                </button>
                                <input type="hidden" name="selected_image" id="selected_image_input" value="">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_user" class="btn btn-primary btn-block">
                                <i class="fas fa-check"></i> Create User
                            </button>
                            <a href="users.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="filesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select an Avatar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="files-gallery-content">
        <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    // 1. Aperçu Upload Manuel
    $('#avatarUpload').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
        
        // Vider la sélection bibliothèque
        $('#selected_image_input').val('');
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_image_box').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // 2. Chargement Bibliothèque (Ajax)
    $('#filesModal').on('show.bs.modal', function (e) {
        if($('#files-gallery-content').html().indexOf('fa-spinner') !== -1) {
            $.get('ajax_load_files.php', function(data) {
                $('#files-gallery-content').html(data);
            });
        }
    });
});

// 3. Fonction Selection Bibliothèque
function selectFile(dbValue, fullPath) {
    document.getElementById('selected_image_input').value = dbValue;
    document.getElementById('preview_image_box').src = fullPath;
    document.getElementById('avatarUpload').value = ""; // Reset upload
    $('.custom-file-label').html('Upload New');
    $('#filesModal').modal('hide');
}
</script>
