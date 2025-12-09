<?php
include "core_api.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = mysqli_prepare($connect, "SELECT p.*, c.category as category_name, u.username as author, u.avatar as author_avatar 
                                      FROM posts p 
                                      LEFT JOIN categories c ON p.category_id = c.id 
                                      LEFT JOIN users u ON p.author_id = u.id 
                                      WHERE p.id = ? AND p.active = 'Yes'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Nettoyage des données pour le JSON
        $row['image'] = api_image_url($row['image']);
        $row['author_avatar'] = api_image_url($row['author_avatar']);
        $row['content'] = html_entity_decode($row['content']); // Contenu HTML complet
        
        echo json_encode(["status" => "success", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "message" => "Post not found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing ID."]);
}
?>