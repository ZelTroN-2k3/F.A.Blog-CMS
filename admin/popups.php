<?php
include "header.php";

// --- GESTION STATUT URL (Pour garder le filtre après une action) ---
$status_url = '';
$current_status = $_GET['status'] ?? 'all';
if ($current_status != 'all') {
    $status_url = '&status=' . htmlspecialchars($current_status);
}

// --- LOGIQUE : ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $ids = $_POST['popup_ids'] ?? [];

    if (!empty($action) && !empty($ids)) {
        $ids_clean = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
        $types = str_repeat('i', count($ids_clean));

        if ($action == 'activate') {
            $stmt = mysqli_prepare($connect, "UPDATE popups SET active = 'Yes' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'deactivate') {
            $stmt = mysqli_prepare($connect, "UPDATE popups SET active = 'No' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'delete') {
            // Seul l'admin peut supprimer en masse pour éviter les accidents
            if ($user['role'] == 'Admin') {
                $stmt = mysqli_prepare($connect, "DELETE FROM popups WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        echo '<meta http-equiv="refresh" content="0; url=popups.php?status=' . $current_status . '">';
        exit;
    }
}

// --- LOGIQUE : SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    $check = mysqli_query($connect, "SELECT author_id FROM popups WHERE id='$id'");
    $row = mysqli_fetch_assoc($check);
    
    if ($row && ($user['role'] == 'Admin' || $row['author_id'] == $user['id'])) {
        $stmt = mysqli_prepare($connect, "DELETE FROM popups WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<meta http-equiv="refresh" content="0; url=popups.php' . str_replace('&', '?', $status_url) . '">';
        exit;
    } else { 
        echo '<div class="alert alert-danger m-3">Access Denied.</div>'; 
    }
}

// --- LOGIQUE : TOGGLE STATUS ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    $check = mysqli_query($connect, "SELECT author_id FROM popups WHERE id='$id'");
    $row = mysqli_fetch_assoc($check);
    
    if ($row && ($user['role'] == 'Admin' || $row['author_id'] == $user['id'])) {
        mysqli_query($connect, "UPDATE popups SET active = IF(active='Yes', 'No', 'Yes') WHERE id='$id'");
        echo '<meta http-equiv="refresh" content="0; url=popups.php' . str_replace('&', '?', $status_url) . '">';
        exit;
    } else { 
        echo '<div class="alert alert-danger m-3">Access Denied.</div>'; 
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-window-restore"></i> Popups Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Popups</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <a href="add_popup.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Create New Popup
                    </a>
                </h3>
                
                <div class="card-tools">
                    <!--<div class="btn-group">-->
                        <a href="popups.php" class="btn btn-sm <?php echo ($current_status == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                        <a href="popups.php?status=active" class="btn btn-sm <?php echo ($current_status == 'active') ? 'btn-success' : 'btn-default text-success'; ?>">Active</a>
                        <a href="popups.php?status=inactive" class="btn btn-sm <?php echo ($current_status == 'inactive') ? 'btn-warning' : 'btn-default text-warning'; ?>">Inactive</a>
                    <!--</div>-->
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <table id="dt-popups" class="table table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width: 80px;" class="text-center">Preview</th>
                                <th>Title & Settings</th>
                                <th style="min-width: 180px;">Author</th> 
                                <th class="text-center">Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
// Construction de la requête avec Filtre
$where_clause = "";
if ($current_status == 'active') { $where_clause = "WHERE p.active='Yes'"; }
if ($current_status == 'inactive') { $where_clause = "WHERE p.active='No'"; }

$query = "
    SELECT p.*, u.username, u.role, u.avatar 
    FROM popups p 
    LEFT JOIN users u ON p.author_id = u.id 
    $where_clause
    ORDER BY p.id DESC
";
$res = mysqli_query($connect, $query);

while ($row = mysqli_fetch_assoc($res)) {
    
    // --- 1. PREVIEW IMAGE ROBUSTE ---
    $preview = '<div class="bg-light d-flex justify-content-center align-items-center text-muted" style="width:80px; height:50px; border:1px solid #ddd; border-radius:4px;"><i class="fas fa-window-maximize"></i></div>';
    
    if (!empty($row['background_image'])) {
        $clean_img = str_replace('../', '', $row['background_image']);
        // On tente d'afficher
        $preview = '<img src="../' . htmlspecialchars($clean_img) . '" 
                         style="width:80px; height:50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                         onerror="this.onerror=null; this.parentNode.innerHTML=\'<i class=\\\'fas fa-image text-muted\\\'></i>\';">';
    }

    // --- 2. LOGIQUE AUTEUR ---
    $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
    $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
    
    // Nettoyage Avatar
    $clean_avatar = str_replace('../', '', $author_avatar);
    if (strpos($clean_avatar, 'http') !== 0) { $clean_avatar = '../' . $clean_avatar; }

    // Badges Rôle
    $role_badge = '';
    if (isset($row['role'])) {
        if ($row['role'] == 'Admin') $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
        elseif ($row['role'] == 'Editor') $role_badge = '<small class="badge badge-info" style="font-size: 0.7em;">Editor</small>';
        else $role_badge = '<small class="badge badge-secondary" style="font-size: 0.7em;">User</small>';
    }

    // --- 3. STATUS & ACTIONS ---
    if ($row['active'] == 'Yes') {
        $status_badge = '<span class="badge badge-success">Active</span>';
        $toggle_class = 'btn-warning';
        $toggle_icon = 'fa-eye-slash';
    } else {
        $status_badge = '<span class="badge badge-secondary">Draft</span>';
        $toggle_class = 'btn-success';
        $toggle_icon = 'fa-eye';
    }

    $is_mine = ($row['author_id'] == $user['id']);
    $is_admin = ($user['role'] == 'Admin');
    $can_edit = ($is_admin || $is_mine);

    echo '
        <tr>
            <td class="text-center align-middle">
                <input type="checkbox" name="popup_ids[]" value="' . $row['id'] . '">
            </td>
            <td class="text-center align-middle">' . $preview . '</td>
            <td class="align-middle">
                <strong>' . htmlspecialchars($row['title']) . '</strong><br>
                <small class="text-muted"><i class="fas fa-clock"></i> Delay: ' . $row['delay_seconds'] . 's</small>
            </td>
            
            <td class="align-middle">
                <div class="user-block">
                    <img src="' . htmlspecialchars($clean_avatar) . '" class="img-circle elevation-1" style="width:35px; height:35px; object-fit:cover; float:left; margin-right:10px;" onerror="this.src=\'../assets/img/avatar.png\';">
                    <span class="username" style="font-size:14px;">' . $author_name . '</span>
                    <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                </div>
            </td>
            
            <td class="text-center align-middle"><span class="badge badge-info">' . htmlspecialchars($row['popup_type']) . '</span></td>
            <td class="text-center align-middle">' . $status_badge . '</td>
            
            <td class="text-center align-middle">';
            
            if ($can_edit) {
                $token_str = '&token=' . ($_SESSION['csrf_token'] ?? '');
                
                echo '<a href="edit_popup.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                echo '<a href="?toggle_id=' . $row['id'] . $token_str . $status_url . '" class="btn ' . $toggle_class . ' btn-sm mr-1"><i class="fas ' . $toggle_icon . '"></i></a>';
                echo '<a href="?delete_id=' . $row['id'] . $token_str . $status_url . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this popup?\');" title="Delete"><i class="fas fa-trash"></i></a>';
            } else {
                echo '<small class="text-muted"><i class="fas fa-lock"></i></small>';
            }
                
    echo '  </td>
        </tr>';
}
?>
                        </tbody>
                    </table>

                    <div class="mt-3 p-2 bg-light border rounded">
                        <div class="d-inline-flex align-items-center">
                            <span class="mr-2">With Selected:</span>
                            <select name="bulk_action" class="form-control form-control-sm mr-2" style="width: auto;">
                                <option value="">-- Choose Action --</option>
                                <option value="activate">Set as Active</option>
                                <option value="deactivate">Set as Inactive</option>
                                <?php if ($user['role'] == 'Admin'): ?>
                                    <option value="delete">Delete</option>
                                <?php endif; ?>
                            </select>
                            <button type="submit" name="apply_bulk_action" class="btn btn-primary btn-sm">Apply</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    var table = $('#dt-popups').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 2, "asc" ]], // Tri par Titre (Colonne 2)
        "columnDefs": [
            { "orderable": false, "targets": [0, 1, 6] } // Pas de tri sur Checkbox, Image, Actions
        ]
    });

    // Select All
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>