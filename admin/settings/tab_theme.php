<?php
// Liste des polices Google populaires (Safe list)
$fonts = [
    'Nunito' => 'Nunito (Default)',
    'Roboto' => 'Roboto',
    'Open Sans' => 'Open Sans',
    'Lato' => 'Lato',
    'Montserrat' => 'Montserrat',
    'Poppins' => 'Poppins',
    'Merriweather' => 'Merriweather (Serif)',
    'Playfair Display' => 'Playfair Display (Serif)',
    'Oswald' => 'Oswald (Condensed)',
    'Raleway' => 'Raleway'
];
?>

<div class="row h-100">
    
    <div class="col-lg-4 col-md-5 d-flex flex-column">
        
        <div class="card card-outline card-purple mb-3 shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paint-brush mr-1"></i> Theme Editor</h3>
            </div>
            <div class="card-body">
                
                <div class="form-group">
                    <label>Typography (Google Fonts)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-font"></i></span>
                        </div>
                        <select name="design_font" id="fontSelector" class="form-control">
                            <?php foreach($fonts as $f_name => $f_label): ?>
                                <option value="<?php echo $f_name; ?>" <?php if($settings['design_font'] == $f_name) echo 'selected'; ?>>
                                    <?php echo $f_label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Primary Color (Buttons, Links)</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color" id="primaryColorPicker" value="<?php echo $settings['design_color_primary']; ?>" title="Choose your color" style="max-width: 50px; padding: 5px;">
                        <input type="text" name="design_color_primary" id="primaryColorText" class="form-control" value="<?php echo $settings['design_color_primary']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Secondary Color (Subtitles, Borders)</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color" id="secondaryColorPicker" value="<?php echo $settings['design_color_secondary']; ?>" title="Choose your color" style="max-width: 50px; padding: 5px;">
                        <input type="text" name="design_color_secondary" id="secondaryColorText" class="form-control" value="<?php echo $settings['design_color_secondary']; ?>">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Custom CSS</label>
                    <textarea name="design_custom_css" id="customCssArea" class="form-control" rows="6" placeholder=".my-class { background: red; }" style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($settings['design_custom_css']); ?></textarea>
                </div>

            </div>
        </div>

        <div class="alert alert-info small">
            <i class="fas fa-info-circle"></i> Changes in the preview are temporary. Click <b>"Save Changes"</b> at the bottom of the page to apply them.
        </div>

    </div>

    <div class="col-lg-8 col-md-7">
        <div class="card card-outline card-dark h-100 shadow-none border bg-light">
            <div class="card-header d-flex justify-content-between align-items-center p-2">
                <h3 class="card-title mb-0"><i class="fas fa-eye mr-1"></i> Live Preview</h3>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-default active" onclick="resizePreview('100%')"><i class="fas fa-desktop"></i></button>
                    <button type="button" class="btn btn-default" onclick="resizePreview('768px')"><i class="fas fa-tablet-alt"></i></button>
                    <button type="button" class="btn btn-default" onclick="resizePreview('375px')"><i class="fas fa-mobile-alt"></i></button>
                </div>
            </div>
            <div class="card-body p-0 d-flex justify-content-center bg-secondary" style="height: 600px; overflow: hidden;">
                <iframe id="liveSiteFrame" src="../index.php?preview=true" style="width: 100%; height: 100%; border:none; background: #fff; transition: width 0.3s;"></iframe>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const iframe = document.getElementById('liveSiteFrame');
    
    // Attendre que l'iframe soit chargée pour pouvoir injecter du style
    iframe.onload = function() {
        updatePreview();
    };

    // --- FONCTION DE MISE À JOUR ---
    function updatePreview() {
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        const primary = document.getElementById('primaryColorText').value;
        const secondary = document.getElementById('secondaryColorText').value;
        const font = document.getElementById('fontSelector').value;
        
        // 1. Mise à jour des Variables CSS (Root)
        // Note : On surcharge les variables Bootstrap
        let style = doc.createElement('style');
        style.innerHTML = `
            :root {
                --bs-primary: ${primary} !important;
                --bs-secondary: ${secondary} !important;
                --bs-link-color: ${primary} !important;
                --bs-btn-primary-bg: ${primary} !important;
                --bs-btn-primary-border-color: ${primary} !important;
            }
            body { font-family: '${font}', sans-serif !important; }
            .text-primary { color: ${primary} !important; }
            .bg-primary { background-color: ${primary} !important; }
            .btn-primary { background-color: ${primary} !important; border-color: ${primary} !important; }
            .btn-outline-primary { color: ${primary} !important; border-color: ${primary} !important; }
            .btn-outline-primary:hover { background-color: ${primary} !important; color: #fff !important; }
        `;
        
        // On supprime l'ancien style "live-preview" s'il existe pour éviter l'accumulation
        const oldStyle = doc.getElementById('live-preview-style');
        if(oldStyle) oldStyle.remove();
        
        style.id = 'live-preview-style';
        doc.head.appendChild(style);
        
        // 2. Injection Google Font (Dynamique)
        const fontLink = doc.getElementById('live-font-link');
        if(fontLink) fontLink.remove();
        
        let link = doc.createElement('link');
        link.id = 'live-font-link';
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=' + font.replace(' ', '+') + ':wght@300;400;600;700&display=swap';
        doc.head.appendChild(link);
    }

    // --- ÉCOUTEURS D'ÉVÉNEMENTS (INPUTS) ---
    
    // Couleur Primaire
    const pPicker = document.getElementById('primaryColorPicker');
    const pText = document.getElementById('primaryColorText');
    pPicker.addEventListener('input', function() { pText.value = this.value; updatePreview(); });
    pText.addEventListener('input', function() { pPicker.value = this.value; updatePreview(); });

    // Couleur Secondaire
    const sPicker = document.getElementById('secondaryColorPicker');
    const sText = document.getElementById('secondaryColorText');
    sPicker.addEventListener('input', function() { sText.value = this.value; updatePreview(); });
    sText.addEventListener('input', function() { sPicker.value = this.value; updatePreview(); });

    // Police
    document.getElementById('fontSelector').addEventListener('change', updatePreview);
});

// Fonction redimensionnement (Mobile/Tablette/Desktop)
function resizePreview(width) {
    document.getElementById('liveSiteFrame').style.width = width;
}
</script>