<?php
// -------------------------------------------------------------------------
// includes/newsletter_logic.php
// Gère l'export CSV, l'envoi d'emails et la gestion des abonnés
// -------------------------------------------------------------------------

// 1. SÉCURITÉ : On s'assure que core.php est chargé et l'utilisateur connecté
// (Nécessaire car ce fichier est inclus AVANT header.php dans le fichier principal)
if (!isset($connect)) {
    $core_path = dirname(__DIR__) . '/../core.php'; 
    if (file_exists($core_path)) require_once $core_path;
}

// Vérification basique de session (identique à header.php) pour sécuriser les actions POST
if (!isset($_SESSION['sec-username'])) {
    header("Location: ../login");
    exit;
}

// Initialisation des variables
$preview_html = '';
$form_data = [
    'template' => 'simple',
    'title' => '',
    'content' => '',
    'featured_post_id' => 0,
    'promo_btn_text' => '',
    'promo_btn_url' => ''
];
$display_message = '';

// Gestion des messages de session
if (isset($_SESSION['message'])) {
    $display_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Messages d'erreur GET
if (isset($_GET['error']) && $_GET['error'] == 'db_error') {
     $display_message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>A database error occurred during the export attempt.</div>';
} elseif (isset($_GET['info']) && $_GET['info'] == 'no_subscribers_to_export') {
     $display_message = '<div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>There are no subscribers to export.</div>';
}

// =========================================================================
// 2. EXPORT CSV (Doit être exécuté avant tout HTML)
// =========================================================================
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $query = mysqli_query($connect, "SELECT email FROM newsletter ORDER BY email ASC");
    
    if (mysqli_num_rows($query) > 0) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=newsletter_subscribers_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Email Address'));
        while ($row = mysqli_fetch_assoc($query)) {
            fputcsv($output, array($row['email'])); 
        }
        fclose($output);
        exit; 
    } else {
        header('Location: newsletter.php?info=no_subscribers_to_export');
        exit;
    }
}

// =========================================================================
// 3. TRAITEMENT DES FORMULAIRES (POST)
// =========================================================================

// --- AJOUTER UN ABONNÉ ---
if (isset($_POST['add_subscriber'])) {
    validate_csrf_token();
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Invalid email address: <b>' . htmlspecialchars($email) . '</b></div>';
    } else {
        $stmt_check = mysqli_prepare($connect, "SELECT id FROM newsletter WHERE email = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) > 0) {
            $_SESSION['message'] = '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Email <b>' . htmlspecialchars($email) . '</b> is already subscribed.</div>';
        } else {
            $stmt_add = mysqli_prepare($connect, "INSERT INTO newsletter (email) VALUES (?)");
            mysqli_stmt_bind_param($stmt_add, "s", $email);
            mysqli_stmt_execute($stmt_add);
            mysqli_stmt_close($stmt_add);
            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Email <b>' . htmlspecialchars($email) . '</b> added.</div>';
        }
        mysqli_stmt_close($stmt_check);
    }
    header('Location: newsletter.php'); exit;
}

// --- DÉSABONNEMENT UNIQUE ---
if (isset($_POST['action']) && $_POST['action'] === 'unsubscribe_from_list') {
    validate_csrf_token(); 
    $unsubscribe_id = (int)$_POST['unsubscribe_id'];
    $email_for_message = $_POST['email_for_message']; 

    $stmt = mysqli_prepare($connect, "DELETE FROM `newsletter` WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $unsubscribe_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $_SESSION['message'] = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Email <b>' . htmlspecialchars($email_for_message) . '</b> unsubscribed.</div>';
    header('Location: newsletter.php'); exit;
}

// --- ACTIONS EN MASSE ---
if (isset($_POST['apply_bulk_action'])) {
    validate_csrf_token();
    $action = $_POST['bulk_action'];
    $subscriber_ids = $_POST['subscriber_ids'] ?? [];

    if ($action == 'delete' && !empty($subscriber_ids)) {
        $placeholders = implode(',', array_fill(0, count($subscriber_ids), '?'));
        $types = str_repeat('i', count($subscriber_ids));
        $stmt = mysqli_prepare($connect, "DELETE FROM newsletter WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types, ...$subscriber_ids);
        mysqli_stmt_execute($stmt);
        $count = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['message'] = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button><b>' . $count . '</b> subscriber(s) deleted.</div>';
    }
    header('Location: newsletter.php'); exit;
}

// --- ENVOI DE MESSAGE / APERÇU ---
if (isset($_POST['send_mass_message']) || isset($_POST['preview_message'])) {
    validate_csrf_token();
    
    $form_data = [
        'title'            => $_POST['title'] ?? '',
        'content'          => $_POST['content'] ?? '',
        'template'         => $_POST['template'] ?? 'simple',
        'featured_post_id' => (int)($_POST['featured_post_id'] ?? 0),
        'promo_btn_text'   => $_POST['promo_btn_text'] ?? '',
        'promo_btn_url'    => $_POST['promo_btn_url'] ?? ''
    ];

    $from             = $settings['email'];
    $sitename         = $settings['sitename'];
    $unsubscribe_link = $settings['site_url'] . '/unsubscribe.php';
    $message_body     = '';

    // Construction du corps du message selon le template
    switch ($form_data['template']) {
        case 'featured_post':
            $post_html = '';
            if ($form_data['featured_post_id'] > 0) {
                $stmt_post = mysqli_prepare($connect, "SELECT title, slug, image, content FROM posts WHERE id = ?");
                mysqli_stmt_bind_param($stmt_post, "i", $form_data['featured_post_id']);
                mysqli_stmt_execute($stmt_post);
                $post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_post));
                mysqli_stmt_close($stmt_post);
                
                if ($post) {
                    $post_url = $settings['site_url'] . '/post?name=' . $post['slug'];
                    $post_excerpt = short_text(strip_tags(html_entity_decode($post['content'])), 150);
                    $post_html = '<hr><h2 style="font-size: 20px; margin-bottom: 10px;">À la une: ' . htmlspecialchars($post['title']) . '</h2>' . 
                                 ($post['image'] ? '<a href="' . $post_url . '"><img src="' . htmlspecialchars($post['image']) . '" alt="Image" style="width:100%; max-width: 500px; height:auto; border-radius: 5px;"></a>' : '') . 
                                 '<p style="font-size: 16px; margin-top: 15px;">' . $post_excerpt . '</p><a href="' . $post_url . '" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px;">Lire la suite</a><hr style="margin-top: 25px;">';
                }
            }
            $message_body = '<h1 style="font-size: 24px;">' . htmlspecialchars($form_data['title']) . '</h1>' . $post_html . '<div style="margin-top: 20px;">' . $form_data['content'] . '</div>';
            break;
        
        case 'promo':
            $button_html = (!empty($form_data['promo_btn_text']) && !empty($form_data['promo_btn_url'])) ? 
                '<div style="margin-top: 25px; text-align: center;"><a href="' . htmlspecialchars($form_data['promo_btn_url']) . '" style="display: inline-block; padding: 12px 25px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold;">' . htmlspecialchars($form_data['promo_btn_text']) . '</a></div>' : '';
            $message_body = '<div style="background-color: #f4f4f4; padding: 20px; border-radius: 5px; text-align: center;"><h1 style="font-size: 24px;">' . htmlspecialchars($form_data['title']) . '</h1><p style="font-size: 16px; margin-top: 15px;">' . $form_data['content'] . '</p>' . $button_html . '</div>';
            break;
            
        default: // Simple
            $message_body = '<h1 style="font-size: 24px;">' . htmlspecialchars($form_data['title']) . '</h1><br />' . $form_data['content'];
            break;
    }

    // Wrapper HTML Global
    $message = '<html><head><meta charset="utf-8"></head><body style="font-family: Arial, sans-serif; line-height: 1.6;"><div style="width: 90%; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;"><div style="background-color: #007bff; color: #ffffff; padding: 20px; text-align: center;"><h1 style="margin: 0; font-size: 28px;"><a href="' . $settings['site_url'] . '/" style="color: #ffffff; text-decoration: none;">' . $sitename . '</a></h1></div><div style="padding: 30px;">' . $message_body . '</div><div style="background-color: #f9f9f9; color: #777; padding: 20px; text-align: center; border-top: 1px solid #ddd;"><p style="font-size: 12px; margin: 0;">&copy; ' . date('Y') . ' ' . $sitename . '. Tous droits réservés.</p><p style="font-size: 12px; margin: 5px 0 0 0;"><a href="' . $unsubscribe_link . '" style="color: #007bff; text-decoration: none;">Se désabonner</a></p></div></div></body></html>';

    if (isset($_POST['preview_message'])) {
        $preview_html = $message;
        $display_message = '<div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Ceci est un aperçu. L\'email n\'a pas été envoyé.</div>';
    } elseif (isset($_POST['send_mass_message'])) {
        $emails = [];
        $run2 = mysqli_query($connect, "SELECT email FROM `newsletter`");
        while ($row = mysqli_fetch_assoc($run2)) $emails[] = $row['email'];

        if (!empty($emails)) {
            $bcc_string = implode(',', $emails);
            $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n" . 'From: ' . $sitename . ' <' . $from . '>' . "\r\n" . 'Bcc: ' . $bcc_string . "\r\n"; 
            @mail($from, $form_data['title'], $message, $headers);
            $display_message = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Message envoyé avec succès à <b>' . count($emails) . '</b> abonné(s).</div>';
            $form_data = ['template' => 'simple', 'title' => '', 'content' => '', 'featured_post_id' => 0, 'promo_btn_text' => '', 'promo_btn_url' => '' ];
        } else {
             $display_message = '<div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button>Il n\'y a aucun abonné.</div>';
        }
    }
}

// =========================================================================
// 4. RÉCUPÉRATION DES DONNÉES POUR LA VUE
// =========================================================================

// Derniers articles pour le select
$latest_posts = [];
$posts_query = mysqli_query($connect, "SELECT id, title FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT 10");
while ($post_row = mysqli_fetch_assoc($posts_query)) $latest_posts[] = $post_row;

// Liste des abonnés pour le tableau
$subscribers_list = [];
$sub_query = mysqli_query($connect, "SELECT id, email FROM newsletter ORDER BY email ASC");
while ($sub = mysqli_fetch_assoc($sub_query)) $subscribers_list[] = $sub;

?>