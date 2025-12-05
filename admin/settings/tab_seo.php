<div class="callout callout-info">
    <h5><i class="fas fa-search"></i> Search Engine Optimization (SEO)</h5>
    <p class="text-muted mb-0">Configure how your site appears on Google and social networks.</p>
</div>

<div class="card card-outline card-secondary mb-4">
    <div class="card-header">
        <h3 class="card-title">Global Meta Data</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Main Title (Meta Title & OG:Title)</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                </div>
                <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($settings['meta_title']); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Meta Description</label>
            <textarea name="description" class="form-control" rows="2" required><?php echo htmlspecialchars($settings['description']); ?></textarea>
            <small class="text-muted">Recommended: 150-160 characters.</small>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Author (Meta Author)</label>
                    <input type="text" name="meta_author" class="form-control" value="<?php echo htmlspecialchars($settings['meta_author']); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Generator (Meta Generator)</label>
                    <input type="text" name="meta_generator" class="form-control" value="<?php echo htmlspecialchars($settings['meta_generator']); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Robots (Meta Robots)</label>
                    <select name="meta_robots" class="form-control custom-select">
                         <option value="index, follow" <?php if($settings['meta_robots'] == 'index, follow') echo 'selected'; ?>>Index, Follow (Default)</option>
                         <option value="noindex, nofollow" <?php if($settings['meta_robots'] == 'noindex, nofollow') echo 'selected'; ?>>NoIndex, NoFollow</option>
                    </select>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <label>Favicon URL (.ico / .png)</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><img src="../<?php echo htmlspecialchars($settings['favicon_url']); ?>" style="width:25px; height:25px;" onerror="this.style.display='none'"></span>
                    </div>
                    <input type="text" name="favicon_url" class="form-control" value="<?php echo htmlspecialchars($settings['favicon_url']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <label>Apple Touch Icon URL</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><img src="../<?php echo htmlspecialchars($settings['apple_touch_icon_url']); ?>" style="width:25px; height:25px;" onerror="this.style.display='none'"></span>
                    </div>
                    <input type="text" name="apple_touch_icon_url" class="form-control" value="<?php echo htmlspecialchars($settings['apple_touch_icon_url']); ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="text-primary mb-3"><i class="fas fa-share-alt mr-1"></i> Social Networks</h5>
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