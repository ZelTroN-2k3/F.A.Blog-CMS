<?php
include "header.php";

// --- LOGIQUE ACTIONS EN MASSE (NOUVEAU) ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $ad_ids = $_POST['ad_ids'] ?? [];

    if (!empty($action) && !empty($ad_ids)) {
        $ids_clean = array_map('intval', $ad_ids);
        $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
        $types = str_repeat('i', count($ids_clean));

        if ($action == 'activate') {
            $stmt = mysqli_prepare($connect, "UPDATE ads SET active = 'Yes' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'deactivate') {
            $stmt = mysqli_prepare($connect, "UPDATE ads SET active = 'No' WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action == 'delete') {
            // Suppression des images physiques
            foreach ($ids_clean as $id_del) {
                $q = mysqli_query($connect, "SELECT image_url FROM ads WHERE id=$id_del");
                $r = mysqli_fetch_assoc($q);
                if ($r && !empty($r['image_url']) && file_exists("../" . $r['image_url'])) {
                    unlink("../" . $r['image_url']);
                }
            }
            // Suppression BDD
            $stmt = mysqli_prepare($connect, "DELETE FROM ads WHERE id IN ($placeholders)");
            mysqli_stmt_bind_param($stmt, $types, ...$ids_clean);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        echo '<meta http-equiv="refresh" content="0; url=ads.php">';
        exit;
    }
}

// --- SUPPRESSION INDIVIDUELLE ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    $q = mysqli_query($connect, "SELECT image_url FROM ads WHERE id=$id");
    $r = mysqli_fetch_assoc($q);
    if ($r && !empty($r['image_url']) && file_exists("../" . $r['image_url'])) {
        unlink("../" . $r['image_url']);
    }

    $stmt = mysqli_prepare($connect, "DELETE FROM ads WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=ads.php">';
    exit;
}

// --- TOGGLE STATUS INDIVIDUEL ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    $stmt = mysqli_prepare($connect, "UPDATE ads SET active = IF(active='Yes', 'No', 'Yes') WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0; url=ads.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-ad"></i> Advertising Management</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Ads</li>
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
                    <a href="add_ads.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Ad</a>
                </h3>

                <div class="card-tools">
                    <!--<div class="btn-group">-->
                        <?php $st = $_GET['status'] ?? 'all'; ?>
                        <a href="ads.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                        <a href="ads.php?status=active" class="btn btn-sm <?php echo ($st == 'active') ? 'btn-success' : 'btn-default text-success'; ?>">Active</a>
                        <a href="ads.php?status=inactive" class="btn btn-sm <?php echo ($st == 'inactive') ? 'btn-warning' : 'btn-default text-warning'; ?>">Inactive</a>
                    <!--</div>-->
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <table class="table table-bordered table-hover" id="dt-ads" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width:120px" class="text-center">Preview</th>
                                <th>Name & Link</th>
                                <th class="text-center">Format</th>
                                <th class="text-center">Clicks</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FILTRE SQL
                            $where = "";
                            if ($st == 'active') $where = "WHERE active='Yes'";
                            if ($st == 'inactive') $where = "WHERE active='No'";
                            
                            $q = mysqli_query($connect, "SELECT * FROM ads $where ORDER BY id DESC");
                            
                            while ($row = mysqli_fetch_assoc($q)) {
                                // Nettoyage image
                                $img_src = (!empty($row['image_url'])) ? '../' . str_replace('../', '', $row['image_url']) : '../assets/img/no-image.png';
                            ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="ad_ids[]" value="<?php echo $row['id']; ?>">
                                    </td>
                                    <td class="text-center align-middle">
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" style="max-width: 100px; max-height: 60px; border:1px solid #ddd; border-radius: 4px;" onerror="this.src='../assets/img/no-image.png';">
                                    </td>
                                    <td class="align-middle">
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                        <small class="text-muted"><i class="fas fa-link"></i> <?php echo htmlspecialchars($row['link_url']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-info"><?php echo htmlspecialchars($row['ad_size']); ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-dark" style="font-size:0.9em;"><?php echo number_format($row['clicks']); ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo ($row['active'] == 'Yes') ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Inactive</span>'; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="edit_ads.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mr-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="?toggle_id=<?php echo $row['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm <?php echo ($row['active']=='Yes') ? 'btn-warning' : 'btn-success'; ?> mr-1" title="Toggle">
                                            <i class="fas <?php echo ($row['active']=='Yes') ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                        </a>
                                        
                                        <a href="?delete_id=<?php echo $row['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this ad?');" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="mt-3 p-2 bg-light border rounded">
                        <div class="d-inline-flex align-items-center">
                            <span class="mr-2">With Selected:</span>
                            <select name="bulk_action" class="form-control form-control-sm mr-2" style="width: auto;">
                                <option value="">-- Choose Action --</option>
                                <option value="activate">Set as Active</option>
                                <option value="deactivate">Set as Inactive</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" name="apply_bulk_action" class="btn btn-primary btn-sm">Apply</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    var table = $('#dt-ads').DataTable({
        "responsive": true, 
        "autoWidth": false,
        "order": [[ 2, "asc" ]], // Tri par nom
        "columnDefs": [
            { "orderable": false, "targets": [0, 1, 6] } // Pas de tri sur Checkbox, Image, Actions
        ]
    });

    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
});
</script>

<?php include "footer.php"; ?>