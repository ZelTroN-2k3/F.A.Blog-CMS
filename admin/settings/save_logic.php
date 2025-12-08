<?php
// --- LOGIQUE : Suppression d'image de fond (BDD) ---
if (isset($_GET['delete_bgrimg'])) {
    validate_csrf_token_get();
    
    $bgr_img_path = '../' . $settings['background_image'];
    if (!empty($settings['background_image']) && file_exists($bgr_img_path) && is_file($bgr_img_path)) {
        @unlink($bgr_img_path);
    }
    
    $stmt = mysqli_prepare($connect, "UPDATE settings SET background_image = '' WHERE id = 1");
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo '<meta http-equiv="refresh" content="0;url=settings.php">';
    exit;
}

// --- LOGIQUE : Sauvegarde des paramètres (POST) ---
if (isset($_POST['save'])) {

    validate_csrf_token();

    $uploadOk = 1;
    
    // 1. Gestion de l'image de fond
    $new_background_image = $settings['background_image']; 
    if (isset($_FILES['background_image']) && $_FILES['background_image']['name'] != '') {
        $target_dir    = "../uploads/other/"; 
        $target_file   = $target_dir . basename($_FILES["background_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $new_filename = "bgr_" . uniqid() . "." . $imageFileType;
        $destination_path = $target_dir . $new_filename;

        $check = @getimagesize($_FILES["background_image"]["tmp_name"]);
        if ($check === false) {
            echo '<div class="alert alert-danger">The file is not a valid image.</div>';
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $destination_path)) {
                if (!empty($settings['background_image']) && file_exists('../' . $settings['background_image'])) {
                    @unlink('../' . $settings['background_image']);
                }
                $new_background_image = 'uploads/other/' . $new_filename;
            } else {
                echo '<div class="alert alert-danger">Error uploading the background image.</div>';
                $uploadOk = 0;
            }
        }
    }

    // 2. Gestion du Logo
    $new_site_logo = $settings['site_logo'];
    if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == 'Yes') {
        if (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo'])) {
            @unlink('../' . $settings['site_logo']);
        }
        $new_site_logo = '';
    }
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['name'] != '') {
        $target_dir_logo = "../uploads/other/";
        $ext_logo = strtolower(pathinfo($_FILES["site_logo"]["name"], PATHINFO_EXTENSION));
        $filename_logo = "logo_" . uniqid() . "." . $ext_logo;
        $dest_logo = $target_dir_logo . $filename_logo;
        
        if(in_array($ext_logo, ["jpg", "png", "jpeg", "gif", "webp", "svg"])) {
            if (move_uploaded_file($_FILES["site_logo"]["tmp_name"], $dest_logo)) {
                if (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo']) && $new_site_logo != '') {
                     @unlink('../' . $settings['site_logo']);
                }
                $new_site_logo = 'uploads/other/' . $filename_logo;
            }
        } else {
            echo '<div class="alert alert-warning">Logo format not supported.</div>';
        }
    }

    // 3. Mise à jour BDD
    if ($uploadOk == 1) {
        try {
            // AJOUT DES CHAMPS "EVENTS" À LA FIN DE LA REQUÊTE
            $sql = "UPDATE settings SET 
                            site_url = ?, sitename = ?, description = ?, email = ?, 
                            gcaptcha_sitekey = ?, gcaptcha_secretkey = ?, head_customcode = ?, 
                            head_customcode_enabled = ?,
                            facebook = ?, instagram = ?, twitter = ?, youtube = ?, 
                            linkedin = ?, discord = ?, rtl = ?, date_format = ?, 
                            layout = ?, latestposts_bar = ?, homepage_slider = ?, sidebar_position = ?, 
                            posts_per_row = ?, theme = ?, posts_per_page = ?, projects_per_page = ?, 
                            background_image = ?, 
                            meta_title = ?, favicon_url = ?, apple_touch_icon_url = ?,
                            meta_author = ?, meta_generator = ?, meta_robots = ?,
                            sticky_header = ?, google_maps_code = ?, site_logo = ?,
                            
                            mail_protocol = ?, mail_from_name = ?, mail_from_email = ?,
                            smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_pass = ?, smtp_enc = ?,
                            comments_approval = ?, comments_blacklist = ?,
                            cookie_consent_enabled = ?, cookie_message = ?,

                            event_mode = ?, event_effect = ?, event_banner_active = ?, event_banner_content = ?, event_banner_color = ?

                        WHERE id = 1";
                        
            $stmt = mysqli_prepare($connect, $sql);
            if ($stmt === false) { throw new Exception("MySQL preparation error: " . mysqli_error($connect)); }

            // MISE À JOUR DES TYPES : On ajoute 5 's' à la fin pour les 5 nouveaux champs
            // Ancien total : 46. Nouveau total : 51
            $types = str_repeat('s', 38) . 'isssisis' . 'sssss'; 
            
            $head_customcode_encoded = base64_encode($_POST['head_customcode']);
            $google_maps_encoded = base64_encode($_POST['google_maps_code']);

            $comments_approval_val = isset($_POST['comments_approval']) ? (int)$_POST['comments_approval'] : 0;
            $cookie_consent_val = isset($_POST['cookie_consent_enabled']) ? (int)$_POST['cookie_consent_enabled'] : 0;
            $smtp_port_val = (int)$_POST['smtp_port'];
            
            // Récupération sécurisée des valeurs Event (avec valeurs par défaut)
            $event_effect = $_POST['event_effect'] ?? 'None';
            $event_banner_active = $_POST['event_banner_active'] ?? 'No';
            $event_banner_content = $_POST['event_banner_content'] ?? '';
            $event_banner_color = $_POST['event_banner_color'] ?? '#dc3545';

            mysqli_stmt_bind_param($stmt, $types,
                $_POST['site_url'], $_POST['sitename'], $_POST['description'], $_POST['email'],
                $_POST['gcaptcha_sitekey'], $_POST['gcaptcha_secretkey'], $head_customcode_encoded,
                $_POST['head_customcode_enabled'], $_POST['facebook'], $_POST['instagram'],
                $_POST['twitter'], $_POST['youtube'], $_POST['linkedin'], $_POST['discord'], $_POST['rtl'],
                $_POST['date_format'], $_POST['layout'], $_POST['latestposts_bar'],
                $_POST['homepage_slider'], $_POST['sidebar_position'], $_POST['posts_per_row'],
                $_POST['theme'], $_POST['posts_per_page'], $_POST['projects_per_page'], 
                $new_background_image,
                $_POST['meta_title'], $_POST['favicon_url'], $_POST['apple_touch_icon_url'],
                $_POST['meta_author'], $_POST['meta_generator'], $_POST['meta_robots'],
                $_POST['sticky_header'], $google_maps_encoded, $new_site_logo,
                
                $_POST['mail_protocol'], $_POST['mail_from_name'], $_POST['mail_from_email'],
                $_POST['smtp_host'], $smtp_port_val, $_POST['smtp_user'], $_POST['smtp_pass'], $_POST['smtp_enc'],
                $comments_approval_val, $_POST['comments_blacklist'],
                $cookie_consent_val, $_POST['cookie_message'],

                // NOUVEAUX PARAMÈTRES BINDÉS
                $event_effect,        // event_mode (on utilise le même que l'effet pour simplifier)
                $event_effect,        // event_effect
                $event_banner_active,
                $event_banner_content,
                $event_banner_color
            );

            mysqli_stmt_execute($stmt);
            // --- AJOUT LOG & CACHE FLUSH ---
            if(function_exists('log_activity')) {
                log_activity("Update Settings", "Updated global site settings.");
            }
            if(function_exists('clear_site_cache')) {
                clear_site_cache(); // Vide le dossier cache/
            }
            // -------------------------------          
            mysqli_stmt_close($stmt);

            echo '
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Success!</h5>
                Settings saved successfully.
            </div>';
            
            // Rechargement immédiat
            $stmt_reload = mysqli_prepare($connect, "SELECT * FROM settings WHERE id = 1");
            mysqli_stmt_execute($stmt_reload);
            $result_reload = mysqli_stmt_get_result($stmt_reload);
            $settings = mysqli_fetch_assoc($result_reload);
            mysqli_stmt_close($stmt_reload);

        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Error saving settings: ' . $e->getMessage() . '</div>';
        }
    }
}
?>