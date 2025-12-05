<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Site Identity</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Site Name</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                        </div>
                        <input type="text" name="sitename" class="form-control" value="<?php echo htmlspecialchars($settings['sitename']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Site Email</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($settings['email']); ?>" required>
                    </div>
                    <small class="form-text text-muted">Used for admin notifications and the contact form.</small>
                </div>
                <div class="form-group">
                    <label>Site URL</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                        </div>
                        <input type="text" name="site_url" class="form-control" value="<?php echo htmlspecialchars($settings['site_url']); ?>" placeholder="https://..." required>
                    </div>
                    <small class="form-text text-muted">Important: Do not put a slash <code>(/)</code> at the end.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-info h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-globe-europe mr-1"></i> Regional Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Date Format</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                        <select name="date_format" class="form-control" required>
                            <option value="d.m.Y" <?php if ($settings['date_format'] == "d.m.Y") echo 'selected'; ?>>31.12.2024 (d.m.Y)</option>
                            <option value="d/m/Y" <?php if ($settings['date_format'] == "d/m/Y") echo 'selected'; ?>>31/12/2024 (d/m/Y)</option>
                            <option value="d-m-Y" <?php if ($settings['date_format'] == "d-m-Y") echo 'selected'; ?>>31-12-2024 (d-m-Y)</option>
                            <option disabled>───────────</option>
                            <option value="Y-m-d" <?php if ($settings['date_format'] == "Y-m-d") echo 'selected'; ?>>2024-12-31 (Y-m-d)</option>
                            <option value="F j, Y" <?php if ($settings['date_format'] == "F j, Y") echo 'selected'; ?>>December 31, 2024</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Text direction (RTL)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-align-right"></i></span>
                        </div>
                        <select name="rtl" class="form-control" required>
                            <option value="No" <?php if ($settings['rtl'] == 'No') echo 'selected'; ?>>No (LTR - Left to Right)</option>
                            <option value="Yes" <?php if ($settings['rtl'] == 'Yes') echo 'selected'; ?>>Yes (RTL - Arabic, Hebrew...)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>