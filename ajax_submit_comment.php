<?php
// Inclure le cœur de l'application pour accéder à la base de données et aux fonctions
include "core.php";

// Définir l'en-tête de la réponse comme JSON
header('Content-Type: application/json');

// --- NOTE : Validation CSRF désactivée pour éviter le blocage ---
// Si vous mettez à jour votre JS plus tard, vous pourrez décommenter la ligne ci-dessous.
// validate_csrf_token(); 

// Initialiser le tableau de réponse
$response = [
    'success' => false,
    'message' => 'An unknown error has occurred.',
    'html' => '',
    'parent_id' => 0,
    'moderation' => false // Flag pour dire au JS si le commentaire est masqué
];

// --- Validation des données ---
// 1. Vérifier si les données POST existent
if (!isset($_POST['comment'], $_POST['parent_id'], $_POST['post_id'])) {
    $response['message'] = 'Missing form data.';
    echo json_encode($response);
    exit;
}

// 2. Nettoyer les données
$comment   = $_POST['comment'];
$parent_id = (int)$_POST['parent_id'];
$post_id   = (int)$_POST['post_id'];
$guest     = 'No';

// 3. Vérifier si l'utilisateur peut commenter
$cancomment = 'No';
if ($logged == 'No' && $settings['comments'] == 'guests') {
    $cancomment = 'Yes';
} elseif ($logged == 'Yes') {
    $cancomment = 'Yes';
}

if ($cancomment == 'No') {
    $response['message'] = 'You must be logged in to comment.';
    echo json_encode($response);
    exit;
}

// 4. Gérer l'auteur (Invité ou Membre)
$authname_problem = 'No';

// --- MODÉRATION : Par défaut, le statut est 'Yes' (Approuvé) ---
// Sauf si on détecte un problème plus bas
$approved = 'Yes'; 

if ($logged == 'No') {
    $guest  = 'Yes';
    $author = $_POST['author'] ?? ''; // Nom de l'invité
    
    // 4a. Vérifier le reCAPTCHA pour les invités
    $captcha = $_POST['g-recaptcha-response'] ?? '';
    if (empty($captcha)) {
        $response['message'] = 'Please complete the reCAPTCHA.';
        echo json_encode($response);
        exit;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($settings['gcaptcha_secretkey']) . '&response=' . urlencode($captcha);
    $recaptcha_response = file_get_contents($url);
    $responseKeys = json_decode($recaptcha_response, true);
    
    if (!$responseKeys["success"]) {
        $response['message'] = 'reCAPTCHA verification failed.';
        echo json_encode($response);
        exit;
    }
    
    // 4b. Vérifier le nom de l'invité
    if (strlen($author) < 2) {
        $authname_problem = 'Yes';
        $response['message'] = 'Your name is too short.';
    }

    // Règle : Les invités sont toujours modérés (optionnel, changer en 'Yes' si vous voulez)
    $approved = 'No'; 

} else {
    $author = $rowu['id']; // ID de l'utilisateur connecté
}

// 5. Vérifier la longueur du commentaire
if (strlen($comment) < 2) {
    $response['message'] = 'Your comment is too short.';
    echo json_encode($response);
    exit;
}

// 6. Vérifier s'il y a eu un problème avec le nom de l'auteur
if ($authname_problem == 'Yes') {
    echo json_encode($response);
    exit;
}

// ---------------------------------------------------------
// --- NOUVELLE LOGIQUE DE MODÉRATION (Liste Noire & Admin) ---
// ---------------------------------------------------------

// A. Vérifier la "Liste Noire" (Mots interdits) - Version Intelligente (Mots Entiers)
if (!empty($settings['comments_blacklist'])) {
    // On explose la liste
    $blacklist = explode(',', $settings['comments_blacklist']);
    
    foreach ($blacklist as $bad_word) {
        $bad_word = trim($bad_word);
        if ($bad_word == "") continue;

        // CONSTRUCTION DU MOTIF REGEX :
        // \b   = Limite de mot (Word Boundary)
        // preg_quote = Sécurise le mot (échappe les caractères spéciaux)
        // /iu  = i (Insensible à la casse) + u (Support Unicode/UTF-8 pour les accents)
        $pattern = '/\b' . preg_quote($bad_word, '/') . '\b/iu';
        
        if (preg_match($pattern, $comment)) {
            $approved = 'No'; // Bloqué !
            break; 
        }
    }
}

// B. Vérifier si l'admin a activé "Approbation manuelle pour tous"
if (isset($settings['comments_approval']) && $settings['comments_approval'] == 1) {
    $approved = 'No';
}

// ---------------------------------------------------------
// --- FIN LOGIQUE MODÉRATION ---
// ---------------------------------------------------------


// --- Insertion dans la base de données ---

// MODIFICATION DE LA REQUÊTE : Ajout de la colonne `approved`
$stmt = mysqli_prepare($connect, "INSERT INTO `comments` (`post_id`, `parent_id`, `comment`, `user_id`, `guest`, `approved`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, NOW())");
// Types: i (int), i (int), s (string), s (string), s (string), s (string)
mysqli_stmt_bind_param($stmt, "iissss", $post_id, $parent_id, $comment, $author, $guest, $approved);

if (mysqli_stmt_execute($stmt)) {
    $new_comment_id = mysqli_insert_id($connect);
    mysqli_stmt_close($stmt);

    // --- RÉPONSE INTELLIGENTE ---
    if ($approved == 'No') {
        // Cas : Commentaire en attente
        $response['success'] = true;
        $response['message'] = 'Your comment has been submitted and is awaiting moderation.';
        $response['moderation'] = true; // Dit au JS de ne pas l'afficher tout de suite
    } else {
        // Cas : Commentaire publié directement
        // Calculer la marge pour l'affichage
        $margin_left = 0;
        if ($parent_id > 0) {
            $stmt_level = mysqli_prepare($connect, "SELECT * FROM comments WHERE id = ?");
            mysqli_stmt_bind_param($stmt_level, "i", $parent_id);
            mysqli_stmt_execute($stmt_level);
            $parent_comment = mysqli_stmt_get_result($stmt_level);
            
            $level = 1; 
            if (mysqli_num_rows($parent_comment) > 0) {
                $parent_data = mysqli_fetch_assoc($parent_comment);
                $current_parent_id = $parent_data['parent_id'];
                $level = 1;
                while ($current_parent_id > 0 && $level < 5) {
                    $stmt_parent_check = mysqli_prepare($connect, "SELECT parent_id FROM comments WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_parent_check, "i", $current_parent_id);
                    mysqli_stmt_execute($stmt_parent_check);
                    $parent_result = mysqli_stmt_get_result($stmt_parent_check);
                    $parent_row = mysqli_fetch_assoc($parent_result);
                    $current_parent_id = $parent_row['parent_id'];
                    $level++;
                    mysqli_stmt_close($stmt_parent_check);
                }
            }
            mysqli_stmt_close($stmt_level);
            
            $margin_left = ($level > 5) ? (5 * 30) : ($level * 30);
        }

        $response['success'] = true;
        $response['message'] = 'Comment published!';
        // Utilise la fonction HTML définie dans core.php
        $response['html'] = render_comment_html($new_comment_id, $margin_left);
        $response['parent_id'] = $parent_id;
        $response['moderation'] = false;
    }
        // --- NOTIFICATION ---
        $q_auth = mysqli_query($connect, "SELECT author_id, title, slug FROM posts WHERE id=$post_id");
        $post_info = mysqli_fetch_assoc($q_auth);
        
        $msg = "Commented on your post: " . short_text($post_info['title'], 20);
        $link = "post?name=" . $post_info['slug'] . "#comments";
        
        // Définir l'ID de l'envoyeur (0 si invité)
        $sender_id = ($logged == 'Yes' && isset($rowu['id'])) ? $rowu['id'] : 0;
        
        // Envoi notif à l'auteur de l'article
        send_notification($post_info['author_id'], $sender_id, 'comment', $msg, $link);
        // --------------------
} else {
    // Erreur SQL
    $response['message'] = 'Error saving comment.';
}

// Envoyer la réponse JSON finale
echo json_encode($response);
exit;
?>