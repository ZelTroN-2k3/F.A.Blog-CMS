<?php
include "header.php";

// --- LOGIQUE : Suppression (SÉCURISÉE) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get(); 
    
    // SÉCURITÉ : Seul un Admin peut supprimer un commentaire
    if ($user['role'] != 'Admin') {
        echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can delete comments.</div>';
        echo '<meta http-equiv="refresh" content="2; url=comments.php">';
        exit;
    }

    $id = (int) $_GET["delete-id"];
    
    $stmt = mysqli_prepare($connect, "DELETE FROM `comments` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=comments.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-comments"></i> Comments</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="posts.php">Posts</a></li>
                    <li class="breadcrumb-item active">Comments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Comments List</h3>
                <div class="card-tools">
                    <a href="comments.php" class="btn btn-sm btn-default">All</a>
                    <a href="comments.php?status=pending" class="btn btn-sm btn-warning">Pending</a>
                </div>
            </div>         
            <div class="card-body">

                <?php
                // --- GESTION DU FILTRE (Status) ---
                $where_clause_comments = "";
                $filter_msg = "";

                if (isset($_GET['status']) && $_GET['status'] == 'pending') {
                    $where_clause_comments = "WHERE approved = 'No'";
                    $filter_msg = "Pending Approval";
                }

                if ($filter_msg) {
                    echo '<div class="alert alert-warning alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-filter"></i> Moderation Mode</h5>
                            Showing comments: <strong>' . $filter_msg . '</strong>. <a href="comments.php">Show all</a>.
                          </div>';
                }
                ?>

                <table class="table table-bordered table-hover table-striped" id="dt-basic" style="width:100%">
                    <thead>
                    <tr>
                        <th>Author</th>
                        <th>Date</th>
                        <th class="text-center">Status</th>
                        <th>Post</th>
                        <th>Comment</th>
                        <th class="text-center" style="width: 140px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT * FROM comments $where_clause_comments ORDER BY id DESC";
                    $result = mysqli_query($connect, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        // --- 1. Récupération Infos Auteur ---
                        $author_name = $row['user_id'];
                        $badge  = '';
                        $avatar = 'assets/img/avatar.png'; // Fallback

                        if ($row['guest'] == 'Yes') {
                            $author_name = $row['user_id'];
                            $badge  = ' <span class="badge bg-secondary">Guest</span>';
                        } else {
                            $stmt_user = mysqli_prepare($connect, "SELECT * FROM `users` WHERE id=? LIMIT 1");
                            mysqli_stmt_bind_param($stmt_user, "i", $row['user_id']);
                            mysqli_stmt_execute($stmt_user);
                            $querych = mysqli_stmt_get_result($stmt_user);
                            
                            if ($user_row = mysqli_fetch_assoc($querych)) {
                                $avatar = $user_row['avatar'];
                                $author_name = $user_row['username'];
                                
                                if ($user_row['role'] == 'Admin') {
                                    $badge = ' <small class="badge badge-success">Admin</small>';
                                } elseif ($user_row['role'] == 'Editor') {
                                    $badge = ' <small class="badge badge-primary">Editor</small>';
                                } else {
                                    $badge = ' <small class="badge badge-secondary">User</small>';
                                }
                            }
                            mysqli_stmt_close($stmt_user);
                        }

                        // --- 2. Récupération Infos Post ---
                        $post_title = '<span class="text-muted text-sm">Deleted Post</span>';
                        $post_id = $row['post_id'];
                        
                        $stmt_post = mysqli_prepare($connect, "SELECT title, slug FROM `posts` WHERE id=?");
                        mysqli_stmt_bind_param($stmt_post, "i", $post_id);
                        mysqli_stmt_execute($stmt_post);
                        $res_post = mysqli_stmt_get_result($stmt_post);
                        if ($p_row = mysqli_fetch_assoc($res_post)) {
                            $post_title = '<a href="../post?name=' . htmlspecialchars($p_row['slug']) . '" target="_blank" title="View Post">' . htmlspecialchars($p_row['title']) . ' <i class="fas fa-external-link-alt small"></i></a>';
                        }
                        mysqli_stmt_close($stmt_post);

                        // --- 3. GESTION AVATAR ROBUSTE (Correction) ---
                        $avatar_path = '../assets/img/avatar.png';
                        if (!empty($avatar)) {
                            $clean_path = str_replace('../', '', $avatar);
                            if (strpos($clean_path, 'http') === 0) {
                                $avatar_path = $clean_path;
                            } elseif (file_exists('../' . $clean_path)) {
                                $avatar_path = '../' . $clean_path;
                            }
                        }
                        // ----------------------------------------------

                        // --- 4. Statut Badge ---
                        $status_badge = ($row['approved'] == "Yes") 
                            ? '<span class="badge badge-success">Approved</span>' 
                            : '<span class="badge badge-warning">Pending</span>';

                        // --- 5. Affichage Ligne ---
                        echo '
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="' . htmlspecialchars($avatar_path) . '" 
                                         class="img-circle elevation-1 mr-2" 
                                         style="width:35px; height:35px; object-fit:cover;"
                                         onerror="this.src=\'../assets/img/avatar.png\';">
                                    <div>
                                        <strong>' . htmlspecialchars($author_name) . '</strong><br>
                                        ' . $badge . '
                                    </div>
                                </div>
                            </td>
                            <td data-sort="' . strtotime($row['created_at']) . '">
                                <small>' . date($settings['date_format'], strtotime($row['created_at'])) . '</small><br>
                                <small class="text-muted">' . date('H:i', strtotime($row['created_at'])) . '</small>
                            </td>
                            <td class="text-center">' . $status_badge . '</td>
                            <td>' . $post_title . '</td>
                            <td><small>' . htmlspecialchars(short_text($row['comment'], 60)) . '</small></td>
                            <td class="text-center">
                                <a href="edit_comment.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>';
                                
                                // SÉCURITÉ : Bouton Delete visible uniquement pour Admin
                                if ($user['role'] == 'Admin') {
                                    echo ' <a href="?delete-id=' . $row['id'] . '&token=' . $_SESSION['csrf_token'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this comment?\');" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>';
                                }
                                
                        echo '</td>
                        </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#dt-basic').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 1, "desc" ]], // Trier par date décroissante
        "columnDefs": [
            { "orderable": false, "targets": 5 } // Désactiver le tri sur la colonne Actions
        ]
    });
});
</script>

<?php include "footer.php"; ?>