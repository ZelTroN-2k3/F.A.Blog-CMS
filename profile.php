<?php
include "core.php";
head();

// 1. Sécurité : Connexion requise
if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

// --- Gestion Sidebar Gauche ---
if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Initialisation
$user_id = $rowu['id'];
$message = '';

// --- CONFIGURATION : Images de la galerie ---
// Créez ces images ou adaptez les noms
$default_avatars = [
    'assets/img/avatars_default/avatar.png',
    'assets/img/avatars_default/avatar2.png',
    'assets/img/avatars_default/avatar3.png',
    'assets/img/avatars_default/avatar4.png',
    'assets/img/avatars_default/avatar5.png',
    'assets/img/avatars_default/avatar6.png',
    'assets/img/avatars_default/avatar7.png',
    'assets/img/avatars_default/avatar8.png',
    'assets/img/avatars_default/avatar9.png',
    'assets/img/avatars_default/avatar10.png',
    'assets/img/avatars_default/avatar11.png',
    'assets/img/avatars_default/avatar12.png',
    'assets/img/avatars_default/avatar13.png',
    'assets/img/avatars_default/avatar14.png',
    'assets/img/avatars_default/avatar15.png',
    'assets/img/avatars_default/avatar16.png',
    'assets/img/avatars_default/avatar17.png'    
];

// 2. Traitement du formulaire
if (isset($_POST['save'])) {
    
    validate_csrf_token(); // Sécurité CSRF

    $email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $username = strip_tags(trim($_POST['username']));
    $avatar   = $rowu['avatar']; // Par défaut, on garde l'ancien
    $password = $_POST['password'];
    
    // Nettoyage Bio
    if (!isset($purifier)) { $purifier = get_purifier(); }
    $bio = $purifier->purify($_POST['bio']);

    // Validation URL et Location
    $website = filter_var($_POST['website'] ?? '', FILTER_VALIDATE_URL) ?: '';
    $location = strip_tags($_POST['location'] ?? '');

    // A. Vérifier doublons
    $emused = false;
    $stmt_email = mysqli_prepare($connect, "SELECT id FROM `users` WHERE email=? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_email, "si", $email, $user_id);
    mysqli_stmt_execute($stmt_email);
    mysqli_stmt_store_result($stmt_email);
    if (mysqli_stmt_num_rows($stmt_email) > 0) { $emused = true; }
    mysqli_stmt_close($stmt_email);
    
    $unused = false;
    $stmt_user = mysqli_prepare($connect, "SELECT id FROM `users` WHERE username=? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_user, "si", $username, $user_id);
    mysqli_stmt_execute($stmt_user);
    mysqli_stmt_store_result($stmt_user);
    if (mysqli_stmt_num_rows($stmt_user) > 0) { $unused = true; }
    mysqli_stmt_close($stmt_user);
    
    // Gestion des erreurs
    if ($emused) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> This E-Mail Address is already used.</div>';
    } elseif ($unused) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> This Username is already used.</div>';
    } elseif (strlen($username) < 3) {
        $message = '<div class="alert alert-danger">Username must be at least 3 characters long.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Please enter a valid E-Mail Address.</div>';
    } else {
        
        // --- GESTION AVATAR (Priorité : Upload > Galerie > Ancien) ---
        
        // 1. Si sélection Galerie
        if (!empty($_POST['avatar_gallery'])) {
            // Sécurité : on vérifie que l'image est bien dans la liste autorisée
            if (in_array($_POST['avatar_gallery'], $default_avatars)) {
                $avatar = $_POST['avatar_gallery'];
            }
        }

        // 2. Si Upload Manuel (Écrase la sélection galerie)
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['name'] != '') {
            $target_dir = "uploads/avatars/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

            $ext = strtolower(pathinfo($_FILES["avatar_file"]["name"], PATHINFO_EXTENSION));
            $new_name = "user_" . $user_id . "_" . uniqid(); 
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if ($_FILES['avatar_file']['size'] > 2000000) { // 2MB
                $message = '<div class="alert alert-danger">File is too large. Max 2MB.</div>';
            } elseif (!in_array($ext, $allowed)) {
                $message = '<div class="alert alert-danger">Invalid format. JPG, PNG, GIF, WEBP only.</div>';
            } else {
                // Optimisation
                if (function_exists('optimize_and_save_image')) {
                    $saved_path = optimize_and_save_image($_FILES["avatar_file"]["tmp_name"], $target_dir . $new_name, 300, 90);
                    
                    if ($saved_path) {
                        // Nettoyage ancien avatar (si fichier local)
                        $old_clean = str_replace('../', '', $rowu['avatar']);
                        if (file_exists($old_clean) && strpos($old_clean, 'assets/') === false) {
                            @unlink($old_clean);
                        }
                        $avatar = $saved_path;
                    } else {
                        $message = '<div class="alert alert-danger">Error processing image.</div>';
                    }
                } else {
                    // Fallback
                    $final_path = $target_dir . $new_name . '.' . $ext;
                    if(move_uploaded_file($_FILES["avatar_file"]["tmp_name"], $final_path)){
                         $avatar = $final_path;
                    }
                }
            }
        }
        
        // D. Mise à jour en Base de Données
        if (empty($message)) {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $message = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_update = mysqli_prepare($connect, "UPDATE `users` SET email=?, username=?, avatar=?, bio=?, website=?, location=?, password=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt_update, "sssssssi", $email, $username, $avatar, $bio, $website, $location, $hashed_password, $user_id);
                }
            } else {
                $stmt_update = mysqli_prepare($connect, "UPDATE `users` SET email=?, username=?, avatar=?, bio=?, website=?, location=? WHERE id=?");
                mysqli_stmt_bind_param($stmt_update, "ssssssi", $email, $username, $avatar, $bio, $website, $location, $user_id);
            }

            if (empty($message)) {
                if (mysqli_stmt_execute($stmt_update)) {
                    $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Your account settings have been updated.</div>';
                    
                    // Recharger les données
                    $stmt_reload = mysqli_prepare($connect, "SELECT * FROM users WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_reload, "i", $user_id);
                    mysqli_stmt_execute($stmt_reload);
                    $result_reload = mysqli_stmt_get_result($stmt_reload);
                    $rowu = mysqli_fetch_assoc($result_reload);
                    mysqli_stmt_close($stmt_reload);
                    
                    $_SESSION['sec-username'] = $rowu['username'];
                    
                } else {
                    $message = '<div class="alert alert-danger">Database Error: ' . mysqli_error($connect) . '</div>';
                }
                mysqli_stmt_close($stmt_update);
            }
        }
    }
}
?>

<div class="col-md-8 mb-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user-cog"></i> Account Settings
        </div>
        <div class="card-body">
            
            <?php echo $message; ?>
            
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <label class="form-label fw-bold">Profile Picture</label>
                        
                        <div class="mb-3 position-relative d-inline-block">
                            <?php 
                            // --- AFFICHAGE AVATAR SÉCURISÉ ---
                            $avatar_src = 'assets/img/avatar.png';
                            if (!empty($rowu['avatar'])) {
                                $clean = str_replace('../', '', $rowu['avatar']);
                                if (strpos($clean, 'http') === 0 || file_exists($clean)) {
                                    $avatar_src = $clean;
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($avatar_src); ?>" id="avatar_preview" class="img-thumbnail rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='assets/img/avatar.png';">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <label class="btn btn-outline-primary btn-sm" for="avatar_file">
                                <i class="fas fa-upload"></i> Upload New
                                <input type="file" name="avatar_file" id="avatar_file" hidden accept="image/*">
                            </label>
                            
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#avatarGalleryModal">
                                <i class="fas fa-images"></i> Choose from Gallery
                            </button>
                            
                            <input type="hidden" name="avatar_gallery" id="avatar_gallery_input">
                        </div>
                        
                        <small class="text-muted d-block mt-2" style="font-size:0.8rem;">JPG, PNG or GIF. Max 2MB.</small>
                    </div>

                    <div class="col-md-8">
                        <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-id-card"></i> Identity</h5>
                        
                        <div class="form-group mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($rowu['username']); ?>" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($rowu['email']); ?>" class="form-control" required>
                            </div>
                        </div>

                        <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-info-circle"></i> Public Info</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($rowu['location'] ?? ''); ?>" class="form-control" placeholder="City, Country">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="url" name="website" id="website" value="<?php echo htmlspecialchars($rowu['website'] ?? ''); ?>" class="form-control" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="bio" class="form-label">Biography</label>
                            <textarea name="bio" id="summernote" class="form-control"><?php echo html_entity_decode($rowu['bio'] ?? ''); ?></textarea>
                        </div>

                        <h5 class="text-danger border-bottom pb-2 mb-3 mt-4"><i class="fas fa-lock"></i> Security</h5>
                        
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Leave empty to keep current password">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" name="save" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="avatarGalleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Choose an Avatar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="row g-3">
              <?php foreach ($default_avatars as $av): ?>
                  <?php if(file_exists($av)): ?>
                      <div class="col-3 text-center">
                          <img src="<?php echo $av; ?>" 
                               class="img-fluid rounded-circle border p-1 avatar-option hover-shadow" 
                               style="cursor: pointer; width: 80px; height: 80px; object-fit: cover;"
                               onclick="selectAvatar('<?php echo $av; ?>')">
                      </div>
                  <?php endif; ?>
              <?php endforeach; ?>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
// 1. Prévisualisation Upload Manuel
document.getElementById('avatar_file').addEventListener('change', function(event) {
    if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar_preview').src = e.target.result;
            document.getElementById('avatar_gallery_input').value = ''; // Reset galerie
        }
        reader.readAsDataURL(event.target.files[0]);
    }
});

// 2. Sélection depuis la galerie (Fonction Globale)
function selectAvatar(path) {
    // Mise à jour de l'aperçu
    document.getElementById('avatar_preview').src = path;
    // Remplissage du champ caché pour le PHP
    document.getElementById('avatar_gallery_input').value = path;
    // Reset de l'input file
    document.getElementById('avatar_file').value = '';
    
    // Fermeture du modal (Bootstrap 5)
    var modalEl = document.getElementById('avatarGalleryModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();
}
</script>

<?php
// --- Gestion Sidebar Droite ---
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>