<?php
include "core.php"; // Inclut la connexion BDD ($connect) et les sessions

use Hybridauth\Hybridauth;

// 1. Charger la configuration
$config = include 'social_config.php';

// --- LIGNES DE DÉBOGAGE À AJOUTER ---
// var_dump($config['providers']['Google']); 
// die("FIN DU DEBUG");
// --- FIN DES LIGNES À AJOUTER ---

try {
    // 2. Initialiser Hybridauth
    $hybridauth = new Hybridauth($config);

    // 3. Tenter de s'authentifier
    $adapter = $hybridauth->authenticate($_GET['provider'] ?? 'Google');

    // 4. Récupérer le profil utilisateur
    $userProfile = $adapter->getUserProfile();

    if ($userProfile && isset($userProfile->email)) {
        
        $email = $userProfile->email;
        $username_social = $userProfile->displayName;
        
        // --- NOUVEL AJOUT : Récupérer l'avatar ---
        $avatar_url = $userProfile->photoURL;
        
        // Si Google ne fournit pas d'avatar, utiliser celui par défaut
        if (empty($avatar_url)) {
            // Utilise l'avatar par défaut de votre site
            $avatar_url = 'assets/img/avatar.png'; 
        }
        // --- FIN AJOUT ---
        
        // 5. Logique de connexion / inscription
        
        $stmt_check = mysqli_prepare($connect, "SELECT username FROM `users` WHERE `email` = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        mysqli_stmt_close($stmt_check);

        if (mysqli_num_rows($result) > 0) {
            // ----- UTILISATEUR EXISTANT -----
            $row = mysqli_fetch_assoc($result);
            $username_to_log = $row['username'];
            
            // --- NOUVEL AJOUT : Mettre à jour l'avatar à chaque connexion ---
            $stmt_update_avatar = mysqli_prepare($connect, "UPDATE `users` SET `avatar` = ? WHERE `email` = ?");
            mysqli_stmt_bind_param($stmt_update_avatar, "ss", $avatar_url, $email);
            mysqli_stmt_execute($stmt_update_avatar);
            mysqli_stmt_close($stmt_update_avatar);
            // --- FIN AJOUT ---
            
        } else {
            // ----- NOUVEL UTILISATEUR -----
            
            // Logique pour créer un username unique (inchangée)
            $new_username = preg_replace('/[^a-z0-9_.]+/i', '', strtolower(str_replace(' ', '.', $username_social)));
            $username_to_log = $new_username;
            $i = 1;
            while (true) {
                $stmt_u = mysqli_prepare($connect, "SELECT username FROM `users` WHERE `username` = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt_u, "s", $username_to_log);
                mysqli_stmt_execute($stmt_u);
                if (mysqli_stmt_get_result($stmt_u)->num_rows == 0) {
                    mysqli_stmt_close($stmt_u);
                    break; 
                }
                mysqli_stmt_close($stmt_u);
                $username_to_log = $new_username . $i; 
                $i++;
            }

            $password_hashed = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

            // --- MODIFICATION : Insérer l'utilisateur AVEC l'avatar ---
            $stmt_insert = mysqli_prepare($connect, "INSERT INTO `users` (`username`, `password`, `email`, `avatar`) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "ssss", $username_to_log, $password_hashed, $email, $avatar_url);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
            // --- FIN MODIFICATION ---
            
            // (Optionnel) Inscrire à la newsletter
            $stmt_news = mysqli_prepare($connect, "INSERT INTO `newsletter` (`email`) VALUES (?)");
            mysqli_stmt_bind_param($stmt_news, "s", $email);
            mysqli_stmt_execute($stmt_news);
            mysqli_stmt_close($stmt_news);
        }

        // 6. Connecter l'utilisateur (inchangé)
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout_time'] = 0;
        $_SESSION['sec-username'] = $username_to_log; //

        // 7. Rediriger vers l'accueil (inchangé)
        echo '<meta http-equiv="refresh" content="0; url=' . $settings['site_url'] . '">';
        exit;

    } else {
        // Le fournisseur n'a pas retourné d'email (rare, mais possible)
        echo "Error: Unable to retrieve your email address from " . htmlspecialchars($_GET['provider']) . ".";
    }

} catch (\Exception $e) {
    // Gérer les erreurs
    echo "An error has occurred: " . $e->getMessage();
    // Gérer les erreurs spécifiques d'Hybridauth
    // switch ($e->getCode()) { ... }
}