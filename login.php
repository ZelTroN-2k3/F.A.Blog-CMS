<?php
include "core.php";
require_once "core/GoogleAuth.php"; // Inclusion de la librairie 2FA

head();

// --- FONCTION LOG ACTIVITY (Version Locale pour login.php) ---
if (!function_exists('log_activity')) {
    function log_activity($user_id, $action, $details) {
        global $connect;
        $ip = $_SERVER['REMOTE_ADDR'];
        $action = strip_tags($action);
        $details = strip_tags($details);
        
        $stmt = mysqli_prepare($connect, "INSERT INTO activity_logs (user_id, action_type, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $details, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
// -------------------------------------------------------------

// Redirection si déjà connecté
if ($logged == 'Yes') {
    echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
    exit;
}

// Gestion de la Sidebar Gauche
if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// --- Initialisation du Rate Limiting (Sécurité Brute Force) ---
if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
if (!isset($_SESSION['login_lockout_time'])) { $_SESSION['login_lockout_time'] = 0; }

$error_login = '';
$error_register = '';
$success_register = '';
$step_2fa = false; // Variable pour afficher le formulaire 2FA

// ============================================================
// LOGIQUE DE CONNEXION (SIGN IN)
// ============================================================
$is_locked_out = false;
if ($_SESSION['login_lockout_time'] > time()) {
    $is_locked_out = true;
    $time_remaining = ceil(($_SESSION['login_lockout_time'] - time()) / 60);
    $error_login = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Too many failed attempts. Please try again in ' . $time_remaining . ' minute(s).</div>';
}

// 1. TRAITEMENT DU FORMULAIRE DE CONNEXION (Username/Pass)
if (isset($_POST['signin']) && !$is_locked_out) {
    validate_csrf_token();
    
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    
    // Récupérer le hash ET les infos 2FA
    $stmt = mysqli_prepare($connect, "SELECT id, username, password, two_factor_enabled, two_factor_secret FROM `users` WHERE `username`=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user_row = mysqli_fetch_assoc($result);
        
        if (password_verify($password_plain, $user_row['password'])) {
            
            // --- VÉRIFICATION 2FA ---
            if ($user_row['two_factor_enabled'] == 'Yes') {
                // On ne connecte PAS tout de suite. On stocke l'ID en session temporaire.
                $_SESSION['temp_2fa_user_id'] = $user_row['id'];
                $_SESSION['temp_2fa_username'] = $user_row['username'];
                $_SESSION['temp_2fa_secret'] = $user_row['two_factor_secret'];
                $step_2fa = true; // On active l'affichage du formulaire 2FA
            } else {
                // Pas de 2FA : Connexion Directe
                doLogin($user_row['id'], $user_row['username']);
            }
            
        } else {
            handleLoginFail();
        }
    } else {
        handleLoginFail();
    }
}

// 2. TRAITEMENT DU CODE 2FA
if (isset($_POST['verify_2fa'])) {
    validate_csrf_token();
    
    if (isset($_SESSION['temp_2fa_user_id'])) {
        $code = $_POST['code'];
        $secret = $_SESSION['temp_2fa_secret'];
        
        $ga = new GoogleAuth();
        if ($ga->verifyCode($secret, $code)) {
            // Code OK : Connexion finale
            doLogin($_SESSION['temp_2fa_user_id'], $_SESSION['temp_2fa_username']);
            
            // Nettoyage session temporaire
            unset($_SESSION['temp_2fa_user_id']);
            unset($_SESSION['temp_2fa_username']);
            unset($_SESSION['temp_2fa_secret']);
        } else {
            $error_login = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Invalid Code.</div>';
            $step_2fa = true; // On reste sur le formulaire de code
        }
    } else {
        $error_login = '<div class="alert alert-danger">Session expired. Please sign in again.</div>';
    }
}

// --- FONCTIONS HELPER ---

function doLogin($uid, $uname) {
    global $connect;
    // Succès : Reset des tentatives
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_time'] = 0;
    $_SESSION['sec-username'] = $uname;
    
    // Log
    log_activity($uid, "Login", "User logged in successfully");

    echo '<meta http-equiv="refresh" content="0; url=profile">';
    exit;
}

function handleLoginFail() {
    global $is_locked_out, $error_login;
    // Échec : Incrémenter tentatives
    $_SESSION['login_attempts']++;
    $attempts_left = 5 - $_SESSION['login_attempts'];
    
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_lockout_time'] = time() + 300; // Bloquer 5 min
        $error_login = '<div class="alert alert-danger"><i class="fas fa-lock"></i> Too many failures. Account locked for 5 minutes.</div>';
        $is_locked_out = true;
    } else {
        $error_login = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Incorrect username or password. (' . $attempts_left . ' attempts left)</div>';
    }
}

// ============================================================
// LOGIQUE D'INSCRIPTION (REGISTER)
// ============================================================
if (isset($_POST['register'])) {
    validate_csrf_token();
    
    $reg_username = strip_tags(trim($_POST['reg_username']));
    $reg_email    = filter_var($_POST['reg_email'], FILTER_SANITIZE_EMAIL);
    $reg_password = $_POST['reg_password'];
    $captcha      = $_POST['g-recaptcha-response'] ?? '';
    
    // Validation Captcha
    $captcha_valid = false;
    if (!empty($settings['gcaptcha_secretkey']) && !empty($captcha)) {
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($settings['gcaptcha_secretkey']) . '&response=' . urlencode($captcha);
        $response = file_get_contents($url);
        $keys = json_decode($response, true);
        if ($keys["success"]) { $captcha_valid = true; }
    } elseif (empty($settings['gcaptcha_secretkey'])) {
        $captcha_valid = true; // Pas de captcha configuré
    }

    if (!$captcha_valid) {
        $error_register = '<div class="alert alert-danger">Please complete the Captcha verification.</div>';
    } else {
        // Vérifier Username
        $stmt_u = mysqli_prepare($connect, "SELECT id FROM `users` WHERE username=?");
        mysqli_stmt_bind_param($stmt_u, "s", $reg_username);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_store_result($stmt_u);
        $user_exist = mysqli_stmt_num_rows($stmt_u);
        mysqli_stmt_close($stmt_u);

        // Vérifier Email
        $stmt_e = mysqli_prepare($connect, "SELECT id FROM `users` WHERE email=?");
        mysqli_stmt_bind_param($stmt_e, "s", $reg_email);
        mysqli_stmt_execute($stmt_e);
        mysqli_stmt_store_result($stmt_e);
        $email_exist = mysqli_stmt_num_rows($stmt_e);
        mysqli_stmt_close($stmt_e);

        if ($user_exist > 0) {
            $error_register = '<div class="alert alert-warning">This Username is already taken.</div>';
        } elseif ($email_exist > 0) {
            $error_register = '<div class="alert alert-warning">This E-Mail is already registered.</div>';
        } else {
            // Création du compte
            $password_hashed = password_hash($reg_password, PASSWORD_DEFAULT);
            $avatar_url = 'assets/img/avatar.png'; 

            // Insertion Utilisateur
            $stmt_ins = mysqli_prepare($connect, "INSERT INTO `users` (`username`, `password`, `email`, `avatar`, `role`) VALUES (?, ?, ?, ?, 'User')");
            mysqli_stmt_bind_param($stmt_ins, "ssss", $reg_username, $password_hashed, $reg_email, $avatar_url);
            
            if (mysqli_stmt_execute($stmt_ins)) {
                // Récupérer l'ID créé pour le log
                $new_user_id = mysqli_insert_id($connect);

                // --- AJOUT LOG ---
                log_activity($new_user_id, "Register", "New user registered: " . $reg_username);
                // -----------------

                // Insertion Newsletter
                $stmt_news = mysqli_prepare($connect, "INSERT INTO `newsletter` (`email`) VALUES (?)");
                mysqli_stmt_bind_param($stmt_news, "s", $reg_email);
                mysqli_stmt_execute($stmt_news);
                
                // Envoi Email
                $subject = 'Welcome to ' . $settings['sitename'];
                $message = "<h2>Welcome to {$settings['sitename']}</h2><p>You have successfully registered.</p><p>Username: <b>{$reg_username}</b></p>";
                $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nFrom: {$settings['email']}";
                @mail($reg_email, $subject, $message, $headers);

                // Auto-Login
                $_SESSION['sec-username'] = $reg_username;
                echo '<meta http-equiv="refresh" content="0;url=profile">';
                exit;
            } else {
                $error_register = '<div class="alert alert-danger">Database error. Please try again.</div>';
            }
        }
    }
}
?>

<div class="col-md-8 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-users"></i> Member Area
        </div>
        <div class="card-body">
            <div class="row">
                
                <div class="col-md-6 mb-4 border-end-md">
                    <h4 class="mb-4 text-primary"><i class="fas fa-sign-in-alt"></i> Sign In</h4>
                    
                    <?php if (!$step_2fa): // --- MODE NORMAL (LOGIN) --- ?>
                        
                        <div class="d-grid gap-2 mb-4">
                            <a href="social_callback.php?provider=Google" class="btn btn-outline-danger shadow-sm">
                                <i class="fab fa-google me-2"></i> Sign in with Google
                            </a>
                        </div>

                        <div class="position-relative mb-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle px-2 bg-white text-muted small">OR</span>
                        </div>

                        <?php echo $error_login; ?>

                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="username" class="form-control" required <?php if ($is_locked_out) echo 'disabled'; ?>>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" required <?php if ($is_locked_out) echo 'disabled'; ?>>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="signin" class="btn btn-primary" <?php if ($is_locked_out) echo 'disabled'; ?>>
                                    Login
                                </button>
                            </div>
                        </form>

                    <?php else: // --- MODE 2FA (CODE) --- ?>

                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt fa-2x float-start me-3"></i>
                            <strong>Security Verification</strong><br>
                            Please enter the 6-digit code from your Authenticator app to continue.
                        </div>
                        
                        <?php echo $error_login; ?>

                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Verification Code</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" name="code" class="form-control text-center letter-spacing-2" placeholder="000 000" required autocomplete="off" autofocus maxlength="6" style="letter-spacing: 5px; font-weight: bold;">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="verify_2fa" class="btn btn-primary btn-lg shadow-sm">
                                    Verify & Login
                                </button>
                            </div>
                        </form>

                    <?php endif; ?>

                </div>

                <div class="col-md-6">
                    <h4 class="mb-4 text-success"><i class="fas fa-user-plus"></i> Create Account</h4>
                    
                    <p class="text-muted small mb-3">Join our community to comment, vote and share your own articles!</p>

                    <?php echo $error_register; ?>

                    <form action="" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Choose Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="reg_username" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="reg_email" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="reg_password" class="form-control" required>
                            </div>
                        </div>

                        <?php if(!empty($settings['gcaptcha_sitekey'])): ?>
                        <div class="mb-3">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($settings['gcaptcha_sitekey']); ?>"></div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-success">
                                Register Now
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>