<?php
include "core.php";

// --- VARIABLES D'ÉTAT ---
$install_success = false;
$error_message = null;
$site_url_created = ''; // Pour l'afficher sur la page de succès

// --- LOGIQUE D'INSTALLATION COMPLÈTE (AVANT head()) ---
try {
    // 1. Récupérer les données de session (ou assigner null)
    $database_host     = $_SESSION['database_host'] ?? null;
    $database_username = $_SESSION['database_username'] ?? null;
    $database_password = $_SESSION['database_password'] ?? null;
    $database_name     = $_SESSION['database_name'] ?? null;
    $username          = $_SESSION['username'] ?? null;
    $email             = $_SESSION['email'] ?? null;      // Email Admin (table users)
    $site_email        = $_SESSION['site_email'] ?? null; // Email Site (table settings)
    $raw_password      = $_SESSION['password'] ?? null;

    // 2. Vérifier si les données de session essentielles sont présentes
    if (!$database_host || !$database_name || !$database_username || !$username || !$email || !$site_email || !$raw_password) {
        throw new Exception("Session expirée ou données manquantes. Veuillez recommencer l'installation depuis le début.");
    }
    
    // Hasher le mot de passe
    $password_hashed = password_hash($raw_password, PASSWORD_DEFAULT);

    // 3. Connexion à la base de données (active les exceptions)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli($database_host, $database_username, $database_password, $database_name);

    // 4. Importation des tables SQL depuis database.sql
    $sql_dump_content = file_get_contents('database.sql');
    if ($sql_dump_content === false) {
        throw new Exception("Impossible de lire le fichier 'database.sql'.");
    }
    
    // Exécuter le script SQL (multi_query est plus robuste pour les fichiers .sql)
    if ($db->multi_query($sql_dump_content) === false) {
        throw new Exception("Erreur lors de l'importation de la base de données : " . $db->error);
    }
    // Vider les résultats de multi_query
    while ($db->more_results() && $db->next_result()) {};

    
    // 5. Création du fichier config.php (à partir du config.tpl)
    $config_template = file_get_contents(CONFIG_FILE_TEMPLATE);
    if ($config_template === false) {
        throw new Exception("Impossible de lire le fichier template '" . CONFIG_FILE_TEMPLATE . "'.");
    }
    
    $config_file = str_replace("<DB_HOST>", $database_host, $config_template);
    $config_file = str_replace("<DB_NAME>", $database_name, $config_file);
    $config_file = str_replace("<DB_USER>", $database_username, $config_file);
    $config_file = str_replace("<DB_PASSWORD>", $database_password, $config_file);
    
    // 6. Création de l'utilisateur Admin (requête préparée)
    $stmt = $db->prepare("INSERT INTO `users` (username, password, email, role) VALUES (?, ?, ?, 'Admin')");
    $stmt->bind_param("sss", $username, $password_hashed, $email);
    $stmt->execute();
    $stmt->close();
	
    // --- DÉBUT DE LA MODIFICATION (Étape 7) ---
    // 7. Mettre à jour site_url et email dans la table 'settings' (NOUVELLE STRUCTURE)
    
    // Calculer le site_url (plus robuste)
    $htp = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'; // Correction du typo 'httpsias'
    $fullpath = "$htp://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $site_url = substr($fullpath, 0, strpos($fullpath, '/install'));
    $site_url_created = $site_url; // Sauvegarder pour affichage

    // MISE À JOUR : Mettre à jour site_url et email dans la BDD (MAINTENANT EN 1 REQUÊTE)
    // On met à jour la ligne unique où id = 1
    $stmt_update = $db->prepare("UPDATE `settings` SET `site_url` = ?, `email` = ? WHERE `id` = 1");
    $stmt_update->bind_param("ss", $site_url, $site_email); // <-- UTILISATION DE $site_email
    $stmt_update->execute();
    $stmt_update->close();
    // --- FIN DE LA MODIFICATION ---
    
    // 8. Écrire le fichier config.php final (sans chmod 0777)
    
    // Vérifier d'abord si le dossier est accessible en écriture
    if (!is_writable(CONFIG_FILE_DIRECTORY)) {
        throw new Exception("Erreur de permission : Le dossier '" . htmlspecialchars(CONFIG_FILE_DIRECTORY) . "' n'est pas accessible en écriture. Veuillez appliquer un CHMOD 755 sur ce dossier.");
    }
    
    // file_put_contents est plus propre que fopen/fwrite/fclose
    if (file_put_contents(CONFIG_FILE_PATH, $config_file) === false) {
        throw new Exception("Impossible d'écrire le fichier de configuration '" . CONFIG_FILE_PATH . "'.");
    }
    
    // 9. Succès !
    $install_success = true;
    session_destroy(); // Détruire la session d'installation
    
} catch (Exception $e) {
    // 10. Capturer n'importe quelle erreur survenue pendant le processus
    $error_message = $e->getMessage();
}

// --- AFFICHAGE HTML (BASÉ SUR $install_success) ---
head();

if ($install_success) {
?>
<center>
    <h2>Installation Completed</h2>
    <br />
    <h5>Congratulations! phpBlog has been successfully installed.</h5>
    <br />
    <div class="alert alert-warning">
        <b>Security Warning!</b> Please remove the "install" folder from your server.
    </div>
    <br />
    <a href="<?php echo htmlspecialchars($site_url_created); ?>/" class="btn-success btn btn-lg"><i class="fas fa-home"></i> Go to Homepage</a>
    <a href="<?php echo htmlspecialchars($site_url_created); ?>/admin/index.php" class="btn-primary btn btn-lg"><i class="fas fa-cog"></i> Go to Admin Panel</a>
</center>

<?php
} else {
    // Afficher un message d'erreur clair
?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-times-circle"></i> Installation Failed</h4>
        <p>An error occurred during the installation process:</p>
        <pre><?php echo htmlspecialchars($error_message); ?></pre>
        <br>
        <p>Please check the following:</p>
        <ul>
            <li>Are your database credentials correct?</li>
            <li>Does the database user have permission to create tables?</li>
            <li>Is the file <code>database.sql</code> present and readable?</li>
            <li>Is the parent directory (<code><?php echo htmlspecialchars(CONFIG_FILE_DIRECTORY); ?></code>) writable by the server?</li>
        </ul>
        <br>
        <a href="index.php" class="btn-primary btn"><i class="fas fa-arrow-left"></i> Go Back & Try Again</a>
    </div>
<?php
}

footer();
?>