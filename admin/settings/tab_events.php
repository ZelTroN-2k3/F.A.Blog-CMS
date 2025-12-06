<div class="row">
    <div class="col-md-12">
        <div class="callout callout-success">
            <h5><i class="fas fa-calendar-alt"></i> Seasonal Events & Holidays</h5>
            <p class="text-muted">Activate special decorations and banners for holidays like Christmas, New Year, or Black Friday.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-magic mr-1"></i> Visual Effects</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Decoration Theme</label>
                    <select name="event_effect" class="form-control custom-select">
                        <option value="None" <?php if($settings['event_effect'] == 'None') echo 'selected'; ?>>None (Normal)</option>
                        <option value="Snow" <?php if($settings['event_effect'] == 'Snow') echo 'selected'; ?>>❄️ Falling Snow (Christmas/Winter)</option>
                        <option value="Confetti" <?php if($settings['event_effect'] == 'Confetti') echo 'selected'; ?>>🎉 Confetti (New Year/Celebration)</option>
                        <option value="Grayscale" <?php if($settings['event_effect'] == 'Grayscale') echo 'selected'; ?>>⚫ Black & White (Black Friday)</option>
                    </select>
                </div>
                
                <div class="alert alert-light border mt-3">
                    <small><i class="fas fa-info-circle"></i> Effects are applied to the public side of the website only.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-danger h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> Top Announcement Bar</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Activate Banner</label>
                    <select name="event_banner_active" class="form-control custom-select">
                        <option value="No" <?php if($settings['event_banner_active'] == 'No') echo 'selected'; ?>>No</option>
                        <option value="Yes" <?php if($settings['event_banner_active'] == 'Yes') echo 'selected'; ?>>Yes</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Banner Background Color</label>
                    <input type="color" name="event_banner_color" class="form-control" value="<?php echo htmlspecialchars($settings['event_banner_color']); ?>">
                </div>

                <div class="form-group">
                    <label>Message (HTML Allowed)</label>
                    <textarea name="event_banner_content" class="form-control" rows="3" placeholder="Ex: <strong class='text-warning'>BLACK FRIDAY:</strong> -50% on all products!"><?php echo htmlspecialchars($settings['event_banner_content']); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>