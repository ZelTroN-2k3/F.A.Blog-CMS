<?php
include "header.php";

// --- LOGIQUE ACTIONS EN MASSE (NOUVEAU) ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $post_ids = $_POST['post_ids'] ?? [];

    if (!empty($action) && !empty($post_ids)) {
        
        // SÉCURITÉ : Suppression réservée aux Admins
        if ($action == 'delete' && $user['role'] != 'Admin') {
            echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete posts in bulk.</div>';
        } else {
            $ids_clean = array_map('intval', $post_ids);
            $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
            $types = str_repeat('i', count($ids_clean));

            if ($action == 'publish') {
                $stmt = mysqli_prepare($connect, "UPDATE posts SET active = 'Yes' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'draft') {
                $stmt = mysqli_prepare($connect, "UPDATE posts SET active = 'Draft' WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action == 'delete') {
                // Suppression en cascade (Commentaires, Tags...)
                $stmt_c = mysqli_prepare($connect, "DELETE FROM comments WHERE post_id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt_c, $types, ...$ids_clean); mysqli_stmt_execute($stmt_c); mysqli_stmt_close($stmt_c);
                
                $stmt_t = mysqli_prepare($connect, "DELETE FROM post_tags WHERE post_id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt_t, $types, ...$ids_clean); mysqli_stmt_execute($stmt_t); mysqli_stmt_close($stmt_t);
                
                $stmt = mysqli_prepare($connect, "DELETE FROM posts WHERE id IN ($placeholders)");
                mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            echo '<meta http-equiv="refresh" content="0; url=posts.php">';
            exit;
        }
    }
}

// --- LOGIQUE ADMIN : APPROBATION / REJET ---
if ($user['role'] == 'Admin') {
    if (isset($_GET['approve-id'])) {
        validate_csrf_token_get();
        $post_id = (int)$_GET['approve-id'];
        $stmt = mysqli_prepare($connect, "UPDATE posts SET active='Yes' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<meta http-equiv="refresh" content="0; url=posts.php">';
        exit;
    }
    if (isset($_GET['reject-id'])) {
        validate_csrf_token_get();
        $post_id = (int)$_GET['reject-id'];
        $stmt = mysqli_prepare($connect, "DELETE FROM posts WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<meta http-equiv="refresh" content="0; url=posts.php">';
        exit;
    }
}

// --- LOGIQUE SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int) $_GET["delete-id"];
    
    $stmt = mysqli_prepare($connect, "DELETE FROM `comments` WHERE post_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);

    $stmt_tags = mysqli_prepare($connect, "DELETE FROM `post_tags` WHERE post_id=?");
    mysqli_stmt_bind_param($stmt_tags, "i", $id); mysqli_stmt_execute($stmt_tags); mysqli_stmt_close($stmt_tags);

    $stmt = mysqli_prepare($connect, "DELETE FROM `posts` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    log_activity($user['id'], "Delete Post", "Deleted post ID: " . $id);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=posts.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-list"></i> All Posts</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Posts</li>
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
                            <a href="add_post.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Post
                            </a>
                        </h3>
                        <div class="card-tools">
                            <!--<div class="btn-group">
                                <a href="posts.php" class="btn btn-sm btn-default">All</a>
                                <a href="posts.php?status=published" class="btn btn-sm btn-success">Published</a>
                                <a href="posts.php?status=draft" class="btn btn-sm btn-warning">Drafts</a>
                                <a href="posts.php?status=pending" class="btn btn-sm btn-info">Pending</a>
                            </div>-->                                  
                        
                            <!--<div class="btn-group">-->
                                <?php
                                // Gestion de l'état actif des boutons
                                $st = $_GET['status'] ?? 'all';
                                ?>
                                <a href="posts.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                                <a href="posts.php?status=published" class="btn btn-sm <?php echo ($st == 'published') ? 'btn-success' : 'btn-default text-success'; ?>">Published</a>
                                <a href="posts.php?status=draft" class="btn btn-sm <?php echo ($st == 'draft') ? 'btn-warning' : 'btn-default text-warning'; ?>">Drafts</a>
                                <a href="posts.php?status=pending" class="btn btn-sm <?php echo ($st == 'pending') ? 'btn-info' : 'btn-default text-info'; ?>">Pending</a>
                            <!--</div>-->                        
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <table class="table table-bordered table-hover" id="dt-basic" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 10px;" class="text-center">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th style="width:50px;">Image</th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th>Status</th> 
                                        <th>Category</th>
                                        <th class="text-center" style="width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
// --- FILTRES SQL ---
$where_clause = "";
if (isset($_GET['status'])) {
    $status_code = $_GET['status'];
    if ($status_code == 'draft') { $where_clause = "WHERE p.active = 'Draft'"; } 
    elseif ($status_code == 'pending') { $where_clause = "WHERE p.active = 'Pending'"; } 
    elseif ($status_code == 'published') { $where_clause = "WHERE p.active = 'Yes'"; } 
}

$query = "
    SELECT 
        p.*, 
        c.category AS category_name, 
        u.username AS author_name,
        u.role AS author_role, 
        u.avatar AS author_avatar
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    $where_clause
    ORDER BY p.id DESC
";
$sql = mysqli_query($connect, $query);

while ($row = mysqli_fetch_assoc($sql)) {
    $featured = ($row['featured'] == "Yes") ? '<span class="badge badge-primary ml-1" title="Featured Post"><i class="fas fa-star"></i></span>' : '';
    
    // Avatar
    $author_avatar = !empty($row['author_avatar']) ? $row['author_avatar'] : 'assets/img/avatar.png';
    $clean_avatar = str_replace('../', '', $author_avatar);
    if (strpos($clean_avatar, 'http') !== 0) { $clean_avatar = '../' . $clean_avatar; }

    // Image Article
    $img_src = '../assets/img/no-image.png'; 
    if (!empty($row['image'])) {
        $img_src = '../' . str_replace('../', '', $row['image']);
    }
    
    $role_badge = '';
    if (isset($row['author_role'])) {
        if ($row['author_role'] == 'Admin') $role_badge = '<small class="badge badge-success" style="font-size: 0.7em;">Admin</small>';
        elseif ($row['author_role'] == 'Editor') $role_badge = '<small class="badge badge-info" style="font-size: 0.7em;">Editor</small>';
    }

    $is_my_post = ($row['author_id'] == $user['id']);
    $is_admin = ($user['role'] == 'Admin');

echo '
        <tr>
            <td class="text-center">
                <input type="checkbox" name="post_ids[]" value="' . $row['id'] . '">
            </td>
            <td class="text-center">
                <img src="' . htmlspecialchars($img_src) . '" width="50" height="50" style="object-fit: cover; border-radius: 4px;" alt="Image" onerror="this.onerror=null; this.src=\'../assets/img/no-image.png\';" />
            </td>
            <td>' . htmlspecialchars($row['title']) . ' ' . $featured . '</td>
            <td><small class="text-muted">/' . htmlspecialchars($row['slug']) . '</small></td>
            <td>
                <div class="user-block">
                    <img src="' . htmlspecialchars($clean_avatar) . '" width="40" height="40" class="img-circle elevation-1" alt="User" style="float:left; margin-right:10px; object-fit:cover;" onerror="this.src=\'../assets/img/avatar.png\';"> 
                    <span class="username" style="font-size:14px;">' . htmlspecialchars($row['author_name'] ?? 'N/A') . '</span>
                    <span class="description" style="margin-left: 0;">' . $role_badge . '</span>
                </div>
            </td>
            <td data-sort="' . strtotime($row['created_at']) . '">' . date($settings['date_format'], strtotime($row['created_at'])) . '</td>
            
            <td>';
    if($row['active'] == "Yes") {
        echo '<span class="badge badge-success">Published</span>';
    } else if ($row['active'] == 'Pending') {
        echo '<span class="badge badge-info">Pending</span>';
    } else {
        echo '<span class="badge badge-warning">Draft</span>';
    }
    echo '</td>
        <td>' . htmlspecialchars($row['category_name'] ?? 'Uncategorized') . '</td>
        
        <td class="text-center">
            <a href="../post?name=' . htmlspecialchars($row['slug']) . '" target="_blank" class="btn btn-secondary btn-sm mr-1" title="View on site">
                <i class="fas fa-eye"></i>
            </a>';

            if ($user['role'] == 'Admin' && $row['active'] == 'Pending') {
                echo '<a href="?approve-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-success btn-sm mr-1" title="Approve"><i class="fa fa-check"></i></a>';
                echo '<a href="?reject-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-warning btn-sm mr-1" onclick="return confirm(\'Reject this post?\');" title="Reject"><i class="fa fa-times"></i></a>';
            }
            
            if ($is_admin || $is_my_post) {
                echo '<a href="edit_post.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fa fa-edit"></i></a>';
                echo '<a href="?delete-id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this post?\');" title="Delete"><i class="fa fa-trash"></i></a>';
            }
            
echo '</td>
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
    var table = $('#dt-basic').DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "order": [[ 5, "desc" ]], // Trier par date (colonne 5)
        "columnDefs": [
            { "orderable": false, "targets": [0, 8] } // Désactive tri sur Checkbox et Actions
        ]
    });
    
    // Select All
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>

<?php include "footer.php"; ?>