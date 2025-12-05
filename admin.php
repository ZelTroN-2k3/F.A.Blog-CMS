<?php
// Utilise le coeur admin, plus léger et sans HTML
include "core-admin.php"; 

// Si déjà loggé en admin, redirection vers le dashboard
if ($logged == 'Yes' && $rowu['role'] == 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=admin/dashboard.php">';
    exit;
}

// Initialisation du Rate Limiting (copié de login.php)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['login_lockout_time'])) {
    $_SESSION['login_lockout_time'] = 0;
}

$error = 0;
$message = '';

// --- Vérification du blocage (copié de login.php) ---
$is_locked_out = false;
if ($_SESSION['login_lockout_time'] > time()) {
    $is_locked_out = true;
    $time_remaining = ceil(($_SESSION['login_lockout_time'] - time()) / 60);
    $message = '
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> You failed too many times. Please try again in ' . $time_remaining . ' minute(s).
    </div>';
    $error = 1;
}
// --- Fin Vérification ---

if (isset($_POST['signin']) && !$is_locked_out) {
    
    validate_csrf_token(); // Sécurité CSRF
    
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    
    // 1. Récupérer le hash ET le rôle
    $stmt = mysqli_prepare($connect, "SELECT username, password, role FROM `users` WHERE `username`=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user_row = mysqli_fetch_assoc($result);
        $hashed_password = $user_row['password'];

        // 2. Vérifier le mot de passe
        if (password_verify($password_plain, $hashed_password)) {
            
            // 3. VÉRIFIER LE RÔLE
            if ($user_row['role'] == 'Admin') {
                // Le mot de passe est correct ET c'est un admin
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lockout_time'] = 0;
                
                $_SESSION['sec-username'] = $username;
                $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Login successful! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="2; url=admin/dashboard.php">';
            
            } else {
                // Mot de passe correct, MAIS ce n'est pas un admin
                $_SESSION['login_attempts']++; // Compte comme un échec
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Access restricted to administrators only.</div>';
                $error = 1;
            }

        } else {
            // Mot de passe incorrect
            $_SESSION['login_attempts']++;
            $attempts_remaining = 5 - $_SESSION['login_attempts'];
            
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_lockout_time'] = time() + 300; // 5 min
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> You have failed 5 times. Please try again in 5 minutes.</div>';
            } else {
                 $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Invalid credentials. ' . $attempts_remaining . ' attempt(s) remaining.</div>';
            }
            $error = 1;
        }
    } else {
        // Utilisateur non trouvé
        $_SESSION['login_attempts']++;
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Invalid credentials.</div>';
        $error = 1;
    }
    
    // Gérer le blocage si la limite est atteinte CE tour-ci
    if ($_SESSION['login_attempts'] >= 5 && !$is_locked_out) {
         $_SESSION['login_lockout_time'] = time() + 300;
         $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> You have failed 5 times. Please try again in 5 minutes.</div>';
         $is_locked_out = true; // Bloquer le formulaire immédiatement
    }
}

// --- DÉBUT HTML POUR LE FORMULAIRE DE CONNEXION ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - <?php echo htmlspecialchars($settings['sitename']); ?></title>
    
    <link id="theme-link" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" type="text/css" rel="stylesheet"/>
    
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
        .form-signin {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>
<body class="text-center">
    
    <main class="form-signin">
        <form action="admin.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <i class="fas fa-user-shield fa-3x mb-4 text-danger"></i>
            <h1 class="h3 mb-3 fw-normal">Admin Login</h1>
            <p class="text-muted">Restricted access (Maintenance)</p>

            <?php echo $message; // Affiche les erreurs ou succès ?>

            <div class="form-floating mb-3">
                <input type="username" name="username" class="form-control" id="floatingInput" placeholder="Username" required <?php if ($is_locked_out) echo 'disabled'; ?>>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>