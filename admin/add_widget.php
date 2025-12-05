<?php
include "header.php";

// --- Gestion Statut URL ---
$status_url_query = '';
if (isset($_GET['status']) && $_GET['status'] != 'all') {
    $status_url_query = '?status=' . htmlspecialchars($_GET['status']);
}

// --- TRAITEMENT FORMULAIRE ---
if (isset($_POST['add'])) {
    validate_csrf_token();

    $title       = $_POST['title'];
    $position    = $_POST['position'];
    $active      = $_POST['active'];
    $widget_type = $_POST['widget_type'];
    
    $content     = null;
    $config_data = null;

    // Logique spécifique par type
    switch ($widget_type) {
        case 'html':
            $content = $_POST['content'];
            break;
        case 'latest_posts':
        case 'latest_projects':
            $limit = (int)$_POST['limit'];
            $config_data = json_encode(['count' => $limit]);
            break;
        // Les autres types (search, quiz_leaderboard, etc.) n'ont pas de config spéciale
    }

    $stmt = mysqli_prepare($connect, "INSERT INTO widgets (title, widget_type, content, config_data, position, active) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $title, $widget_type, $content, $config_data, $position, $active);
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<meta http-equiv="refresh" content="0; url=widgets.php' . $status_url_query . '">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}

// --- AFFICHAGE ---
$type = $_GET['type'] ?? null;
$type_labels = [
    'html' => 'Custom HTML',
    'latest_posts' => 'Latest Posts',
    'search' => 'Search Bar',
    'quiz_leaderboard' => 'Quiz Leaderboard',
    'faq_leaderboard' => 'FAQ Leaderboard',
    'testimonials' => 'Testimonials Slider'
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Widget <?php echo $type ? ': ' . $type_labels[$type] : ''; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="widgets.php">Widgets</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <?php if (!$type): // --- ETAPE 1 : CHOIX DU TYPE --- ?>
        
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Select Widget Type</h3></div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $widgets_list = [
                        ['type' => 'html', 'icon' => 'fa-code', 'name' => 'Custom HTML', 'desc' => 'Free text, images, or HTML code.'],
                        ['type' => 'latest_posts', 'icon' => 'fa-list', 'name' => 'Latest Posts', 'desc' => 'List of most recent blog posts.'],
                        ['type' => 'search', 'icon' => 'fa-search', 'name' => 'Search Bar', 'desc' => 'Site search input field.'],
                        ['type' => 'quiz_leaderboard', 'icon' => 'fa-trophy', 'name' => 'Quiz Leaderboard', 'desc' => 'Top 10 quiz players.'],
                        ['type' => 'faq_leaderboard', 'icon' => 'fa-question-circle', 'name' => 'FAQ Leaderboard', 'desc' => 'Top 10 FAQ questions.'],
                        ['type' => 'testimonials', 'icon' => 'fa-comments', 'name' => 'Testimonials', 'desc' => 'Customer reviews slider.'],
                        ['type' => 'newsletter', 'icon' => 'fa-envelope', 'name' => 'Newsletter Form', 'desc' => 'Email subscription form.'],
                        ['type' => 'online_users', 'icon' => 'fa-users', 'name' => 'Online Users', 'desc' => 'List of currently connected users.'],
                        ['type' => 'latest_projects', 'icon' => 'fa-microchip', 'name' => 'Latest Projects', 'desc' => 'List of most recent projects.'],
                    ];
                    
                    foreach($widgets_list as $w) {
                        echo '
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="add_widget.php?type=' . $w['type'] . $status_url_query . '" class="btn btn-default btn-block p-4" style="height:100%;">
                                <i class="fas ' . $w['icon'] . ' fa-3x mb-2 text-primary"></i>
                                <h4>' . $w['name'] . '</h4>
                                <p class="text-muted">' . $w['desc'] . '</p>
                            </a>
                        </div>';
                    }
                    ?>
                </div>
            </div>
            <div class="card-footer">
                <a href="widgets.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>

        <?php else: // --- ETAPE 2 : CONFIGURATION --- ?>

        <form method="post" action="add_widget.php<?php echo $status_url_query; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="widget_type" value="<?php echo htmlspecialchars($type); ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title">Content</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input name="title" class="form-control form-control-lg" type="text" placeholder="Widget Title" required>
                            </div>

                            <?php if ($type == 'html'): ?>
                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea name="content" id="summernote" class="form-control"></textarea>
                                </div>
                            <?php elseif ($type == 'latest_posts' || $type == 'latest_projects'): ?>
                                <div class="form-group">
                                    <label>Number of items to show</label>
                                    <input type="number" name="limit" class="form-control" value="5" min="1" max="20">
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No specific configuration needed for this widget type.
                                </div>
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
                                    <option value="Sidebar">Sidebar</option>
                                    <option value="Header">Header</option>
                                    <option value="Footer">Footer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes">Published</option>
                                    <option value="No">Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add" class="btn btn-primary btn-block">Save Widget</button>
                            <a href="add_widget.php" class="btn btn-default btn-block">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php endif; ?>

    </div>
</section>
<?php include "footer.php"; ?>