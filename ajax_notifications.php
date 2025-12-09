<?php
// ajax_notifications.php
// Gestion des notifications en temps réel

// On désactive l'affichage des erreurs HTML pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(0);

include "core.php";

// On remet le content-type JSON
header('Content-Type: application/json; charset=utf-8');

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    if ($logged != 'Yes') {
        throw new Exception('Login required');
    }

    $my_id = $rowu['id'];
    $action = $_POST['action'] ?? '';

    // --- 1. MARQUER COMME LU ---
    if ($action == 'mark_read') {
        $notif_id = (int)$_POST['id'];
        mysqli_query($connect, "UPDATE notifications SET is_read='Yes' WHERE id=$notif_id AND user_id=$my_id");
        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- 2. MARQUER TOUT COMME LU ---
    if ($action == 'mark_all_read') {
        mysqli_query($connect, "UPDATE notifications SET is_read='Yes' WHERE user_id=$my_id");
        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- 3. RÉCUPÉRER (POLLING) ---
    if ($action == 'fetch') {
        
        // Compter les non-lues
        $q_count = mysqli_query($connect, "SELECT COUNT(id) as c FROM notifications WHERE user_id=$my_id AND is_read='No'");
        $unread_count = ($q_count) ? mysqli_fetch_assoc($q_count)['c'] : 0;
        
        // Récupérer les 10 dernières
        $q_list = mysqli_query($connect, "SELECT n.*, u.username, u.avatar FROM notifications n LEFT JOIN users u ON n.from_user_id = u.id WHERE n.user_id=$my_id ORDER BY n.created_at DESC LIMIT 10");
        
        $html = '';
        if ($q_list && mysqli_num_rows($q_list) > 0) {
            while ($row = mysqli_fetch_assoc($q_list)) {
                // Avatar
                $avatar = 'assets/img/avatar.png'; 
                if ($row['from_user_id'] > 0 && !empty($row['avatar'])) {
                    $avatar = str_replace('../', '', $row['avatar']);
                } elseif ($row['type'] == 'badge') {
                    $avatar = 'assets/img/trophy.png'; 
                }
                
                // Correction URL Avatar
                if(strpos($avatar, 'http') !== 0) {
                    $avatar = $settings['site_url'] . '/' . $avatar;
                }

                // Style Non-lu
                $bg_class = ($row['is_read'] == 'No') ? 'bg-light fw-bold' : '';
                
                // Icône
                $icon = 'fa-bell';
                if($row['type'] == 'like') $icon = 'fa-heart text-danger';
                if($row['type'] == 'comment') $icon = 'fa-comment text-primary';
                if($row['type'] == 'badge') $icon = 'fa-trophy text-warning';

                // Date
                $time_text = time_elapsed_string_fixed($row['created_at']);

                $html .= '
                <a href="'.$row['link'].'" class="list-group-item list-group-item-action d-flex align-items-center p-2 '.$bg_class.'" onclick="markRead('.$row['id'].')">
                    <div class="position-relative me-3">
                        <img src="'.$avatar.'" class="rounded-circle" width="40" height="40" style="object-fit:cover;" onerror="this.src=\''.$settings['site_url'].'/assets/img/avatar.png\'">
                        <span class="position-absolute bottom-0 start-100 translate-middle badge rounded-pill bg-white border border-light text-muted" style="font-size:0.6rem;">
                            <i class="fas '.$icon.'"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1" style="line-height:1.2;">
                        <span class="d-block text-dark small">'.$row['message'].'</span>
                        <small class="text-muted" style="font-size:0.7rem;">'.$time_text.'</small>
                    </div>
                </a>';
            }
            $html .= '<div class="text-center p-2 border-top"><a href="#" onclick="markAllRead(); return false;" class="small text-decoration-none">Mark all as read</a></div>';
        } else {
            $html = '<div class="p-3 text-center text-muted small">No notifications</div>';
        }
        
        echo json_encode(['count' => $unread_count, 'html' => $html]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// --- FONCTION DATE CORRIGÉE (Compatible PHP 8.2+) ---
function time_elapsed_string_fixed($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Calcul manuel pour éviter l'erreur "Undefined property w"
        $weeks = floor($diff->d / 7);
        $days = $diff->d - ($weeks * 7);

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'min',
            's' => 'sec'
        );
        
        foreach ($string as $k => &$v) {
            if ($k == 'w') $value = $weeks;
            elseif ($k == 'd') $value = $days;
            else $value = $diff->$k;

            if ($value) {
                $v = $value . ' ' . $v . ($value > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
        
    } catch (Exception $e) {
        return $datetime; // Fallback en cas d'erreur de date
    }
}
?>