<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>
    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-header"><i class="fas fa-envelope"></i> Unsubscribe</div>
                <div class="card-body">
<?php
if (!isset($_GET['email'])) {
    echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
    exit;
} else {
	$email = $_GET['email'];

    // Requête préparée pour vérifier l'existence
    $stmt_check = mysqli_prepare($connect, "SELECT email FROM `newsletter` WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        mysqli_stmt_close($stmt_check);
        echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
        exit;
    } else {
        mysqli_stmt_close($stmt_check);
        
        // Requête préparée pour la suppression
        $stmt_delete = mysqli_prepare($connect, "DELETE FROM `newsletter` WHERE email=?");
        mysqli_stmt_bind_param($stmt_delete, "s", $email);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        echo '<div class="alert alert-primary">You were unsubscribed successfully.</div>';
    }
}
?>
                </div>
        </div>
    </div>
<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>