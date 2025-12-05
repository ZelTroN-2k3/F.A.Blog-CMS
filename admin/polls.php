<?php
include "header.php";

// --- LOGIQUE : SUPPRESSION ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete_id'];
    
    // 1. Supprimer les votes (historique)
    $stmt1 = mysqli_prepare($connect, "DELETE FROM poll_voters WHERE poll_id = ?");
    mysqli_stmt_bind_param($stmt1, "i", $id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // 2. Supprimer les options
    $stmt2 = mysqli_prepare($connect, "DELETE FROM poll_options WHERE poll_id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // 3. Supprimer le sondage
    $stmt3 = mysqli_prepare($connect, "DELETE FROM polls WHERE id = ?");
    mysqli_stmt_bind_param($stmt3, "i", $id);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);
    
    echo '<meta http-equiv="refresh" content="0; url=polls.php">';
    exit;
}

// --- LOGIQUE : RESET VOTES ---
if (isset($_GET['reset_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['reset_id'];
    
    // Supprimer uniquement les votes
    $stmt = mysqli_prepare($connect, "DELETE FROM poll_voters WHERE poll_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo '<div class="alert alert-success m-3">Votes has been reset.</div>';
    echo '<meta http-equiv="refresh" content="1; url=polls.php">';
    exit;
}

// --- LOGIQUE : TOGGLE STATUS ---
if (isset($_GET['toggle_id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['toggle_id'];
    
    $q = mysqli_query($connect, "SELECT active FROM polls WHERE id=$id");
    if ($r = mysqli_fetch_assoc($q)) {
        $new_status = ($r['active'] == 'Yes') ? 'No' : 'Yes';
        $stmt = mysqli_prepare($connect, "UPDATE polls SET active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    echo '<meta http-equiv="refresh" content="0; url=polls.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-poll"></i> Polls Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Polls</li>
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
                            <a href="add_poll.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Poll
                            </a>
                        </h3>

                        <!-- AJOUT DES FILTRES ICI -->
                        <div class="card-tools">
                            <!--<div class="btn-group">-->
                                <?php
                                // Gestion de l'état actif des boutons
                                $st = $_GET['status'] ?? 'all';
                                ?>
                                <a href="polls.php" class="btn btn-sm <?php echo ($st == 'all') ? 'btn-secondary' : 'btn-default'; ?>">All</a>
                                <a href="polls.php?status=published" class="btn btn-sm <?php echo ($st == 'published') ? 'btn-success' : 'btn-default text-success'; ?>">Published</a>
                                <a href="polls.php?status=draft" class="btn btn-sm <?php echo ($st == 'draft') ? 'btn-danger' : 'btn-default text-danger'; ?>">Drafts</a>
                            <!--</div>-->
                        </div>
                        <!-- FIN AJOUT -->

                    </div>
                    
                    <div class="card-body">
                        <table id="dt-polls" class="table table-bordered table-hover table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>Question</th>
                                    <th class="text-center">Votes</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // --- LOGIQUE DE FILTRE SQL ---
                                $where_clause = "";
                                if (isset($_GET['status'])) {
                                    if ($_GET['status'] == 'published') {
                                        $where_clause = "WHERE active = 'Yes'";
                                    } elseif ($_GET['status'] == 'draft') {
                                        $where_clause = "WHERE active = 'No'";
                                    }
                                }

                                $sql = mysqli_query($connect, "SELECT * FROM polls $where_clause ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    
                                    // Compter les votes
                                    $poll_id = $row['id'];
                                    $q_votes = mysqli_query($connect, "SELECT COUNT(*) as total FROM poll_voters WHERE poll_id='$poll_id'");
                                    $votes_count = mysqli_fetch_assoc($q_votes)['total'];
                                    
                                    // Badge Status
                                    $status_badge = ($row['active'] == 'Yes') 
                                        ? '<span class="badge badge-success">Active</span>' 
                                        : '<span class="badge badge-secondary">Inactive</span>';
                                    
                                    // Toggle button styles
                                    $toggle_icon = ($row['active'] == 'Yes') ? 'fa-eye-slash' : 'fa-eye';
                                    $toggle_btn  = ($row['active'] == 'Yes') ? 'btn-warning' : 'btn-success';

                                    echo '
                                    <tr>
                                        <td>' . $row['id'] . '</td>
                                        <td><strong>' . htmlspecialchars($row['question']) . '</strong></td>
                                        <td class="text-center"><span class="badge badge-info">' . $votes_count . '</span></td>
                                        <td class="text-center align-middle">' . $status_badge . '</td>
                                        <td class="text-center align-middle">
                                            <a href="?toggle_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm ' . $toggle_btn . ' mr-1" title="Toggle Status">
                                                <i class="fas ' . $toggle_icon . '"></i>
                                            </a>
                                            
                                            <a href="?reset_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-info mr-1" onclick="return confirm(\'Reset all votes for this poll?\');" title="Reset Votes">
                                                <i class="fas fa-redo"></i>
                                            </a>

                                            <a href="edit_poll.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this poll and all its data?\');" title="Delete">
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
    $('#dt-polls').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]] 
    });
});
</script>