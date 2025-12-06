<?php
include "header.php";

// --- GESTION STATUT URL ---
$status_url = '';
$current_status = $_GET['status'] ?? 'all';
if ($current_status != 'all') {
    $status_url = '?status=' . htmlspecialchars($current_status);
}

// --- LOGIQUE ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $project_ids = $_POST['project_ids'] ?? [];

    if (!empty($action) && !empty($project_ids)) {
        
        $ids_clean = array_map('intval', $project_ids);
        $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
        $types = str_repeat('i', count($ids_clean));
        
        if ($action == 'delete' && $user['role'] != 'Admin') {
             echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete projects in bulk.</div>';
        } else {
            if ($action == 'publish') {
                $stmt = mysqli_prepare($connect, "UPDATE projects SET active = 'Yes' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'draft') {
                $stmt = mysqli_prepare($connect, "UPDATE projects SET active = 'No' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'delete') {
                $stmt = mysqli_prepare($connect, "DELETE FROM projects WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            echo '<meta http-equiv="refresh" content="0; url=projects.php' . $status_url . '">';
            exit;
        }
    }
}

// --- LOGIQUE SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete-id'];
    
    $check = mysqli_query($connect, "SELECT author_id FROM projects WHERE id=$id");
    $pdata = mysqli_fetch_assoc($check);
    
    if ($user['role'] == 'Admin' || $pdata['author_id'] == $user['id']) {
        $stmt = mysqli_prepare($connect, "DELETE FROM projects WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<meta http-equiv="refresh" content="0; url=projects.php' . $status_url . '">';
        exit;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-microchip"></i> My Projects</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Projects</li>
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
                    <a href="add_project.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Create Project</a>
                </h3>

                <div class="card-tools">
                    <!--<div class="btn-group">-->
                        <?php $st = $_GET['status'] ?? 'all'; ?>
                        <a href="projects.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                        <a href="projects.php?status=published" class="btn btn-sm <?php echo ($st == 'published') ? 'btn-success' : 'btn-default text-success'; ?>">Published</a>
                        <a href="projects.php?status=draft" class="btn btn-sm <?php echo ($st == 'draft') ? 'btn-warning' : 'btn-default text-warning'; ?>">Drafts</a>
                    <!--</div>-->
                </div>
            </div>

            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <table id="dt-projects" class="table table-bordered table-hover table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width:60px">Cover</th>
                                <th>Project Title</th>
                                <th>Category</th> <th>Difficulty</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                                <th class="text-center" style="width:140px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FILTRE SQL
                            $where_clause = "";
                            if ($st == 'published') { $where_clause = "WHERE p.active='Yes'"; }
                            if ($st == 'draft') { $where_clause = "WHERE p.active!='Yes'"; }

                            // REQUÊTE MISE À JOUR (JOIN avec categories)
                            $q = mysqli_query($connect, "
                                SELECT p.*, u.username, c.category as cat_name 
                                FROM projects p 
                                LEFT JOIN users u ON p.author_id = u.id 
                                LEFT JOIN project_categories c ON p.project_category_id = c.id 
                                $where_clause 
                                ORDER BY p.id DESC
                            ");
                            
                            while ($row = mysqli_fetch_assoc($q)) {
                                
                                // Image Robuste
                                $img_src = '../assets/img/project-no-image.png';
                                if (!empty($row['image'])) {
                                    $clean = str_replace('../', '', $row['image']);
                                    if (file_exists('../' . $clean)) { $img_src = '../' . $clean; }
                                }

                                $status_badge = ($row['active'] == 'Yes') ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-warning">Draft</span>';
                                
                                // Couleurs Difficulté
                                $badge_color = 'secondary';
                                if($row['difficulty'] == 'Easy') $badge_color = 'success';
                                if($row['difficulty'] == 'Intermediate') $badge_color = 'primary';
                                if($row['difficulty'] == 'Advanced') $badge_color = 'warning';
                                if($row['difficulty'] == 'Expert') $badge_color = 'danger';
                                
                                // Nom Catégorie
                                $cat_display = !empty($row['cat_name']) ? htmlspecialchars($row['cat_name']) : '<span class="text-muted small">Uncategorized</span>';

                                // Permissions
                                $can_edit = ($user['role'] == 'Admin' || $row['author_id'] == $user['id']);

                                // --- NOUVEAU : GESTION DU BADGE "FEATURED" (Étoile) ---
                                $featured_badge = '';
                                if ($row['featured'] == 'Yes') {
                                    $featured_badge = '<span class="badge bg-warning ms-1" title="Featured in Slider"><i class="fas fa-star"></i></span>';
                                }
                                // ------------------------------------------------------

                                echo '
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="project_ids[]" value="' . $row['id'] . '">
                                    </td>
                                    <td class="text-center"><img src="' . htmlspecialchars($img_src) . '" width="60" height="40" style="object-fit:cover; border-radius:4px;" onerror="this.src=\'../assets/img/no-image.png\';"></td>
                                    
                                    <td>
                                        <strong>' . htmlspecialchars($row['title']) . '</strong>' . $featured_badge . '<br>
                                        <small class="text-muted">' . htmlspecialchars(short_text($row['pitch'], 80)) . '</small>
                                    </td>
                                    
                                    <td>' . $cat_display . '</td>
                                    
                                    <td><span class="badge bg-' . $badge_color . '">' . htmlspecialchars($row['difficulty']) . '</span></td>
                                    <td class="text-center">' . $status_badge . '</td>
                                    <td>' . date('d M Y', strtotime($row['created_at'])) . '</td>
                                    <td class="text-center">';
                                        
                                    if ($can_edit) {
                                        echo '<a href="edit_project.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1"><i class="fas fa-edit"></i></a>';
                                        echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . $status_url . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete project?\');"><i class="fas fa-trash"></i></a>';
                                    }
                                    
                                echo '</td></tr>';
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
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() { 
    var table = $('#dt-projects').DataTable({ 
        "responsive": true, 
        "autoWidth": false, 
        "order": [[ 6, "desc" ]], // Tri par Date (Colonne 6 maintenant, car ajout de catégorie)
        "columnDefs": [
            { "orderable": false, "targets": [0, 1, 7] } // Pas de tri sur Checkbox, Image, Actions
        ]
    }); 

    // Select All
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>