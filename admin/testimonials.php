<?php
include "header.php";

// --- LOGIQUE SUPPRESSION ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    // 1. Récupérer l'image pour suppression physique
    $stmt_img = mysqli_prepare($connect, "SELECT avatar FROM testimonials WHERE id=?");
    mysqli_stmt_bind_param($stmt_img, "i", $id);
    mysqli_stmt_execute($stmt_img);
    $res = mysqli_stmt_get_result($stmt_img);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt_img);

    if ($row && !empty($row['avatar']) && file_exists("../" . $row['avatar'])) {
        @unlink("../" . $row['avatar']);
    }

    // 2. Supprimer l'entrée
    $stmt = mysqli_prepare($connect, "DELETE FROM testimonials WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=testimonials.php">';
    exit;
}

// --- LOGIQUE TOGGLE STATUS ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    
    // Récupérer l'état actuel
    $q = mysqli_query($connect, "SELECT active FROM testimonials WHERE id=$id");
    if($r = mysqli_fetch_assoc($q)){
        $new_status = ($r['active'] == 'Yes') ? 'No' : 'Yes';
        $stmt = mysqli_prepare($connect, "UPDATE testimonials SET active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    echo '<meta http-equiv="refresh" content="0; url=testimonials.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-comments"></i> Testimonials</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Testimonials</li>
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
                            <a href="add_testimonial.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Testimonial
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-basic" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">Avatar</th>
                                    <th>Author Info</th>
                                    <th>Quote / Content</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = mysqli_query($connect, "SELECT * FROM testimonials ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    
                                    $avatar = !empty($row['avatar']) ? '../' . htmlspecialchars($row['avatar']) : 'assets/img/avatar.png';
                                    $status_badge = ($row['active'] == 'Yes') 
                                        ? '<span class="badge badge-success">Active</span>' 
                                        : '<span class="badge badge-secondary">Inactive</span>';
                                    
                                    // Toggle button styles
                                    $toggle_icon = ($row['active'] == 'Yes') ? 'fa-eye-slash' : 'fa-eye';
                                    $toggle_btn  = ($row['active'] == 'Yes') ? 'btn-warning' : 'btn-success';

                                    echo '
                                    <tr>
                                        <td class="text-center align-middle">
                                            <img src="' . $avatar . '" class="img-circle elevation-2" width="50" height="50" style="object-fit: cover;">
                                        </td>
                                        <td class="align-middle">
                                            <strong>' . htmlspecialchars($row['name']) . '</strong><br>
                                            <small class="text-muted">' . htmlspecialchars($row['position']) . '</small>
                                        </td>
                                        <td class="align-middle">
                                            <em class="text-muted">"' . htmlspecialchars(substr(strip_tags($row['content']), 0, 80)) . (strlen($row['content']) > 80 ? '...' : '') . '"</em>
                                        </td>
                                        <td class="text-center align-middle">' . $status_badge . '</td>
                                        <td class="text-center align-middle">
                                            <a href="?toggle_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm ' . $toggle_btn . ' mr-1" title="Toggle Status">
                                                <i class="fas ' . $toggle_icon . '"></i>
                                            </a>
                                            <a href="edit_testimonial.php?id=' . $row['id'] . '" class="btn btn-sm btn-info mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this testimonial?\');" title="Delete">
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
        "order": [[ 0, "desc" ]] 
    });
});
</script>