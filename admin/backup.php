<?php
include "header.php";

// Sécurité : Admin uniquement
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

$message = '';
$backup_dir = '../backup-database/'; // Dossier de stockage

// Vérifier si le dossier existe, sinon le créer
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    // Création auto du .htaccess de sécurité
    file_put_contents($backup_dir . '.htaccess', "Deny from all");
}

// --- FONCTION DE FORMATAGE DE TAILLE ---
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

// --- ACTION 1 : CRÉER UNE NOUVELLE SAUVEGARDE ---
if (isset($_POST['create_backup'])) {
    validate_csrf_token();

    // Génération du contenu SQL (Même logique que précédemment)
    $tables = [];
    $result = mysqli_query($connect, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }

    $sqlScript = "-- F.A Blog Database Backup\n";
    $sqlScript .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlScript .= "-- Host: " . $_SERVER['HTTP_HOST'] . "\n\n";
    $sqlScript .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sqlScript .= "SET time_zone = \"+00:00\";\n\n";

    foreach ($tables as $table) {
        $result = mysqli_query($connect, "SHOW CREATE TABLE $table");
        $row = mysqli_fetch_row($result);
        $sqlScript .= "\n\n-- Table structure for table `$table`\n";
        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
        $sqlScript .= $row[1] . ";\n\n";

        $result = mysqli_query($connect, "SELECT * FROM $table");
        $columnCount = mysqli_num_fields($result);
        if (mysqli_num_rows($result) > 0) {
            $sqlScript .= "-- Dumping data for table `$table`\n";
            while ($row = mysqli_fetch_row($result)) {
                $sqlScript .= "INSERT INTO `$table` VALUES(";
                for ($j = 0; $j < $columnCount; $j++) {
                    $row[$j] = isset($row[$j]) ? mysqli_real_escape_string($connect, $row[$j]) : null;
                    if (isset($row[$j])) { $sqlScript .= '"' . $row[$j] . '"'; } else { $sqlScript .= 'NULL'; }
                    if ($j < ($columnCount - 1)) { $sqlScript .= ','; }
                }
                $sqlScript .= ");\n";
            }
        }
    }

    // Sauvegarde sur le serveur
    if (!empty($sqlScript)) {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $file_path = $backup_dir . $filename;
        
        if(file_put_contents($file_path, $sqlScript)){
            $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Backup created successfully: <strong>'.$filename.'</strong></div>';
        } else {
            $message = '<div class="alert alert-danger">Error: Could not write to backup directory. Check permissions.</div>';
        }
    }
}

// --- ACTION 2 : TÉLÉCHARGER UN FICHIER ---
if (isset($_GET['download'])) {
    // Pas de CSRF nécessaire pour un download GET simple, mais on vérifie le path
    $file = basename($_GET['download']);
    $filepath = $backup_dir . $file;

    if(file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// --- ACTION 3 : SUPPRIMER UN FICHIER ---
if (isset($_GET['delete'])) {
    validate_csrf_token_get();
    $file = basename($_GET['delete']);
    $filepath = $backup_dir . $file;

    if(file_exists($filepath)) {
        unlink($filepath);
        $message = '<div class="alert alert-success">Backup deleted successfully.</div>';
    }
}

// --- LISTER LES FICHIERS EXISTANTS ---
$backup_files = glob($backup_dir . "*.sql");
// Trier par date décroissante (le plus récent en premier)
usort($backup_files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-database"></i> Database Backups</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $message; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">New Backup</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted">Create a new restore point stored on the server.</p>
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit" name="create_backup" class="btn btn-app bg-success">
                                <i class="fas fa-save"></i> Create Local Backup
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Stats</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Total Backups</td>
                                    <td><span class="badge bg-primary"><?php echo count($backup_files); ?></span></td>
                                </tr>
                                <?php
                                // Calcul taille totale dossier
                                $total_size = 0;
                                foreach($backup_files as $f) $total_size += filesize($f);
                                ?>
                                <tr>
                                    <td>Total Space Used</td>
                                    <td><?php echo formatSizeUnits($total_size); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Existing Backups</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Date</th>
                                    <th>Size</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($backup_files)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No backups found in <code>/backup-database/</code></td></tr>
                                <?php else: ?>
                                    <?php foreach($backup_files as $file): 
                                        $filename = basename($file);
                                        $filesize = filesize($file);
                                        $filedate = filemtime($file);
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-code text-secondary mr-2"></i> 
                                            <strong><?php echo $filename; ?></strong>
                                        </td>
                                        <td><?php echo date("d M Y H:i", $filedate); ?></td>
                                        <td><span class="badge bg-info"><?php echo formatSizeUnits($filesize); ?></span></td>
                                        <td class="text-right">
                                            <a href="?download=<?php echo $filename; ?>" class="btn btn-sm btn-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="?delete=<?php echo $filename; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this backup permanently?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>