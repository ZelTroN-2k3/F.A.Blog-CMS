<?php
// Inclure l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Vérifier si le fichier de configuration existe
$configfile = __DIR__ . '/../config.php';
if (!file_exists($configfile)) {
    echo '<meta http-equiv="refresh" content="0; url=install/index.php" />';
    exit();
}

// Configuration Session
@ini_set("session.gc_maxlifetime", '604800');
@ini_set("session.cookie_lifetime", '604800');
session_start();

// Protection CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include __DIR__ . '/../config.php';

// ------------------------------------------------------------
// --- SYSTÈME DE BANNISSEMENT ---
// ------------------------------------------------------------
$ban_bg_file = 'default.jpg'; 
$q_bg_setting = mysqli_query($connect, "SELECT ban_bg_image FROM settings WHERE id = 1 LIMIT 1");
if ($q_bg_setting && mysqli_num_rows($q_bg_setting) > 0) {
    $row_bg = mysqli_fetch_assoc($q_bg_setting);
    if (!empty($row_bg['ban_bg_image'])) {
        $ban_bg_file = $row_bg['ban_bg_image'];
    }
}

// Modèle HTML Ban (Code réduit pour lisibilité, c'est le même que votre original)
$ban_page_template = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Restricted</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
<style>
    body, html { height: 100%; margin: 0; font-family: 'Roboto', sans-serif; background-image: url('uploads/banned_bg/{{BG_IMAGE}}'); background-size: cover; background-position: center center; background-repeat: no-repeat; background-attachment: fixed; background-color: #343a40; display: flex; align-items: center; justify-content: center; }
    .ban-container { text-align: center; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(10px); padding: 40px; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); max-width: 500px; width: 90%; border-top: 6px solid #dc3545; }
    .icon-container { color: #dc3545; font-size: 80px; margin-bottom: 20px; }
    h1 { color: #343a40; margin: 0 0 10px 0; font-weight: 700; }
    .details-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 6px; text-align: left; font-size: 0.95em; color: #495057; margin-top: 20px;}
</style>
</head>
<body>
    <div class="ban-container">
        <div class="icon-container"><i class="fas fa-shield-alt"></i></div>
        <h1>{{TITLE}}</h1>
        <p>{{MESSAGE}}</p>
        <div class="details-box"><div><strong>{{LABEL}}:</strong> {{TARGET}}</div><div style="margin-top: 10px;"><strong>Reason:</strong> {{REASON}}</div></div>
    </div>
</body>
</html>
HTML;

$visitor_ip = $_SERVER['REMOTE_ADDR'];
$visitor_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 1. Vérifier l'IP
$stmt_ban_ip = mysqli_prepare($connect, "SELECT reason FROM bans WHERE ban_type='ip' AND ban_value=? AND active='Yes' LIMIT 1");
mysqli_stmt_bind_param($stmt_ban_ip, "s", $visitor_ip);
mysqli_stmt_execute($stmt_ban_ip);
$res_ban_ip = mysqli_stmt_get_result($stmt_ban_ip);
if ($ban_row = mysqli_fetch_assoc($res_ban_ip)) {
    die(str_replace(['{{TITLE}}', '{{MESSAGE}}', '{{LABEL}}', '{{TARGET}}', '{{REASON}}', '{{BG_IMAGE}}'], ['Access Denied', 'Your IP address has been blocked.', 'IP Address', htmlspecialchars($visitor_ip), htmlspecialchars($ban_row['reason']), $ban_bg_file], $ban_page_template));
}
mysqli_stmt_close($stmt_ban_ip);

// 2. Vérifier User-Agent
if (!empty($visitor_ua)) {
    $q_ua_bans = mysqli_query($connect, "SELECT ban_value, reason FROM bans WHERE ban_type='user_agent' AND active='Yes'");
    while ($row_ua = mysqli_fetch_assoc($q_ua_bans)) {
        if (stripos($visitor_ua, $row_ua['ban_value']) !== false) {
            die(str_replace(['{{TITLE}}', '{{MESSAGE}}', '{{LABEL}}', '{{TARGET}}', '{{REASON}}', '{{BG_IMAGE}}'], ['Access Restricted', 'Automated traffic detected.', 'User Agent', 'Bot/Spam Signature', htmlspecialchars($row_ua['reason']), $ban_bg_file], $ban_page_template));
        }
    }
}

// 3. Vérifier Utilisateur
if (isset($_SESSION['sec-username'])) {
    $current_user_ban = $_SESSION['sec-username'];
    $stmt_ban_user = mysqli_prepare($connect, "SELECT reason FROM bans WHERE (ban_type='username' OR ban_type='email') AND ban_value=? AND active='Yes' LIMIT 1");
    mysqli_stmt_bind_param($stmt_ban_user, "s", $current_user_ban);
    mysqli_stmt_execute($stmt_ban_user);
    $res_ban_user = mysqli_stmt_get_result($stmt_ban_user);
    if ($ban_row = mysqli_fetch_assoc($res_ban_user)) {
         session_destroy();
         die(str_replace(['{{TITLE}}', '{{MESSAGE}}', '{{LABEL}}', '{{TARGET}}', '{{REASON}}', '{{BG_IMAGE}}'], ['Account Suspended', 'Your account has been suspended.', 'Account', htmlspecialchars($current_user_ban), htmlspecialchars($ban_row['reason']), $ban_bg_file], $ban_page_template));
    }
    mysqli_stmt_close($stmt_ban_user);
}

// --- CHARGEMENT DES PARAMÈTRES ---
$settings = array();
$stmt_settings = mysqli_prepare($connect, "SELECT * FROM settings WHERE id = 1");
if ($stmt_settings) {
    mysqli_stmt_execute($stmt_settings);
    $result_settings = mysqli_stmt_get_result($stmt_settings);
    if (!$result_settings) { die("Erreur critique : Impossible d'obtenir les résultats des paramètres."); }
    $settings = mysqli_fetch_assoc($result_settings);
    mysqli_stmt_close($stmt_settings);
    if (!$settings) { die("Erreur critique : La table des paramètres est vide."); }
} else {
    die("Erreur critique : Impossible de préparer la requête des paramètres.");
}

// --- VÉRIFICATION DU MODE MAINTENANCE ---
if ($settings['maintenance_mode'] == 'On') {
    $is_admin = false;
    if (isset($_SESSION['sec-username'])) {
        $uname = $_SESSION['sec-username'];
        $stmt_admin_check = mysqli_prepare($connect, "SELECT role FROM `users` WHERE username=? AND role='Admin'");
        mysqli_stmt_bind_param($stmt_admin_check, "s", $uname);
        mysqli_stmt_execute($stmt_admin_check);
        $result_admin_check = mysqli_stmt_get_result($stmt_admin_check);
        if (mysqli_num_rows($result_admin_check) > 0) { $is_admin = true; }
        mysqli_stmt_close($stmt_admin_check);
    }
    
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    $is_admin_folder = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false);
    $is_special_admin_login = ($current_script == 'admin.php');

    if (!$is_admin && !$is_admin_folder && !$is_special_admin_login) {

    // --- PAGE DE MAINTENANCE ---
    // CORRECTION : On ne peut pas utiliser get_purifier() ici car functions.php n'est pas encore chargé
    // On utilise une sécurité basique pour le message de maintenance
    $allowed_tags = '<br><p><b><i><strong><em><a><img><ul><ol><li>';
    $safe_message = strip_tags($settings['maintenance_message'], $allowed_tags);
    $page_title = htmlspecialchars($settings['maintenance_title']);
    $sitename = htmlspecialchars($settings['sitename']);
    
    // Gestion de l'image de fond
    $bg_style = 'background: #f4f6f9;'; // Fond gris par défaut
    if (!empty($settings['maintenance_image']) && file_exists($settings['maintenance_image'])) {
        $bg_url = htmlspecialchars($settings['maintenance_image']);
        $bg_style = "background: url('$bg_url') no-repeat center center fixed; background-size: cover;";
    }

        die('
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>' . $page_title . '</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body {
                    ' . $bg_style . '
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    margin: 0;
                }
                /* Ajout d\'un overlay sombre léger si image */
                .bg-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0, 0, 0, 0.4); /* Assombrit légèrement le fond */
                    z-index: 0;
                    ' . (empty($settings['maintenance_image']) ? 'display: none;' : '') . '
                }
                .maintenance-container {
                    position: relative; /* Pour passer au-dessus de l\'overlay */
                    z-index: 1;
                    text-align: center;
                    max-width: 600px;
                    width: 90%;
                    padding: 40px;
                    background: rgba(255, 255, 255, 0.95); /* Fond blanc légèrement transparent */
                    border-radius: 15px;
                    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                    backdrop-filter: blur(5px); /* Effet de flou moderne */
                }
                .maintenance-icon { font-size: 80px; color: #ffc107; margin-bottom: 20px; }
                .site-name { color: #6c757d; font-weight: 600; letter-spacing: 1px; margin-bottom: 30px; text-transform: uppercase; font-size: 0.9rem; }
                h1 { font-weight: 700; color: #343a40; margin-bottom: 20px; }
                .message { font-size: 1.1rem; color: #6c757d; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="bg-overlay"></div>
            
            <div class="maintenance-container">
                <div class="site-name">' . $sitename . '</div>
                <div class="maintenance-icon"><i class="fas fa-tools"></i></div>
                <h1>' . $page_title . '</h1>
                <div class="message">' . $safe_message . '</div>
                <div class="mt-4">
                    <a href="admin.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-lock"></i> Admin Login</a>
                </div>
            </div>
        </body>
        </html>
        ');
        // --- FIN PAGE MAINTENANCE ---
    }
}

// --- DÉFINITION GLOBALE MODE SOMBRE ---
$light_theme_name = $settings['theme'];
$dark_theme_name = "Darkly"; 
$bootstrap_css = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";
$bootswatch_base_url = "https://bootswatch.com/5/";
$light_theme_url = ($light_theme_name == "Bootstrap 5") ? $bootstrap_css : $bootswatch_base_url . strtolower($light_theme_name) . "/bootstrap.min.css";
$dark_theme_url = $bootswatch_base_url . strtolower($dark_theme_name) . "/bootstrap.min.css";

// --- VÉRIFICATION UTILISATEUR ---
$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);

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
        
        // --- NOUVEAU : TRACKING ACTIVITÉ ---
        // Modification : On utilise votre colonne existante 'last_activity'
        $stmt_track = mysqli_prepare($connect, "UPDATE users SET last_activity = NOW() WHERE id = ?");
        
        if ($stmt_track) {
            mysqli_stmt_bind_param($stmt_track, "i", $rowu['id']);
            mysqli_stmt_execute($stmt_track);
            mysqli_stmt_close($stmt_track);
        }
        // ---------------------------------------------------------------------
    }
    mysqli_stmt_close($stmt_user_check);
}
?>