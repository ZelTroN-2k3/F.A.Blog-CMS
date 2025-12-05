<?php
include "header.php";

// --- LOGIQUE DE SUPPRESSION ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    
    $tag_id = (int)$_GET['delete-id'];
    
    // 1. Supprimer les liaisons dans post_tags
    $stmt1 = mysqli_prepare($connect, "DELETE FROM post_tags WHERE tag_id = ?");
    mysqli_stmt_bind_param($stmt1, "i", $tag_id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);
    
    // 2. Supprimer le tag lui-même
    $stmt2 = mysqli_prepare($connect, "DELETE FROM tags WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $tag_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    
    echo '<meta http-equiv="refresh" content="0; url=tags.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tags"></i> Tags Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Tags</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">List of all Tags</h3>
                <div class="card-tools">
                    <a href="add_tag.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add New Tag</a>
                </div>
            </div>
            
            <div class="card-body">
                <table id="dt-basic" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Posts Count</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Récupérer les tags avec le compte des articles associés
                    $query = "
                        SELECT t.*, COUNT(pt.post_id) as post_count 
                        FROM tags t 
                        LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                        GROUP BY t.id 
                        ORDER BY post_count DESC
                    ";
                    $run = mysqli_query($connect, $query);
                    
                    while ($row = mysqli_fetch_assoc($run)) {
                        echo '
                        <tr>
                            <td>' . $row['id'] . '</td>
                            <td><b>' . htmlspecialchars($row['name']) . '</b></td>
                            <td>' . htmlspecialchars($row['slug']) . '</td>
                            <td><span class="badge bg-info">' . $row['post_count'] . '</span></td>
                            <td>
                                <a href="edit_tag.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete-id=' . $row['id'] . '&token=' . $_SESSION['csrf_token'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure? This will remove the tag from all linked posts.\');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
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
        "order": [[ 3, "desc" ]], // Trier par nombre d'articles par défaut
        "responsive": true,
        "autoWidth": false
    });
});
</script>

<?php include "footer.php"; ?>