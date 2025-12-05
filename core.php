<?php
// 1. Initialisation (BDD, Session, Config, Auth, Maintenance)
require_once __DIR__ . '/core/init.php';

// 2. Fonctions utilitaires (Texte, SEO, Email, Tchat, Commentaires)
require_once __DIR__ . '/core/functions.php';

// Suivi des visiteurs
track_visitor(); 

// 3. Composants d'interface (Header, Sidebar, Footer)
require_once __DIR__ . '/core/header.php';
require_once __DIR__ . '/core/sidebar.php';
require_once __DIR__ . '/core/footer.php';
?>