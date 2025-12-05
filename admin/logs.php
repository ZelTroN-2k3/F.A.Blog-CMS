<?php
include "header.php";

// SÉCURITÉ : Seul l'Admin a accès aux logs
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

// --- LOGIQUE : VIDER LES LOGS ---
if (isset($_POST['clear_logs'])) {
    validate_csrf_token();
    mysqli_query($connect, "TRUNCATE TABLE activity_logs");
    echo '<div class="alert alert-success m-3">Activity logs cleared successfully.</div>';
    echo '<meta http-equiv="refresh" content="1; url=logs.php">';
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-history"></i> Activity Logs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">System Activities</h3>
                <div class="card-tools">
                    <form method="post" onsubmit="return confirm('Are you sure you want to clear ALL logs? This cannot be undone.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <button type="submit" name="clear_logs" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Clear All Logs
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card-body">
                <table id="dt-logs" class="table table-bordered table-hover table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th style="width: 150px;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Requête avec jointure pour avoir le nom de l'utilisateur
                    $query = "
                        SELECT l.*, u.username, u.role, u.avatar 
                        FROM activity_logs l 
                        LEFT JOIN users u ON l.user_id = u.id 
                        ORDER BY l.id DESC
                    ";
                    $result = mysqli_query($connect, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        // Badge Rôle
                        $role_badge = '';
                        if ($row['role'] == 'Admin') $role_badge = '<small class="badge bg-success">Admin</small>';
                        elseif ($row['role'] == 'Editor') $role_badge = '<small class="badge bg-primary">Editor</small>';
                        else $role_badge = '<small class="badge bg-secondary">User</small>';

                        // Avatar
                        $avatar = !empty($row['avatar']) ? '../' . $row['avatar'] : '../assets/img/avatar.png';
                        $username = !empty($row['username']) ? htmlspecialchars($row['username']) : 'Unknown (ID ' . $row['user_id'] . ')';

                        // Couleur de l'action
                        $action_color = 'text-dark';
                        if (stripos($row['action_type'], 'Delete') !== false) $action_color = 'text-danger fw-bold';
                        if (stripos($row['action_type'], 'Create') !== false) $action_color = 'text-success fw-bold';
                        if (stripos($row['action_type'], 'Update') !== false) $action_color = 'text-primary';
                        if (stripos($row['action_type'], 'Login') !== false) $action_color = 'text-info';

                        echo '
                        <tr>
                            <td>' . $row['id'] . '</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="' . htmlspecialchars($avatar) . '" class="img-circle elevation-1 mr-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <div>
                                        <strong>' . $username . '</strong><br>
                                        ' . $role_badge . '
                                    </div>
                                </div>
                            </td>
                            <td class="' . $action_color . '">' . htmlspecialchars($row['action_type']) . '</td>
                            <td>' . htmlspecialchars($row['details']) . '</td>
                            <td><span class="badge bg-light text-dark border">' . htmlspecialchars($row['ip_address']) . '</span></td>
                            <td data-sort="' . strtotime($row['created_at']) . '">
                                <small><i class="far fa-clock"></i> ' . date('d M Y H:i', strtotime($row['created_at'])) . '</small>
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
    $('#dt-logs').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]], // Le plus récent en premier
        "pageLength": 25
    });
});
</script>

<?php include "footer.php"; ?>