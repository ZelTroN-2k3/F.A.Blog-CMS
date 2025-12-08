<?php
// ajax_submit_score.php
require_once 'core.php';

header('Content-Type: application/json');

// 1. Sécurité : Utilisateur connecté uniquement
if ($logged != 'Yes') {
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $game = mysqli_real_escape_string($connect, $_POST['game']); // ex: 'snake'
    $score = (int)$_POST['score'];
    $user_id = $rowu['id'];

    // 2. Enregistrer le score
    $stmt = mysqli_prepare($connect, "INSERT INTO game_scores (user_id, game_name, score) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isi", $user_id, $game, $score);
    
    if (mysqli_stmt_execute($stmt)) {
        
        // 3. VÉRIFICATION DES BADGES (Gamification)
        $new_badges = [];
        $trigger_key = 'score_' . $game; // ex: score_snake
        
        // On cherche les badges de ce jeu que l'utilisateur N'A PAS encore
        $sql_badges = "SELECT * FROM badges 
                       WHERE trigger_type = '$trigger_key' 
                       AND trigger_value <= $score 
                       AND id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = $user_id)";
                       
        $q_badges = mysqli_query($connect, $sql_badges);
        
        while ($badge = mysqli_fetch_assoc($q_badges)) {
            // Attribuer le badge
            $b_id = $badge['id'];
            mysqli_query($connect, "INSERT INTO user_badges (user_id, badge_id) VALUES ($user_id, $b_id)");
            $new_badges[] = $badge['name'];
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Score saved!', 
            'new_badges' => $new_badges
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>