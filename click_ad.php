<?php
// On inclut core pour la connexion BDD
require "core.php";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_ip = $_SERVER['REMOTE_ADDR']; // Récupérer l'IP du visiteur

    // 1. Vérifier si cette IP a déjà cliqué sur cette Pub dans les dernières 24h
    // On cherche une entrée correspondante datant de moins d'1 jour
    $stmt_check = mysqli_prepare($connect, "
        SELECT id 
        FROM ad_clicks 
        WHERE ad_id = ? 
          AND ip_address = ? 
          AND clicked_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        LIMIT 1
    ");
    
    mysqli_stmt_bind_param($stmt_check, "is", $id, $user_ip);
    mysqli_stmt_execute($stmt_check);
    $stmt_check->store_result();
    
    // Si num_rows > 0, c'est qu'il a déjà cliqué récemment
    $already_clicked = ($stmt_check->num_rows > 0);
    mysqli_stmt_close($stmt_check);


    // 2. Si c'est un NOUVEAU clic (pas cliqué depuis 24h)
    if (!$already_clicked) {
        
        // A. On incrémente le compteur global (Table ads)
        $stmt_update = mysqli_prepare($connect, "UPDATE ads SET clicks = clicks + 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "i", $id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        // B. On enregistre ce clic dans l'historique (Table ad_clicks) pour bloquer les prochains
        $stmt_log = mysqli_prepare($connect, "INSERT INTO ad_clicks (ad_id, ip_address, clicked_at) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmt_log, "is", $id, $user_ip);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);
    }

    // 3. Récupérer le lien et Rediriger (On le fait dans TOUS les cas)
    $stmt_get = mysqli_prepare($connect, "SELECT link_url FROM ads WHERE id = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);
    $result = mysqli_stmt_get_result($stmt_get);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt_get);

    // Redirection
    if ($row && !empty($row['link_url'])) {
        header("Location: " . $row['link_url']);
        exit;
    }
}

// Sécurité : Si pas d'ID ou pub introuvable, retour à l'accueil
header("Location: index.php");
exit;
?>