<?php
// Inclure l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// --- Vérification du fichier de configuration ---
$configfile = __DIR__ . '/config.php';
if (!file_exists($configfile)) {
    // Si config.php n'existe pas, on ne peut rien faire.
    die("Critical error: config.php file not found. Please start the installation.");
}

// --- Démarrage de la session ---
@ini_set( "session.gc_maxlifetime", '604800');
@ini_set( "session.cookie_lifetime", '604800');
session_start();

// --- Protection CSRF ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inclure la configuration (connexion BDD)
include "config.php";

// --- Chargement des paramètres ---
$settings = array();
$stmt_settings = mysqli_prepare($connect, "SELECT * FROM settings WHERE id = 1");
if ($stmt_settings) {
    mysqli_stmt_execute($stmt_settings);
    $result_settings = mysqli_stmt_get_result($stmt_settings);
    if ($result_settings) {
        $settings = mysqli_fetch_assoc($result_settings);
    }
    mysqli_stmt_close($stmt_settings);
    if (!$settings) {
         die("Critical error: Unable to load site settings.");
    }
} else {
    die("Critical error: Unable to prepare settings query.");
}

// --- Nettoyage des données (minimal) ---
$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);

// --- Vérification de connexion ---
if (!isset($_SESSION['sec-username'])) {
    $logged = 'No';
} else {
    $username = $_SESSION['sec-username'];
    $stmt_user_check = mysqli_prepare($connect, "SELECT * FROM `users` WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt_user_check, "s", $username);
    mysqli_stmt_execute($stmt_user_check);
    $querych = mysqli_stmt_get_result($stmt_user_check);
    
    if (mysqli_num_rows($querych) == 0) {
        $logged = 'No';
        unset($_SESSION['sec-username']);
    } else {
        $rowu   = mysqli_fetch_assoc($querych);
        $logged = 'Yes';
    }
    mysqli_stmt_close($stmt_user_check);
}

// --- Fonction de validation CSRF ---
function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Validation error (CSRF token). The session may have expired. Please reload the page.');
    }
}

// On n'inclut PAS la vérification du mode maintenance ici, 
// car ce fichier n'est utilisé que par admin.php (déjà exempté).
// On n'inclut PAS les fonctions head() et footer().
?>