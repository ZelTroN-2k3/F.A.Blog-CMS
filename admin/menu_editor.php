<?php
include "header.php";

// --- FONCTION : ECHANGER DEUX ELEMENTS DU MENU ---
function swap_menu_data($connect, $id1, $id2) {
    // 1. Récupérer les données
    $q1 = mysqli_query($connect, "SELECT page, path, fa_icon, active FROM menu WHERE id='$id1'");
    $q2 = mysqli_query($connect, "SELECT page, path, fa_icon, active FROM menu WHERE id='$id2'");
    
    if (mysqli_num_rows($q1) > 0 && mysqli_num_rows($q2) > 0) {
        $row1 = mysqli_fetch_assoc($q1);
        $row2 = mysqli_fetch_assoc($q2);

        // 2. Update ID 1 avec données du 2
        $stmt1 = mysqli_prepare($connect, "UPDATE menu SET page=?, path=?, fa_icon=?, active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt1, "ssssi", $row2['page'], $row2['path'], $row2['fa_icon'], $row2['active'], $id1);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // 3. Update ID 2 avec données du 1
        $stmt2 = mysqli_prepare($connect, "UPDATE menu SET page=?, path=?, fa_icon=?, active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "ssssi", $row1['page'], $row1['path'], $row1['fa_icon'], $row1['active'], $id2);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }
}

// --- LOGIQUE : MOVE UP ---
if (isset($_GET['up-id'])) {
    validate_csrf_token_get();
    $curr_id = (int)$_GET['up-id'];
    // Trouver l'ID précédent
    $q_prev = mysqli_query($connect, "SELECT id FROM menu WHERE id < $curr_id ORDER BY id DESC LIMIT 1");
    if ($row_prev = mysqli_fetch_assoc($q_prev)) {
        swap_menu_data($connect, $curr_id, $row_prev['id']);
    }
    echo '<meta http-equiv="refresh" content="0; url=menu_editor.php">';
    exit;
}

// --- LOGIQUE : MOVE DOWN ---
if (isset($_GET['down-id'])) {
    validate_csrf_token_get();
    $curr_id = (int)$_GET['down-id'];
    // Trouver l'ID suivant
    $q_next = mysqli_query($connect, "SELECT id FROM menu WHERE id > $curr_id ORDER BY id ASC LIMIT 1");
    if ($row_next = mysqli_fetch_assoc($q_next)) {
        swap_menu_data($connect, $curr_id, $row_next['id']);
    }
    echo '<meta http-equiv="refresh" content="0; url=menu_editor.php">';
    exit;
}

// --- LOGIQUE : ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $menu_ids = $_POST['menu_ids'] ?? [];

    if (!empty($action) && !empty($menu_ids)) {
        $placeholders = implode(',', array_fill(0, count($menu_ids), '?'));
        $types = str_repeat('i', count($menu_ids));
        $stmt = null;

        if ($action == 'publish') {
            $stmt = mysqli_prepare($connect, "UPDATE menu SET active = 'Yes' WHERE id IN ($placeholders)");
        } elseif ($action == 'draft') {
            $stmt = mysqli_prepare($connect, "UPDATE menu SET active = 'No' WHERE id IN ($placeholders)");
        } elseif ($action == 'delete') {
            $stmt = mysqli_prepare($connect, "DELETE FROM menu WHERE id IN ($placeholders)");
        }

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$menu_ids);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        echo '<meta http-equiv="refresh" content="0; url=menu_editor.php">';
        exit;
    }
}

// --- LOGIQUE : SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];
    $stmt = mysqli_prepare($connect, "DELETE FROM `menu` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=menu_editor.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-bars"></i> Menu Editor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Menu</li>
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
                            <a href="add_menu.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Menu Item
                            </a>
                        </h3>
                        
                        <div class="card-tools">
                            <!--<div class="btn-group">-->
                                <a href="menu_editor.php" class="btn btn-sm btn-default">All</a>
                                <a href="menu_editor.php?status=published" class="btn btn-sm btn-default text-success">Published</a>
                                <a href="menu_editor.php?status=draft" class="btn btn-sm btn-default text-warning">Drafts</a>
                            <!--</div>-->
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <table id="dt-menu" class="table table-bordered table-hover table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 10px;" class="text-center">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th style="width: 35px;" class="text-center">Order</th>
                                        <th style="width: 25px;" class="text-center">Icon</th>
                                        <th>Label</th>
                                        <th>Path / URL</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $where = "";
                                $status_url = "";
                                if (isset($_GET['status'])) {
                                    if ($_GET['status'] == 'published') { $where = "WHERE active='Yes'"; $status_url="&status=published"; }
                                    if ($_GET['status'] == 'draft') { $where = "WHERE active='No'"; $status_url="&status=draft"; }
                                }

                                // Trouver le dernier ID pour masquer la flèche "Bas"
                                $q_last = mysqli_query($connect, "SELECT id FROM menu $where ORDER BY id DESC LIMIT 1");
                                $last_id = ($row_last = mysqli_fetch_assoc($q_last)) ? $row_last['id'] : 0;

                                $sql = mysqli_query($connect, "SELECT * FROM `menu` $where ORDER BY id ASC");
                                $first = true;

                                while ($row = mysqli_fetch_assoc($sql)) {
                                    $status_badge = ($row['active'] == 'Yes') 
                                        ? '<span class="badge badge-success">Published</span>' 
                                        : '<span class="badge badge-warning">Draft</span>';
                                        
                                    echo '
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="menu_ids[]" value="' . $row['id'] . '">
                                        </td>
                                        <td class="text-center"><span class="badge badge-light border">' . $row['id'] . '</span></td>
                                        <td class="text-center"><i class="' . htmlspecialchars($row['fa_icon']) . ' fa-lg text-secondary"></i></td>
                                        <td><strong>' . htmlspecialchars($row['page']) . '</strong></td>
                                        <td><code>' . htmlspecialchars($row['path']) . '</code></td>
                                        <td class="text-center">' . $status_badge . '</td>
                                        <td class="text-center">
                                            ';

                                    // Bouton UP
                                    if ($first == false) {
                                        echo '<a href="?up-id=' . $row['id'] . '&token=' . $csrf_token . $status_url . '" class="btn btn-secondary btn-sm mr-1" title="Move Up"><i class="fas fa-arrow-up"></i></a>';
                                    } else {
                                        echo '<button type="button" class="btn btn-secondary btn-sm disabled mr-1"><i class="fas fa-arrow-up"></i></button>';
                                    }

                                    // Bouton DOWN
                                    if ($row['id'] != $last_id) {
                                        echo '<a href="?down-id=' . $row['id'] . '&token=' . $csrf_token . $status_url . '" class="btn btn-secondary btn-sm mr-1" title="Move Down"><i class="fas fa-arrow-down"></i></a>';
                                    } else {
                                        echo '<button type="button" class="btn btn-secondary btn-sm disabled mr-1"><i class="fas fa-arrow-down"></i></button>';
                                    }

                                    // Boutons Edit et Delete
                                    echo '
                                            <a href="edit_menu.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this menu item?\');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>';
                                    
                                    $first = false;
                                }
                                ?>
                                </tbody>
                            </table>

                            <div class="mt-3 p-2 bg-light border rounded">
                                <div class="d-inline-flex align-items-center">
                                    <span class="mr-2">With Selected:</span>
                                    <select name="bulk_action" class="form-control form-control-sm mr-2" style="width: auto;">
                                        <option value="">-- Choose --</option>
                                        <option value="publish">Set Published</option>
                                        <option value="draft">Set Draft</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" name="apply_bulk_action" class="btn btn-primary btn-sm">Apply</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    // IMPORTANT : Désactiver le tri JS pour respecter l'ordre SQL
    $('#dt-menu').DataTable({
        "responsive": true,
        "autoWidth": false,
        "ordering": false, 
        "lengthChange": false
    });

    $('#select-all').on('click', function(){
        var rows = $(this).closest('table').find('tbody tr');
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>