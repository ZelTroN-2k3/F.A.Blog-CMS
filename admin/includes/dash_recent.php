<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock mr-1"></i> New Users</h3></div>
    <div class="card-body p-0">
        <ul class="users-list clearfix">
            <?php
            if (mysqli_num_rows($query_latest_users) > 0) {
                while ($row_user = mysqli_fetch_assoc($query_latest_users)) {
                    $avatar_raw = $row_user['avatar'];
                    $avatar_path = (strpos($avatar_raw, 'http') === 0) ? htmlspecialchars($avatar_raw) : '../' . htmlspecialchars($avatar_raw);
                    echo '<li>
                            <img src="'.$avatar_path.'" alt="User" style="width:50px; height:50px; object-fit:cover;" class="rounded-circle">
                            <a class="users-list-name mt-2" href="users.php?edit-id='.$row_user['id'].'">'.htmlspecialchars($row_user['username']).'</a>
                            <span class="users-list-date">'.htmlspecialchars($row_user['role']).'</span>
                          </li>';
                }
            } else { echo '<p class="text-center p-3">No users.</p>'; }
            ?>
        </ul>
    </div>
    <div class="card-footer text-center"><a href="users.php">View All Users</a></div>
</div>

<div class="card card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-server mr-1"></i> System Health</h3></div>
    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <span><i class="fas fa-database text-muted"></i> Backups</span>
            <span class="badge bg-<?php echo ($last_backup_date == 'Never' ? 'danger' : 'success'); ?>"><?php echo $last_backup_date; ?></span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <span><i class="fas fa-tools text-muted"></i> Maintenance</span>
            <span class="badge bg-<?php echo ($maintenance_status == 'On' ? 'danger' : 'success'); ?>"><?php echo $maintenance_status; ?></span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span><i class="fas fa-shield-alt text-muted"></i> Security</span>
            <span class="badge bg-success">Active</span>
        </div>

        <div class="mt-3">
            <a href="backup.php" class="btn btn-primary btn-block btn-sm"><i class="fas fa-download"></i> Manage Backups</a>
        </div>
    </div>
</div>

<div class="info-box mb-3 bg-light">
    <span class="info-box-icon bg-navy"><i class="fas fa-code-branch"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Version</span>
        <span class="info-box-number"><?php echo $admin_version; ?> (Pro)</span>
    </div>
</div>