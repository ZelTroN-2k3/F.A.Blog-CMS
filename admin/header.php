<?php
// -------------------------------------------------------------------------
// header.php
// Structure principale de l'admin (Head, Navbar, inclusion Sidebar)
// -------------------------------------------------------------------------

// 1. Charger toute la logique (Sécurité + Calcul des Badges)
include "includes/header_logic.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>F.A Blog - Admin Panel</title>
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <meta name="author" content="ZelTroN2k3_WEB" />
    <link rel="shortcut icon" href="<?php echo $settings['site_url']; ?>/assets/img/favicon.png" />
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>/admin/css/admin-header.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="assets/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/adminlte/plugins/summernote/summernote-bs4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <script src="assets/adminlte/plugins/jquery/jquery.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?php echo $settings['site_url']; ?>" class="nav-link" target="_blank"><i class="fas fa-eye"></i> Visit Site</a>
            </li>
            
            <?php if ($user['role'] == 'Admin') { ?>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="test_email.php" class="nav-link <?php echo ($current_page == 'test_email.php') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Test Email
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="logs.php" class="nav-link <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-history"></i> Activity Logs
                    </a>
                </li> 
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="sync_files.php" class="nav-link <?php echo ($current_page == 'sync_files.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-sync"></i> Sync-Files
                    </a>
                </li> 
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="security_2fa.php" class="nav-link <?php echo ($current_page == 'security_2fa.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-shield"></i> Two-Factor Auth
                    </a>
                </li>
            <?php } ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../logout" role="button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    <?php include "includes/sidebar.php"; ?>

    <div class="content-wrapper">