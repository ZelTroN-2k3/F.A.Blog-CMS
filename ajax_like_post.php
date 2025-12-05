<?php
include "core.php";

header('Content-Type: application/json');

$response = [
    'success' => false,
    'new_count' => 0,
    'liked' => false,
    'message' => 'An error has occurred.'
];

if (!isset($_POST['post_id'])) {
    $response['message'] = 'Missing post ID.';
    echo json_encode($response);
    exit;
}

$post_id = (int)$_POST['post_id'];

// Déterminer l'identifiant du "likeur"
$user_id_sql = "NULL";
$session_id_sql = "NULL";
$is_user = false;

if ($logged == 'Yes') {
    $user_id = (int)$rowu['id'];
    $user_id_sql = (string)$user_id;
    $is_user = true;
} else {
    $session_id = session_id();
    $session_id_sql = "'" . mysqli_real_escape_string($connect, $session_id) . "'";
}

// 1. Vérifier si un "like" existe déjà
$check_sql = "";
if ($is_user) {
    $check_sql = "SELECT id FROM post_likes WHERE post_id = $post_id AND user_id = $user_id_sql";
} else {
    $check_sql = "SELECT id FROM post_likes WHERE post_id = $post_id AND session_id = $session_id_sql";
}

$result = mysqli_query($connect, $check_sql);
$has_liked = (mysqli_num_rows($result) > 0);

if ($has_liked) {
    // 2. Si oui -> Supprimer le "like"
    $like_row = mysqli_fetch_assoc($result);
    $like_id = $like_row['id'];
    mysqli_query($connect, "DELETE FROM post_likes WHERE id = $like_id");
    $response['liked'] = false;
} else {
    // 3. Si non -> Insérer le "like"
    mysqli_query($connect, "INSERT INTO post_likes (post_id, user_id, session_id, created_at) VALUES ($post_id, $user_id_sql, $session_id_sql, NOW())");
    $response['liked'] = true;
}

// 4. Mettre à jour la réponse
$response['success'] = true;
$response['new_count'] = get_post_like_count($post_id);
$response['message'] = 'Action recorded.';

echo json_encode($response);
exit;
?>