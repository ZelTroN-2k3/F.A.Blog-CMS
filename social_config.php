<?php
// Fichier de configuration pour Hybridauth

// Assurez-vous que $settings est disponible (si ce fichier est inclus après core.php)
// Sinon, récupérez l'URL du site autrement.
global $settings;

$callback_url = $settings['site_url'] . '/social_callback.php';

return [
    'callback' => $callback_url,

    'providers' => [
        'Google' => [
            'enabled' => true,
            'keys'    => [
                'id' => '39874386834-7ut9jtu7saibmvo06tkn3ghmqei52rfa.apps.googleusercontent.com', // Remplacez par votre ID client Google
                'secret' => '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' // Remplacez par votre secret client Google
            ],
            //'scope'   => 'https_get_user_email_and_profile' // Demander l'email et le profil
            
            // --- MODIFICATION ICI ---
            // Remplacez l'ancienne ligne "scope" par celle-ci :
            'scope'   => 'email profile'
            // --- FIN DE LA MODIFICATION ---
        ],
    
        // Vous pouvez ajouter d'autres fournisseurs ici (Facebook, Twitter, etc.)
        // 'Facebook' => [ ... ],
        // 'Twitter' => [ ... ],
    ],
];