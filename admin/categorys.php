<?php
include "header.php";

// --- LOGIQUE SUPPRESSION (SÉCURISÉE) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    
    // SÉCURITÉ : Seul un Admin peut supprimer une catégorie
    if ($user['role'] != 'Admin') {
        echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete categories.</div>';
        echo '<meta http-equiv="refresh" content="2; url=categorys.php">';
        exit;
    }

    $id = (int) $_GET["delete-id"];
    
    // Récupérer l'image pour la supprimer du serveur
    $stmt_img = mysqli_prepare($connect, "SELECT image FROM categories WHERE id=?");
    mysqli_stmt_bind_param($stmt_img, "i", $id);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    
    if ($r = mysqli_fetch_assoc($res_img)) {
        if (!empty($r['image']) && file_exists("../" . $r['image'])) {
            unlink("../" . $r['image']);
        }
    }
    mysqli_stmt_close($stmt_img);

    // Supprimer les articles liés
    $stmt_posts = mysqli_prepare($connect, "DELETE FROM `posts` WHERE category_id=?");
    mysqli_stmt_bind_param($stmt_posts, "i", $id);
    mysqli_stmt_execute($stmt_posts);
    mysqli_stmt_close($stmt_posts);

    // Supprimer la catégorie
    $stmt = mysqli_prepare($connect, "DELETE FROM `categories` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    // --- LOG ACTIVITY ---
    log_activity($user['id'], "Delete Category", "Deleted category ID: " . $id);
    // -----------
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=categorys.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-folder"></i> Categories</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Categories</li>
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
                            <a href="add_category.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Category
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="dt-categories" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th style="width: 100px;" class="text-center">Image</th>
                                    <th>Name & Description</th>
                                    <th>Author</th> <th>Slug</th>
                                    <th class="text-center">Posts</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
// REQUÊTE MODIFIÉE : Jointure avec la table users pour récupérer l'auteur
$query = "
    SELECT c.*, u.username, u.avatar, u.role 
    FROM categories c 
    LEFT JOIN users u ON c.author_id = u.id 
    ORDER BY c.id DESC
";
$sql = mysqli_query($connect, $query);

while ($row = mysqli_fetch_assoc($sql)) {
    
    // 1. Logique Compteur Articles
    $cat_id = $row['id'];
    $count_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM posts WHERE category_id='$cat_id'");
    $count_data = mysqli_fetch_assoc($count_query);
    $article_count = $count_data['total'];
    $badge_color = ($article_count > 0) ? 'badge-info' : 'badge-secondary';

    // 2. Logique Image Catégorie
    $img_src = '<img src="../assets/img/category_default.jpg" width="80" height="50" style="object-fit: cover; border-radius: 3px;">';
    if ($row['image'] != '') {
        $img_src = '<img src="../' . htmlspecialchars($row['image']) . '" width="80" height="50" style="object-fit: cover; border-radius: 3px;">';
    }

    // 3. Logique Auteur (Avatar + Badge Role)
    $author_name = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
    $avatar_path = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
    
    // Correction chemin avatar si local ou url externe
    if (strpos($avatar_path, 'http') !== 0) {
        $avatar_path = '../' . $avatar_path;
    }

    $role_badge = '';
    if (isset($row['role'])) {
        if ($row['role'] == 'Admin') {
            $role_badge = '<small class="badge badge-success">Admin</small>';
        } elseif ($row['role'] == 'Editor') {
            $role_badge = '<small class="badge badge-primary">Editor</small>';
        } else {
            $role_badge = '<small class="badge badge-secondary">User</small>';
        }
    }

    echo '
        <tr>
            <td>' . $row['id'] . '</td>
            <td class="text-center">' . $img_src . '</td>
            <td>
                <b>' . htmlspecialchars($row['category']) . '</b><br>
                <small class="text-muted">' . htmlspecialchars(substr($row['description'], 0, 60)) . (strlen($row['description']) > 60 ? '...' : '') . '</small>
            </td>
            
            <td>
                <div class="d-flex align-items-center">
                    <img src="' . htmlspecialchars($avatar_path) . '" class="img-circle elevation-1 mr-2" style="width:30px; height:30px; object-fit:cover;">
                    <div>
                        ' . $author_name . '<br>
                        ' . $role_badge . '
                    </div>
                </div>
            </td>
            
            <td><code class="text-muted">' . htmlspecialchars($row['slug']) . '</code></td>
            <td class="text-center"><span class="badge ' . $badge_color . '">' . $article_count . '</span></td>
            
            <td class="text-center">
                <a href="../category?name=' . htmlspecialchars($row['slug']) . '" target="_blank" class="btn btn-secondary btn-sm mr-1" title="View on site">
                    <i class="fas fa-eye"></i>
                </a>
                
                <a href="edit_category.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                    <i class="fa fa-edit"></i>
                </a>';
                
                // SÉCURITÉ : Bouton Delete visible uniquement pour Admin
                if ($user['role'] == 'Admin') {
                    echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'WARNING: Deleting this category will DELETE ALL ' . $article_count . ' POSTS inside it. Are you sure?\');" title="Delete">
                        <i class="fa fa-trash"></i>
                    </a>';
                }
                
    echo '  </td>
        </tr>';
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#dt-categories').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]]
    });
});
</script>

<?php include "footer.php"; ?>