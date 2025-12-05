<?php
// On démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Nettoyage complet de la session ---

// Détruit toutes les variables de session
$_SESSION = array();

// Si l'on souhaite détruire complètement la session, il faut aussi effacer le cookie de session.
// Note : Cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalement, on détruit la session.
session_destroy();

// --- 2. Redirection robuste ---

// On utilise la redirection PHP native, bien meilleure que la balise <meta> HTML.
// On redirige simplement vers la racine du site ou index.php.
// Pas besoin de demander l'URL à la base de données.
header("Location: index.php");
exit(); // Toujours mettre exit() après une redirection header
?>