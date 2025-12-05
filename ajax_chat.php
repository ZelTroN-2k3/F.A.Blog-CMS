<?php
include "core.php";

$my_id = $rowu['id']; // Indispensable pour savoir "qui est moi"

// Mise à jour de l'activité de l'utilisateur courant
if ($logged == 'Yes') {
    mysqli_query($connect, "UPDATE users SET last_activity = NOW() WHERE id = " . $rowu['id']);
}

// Désactiver l'affichage des erreurs HTML pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Forcer le header JSON

// Vérification de sécurité
if ($logged != 'Yes') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$current_user_id = $rowu['id'];
$action = $_POST['action'] ?? '';

try {
    // --- 1. RÉCUPÉRER LA LISTE DES CONVERSATIONS ---
    if ($action == 'fetch_conversations') {
        $sql = "
            SELECT c.id as conv_id, c.updated_at,
                   u.id as other_user_id, u.username, u.avatar, u.role,
                   (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_msg,
                   (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 'No') as unread
            FROM chat_conversations c
            JOIN users u ON (CASE WHEN c.user_1 = ? THEN c.user_2 ELSE c.user_1 END) = u.id
            WHERE (c.user_1 = ? AND c.archived_user_1 = 'No') OR (c.user_2 = ? AND c.archived_user_2 = 'No')
            ORDER BY c.updated_at DESC
        ";

        $stmt = mysqli_prepare($connect, $sql);
        if (!$stmt) { throw new Exception("Erreur SQL (Prepare fetch_conv): " . mysqli_error($connect)); }
        
        mysqli_stmt_bind_param($stmt, "iiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $conversations = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['avatar'] = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
            $row['last_msg'] = $row['last_msg'] ? htmlspecialchars(short_text($row['last_msg'], 30)) : '<i>New conversation</i>';
            $row['time_ago'] = date('H:i', strtotime($row['updated_at']));
            $conversations[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $conversations]);
        exit;
    }

    // --- 2. RÉCUPÉRER L'HISTORIQUE ---
    if ($action == 'fetch_messages') {
        $other_user_id = (int)$_POST['other_user_id'];
        
        $stmt_conv = mysqli_prepare($connect, "
            SELECT id 
            FROM chat_conversations 
            WHERE (user_1=? AND user_2=?) OR (user_1=? AND user_2=?) 
            LIMIT 1
        ");
        if (!$stmt_conv) { throw new Exception("Erreur SQL: " . mysqli_error($connect)); }

        mysqli_stmt_bind_param($stmt_conv, "iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_conv);
        $res_conv = mysqli_stmt_get_result($stmt_conv);
        $conv = mysqli_fetch_assoc($res_conv);
        mysqli_stmt_close($stmt_conv);
        
        if (!$conv) {
            echo json_encode(['status' => 'success', 'html' => '<div class="text-center mt-5 text-muted">Start the conversation!</div>']);
            exit;
        }
        
        $conv_id = $conv['id'];
        
        // Marquer comme lu
        $stmt_read = mysqli_prepare($connect, "UPDATE chat_messages SET is_read='Yes' WHERE conversation_id=? AND sender_id != ?");
        mysqli_stmt_bind_param($stmt_read, "ii", $conv_id, $current_user_id);
        mysqli_stmt_execute($stmt_read);
        mysqli_stmt_close($stmt_read);
        
        // Récupérer messages
        $stmt_msg = mysqli_prepare($connect, "SELECT * FROM chat_messages WHERE conversation_id=? ORDER BY created_at ASC");
        mysqli_stmt_bind_param($stmt_msg, "i", $conv_id);
        mysqli_stmt_execute($stmt_msg);
        $res_msg = mysqli_stmt_get_result($stmt_msg);
        
        $html = '';
        $prev_date = null; 

        while ($msg = mysqli_fetch_assoc($res_msg)) {
            // SÉPARATEUR DE DATE
            $msg_date = date('Y-m-d', strtotime($msg['created_at']));
            
            if ($msg_date != $prev_date) {
                if ($msg_date == date('Y-m-d')) { $label = 'Aujourd\'hui'; } 
                elseif ($msg_date == date('Y-m-d', strtotime('-1 day'))) { $label = 'Hier'; } 
                else { $label = date('d/m/Y', strtotime($msg_date)); }
                
                $html .= '<div class="chat-date-separator"><span>' . $label . '</span></div>';
                $prev_date = $msg_date;
            }

            // STYLE BULLE & COCHES
            $is_me = ($msg['sender_id'] == $my_id);
            $side = $is_me ? 'message-sent' : 'message-received';

            // Favoris
            $stmt_star = mysqli_prepare($connect, "SELECT id FROM chat_starred WHERE user_id=? AND message_id=?");
            mysqli_stmt_bind_param($stmt_star, "ii", $current_user_id, $msg['id']);
            mysqli_stmt_execute($stmt_star);
            $is_starred = mysqli_stmt_fetch($stmt_star);
            mysqli_stmt_close($stmt_star);
            $star_icon = $is_starred ? '<i class="fas fa-star text-warning ms-1 small"></i>' : '';

            // Contenu
            $content = '';
            if (isset($msg['type']) && $msg['type'] == 'image') {
                $clean_path = str_replace('../', '', $msg['message']);
                $content = '<a href="'.$clean_path.'" target="_blank"><img src="'.$clean_path.'" style="max-width:200px; border-radius:5px;"></a>';
            } else {
                $content = nl2br(htmlspecialchars($msg['message']));
            }

            // Heure et Coches
            $time = date('H:i', strtotime($msg['created_at']));
            $ticks = '';
            
            if ($is_me) {
                if ($msg['is_read'] == 'Yes') {
                    $ticks = '<i class="fas fa-check-double text-primary msg-status"></i>'; 
                } else {
                    $ticks = '<i class="fas fa-check text-muted msg-status"></i>';
                }
            }

            $html .= '
            <div class="message-row ' . ($is_me ? 'justify-content-end' : 'justify-content-start') . '">
                <div class="message-bubble ' . $side . '">
                    <div class="message-text">' . $content . '</div>
                    <div class="message-meta">
                        <span class="message-time">' . $time . '</span>
                        ' . $ticks . ' ' . $star_icon . '
                    </div>
                </div>
            </div>';
        }
        mysqli_stmt_close($stmt_msg);
        
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- 3. ENVOYER UN MESSAGE ---
    if ($action == 'send_message') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error']); exit;
        }

        $other_user_id = (int)$_POST['other_user_id'];
        $message = trim($_POST['message']);
        $type = 'text';
        $final_content = $message;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "uploads/chat/";
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0777, true); }
            
            $base_name = $upload_dir . 'msg_' . time() . '_' . uniqid();
            $uploaded_path = optimize_and_save_image($_FILES['image']['tmp_name'], $base_name, 800, 80);
            
            if ($uploaded_path) {
                $type = 'image';
                $final_content = $uploaded_path;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image format']); exit;
            }
        }

        if (empty($final_content) && $type == 'text') { 
            echo json_encode(['status' => 'error', 'message' => 'Empty message']); exit; 
        }

        $stmt_check = mysqli_prepare($connect, "SELECT id FROM chat_conversations WHERE (user_1=? AND user_2=?) OR (user_1=? AND user_2=?) LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        
        if ($row_conv = mysqli_fetch_assoc($res_check)) {
            $conversation_id = $row_conv['id'];
            mysqli_query($connect, "UPDATE chat_conversations SET updated_at=NOW() WHERE id='$conversation_id'");
        } else {
            $stmt_new = mysqli_prepare($connect, "INSERT INTO chat_conversations (user_1, user_2, updated_at) VALUES (?, ?, NOW())");
            mysqli_stmt_bind_param($stmt_new, "ii", $current_user_id, $other_user_id);
            mysqli_stmt_execute($stmt_new);
            $conversation_id = mysqli_insert_id($connect);
            mysqli_stmt_close($stmt_new);
        }
        mysqli_stmt_close($stmt_check);
        
        $stmt_insert = mysqli_prepare($connect, "INSERT INTO chat_messages (conversation_id, sender_id, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt_insert, "iiss", $conversation_id, $current_user_id, $final_content, $type);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB Error']);
        }
        mysqli_stmt_close($stmt_insert);
        exit;
    }

    // --- 4. RECHERCHE ---
    if ($action == 'search_users') {
        $term = '%' . $_POST['term'] . '%';
        $stmt = mysqli_prepare($connect, "SELECT id, username, avatar FROM users WHERE username LIKE ? AND id != ? LIMIT 5");
        mysqli_stmt_bind_param($stmt, "si", $term, $current_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $users = [];
        while($u = mysqli_fetch_assoc($res)){
            $u['avatar'] = !empty($u['avatar']) ? $u['avatar'] : 'assets/img/avatar.png';
            $users[] = $u;
        }
        echo json_encode($users);
        exit;
    }

    // --- 5. CHECK UNREAD ---
    if ($action == 'check_unread_count') {
        $stmt_count = mysqli_prepare($connect, "SELECT COUNT(id) as count FROM chat_messages WHERE is_read = 'No' AND sender_id != ? AND conversation_id IN (SELECT id FROM chat_conversations WHERE user_1 = ? OR user_2 = ?)");
        mysqli_stmt_bind_param($stmt_count, "iii", $current_user_id, $current_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_count);
        $res = mysqli_stmt_get_result($stmt_count);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['status' => 'success', 'count' => (int)$row['count']]);
        exit;
    }

    // --- 6. UTILISATEURS EN LIGNE ---
    if ($action == 'fetch_online_users') {
        $stmt = mysqli_prepare($connect, "SELECT id, username, avatar, role FROM users WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND id != ? ORDER BY username ASC");
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $online_users = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['avatar'] = !empty($row['avatar']) ? $row['avatar'] : 'assets/img/avatar.png';
            $online_users[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $online_users]);
        exit;
    }

    // --- 7. TYPING STATUS ---
    if ($action == 'set_typing_status') {
        $target_id = (int)$_POST['target_id'];
        $stmt = mysqli_prepare($connect, "UPDATE users SET typing_in_chat_with = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $target_id, $current_user_id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- 8. GET USER STATUS ---
    if ($action == 'get_user_status') {
        $other_user_id = (int)$_POST['other_user_id'];
        $stmt = mysqli_prepare($connect, "SELECT last_activity, typing_in_chat_with FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $other_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($res);
        
        $status_text = '';
        $is_online = false;
        
        if ($user_data['typing_in_chat_with'] == $current_user_id) {
            $status_text = '<span class="text-success fw-bold">écrit...</span>';
        } else {
            $time_diff = time() - strtotime($user_data['last_activity']);
            if ($time_diff < 120) {
                $status_text = 'En ligne';
                $is_online = true;
            } else {
                $status_text = 'Vu à ' . date('H:i', strtotime($user_data['last_activity']));
                if ($time_diff > 86400) { $status_text = 'Vu le ' . date('d/m', strtotime($user_data['last_activity'])); }
            }
        }
        echo json_encode(['status' => 'success', 'text' => $status_text, 'online' => $is_online]);
        exit;
    }

    // --- 9. ARCHIVER ---
    if ($action == 'toggle_archive') {
        $conv_id = (int)$_POST['conv_id'];
        $stmt = mysqli_prepare($connect, "SELECT user_1, user_2, archived_user_1, archived_user_2 FROM chat_conversations WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $conv_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $conv = mysqli_fetch_assoc($res);
        
        if ($conv) {
            $col = ($conv['user_1'] == $current_user_id) ? 'archived_user_1' : 'archived_user_2';
            $current_val = ($conv['user_1'] == $current_user_id) ? $conv['archived_user_1'] : $conv['archived_user_2'];
            $new_val = ($current_val == 'Yes') ? 'No' : 'Yes';
            
            mysqli_query($connect, "UPDATE chat_conversations SET $col = '$new_val' WHERE id=$conv_id");
            echo json_encode(['status' => 'success', 'new_state' => $new_val]);
        }
        exit;
    }

    // --- 10. LISTER ARCHIVES ---
    if ($action == 'fetch_archived_conversations') {
        $sql = "SELECT c.id as conv_id, c.updated_at, u.username, u.avatar FROM chat_conversations c JOIN users u ON (CASE WHEN c.user_1 = ? THEN c.user_2 ELSE c.user_1 END) = u.id WHERE (c.user_1 = ? AND c.archived_user_1 = 'Yes') OR (c.user_2 = ? AND c.archived_user_2 = 'Yes') ORDER BY c.updated_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "iiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = [];
        while($row = mysqli_fetch_assoc($result)) { 
            $row['avatar'] = (!empty($row['avatar'])) ? $row['avatar'] : 'assets/img/avatar.png';
            $data[] = $row; 
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // --- 11. FAVORIS ---
    if ($action == 'toggle_star') {
        $msg_id = (int)$_POST['msg_id'];
        $check = mysqli_query($connect, "SELECT id FROM chat_starred WHERE user_id='$current_user_id' AND message_id='$msg_id'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($connect, "DELETE FROM chat_starred WHERE user_id='$current_user_id' AND message_id='$msg_id'");
            echo json_encode(['status' => 'success', 'action' => 'removed']);
        } else {
            mysqli_query($connect, "INSERT INTO chat_starred (user_id, message_id) VALUES ('$current_user_id', '$msg_id')");
            echo json_encode(['status' => 'success', 'action' => 'added']);
        }
        exit;
    }

    // --- 12. LISTER FAVORIS ---
    if ($action == 'fetch_starred_messages') {
        $sql = "SELECT m.message, m.created_at, m.type, u.username FROM chat_starred s JOIN chat_messages m ON s.message_id = m.id JOIN users u ON m.sender_id = u.id WHERE s.user_id = ? ORDER BY s.created_at DESC";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = [];
        while($row = mysqli_fetch_assoc($res)) { $data[] = $row; }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // --- 13. POSTER STATUT ---
    if ($action == 'post_status') {
        $caption = trim($_POST['caption']);
        $type = 'text';
        $content = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "uploads/status/";
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0777, true); }
            
            $base_name = $upload_dir . 'status_' . time() . '_' . uniqid();
            $uploaded_path = optimize_and_save_image($_FILES['image']['tmp_name'], $base_name, 1080, 80);
            
            if ($uploaded_path) {
                $type = 'image';
                $content = $uploaded_path;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image']); exit;
            }
        } else {
            if (empty($caption)) { echo json_encode(['status' => 'error', 'message' => 'Empty status']); exit; }
            $content = $caption;
        }

        $stmt = mysqli_prepare($connect, "INSERT INTO chat_status (user_id, type, content, caption, created_at) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isss", $current_user_id, $type, $content, $caption);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- 14. FETCH STATUTS ---
    if ($action == 'fetch_statuses') {
        $stmt = mysqli_prepare($connect, "SELECT s.*, u.username, u.avatar FROM chat_status s JOIN users u ON s.user_id = u.id WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY s.created_at DESC");
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = [];
        while($row = mysqli_fetch_assoc($res)) {
            $row['avatar'] = (!empty($row['avatar'])) ? $row['avatar'] : 'assets/img/avatar.png';
            if ($row['type'] == 'image') {
                $row['content'] = str_replace('../', '', $row['content']);
            }
            $row['time_ago'] = date('H:i', strtotime($row['created_at']));
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
    
    // Message par défaut si aucune action n'est trouvée
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>