<?php
include "core.php";

// Définir le titre de la page pour le SEO (utilisé dans head())
$pagetitle = "Newsletter";
$description = "Abonnez-vous à notre newsletter pour recevoir les dernières actualités directement dans votre boîte mail.";

head();

// Affichage de la barre latérale si elle est configurée à gauche
if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>

<div class="col-md-8 mb-4">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-envelope-open-text me-2"></i> Newsletter
        </div>
        <div class="card-body p-4 p-md-5 text-center">
            
            <div class="mb-4">
                <span class="d-inline-block p-3 rounded-circle bg-light text-primary mb-3">
                    <i class="fas fa-paper-plane fa-3x"></i>
                </span>
                <h2 class="fw-bold">Let's stay connected!</h2>
                <p class="text-muted">Subscribe to our mailing list to receive the latest updates, new articles, and exclusive offers directly in your inbox.</p>
            </div>

            <?php
            // --- TRAITEMENT DU FORMULAIRE ---
            if (isset($_POST['subscribe'])) {
                // Protection CSRF (fonction définie dans core.php)
                validate_csrf_token();

                // Nettoyage de l'email
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

                // Validation
                if (empty($email)) {
                    echo '<div class="alert alert-danger fade show"><i class="fas fa-exclamation-circle me-2"></i> Please enter an email address.</div>';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo '<div class="alert alert-danger fade show"><i class="fas fa-at me-2"></i> The email address is not valid.</div>';
                } else {
                    // Vérifier si l'email existe déjà (Requête préparée)
                    $stmt_check = mysqli_prepare($connect, "SELECT email FROM `newsletter` WHERE email=? LIMIT 1");
                    mysqli_stmt_bind_param($stmt_check, "s", $email);
                    mysqli_stmt_execute($stmt_check);
                    $result_check = mysqli_stmt_get_result($stmt_check);

                    if (mysqli_num_rows($result_check) > 0) {
                        echo '<div class="alert alert-warning fade show"><i class="fas fa-info-circle me-2"></i> This email address is already registered.</div>';
                    } else {
                        // Insertion en base de données
                        // Note: On suit la structure vue dans core.php (juste le champ email)
                        $stmt_insert = mysqli_prepare($connect, "INSERT INTO `newsletter` (email) VALUES (?)");
                        mysqli_stmt_bind_param($stmt_insert, "s", $email);
                        
                        if (mysqli_stmt_execute($stmt_insert)) {
                            echo '<div class="alert alert-success fade show"><i class="fas fa-check-circle me-2"></i> Congratulations! You are now subscribed to our newsletter.</div>';
                        } else {
                            echo '<div class="alert alert-danger fade show"><i class="fas fa-times-circle me-2"></i> An error occurred. Please try again later.</div>';
                        }
                        mysqli_stmt_close($stmt_insert);
                    }
                    mysqli_stmt_close($stmt_check);
                }
            }
            ?>

            <form action="" method="POST" class="mt-4 mx-auto" style="max-width: 500px;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Your email address..." required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="subscribe" class="btn btn-primary btn-lg rounded-pill shadow-sm hover-effect">
                        <i class="fas fa-check me-2"></i> Subscribe
                    </button>
                </div>

                <p class="text-muted small mt-3">
                    <i class="fas fa-lock me-1"></i> We respect your privacy. You can unsubscribe at any time.
                </p>
            </form>

        </div>
    </div>
</div>

<?php
// Affichage de la barre latérale si elle est configurée à droite
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>