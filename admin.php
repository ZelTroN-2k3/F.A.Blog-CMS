<?php
// Utilise le coeur admin, plus léger et sans HTML
include "core-admin.php"; 
require_once "core/GoogleAuth.php"; // Inclusion de la librairie 2FA

// Si déjà loggé en admin, redirection vers le dashboard
if ($logged == 'Yes' && $rowu['role'] == 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=admin/dashboard.php">';
    exit;
}

// Initialisation du Rate Limiting
if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
if (!isset($_SESSION['login_lockout_time'])) { $_SESSION['login_lockout_time'] = 0; }

$error = 0;
$message = '';
$step_2fa = false; // Par défaut, on n'est pas à l'étape du code

// --- Vérification du blocage ---
$is_locked_out = false;
if ($_SESSION['login_lockout_time'] > time()) {
    $is_locked_out = true;
    $time_remaining = ceil(($_SESSION['login_lockout_time'] - time()) / 60);
    $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> You failed too many times. Please try again in ' . $time_remaining . ' minute(s).</div>';
    $error = 1;
}

// 1. TRAITEMENT DU FORMULAIRE (Username/Pass)
if (isset($_POST['signin']) && !$is_locked_out) {
    validate_csrf_token();
    
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    
    // Récupérer hash, rôle ET 2FA
    $stmt = mysqli_prepare($connect, "SELECT id, username, password, role, two_factor_enabled, two_factor_secret FROM `users` WHERE `username`=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user_row = mysqli_fetch_assoc($result);

        if (password_verify($password_plain, $user_row['password'])) {
            
            // VÉRIFIER LE RÔLE
            if ($user_row['role'] == 'Admin') {
                
                // --- VÉRIFICATION 2FA ---
                if ($user_row['two_factor_enabled'] == 'Yes') {
                    // 2FA Actif : On stocke en temp et on demande le code
                    $_SESSION['temp_2fa_user_id'] = $user_row['id'];
                    $_SESSION['temp_2fa_username'] = $user_row['username'];
                    $_SESSION['temp_2fa_secret'] = $user_row['two_factor_secret'];
                    $step_2fa = true; 
                } else {
                    // Pas de 2FA : Connexion Directe
                    doLogin($user_row['username']);
                }

            } else {
                handleFail("Access restricted to administrators only.");
            }

        } else {
            handleFail("Invalid credentials.");
        }
    } else {
        handleFail("Invalid credentials.");
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
            doLogin($_SESSION['temp_2fa_username']);
            
            // Nettoyage session temporaire
            unset($_SESSION['temp_2fa_user_id']);
            unset($_SESSION['temp_2fa_username']);
            unset($_SESSION['temp_2fa_secret']);
        } else {
            $message = '<div class="alert alert-danger">Invalid 2FA Code.</div>';
            $step_2fa = true; // On reste sur le formulaire de code
        }
    } else {
        $message = '<div class="alert alert-danger">Session expired. Please sign in again.</div>';
    }
}

// Fonction Helper : Connexion
function doLogin($uname) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_time'] = 0;
    $_SESSION['sec-username'] = $uname;
    
    echo '<div class="alert alert-success"><i class="fas fa-check"></i> Login successful! Redirecting...</div>';
    echo '<meta http-equiv="refresh" content="1; url=admin/dashboard.php">';
    exit;
}

// Fonction Helper : Échec
function handleFail($msg_text) {
    global $message, $error, $is_locked_out;
    $_SESSION['login_attempts']++;
    $attempts_remaining = 5 - $_SESSION['login_attempts'];
    
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_lockout_time'] = time() + 300;
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Account locked for 5 minutes.</div>';
        $is_locked_out = true;
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . $msg_text . ' (' . $attempts_remaining . ' attempts left)</div>';
    }
    $error = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - <?php echo htmlspecialchars($settings['sitename']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet"/>
    <style>
        html, body { height: 100%; }
        body { display: flex; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #f5f5f5; }
        .form-signin { width: 100%; max-width: 400px; padding: 15px; margin: auto; }
        .letter-spacing-2 { letter-spacing: 2px; font-weight: bold; }
    </style>
</head>
<body class="text-center">
    
    <main class="form-signin">
        
        <?php if ($step_2fa): ?>
            <form action="admin.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <i class="fas fa-shield-alt fa-3x mb-4 text-primary"></i>
                <h1 class="h3 mb-3 fw-normal">Security Check</h1>
                <p class="text-muted">Enter code from Authenticator</p>
                <?php echo $message; ?>

                <div class="form-floating mb-3">
                    <input type="text" name="code" class="form-control text-center letter-spacing-2" id="floatingCode" placeholder="000 000" required autocomplete="off" autofocus>
                    <label for="floatingCode">Verification Code</label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit" name="verify_2fa">Verify</button>
            </form>

        <?php else: ?>
            <form action="admin.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <i class="fas fa-user-shield fa-3x mb-4 text-danger"></i>
                <h1 class="h3 mb-3 fw-normal">Admin Login</h1>
                <p class="text-muted">Restricted access (Maintenance)</p>
                <?php echo $message; ?>

                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required <?php if ($is_locked_out) echo 'disabled'; ?>>
                    <label for="floatingInput"><i class="fas fa-user"></i> Username</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required <?php if ($is_locked_out) echo 'disabled'; ?>>
                    <label for="floatingPassword"><i class="fas fa-key"></i> Password</label>
                </div>
                <button class="w-100 btn btn-lg btn-danger" type="submit" name="signin" <?php if ($is_locked_out) echo 'disabled'; ?>>
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
                <p class="mt-5 mb-3 text-muted">&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($settings['sitename']); ?></p>
            </form>
            <div class="text-center mt-4">
                <a href="index.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Return to site</a>
            </div>
        <?php endif; ?>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>