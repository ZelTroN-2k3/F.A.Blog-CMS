<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search mr-1"></i> Meta Tags & Icons</h3>
            </div>
            <div class="card-body">
                
                <div class="form-group">
                    <label>Meta Title (Homepage)</label>
                    <input type="text" class="form-control" name="meta_title" value="<?php echo htmlspecialchars($settings['meta_title']); ?>" placeholder="Site Name - Slogan">
                </div>
                
                <div class="form-group">
                    <label>Meta Description (Homepage)</label>
                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($settings['description']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" class="form-control" name="meta_generator" value="<?php echo htmlspecialchars($settings['meta_generator']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Meta Author</label>
                            <input type="text" class="form-control" name="meta_author" value="<?php echo htmlspecialchars($settings['meta_author']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Robots Tag</label>
                    <select class="form-control" name="meta_robots">
                        <option value="index, follow" <?php if($settings['meta_robots'] == 'index, follow') echo 'selected'; ?>>Index, Follow (Recommended)</option>
                        <option value="noindex, nofollow" <?php if($settings['meta_robots'] == 'noindex, nofollow') echo 'selected'; ?>>NoIndex, NoFollow (Private)</option>
                        <option value="index, nofollow" <?php if($settings['meta_robots'] == 'index, nofollow') echo 'selected'; ?>>Index, NoFollow</option>
                    </select>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Favicon URL</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1"><img src="../<?php echo htmlspecialchars($settings['favicon_url']); ?>" style="width:20px; height:20px; object-fit: contain;" onerror="this.style.display='none'"></span>
                                </div>
                                <input type="text" name="favicon_url" class="form-control" value="<?php echo htmlspecialchars($settings['favicon_url']); ?>">
                            </div>
                            <small class="text-muted">.ico or .png (32x32)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Apple Touch Icon</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1"><img src="../<?php echo htmlspecialchars($settings['apple_touch_icon_url']); ?>" style="width:20px; height:20px; object-fit: contain;" onerror="this.style.display='none'"></span>
                                </div>
                                <input type="text" name="apple_touch_icon_url" class="form-control" value="<?php echo htmlspecialchars($settings['apple_touch_icon_url']); ?>">
                            </div>
                            <small class="text-muted">.png (180x180)</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-6">
        
        <div class="card card-outline card-success mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sitemap mr-1"></i> Sitemap XML</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">Submit this URL to <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>.</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo $settings['site_url']; ?>/sitemap.php" readonly id="sitemapUrl">
                    <div class="input-group-append">
                        <a href="../sitemap.php" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-external-link-alt"></i> Open</a>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="http://www.google.com/ping?sitemap=<?php echo $settings['site_url']; ?>/sitemap.php" target="_blank" class="btn btn-block btn-success">
                        <i class="fab fa-google"></i> Ping Google
                    </a>
                </div>
            </div>
        </div>

        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-robot mr-1"></i> Robots.txt Editor</h3>
            </div>
            <div class="card-body">
                <?php
                $robots_file = '../robots.txt';
                $robots_content = "User-agent: *\nAllow: /"; // Contenu par défaut
                
                if (file_exists($robots_file)) {
                    $robots_content = file_get_contents($robots_file);
                }
                ?>
                <div class="form-group">
                    <textarea name="robots_txt" class="form-control" rows="8" style="font-family: monospace; background:#2d3436; color:#dfe6e9;"><?php echo htmlspecialchars($robots_content); ?></textarea>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle"></i> Use this to allow or block bots from specific folders.</small>
            </div>
        </div>

    </div>
</div>

<hr>

<div class="card card-outline card-indigo">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-share-alt mr-1"></i> Social Networks</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white" style="width: 40px; justify-content: center;"><i class="fab fa-facebook-f"></i></span>
                        </div>
                        <input type="text" name="facebook" class="form-control" placeholder="Page Facebook" value="<?php echo htmlspecialchars($settings['facebook']); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text text-white" style="background-color: #E1306C; width: 40px; justify-content: center;"><i class="fab fa-instagram"></i></span>
                        </div>
                        <input type="text" name="instagram" class="form-control" placeholder="Profil Instagram" value="<?php echo htmlspecialchars($settings['instagram']); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white" style="width: 40px; justify-content: center; font-weight:bold; font-family: sans-serif;">𝕏</span>
                        </div>
                        <input type="text" name="twitter" class="form-control" placeholder="Compte X (Twitter)" value="<?php echo htmlspecialchars($settings['twitter']); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white" style="width: 40px; justify-content: center;"><i class="fab fa-youtube"></i></span>
                        </div>
                        <input type="text" name="youtube" class="form-control" placeholder="Chaîne YouTube" value="<?php echo htmlspecialchars($settings['youtube']); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white" style="width: 40px; justify-content: center;"><i class="fab fa-linkedin-in"></i></span>
                        </div>
                        <input type="text" name="linkedin" class="form-control" placeholder="Profil LinkedIn" value="<?php echo htmlspecialchars($settings['linkedin']); ?>">
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text text-white" style="background-color: #5865F2; width: 40px; justify-content: center;"><i class="fab fa-discord"></i></span>
                        </div>
                        <input type="text" name="discord" class="form-control" placeholder="Serveur Discord" value="<?php echo htmlspecialchars($settings['discord'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>