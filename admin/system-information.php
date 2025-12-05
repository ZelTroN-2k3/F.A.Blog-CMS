<?php
include "header.php";

// L'administrateur seul peut voir cette page
if ($user['role'] != "Admin") {
    // Rediriger vers le tableau de bord
    header('Location: dashboard.php');
    exit;
}

// --- Variable de version ---
// $faBlog_version = "x.x.x"; // Définie dans config.php

// ------------------------------------------------------------
// --- LOGIQUE POUR LES INFORMATIONS SYSTÈME ---
// ------------------------------------------------------------

// --- Infos Serveur ---
$server_domain = $_SERVER['SERVER_NAME'];
$server_ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname($_SERVER['SERVER_NAME']); 
$server_os = php_uname('s') . ' ' . php_uname('r'); // OS + Version
$server_software = $_SERVER['SERVER_SOFTWARE'];
$server_port = $_SERVER['SERVER_PORT'];
$http_protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'N/A';
$gateway_interface = $_SERVER['GATEWAY_INTERFACE'] ?? 'N/A';

// --- 2. Espace Disque ---
$disk_total = disk_total_space(".");
$disk_free  = disk_free_space(".");
$disk_used  = $disk_total - $disk_free;
$disk_percent = sprintf('%.2f',($disk_used / $disk_total) * 100);

// Fonction locale de formatage (si non dispo dans functions.php)
if (!function_exists('format_size')) {
    function format_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// --- 3. Infos Base de Données (Taille & Version) ---
$php_version = phpversion();
$db_version_query = mysqli_query($connect, "SELECT VERSION() as version");
$db_version = mysqli_fetch_assoc($db_version_query)['version'];

// Récupérer le nom de la BDD actuelle
$db_name_q = mysqli_query($connect, "SELECT DATABASE()");
$row_db = mysqli_fetch_row($db_name_q);
$current_db_name = $row_db[0];

// A. Calculer la taille (Méthode simple sans GROUP BY)
$q_size = mysqli_query($connect, "
    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
    FROM information_schema.tables 
    WHERE table_schema = '$current_db_name'
");

if ($q_size) {
    $r_size = mysqli_fetch_assoc($q_size);
    $db_size = $r_size['size_mb'] ?? 0;
} else {
    $db_size = 0; // En cas d'erreur (permissions restreintes)
}

// B. Récupérer la Collation (Méthode directe)
$q_coll = mysqli_query($connect, "SELECT @@collation_database as col");
if ($q_coll) {
    $r_coll = mysqli_fetch_assoc($q_coll);
    $db_collation = $r_coll['col'];
} else {
    $db_collation = 'Unknown';
}

// --- 4. Synchronisation Temps (Timezone) ---
$php_time = date('Y-m-d H:i:s');
$sql_time_q = mysqli_query($connect, "SELECT NOW() as mysql_time");
$sql_time = mysqli_fetch_assoc($sql_time_q)['mysql_time'];
$timezone = date_default_timezone_get();

// Directives PHP
$max_upload = ini_get('upload_max_filesize');
$post_max_size = ini_get('post_max_size');
$php_memory_limit = ini_get('memory_limit');
$php_max_execution_time = ini_get('max_execution_time');

// Statut 'display_errors' (Important pour la sécurité)
$display_errors = ini_get('display_errors');
$display_errors_badge = ($display_errors == 1 || strtolower($display_errors) == 'on') 
    ? '<span class="badge bg-danger">On (Risk)</span>' 
    : '<span class="badge bg-success">Off (Secure)</span>';

// Statut 'file_uploads'
$file_uploads = ini_get('file_uploads');
$file_uploads_badge = ($file_uploads == 1 || strtolower($file_uploads) == 'on')
    ? '<span class="badge bg-success">Enabled</span>'
    : '<span class="badge bg-danger">Disabled</span>';

// --- Extensions PHP ---
$curl_status = extension_loaded('curl');
$gd_status = extension_loaded('gd');
$gd2_status = function_exists('gd_info') && (gd_info()['GD Version'] ?? false);
$mbstring_status = extension_loaded('mbstring');
$mysqli_status = extension_loaded('mysqli');
$json_status = extension_loaded('json');
$openssl_status = extension_loaded('openssl');
$zip_status = extension_loaded('zip');
$fileinfo_status = extension_loaded('fileinfo');
$xls_status = extension_loaded('xlsxwriter');
$openssl_version = $openssl_status ? OPENSSL_VERSION_TEXT : 'N/A';

// --- Permissions ---

// Fonction d'aide pour les dossiers
function check_dir_permission($path) {
    if (!file_exists($path)) return '<span class="badge bg-danger">Does not exist</span>';
    if (is_writable($path)) {
        return '<span class="badge bg-success">Writable</span>';
    } else {
        return '<span class="badge bg-danger">Not writable</span>';
    }
}

// Fonction d'aide pour config.php (logique inversée)
function check_config_permission($path) {
     if (!file_exists($path)) return '<span class="badge bg-danger">Does not exist</span>';
     if (is_writable($path)) {
        return '<span class="badge bg-danger">Writable (Risk)</span>';
    } else {
        return '<span class="badge bg-success">Not writable (Secure)</span>';
    }
}

// --- 6. Permissions Dossiers ---
if (!function_exists('check_perm')) {
    function check_perm($path, $inverse = false) {
        if (!file_exists($path)) return '<span class="badge bg-secondary">Does not exist</span>';
        $writable = is_writable($path);
        if ($inverse) { 
            // Cas inversé (ex: config.php) : Si c'est inscriptible, c'est un DANGER
            return $writable ? '<span class="badge bg-danger">Writable (Risk)</span>' : '<span class="badge bg-success">ReadOnly (Secure)</span>';
        }
        // Cas normal (ex: logs, uploads) : Si c'est inscriptible, c'est BIEN
        return $writable ? '<span class="badge bg-success">Writable</span>' : '<span class="badge bg-danger">Not Writable</span>';
    }
}

// Chemins (relatifs à ce fichier admin/system-information.php)
$perm_backup    = check_dir_permission('../backup-database/');
$perm_uploads   = check_dir_permission('../uploads/');
$perm_cache     = check_dir_permission('../cache/');
$perm_vendor    = check_dir_permission('../vendor/');
$perm_core      = check_dir_permission('../core/');
$perm_logs      = check_perm('../admin/logs.php');
$perm_config    = check_config_permission('../config.php');

// ------------------------------------------------------------
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-server"></i> System Information</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">System Information</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row">

            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-hdd"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Server Disk Space</span>
                        <span class="info-box-number"><?php echo format_size($disk_used); ?> / <?php echo format_size($disk_total); ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: <?php echo $disk_percent; ?>%"></div>
                        </div>
                        <span class="progress-description small">
                            <?php echo format_size($disk_free); ?> free (<?php echo $disk_percent; ?>% used)
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Database Size</span>
                        <span class="info-box-number"><?php echo $db_size; ?> MB</span>
                        <span class="progress-description small text-muted">
                            Collation: <?php echo $db_collation; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Time Sync</span>
                        <span class="info-box-number"><?php echo date('H:i', strtotime($php_time)); ?> <small>(PHP)</small></span>
                        <span class="progress-description small <?php echo ($php_time == $sql_time) ? 'text-success' : 'text-danger'; ?>">
                            SQL: <?php echo date('H:i', strtotime($sql_time)); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                
                <div class="card card-outline card-primary"> <!-- Infos Serveur et logiciel -->
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-server"></i> Server & Software</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Server Domain
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($server_domain); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                IP Address
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($server_ip); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Operating System
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($server_os); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Server Software
                                <span class="badge bg-secondary" style="font-size: 0.8em;"><?php echo htmlspecialchars(short_text($server_software, 55)); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                HTTP Protocol
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($http_protocol); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Gateway Interface
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($gateway_interface); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Port
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($server_port); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                MySQL Version
                                <span class="badge bg-info"><?php echo short_text($db_version, 15); ?></span>
                            </li>
                        </ul>
                    </div>
                </div> <!-- Fin Infos Serveur et logiciel -->
                
                <div class="card card-outline card-success mt-3"> <!-- Extensions PHP -->
                    <div class="card-header">
                        <h3 class="card-title"><i class="fab fa-php"></i> PHP Extensions</h3>
                    </div>
                    <div class="card-body p-0">
                         <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                MySQLi (Database)
                                <?php if ($mysqli_status): ?>
                                    <span class="badge bg-success">Enabled (<?php echo htmlspecialchars(short_text(mysqli_get_client_info(), 55)); ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled (ERROR)</span>
                                <?php endif; ?>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                cURL (RSS Feeds)
                                <?php if ($curl_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                GD (Images)
                                <?php if ($gd_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                GD2 (Images)
                                <?php if ($gd2_status): ?>
                                    <span class="badge bg-success">Enabled (<?php echo htmlspecialchars(short_text(gd_info()['GD Version'] ?? '', 55)); ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>                            
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                mbstring (Characters)
                                <?php if ($mbstring_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                JSON (Data Handling)
                                <?php if ($json_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled (ERROR)</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Open SSL
                                <?php if ($openssl_status): ?>
                                    <span class="badge bg-success">Enabled (<?php echo htmlspecialchars(short_text($openssl_version, 55)); ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>                           
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Zip
                                <?php if ($zip_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Fileinfo
                                <?php if ($fileinfo_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li> 
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                XLSX Writer
                                <?php if ($xls_status): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </li>                                                        
                        </ul>
                    </div>
                </div> <!-- Fin Extensions PHP -->

            </div>
            
            <div class="col-md-6">
                
                <div class="card card-outline card-info"> <!-- Configuration PHP -->
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cogs"></i> PHP Configuration</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                PHP Version
                                <span class="badge bg-info"><?php echo $php_version; ?></span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                <h6><i class="fas fa-info text-info"></i> [F.A Blog] Version</h6>
                                <span class="badge bg-primary"><?php echo $phpblog_version; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <h6><i class="fas fa-info text-info"></i> [F.A Blog] Admin Version</h6 >  
                                <span class="badge bg-warning text-dark"><?php echo $admin_version; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Active Theme
                                <span class="badge bg-primary"><?php echo htmlspecialchars($settings['theme']); ?></span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                Display Errors (display_errors)
                                <?php echo $display_errors_badge; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                File Uploads (file_uploads)
                                <?php echo $file_uploads_badge; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                POST Limit (post_max_size)
                                <span class="badge bg-warning text-dark"><?php echo $post_max_size; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Upload Limit (upload_max_filesize)
                                <span class="badge bg-warning text-dark"><?php echo $max_upload; ?></span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                PHP Memory Limit
                                <span class="badge bg-warning text-dark"><?php echo $php_memory_limit; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Execution Time Limit
                                <span class="badge bg-warning text-dark"><?php echo $php_max_execution_time; ?>s</span>
                            </li>
                        </ul>
                    </div>
                </div> <!-- Fin Configuration PHP -->
                
                <div class="card card-outline card-warning mt-3"> <!-- Permissions Fichiers et Dossiers -->
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-folder-open"></i> File & Directory Permissions</h3>
                    </div>
                    <div class="card-body p-0">
                         <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Directory <code>/backup-database/</code>
                                <?php echo $perm_backup; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Directory <code>/uploads/</code>
                                <?php echo $perm_uploads; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Directory <code>/cache/</code> (HTMLPurifier)
                                <?php echo $perm_cache; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Directory <code>/core/</code>
                                <?php echo $perm_core; ?>
                            </li> 
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Directory <code>/vendor/</code>
                                <?php echo $perm_vendor; ?>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                File <code>/admin/logs.php</code>
                                <?php echo $perm_logs; ?>
                            </li>                                           
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                File <code>/config.php</code>
                                <?php echo $perm_config; ?>
                            </li>                                                    
                        </ul>
                    </div>
                </div> <!-- Fin Permissions Fichiers et Dossiers -->

            </div>
        </div>
    </div>
</section>

<?php
include "footer.php";
?>