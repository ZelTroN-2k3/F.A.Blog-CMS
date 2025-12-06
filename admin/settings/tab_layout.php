<h5 class="text-primary mb-3"><i class="fas fa-paint-brush mr-1"></i> Visual Appearance</h5>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Theme</label>
            <select name="theme" class="form-control custom-select" required>
                <?php
                $themes = ["Bootstrap 5", "Cerulean", "Cosmo", "Cyborg", "Flatly", "Journal", "Litera", "Lumen", "Lux", "Materia", "Minty", "Morph", "Pulse", "Quartz", "Sandstone", "Simplex", "Sketchy", "Solar", "Spacelab", "United", "Vapor", "Yeti", "Zephyr"];
                foreach($themes as $th) {
                    $selected = ($settings['theme'] == $th) ? 'selected' : '';
                    echo "<option value=\"$th\" $selected>$th</option>";
                }
                ?>
            </select>
            <small class="form-text text-muted">Defines the global colors and fonts of the site.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Layout</label>
            <select name="layout" class="form-control custom-select" required>
                <option value="Wide" <?php if ($settings['layout'] == 'Wide') echo 'selected'; ?>>Wide (Maximum Width)</option>
                <option value="Fixed" <?php if ($settings['layout'] == 'Fixed') echo 'selected'; ?>>Boxed (Framed)</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Sidebar Position</label>
            <select name="sidebar_position" class="form-control custom-select" required>
                <option value="Left" <?php if ($settings['sidebar_position'] == 'Left') echo 'selected'; ?>>Left</option>
                <option value="Right" <?php if ($settings['sidebar_position'] == 'Right') echo 'selected'; ?>>Right</option>
            </select>
        </div>
    </div>
</div>

<hr class="my-4">

<h5 class="text-primary mb-3"><i class="fas fa-th mr-1"></i> Homepage Structure</h5>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Posts per Row</label>
            <div class="input-group">
                 <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-grip-horizontal"></i></span>
                </div>
                <select name="posts_per_row" class="form-control" required>
                    <option value="2" <?php if ($settings['posts_per_row'] == "2") echo 'selected'; ?>>2 Columns</option>
                    <option value="3" <?php if ($settings['posts_per_row'] == "3") echo 'selected'; ?>>3 Columns</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Posts per Page (Blog)</label>
            <input type="number" name="posts_per_page" class="form-control" value="<?php echo (int) $settings['posts_per_page']; ?>" min="1" required>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="form-group">
            <label>Projects per Page (Blog)</label>
            <input type="number" name="projects_per_page" class="form-control" value="<?php echo (int) $settings['projects_per_page']; ?>" min="1" required>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>"Latest News" Bar</label>
            <select name="latestposts_bar" class="form-control custom-select" required>
                <option value="Enabled" <?php if ($settings['latestposts_bar'] == 'Enabled') echo 'selected'; ?>>Show</option>
                <option value="Disabled" <?php if ($settings['latestposts_bar'] == 'Disabled') echo 'selected'; ?>>Hide</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Homepage Slider</label>
            <select name="homepage_slider" class="form-control custom-select">
                <option value="Featured" <?php if($settings['homepage_slider'] == 'Featured') echo 'selected'; ?>>"Featured" Slider (Auto)</option>
                <option value="Custom" <?php if($settings['homepage_slider'] == 'Custom') echo 'selected'; ?>>Custom Slider</option>
            </select>
        </div>
    </div>
</div>
<div class="row mt-2">
    <div class="col-md-12">
        <div class="form-group">
             <label class="mr-2">Sticky Header: </label>
             <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="stickyOn" name="sticky_header" value="On" class="custom-control-input" <?php if ($settings['sticky_header'] == 'On') echo 'checked'; ?>>
                <label class="custom-control-label text-success" for="stickyOn">On</label>
             </div>
             <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="stickyOff" name="sticky_header" value="Off" class="custom-control-input" <?php if ($settings['sticky_header'] == 'Off') echo 'checked'; ?>>
                <label class="custom-control-label text-secondary" for="stickyOff">Off</label>
             </div>
             <small class="form-text text-muted">Enable/Disable Sticky Header: <code>[On/Off]</code></small>
        </div>
        
    </div>
</div>

<hr class="my-4">

<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h3 class="card-title">Site Logo</h3>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($settings['site_logo'])): ?>
                    <div class="mb-3 p-3 bg-dark d-inline-block rounded">
                        <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" style="max-height: 60px;">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="delete_logo" value="Yes" id="delLogo">
                        <label class="form-check-label text-danger font-weight-bold" for="delLogo"><i class="fas fa-trash"></i> Delete Logo</label>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">No logo (Text used)</div>
                <?php endif; ?>
                <div class="custom-file text-left">
                    <input name="site_logo" class="custom-file-input" type="file" id="fileLogo">
                    <label class="custom-file-label" for="fileLogo">Choose an image...</label>
                </div>
                <small class="text-muted mt-2 d-block">Formats: PNG <code>(Transparency recommended)</code>, JPG, SVG. Max height: 50px.</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h3 class="card-title">Background Image</h3>
            </div>
            <div class="card-body">
                <?php if ($settings['background_image'] != ""): ?>
                    <div class="row mb-3 align-items-center">
                        <div class="col-4">
                            <img src="../<?php echo htmlspecialchars($settings['background_image']); ?>" class="img-fluid img-thumbnail shadow-sm">
                        </div>
                        <div class="col-8">
                            <a href="?delete_bgrimg&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">
                                <i class="fas fa-trash"></i> Delete Image
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border text-center mb-3">No custom background set.</div>
                <?php endif; ?>
                
                <div class="custom-file">
                    <input name="background_image" class="custom-file-input" type="file" id="formFileBg">
                    <label class="custom-file-label" for="formFileBg">Choose an image...</label>
                </div>
                <small class="text-muted mt-2 d-block">Applies to the site's <code>`body`</code> (background).</small>
            </div>
        </div>
    </div>
</div>