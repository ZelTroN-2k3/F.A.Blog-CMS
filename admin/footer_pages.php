<?php
include "header.php";

// --- LOGIQUE : Toggle Status (Active/Inactive) ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    
    // Récupérer l'état actuel
    $req = mysqli_query($connect, "SELECT active FROM footer_pages WHERE id=$id");
    if ($row = mysqli_fetch_assoc($req)) {
        $new_status = ($row['active'] == 'Yes') ? 'No' : 'Yes';
        $stmt = mysqli_prepare($connect, "UPDATE footer_pages SET active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    echo '<meta http-equiv="refresh" content="0; url=footer_pages.php">';
    exit;
}

// --- LOGIQUE : Suppression ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    $stmt = mysqli_prepare($connect, "DELETE FROM footer_pages WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=footer_pages.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-contract"></i> Footer Pages</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Footer Pages</li>
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
                        <h3 class="card-title">Manage Pages</h3>
                        </div>
                    
                    <div class="card-body">
                        <table id="dt-basic" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>Title</th>
                                    <th>Slug (URL)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($connect, "SELECT * FROM footer_pages ORDER BY id ASC");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    
                                    // Status Badge
                                    $status_badge = ($row['active'] == 'Yes') 
                                        ? '<span class="badge badge-success">Active</span>' 
                                        : '<span class="badge badge-secondary">Draft</span>';
                                    
                                    // Toggle Button Logic
                                    $toggle_icon = ($row['active'] == 'Yes') ? 'fa-eye-slash' : 'fa-eye';
                                    $toggle_cls  = ($row['active'] == 'Yes') ? 'btn-warning' : 'btn-success';
                                    
                                    echo '
                                    <tr>
                                        <td>' . $row['id'] . '</td>
                                        <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>
                                        <td><code>' . htmlspecialchars($row['page_key']) . '</code></td>
                                        <td class="text-center">' . $status_badge . '</td>
                                        <td class="text-center">
                                            <a href="?toggle_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm ' . $toggle_cls . ' mr-1" title="Toggle Status">
                                                <i class="fas ' . $toggle_icon . '"></i>
                                            </a>
                                            <a href="edit_footer_page.php?id=' . $row['id'] . '" class="btn btn-sm btn-info mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this page?\');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
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

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('#dt-basic').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "asc" ]]
    });
});
</script>