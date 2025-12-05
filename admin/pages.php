<?php
include "header.php";

// --- LOGIQUE ACTIONS EN MASSE (SÉCURISÉE) ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $page_ids = $_POST['page_ids'] ?? [];

    if (!empty($action) && !empty($page_ids)) {
        
        // SÉCURITÉ : Restriction des suppressions en masse
        if ($action == 'delete' && $user['role'] != 'Admin') {
            echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete pages.</div>';
        } else {
            $ids_clean = array_map('intval', $page_ids);
            $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
            $types = str_repeat('i', count($ids_clean));

            if ($action == 'publish') {
                $stmt = mysqli_prepare($connect, "UPDATE pages SET active = 'Yes' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'draft') {
                $stmt = mysqli_prepare($connect, "UPDATE pages SET active = 'No' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'delete') {
                $stmt_menu = mysqli_prepare($connect, "DELETE FROM menu WHERE path LIKE 'page?name=%' AND parent_id IN (SELECT id FROM pages WHERE id IN ($placeholders))"); 
                mysqli_stmt_execute($stmt_menu); 
                
                $stmt = mysqli_prepare($connect, "DELETE FROM pages WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            echo '<meta http-equiv="refresh" content="0; url=pages.php">';
            exit;
        }
    }
}

// --- LOGIQUE SUPPRESSION INDIVIDUELLE (SÉCURISÉE) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];
    
    $check = mysqli_query($connect, "SELECT author_id, slug FROM pages WHERE id='$id'");
    $page_data = mysqli_fetch_assoc($check);

    if ($page_data) {
        if ($user['role'] == 'Admin' || $page_data['author_id'] == $user['id']) {
            $stmt_menu = mysqli_prepare($connect, "DELETE FROM menu WHERE path = ?");
            $menu_path = "page?name=" . $page_data['slug'];
            mysqli_stmt_bind_param($stmt_menu, "s", $menu_path);
            mysqli_stmt_execute($stmt_menu);
            mysqli_stmt_close($stmt_menu);

            $stmt = mysqli_prepare($connect, "DELETE FROM `pages` WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo '<div class="alert alert-danger m-3">Access Denied. You can only delete your own pages.</div>';
            echo '<meta http-equiv="refresh" content="2; url=pages.php">';
            exit;
        }
    }
    echo '<meta http-equiv="refresh" content="0; url=pages.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-alt"></i> Pages</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Pages</li>
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
                            <a href="add_page.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Page
                            </a>
                        </h3>

                        <div class="card-tools">
                            <!--<div class="btn-group">-->
                                <?php
                                // Gestion de l'état actif des boutons
                                $st = $_GET['status'] ?? 'all';
                                ?>
                                <a href="pages.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                                <a href="pages.php?status=published" class="btn btn-sm <?php echo ($st == 'published') ? 'btn-success' : 'btn-default text-success'; ?>">Published</a>
                                <a href="pages.php?status=draft" class="btn btn-sm <?php echo ($st == 'draft') ? 'btn-warning' : 'btn-default text-warning'; ?>">Drafts</a>
                            <!--</div>-->
                        </div>
                        </div>
                    
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <table class="table table-bordered table-hover" id="dt-pages" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 10px;" class="text-center">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th>Title</th>
                                        <th style="min-width: 180px;">Author</th> <th>Slug (URL)</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
// --- LOGIQUE DE FILTRE SQL ---
$where_clause = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'published') {
        $where_clause = "WHERE p.active = 'Yes'";
    } elseif ($_GET['status'] == 'draft') {
        $where_clause = "WHERE p.active = 'No'";
    }
}

$query = "
    SELECT p.*, u.username, u.role, u.avatar 
    FROM pages p 
    LEFT JOIN users u ON p.author_id = u.id 
    $where_clause 
    ORDER BY p.id DESC
";
$sql = mysqli_query($connect, $query);

while ($row = mysqli_fetch_assoc($sql)) {
    
    $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
    $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';

    $role_badge = '';
    if (isset($row['role'])) {
        if ($row['role'] == 'Admin') {
            $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
        } elseif ($row['role'] == 'Editor') {
            $role_badge = '<small class="badge badge-primary" style="font-size: 0.7em;">Editor</small>';
        } else {
            $role_badge = '<small class="badge badge-secondary" style="font-size: 0.7em;">User</small>';
        }
    }

    $is_mine = ($row['author_id'] == $user['id']);
    $is_admin = ($user['role'] == 'Admin');
    
    $clean_avatar = str_replace('../', '', $author_avatar);
    if (strpos($clean_avatar, 'http') !== 0) {
        $clean_avatar = '../' . $clean_avatar;
    }
    
    echo '
        <tr>
            <td class="text-center">
                <input type="checkbox" name="page_ids[]" value="' . $row['id'] . '">
            </td>
            <td>' . htmlspecialchars($row['title']) . '</td>
            
            <td>
                <div class="user-block">
                    <img src="' . htmlspecialchars($clean_avatar) . '" 
                         class="img-circle elevation-1" 
                         width="40" height="40" 
                         style="float:left; margin-right:10px; object-fit:cover;"
                         onerror="this.src=\'../assets/img/avatar.png\';">
                         
                    <span class="username" style="font-size:14px;">' . $author_name . '</span>
                    <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                </div>
            </td>

            <td><code class="text-muted">page?name=' . htmlspecialchars($row['slug']) . '</code></td>
            <td class="text-center">';
            
    if ($row['active'] == 'Yes') {
        echo '<span class="badge badge-success">Published</span>';
    } else {
        echo '<span class="badge badge-warning">Draft</span>';
    }
    
    echo '  </td>
            <td class="text-center">
                <a href="../page?name=' . htmlspecialchars($row['slug']) . '" target="_blank" class="btn btn-secondary btn-sm mr-1" title="View">
                    <i class="fas fa-eye"></i>
                </a>';
                
                if ($is_admin || $is_mine) {
                    echo '<a href="edit_page.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>';
                }
                
                if ($is_admin || $is_mine) {
                    echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this page?\');" title="Delete">
                        <i class="fa fa-trash"></i>
                    </a>';
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
                                        <option value="publish">Set as Published</option>
                                        <option value="draft">Set as Draft</option>
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
        </div>

    </div>
</section>

<script>
$(document).ready(function() {
    var table = $('#dt-pages').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 1, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 5] }
        ]
    });

    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>
<?php include "footer.php"; ?>