<?php
include "header.php";

// --- ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $cat_ids = $_POST['cat_ids'] ?? [];

    if (!empty($action) && !empty($cat_ids)) {
        $ids_clean = array_map('intval', $cat_ids);
        $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
        $types = str_repeat('i', count($ids_clean));
        
        if ($action == 'delete' && $user['role'] != 'Admin') {
             echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete categories.</div>';
        } else {
            if ($action == 'delete') {
                // 1. Désassocier les projets
                $stmt_up = mysqli_prepare($connect, "UPDATE projects SET project_category_id=0 WHERE project_category_id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt_up, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt_up);
                
                // 2. Supprimer les images physiques
                foreach ($ids_clean as $id_del) {
                    $q_img = mysqli_query($connect, "SELECT image FROM project_categories WHERE id=$id_del");
                    $r_img = mysqli_fetch_assoc($q_img);
                    if ($r_img && !empty($r_img['image']) && file_exists("../" . $r_img['image'])) {
                        @unlink("../" . $r_img['image']);
                    }
                }

                // 3. Supprimer les catégories
                $stmt = mysqli_prepare($connect, "DELETE FROM project_categories WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            echo '<meta http-equiv="refresh" content="0; url=project_categories.php">';
            exit;
        }
    }
}

// --- SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    // Récupérer l'image pour suppression
    $q_img = mysqli_query($connect, "SELECT image FROM project_categories WHERE id=$id");
    $r_img = mysqli_fetch_assoc($q_img);
    if ($r_img && !empty($r_img['image']) && file_exists("../" . $r_img['image'])) {
        @unlink("../" . $r_img['image']);
    }
    
    // Désassocier les projets
    mysqli_query($connect, "UPDATE projects SET project_category_id=0 WHERE project_category_id=$id");

    $stmt = mysqli_prepare($connect, "DELETE FROM project_categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    echo '<meta http-equiv="refresh" content="0; url=project_categories.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-tags"></i> Project Categories</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="projects.php">Projects</a></li>
                    <li class="breadcrumb-item active">Categories</li>
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
                    <a href="add_project_category.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add Category</a>
                </h3>
            </div>

            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <table class="table table-bordered table-striped" id="dt-cats" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width: 80px;" class="text-center">Image</th>
                                <th>Category Name</th>
                                <th>Author</th>
                                <th>Slug</th>
                                <th class="text-center">Projects</th>
                                <th class="text-center" style="width:140px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // REQUÊTE COMPLÈTE (Avec Auteur et Compte Projets)
                            $q = mysqli_query($connect, "
                                SELECT c.*, COUNT(p.id) as count, u.username, u.avatar, u.role 
                                FROM project_categories c 
                                LEFT JOIN projects p ON c.id = p.project_category_id 
                                LEFT JOIN users u ON c.author_id = u.id
                                GROUP BY c.id ORDER BY c.id DESC
                            ");
                            
                            while ($row = mysqli_fetch_assoc($q)) {
                                
                                // 1. IMAGE CATÉGORIE
                                $img_src = '../assets/img/projects_category_default'; // Défaut
                                if (!empty($row['image'])) {
                                    $clean_img = str_replace('../', '', $row['image']);
                                    // Si pas URL externe, on ajoute ../ pour l'admin
                                    if (strpos($clean_img, 'http') !== 0) {
                                        $img_src = '../' . $clean_img;
                                    }
                                }

                                // 2. AUTEUR (Avatar + Nom + Rôle)
                                $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
                                $author_avatar = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
                                
                                // Nettoyage Avatar
                                $clean_avatar = str_replace('../', '', $author_avatar);
                                if (strpos($clean_avatar, 'http') !== 0) {
                                    $clean_avatar = '../' . $clean_avatar;
                                }

                                $role_badge = '';
                                if (isset($row['role'])) {
                                    if ($row['role'] == 'Admin') $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
                                    elseif ($row['role'] == 'Editor') $role_badge = '<small class="badge badge-primary" style="font-size: 0.7em;">Editor</small>';
                                    else $role_badge = '<small class="badge badge-secondary" style="font-size: 0.7em;">User</small>';
                                }

                                echo '<tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="cat_ids[]" value="' . $row['id'] . '">
                                    </td>
                                    
                                    <td class="text-center">
                                        <img src="' . htmlspecialchars($img_src) . '" width="80" height="50" style="object-fit:cover; border-radius:4px;" onerror="this.src=\'../assets/img/projects_category_default.jpg\';">
                                    </td>

                                    <td>
                                        <b>'.htmlspecialchars($row['category']).'</b><br>
                                        <small class="text-muted">'.htmlspecialchars(short_text($row['description'], 50)).'</small>
                                    </td>
                                    
                                    <td>
                                        <div class="user-block">
                                            <img src="' . htmlspecialchars($clean_avatar) . '" class="img-circle elevation-1" style="width:30px; height:30px; object-fit:cover; float:left; margin-right:8px;" onerror="this.src=\'../assets/img/avatar.png\';">
                                            <span class="username" style="font-size:13px;">' . $author_name . '</span>
                                            <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                                        </div>
                                    </td>

                                    <td><code class="text-muted">'.$row['slug'].'</code></td>
                                    <td class="text-center"><span class="badge bg-info">'.$row['count'].'</span></td>
                                    
                                    <td class="text-center">
                                        <a href="edit_project_category.php?id='.$row['id'].'" class="btn btn-sm btn-primary mr-1"><i class="fas fa-edit"></i></a>
                                        <a href="?delete_id='.$row['id'].'&token='.$_SESSION['csrf_token'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete category? Projects will be uncategorized.\')"><i class="fas fa-trash"></i></a>
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
    var table = $('#dt-cats').DataTable({ "responsive": true, "autoWidth": false, "order": [[ 0, "desc" ]] }); 
    
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
}); 
</script>