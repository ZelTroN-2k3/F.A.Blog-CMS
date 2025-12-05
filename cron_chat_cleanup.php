<?php
// Ce fichier doit être appelé par une Tâche Cron (Cron Job) toutes les heures.
// Exemple : 0 * * * * /usr/bin/php /path/to/your/site/cron_chat_cleanup.php

// Pour la sécurité, on peut empêcher l'exécution directe via navigateur
// if (php_sapi_name() !== 'cli') { die('Access denied'); }

include "config.php"; // On inclut juste la config pour la DB

// 1. Définir le seuil d'inactivité (ex: 5 minutes)
// Si aucun utilisateur n'a été actif ces 5 dernières minutes, on considère que le tchat est vide.
$inactive_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));

// 2. Compter les utilisateurs actifs
$query = mysqli_query($connect, "SELECT COUNT(*) as active_count FROM users WHERE last_activity > '$inactive_threshold'");
$row = mysqli_fetch_assoc($query);
$active_users = $row['active_count'];

// 3. Logique de nettoyage
if ($active_users == 0) {
    // Personne n'est connecté au tchat -> ON VIDE TOUT
    
    // Optionnel : Supprimer les fichiers images du dossier uploads/chat/ pour ne pas encombrer
    $files = glob('uploads/chat/*'); 
    foreach($files as $file){ 
        if(is_file($file)) unlink($file); 
    }

    // Vider les tables
    mysqli_query($connect, "TRUNCATE TABLE chat_messages");
    mysqli_query($connect, "TRUNCATE TABLE chat_conversations");
    
    echo "Chat cleaned successfully at " . date('Y-m-d H:i:s');
} else {
    echo "Cleanup skipped: " . $active_users . " user(s) currently active.";
}
?>