<?php
include "core.php";

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$response = ['status' => 'error'];

if ($id > 0) {
    
    // --- LIKE PROJET ---
    if ($action == 'like_project') {
        if (check_user_has_liked_project($id)) {
            // Unlike
            if ($logged == 'Yes') {
                $uid = $rowu['id'];
                mysqli_query($connect, "DELETE FROM project_likes WHERE project_id='$id' AND user_id='$uid'");
            } else {
                $sid = session_id();
                mysqli_query($connect, "DELETE FROM project_likes WHERE project_id='$id' AND session_id='$sid'");
            }
            $response['liked'] = false;
        } else {
            // Like
            if ($logged == 'Yes') {
                $uid = $rowu['id'];
                mysqli_query($connect, "INSERT INTO project_likes (project_id, user_id) VALUES ('$id', '$uid')");
            } else {
                $sid = session_id();
                mysqli_query($connect, "INSERT INTO project_likes (project_id, session_id) VALUES ('$id', '$sid')");
            }
            $response['liked'] = true;
        }
        $response['count'] = get_project_like_count($id);
        $response['status'] = 'success';
        
        // --- NOTIFICATION (CORRIGÉE) ---
        // On cherche dans la table PROJECTS, pas POSTS
        $q_p = mysqli_query($connect, "SELECT author_id, title, slug FROM projects WHERE id=$id");
        if ($q_p && mysqli_num_rows($q_p) > 0) {
            $p = mysqli_fetch_assoc($q_p);
            
            // On envoie la notif seulement si l'utilisateur est connecté (sinon $uid n'existe pas ou est vide)
            if ($logged == 'Yes') {
                $msg = "Liked your project: " . short_text($p['title'], 20);
                $link = "project?name=" . $p['slug']; // Lien vers le projet
                
                send_notification($p['author_id'], $uid, 'like', $msg, $link); 
            }
        }
        // -------------------------------
    }

    // --- FAVORIS PROJET ---
    if ($action == 'favorite_project' && $logged == 'Yes') {
        $uid = $rowu['id'];
        if (check_user_has_favorited_project($id)) {
            mysqli_query($connect, "DELETE FROM user_project_favorites WHERE project_id='$id' AND user_id='$uid'");
            $response['favorited'] = false;
        } else {
            mysqli_query($connect, "INSERT INTO user_project_favorites (project_id, user_id) VALUES ('$id', '$uid')");
            $response['favorited'] = true;
        }
        $response['status'] = 'success';
    }
}

echo json_encode($response);
?>