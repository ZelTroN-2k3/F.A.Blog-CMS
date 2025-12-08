<?php
// admin/includes/dashboard_admin.php
// Ce fichier est le chef d'orchestre. Il appelle les sous-modules.
?>

<?php include "dash_kpi.php"; ?>

<?php include "dash_analytics.php"; ?>

<div class="row">
    <div class="col-lg-8">
        <?php include "dash_tasks.php"; ?>
    </div>

    <div class="col-lg-4">
        <?php include "dash_recent.php"; ?>
    </div>
</div>