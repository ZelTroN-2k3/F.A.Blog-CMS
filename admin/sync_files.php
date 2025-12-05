<?php
include "header.php";

// Sécurité : Admin seulement
if ($user['role'] != 'Admin') {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">';
    exit;
}

$report_added = [];
$report_deleted = [];
$target_dir = "../uploads/files/";

// Extensions autorisées (Sécurité)
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip', 'rar', 'mp3', 'mp4'];

if (isset($_POST['sync_now'])) {
    
    // --- 1. IMPORTATION (Disque -> BDD) ---
    if (is_dir($target_dir)) {
        $files = scandir($target_dir);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            if ($file == 'index.html' || $file == '.htaccess') continue;

            // Vérification Extension
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) continue;

            $full_path_db = "uploads/files/" . $file;
            
            // Vérifier si déjà en BDD
            $stmt_check = mysqli_prepare($connect, "SELECT id FROM files WHERE path = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt_check, "s", $full_path_db);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) == 0) {
                $stmt_insert = mysqli_prepare($connect, "INSERT INTO files (filename, path, created_at, author_id) VALUES (?, ?, NOW(), ?)");
                mysqli_stmt_bind_param($stmt_insert, "ssi", $file, $full_path_db, $user['id']);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
                
                $report_added[] = $file;
            }
            mysqli_stmt_close($stmt_check);
        }
    }

    // --- 2. NETTOYAGE (BDD -> Disque) ---
    if (isset($_POST['clean_orphans'])) {
        $q_all = mysqli_query($connect, "SELECT id, path, filename FROM files");
        while ($row = mysqli_fetch_assoc($q_all)) {
            $physical_path = "../" . $row['path'];
            
            if (strpos($row['path'], 'uploads/files/') === 0) {
                if (!file_exists($physical_path)) {
                    mysqli_query($connect, "DELETE FROM files WHERE id = " . $row['id']);
                    $report_deleted[] = $row['filename'];
                }
            }
        }
    }
}

// Compteurs
$db_count_q = mysqli_query($connect, "SELECT COUNT(id) as c FROM files");
$db_count = mysqli_fetch_assoc($db_count_q)['c'];

$real_files_count = 0;
if (is_dir($target_dir)) {
    $scanned = scandir($target_dir);
    foreach($scanned as $f) {
        if($f!='.' && $f!='..' && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowed_ext)) {
            $real_files_count++;
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-sync"></i> Synchronize Files</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="files.php">Files</a></li>
                    <li class="breadcrumb-item active">Sync</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <?php if (!empty($report_added)): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5><i class="icon fas fa-check"></i> Import Successful!</h5>
                <ul class="mb-0 pl-3"><?php foreach($report_added as $f) echo "<li>Added: <strong>$f</strong></li>"; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($report_deleted)): ?>
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5><i class="icon fas fa-trash"></i> Cleanup Successful!</h5>
                <ul class="mb-0 pl-3"><?php foreach($report_deleted as $f) echo "<li>Removed: <strong>$f</strong></li>"; ?></ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_POST['sync_now']) && empty($report_added) && empty($report_deleted)): ?>
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                Nothing to do. Everything is synchronized.
            </div>
        <?php endif; ?>


        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Sync Dashboard</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-primary" data-toggle="modal" data-target="#helpModal">
                        <i class="fas fa-info-circle fa-lg"></i> README Info
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <div class="info-box mb-3 bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Files in Database</span>
                                <span class="info-box-number" style="font-size: 1.5rem;"><?php echo $db_count; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box mb-3 bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-folder-open"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valid Files on Disk</span>
                                <span class="info-box-number" style="font-size: 1.5rem;"><?php echo $real_files_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <form method="post">
                    <div class="form-group text-center">
                        <div class="custom-control custom-checkbox d-inline-block">
                            <input class="custom-control-input" type="checkbox" id="cleanOrphans" name="clean_orphans" value="1" checked>
                            <label for="cleanOrphans" class="custom-control-label text-left">
                                <strong>Clean Database Orphans</strong><br>
                                <small class="text-muted">Delete database entries for missing files.</small>
                            </label>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="sync_now" class="btn btn-success btn-lg px-5 shadow-sm">
                            <i class="fas fa-sync-alt"></i> Run Synchronization
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-muted small">
                Target folder: <code>/uploads/files/</code>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> How to use this tool?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <h5>1. Purpose</h5>
          <p>This tool reconciles the database with the actual files stored on your server in the <code>/uploads/files/</code> directory.</p>
          <ul>
              <li>It detects files uploaded via FTP that are not yet visible in the Media Library.</li>
              <li>It cleans up database entries pointing to files that have been deleted manually.</li>
          </ul>

          <hr>

          <h5>2. How it works</h5>
          <div class="row">
              <div class="col-md-6">
                  <h6><i class="fas fa-file-import text-success"></i> Import</h6>
                  <p class="small text-muted">The script scans the folder and adds any valid file (Images, PDFs, Archives) to the database if it's missing.</p>
              </div>
              <div class="col-md-6">
                  <h6><i class="fas fa-trash-alt text-warning"></i> Cleanup (Orphans)</h6>
                  <p class="small text-muted">If "Clean Database Orphans" is checked, the script checks every database entry. If the file is missing from the server, the entry is deleted.</p>
              </div>
          </div>

          <hr>

          <h5>3. Security Rules</h5>
          <p class="small">Only specific file extensions are allowed for import to ensure security:</p>
          <p><code>jpg, jpeg, png, gif, webp, pdf, doc, docx, zip, rar, mp3, mp4</code></p>
          <p class="small text-danger"><strong>Note:</strong> System files (.php, .html, .htaccess) are strictly ignored.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>