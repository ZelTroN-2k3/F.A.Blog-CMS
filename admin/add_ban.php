<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $type  = $_POST['ban_type'];
    $value = trim($_POST['ban_value']);
    $reason = $_POST['reason'];
    $active = $_POST['active']; // Ajout du champ active (manquant dans l'original mais logique)

    // Valeur par défaut si 'active' n'est pas envoyé (cas rare)
    if(empty($active)) $active = 'Yes';

    if (empty($value)) {
        echo '<div class="alert alert-danger m-3">Value is required.</div>';
    } else {
        // Sécurité : Empêcher de se bannir soi-même
        $my_ip = $_SERVER['REMOTE_ADDR'];
        $my_username = $user['username']; 
        
        if (($type == 'ip' && $value == $my_ip) || ($type == 'username' && $value == $my_username)) {
             echo '<div class="alert alert-danger m-3">Safety Warning: You cannot ban yourself!</div>';
        } else {
            // Requête
            $stmt = mysqli_prepare($connect, "INSERT INTO bans (ban_type, ban_value, reason, active, created_at) VALUES (?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, "ssss", $type, $value, $reason, $active);
            
            if(mysqli_stmt_execute($stmt)) {
                echo '<div class="alert alert-success m-3">Ban added successfully! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="1; url=bans.php">';
                exit;
            } else {
                echo '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-slash"></i> Add Ban Rule</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="bans.php">Bans</a></li>
                    <li class="breadcrumb-item active">Add</li>
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
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Ban Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Ban Type</label>
                                        <select name="ban_type" class="form-control">
                                            <option value="ip">IP Address</option>
                                            <option value="username">Username</option>
                                            <option value="email">E-Mail Address</option>
                                            <option value="user_agent">User-Agent (Bot)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Value to Ban</label>
                                        <input type="text" name="ban_value" class="form-control" placeholder="e.g. 192.168.1.1 or SpammerUser" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Reason (Internal Note)</label>
                                <textarea name="reason" class="form-control" rows="4" placeholder="Why are you banning this user/bot?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" selected>Active (Enforce Ban)</option>
                                    <option value="No">Inactive (Lift Ban)</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info p-2" style="font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Tip:</strong> Banning an IP prevents access to the entire site. Banning a username only prevents login.
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-ban"></i> Apply Ban
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