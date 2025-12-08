<?php
include "header.php";
require_once "../core/GoogleAuth.php";

$ga = new GoogleAuth();
$current_secret = $rowu['two_factor_secret'];
$is_enabled = $rowu['two_factor_enabled'];

// 1. Générer un nouveau secret si aucun n'existe
if (empty($current_secret)) {
    $current_secret = $ga->createSecret();
    // On le sauvegarde temporairement (inactif)
    mysqli_query($connect, "UPDATE users SET two_factor_secret='$current_secret' WHERE id={$rowu['id']}");
}

// 2. Traitement du formulaire
$msg = "";
if (isset($_POST['enable_2fa'])) {
    validate_csrf_token();
    $code = $_POST['code'];
    
    if ($ga->verifyCode($current_secret, $code)) {
        mysqli_query($connect, "UPDATE users SET two_factor_enabled='Yes' WHERE id={$rowu['id']}");
        $is_enabled = 'Yes';
        
        // Log
        if(function_exists('log_activity')) { log_activity("Security", "Enabled 2FA for user " . $rowu['username']); }
        
        $msg = '<div class="alert alert-success">2FA Enabled successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Invalid Code. Try again.</div>';
    }
}

if (isset($_POST['disable_2fa'])) {
    validate_csrf_token();
    // On demande le mot de passe pour désactiver par sécurité
    $password = $_POST['password'];
    // Vérification simplifiée (à adapter si vous hashz différemment, ex: password_verify)
    // Ici on suppose que $rowu['password'] est le hash
    if (password_verify($password, $rowu['password'])) {
        mysqli_query($connect, "UPDATE users SET two_factor_enabled='No', two_factor_secret=NULL WHERE id={$rowu['id']}");
        $is_enabled = 'No';
        $current_secret = $ga->createSecret(); // Nouveau secret pour la prochaine fois
        mysqli_query($connect, "UPDATE users SET two_factor_secret='$current_secret' WHERE id={$rowu['id']}");
        
        if(function_exists('log_activity')) { log_activity("Security", "Disabled 2FA for user " . $rowu['username']); }
        
        $msg = '<div class="alert alert-warning">2FA Disabled.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Incorrect Password.</div>';
    }
}

// URL du QR Code
$qrCodeUrl = $ga->getQRCodeUrl($settings['sitename'], $current_secret);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-user-shield"></i> Two-Factor Authentication</h1></div>
            <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="dashboard.php">Home</a></li><li class="breadcrumb-item active">2FA</li></ol></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php echo $msg; ?>
                
                <div class="card card-outline <?php echo ($is_enabled == 'Yes') ? 'card-success' : 'card-danger'; ?>">
                    <div class="card-header">
                        <h3 class="card-title">Status: <strong><?php echo ($is_enabled == 'Yes') ? 'ENABLED' : 'DISABLED'; ?></strong></h3>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($is_enabled == 'No'): ?>
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img src="<?php echo $qrCodeUrl; ?>" class="img-thumbnail mb-2" style="max-width:200px;">
                                    <p class="text-muted small">Scan with Google Authenticator</p>
                                </div>
                                <div class="col-md-8">
                                    <h4>Setup Instructions</h4>
                                    <ol>
                                        <li>Install <strong>Google Authenticator</strong> (or Authy) on your phone.</li>
                                        <li>Scan the QR Code.</li>
                                        <li>Enter the 6-digit code below to confirm.</li>
                                    </ol>
                                    
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <div class="form-group">
                                            <label>Verification Code</label>
                                            <input type="text" name="code" class="form-control form-control-lg" placeholder="123456" required autocomplete="off">
                                        </div>
                                        <button type="submit" name="enable_2fa" class="btn btn-success btn-block">Enable 2FA</button>
                                    </form>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">Can't scan? Manual Key: <code><?php echo $current_secret; ?></code></small>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                                <h2>Your account is secure!</h2>
                                <p class="lead">Two-Factor Authentication is currently active.</p>
                                <hr>
                                <div class="text-left mt-4">
                                    <h5>Disable 2FA</h5>
                                    <p class="text-danger small">Warning: This will make your account less secure.</p>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <button type="submit" name="disable_2fa" class="btn btn-danger">Disable 2FA</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>