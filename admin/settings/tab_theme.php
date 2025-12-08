<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-paint-brush mr-1"></i> Theme Customizer</h3>
    </div>
    <div class="card-body">
        
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-palette text-muted"></i> Colors</h5>
                <hr>
                <div class="form-group row">
                    <label class="col-sm-6 col-form-label">Primary Color</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="primaryColorInput" name="design_color_primary" value="<?php echo htmlspecialchars($settings['design_color_primary']); ?>" title="Choose your color">
                            <input type="text" class="form-control" id="primaryColorText" value="<?php echo htmlspecialchars($settings['design_color_primary']); ?>" readonly>
                        </div>
                        <small class="text-muted">Buttons, Links, Active items</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-6 col-form-label">Secondary Color</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="secColorInput" name="design_color_secondary" value="<?php echo htmlspecialchars($settings['design_color_secondary']); ?>" title="Choose your color">
                            <input type="text" class="form-control" id="secColorText" value="<?php echo htmlspecialchars($settings['design_color_secondary']); ?>" readonly>
                        </div>
                        <small class="text-muted">Badges, Subtitles, Borders</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h5><i class="fas fa-font text-muted"></i> Typography</h5>
                <hr>
                <div class="form-group">
                    <label>Google Font</label>
                    <select class="form-control custom-select" name="design_font">
                        <?php
                        $fonts = ['Nunito', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins', 'Raleway', 'Merriweather', 'Playfair Display'];
                        foreach ($fonts as $font) {
                            $selected = ($settings['design_font'] == $font) ? 'selected' : '';
                            echo "<option value='$font' $selected>$font</option>";
                        }
                        ?>
                    </select>
                    <small class="text-muted">Select the main font for the website.</small>
                </div>
                
                <div class="alert alert-light border mt-3">
                    <strong>Preview:</strong><br>
                    <span style="font-family: <?php echo $settings['design_font']; ?>, sans-serif; font-size: 1.2rem;">
                        The quick brown fox jumps over the lazy dog.
                    </span>
                </div>
            </div>
        </div>

        <hr>

        <div class="form-group mt-3">
            <label><i class="fab fa-css3-alt text-warning"></i> Custom CSS (Advanced)</label>
            <textarea name="design_custom_css" class="form-control" rows="8" style="background: #282c34; color: #abb2bf; font-family: monospace;" placeholder=".my-class { background: red; }"><?php echo htmlspecialchars($settings['design_custom_css']); ?></textarea>
            <small class="text-muted">Add your own CSS rules here to override the theme. Be careful!</small>
        </div>

    </div>
</div>

<script>
    // Petit script pour mettre à jour le champ texte quand on change la couleur
    document.getElementById('primaryColorInput').addEventListener('input', function() { document.getElementById('primaryColorText').value = this.value; });
    document.getElementById('secColorInput').addEventListener('input', function() { document.getElementById('secColorText').value = this.value; });
</script>