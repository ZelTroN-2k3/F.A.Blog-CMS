<?php
include "header.php";

// SÉCURITÉ : Seul l'Admin peut accéder aux réglages
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

$msg = "";
$msg_type = "";

// Traitement du formulaire de test
if (isset($_POST['send_test'])) {
    validate_csrf_token();
    
    $test_email = $_POST['test_email'];
    
    if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        // Appel de notre nouvelle fonction
        $subject = "Test SMTP - " . $settings['sitename'];
        $body    = "<h3>Ceci est un email de test.</h3><p>Si vous lisez ceci, votre configuration SMTP fonctionne parfaitement ! 🎉</p><hr><small>Envoyé par F.A Blog CMS</small>";
        
        $response = send_email($test_email, $subject, $body);
        
        if ($response['success']) {
            $msg = "<strong>Succès !</strong> L'email a été envoyé à $test_email. Vérifiez votre boîte de réception (et vos spams).";
            $msg_type = "success";
        } else {
            $msg = "<strong>Erreur :</strong> " . htmlspecialchars($response['message']);
            $msg_type = "danger";
        }
    } else {
        $msg = "Adresse email invalide.";
        $msg_type = "warning";
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-paper-plane"></i> Testeur Email</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
                    <li class="breadcrumb-item active">Test Email</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <?php if ($msg != ""): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Configuration Actuelle</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Protocole</dt>
                            <dd class="col-sm-8"><span class="badge badge-primary"><?php echo strtoupper($settings['mail_protocol']); ?></span></dd>
                            
                            <?php if($settings['mail_protocol'] == 'smtp'): ?>
                                <dt class="col-sm-4">Host</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($settings['smtp_host']); ?></dd>

                                <dt class="col-sm-4">Port</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($settings['smtp_port']); ?></dd>

                                <dt class="col-sm-4">Utilisateur</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($settings['smtp_user']); ?></dd>
                                
                                <dt class="col-sm-4">Chiffrement</dt>
                                <dd class="col-sm-8"><?php echo strtoupper($settings['smtp_enc']); ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">De (From)</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($settings['mail_from_email']); ?></dd>
                        </dl>
                        <div class="mt-3">
                            <a href="settings.php" class="btn btn-sm btn-secondary"><i class="fas fa-cog"></i> Modifier la configuration</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Envoyer un test</h3>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="test_email">Envoyer à l'adresse :</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="test_email" name="test_email" value="<?php echo htmlspecialchars($rowu['email']); ?>" required>
                                </div>
                            </div>
                            <div class="callout callout-warning">
                                <small>Assurez-vous d'avoir sauvegardé vos paramètres SMTP dans la page "Settings" avant de tester.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="send_test" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Envoyer le test maintenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>