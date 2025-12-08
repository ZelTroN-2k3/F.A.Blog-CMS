<?php
include "header.php";

// Validation ID
if (!isset($_GET['id']) && !isset($_POST['widget_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=widgets.php">'; exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['widget_id'];

// Récupération
$stmt = mysqli_prepare($connect, "SELECT * FROM widgets WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$widget = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$widget) {
    echo '<div class="alert alert-danger m-3">Widget not found.</div>'; include "footer.php"; exit;
}

// Statut URL pour redirection
$status_url_query = '';
if (isset($_GET['status']) && $_GET['status'] != 'all') {
    $status_url_query = '&status=' . htmlspecialchars($_GET['status']);
}

// --- TRAITEMENT UPDATE ---
if (isset($_POST['edit'])) {
    validate_csrf_token();

    $title    = $_POST['title'];
    $position = $_POST['position'];
    $active   = $_POST['active'];
    $type     = $widget['widget_type']; // Le type ne change pas à l'édition
    
    $content     = null;
    $config_data = null;

    switch ($type) {
        case 'html':
            $content = $_POST['content'];
            break;
        case 'latest_posts':
        case 'latest_projects':
        case 'shop':
            $limit = (int)$_POST['limit'];
            $config_data = json_encode(['count' => $limit]);
            break;
    }

    $stmt_up = mysqli_prepare($connect, "UPDATE widgets SET title=?, content=?, config_data=?, position=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "sssssi", $title, $content, $config_data, $position, $active, $id);
    
    if(mysqli_stmt_execute($stmt_up)) {
        // --- CORRECTION CACHE ---
        if(function_exists('clear_site_cache')) {
            clear_site_cache();
        }
        // ------------------------ 
        echo '<div class="alert alert-success m-3">Widget updated! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="0; url=widgets.php' . str_replace('&', '?', $status_url_query) . '">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error updating item.</div>';
    }
    mysqli_stmt_close($stmt_up);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Widget</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="widgets.php">Widgets</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="post" action="edit_widget.php?id=<?php echo $id . $status_url_query; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="widget_id" value="<?php echo $id; ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                Type: <span class="badge badge-info"><?php echo htmlspecialchars($widget['widget_type']); ?></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input name="title" class="form-control form-control-lg" type="text" value="<?php echo htmlspecialchars($widget['title']); ?>" required>
                            </div>

                            <?php if ($widget['widget_type'] == 'html'): ?>
                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea name="content" id="summernote" class="form-control"><?php echo html_entity_decode($widget['content']); ?></textarea>
                                </div>
                            <?php elseif ($widget['widget_type'] == 'latest_posts' || $widget['widget_type'] == 'latest_projects' || $widget['widget_type'] == 'shop'): 
                                $conf = json_decode($widget['config_data'], true);
                                $count = $conf['count'] ?? 2; // Par défaut 2 pour le shop
                            ?>
                                <div class="form-group">
                                    <label>Number of items to show</label>
                                    <input type="number" name="limit" class="form-control" value="<?php echo $count; ?>" min="1" max="20">
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">No specific configuration options for this widget.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title">Settings</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Position</label>
                                <select name="position" class="form-control">
                                    <option value="Sidebar" <?php if($widget['position']=='Sidebar') echo 'selected'; ?>>Sidebar</option>
                                    <option value="Header" <?php if($widget['position']=='Header') echo 'selected'; ?>>Header</option>
                                    <option value="Footer" <?php if($widget['position']=='Footer') echo 'selected'; ?>>Footer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($widget['active']=='Yes') echo 'selected'; ?>>Published</option>
                                    <option value="No" <?php if($widget['active']=='No') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="edit" class="btn btn-primary btn-block">Update Widget</button>
                            <a href="widgets.php<?php echo str_replace('&', '?', $status_url_query); ?>" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
<?php include "footer.php"; ?>