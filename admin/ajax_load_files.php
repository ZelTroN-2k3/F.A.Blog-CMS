<?php
// admin/ajax_load_files.php

// 1. Inclusion et Sécurité
if (!file_exists('../core.php')) { die('<div class="alert alert-danger">Erreur: core.php introuvable</div>'); }
include "../core.php"; 

if (!isset($_SESSION['sec-username'])) { die('<div class="alert alert-danger">Accès refusé.</div>'); }

// 2. Récupération des fichiers depuis la BDD
$query = mysqli_query($connect, "SELECT * FROM `files` ORDER BY id DESC LIMIT 50");

if (mysqli_num_rows($query) > 0) {
    echo '<div class="row">';
    while ($row = mysqli_fetch_assoc($query)) {
        
        // --- 1. Récupération du nom du fichier ---
        // On essaie de trouver le nom du fichier quelle que soit la colonne
        $filename = '';
        if(isset($row['file'])) { $filename = $row['file']; }
        elseif(isset($row['filename'])) { $filename = $row['filename']; }
        elseif(isset($row['path'])) { $filename = $row['path']; }
        
        // On ne garde que le nom (ex: "photo.jpg") pour chercher proprement
        $filename_clean = basename($filename);

        if(empty($filename_clean)) continue;

        // --- 2. Détection automatique du dossier (ORDRE IMPORTANT) ---
        // J'ai ajouté '../uploads/files/' en premier priorité
        $possible_paths = [
            '../uploads/files/',   // <--- VOTRE DOSSIER
            '../uploads/', 
            '../uploads/posts/', 
            '../assets/img/', 
            '../media/'
        ];

        $found_path = '';       // Chemin pour l'affichage (Admin)
        $db_value_clean = '';   // Chemin propre pour la BDD (sans le ../)

        // On teste chaque dossier pour voir où est le fichier
        foreach ($possible_paths as $path) {
            if (file_exists($path . $filename_clean)) {
                $found_path = $path . $filename_clean;
                
                // Pour la BDD, on veut "uploads/files/image.jpg", pas "../uploads/..."
                $db_value_clean = str_replace('../', '', $found_path);
                break; // On a trouvé, on arrête de chercher
            }
        }

        // Si l'image est introuvable physiquement, on met un placeholder
        $img_src_display = $found_path;
        if (empty($found_path)) {
            // On force le chemin présumé pour tenter l'affichage quand même (avec onerror)
            $img_src_display = '../uploads/files/' . $filename_clean;
            $db_value_clean = 'uploads/files/' . $filename_clean;
        }

        // --- 3. Affichage ---
        $ext = strtolower(pathinfo($filename_clean, PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo '
            <div class="col-6 col-md-3 mb-3">
                <div class="card h-100 file-selector-item" style="cursor:pointer; border:1px solid #dee2e6;" 
                     onclick="selectFile(\''.$db_value_clean.'\', \''.$img_src_display.'\')">
                    
                    <div style="height: 100px; overflow:hidden; background:#f4f6f9; display:flex; align-items:center; justify-content:center; position:relative;">
                        <img src="'.$img_src_display.'" style="width:100%; height:100%; object-fit:cover;" 
                             onerror="this.src=\'assets/img/no-image.png\'; this.style.objectFit=\'contain\';">
                    </div>
                    
                    <div class="card-body p-2 text-center bg-white">
                        <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;" title="'.$filename_clean.'">
                            '.$filename_clean.'
                        </small>
                    </div>
                </div>
            </div>';
        }
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-info text-center">
            <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i><br>
            Aucune image trouvée dans la table `files`.<br>
            <small>Vérifiez que vous avez bien uploadé des fichiers.</small>
          </div>';
}
?>