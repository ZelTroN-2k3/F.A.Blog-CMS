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
        <a href="add_image.php" class="btn btn-app bg-success"><i class="fas fa-camera-retro"></i> Add Image</a>
        <a href="upload_file.php" class="btn btn-app bg-success"><i class="fas fa-upload"></i> Upload File</a>
        <a href="<?php echo $settings['site_url']; ?>" class="btn btn-app bg-info"><i class="fas fa-eye"></i> Visit Site</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $my_published; ?></h3>
                <p>My Published Articles</p>
            </div>
            <div class="icon"><i class="fas fa-newspaper"></i></div>
            <a href="posts.php" class="small-box-footer">View List <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo number_format($my_views); ?></h3>
                <p>Total Views</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <span class="small-box-footer">Performance</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $my_pending; ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="posts.php?status=pending" class="small-box-footer">Check Status <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo $my_comments; ?></h3>
                <p>Comments Received</p>
            </div>
            <div class="icon"><i class="fas fa-comments"></i></div>
            <a href="comments.php" class="small-box-footer">Moderate <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-pen-nib mr-1"></i> My Latest Articles
                </h3>
                <div class="card-tools">
                    <a href="add_post.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Write New
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                    <tr>
                        <th>Article</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Requête spécifique pour l'affichage du tableau
                    $stmt_latest = mysqli_prepare($connect, "SELECT * FROM posts WHERE author_id = ? ORDER BY id DESC LIMIT 5");
                    mysqli_stmt_bind_param($stmt_latest, "i", $my_id);
                    mysqli_stmt_execute($stmt_latest);
                    $res_latest = mysqli_stmt_get_result($stmt_latest);

                    if (mysqli_num_rows($res_latest) == 0) {
                        echo '<tr><td colspan="5" class="text-center text-muted">You haven\'t written any articles yet.</td></tr>';
                    } else {
                        while ($post = mysqli_fetch_assoc($res_latest)) {
                            $img = !empty($post['image']) ? '../'.$post['image'] : 'assets/img/no-image.png';
                            
                            $status = '<span class="badge bg-secondary">Draft</span>';
                            if ($post['active'] == 'Yes') $status = '<span class="badge bg-success">Published</span>';
                            elseif ($post['active'] == 'Pending') $status = '<span class="badge bg-warning">Pending</span>';

                            echo '
                            <tr>
                                <td>
                                    <img src="' . htmlspecialchars($img) . '" alt="Img" class="img-circle img-size-32 mr-2" style="object-fit:cover;">
                                    ' . htmlspecialchars(short_text($post['title'], 40)) . '
                                </td>
                                <td>
                                    ' . $post['views'] . ' <small class="text-muted"><i class="fas fa-eye"></i></small>
                                </td>
                                <td>' . $status . '</td>
                                <td><small>' . date("d M Y", strtotime($post['created_at'])) . '</small></td>
                                <td>
                                    <a href="edit_post.php?id=' . $post['id'] . '" class="text-muted" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../post?name=' . $post['slug'] . '" target="_blank" class="text-muted ml-2" title="View">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>';
                        }
                    }
                    mysqli_stmt_close($stmt_latest);
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
