<?php
include "header.php";

// --- LOGIQUE : CONSERVATION DU STATUT ---
$status_url_param = ''; 
$status_url_query = ''; 
$current_status = $_GET['status'] ?? 'all'; 

$status_sql = ""; 
if ($current_status == 'published') {
    $status_sql = " WHERE active='Yes'";
} elseif ($current_status == 'draft') {
    $status_sql = " WHERE active='No'";
}

if ($current_status != 'all') {
    $status_param = htmlspecialchars($current_status);
    $status_url_param = '&status=' . $status_param;
    $status_url_query = '?status=' . $status_param;
}

// --- ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $widget_ids = $_POST['widget_ids'] ?? [];

    if (!empty($action) && !empty($widget_ids)) {
        $placeholders = implode(',', array_fill(0, count($widget_ids), '?'));
        $types = str_repeat('i', count($widget_ids));

        if ($action == 'publish') {
            $stmt = mysqli_prepare($connect, "UPDATE widgets SET active = 'Yes' WHERE id IN ($placeholders)");
        } elseif ($action == 'draft') {
            $stmt = mysqli_prepare($connect, "UPDATE widgets SET active = 'No' WHERE id IN ($placeholders)");
        } elseif ($action == 'delete') {
            $stmt = mysqli_prepare($connect, "DELETE FROM widgets WHERE id IN ($placeholders)");
        }

        if (isset($stmt)) {
            mysqli_stmt_bind_param($stmt, $types, ...$widget_ids);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        echo '<meta http-equiv="refresh" content="0; url=widgets.php' . $status_url_query . '">';
        exit;
    }
}

// --- LOGIQUE MOVE UP (ECHANGE D'ID - VOTRE CODE ORIGINAL) ---
if (isset($_GET['up-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["up-id"];

    $stmt = mysqli_prepare($connect, "SELECT id FROM `widgets` WHERE id < ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowpe = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($rowpe) {
        $prev_id = $rowpe['id'];
        $temp_id = 9999999; // ID temporaire pour l'échange

        mysqli_begin_transaction($connect);
        try {
            $stmt1 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt1, "ii", $temp_id, $prev_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);

            $stmt2 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "ii", $prev_id, $id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $stmt3 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt3, "ii", $id, $temp_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);

            mysqli_commit($connect);
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($connect);
        }
    }
    echo '<meta http-equiv="refresh" content="0; url=widgets.php' . $status_url_query . '">';
    exit;
}

// --- LOGIQUE MOVE DOWN (ECHANGE D'ID - VOTRE CODE ORIGINAL) ---
if (isset($_GET['down-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["down-id"];

    $stmt = mysqli_prepare($connect, "SELECT id FROM `widgets` WHERE id > ? ORDER BY id ASC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowne = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($rowne) {
        $next_id = $rowne['id'];
        $temp_id = 9999998; 

        mysqli_begin_transaction($connect);
        try {
            $stmt1 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt1, "ii", $temp_id, $next_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);

            $stmt2 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "ii", $next_id, $id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $stmt3 = mysqli_prepare($connect, "UPDATE widgets SET id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt3, "ii", $id, $temp_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);

            mysqli_commit($connect);
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($connect);
        }
    }
    echo '<meta http-equiv="refresh" content="0; url=widgets.php' . $status_url_query . '">';
    exit;
}

// --- SUPPRESSION ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];
    
    $stmt = mysqli_prepare($connect, "DELETE FROM `widgets` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=widgets.php' . $status_url_query . '">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-archive"></i> Widgets Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Widgets</li>
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
                    <a href="add_widget.php<?php echo $status_url_query; ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Widget
                    </a>
                </h3>
                <div class="card-tools">
                    <!--<div class="btn-group">-->
                        <a href="widgets.php" class="btn btn-default btn-sm <?php echo ($current_status == 'all') ? 'active' : ''; ?>">All</a>
                        <a href="widgets.php?status=published" class="btn btn-default text-success btn-sm <?php echo ($current_status == 'published') ? 'active' : ''; ?>">Published</a>
                        <a href="widgets.php?status=draft" class="btn btn-default text-warning btn-sm <?php echo ($current_status == 'draft') ? 'active' : ''; ?>">Drafts</a>
                    <!--</div>-->
                </div>
            </div>
            
            <div class="card-body">
                <form action="widgets.php<?php echo $status_url_query; ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <table id="dt-widgets" class="table table-bordered table-hover table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width: 10px;"><input type="checkbox" id="select-all"></th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
<?php
// Requête filtrée
$query_sql = "SELECT * FROM widgets" . $status_sql . " ORDER BY id ASC";
$query = mysqli_query($connect, $query_sql);
$widgets = [];
while($row = mysqli_fetch_assoc($query)) { $widgets[] = $row; }

// Dernier ID pour la logique des flèches
$query_last = mysqli_query($connect, "SELECT id FROM widgets" . $status_sql . " ORDER BY id DESC LIMIT 1");
$row_last = mysqli_fetch_assoc($query_last);
$last_id = $row_last ? $row_last['id'] : null;

$first = true;

foreach ($widgets as $row) {
    $status_badge = ($row['active'] == 'Yes') 
        ? '<span class="badge bg-success">Published</span>' 
        : '<span class="badge bg-warning">Draft</span>';
    
    // Icones par type
    $type_icon = 'fa-puzzle-piece';
    $type_label = $row['widget_type'];
    if($row['widget_type'] == 'html') { $type_icon = 'fa-code'; $type_label = 'HTML'; }
    if($row['widget_type'] == 'latest_posts') { $type_icon = 'fa-list'; $type_label = 'Posts'; }
    if($row['widget_type'] == 'search') { $type_icon = 'fa-search'; $type_label = 'Search'; }
    if($row['widget_type'] == 'quiz_leaderboard') { $type_icon = 'fa-trophy'; $type_label = 'Quiz Top'; }
    if($row['widget_type'] == 'faq_leaderboard') { $type_icon = 'fa-question-circle'; $type_label = 'FAQ Top'; }
    if($row['widget_type'] == 'testimonials') { $type_icon = 'fa-comments'; $type_label = 'Testimonials'; }
    if($row['widget_type'] == 'newsletter') { $type_icon = 'fa-envelope'; $type_label = 'Newsletter'; }
    if($row['widget_type'] == 'online_users') { $type_icon = 'fa-users'; $type_label = 'Online Users'; }
    if($row['widget_type'] == 'latest_projects') { $type_icon = 'fa-microchip'; $type_label = 'Projects'; }
    if($row['widget_type'] == 'shop') { $type_icon = 'fa-shopping-cart'; $type_label = 'Shop'; }

    echo '
        <tr>
            <td><input type="checkbox" name="widget_ids[]" value="' . $row['id'] . '"></td>
            <td>' . htmlspecialchars($row['title']) . '</td>
            <td><i class="fas '.$type_icon.' text-muted mr-1"></i> ' . htmlspecialchars($type_label) . '</td>
            <td>' . htmlspecialchars($row['position']) . '</td>
            <td>' . $status_badge . '</td>
            <td>';
            
    // Flèches de déplacement
    if (!$first) {
        echo '<a href="?up-id=' . $row['id'] . '&token=' . $csrf_token . $status_url_param . '" class="btn btn-secondary btn-sm mr-1"><i class="fa fa-arrow-up"></i></a>';
    } else {
        echo '<a href="#" class="btn btn-secondary btn-sm disabled mr-1"><i class="fa fa-arrow-up"></i></a>';
    }

    if ($row['id'] != $last_id) {
        echo '<a href="?down-id=' . $row['id'] . '&token=' . $csrf_token . $status_url_param . '" class="btn btn-secondary btn-sm mr-1"><i class="fa fa-arrow-down"></i></a>';
    } else {
         echo '<a href="#" class="btn btn-secondary btn-sm disabled mr-1"><i class="fa fa-arrow-down"></i></a>';
    }

    echo '<a href="edit_widget.php?id=' . $row['id'] . $status_url_param . '" class="btn btn-primary btn-sm mr-1"><i class="fa fa-edit"></i></a>';
    echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . $status_url_param . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this widget?\');"><i class="fa fa-trash"></i></a>';
    
    echo '</td>
        </tr>';
    $first = false;
}
?>
                        </tbody>
                    </table>

                    <div class="card-footer clearfix">
                         <div class="float-left">
                            <select name="bulk_action" class="form-control custom-select" style="width: 200px; display: inline-block;">
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="draft">Draft</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" name="apply_bulk_action" class="btn btn-primary">Apply</button>
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
    // On désactive le tri auto de DataTables pour respecter l'ordre ID
    $('#dt-widgets').DataTable({
        "responsive": true, "autoWidth": false, "ordering": false, "lengthChange": false
    });
    $('#select-all').on('click', function(){
        var rows = $(this).closest('table').find('tbody tr');
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>