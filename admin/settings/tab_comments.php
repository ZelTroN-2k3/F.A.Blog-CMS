<div class="card card-outline card-purple">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-comments mr-1"></i> Moderation Rules</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Comment Approval</label>
            <div class="custom-control custom-switch">
                <input type="hidden" name="comments_approval" value="0">
                <input type="checkbox" class="custom-control-input" id="commentApprove" name="comments_approval" value="1" <?php if($settings['comments_approval'] == 1) echo 'checked'; ?>>
                <label class="custom-control-label" for="commentApprove">Comments must be manually approved by an admin</label>
            </div>
            <small class="text-muted">If disabled, comments will be displayed immediately.</small>
        </div>
        <hr>
        <div class="form-group">
            <label class="text-danger"><i class="fas fa-ban"></i> Blacklist (Forbidden Words)</label>
            <textarea name="comments_blacklist" class="form-control" rows="10" style="background:#2d3436; color:#dfe6e9;" placeholder="viagra, casino, ..."><?php echo htmlspecialchars($settings['comments_blacklist']); ?></textarea>
            <small class="text-muted">Separate words with commas. Comments containing these words will be automatically marked as <code>"Pending"</code> or deleted according to your logic.</small>
        </div>
    </div>
</div>