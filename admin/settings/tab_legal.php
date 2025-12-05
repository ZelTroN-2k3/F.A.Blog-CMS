<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cookie-bite mr-1"></i> Cookie Consent (GDPR)</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Display Banner</label>
                    <select name="cookie_consent_enabled" class="form-control custom-select">
                        <option value="1" <?php if($settings['cookie_consent_enabled'] == 1) echo 'selected'; ?>>Yes</option>
                        <option value="0" <?php if($settings['cookie_consent_enabled'] == 0) echo 'selected'; ?>>No</option>
                    </select>
                    <small class="form-text text-muted">Enable/Disable Cookie Consent Banner: <code>[Yes/No]</code></small>
                </div>
            </div>
            <div class="col-md-9">
                <div class="form-group">
                    <label>Banner Message</label>
                    <textarea name="cookie_message" class="form-control" rows="2"><?php echo htmlspecialchars($settings['cookie_message']); ?></textarea>
                    <small class="text-muted">Ex: This site uses cookies to offer you the best service.</small>
                </div>
            </div>
        </div>
    </div>
</div>