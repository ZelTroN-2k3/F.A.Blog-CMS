<?php
include "header.php";

// Validation ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=bans.php">';
    exit;
}

$ban_id = (int)$_GET['id'];

// Récupération données
$stmt = mysqli_prepare($connect, "SELECT * FROM bans WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $ban_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ban_data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$ban_data) {
    echo '<div class="alert alert-danger m-3">Ban rule not found.</div>';
    include "footer.php";
    exit;
}

// Traitement
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $type  = $_POST['ban_type'];
    $value = trim($_POST['ban_value']);
    $reason = $_POST['reason'];
    $active = $_POST['active'];

    if (empty($value)) {
        echo '<div class="alert alert-danger m-3">Value is required.</div>';
    } else {
        $stmt = mysqli_prepare($connect, "UPDATE bans SET ban_type=?, ban_value=?, reason=?, active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $type, $value, $reason, $active, $ban_id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo '<div class="alert alert-success m-3">Ban updated successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=bans.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Ban Rule</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="bans.php">Bans</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Rule Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="ban_type" class="form-control">
                                            <option value="ip" <?php if($ban_data['ban_type']=='ip') echo 'selected'; ?>>IP Address</option>
                                            <option value="username" <?php if($ban_data['ban_type']=='username') echo 'selected'; ?>>Username</option>
                                            <option value="email" <?php if($ban_data['ban_type']=='email') echo 'selected'; ?>>E-Mail</option>
                                            <option value="user_agent" <?php if($ban_data['ban_type']=='user_agent') echo 'selected'; ?>>User Agent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Value</label>
                                        <input type="text" name="ban_value" class="form-control" value="<?php echo htmlspecialchars($ban_data['ban_value']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Reason</label>
                                <textarea name="reason" class="form-control" rows="4"><?php echo htmlspecialchars($ban_data['reason']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Active Rule?</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($ban_data['active'] == 'Yes') echo 'selected'; ?>>Yes (Banned)</option>
                                    <option value="No" <?php if($ban_data['active'] == 'No') echo 'selected'; ?>>No (Inactive)</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Rule
                            </button>
                            <a href="bans.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>