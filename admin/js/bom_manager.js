// Fonction pour ajouter une ligne
function addBomRow(containerId, type, data = null) {
    const container = document.getElementById(containerId);
    const index = container.children.length; // Index unique
    
    // Valeurs par défaut ou chargées
    const name = data ? data.name : '';
    const qty = data ? data.qty : '1';
    const link = data ? data.link : '';
    // Pour l'image, on triche un peu : on demande l'URL de l'image
    // (Récupérer l'image automatiquement d'un site tiers est complexe et souvent bloqué)
    const img = data ? data.img : ''; 

    const html = `
    <div class="card p-3 mb-2 border bg-light bom-row">
        <div class="row g-2 align-items-end">
            <div class="col-md-1 text-center">
                <img src="${img || '../assets/img/no-image-icon.png'}" class="img-thumbnail img-preview" style="width:50px; height:50px; object-fit:cover;">
            </div>
            <div class="col-md-3">
                <label class="small text-muted">Name</label>
                <input type="text" name="${type}[${index}][name]" class="form-control form-control-sm" value="${name}" placeholder="Ex: Arduino Uno" required>
            </div>
            <div class="col-md-2">
                <label class="small text-muted">Quantity</label>
                <input type="text" name="${type}[${index}][qty]" class="form-control form-control-sm" value="${qty}">
            </div>
            <div class="col-md-3">
                <label class="small text-muted">Purchase/Source Link</label>
                <input type="url" name="${type}[${index}][link]" class="form-control form-control-sm" value="${link}" placeholder="https://store...">
            </div>
             <div class="col-md-2">
                <label class="small text-muted">Image URL</label>
                <input type="url" name="${type}[${index}][img]" class="form-control form-control-sm img-input" value="${img}" placeholder="https://.../img.jpg" onchange="updateRowPreview(this)">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="this.closest('.bom-row').remove()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
}

// Mettre à jour la petite image quand on change l'URL
function updateRowPreview(input) {
    const imgTag = input.closest('.row').querySelector('.img-preview');
    imgTag.src = input.value || '../assets/img/no-image-icon.png';
}

// Fonction pour charger les données existantes (pour Edit)
function loadBomData(containerId, type, jsonString) {
    try {
        const data = JSON.parse(jsonString);
        if (Array.isArray(data)) {
            data.forEach(item => addBomRow(containerId, type, item));
        }
    } catch (e) {
        console.log("No JSON data or invalid format");
    }
}