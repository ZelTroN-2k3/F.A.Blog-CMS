<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center py-2">
                <div>
                    <h5 class="mb-0 text-dark"><i class="fas fa-hand-sparkles text-warning me-2"></i> Welcome back, <strong><?php echo htmlspecialchars($user['username']); ?></strong>!</h5>
                    <small class="text-muted">Here is what's happening on your site today.</small>
                </div>
                <div class="btn-group">
                    <a href="add_post.php" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> New Post</a>
                    <a href="add_project.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-microchip"></i> New Project</a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="collapse" data-target="#quickShortcuts"><i class="fas fa-bars"></i> More</button>
                </div>
            </div>
            <div class="collapse border-top bg-light" id="quickShortcuts">
                <div class="card-body text-center">
                    <a href="settings.php" class="btn btn-app bg-white"><i class="fas fa-cogs text-secondary"></i> Settings</a>
                    <a href="menu_editor.php" class="btn btn-app bg-white"><i class="fas fa-bars text-secondary"></i> Menu</a>
                    <a href="widgets.php" class="btn btn-app bg-white"><i class="fas fa-archive text-secondary"></i> Widgets</a>
                    <a href="add_user.php" class="btn btn-app bg-white"><i class="fas fa-user-plus text-success"></i> Add User</a>
                    <a href="newsletter.php" class="btn btn-app bg-white"><i class="fas fa-envelope-open-text text-danger"></i> Newsletter</a>
                    <a href="backup.php" class="btn btn-app bg-white"><i class="fas fa-database text-warning"></i> Backup</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-gradient-info elevation-2">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Analytics (7 Days)</span>
                <span class="info-box-number">
                    <?php 
                    // Calcul rapide de la somme des 7 derniers jours
                    $total_visits_7d = array_sum(json_decode($json_visits_data));
                    echo number_format($total_visits_7d); 
                    ?> Visits
                </span>
                <div class="progress"><div class="progress-bar" style="width: 70%"></div></div>
                <span class="progress-description text-white"><i class="fas fa-globe"></i> Global Traffic</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-gradient-success elevation-2">
            <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Content Health</span>
                <span class="info-box-number"><?php echo $count_posts_published + $count_projects; ?> Items</span>
                <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                <span class="progress-description text-white">
                    <?php echo $count_posts_published; ?> Posts, <?php echo $count_projects; ?> Projects
                </span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-gradient-danger elevation-2">
            <span class="info-box-icon"><i class="fas fa-heart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Engagement</span>
                <span class="info-box-number"><?php echo number_format($total_likes + $total_favorites); ?> Actions</span>
                <div class="progress"><div class="progress-bar" style="width: <?php echo $like_percent; ?>%"></div></div>
                <span class="progress-description text-white">
                    <?php echo $l_posts; ?> Likes on Blog
                </span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-gradient-warning elevation-2">
            <span class="info-box-icon"><i class="fas fa-users text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-white">Community</span>
                <span class="info-box-number text-white"><?php echo $count_total_users; ?> Members</span>
                <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                <span class="progress-description text-white">
                    <i class="fas fa-envelope"></i> <?php echo $total_subscribers_count; ?> Subscribers
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <a href="comments.php" class="btn btn-app btn-sm m-0 <?php echo ($count_comments_pending > 0) ? 'bg-danger' : 'bg-light'; ?>">
                <?php if($count_comments_pending > 0) echo '<span class="badge bg-warning">'.$count_comments_pending.'</span>'; ?>
                <i class="fas fa-comments"></i> Comments
            </a>
            <a href="messages.php" class="btn btn-app btn-sm m-0 <?php echo ($count_messages_unread > 0) ? 'bg-danger' : 'bg-light'; ?>">
                <?php if($count_messages_unread > 0) echo '<span class="badge bg-warning">'.$count_messages_unread.'</span>'; ?>
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="polls.php" class="btn btn-app btn-sm m-0 bg-light"><span class="badge bg-purple"><?php echo $count_polls_total; ?></span><i class="fas fa-poll text-purple"></i> Polls</a>
            <a href="quizzes.php" class="btn btn-app btn-sm m-0 bg-light"><span class="badge bg-indigo"><?php echo $count_quiz_total; ?></span><i class="fas fa-graduation-cap text-indigo"></i> Quiz</a>
            <a href="testimonials.php" class="btn btn-app btn-sm m-0 bg-light"><span class="badge bg-warning"><?php echo $count_testi_total; ?></span><i class="fas fa-star text-warning"></i> Testi.</a>
            <a href="slides.php" class="btn btn-app btn-sm m-0 bg-light"><span class="badge bg-primary"><?php echo $count_slides_total; ?></span><i class="fas fa-images text-primary"></i> Slides</a>
            <a href="bans.php" class="btn btn-app btn-sm m-0 bg-light"><span class="badge bg-dark"><?php echo $count_bans; ?></span><i class="fas fa-ban text-danger"></i> Bans</a>
        </div>
    </div>
</div>