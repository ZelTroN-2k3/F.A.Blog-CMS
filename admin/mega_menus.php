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
    $ids = $_POST['menu_ids'] ?? [];

    if (!empty($action) && !empty($ids)) {
        $ids_clean = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
        $types = str_repeat('i', count($ids_clean));

        if ($action == 'activate') {
            $stmt = mysqli_prepare($connect, "UPDATE mega_menus SET active = 'Yes' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'deactivate') {
            $stmt = mysqli_prepare($connect, "UPDATE mega_menus SET active = 'No' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'delete') {
            $stmt = mysqli_prepare($connect, "DELETE FROM mega_menus WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        echo '<meta http-equiv="refresh" content="0; url=mega_menus.php?status=' . $current_status . '">';
        exit;
    }
}

// --- LOGIQUE : SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    $stmt = mysqli_prepare($connect, "DELETE FROM mega_menus WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=mega_menus.php' . str_replace('&', '?', $status_url) . '">';
    exit;
}

// --- LOGIQUE : TOGGLE STATUS ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    $stmt = mysqli_prepare($connect, "UPDATE mega_menus SET active = IF(active='Yes', 'No', 'Yes') WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=mega_menus.php' . str_replace('&', '?', $status_url) . '">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-columns"></i> Mega Menus Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Mega Menus</li>
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
                    <a href="add_mega_menu.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Create New Mega Menu
                    </a>
                </h3>
                
                <div class="card-tools">
                    <!--<div class="btn-group">-->
                        <a href="mega_menus.php" class="btn btn-sm <?php echo ($current_status == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                        <a href="mega_menus.php?status=active" class="btn btn-sm <?php echo ($current_status == 'active') ? 'btn-success' : 'btn-default text-success'; ?>">Active</a>
                        <a href="mega_menus.php?status=inactive" class="btn btn-sm <?php echo ($current_status == 'inactive') ? 'btn-warning' : 'btn-default text-warning'; ?>">Inactive</a>
                    <!--</div>-->
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <table id="dt-menus" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width: 50px;" class="text-center">Order</th>
                                <th>Name (Internal)</th>
                                <th>Menu Label</th>
                                <th class="text-center">Icon</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Construction de la requête avec Filtre
                        $where_clause = "";
                        if ($current_status == 'active') { $where_clause = "WHERE active='Yes'"; }
                        if ($current_status == 'inactive') { $where_clause = "WHERE active='No'"; }

                        $query = "SELECT * FROM mega_menus $where_clause ORDER BY position_order ASC";
                        $res = mysqli_query($connect, $query);

                        while ($m = mysqli_fetch_assoc($res)) {
                            
                            // --- STATUS & ACTIONS ---
                            if ($m['active'] == 'Yes') {
                                $status_badge = '<span class="badge badge-success">Active</span>';
                                $toggle_class = 'btn-warning';
                                $toggle_icon = 'fa-eye-slash';
                            } else {
                                $status_badge = '<span class="badge badge-warning">Inactive</span>';
                                $toggle_class = 'btn-success';
                                $toggle_icon = 'fa-eye';
                            }

                            $token_str = '&token=' . ($_SESSION['csrf_token'] ?? '');

                            echo '
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="menu_ids[]" value="' . $m['id'] . '">
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-light border text-dark">' . $m['position_order'] . '</span>
                                    </td>
                                    <td class="align-middle">
                                        <b>' . htmlspecialchars($m['name']) . '</b><br>
                                        <small class="text-muted">Link: ' . htmlspecialchars($m['trigger_link']) . '</small>
                                    </td>
                                    <td class="align-middle">' . htmlspecialchars($m['trigger_text']) . '</td>
                                    <td class="text-center align-middle"><i class="fa ' . htmlspecialchars($m['trigger_icon']) . ' fa-lg"></i></td>
                                    <td class="text-center align-middle">' . $status_badge . '</td>
                                    
                                    <td class="text-center align-middle">
                                        <a href="edit_mega_menu.php?id=' . $m['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        
                                        <a href="?toggle_id=' . $m['id'] . $token_str . $status_url . '" class="btn ' . $toggle_class . ' btn-sm mr-1" title="Toggle"><i class="fas ' . $toggle_icon . '"></i></a>
                                        
                                        <a href="?delete_id=' . $m['id'] . $token_str . $status_url . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this menu? This cannot be undone.\');" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
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
                                <option value="delete">Delete</option>
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
    var table = $('#dt-menus').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 1, "asc" ]], // Tri par Ordre (Colonne 1)
        "columnDefs": [
            { "orderable": false, "targets": [0, 4, 6] } // Pas de tri sur Checkbox, Icone, Actions
        ]
    });

    // Select All
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>