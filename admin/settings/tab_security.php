<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-warning h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-1"></i> APIs & Keys</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><i class="fas fa-map-marked-alt text-warning mr-1"></i> Google Maps (Iframe)</label>
                    <textarea name="google_maps_code" class="form-control" rows="6" style="background:#2d3436; color:#dfe6e9; font-family: monospace; font-size: 12px;" placeholder='<iframe src="...">'><?php echo htmlspecialchars(base64_decode($settings['google_maps_code'])); ?></textarea>
                    <small class="text-muted">Paste the Google Maps embed code here.</small>
                </div>
                
                <hr>
                <label><i class="fas fa-shield-alt text-success mr-1"></i> Google reCAPTCHA v2</label>
                <div class="form-group mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 90px;">Site Key</span>
                        </div>
                        <input type="text" name="gcaptcha_sitekey" class="form-control" value="<?php echo htmlspecialchars($settings['gcaptcha_sitekey']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group input-group-sm">
                         <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 90px;">Secret Key</span>
                        </div>
                        <input type="text" name="gcaptcha_secretkey" class="form-control" value="<?php echo htmlspecialchars($settings['gcaptcha_secretkey']); ?>">
                    </div>
                </div>
                <small class="text-muted">With the following test keys, you will still not get any CAPTCHAs and all verification requests will be accepted. <code>&lt;The reCAPTCHA widget will display a warning message stating that it is for testing purposes only.
Please do not use these keys for your production traffic.&gt;</code></small>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-danger h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-code mr-1"></i> Code Injection (Advanced)</h3>
            </div>
            <div class="card-body">
                <div class="callout callout-danger py-2 px-3 mb-3">
                    <small><strong>Warning:</strong> Malformed code here can break your site's display. Use with caution (e.g., Google Analytics).</small>
                </div>
                
                <div class="form-group">
                    <label>Custom Code (Head)</label>
                    <textarea name="head_customcode" class="form-control font-monospace" rows="10" style="background:#2d3436; color:#dfe6e9; font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars(base64_decode($settings['head_customcode'])); ?></textarea>
                    <small class="text-muted">This code will be injected just before the <code>&lt;/head&gt;</code> tag.</small>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" id="customCodeSwitch" disabled <?php if ($settings['head_customcode_enabled'] == 'On') echo 'checked'; ?>>
                      <label class="custom-control-label" for="customCodeSwitch">Status (Controlled via Select below)</label>
                    </div>
                    <div class="mt-1">
                        <select name="head_customcode_enabled" class="form-control custom-select custom-select-sm" style="width: 150px;">
                            <option value="On" <?php if ($settings['head_customcode_enabled'] == 'On') echo 'selected'; ?>>Enabled</option>
                            <option value="Off" <?php if ($settings['head_customcode_enabled'] == 'Off') echo 'selected'; ?>>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>