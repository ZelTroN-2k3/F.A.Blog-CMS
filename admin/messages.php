<?php
include "header.php";

// --- LOGIQUE SUPPRESSION ---
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; 
    
    // Validation CSRF
    validate_csrf_token_get(); 
    
    $stmt = mysqli_prepare($connect, "DELETE FROM messages WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<meta http-equiv="refresh" content="0; url=messages.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-envelope"></i> Messages</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Messages</li>
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
                        <h3 class="card-title">Inbox</h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="dt-basic" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">ID</th>
                                    <th>Sender Name</th>
                                    <th>Email Address</th>
                                    <th>Date Received</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
    $sql = mysqli_query($connect, "SELECT * FROM `messages` ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($sql)) {
        
        // Statut Lu / Non Lu
        $status_badge = ($row['viewed'] == 'No') 
            ? '<span class="badge badge-danger">Unread</span>' 
            : '<span class="badge badge-secondary">Read</span>';
        
        // Gras si non lu
        $name_style = ($row['viewed'] == 'No') ? 'font-weight-bold' : '';
        
        echo '
        <tr>
            <td class="text-center">' . $row['id'] . '</td>
            <td class="' . $name_style . '">' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['email']) . '</td>
            <td data-sort="' . strtotime($row['created_at']) . '">' . date($settings['date_format'] . ' H:i', strtotime($row['created_at'])) . '</td>
            <td class="text-center">' . $status_badge . '</td>
            <td class="text-center">
                <a href="read_message.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Read Message">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="?id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this message permanently?\');" title="Delete">
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
        "order": [[ 3, "desc" ]] // Trier par date (colonne index 3) descendant
    });
});
</script>