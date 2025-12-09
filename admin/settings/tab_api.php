<div class="card card-outline card-purple">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-mobile-alt mr-1"></i> REST API Settings</h3>
    </div>
    <div class="card-body">
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> The API allows external applications (Mobile Apps, other websites) to retrieve your content via JSON.
        </div>

        <div class="form-group">
            <label>Enable API Access</label>
            <div class="custom-control custom-switch">
                <input type="hidden" name="api_enabled" value="No">
                <input type="checkbox" class="custom-control-input" id="apiEnableSwitch" name="api_enabled" value="Yes" <?php if($settings['api_enabled'] == 'Yes') echo 'checked'; ?>>
                <label class="custom-control-label" for="apiEnableSwitch">Allow external requests</label>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <label>API Key (Secret Token)</label>
            <div class="input-group">
                <input type="text" name="api_key" id="apiKeyField" class="form-control" value="<?php echo htmlspecialchars($settings['api_key']); ?>" readonly>
                <div class="input-group-append">
                    <button type="button" class="btn btn-warning" onclick="generateApiKey()"><i class="fas fa-sync"></i> Generate New Key</button>
                </div>
            </div>
            <small class="text-muted">Clients must send this key in the request URL: <code>/api/get_posts.php?key=YOUR_KEY</code></small>
        </div>

        <hr>
        
        <h5>Endpoints Documentation</h5>
        <ul>
            <li><code>GET /api/get_posts.php</code> - List latest articles</li>
            <li><code>GET /api/get_post.php?id=1</code> - Get single article details</li>
            <li><code>GET /api/get_categories.php</code> - List all categories</li>
        </ul>

    </div>
</div>

<script>
function generateApiKey() {
    // Génère une chaîne aléatoire de 32 caractères
    var chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    var keyLength = 32;
    var key = "";
    for (var i = 0; i <= keyLength; i++) {
        var randomNumber = Math.floor(Math.random() * chars.length);
        key += chars.substring(randomNumber, randomNumber +1);
    }
    document.getElementById("apiKeyField").value = key;
}
</script>