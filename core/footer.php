<?php
function footer()
{
    // --- 1. GLOBALES (Correction : Ajout de $logged et $rowu) ---
    global $phpblog_version, $connect, $settings, $purifier, $logged, $rowu;
    
    // --- 2. RÉCUPÉRER LES PAGES DU FOOTER ---
    $footer_content = [
        'legal' => null,
        'contact_methods' => null,
        'most_viewed' => null,
        'cta_buttons' => null,
        'trust_badges' => null
    ];
    
    if (!isset($purifier)) {
        $purifier = get_purifier();
    }

    $stmt_footer = mysqli_prepare($connect, "
        SELECT page_key, title, content 
        FROM footer_pages 
        WHERE active = 'Yes' AND page_key IN ('legal', 'contact_methods', 'most_viewed', 'cta_buttons', 'trust_badges')
    ");
    
    if ($stmt_footer) {
        mysqli_stmt_execute($stmt_footer);
        $result_footer = mysqli_stmt_get_result($stmt_footer);
        while ($page = mysqli_fetch_assoc($result_footer)) {
            $footer_content[$page['page_key']] = [
                'title' => htmlspecialchars($page['title']),
                'content' => $purifier->purify($page['content'])
            ];
        }
        mysqli_stmt_close($stmt_footer);
    }
?>
            </div> <?php
    // Widgets Footer
    $run = mysqli_query($connect, "SELECT * FROM widgets WHERE position = 'footer' AND active = 'Yes' ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($run)) {
        render_widget($row);
    }
?>
    </div> 
    <?php
        // --- AJOUTEZ LE CODE ICI ---
        // TRAITEMENT NEWSLETTER POPUP
        if (isset($_POST['subscribe_popup'])) {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Vérifier si l'email existe déjà
                $stmt = mysqli_prepare($connect, "SELECT id FROM newsletter WHERE email = ?");
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 0) {
                    // Insérer si nouveau
                    $stmt_ins = mysqli_prepare($connect, "INSERT INTO newsletter (email) VALUES (?)");
                    mysqli_stmt_bind_param($stmt_ins, "s", $email);
                    mysqli_stmt_execute($stmt_ins);
                    mysqli_stmt_close($stmt_ins);
                }
                mysqli_stmt_close($stmt);
                
                // Feedback JS simple (Pop-up navigateur)
                echo "<script>alert('Merci pour votre inscription !');</script>";
            }
        }
        // ---------------------------
    ?>    
    <footer class="bg-dark text-light pt-5 pb-3 mt-3">
        <div class="<?php echo ($settings['layout'] == 'Wide') ? 'container-fluid' : 'container'; ?>">
            <div class="row gy-4">

                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4">
                        <a href="<?php echo htmlspecialchars($settings['site_url']); ?>" class="d-flex align-items-center text-white mb-3 mb-md-0 me-md-auto text-decoration-none">
                            <?php if (!empty($settings['site_logo']) && file_exists($settings['site_logo'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="<?php echo htmlspecialchars($settings['sitename']); ?>" height="54" style="max-width: 200px; object-fit: contain;">
                            <?php else: ?>
                                <span class="fs-4"><b><i class="far fa-newspaper me-2"></i> <?php echo htmlspecialchars($settings['sitename']); ?></b></span>
                            <?php endif; ?>
                        </a>                        
                    </h5>
                    <p class="text-white-50"><?php echo htmlspecialchars($settings['description']); ?></p>
                    
                    <h5 class="text-uppercase fw-bold mt-4 mb-3">Follow us</h5>
                    <div class="mt-3">
                        <?php if ($settings['facebook'] != ''): ?><a href="<?php echo htmlspecialchars($settings['facebook']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-facebook fs-4"></i></a><?php endif; ?>
                        <?php if ($settings['twitter'] != ''): ?><a href="<?php echo htmlspecialchars($settings['twitter']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-twitter-x fs-4"></i></a><?php endif; ?>
                        <?php if ($settings['instagram'] != ''): ?><a href="<?php echo htmlspecialchars($settings['instagram']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-instagram fs-4"></i></a><?php endif; ?>
                        <?php if ($settings['youtube'] != ''): ?><a href="<?php echo htmlspecialchars($settings['youtube']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-youtube fs-4"></i></a><?php endif; ?>
                        <?php if ($settings['linkedin'] != ''): ?><a href="<?php echo htmlspecialchars($settings['linkedin']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-linkedin fs-4"></i></a><?php endif; ?>
                        <?php if ($settings['discord'] != ''): ?><a href="<?php echo htmlspecialchars($settings['discord']); ?>" target="_blank" class="text-white-50 me-3"><i class="bi bi-discord fs-4"></i></a><?php endif; ?>
                        </div>
                    <div class="mt-3">
                        <h5 class="text-uppercase fw-bold mb-4">Others</h5>
                        <ul class="list-unstyled mb-0">
                            <div class="d-flex gap-2 justify-content-start flex-wrap">
                                <a href="sitemap.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sitemap fa-lg text-info"></i> <span class="small">Sitemap</span></a>
                                <a href="rss.php" class="btn btn-outline-light btn-sm"><i class="fas fa-rss fa-lg text-warning"></i> <span class="small">RSS Feed</span></a>
                            </div>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4">Navigation</h5>
                    <ul class="list-unstyled mb-0">
                        <?php
                        $menu_query = mysqli_query($connect, "SELECT * FROM `menu` WHERE active = 'Yes' ORDER BY id ASC");
                        while ($menu_item = mysqli_fetch_assoc($menu_query)) {
                            echo '<li class="mb-2"><a href="' . htmlspecialchars($menu_item['path']) . '" class="text-white-50 text-decoration-none"><i class="' . htmlspecialchars($menu_item['fa_icon']) . ' me-2" style="width: 1.2em;"></i> ' . htmlspecialchars($menu_item['page']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <?php if ($footer_content['legal']): ?>
                        <h5 class="text-uppercase fw-bold mb-4"><?php echo $footer_content['legal']['title']; ?></h5>
                        <div class="text-white-50 small mb-3"><?php echo $footer_content['legal']['content']; ?></div>
                    <?php endif; ?>
                    <?php if ($footer_content['contact_methods']): ?>
                        <h5 class="text-uppercase fw-bold mb-4"><?php echo $footer_content['contact_methods']['title']; ?></h5>
                        <div class="text-white-50 small"><?php echo $footer_content['contact_methods']['content']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <?php if ($footer_content['cta_buttons']): ?>
                        <h5 class="text-uppercase fw-bold mb-4"><?php echo $footer_content['cta_buttons']['title']; ?></h5>
                        <div class="text-white-50 small mb-3"><?php echo $footer_content['cta_buttons']['content']; ?></div>
                    <?php endif; ?>
                    <?php if ($footer_content['most_viewed']): ?>
                        <h5 class="text-uppercase fw-bold mb-4"><?php echo $footer_content['most_viewed']['title']; ?></h5>
                        <div class="text-white-50 small mb-3"><?php echo $footer_content['most_viewed']['content']; ?></div>
                    <?php endif; ?>
                    <?php if ($footer_content['trust_badges']): ?>
                        <h5 class="text-uppercase fw-bold mb-4"><?php echo $footer_content['trust_badges']['title']; ?></h5>
                        <div class="text-white-50 small"><?php echo $footer_content['trust_badges']['content']; ?></div>
                    <?php else: ?>
                        <h5 class="text-uppercase fw-bold mb-4">Logo</h5>
                        <img src="<?php echo htmlspecialchars($settings['favicon_url']); ?>" alt="Logo" width="96" height="96">
                    <?php endif; ?>
                </div>
            
            </div>
            
            <div class="text-center text-white-50 border-top border-secondary-subtle pt-3 mt-4">
                <p class="small mb-0">
                    &copy; <?php echo date("Y") .' '. htmlspecialchars($settings['sitename']); ?>. All rights reserved.
                    <span class="mx-2">|</span>
                    <i>Powered by <?php echo htmlspecialchars($settings['sitename']); ?> v<?php echo htmlspecialchars($phpblog_version); ?></i>
                </p>
            </div>
            <div class="scroll-btn"><div class="scroll-btn-arrow"></div></div>
        </div>
    </footer>

    <?php
        // --- LOGIQUE POPUPS ---
        global $current_page;
        $page_condition = "display_pages = 'all'";
        if ($current_page == 'index.php') { $page_condition = "(display_pages = 'all' OR display_pages = 'home')"; }

        $popups_to_show = [];
        $stmt_popups = mysqli_prepare($connect, "SELECT * FROM popups WHERE active = 'Yes' AND $page_condition");

        if ($stmt_popups) {
            mysqli_stmt_execute($stmt_popups);
            $result_popups = mysqli_stmt_get_result($stmt_popups);
            while ($popup = mysqli_fetch_assoc($result_popups)) {
                $session_key = 'popup_shown_' . $popup['id'];
                if ($popup['show_once_per_session'] == 'Yes' && isset($_SESSION[$session_key])) { continue; }
                $popups_to_show[] = $popup;
            }
            mysqli_stmt_close($stmt_popups);
        }

        if (!empty($popups_to_show)) {
            
            // --- AJOUT CSS POUR LA TAILLE ---
            echo '
            <style>
                .modal-design-modern .modal-content {
                    border: none;
                    border-radius: 15px; /* Coins arrondis */
                    overflow: hidden;
                }
                .design-popup-content {
                    min-height: 400px; /* Hauteur minimale forcée */
                    display: flex;
                    flex-direction: column;
                    justify-content: center; /* Centre le contenu verticalement */
                    background-size: cover;
                    background-position: center;
                }
                @media (max-width: 768px) {
                    .design-popup-content {
                        min-height: auto; /* Sur mobile, on laisse la hauteur auto */
                        padding: 30px 20px !important;
                    }
                }
            </style>';
            // --------------------------------
            
            if (!isset($purifier)) { $purifier = get_purifier(); }
            
            foreach ($popups_to_show as $popup) {
                // ... (le reste de votre boucle foreach reste identique) ...
                $modal_id = 'popupModal' . (int)$popup['id'];
                
                // --- DESIGN LOGIC ---
                $modal_content_html = '';
                $extra_modal_class = '';
                $close_btn_class = 'btn-close'; // Defaut bootstrap (noir)

                if ($popup['popup_type'] == 'Design') {
                    // --- STYLE MODERNE (IMAGE DE FOND) ---
                    $bg_image = !empty($popup['background_image']) ? $popup['background_image'] : '';
                    $extra_modal_class = 'modal-design-modern';
                    
                    // CSS Inline pour l'image de fond
                    $bg_style = "background: url('{$bg_image}') no-repeat center center / cover;";
                    
                    // Bouton close blanc
                    $close_btn_class = 'btn-close btn-close-white';

                    // Construction du HTML spécifique
                    $modal_content_html .= '<div class="design-popup-content text-white p-5 position-relative" style="'.$bg_style.'">';
                    
                    // Overlay sombre pour lisibilité
                    $modal_content_html .= '<div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50" style="z-index:0;"></div>';
                    
                    // Contenu (au-dessus de l'overlay)
                    $modal_content_html .= '<div class="position-relative" style="z-index:1;">';
                    
                    // Titre
                    if(!empty($popup['main_title'])) {
                        $modal_content_html .= '<h2 class="fw-bold mb-3">'.nl2br(htmlspecialchars($popup['main_title'])).'</h2>';
                    }
                    
                    // Sous-titre
                    if(!empty($popup['subtitle'])) {
                        $modal_content_html .= '<p class="lead mb-4 opacity-75">'.nl2br(htmlspecialchars($popup['subtitle'])).'</p>';
                    }
                    
                    // Newsletter
                    if($popup['newsletter_active'] == 'Yes') {
                        $modal_content_html .= '
                        <form action="" method="POST" class="mb-3">
                            <input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="Votre email" required style="border-radius: 4px;">
                            </div>
                            <button type="submit" name="subscribe_popup" class="btn btn-warning btn-lg w-100 fw-bold text-white" style="background-color: #ff9800; border:none; border-radius: 4px;">Recevoir la newsletter</button>
                        </form>';
                    }
                    
                    // Footer Text
                    if(!empty($popup['footer_text'])) {
                        $modal_content_html .= '<small class="d-block text-white-50 mt-3">'.htmlspecialchars($popup['footer_text']).'</small>';
                    }
                    
                    $modal_content_html .= '</div></div>'; // Fin relative / content

                } else {
                    // --- STYLE STANDARD (Summernote) ---
                    $modal_content_html = '<div class="modal-header">
                            <h5 class="modal-title">'.htmlspecialchars($popup['title']).'</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">'.$purifier->purify($popup['content']).'</div>';
                }

                echo "
                <div class='modal fade {$extra_modal_class}' id='{$modal_id}' tabindex='-1' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered modal-lg'>
                        <div class='modal-content overflow-hidden border-0'>
                            ";
                
                // Si Design, le bouton fermer est en absolute par dessus l'image
                if ($popup['popup_type'] == 'Design') {
                    echo "<button type='button' class='{$close_btn_class} position-absolute top-0 end-0 m-3' data-bs-dismiss='modal' aria-label='Close' style='z-index: 10;'></button>";
                }
                
                echo $modal_content_html;
                
                echo "
                        </div>
                    </div>
                </div>";
            }
        }
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Theme Switcher
        const themeSwitcherBtn = document.getElementById('theme-switcher-btn');
        const lightTheme = document.getElementById('theme-light');
        const darkTheme = document.getElementById('theme-dark');
        const iconMoon = document.getElementById('theme-icon-moon');
        const iconSun = document.getElementById('theme-icon-sun');

        function updateTheme(theme) {
            if (theme === 'dark') {
                lightTheme.disabled = true; darkTheme.disabled = false;
                if(iconMoon) iconMoon.style.display = 'none';
                if(iconSun) iconSun.style.display = 'inline-block';
            } else {
                lightTheme.disabled = false; darkTheme.disabled = true;
                if(iconMoon) iconMoon.style.display = 'inline-block';
                if(iconSun) iconSun.style.display = 'none';
            }
        }
        let currentTheme = localStorage.getItem('theme');
        if (!currentTheme) { currentTheme = 'light'; localStorage.setItem('theme', currentTheme); }
        updateTheme(currentTheme);

        if (themeSwitcherBtn) {
            themeSwitcherBtn.addEventListener('click', function () {
                let newTheme = (localStorage.getItem('theme') === 'dark') ? 'light' : 'dark';
                updateTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }

        // Popups
        <?php
        if (!empty($popups_to_show)) {
            foreach ($popups_to_show as $popup) {
                $modal_id = 'popupModal' . (int)$popup['id'];
                $delay_ms = (int)$popup['delay_seconds'] * 1000;
                echo "setTimeout(function() { var popupModal = new bootstrap.Modal(document.getElementById('{$modal_id}'), {}); popupModal.show(); }, {$delay_ms});";
                if ($popup['show_once_per_session'] == 'Yes') { $_SESSION['popup_shown_' . $popup['id']] = true; }
            }
        }
        ?>
    });
    </script>

<?php
    if ($current_page == 'post.php') {
        echo '<script src="assets/js/post-interactions.js"></script>';
    }

    // --- BANNIÈRE COOKIES ---
    if (isset($settings['cookie_consent_enabled']) && $settings['cookie_consent_enabled'] == 1) {
        $cookie_msg = !empty($settings['cookie_message']) ? $settings['cookie_message'] : "This site uses cookies...";
    ?>
        <div id="cookieConsentBanner" class="fixed-bottom bg-dark text-white p-3 shadow-lg" style="display:none; z-index: 9999; border-top: 3px solid #0d6efd;">
            <div class="<?php echo ($settings['layout'] == 'Wide') ? 'container-fluid' : 'container'; ?>">
                <div class="d-flex justify-content-between align-items-center flex-column flex-md-row">
                    <div class="mb-2 mb-md-0">
                        <i class="fas fa-cookie-bite text-warning me-2" style="font-size: 1.5rem;"></i>
                        <span class="small"><?php echo htmlspecialchars($cookie_msg); ?></span>
                        <a href="privacy-policy.php" class="text-white text-decoration-underline small ms-1">Learn more</a>
                    </div>
                    <div><button id="acceptCookiesBtn" class="btn btn-primary btn-sm fw-bold px-4"><i class="fas fa-check"></i> I accept</button></div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Fonction pour lire un cookie
                function getCookie(name) {
                    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                    return match ? match[2] : null;
                }

                // Si le cookie n'existe pas, on affiche la bannière
                if (!getCookie("cookieConsentAccepted")) {
                    var banner = document.getElementById("cookieConsentBanner");
                    banner.style.display = "block";

                    document.getElementById("acceptCookiesBtn").addEventListener("click", function() {
                        // 1. Créer le cookie (Valable 365 jours)
                        var date = new Date();
                        date.setTime(date.getTime() + (365*24*60*60*1000)); // 1 an
                        document.cookie = "cookieConsentAccepted=true; expires=" + date.toUTCString() + "; path=/; SameSite=Lax";
                        
                        // 2. Cacher la bannière
                        banner.style.display = "none";
                        
                        // 3. Recharger la page pour activer les scripts PHP (Analytics, etc.)
                        location.reload();
                    });
                }
            });
        </script>
    <?php
    } // Fin cookie consent

    // ============================================================
    // --- LOGIQUE DE NOTIFICATION GLOBALE (CORRIGÉE) ---
    // ============================================================
    if ($logged == 'Yes') {
        // Définition de l'URL absolue pour AJAX (Corrige l'erreur "Undefined variable")
        $ajax_absolute_url = $settings['site_url'] . '/ajax_chat.php';
    ?>
        <audio id="globalChatSound" src="<?php echo $settings['site_url']; ?>/assets/sounds/message.mp3" preload="auto"></audio>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const originalTitle = document.title;
                const chatBadge = document.getElementById('nav-chat-badge');
                
                // On vérifie si on est DÉJÀ sur la page chat.php
                if (window.location.pathname.indexOf('chat.php') === -1) {
                    
                    let lastUnreadCount = null; 
                    const chatSound = document.getElementById('globalChatSound');

                    // Fonction de vérification des messages non lus
                    function checkUnreadMessages() {
                        // Utilisation de la variable PHP définie juste au-dessus
                        fetch('<?php echo $ajax_absolute_url; ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'action=check_unread_count'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                let currentCount = parseInt(data.count);
                                
                                // 1. MISE À JOUR DU BADGE
                                if (chatBadge) {
                                    chatBadge.innerText = currentCount;
                                    chatBadge.style.display = (currentCount > 0) ? 'inline-block' : 'none';
                                }

                                // Init au premier chargement
                                if (lastUnreadCount === null) {
                                    lastUnreadCount = currentCount;
                                    updatePageTitle(currentCount);
                                    return;
                                }

                                // SI NOUVEAU MESSAGE
                                if (currentCount > lastUnreadCount) {
                                    playNotificationSound();
                                    showToastNotification(currentCount);
                                }
                                
                                updatePageTitle(currentCount);
                                lastUnreadCount = currentCount;
                            }
                        })
                        .catch(error => console.error('Erreur Chat Polling:', error));
                    }

                    // Mise à jour du titre de la page
                    function updatePageTitle(count) {
                        if (count > 0) {
                            document.title = "(" + count + ") " + originalTitle;
                        } else {
                            document.title = originalTitle;
                        }
                    }

                    // Affichage de la notification toast
                    function showToastNotification(count) {
                        // Option A : Utilisation du plugin AdminLTE/Bootstrap (Le plus joli)
                        if (typeof $(document).Toasts === 'function') {
                            $(document).Toasts('create', {
                                title: 'New Message',
                                // J'ajoute un lien cliquable direct vers le tchat
                                body: 'You have ' + count + ' new message(s).<br><a href="chat.php" class="text-white text-decoration-underline fw-bold mt-2 d-inline-block">Click here to reply</a>',
                                class: 'bg-success',
                                icon: 'fas fa-comments',
                                position: 'bottomRight',
                                
                                // --- MODIFICATION ICI ---
                                autohide: false, // Empêche la disparition automatique
                                close: true      // Affiche la petite croix pour fermer
                                // ------------------------
                            });
                        } 
                        // Option B : Fallback (Si le plugin n'est pas chargé)
                        else {
                            let toast = document.createElement('div');
                            // Design "Boîte persistante" avec bouton fermer et lien
                            toast.innerHTML = `
                                <div style="position:fixed; bottom:20px; right:20px; background:#28a745; color:white; padding:15px; border-radius:5px; z-index:9999; box-shadow:0 4px 15px rgba(0,0,0,0.3); min-width: 250px; font-family: sans-serif;">
                                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                                        <strong style="font-size:1.1em;"><i class="fas fa-envelope"></i> New Message!</strong>
                                        <button onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:white; cursor:pointer; font-size:1.2em; line-height:1;">&times;</button>
                                    </div>
                                    <div style="margin-bottom:10px;">You have ${count} new message(s) waiting.</div>
                                    <a href="chat.php" style="display:block; text-align:center; background:white; color:#28a745; text-decoration:none; padding:8px; border-radius:4px; font-weight:bold;">
                                        <i class="fas fa-reply"></i> Reply Now
                                    </a>
                                </div>`;
                            document.body.appendChild(toast);
                            
                            // J'ai supprimé le setTimeout(), donc la boîte reste là pour toujours.
                        }
                    }
                    
                    // Lecture du son de notification
                    function playNotificationSound() {
                        if(chatSound) {
                            chatSound.play().catch(error => { console.warn("Autoplay bloqué."); });
                        }
                    }
                    
                    setInterval(checkUnreadMessages, 4000); // Toutes les 4 secondes
                    // Premier check immédiat
                    checkUnreadMessages();
                }   
            });
        </script>
    <?php 
    } // Fin if logged
    ?>
    </body>
    </html>
<?php
}
?>