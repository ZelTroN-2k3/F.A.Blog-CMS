<?php
// --- SCRIPT D'IMPORTATION RSS (Design Pro) ---
include "../core.php";

// Clé secrète pour le cron job (A définir dans core.php ou ici)
if(!defined('RSS_CRON_SECRET_KEY')) define('RSS_CRON_SECRET_KEY', '#7dWrR!W@29LxG22wW^b'); 

// Déterminer le mode
$manual_run_id = $_GET['id'] ?? null;
$cron_run_key = $_GET['key'] ?? null;

$is_cron = false;
$logs = [];

// --- GESTION SÉCURITÉ ---
if ($manual_run_id) {
    // Mode Manuel (Admin Connecté)
    if (!isset($_SESSION['sec-username'])) {
        die('Access Denied. Please login.');
    }
    // Inclure header pour le design
    include "header.php";
} elseif ($cron_run_key) {
    // Mode Cron (Clé Secrète)
    $is_cron = true;
    if ($cron_run_key !== RSS_CRON_SECRET_KEY) {
        die('Invalid Cron Key.');
    }
} else {
    die('No ID or Key provided.');
}

// --- LOGIQUE D'IMPORTATION (Fonction) ---
function logMsg($msg) {
    global $logs, $is_cron;
    if ($is_cron) { echo $msg . "\n"; }
    else { $logs[] = $msg; }
}

// Préparation de la requête
$sql_query = "";
if ($manual_run_id) {
    $sql_query = "SELECT * FROM rss_imports WHERE id = " . (int)$manual_run_id;
} else {
    $sql_query = "SELECT * FROM rss_imports WHERE is_active = 1";
}

$result = mysqli_query($connect, $sql_query);
$imported_count = 0;

while ($feed = mysqli_fetch_assoc($result)) {
    logMsg("Processing Feed: <strong>" . htmlspecialchars($feed['feed_url']) . "</strong>");
    
    $xml = @simplexml_load_file($feed['feed_url']);
    
    if ($xml === false) {
        logMsg("<span class='text-danger'>Error: Failed to load XML.</span>");
        continue;
    }

    foreach ($xml->channel->item as $item) {
        $guid = (string)$item->guid;
        if (empty($guid)) $guid = (string)$item->link;
        
        // Vérifier doublon
        $stmt_check = mysqli_prepare($connect, "SELECT id FROM posts WHERE download_link = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $guid); // On utilise download_link pour stocker le GUID temporairement
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) == 0) {
            // Préparation données
            $title = (string)$item->title;
            $content = (string)$item->description;
            $link = (string)$item->link;
            $slug = generateSeoURL($title);
            
            // Image (Tentative de récupération)
            $image_url = '';
            $namespaces = $item->getNamespaces(true);
            if (isset($namespaces['media'])) {
                $media = $item->children($namespaces['media']);
                if (isset($media->content)) {
                    $attrs = $media->content->attributes();
                    $image_url = (string)$attrs['url'];
                }
            }
            
            // Insertion
            $stmt_ins = mysqli_prepare($connect, "INSERT INTO posts (category_id, title, slug, author_id, image, content, active, created_at, publish_at, featured, download_link) VALUES (?, ?, ?, ?, ?, ?, 'Yes', NOW(), NOW(), 'No', ?)");
            mysqli_stmt_bind_param($stmt_ins, "issssss", 
                $feed['import_as_category_id'], 
                $title, 
                $slug, 
                $feed['import_as_user_id'], 
                $image_url, 
                $content, 
                $guid
            );
            
            if (mysqli_stmt_execute($stmt_ins)) {
                $imported_count++;
                logMsg("<span class='text-success'>+ Imported: $title</span>");
            }
            mysqli_stmt_close($stmt_ins);
        }
        mysqli_stmt_close($stmt_check);
    }

    // Mise à jour timestamp
    mysqli_query($connect, "UPDATE rss_imports SET last_import_time = NOW() WHERE id = " . $feed['id']);
}

// --- AFFICHAGE (Mode Manuel uniquement) ---
if (!$is_cron) {
?>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Import Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="rss_imports.php">Back to Feeds</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle"></i> Execution Completed</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border">
                        <?php 
                        if (empty($logs)) {
                            echo "No active feeds found or database error.";
                        } else {
                            foreach($logs as $log) {
                                echo "<div>$log</div>";
                            }
                        }
                        ?>
                    </div>
                    <div class="mt-3">
                        <strong>Total Items Imported: <?php echo $imported_count; ?></strong>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="rss_imports.php" class="btn btn-primary">Return to List</a>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
<?php 
} // Fin mode manuel 
?>