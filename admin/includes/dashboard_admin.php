<div class="card card-warning card-outline collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bars"></i> Shortcuts</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body text-center">
        <a href="add_post.php" class="btn btn-app bg-primary"><i class="fas fa-edit"></i> Write Post</a>
        <a href="settings.php" class="btn btn-app bg-secondary"><i class="fas fa-cogs"></i> Settings</a>
        <a href="messages.php" class="btn btn-app bg-info"><i class="fas fa-envelope"></i> Messages</a>
        <a href="menu_editor.php" class="btn btn-app bg-secondary"><i class="fas fa-bars"></i> Menu</a>
        <a href="add_page.php" class="btn btn-app bg-primary"><i class="fas fa-file-alt"></i> Add Page</a>
        <a href="add_image.php" class="btn btn-app bg-success"><i class="fas fa-camera-retro"></i> Add Image</a>
        <a href="widgets.php" class="btn btn-app bg-secondary"><i class="fas fa-archive"></i> Widgets</a>
        <a href="add_user.php" class="btn btn-app bg-warning"><i class="fas fa-user-plus"></i> Add User</a>
        <a href="upload_file.php" class="btn btn-app bg-success"><i class="fas fa-upload"></i> Upload File</a>
        <a href="<?php echo $settings['site_url']; ?>" class="btn btn-app bg-info"><i class="fas fa-eye"></i> Visit Site</a>
    </div>
</div><!-- End Shortcuts -->

<div class="row">
    <div class="col-12 col-sm-6 col-md-2"><!-- Published Articles -->
        <a href="posts.php" style="color: inherit; text-decoration: none;">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Published Articles</span>
                    <span class="info-box-number"><?php echo $count_posts_published; ?> 
                        <?php if($count_posts_pending > 0) echo "<small class='badge bg-danger'>{$count_posts_pending} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Drafts -->
        <a href="posts.php?status=draft" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-pencil-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Drafts</span>
                    <span class="info-box-number"><?php echo $count_posts_drafts; ?> 
                        <?php if($count_drafts_pending > 0) echo "<small class='badge bg-danger'>{$count_drafts_pending} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-12 col-sm-6 col-md-2"><!-- Pending Comments -->
        <a href="comments.php?status=pending" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-comments"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Comments</span>
                    <span class="info-box-number"><?php echo $count_comments_pending; ?> 
                        <?php if($count_comments_pending > 0) echo "<small class='badge bg-danger'>{$count_comments_pending} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Pending Articles -->
        <a href="posts.php?status=pending" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Articles</span>
                    <span class="info-box-number"><?php echo $count_posts_pending; ?> 
                        <?php if($count_posts_pending > 0) echo "<small class='badge bg-danger'>{$count_posts_pending} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Unread Messages -->
        <a href="messages.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Unread Messages</span>
                    <span class="info-box-number"><?php echo $count_messages_unread; ?> 
                        <?php if($count_messages_unread > 0) echo "<small class='badge bg-danger'>{$count_messages_unread} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Testimonials -->
        <a href="testimonials.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Testimonials</span>
                    <span class="info-box-number"><?php echo $count_testi_total; ?> 
                        <?php if($count_testi_pending > 0) echo "<small class='badge bg-danger'>{$count_testi_pending} new</small>"; ?>
                    </span>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-12 col-sm-6 col-md-2"><!-- Polls -->
        <a href="polls.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-poll"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Polls</span>
                    <span class="info-box-number"><?php echo $count_polls_total; ?></span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Slides -->
        <a href="slides.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-images"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Slides</span>
                    <span class="info-box-number"><?php echo $count_slides_total; ?></span>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-12 col-sm-6 col-md-2"><!-- FAQ -->
        <a href="faq.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-question-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">FAQ</span>
                    <span class="info-box-number"><?php echo $count_faq_total; ?></span>    
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Quiz -->
        <a href="quizzes.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Quiz</span>
                    <span class="info-box-number"><?php echo $count_quiz_total; ?></span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Banned IPs/Users -->
        <a href="bans.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-dark elevation-1">
                    <span class="fa-stack" style="font-size: 0.5em;">
                        <i class="fas fa-user fa-stack-1x text-white"></i>
                        <i class="fas fa-ban fa-stack-2x text-red"></i> </span>
                    </span>
                <div class="info-box-content">
                    <span class="info-box-text">Banned IPs/Users</span>
                    <span class="info-box-number"><?php echo $count_bans; ?></span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-2"><!-- Projects -->
        <a href="projects.php" style="color: inherit; text-decoration: none;">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-indigo elevation-1"><i class="fas fa-microchip"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Projects</span>
                    <span class="info-box-number"><?php echo $count_projects; ?></span>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row"><!-- Top 5 Most Viewed Articles -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 5 Most Viewed Articles</h3></div>
            <div class="card-body">
                <?php if (empty($chart_top_posts_titles)): ?>
                    <div class="alert alert-info">Not enough data to display a chart yet.</div>
                <?php else: ?>
                    <canvas id="popularPostsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-purple">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit"></i> Top 5 Most Active Authors</h3></div>
            <div class="card-body">
                <?php if (empty($chart_authors_labels)): ?>
                    <div class="alert alert-info">Not enough data to display a chart yet.</div>
                <?php else: ?>
                    <canvas id="activeAuthorsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div><!-- End Top 5 Most Active Authors -->

<div class="row">
    <div class="col-md-6"><!-- Publications Last 12 Months -->
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line"></i> Publications (Last 12 Months)</h3></div>
            <div class="card-body">
                    <?php if (empty($chart_months_labels)): ?>
                    <div class="alert alert-info">No data available for this chart yet.</div>
                <?php else: ?>
                    <canvas id="postsPerMonthChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- End Publications Last 12 Months -->
    <div class="col-md-6"><!-- Category Distribution -->
        <div class="card card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie"></i> Category Distribution</h3></div>
            <div class="card-body">
                <?php if (empty($chart_cat_labels)): ?>
                    <div class="alert alert-info">No articles have been categorized yet.</div>
                <?php else: ?>
                    <canvas id="postsPerCategoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- End Category Distribution -->
</div>

<div class="row"><!-- Latest Projects -->
    <div class="col-md-12">
        <div class="card card-indigo card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-microchip mr-1"></i> Latest Projects
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <a href="add_project.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Project
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php
                    $q_latest_proj = mysqli_query($connect, "SELECT * FROM projects ORDER BY id DESC LIMIT 5");
                    
                    if (mysqli_num_rows($q_latest_proj) == 0) {
                        echo '<li class="item p-3 text-center text-muted">No projects found.</li>';
                    } else {
                        while($proj = mysqli_fetch_assoc($q_latest_proj)) {
                            // Gestion Image
                            $p_img = '../assets/img/project-no-image.png';
                            if (!empty($proj['image'])) {
                                $clean_p = str_replace('../', '', $proj['image']);
                                if(file_exists('../' . $clean_p)) { $p_img = '../' . $clean_p; }
                            }

                            // Badge Difficulté
                            $badge_color = 'secondary';
                            if($proj['difficulty'] == 'Easy') $badge_color = 'success';
                            if($proj['difficulty'] == 'Intermediate') $badge_color = 'primary';
                            if($proj['difficulty'] == 'Advanced') $badge_color = 'warning';
                            if($proj['difficulty'] == 'Expert') $badge_color = 'danger';
                            
                            echo '
                            <li class="item">
                                <div class="product-img">
                                    <img src="' . htmlspecialchars($p_img) . '" alt="Image" class="img-size-50 rounded" style="object-fit:cover;">
                                </div>
                                <div class="product-info">
                                    <a href="edit_project.php?id=' . $proj['id'] . '" class="product-title">
                                        ' . htmlspecialchars($proj['title']) . '
                                        <span class="badge bg-' . $badge_color . ' float-right">' . htmlspecialchars($proj['difficulty']) . '</span>
                                    </a>
                                    <span class="product-description">
                                        ' . htmlspecialchars(short_text($proj['pitch'], 100)) . '
                                    </span>
                                </div>
                            </li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="projects.php" class="uppercase">View All Projects</a>
            </div>
        </div>
    </div>
</div><!-- End Latest Projects -->

<div class="row">
    <div class="col-md-6">
        <div class="card card-warning"><!-- Latest Pending Testimonials -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> Latest Pending Testimonials</h3></div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php
                    if(mysqli_num_rows($query_pending_testimonials_list) == 0):
                        echo '<li class="item text-center text-muted p-3">No pending testimonials.</li>';
                    else:
                        while($row_t = mysqli_fetch_assoc($query_pending_testimonials_list)):
                            $avatar = !empty($row_t['avatar']) ? '../'.$row_t['avatar'] : '../assets/img/avatar.png';
                    ?>
                    <li class="item">
                        <div class="product-img">
                            <img src="<?php echo $avatar; ?>" alt="Avatar" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;">
                        </div>
                        <div class="product-info">
                            <span class="product-title">
                                <?php echo htmlspecialchars($row_t['name']); ?>
                                <small class="badge badge-secondary float-right"><?php echo date('d M Y', strtotime($row_t['created_at'])); ?></small>
                            </span>
                            <span class="product-description">"<?php echo emoticons(htmlspecialchars(substr($row_t['content'], 0, 100))); ?>..."</span>
                            <div class="mt-2">
                                <a href="?approve-testimonial=<?php echo $row_t['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Approve</a>
                                <a href="?delete-testimonial=<?php echo $row_t['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete?');"><i class="fas fa-trash"></i> Delete</a>
                                <a href="testimonials.php" class="btn btn-xs btn-default float-right">View All</a>
                            </div>
                        </div>
                    </li>
                    <?php endwhile; endif; ?>
                </ul>
            </div>
        </div><!-- End Latest Pending Testimonials -->

        <div class="card card-primary"><!-- Latest Registered Users -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock"></i> Latest Registered Users</h3></div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                <?php
                if (mysqli_num_rows($query_latest_users) == 0) {
                    echo '<li class="list-group-item">No users found.</li>';
                } else {
                    while ($row_user = mysqli_fetch_assoc($query_latest_users)) {
                        $avatar_url_raw = $row_user['avatar'];
                        $auth_badge = (strpos($avatar_url_raw, 'http') === 0) 
                            ? '<span class="badge bg-danger" style="font-size: 0.7em;"><i class="fab fa-google"></i> Google</span>' 
                            : '<span class="badge bg-secondary" style="font-size: 0.7em;"><i class="fas fa-key"></i> Normal</span>';
                        $avatar_path = (strpos($avatar_url_raw, 'http') === 0) ? htmlspecialchars($avatar_url_raw) : '../' . htmlspecialchars($avatar_url_raw);
                        
                        $role_badge = ($row_user['role'] == 'Admin') ? '<span class="badge bg-success" style="font-size: 0.7em;"><i class="fas fa-user-shield"></i> Admin</span>' :
                                      (($row_user['role'] == 'Editor') ? '<span class="badge bg-primary" style="font-size: 0.7em;"><i class="fas fa-user-edit"></i> Editor</span>' : 
                                      '<span class="badge bg-info" style="font-size: 0.7em;"><i class="fas fa-user"></i> User</span>');
                ?>
                    <li class="item">
                        <div class="product-img"><img src="<?php echo $avatar_path; ?>" alt="Avatar" class="img-size-50 img-circle"></div>
                        <div class="product-info">
                            <div class="float-right text-right">
                                <a href="users.php?edit-id=<?php echo $row_user['id']; ?>" class="btn btn-secondary btn-xs"><i class="fa fa-edit"></i> Manage</a>
                                <div class="mt-1"><?php echo $auth_badge . ' ' . $role_badge; ?></div>
                            </div>
                            <a href="users.php?edit-id=<?php echo $row_user['id']; ?>" class="product-title"><?php echo htmlspecialchars($row_user['username']); ?></a>
                            <div class="product-description text-muted" style="font-size: 0.85em; margin-bottom: 2px;">
                                <i class="fas fa-envelope fa-fw mr-1"></i> <?php echo htmlspecialchars($row_user['email']); ?>
                            </div>
                        </div>
                    </li>
                <?php } } ?>
                </ul>
            </div>
            <div class="card-footer text-center"><a href="users.php">View all users</a></div>
        </div><!-- End Latest Registered Users -->

        <div class="card card-success" id="moderation"><!-- System Health / Moderation -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-gavel"></i> Quick Moderation (Comments)</h3></div>
            <?php if ($cmnts_pending == "0"): ?>
                <div class="card-body"><div class="alert alert-default text-center m-0 p-3">No comments pending.</div></div>
            <?php else: ?>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php while ($row = mysqli_fetch_assoc($query_pending_comments_list)): 
                        $avatar = ($row['guest'] == 'Yes') ? 'assets/img/avatar.png' : ($row['user_avatar'] ?: 'assets/img/avatar.png');
                        $author_name = ($row['guest'] == 'Yes') ? $row['user_id'] . ' <span class="badge badge-info float-right"><i class="fas fa-user"></i> Guest</span>' : $row['user_username'];
                        $avatar_path = (strpos($avatar, 'http') === 0) ? htmlspecialchars($avatar) : '../' . htmlspecialchars($avatar);
                    ?>
                        <li class="item">
                            <div class="product-img"><img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="img-size-50 img-circle"></div>
                            <div class="product-info">
                                <span class="product-title"><?php echo $author_name; ?></span>
                                <span class="product-description">Sur: <a href="../post?name=<?php echo htmlspecialchars($row['post_slug']); ?>" target="_blank"><?php echo htmlspecialchars(short_text($row['post_title'] ?: 'N/A', 40)); ?></a></span>
                                <p class="mt-1 mb-1 text-muted"><?php echo htmlspecialchars(short_text(html_entity_decode($row['comment']), 100)); ?></p>
                                <div>
                                    <a href="?approve-comment=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-xs"><i class="fas fa-check"></i> Approve</a>
                                    <a href="?delete-comment=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Delete</a>
                                    <a href="comments.php?edit-id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-xs"><i class="fas fa-edit"></i> Edit</a>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                </div>
                <div class="card-footer text-center"><a href="comments.php" class="uppercase">View all comments</a></div>
            <?php endif; ?>
        </div><!-- End System Health / Moderation -->
        
        <div class="card card-info" id="moderation-posts"><!-- Quick Moderation Posts -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> Quick Moderation (Posts)</h3></div>
            <?php if ($posts_pending_count == 0): ?>
                <div class="card-body"><div class="alert alert-default text-center m-0 p-3">No posts pending.</div></div>
            <?php else: ?>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php while ($row_post = mysqli_fetch_assoc($query_pending_posts)): 
                        $avatar = $row_post['author_avatar'] ?: 'assets/img/avatar.png';
                        $avatar_path = (strpos($avatar, 'http') === 0) ? htmlspecialchars($avatar) : '../' . htmlspecialchars($avatar);
                    ?>
                        <li class="item">
                            <div class="product-img"><img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="img-size-50 img-circle"></div>
                            <div class="product-info">
                                <span class="product-title"><?php echo htmlspecialchars($row_post['author_name'] ?: 'N/A'); ?></span>
                                <span class="product-description">Article: <a href="../post?name=<?php echo htmlspecialchars($row_post['slug']); ?>" target="_blank"><?php echo htmlspecialchars(short_text($row_post['title'], 40)); ?></a></span>
                                <p class="mt-1 mb-1 text-muted"><?php echo htmlspecialchars(short_text(strip_tags(html_entity_decode($row_post['content'])), 100)); ?></p>
                                <div>
                                    <a href="?approve-post=<?php echo $row_post['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-xs"><i class="fas fa-check"></i> Approve</a>
                                    <a href="?reject-post=<?php echo $row_post['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to reject (delete) this post?');"><i class="fas fa-trash"></i> Reject</a>
                                    <a href="posts.php?edit-id=<?php echo $row_post['id']; ?>" class="btn btn-secondary btn-xs"><i class="fas fa-edit"></i> Edit</a>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                </div>
                <div class="card-footer text-center"><a href="posts.php" class="uppercase">View all posts</a></div>
            <?php endif; ?>
        </div><!-- End Quick Moderation Posts -->
    </div>
    
    <div class="col-md-6">
        <div class="card card-secondary"> <!-- Content at a Glance -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-database"></i> Content at a Glance</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $count_posts_published; ?></h3><p>Published</p></div><div class="icon"><i class="fas fa-file-alt"></i></div><a href="posts.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $count_pages; ?></h3><p>Pages</p></div><div class="icon"><i class="fas fa-file-alt"></i></div><a href="pages.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-secondary"><div class="inner"><h3><?php echo $count_categories; ?></h3><p>Categories</p></div><div class="icon"><i class="fas fa-list-ol"></i></div><a href="categorys.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-secondary"><div class="inner"><h3><?php echo $count_tags; ?></h3><p>Tags</p></div><div class="icon"><i class="fas fa-tags"></i></div><a href="posts.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo $count_comments_total; ?></h3><p>Comments</p></div><div class="icon"><i class="fas fa-comments"></i></div><a href="<?php echo ($count_comments_pending > 0) ? "comments.php?status=pending" : "comments.php"; ?>" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $count_testi_total; ?></h3><p>Testimonials</p></div><div class="icon"><i class="fas fa-star"></i></div><a href="<?php echo ($count_testi_pending > 0) ? "testimonials.php" : "testimonials.php"; ?>" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo $count_messages_total; ?></h3><p>Messages</p></div><div class="icon"><i class="fas fa-envelope"></i></div><a href="messages.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-dark"><div class="inner"><h3><?php echo $count_total_users; ?></h3><p>Users</p></div><div class="icon"><i class="fas fa-users"></i></div><a href="users.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-purple"><div class="inner"><h3><?php echo $count_polls_total; ?></h3><p>Polls</p></div><div class="icon"><i class="fas fa-poll"></i></div><a href="polls.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-primary"><div class="inner"><h3><?php echo $count_slides_total; ?></h3><p>Slider</p></div><div class="icon"><i class="fas fa-images"></i></div><a href="slides.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $count_faq_total; ?></h3><p>FAQ</p></div><div class="icon"><i class="fas fa-question-circle"></i></div><a href="faq.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                    <div class="col-lg-3 col-6"><div class="small-box bg-primary"><div class="inner"><h3><?php echo $backup_count; ?></h3><p>Backups</p></div><div class="icon"><i class="fas fa-database"></i></div><a href="backup.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div></div>
                </div>
                
                <hr class="my-2">
                <p class="card-text mb-0">
                    <small class="text-muted">
                        You have <span class="badge bg-warning text-dark"><?php echo $count_posts_drafts; ?></span> draft(s), 
                        <a href="#moderation"><span class="badge bg-info"><?php echo $count_comments_pending; ?></span> comment(s)</a> and
                        <a href="#moderation-posts"><span class="badge bg-info"><?php echo $count_posts_pending; ?></span> post(s)</a> pending.
                    </small>
                </p>
            </div><!-- /.Content at a Glance  -->
        </div>
        
        <div class="card card-secondary"><!-- System Health -->
            <div class="card-header"><h3 class="card-title"><i class="fas fa-shield-alt"></i> System Health</h3></div>
            <div class="card-body">
                <strong>Database Backups</strong>
                <p class="text-muted">
                    Total files: <span class="badge bg-primary"><?php echo $backup_count; ?></span><br>
                    Last backup: <span class="text-<?php echo ($last_backup_date == 'Never' ? 'danger' : 'success'); ?>"><?php echo $last_backup_date; ?></span>
                </p>
                <a href="backup.php" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Manage Backups</a>
            </div>
        </div><!-- /.System Health -->
    </div>
</div>