<?php
// admin/ajax_autosave.php

// 1. On charge le noyau directement (plus sûr pour l'AJAX)
if (file_exists('../core.php')) {
    include "../core.php";
} else {
    // Si on est dans admin/includes/ par erreur, on remonte de 2 crans
    include "../../core.php";
}

// 2. On s'assure qu'on renvoie du JSON propre sans erreur PHP visible
header('Content-Type: application/json');
error_reporting(0); 
ini_set('display_errors', 0);

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // 3. Vérification Session
    if (!isset($_SESSION['sec-username'])) {
        throw new Exception('Session expired');
    }

    // 4. Vérification Données
    if (!isset($_POST['post_id']) || !isset($_POST['content'])) {
        throw new Exception('Missing data');
    }

    $post_id = (int)$_POST['post_id'];
    $title   = $_POST['title'] ?? 'Untitled'; // Titre par défaut si vide
    // On ne nettoie pas le HTML ici car on utilise des requêtes préparées
    $content = $_POST['content']; 
    
    // On récupère l'ID utilisateur proprement via la session/core
    // (Supposons que $rowu ou $user est défini dans core.php, sinon on cherche)
    global $rowu;
    $user_id = isset($rowu['id']) ? $rowu['id'] : 1; // Fallback sur 1 (Admin) si erreur

    // 5. Logique Autosave (Écrase le dernier autosave de moins d'1h)
    $stmt_check = mysqli_prepare($connect, "SELECT id FROM post_revisions WHERE post_id=? AND revision_type='autosave' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, "i", $post_id);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $existing = mysqli_fetch_assoc($res_check);
    mysqli_stmt_close($stmt_check);

    if ($existing) {
        // UPDATE
        $rev_id = $existing['id'];
        $stmt = mysqli_prepare($connect, "UPDATE post_revisions SET title=?, content=?, created_at=NOW() WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $rev_id);
    } else {
        // INSERT
        $stmt = mysqli_prepare($connect, "INSERT INTO post_revisions (post_id, user_id, title, content, revision_type, created_at) VALUES (?, ?, ?, ?, 'autosave', NOW())");
        mysqli_stmt_bind_param($stmt, "iiss", $post_id, $user_id, $title, $content);
    }

    if (mysqli_stmt_execute($stmt)) {
        $response = ['status' => 'success', 'time' => date('H:i:s')];
    } else {
        throw new Exception(mysqli_error($connect));
    }
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>