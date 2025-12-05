<?php
// Fichier: ajax_vote_poll.php
include "core.php"; // Pour la connexion BDD et la session

header('Content-Type: application/json');

// 1. Vérification de la méthode et du token CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Refresh the page.']);
    exit;
}

// 2. Récupération des données
$poll_id = (int)$_POST['poll_id'];
$option_id = (int)$_POST['option_id'];
$user_ip = $_SERVER['REMOTE_ADDR'];

if ($poll_id <= 0 || $option_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please select an option.']);
    exit;
}

// 3. Vérifier si le sondage est actif
$stmt = mysqli_prepare($connect, "SELECT id FROM polls WHERE id = ? AND active = 'Yes'");
mysqli_stmt_bind_param($stmt, "i", $poll_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'This poll is closed or does not exist.']);
    exit;
}
mysqli_stmt_close($stmt);

// 4. Vérifier si l'IP a déjà voté pour CE sondage
$stmt_check = mysqli_prepare($connect, "SELECT id FROM poll_voters WHERE poll_id = ? AND ip_address = ?");
mysqli_stmt_bind_param($stmt_check, "is", $poll_id, $user_ip);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

// Vérification supplémentaire via cookie (pour les IP partagées)
$cookie_name = 'poll_voted_' . $poll_id;
if (mysqli_stmt_num_rows($stmt_check) > 0 || isset($_COOKIE[$cookie_name])) {
    echo json_encode(['status' => 'error', 'message' => 'You have already voted on this poll.']);
    exit;
}
mysqli_stmt_close($stmt_check);

// 5. Enregistrer le vote
// A. Incrémenter le compteur de l'option choisie
$stmt_upd = mysqli_prepare($connect, "UPDATE poll_options SET votes = votes + 1 WHERE id = ? AND poll_id = ?");
mysqli_stmt_bind_param($stmt_upd, "ii", $option_id, $poll_id);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);

// B. Enregistrer l'IP du votant
$stmt_log = mysqli_prepare($connect, "INSERT INTO poll_voters (poll_id, ip_address) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt_log, "is", $poll_id, $user_ip);
mysqli_stmt_execute($stmt_log);
mysqli_stmt_close($stmt_log);

// C. Poser un cookie (valide 30 jours)
setcookie($cookie_name, '1', time() + (86400 * 30), "/");

// 6. Renvoyer les résultats à jour (pour l'affichage immédiat)
$results = [];
$total_votes = 0;
$q = mysqli_query($connect, "SELECT id, title, votes FROM poll_options WHERE poll_id = $poll_id ORDER BY id ASC");
while($row = mysqli_fetch_assoc($q)){
    $results[] = $row;
    $total_votes += $row['votes'];
}

echo json_encode([
    'status' => 'success',
    'message' => 'Thank you for voting!',
    'total_votes' => $total_votes,
    'results' => $results
]);
exit;
?>