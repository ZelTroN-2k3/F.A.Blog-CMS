<?php
// Active les exceptions MySQLi pour une gestion d'erreurs propre
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Préparer la réponse JSON
header('Content-Type: application/json');
$response = [
    'success' => false,
    'message' => 'An unexpected error has occurred.'
];

// Vérifier si les données POST requises sont là
if (isset($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password'])) {
    
    $database_host     = $_POST['database_host'];
    $database_name     = $_POST['database_name'];
    $database_username = $_POST['database_username'];
    $database_password = $_POST['database_password'];

    try {
        // Tenter la connexion (sans stocker l'objet)
        new mysqli($database_host, $database_username, $database_password, $database_name);
        
        // Si la ligne ci-dessus n'échoue pas, la connexion est réussie
        $response['success'] = true;
        $response['message'] = 'Connection successful! You may proceed.';
        
    } catch (mysqli_sql_exception $e) {
        // Capturer l'erreur de connexion
        $response['message'] = 'Connection error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Form data missing.';
}

// Renvoyer la réponse
echo json_encode($response);
exit;