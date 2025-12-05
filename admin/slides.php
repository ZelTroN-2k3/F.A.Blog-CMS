<?php
include "header.php";

// --- LOGIQUE : Toggle Status (Active/Inactive) ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    
    // On récupère l'état actuel
    $req = mysqli_query($connect, "SELECT active FROM slides WHERE id=$id");
    if($row = mysqli_fetch_assoc($req)) {
        $new_status = ($row['active'] == 'Yes') ? 'No' : 'Yes';
        $stmt = mysqli_prepare($connect, "UPDATE slides SET active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    echo '<meta http-equiv="refresh" content="0; url=slides.php">';
    exit;
}

// --- LOGIQUE : Suppression ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    // 1. Récupérer l'image pour suppression physique
    $stmt_get = mysqli_prepare($connect, "SELECT image_url FROM slides WHERE id=?");
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);
    $res_get = mysqli_stmt_get_result($stmt_get);
    $row_get = mysqli_fetch_assoc($res_get);
    mysqli_stmt_close($stmt_get);
    
    if ($row_get && !empty($row_get['image_url']) && file_exists("../" . $row_get['image_url'])) {
        @unlink("../" . $row_get['image_url']);
    }

    // 2. Supprimer BDD
    $stmt = mysqli_prepare($connect, "DELETE FROM slides WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=slides.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-images"></i> Slider Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Slider</li>
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
                            <a href="add_slide.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Slide
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="dt-slides" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 120px;" class="text-center">Preview</th>
                                    <th>Title & Details</th>
                                    <th class="text-center" style="width: 80px;">Order</th>
                                    <th class="text-center" style="width: 80px;">Status</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $query = "SELECT * FROM slides ORDER BY position_order ASC";
                            $result = mysqli_query($connect, $query);
                            
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Image
                                $img_display = '<span class="text-muted">No Image</span>';
                                if (!empty($row['image_url'])) {
                                    $img_display = '<img src="../' . htmlspecialchars($row['image_url']) . '" class="img-fluid rounded" style="max-height: 60px;">';
                                }
                                
                                // Status Badge
                                $status_badge = ($row['active'] == 'Yes') 
                                    ? '<span class="badge badge-success">Active</span>' 
                                    : '<span class="badge badge-secondary">Inactive</span>';
                                    
                                echo '<tr>
                                    <td class="text-center">' . $img_display . '</td>
                                    <td>
                                        <b>' . htmlspecialchars($row['title']) . '</b><br>
                                        <small class="text-muted"><i class="fas fa-link"></i> ' . htmlspecialchars($row['link_url']) . '</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">' . (int)$row['position_order'] . '</span>
                                    </td>
                                    <td class="text-center">' . $status_badge . '</td>
                                    
                                    <td class="text-center">
                                        <a href="?toggle_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-' . ($row['active'] == 'Yes' ? 'warning' : 'success') . ' mr-1" title="Toggle Status">
                                           <i class="fas ' . ($row['active'] == 'Yes' ? 'fa-eye-slash' : 'fa-eye') . '"></i>
                                        </a>
                                        
                                        <a href="edit_slide.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary mr-1" title="Edit">
                                           <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this slide permanently?\');" title="Delete">
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

<script>
$(document).ready(function() {
    $('#dt-slides').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 2, "asc" ]], // Trier par la colonne Ordre (index 2) par défaut
        "columnDefs": [
            { "orderable": false, "targets": [0, 4] } // Désactiver le tri sur Image et Actions
        ]
    });
});
</script>

<?php include "footer.php"; ?>