<?php
include "header.php";

// --- LOGIQUE D'UPLOAD DE L'IMAGE DE FOND ---
$bg_upload_dir = "../uploads/banned_bg/";
$bg_message = "";

// Récupérer l'image actuelle
$q_curr_bg = mysqli_query($connect, "SELECT ban_bg_image FROM settings WHERE id = 1");
$current_bg_file = ($q_curr_bg && mysqli_num_rows($q_curr_bg) > 0) ? mysqli_fetch_assoc($q_curr_bg)['ban_bg_image'] : 'default.jpg';
if (empty($current_bg_file)) { $current_bg_file = 'default.jpg'; }

if (isset($_POST['upload_bg'])) {
    validate_csrf_token();
    
    if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['bg_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $bg_message = '<div class="alert alert-danger m-3">Error: Format not allowed.</div>';
        } else {
            $new_filename = "ban_bg_" . time() . "." . $ext;
            $target = $bg_upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $target)) {
                // Supprimer l'ancienne image si ce n'est pas celle par défaut
                if ($current_bg_file != 'default.jpg' && file_exists($bg_upload_dir . $current_bg_file)) {
                    @unlink($bg_upload_dir . $current_bg_file);
                }

                // Mettre à jour la BDD
                $stmt_bg = mysqli_prepare($connect, "UPDATE settings SET ban_bg_image = ? WHERE id = 1");
                mysqli_stmt_bind_param($stmt_bg, "s", $new_filename);
                mysqli_stmt_execute($stmt_bg);
                mysqli_stmt_close($stmt_bg);
                
                $bg_message = '<div class="alert alert-success m-3">Background updated successfully!</div>';
                $current_bg_file = $new_filename; // Mettre à jour pour l'affichage immédiat
            } else {
                $bg_message = '<div class="alert alert-danger m-3">Error moving file. Check folder permissions.</div>';
            }
        }
    }
}

// --- LOGIQUE DE SUPPRESSION ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    $stmt = mysqli_prepare($connect, "DELETE FROM bans WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=bans.php">';
    exit;
}

// --- LOGIQUE TOGGLE STATUS ---
if (isset($_GET['toggle_active'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_active'];
    $q = mysqli_query($connect, "SELECT active FROM bans WHERE id='$id'");
    $r = mysqli_fetch_assoc($q);
    
    $new_status = ($r['active'] == 'Yes') ? 'No' : 'Yes';
    
    $stmt = mysqli_prepare($connect, "UPDATE bans SET active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=bans.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-ban"></i> Banned Users/IPs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Bans</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $bg_message; ?>
        
        <div class="row">
            <div class="col-lg-9 col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                             <a href="add_ban.php" class="btn btn-danger btn-sm">
                                <i class="fa fa-plus-circle"></i> Add New Ban
                            </a>
                        </h3>
                        <button type="button" class="btn btn-tool text-primary ml-auto" data-toggle="modal" data-target="#helpModal">
                            <i class="fas fa-info-circle fa-lg"></i> README Info
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="dt-basic" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Reason</th>
                                    <th class="text-center">Status</th>
                                    <th>Date</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = mysqli_query($connect, "SELECT * FROM bans ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    
                                    $type_badge = 'badge-secondary';
                                    if($row['ban_type'] == 'ip') $type_badge = 'badge-danger';
                                    if($row['ban_type'] == 'email') $type_badge = 'badge-warning';
                                    if($row['ban_type'] == 'username') $type_badge = 'badge-info';

                                    $status_class = ($row['active'] == 'Yes') ? 'badge-success' : 'badge-secondary';
                                    $toggle_icon  = ($row['active'] == 'Yes') ? 'fa-toggle-on' : 'fa-toggle-off';
                                    $toggle_color = ($row['active'] == 'Yes') ? 'btn-success' : 'btn-default';
                                    $toggle_text  = ($row['active'] == 'Yes') ? 'Deactivate' : 'Activate';

                                    echo '
                                    <tr>
                                        <td><span class="badge ' . $type_badge . '">' . strtoupper($row['ban_type']) . '</span></td>
                                        <td><code>' . htmlspecialchars($row['ban_value']) . '</code></td>
                                        <td>' . htmlspecialchars($row['reason']) . '</td>
                                        <td class="text-center"><span class="badge ' . $status_class . '">' . $row['active'] . '</span></td>
                                        <td>' . date('d M Y', strtotime($row['created_at'])) . '</td>
                                        <td class="text-center">
                                            <a href="?toggle_active=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm ' . $toggle_color . ' mr-1" title="' . $toggle_text . '">
                                                <i class="fas ' . $toggle_icon . '"></i>
                                            </a>
                                            <a href="edit_ban.php?id=' . $row['id'] . '" class="btn btn-sm btn-info mr-1" title="Edit Ban">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Permanently delete this ban rule?\');" title="Delete Ban">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image"></i> Banned Page BG</h3>
                    </div>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="card-body text-center">
                            <p class="text-muted">Current Background:</p>
                            <a href="<?php echo $bg_upload_dir . $current_bg_file; ?>" data-toggle="lightbox" data-title="Current Background">
                                <img src="<?php echo $bg_upload_dir . $current_bg_file; ?>" class="img-fluid rounded shadow-sm mb-3" style="max-height: 150px;">
                            </a>
                            
                            <div class="form-group text-left">
                                <label>Change Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="bgFile" name="bg_image" required>
                                    <label class="custom-file-label" for="bgFile">Choose file</label>
                                </div>
                                <small class="text-muted">JPG, PNG, GIF only.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="upload_bg" class="btn btn-secondary btn-block">Update Background</button>
                        </div>
                    </form>
                </div>

                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Quick Info</h3>
                    </div>
                    <div class="card-body small">
                        <p>Banned users will see the "Banned Page" instead of the site content.</p>
                        <p>You can ban by:</p>
                        <ul>
                            <li><strong><span class="badge badge-danger">IP</span> Address:</li>
                            <li><strong><span class="badge badge-warning">Email</span>:</li>
                            <li><strong><span class="badge badge-info">Username</span>:</li>
                            <li><strong><span class="badge badge-secondary">User Agent</span>:</strong> (e.g. <code>MJ12bot</code>, <code>AhrefsBot</code>).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</section>

<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-shield-alt"></i> How Ban System Works?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <h5>1. Ban Types</h5>
          <p>You can block access to your site using 4 methods:</p>
          <ul>
              <li><strong><span class="badge badge-danger">IP</span> Address:</strong> The most effective way to block a visitor or a spammer. Blocks the connection from a specific network.</li>
              <li><strong><span class="badge badge-warning">Email</span>:</strong> Blocks any user trying to login with this email address.</li>
              <li><strong><span class="badge badge-info">Username</span>:</strong> Blocks a specific account username.</li>
              <li><strong><span class="badge badge-secondary">User Agent</span>:</strong> Advanced. Blocks specific bots or browsers (e.g. <code>MJ12bot</code>, <code>AhrefsBot</code>). Use partial matching.</li>
          </ul>

          <hr>

          <h5>2. What happens when banned?</h5>
          <p>When a banned user tries to access ANY page of your site, they are immediately redirected to a special <strong>"Access Denied"</strong> page.</p>
          <p>This page displays the reason for the ban (if provided) and shows the custom background image you can set on the right.</p>

          <hr>

          <h5>3. Temporary vs Permanent</h5>
          <p>You can <strong>Deactivate</strong> a ban rule without deleting it using the toggle button <i class="fas fa-toggle-on text-success"></i>. This is useful for temporary bans.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('#dt-basic').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 4, "desc" ]] // Tri par date
    });
    
    // Afficher nom fichier upload
    $('#bgFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>