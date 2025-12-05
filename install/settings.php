<?php
include "core.php";

// --- TRAITEMENT DU FORMULAIRE ---
if (isset($_POST['submit'])) {
    
    // On stocke les deux emails distincts
    $_SESSION['username']   = $_POST['username'];
    $_SESSION['password']   = $_POST['password'];
    $_SESSION['email']      = $_POST['email'];      // Email de l'Admin
    $_SESSION['site_email'] = $_POST['site_email']; // Email du Site (Settings)
    
    header("Location: done.php");
    exit;
}

head();
?>
    <center><h6>Please provide the following information.</h6></center>
    <br />
    
    <form method="post" action="" class="form-horizontal row-border">
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>Website Settings</strong>
        </div>

        <div class="form-group row">
            <p class="col-sm-3">Site E-Mail Address: </p>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope-open-text"></i></span>
                    <input type="email" name="site_email" class="form-control" placeholder="contact@mysite.com" value="<?php echo htmlspecialchars($_SESSION['site_email'] ?? ''); ?>" required>
                </div>
                <small class="text-muted">This email will be used for site notifications and contact forms.</small>
            </div>
        </div>
        <br>

        <div class="alert alert-primary">
            <i class="fas fa-user-shield"></i> <strong>Administrator Account</strong>
        </div>

        <div class="form-group row">
            <p class="col-sm-3">Admin Username: </p>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin" value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>" required>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <p class="col-sm-3">Admin E-Mail: </p>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="my.personal@email.com" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                </div>
                <small class="text-muted">Used for login and password recovery.</small>
            </div>
        </div>

        <div class="form-group row">
            <p class="col-sm-3">Admin Password: </p>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" id="password-field" name="password" class="form-control" value="<?php echo htmlspecialchars($_SESSION['password'] ?? ''); ?>" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <br />
        <div class="row">
            <div class="col-md-6">
                <a href="index.php" class="btn-secondary btn col-12"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
            <div class="col-md-6">
                <input class="btn-primary btn col-12" type="submit" name="submit" value="Next" />
            </div>
        </div>
    
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Logique pour afficher/masquer le mot de passe ---
    const toggleButton = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password-field');
    const toggleIcon = toggleButton.querySelector('i');

    toggleButton.addEventListener('click', function() {
        // Basculer le type du champ
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Basculer l'icône
        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    });

    // --- 2. Logique pour l'indicateur de chargement ---
    const installForm = document.querySelector('form');
    const loaderOverlay = document.getElementById('loader-overlay');

    installForm.addEventListener('submit', function() {
        // Afficher l'overlay lorsque le formulaire est soumis
        loaderOverlay.style.display = 'flex';
    });
});
</script>

<?php
footer();
?>