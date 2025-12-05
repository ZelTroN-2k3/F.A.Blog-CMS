<?php
include "header.php";

// --- LOGIQUE SUPPRESSION ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    $stmt = mysqli_prepare($connect, "DELETE FROM rss_imports WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=rss_imports.php">';
    exit;
}

// --- LOGIQUE TOGGLE STATUS ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    $q = mysqli_query($connect, "SELECT is_active FROM rss_imports WHERE id=$id");
    if ($r = mysqli_fetch_assoc($q)) {
        $new_status = ($r['is_active'] == 1) ? 0 : 1;
        $stmt = mysqli_prepare($connect, "UPDATE rss_imports SET is_active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ii", $new_status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    echo '<meta http-equiv="refresh" content="0; url=rss_imports.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-rss"></i> RSS Feeds</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">RSS Imports</li>
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
                            <a href="add_rss_import.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Feed
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-rss" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Feed URL</th>
                                    <th>Import As</th>
                                    <th>Category</th>
                                    <th>Last Import</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "
                                    SELECT r.*, u.username, c.category 
                                    FROM rss_imports r
                                    LEFT JOIN users u ON r.import_as_user_id = u.id
                                    LEFT JOIN categories c ON r.import_as_category_id = c.id
                                    ORDER BY r.id DESC
                                ";
                                $result = mysqli_query($connect, $query);
                                
                                while ($feed = mysqli_fetch_assoc($result)) {
                                    
                                    // Status Badge
                                    $status_badge = ($feed['is_active'] == 1) 
                                        ? '<span class="badge badge-success">Active</span>' 
                                        : '<span class="badge badge-secondary">Inactive</span>';
                                    
                                    // Toggle styles
                                    $toggle_icon = ($feed['is_active'] == 1) ? 'fa-toggle-on' : 'fa-toggle-off';
                                    $toggle_btn  = ($feed['is_active'] == 1) ? 'btn-success' : 'btn-default';
                                    
                                    $last_import = $feed['last_import_time'] 
                                        ? date($settings['date_format'] . ' H:i', strtotime($feed['last_import_time'])) 
                                        : '<span class="text-muted">Never</span>';

                                    echo '
                                    <tr>
                                        <td>
                                            <a href="' . htmlspecialchars($feed['feed_url']) . '" target="_blank" class="text-dark">
                                                <i class="fas fa-external-link-alt text-muted small mr-1"></i> ' . htmlspecialchars($feed['feed_url']) . '
                                            </a>
                                        </td>
                                        <td>' . htmlspecialchars($feed['username']) . '</td>
                                        <td>' . htmlspecialchars($feed['category']) . '</td>
                                        <td>' . $last_import . '</td>
                                        <td class="text-center">' . $status_badge . '</td>
                                        <td class="text-center">
                                            
                                            <a href="run_rss_import.php?id=' . $feed['id'] . '" target="_blank" class="btn btn-warning btn-sm mr-1" title="Run Import Now">
                                                <i class="fas fa-sync"></i>
                                            </a>

                                            <a href="?toggle_id=' . $feed['id'] . '&token=' . $csrf_token . '" class="btn btn-sm ' . $toggle_btn . ' mr-1" title="Toggle Status">
                                                <i class="fas ' . $toggle_icon . '"></i>
                                            </a>
                                            
                                            <a href="edit_rss_import.php?id=' . $feed['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="?delete_id=' . $feed['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this feed?\');" title="Delete">
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
                
                <div class="callout callout-info mt-3">
                    <h5><i class="fas fa-info"></i> Cron Job Setup</h5>
                    <p>To automate imports, add this command to your server's Cron Jobs (e.g., once per hour):</p>
                    <code>wget -q -O - <?php echo $settings['site_url']; ?>/admin/run_rss_import.php?key=<?php echo RSS_CRON_SECRET_KEY; ?></code>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('#dt-rss').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 3, "desc" ]] // Trier par date d'import
    });
});
</script>