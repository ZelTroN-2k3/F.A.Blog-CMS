<?php
include "header.php";

// SÉCURITÉ : Seul l'Admin peut accéder aux réglages
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

// Inclusion de la logique de sauvegarde
include "settings/save_logic.php"; 
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cogs"></i> Site Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Site Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="card card-primary card-outline card-tabs">
                        
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-settings" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-general-link" data-toggle="pill" href="#tab-general" role="tab"><i class="fas fa-tools"></i> General</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-layout-link" data-toggle="pill" href="#tab-layout" role="tab"><i class="fas fa-palette"></i> Appearance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-seo-link" data-toggle="pill" href="#tab-seo" role="tab"><i class="fas fa-chart-line"></i> SEO & Social</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-email-link" data-toggle="pill" href="#tab-email" role="tab"><i class="fas fa-envelope"></i> Mail Server</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-comments-link" data-toggle="pill" href="#tab-comments" role="tab"><i class="fas fa-comments"></i> Discussions</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-security-link" data-toggle="pill" href="#tab-security" role="tab"><i class="fas fa-shield-alt"></i> Security & Legal</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-events-link" data-toggle="pill" href="#tab-events" role="tab"><i class="fas fa-gift"></i> Events</a>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="card-body tab-content" id="custom-tabs-settings-content">
                            
                            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                                <?php include "settings/tab_general.php"; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="tab-layout" role="tabpanel">
                                <?php include "settings/tab_layout.php"; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                                <?php include "settings/tab_seo.php"; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="tab-email" role="tabpanel">
                                <?php include "settings/tab_email.php"; ?>
                            </div>

                            <div class="tab-pane fade" id="tab-comments" role="tabpanel">
                                <?php include "settings/tab_comments.php"; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="tab-security" role="tabpanel">
                                <?php include "settings/tab_security.php"; ?>
                                <hr>
                                <?php include "settings/tab_legal.php"; ?>
                            </div>

                            <div class="tab-pane fade" id="tab-events" role="tabpanel">
                                <?php include "settings/tab_events.php"; ?>
                            </div>
                        </div>

                        <input type="hidden" name="active_tab" id="input_active_tab" value="<?php echo isset($_POST['active_tab']) ? htmlspecialchars($_POST['active_tab']) : '#tab-general'; ?>">

                        <div class="card-footer">
                            <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </div>
                        
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</section>
<script>
$(document).ready(function() {
    // 1. Activer l'onglet au chargement si une valeur existe
    var activeTab = $('#input_active_tab').val();
    if(activeTab){
        $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
    }

    // 2. Mettre à jour l'input caché au clic
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // ex: #tab-seo
        $('#input_active_tab').val(target);
    });
});
</script>
<?php include "footer.php"; ?>