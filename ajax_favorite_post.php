<?php
include "core.php";

header('Content-Type: application/json');

$response = [
    'success' => false,
    'favorited' => false,
    'message' => 'An error has occurred.'
];

// 1. Vérifier si l'utilisateur est connecté
if ($logged == 'No') {
    $response['message'] = 'You must be logged in to add a favorite.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['post_id'])) {
    $response['message'] = 'Missing post ID.';
    echo json_encode($response);
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = (int)$rowu['id'];

// 2. Vérifier si un favori existe déjà
$stmt_check = mysqli_prepare($connect, "SELECT id FROM user_favorites WHERE user_id = ? AND post_id = ?");
mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $post_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$has_favorited = (mysqli_num_rows($result_check) > 0);
mysqli_stmt_close($stmt_check);

try {
    if ($has_favorited) {
        // 3. Si oui -> Supprimer le favori
        $stmt_delete = mysqli_prepare($connect, "DELETE FROM user_favorites WHERE user_id = ? AND post_id = ?");
        mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $post_id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        $response['favorited'] = false; // L'utilisateur vient de le retirer
        $response['message'] = 'Favorite removed.';
    } else {
        // 4. Si non -> Insérer le favori
        $stmt_insert = mysqli_prepare($connect, "INSERT INTO user_favorites (user_id, post_id, created_at) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $post_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
        
        $response['favorited'] = true; // L'utilisateur vient de l'ajouter
        $response['message'] = 'Favorite added.';
    }
    $response['success'] = true;

} catch (mysqli_sql_exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>