<?php
include "core.php";

// --- ÉTAPE 0 : VÉRIFICATION DES PRÉREQUIS ---
$checks = [];
$all_checks_passed = true;

// 1. Version de PHP
$php_version_required = '7.4';
$php_version_ok = version_compare(phpversion(), $php_version_required, '>=');
$checks[] = [
    'text' => 'PHP version ' . $php_version_required . '+ (Current: ' . phpversion() . ')',
    'status' => $php_version_ok
];
if (!$php_version_ok) $all_checks_passed = false;

// 2. Extension MySQLi
$mysqli_ok = extension_loaded('mysqli');
$checks[] = [
    'text' => 'PHP extension "mysqli" installed',
    'status' => $mysqli_ok
];
if (!$mysqli_ok) $all_checks_passed = false;

// 3. Permission d'écriture config.php
$config_writable_ok = is_writable(CONFIG_FILE_DIRECTORY);
$checks[] = [
    'text' => 'Write permission on directory <code>' . htmlspecialchars(CONFIG_FILE_DIRECTORY) . '</code>',
    'status' => $config_writable_ok
];
if (!$config_writable_ok) $all_checks_passed = false;

// 4. Lecture database.sql
$sql_readable_ok = is_readable('database.sql');
$checks[] = [
    'text' => 'The file <code>database.sql</code> is readable',
    'status' => $sql_readable_ok
];
if (!$sql_readable_ok) $all_checks_passed = false;


// --- ÉTAPE 1 : GESTION DU FORMULAIRE DE BDD (si prérequis OK) ---
$error_message = null; 

if ($all_checks_passed && isset($_POST['submit'])) {
    
    // (Cette logique est la même que celle que nous avons faite précédemment)
    $database_host     = $_POST['database_host'];
    $database_name     = $_POST['database_name'];
    $database_username = $_POST['database_username'];
    $database_password = $_POST['database_password'];
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        @$db = new mysqli($database_host, $database_username, $database_password, $database_name);
        
        $_SESSION['database_host']     = $database_host;
        $_SESSION['database_name']     = $database_name;
        $_SESSION['database_username'] = $database_username;
        $_SESSION['database_password'] = $database_password;
        
        header("Location: settings.php");
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $error_message = 'Connection error: ' . $e->getMessage();
    }
}

// --- AFFICHAGE HTML ---
head();
?>

<center><h5>Step 1/3: Prerequisite Check</h5></center>
<br />
<ul class="checklist">
    <?php foreach ($checks as $check): ?>
        <li>
            <?php if ($check['status']): ?>
                <i class="fas fa-check-circle text-success"></i>
            <?php else: ?>
                <i class="fas fa-times-circle text-danger"></i>
            <?php endif; ?>
            <?php echo $check['text']; ?>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (!$all_checks_passed): ?>
    <div class="alert alert-danger">
        <strong>Warning!</strong> One or more prerequisites are not met.
        Please fix the issues above (marked with <i class="fas fa-times-circle text-danger"></i>) before continuing.
    </div>
<?php else: ?>
    <hr>
    <center><h6>Enter your database connection information.</h6></center>
    <br />
    
    <form method="post" action="" class="form-horizontal row-border"> 
                
        <div class="form-group row">
            <p class="col-sm-3">Database Host: </p>
            <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-database"></i></span>
                <input type="text" id="db-host" name="database_host" class="form-control" placeholder="localhost" value="<?php echo htmlspecialchars($_SESSION['database_host'] ?? 'localhost'); ?>" required>
            </div>
            </div>
        </div>
        <div class="form-group row">
            <p class="col-sm-3">Database Name: </p>
            <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-list-alt"></i></span>
                <input type="text" id="db-name" name="database_name" class="form-control" placeholder="phpblog" value="<?php echo htmlspecialchars($_SESSION['database_name'] ?? ''); ?>" required>
            </div>
            </div>
        </div>
        <div class="form-group row">
            <p class="col-sm-3">Database Username: </p>
            <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" id="db-user" name="database_username" class="form-control" placeholder="root" value="<?php echo htmlspecialchars($_SESSION['database_username'] ?? ''); ?>" required>
            </div>
            </div>
        </div>
        <div class="form-group row">
            <p class="col-sm-3">Database Password: </p>
            <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" id="db-pass" name="database_password" class="form-control" placeholder="" value="<?php echo htmlspecialchars($_SESSION['database_password'] ?? ''); ?>">
            </div>
            </div>
        </div><br />

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div id="test-result" class="mb-3"></div>

        <button id="test-connection-btn" class="btn btn-secondary col-12 mb-2"><i class="fas fa-plug"></i> Test the connection</button>
        
        <input class="btn-primary btn col-12" type="submit" name="submit" value="Next" disabled />
        
    </form>
    </div>
<?php endif; ?>

<?php
// --- JAVASCRIPT POUR L'AJAX (uniquement si les prérequis sont OK) ---
if ($all_checks_passed):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const testBtn = document.getElementById('test-connection-btn');
    const submitBtn = document.querySelector('input[name="submit"]');
    const resultDiv = document.getElementById('test-result');

    // Garder le bouton "Suivant" désactivé au début
    submitBtn.disabled = true;

    testBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Empêche la soumission du formulaire

        // 1. Récupérer les valeurs
        const db_host = document.getElementById('db-host').value;
        const db_name = document.getElementById('db-name').value;
        const db_user = document.getElementById('db-user').value;
        const db_pass = document.getElementById('db-pass').value;

        // 2. Afficher le chargement
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Test in progress...</div>';
        submitBtn.disabled = true; // Désactiver pendant le test

        // 3. Préparer les données pour le POST
        const formData = new FormData();
        formData.append('database_host', db_host);
        formData.append('database_name', db_name);
        formData.append('database_username', db_user);
        formData.append('database_password', db_pass);

        // 4. Exécuter la requête fetch
        fetch('test_db.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Succès !
                resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                submitBtn.disabled = false; // Activer le bouton "Suivant"
            } else {
                // Échec
                resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                submitBtn.disabled = true; // Laisser désactivé
            }
        })
        .catch(error => {
            // Erreur réseau ou JSON
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger">Communication error with test script.</div>';
            submitBtn.disabled = true;
        });
    });
});
</script>
<?php
endif;

footer();
?>