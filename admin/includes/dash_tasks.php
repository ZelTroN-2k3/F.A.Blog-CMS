<div class="card card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="moderation-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-comments-link" data-toggle="pill" href="#tab-comments" role="tab">
                    <i class="fas fa-comments text-primary"></i> Comments 
                    <?php if($count_comments_pending > 0) echo '<span class="badge bg-danger ml-1">'.$count_comments_pending.'</span>'; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-posts-link" data-toggle="pill" href="#tab-posts" role="tab">
                    <i class="fas fa-file-alt text-warning"></i> Pending Posts
                    <?php if($posts_pending_count > 0) echo '<span class="badge bg-danger ml-1">'.$posts_pending_count.'</span>'; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-testi-link" data-toggle="pill" href="#tab-testi" role="tab">
                    <i class="fas fa-star text-warning"></i> Testimonials
                    <?php if($count_testi_pending > 0) echo '<span class="badge bg-danger ml-1">'.$count_testi_pending.'</span>'; ?>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="moderation-tabs-content">
            
            <div class="tab-pane fade show active" id="tab-comments" role="tabpanel">
                <?php if ($cmnts_pending == 0): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>No pending comments. Good job!</div>
                <?php else: ?>
                    <ul class="products-list product-list-in-card pl-3 pr-3 pt-2">
                    <?php while ($row = mysqli_fetch_assoc($query_pending_comments_list)): 
                        $avatar = ($row['guest'] == 'Yes') ? 'assets/img/avatar.png' : ($row['user_avatar'] ?: 'assets/img/avatar.png');
                        $author_name = ($row['guest'] == 'Yes') ? $row['user_id'] . ' <span class="badge badge-info">Guest</span>' : $row['user_username'];
                        $avatar_path = (strpos($avatar, 'http') === 0) ? htmlspecialchars($avatar) : '../' . htmlspecialchars($avatar);
                    ?>
                        <li class="item">
                            <div class="product-img"><img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="img-circle"></div>
                            <div class="product-info">
                                <span class="product-title"><?php echo $author_name; ?> <span class="text-muted small">on</span> <a href="../post?name=<?php echo htmlspecialchars($row['post_slug']); ?>" target="_blank"><?php echo htmlspecialchars(short_text($row['post_title'], 30)); ?></a></span>
                                <span class="product-description bg-light p-2 rounded mt-1 mb-1 font-italic">"<?php echo htmlspecialchars(short_text(html_entity_decode($row['comment']), 100)); ?>"</span>
                                <div>
                                    <a href="?approve-comment=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Approve</a>
                                    <a href="?delete-comment=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                    <div class="card-footer text-center"><a href="comments.php">View All</a></div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="tab-posts" role="tabpanel">
                <?php if ($posts_pending_count == 0): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>No pending articles.</div>
                <?php else: ?>
                    <ul class="products-list product-list-in-card pl-3 pr-3 pt-2">
                    <?php while ($row_post = mysqli_fetch_assoc($query_pending_posts)): 
                        $avatar = $row_post['author_avatar'] ?: 'assets/img/avatar.png';
                        $avatar_path = (strpos($avatar, 'http') === 0) ? htmlspecialchars($avatar) : '../' . htmlspecialchars($avatar);
                    ?>
                        <li class="item">
                            <div class="product-img"><img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="img-size-50 img-circle"></div>
                            <div class="product-info">
                                <span class="product-title"><?php echo htmlspecialchars($row_post['title']); ?></span>
                                <span class="product-description">By: <strong><?php echo htmlspecialchars($row_post['author_name']); ?></strong> - <small><?php echo date('d M Y', strtotime($row_post['created_at'])); ?></small></span>
                                <div class="mt-1">
                                    <a href="?approve-post=<?php echo $row_post['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Publish</a>
                                    <a href="posts.php?edit-id=<?php echo $row_post['id']; ?>" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="../post?name=<?php echo $row_post['slug']; ?>" target="_blank" class="btn btn-xs btn-default"><i class="fas fa-eye"></i> Preview</a>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="tab-testi" role="tabpanel">
                <?php if(!$query_pending_testimonials_list || mysqli_num_rows($query_pending_testimonials_list) == 0): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>No pending testimonials.</div>
                <?php else: ?>
                    <ul class="products-list product-list-in-card pl-3 pr-3 pt-2">
                    <?php while($row_t = mysqli_fetch_assoc($query_pending_testimonials_list)): ?>
                        <li class="item">
                            <div class="product-info ml-0">
                                <span class="product-title"><?php echo htmlspecialchars($row_t['name']); ?></span>
                                <span class="product-description">"<?php echo htmlspecialchars(substr($row_t['content'], 0, 80)); ?>..."</span>
                                <div class="mt-1">
                                    <a href="?approve-testimonial=<?php echo $row_t['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Approve</a>
                                    <a href="?delete-testimonial=<?php echo $row_t['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<div class="card card-outline card-indigo">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-microchip mr-1"></i> Latest Projects</h3>
        <div class="card-tools"><a href="add_project.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add</a></div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-valign-middle">
                <thead><tr><th>Project</th><th>Views</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                if (!$q_latest_proj || mysqli_num_rows($q_latest_proj) == 0) {
                    echo '<tr><td colspan="3" class="text-center text-muted">No projects found.</td></tr>';
                } else {
                    while($proj = mysqli_fetch_assoc($q_latest_proj)) {
                        $p_img = (!empty($proj['image']) && file_exists('../'.str_replace('../','',$proj['image']))) ? '../'.str_replace('../','',$proj['image']) : '../assets/img/project-no-image.png';
                        $status_badge = ($proj['active']=='Yes') ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Draft</span>';
                        echo '<tr>
                            <td><img src="'.htmlspecialchars($p_img).'" style="width:80px; height:50px; object-fit:cover;" class="img-square mr-2"> '.htmlspecialchars($proj['title']).'</td>
                            <td>'.$proj['views'].'</td>
                            <td>'.$status_badge.'</td>
                        </tr>';
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>