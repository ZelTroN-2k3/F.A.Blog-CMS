<?php
include "core_api.php";

// Paramètres optionnels
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT p.id, p.title, p.slug, p.image, p.created_at, c.category as category_name, u.username as author 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.active = 'Yes' AND p.publish_at <= NOW()";

if ($category > 0) {
    $sql .= " AND p.category_id = $category";
}

$sql .= " ORDER BY p.created_at DESC LIMIT $limit";

$result = mysqli_query($connect, $sql);
$posts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['image'] = api_image_url($row['image']);
    $row['content_preview'] = "Content available in get_post endpoint"; 
    $posts[] = $row;
}

echo json_encode([
    "status" => "success",
    "count" => count($posts),
    "data" => $posts
]);
?>