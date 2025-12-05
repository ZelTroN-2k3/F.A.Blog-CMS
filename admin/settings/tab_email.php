<div class="row">
    <div class="col-md-12">
        <div class="callout callout-warning">
            <h5><i class="fas fa-envelope-open-text"></i> Email settings</h5>
            <p class="text-muted">Use an SMTP server (Gmail, Outlook, or your hosting provider) to prevent your emails from landing in SPAM folders.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-secondary h-100">
            <div class="card-header">
                <h3 class="card-title">Sending Method</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Mail Protocol</label>
                    <select name="mail_protocol" class="form-control custom-select">
                        <option value="mail" <?php if ($settings['mail_protocol'] == 'mail') echo 'selected'; ?>>PHP Mail() (Standard)</option>
                        <option value="smtp" <?php if ($settings['mail_protocol'] == 'smtp') echo 'selected'; ?>>SMTP (Recommended)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sender Name</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        </div>
                        <input type="text" name="mail_from_name" class="form-control" value="<?php echo htmlspecialchars($settings['mail_from_name']); ?>" placeholder="Ex: My Awesome Blog">
                    </div>
                </div>
                <div class="form-group">
                    <label>Sender Email</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                        </div>
                        <input type="email" name="mail_from_email" class="form-control" value="<?php echo htmlspecialchars($settings['mail_from_email']); ?>" placeholder="noreply@mysite.com">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-outline card-primary h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-server mr-1"></i> SMTP Credentials</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>SMTP Server (Host)</label>
                            <input type="text" name="smtp_host" class="form-control" placeholder="ex: smtp.gmail.com" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port</label>
                            <input type="number" name="smtp_port" class="form-control" placeholder="587 or 465" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SMTP User</label>
                            <input type="text" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SMTP Password</label>
                            <input type="password" name="smtp_pass" class="form-control" autocomplete="new-password" value="<?php echo htmlspecialchars($settings['smtp_pass']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Encryption</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-secondary <?php if($settings['smtp_enc']=='tls') echo 'active'; ?>">
                            <input type="radio" name="smtp_enc" value="tls" <?php if($settings['smtp_enc']=='tls') echo 'checked'; ?>> TLS
                        </label>
                        <label class="btn btn-outline-secondary <?php if($settings['smtp_enc']=='ssl') echo 'active'; ?>">
                            <input type="radio" name="smtp_enc" value="ssl" <?php if($settings['smtp_enc']=='ssl') echo 'checked'; ?>> SSL
                        </label>
                        <label class="btn btn-outline-secondary <?php if($settings['smtp_enc']=='none') echo 'active'; ?>">
                            <input type="radio" name="smtp_enc" value="none" <?php if($settings['smtp_enc']=='none') echo 'checked'; ?>> None
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>