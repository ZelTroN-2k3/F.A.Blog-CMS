<?php
include "core.php";

// --- GESTION DE LA SOUMISSION DU QUIZ ---
if (isset($_POST['submit_quiz']) && $logged == 'Yes') {
    validate_csrf_token();
    
    $quiz_id = (int)$_POST['quiz_id'];
    $start_time = (int)$_POST['start_time'];
    $user_answers = $_POST['answers'] ?? []; // Tableau [question_id => option_id]
    $user_id = $rowu['id'];

    $time_taken = time() - $start_time;
    $correct_count = 0;
    
    // --- 1. Récupérer TOUTES les données nécessaires (Questions & Options) ---
    // On récupère toutes les options du quiz pour avoir leurs textes et savoir laquelle est juste
    $stmt_data = mysqli_prepare($connect, "
        SELECT q.id as q_id, q.question, o.id as opt_id, o.title as opt_title, o.is_correct
        FROM quiz_questions q
        JOIN quiz_options o ON q.id = o.question_id
        WHERE q.quiz_id = ?
        ORDER BY q.position_order ASC, q.id ASC, o.id ASC
    ");
    mysqli_stmt_bind_param($stmt_data, "i", $quiz_id);
    mysqli_stmt_execute($stmt_data);
    $result_data = mysqli_stmt_get_result($stmt_data);
    
    $quiz_map = [];
    // On restructure les données : [Question_ID => [Texte, Bonnes_Reponses, Toutes_Options]]
    while ($row = mysqli_fetch_assoc($result_data)) {
        $qid = $row['q_id'];
        if (!isset($quiz_map[$qid])) {
            $quiz_map[$qid] = [
                'text' => $row['question'],
                'correct_opt_id' => null,
                'correct_opt_text' => '',
                'options' => []
            ];
        }
        // Stocker le texte de l'option pour pouvoir l'afficher plus tard
        $quiz_map[$qid]['options'][$row['opt_id']] = $row['opt_title'];
        
        if ($row['is_correct'] == 'Yes') {
            $quiz_map[$qid]['correct_opt_id'] = $row['opt_id'];
            $quiz_map[$qid]['correct_opt_text'] = $row['opt_title'];
        }
    }
    mysqli_stmt_close($stmt_data);

    // --- 2. Générer le Rapport Détaillé ---
    $detailed_report = [];
    $total_questions = count($quiz_map);
    
    foreach ($quiz_map as $qid => $q_data) {
        $user_choice_id = isset($user_answers[$qid]) ? (int)$user_answers[$qid] : null;
        $is_correct = ($user_choice_id === $q_data['correct_opt_id']);
        
        if ($is_correct) {
            $correct_count++;
        }
        
        // On sauvegarde les détails pour l'affichage
        $detailed_report[] = [
            'question' => $q_data['text'],
            'user_answer_text' => isset($q_data['options'][$user_choice_id]) ? $q_data['options'][$user_choice_id] : 'Aucune réponse',
            'correct_answer_text' => $q_data['correct_opt_text'],
            'status' => $is_correct ? 'Correct' : 'Incorrect'
        ];
    }

    // --- 3. Sauvegarde en BDD et Session ---
    $score_percentage = ($total_questions > 0) ? round(($correct_count / $total_questions) * 100) : 0;

    $stmt_insert = mysqli_prepare($connect, "INSERT INTO quiz_attempts (quiz_id, user_id, score, time_seconds, attempt_date) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt_insert, "iiii", $quiz_id, $user_id, $score_percentage, $time_taken);
    mysqli_stmt_execute($stmt_insert);
    $new_attempt_id = mysqli_insert_id($connect);
    mysqli_stmt_close($stmt_insert);

    // **C'est ici la magie : On stocke le rapport dans la session pour l'afficher après redirection**
    $_SESSION['quiz_report_' . $new_attempt_id] = $detailed_report;

    header("Location: quiz.php?id=" . $quiz_id . "&attempt=" . $new_attempt_id);
    exit;
}
// --- FIN GESTION ---

head(); 
$purifier = get_purifier();
$quiz_id_get = $_GET['id'] ?? null;

if ($settings['sidebar_position'] == 'Left') { sidebar(); }
?>

<div class="col-md-8 mb-4">
    
    <?php if ($quiz_id_get) : 
        $quiz_id = (int)$quiz_id_get;

        // Infos Quiz
        $stmt_quiz = mysqli_prepare($connect, "SELECT * FROM quizzes WHERE id = ? AND active = 'Yes'");
        mysqli_stmt_bind_param($stmt_quiz, "i", $quiz_id);
        mysqli_stmt_execute($stmt_quiz);
        $quiz = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_quiz));
        mysqli_stmt_close($stmt_quiz);
        
        // Compte Questions
        $stmt_cnt = mysqli_prepare($connect, "SELECT COUNT(id) AS count FROM quiz_questions WHERE quiz_id = ? AND active='Yes'");
        mysqli_stmt_bind_param($stmt_cnt, "i", $quiz_id);
        mysqli_stmt_execute($stmt_cnt);
        $total_questions = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cnt))['count'];
        mysqli_stmt_close($stmt_cnt);

        if ($quiz) :
            $difficulty = $quiz['difficulty'];
            $badge_class = 'bg-info text-dark'; 
            if ($difficulty == 'FACILE') $badge_class = 'bg-success';
            if ($difficulty == 'DIFFICILE') $badge_class = 'bg-warning text-dark';
            if ($difficulty == 'EXPERT') $badge_class = 'bg-danger';

            // Stats globales
            $stmt_avg = mysqli_prepare($connect, "SELECT AVG(score) AS avg_score, COUNT(DISTINCT user_id) AS total_players FROM quiz_attempts WHERE quiz_id = ?");
            mysqli_stmt_bind_param($stmt_avg, "i", $quiz_id);
            mysqli_stmt_execute($stmt_avg);
            $global_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_avg));
            mysqli_stmt_close($stmt_avg);
    ?>

        <div class="card shadow-sm border-0">
            
            <?php if (!empty($quiz['image'])) : ?>
                <img src="<?php echo htmlspecialchars($quiz['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($quiz['title']); ?>" style="height: 250px; object-fit: cover;">
            <?php endif; ?>

            <div class="card-body p-4">
                
                <div class="text-center mb-4">
                    <span class="badge <?php echo $badge_class; ?> mb-2"><?php echo $difficulty; ?></span>
                    <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h2>
                    <div class="text-muted small">
                        <i class="fas fa-users me-1"></i> <?php echo (int)$global_stats['total_players']; ?> players
                        <span class="mx-2">•</span>
                        <i class="fas fa-chart-line me-1"></i> Average: <?php echo round((float)$global_stats['avg_score']); ?>%
                    </div>
                </div>

                <div class="alert alert-light border mb-4">
                    <?php echo $purifier->purify($quiz['description']); ?>
                </div>

                <?php 
                if (isset($_GET['attempt'])) {
                    $attempt_id = (int)$_GET['attempt'];
                    $stmt_res = mysqli_prepare($connect, "SELECT * FROM quiz_attempts WHERE id=? AND user_id=?");
                    $uid = ($logged == 'Yes') ? $rowu['id'] : 0;
                    mysqli_stmt_bind_param($stmt_res, "ii", $attempt_id, $uid);
                    mysqli_stmt_execute($stmt_res);
                    $my_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_res));
                    mysqli_stmt_close($stmt_res);

                    if ($my_result) {
                        $score = $my_result['score'];
                        $res_color = ($score >= 80) ? 'success' : (($score >= 50) ? 'warning' : 'danger');
                        $res_icon = ($score >= 80) ? 'trophy' : (($score >= 50) ? 'check-circle' : 'times-circle');
                        
                        echo '
                        <div class="card bg-' . $res_color . ' text-white text-center mb-4 shadow-sm">
                            <div class="card-body py-4">
                                <i class="fas fa-' . $res_icon . ' fa-4x mb-3 opacity-75"></i>
                                <h3 class="fw-bold">Score : ' . $score . '%</h3>
                                <p class="mb-0">Time : ' . $my_result['time_seconds'] . ' seconds</p>
                            </div>
                        </div>';
                        
                        // --- AFFICHAGE DE LA CORRECTION DÉTAILLÉE (NOUVEAU) ---
                        if (isset($_SESSION['quiz_report_' . $attempt_id])) {
                            $report = $_SESSION['quiz_report_' . $attempt_id];
                            
                            echo '<h4 class="fw-bold mb-3"><i class="fas fa-list-check text-primary"></i> Detailed Correction</h4>';
                            echo '<div class="list-group mb-4 shadow-sm">';
                            
                            $q_count = 1;
                            foreach ($report as $item) {
                                $is_ok = ($item['status'] == 'Correct');
                                $icon_class = $is_ok ? 'fa-check text-success' : 'fa-times text-danger';
                                $border_class = $is_ok ? 'border-success' : 'border-danger';
                                $bg_class = $is_ok ? 'bg-light' : 'bg-danger-subtle';
                                
                                echo '
                                <div class="list-group-item p-3 mb-2 rounded border-start border-4 ' . $border_class . ' ' . $bg_class . '">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-2">Question ' . $q_count++ . ' : ' . htmlspecialchars($item['question']) . '</h6>
                                            
                                            <div class="small">
                                                <span class="fw-bold"><i class="fas ' . $icon_class . ' me-1"></i> Your answer:</span> 
                                                ' . htmlspecialchars($item['user_answer_text']) . '
                                            </div>
                                            
                                            '; 
                                            // Si faux, on affiche la bonne réponse
                                            if (!$is_ok) {
                                                echo '
                                                <div class="small mt-1 text-success">
                                                    <span class="fw-bold"><i class="fas fa-arrow-right me-1"></i> Correct answer:</span> 
                                                    ' . htmlspecialchars($item['correct_answer_text']) . '
                                                </div>';
                                            }
                                echo '
                                        </div>
                                        <span class="badge ' . ($is_ok ? 'bg-success' : 'bg-danger') . '">' . ($is_ok ? '+1' : '0') . '</span>
                                    </div>
                                </div>';
                            }
                            echo '</div>';
                            
                            // Nettoyage de la session (optionnel, pour ne pas encombrer)
                            // unset($_SESSION['quiz_report_' . $attempt_id]); 
                        }
                        // --- FIN CORRECTION ---

                        echo '
                        <div class="text-center mb-4">
                            <a href="quiz.php" class="btn btn-outline-primary">Choose another quiz</a>
                            <a href="quiz.php?id=' . $quiz_id . '" class="btn btn-primary ms-2">Restart</a>
                        </div>';
                    }
                }
                ?>
                
                <?php if (!isset($_GET['attempt']) && $total_questions > 0 && $logged == 'Yes') : ?>
                
                <form action="quiz.php?id=<?php echo $quiz_id; ?>" method="POST">
                    <input type="hidden" name="submit_quiz" value="1">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="start_time" value="<?php echo time(); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="quiz-questions">
                        <?php
                        $stmt_q = mysqli_prepare($connect, "SELECT * FROM quiz_questions WHERE quiz_id = ? AND active='Yes' ORDER BY position_order ASC, id ASC");
                        mysqli_stmt_bind_param($stmt_q, "i", $quiz_id);
                        mysqli_stmt_execute($stmt_q);
                        $questions_result = mysqli_stmt_get_result($stmt_q);

                        $q_num = 1;
                        while ($question = mysqli_fetch_assoc($questions_result)) :
                            $question_id = $question['id'];
                        ?>
                            <div class="card mb-4 border-0 bg-light shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-primary mb-3">
                                        <span class="badge bg-primary me-2"><?php echo $q_num++; ?></span> 
                                        <?php echo htmlspecialchars($question['question']); ?>
                                    </h5>
                                    
                                    <div class="list-group">
                                        <?php
                                        $options_query = mysqli_prepare($connect, "SELECT * FROM quiz_options WHERE question_id = ? ORDER BY id ASC");
                                        mysqli_stmt_bind_param($options_query, "i", $question_id);
                                        mysqli_stmt_execute($options_query);
                                        $opts = mysqli_stmt_get_result($options_query);
                                        
                                        while ($opt = mysqli_fetch_assoc($opts)) :
                                        ?>
                                            <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                                                <input class="form-check-input me-3" type="radio" 
                                                       name="answers[<?php echo $question_id; ?>]" 
                                                       value="<?php echo $opt['id']; ?>" required>
                                                <span class="fw-normal"><?php echo htmlspecialchars($opt['title']); ?></span>
                                            </label>
                                        <?php endwhile; mysqli_stmt_close($options_query); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; mysqli_stmt_close($stmt_q); ?>
                    </div>

                    <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Confirm my answers
                        </button>
                    </div>
                </form>

                <?php elseif ($logged == 'No'): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                        <h4>Login Required</h4>
                        <p class="text-muted">You must be logged in to participate and save your score.</p>
                        <a href="login.php" class="btn btn-primary px-4">Log in</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    <?php else : ?>
        <div class="alert alert-danger">Quiz not found. <a href="quiz.php">Back</a></div>
    <?php endif; ?>

<?php else : ?>
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h3 class="fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i> Our Quizzes</h3>
                <p class="text-muted">Test your knowledge on various topics!</p>
            </div>
            <div class="card-body">
                
                <?php
                // 1. Gestion du Filtre par Difficulté
                $filter_diff = $_GET['difficulty'] ?? 'ALL';
                $allowed_diffs = ['FACILE', 'NORMAL', 'DIFFICILE', 'EXPERT'];
                
                if (!in_array($filter_diff, $allowed_diffs)) {
                    $filter_diff = 'ALL';
                }

                // 2. Barre de Filtres (Tags)
                echo '<div class="mb-4 d-flex flex-wrap gap-2 justify-content-center">';
                
                // Bouton "Tout"
                $btn_class = ($filter_diff == 'ALL') ? 'btn-dark' : 'btn-outline-dark';
                echo '<a href="quiz.php" class="btn ' . $btn_class . ' rounded-pill btn-sm px-3">All</a>';
                
                // Boutons de Difficulté
                $diff_colors = [
                    'FACILE' => 'success', 
                    'NORMAL' => 'info', 
                    'DIFFICILE' => 'warning', 
                    'EXPERT' => 'danger'
                ];

                foreach ($diff_colors as $diff => $color) {
                    $active_class = ($filter_diff == $diff) ? 'btn-' . $color : 'btn-outline-' . $color;
                    $icon = ($diff == 'EXPERT') ? '<i class="fas fa-fire me-1"></i>' : '';
                    echo '<a href="quiz.php?difficulty=' . $diff . '" class="btn ' . $active_class . ' rounded-pill btn-sm px-3">' . $icon . ucfirst(strtolower($diff)) . '</a>';
                }
                echo '</div>';


                // 3. Construction de la requête SQL dynamique
                $sql_query = "
                    SELECT q.*, COUNT(qq.id) AS q_count 
                    FROM quizzes q 
                    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id AND qq.active = 'Yes' 
                    WHERE q.active = 'Yes'
                ";

                if ($filter_diff != 'ALL') {
                    $sql_query .= " AND q.difficulty = ?";
                }

                $sql_query .= " GROUP BY q.id HAVING q_count > 0 ORDER BY q.id DESC";

                $stmt_list = mysqli_prepare($connect, $sql_query);
                
                if ($filter_diff != 'ALL') {
                    mysqli_stmt_bind_param($stmt_list, "s", $filter_diff);
                }
                
                mysqli_stmt_execute($stmt_list);
                $all_quizzes = mysqli_stmt_get_result($stmt_list);


                // 4. Affichage des résultats
                if (mysqli_num_rows($all_quizzes) > 0) :
                    echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
                    while ($quiz = mysqli_fetch_assoc($all_quizzes)) :
                        
                        $difficulty = $quiz['difficulty'];
                        $badge_class = 'bg-info text-dark';
                        if ($difficulty == 'FACILE') $badge_class = 'bg-success';
                        if ($difficulty == 'DIFFICILE') $badge_class = 'bg-warning text-dark';
                        if ($difficulty == 'EXPERT') $badge_class = 'bg-danger';
                        
                        $quiz_url = "quiz.php?id=" . $quiz['id'];
                        $image_src = !empty($quiz['image']) ? htmlspecialchars($quiz['image']) : 'assets/img/no-image.png';
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm hover-shadow transition-300 border-0">
                            <div class="position-relative">
                                <a href="<?php echo $quiz_url; ?>">
                                    <img src="<?php echo $image_src; ?>" class="card-img-top" alt="Cover" style="height: 160px; object-fit: cover;">
                                </a>
                                <a href="quiz.php?difficulty=<?php echo $difficulty; ?>" class="position-absolute top-0 end-0 m-2 badge <?php echo $badge_class; ?> text-decoration-none stretched-link" style="z-index: 2; position: relative;">
                                    <?php echo $difficulty; ?>
                                </a>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-dark mb-2">
                                    <a href="<?php echo $quiz_url; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small mb-3 flex-grow-1">
                                    <?php echo htmlspecialchars(short_text(strip_tags(html_entity_decode($quiz['description'])), 80)); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span class="small text-muted"><i class="fas fa-list-ul me-1"></i> <?php echo $quiz['q_count']; ?> questions</span>
                                    <a href="<?php echo $quiz_url; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Play <i class="fas fa-play ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; echo '</div>';
                else :
                    // Message si aucun quiz trouvé pour ce filtre
                    echo '<div class="text-center py-5">
                            <i class="far fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                            <h5>No quizzes found</h5>
                            <p class="text-muted">There are no quizzes in the <strong>' . htmlspecialchars(ucfirst(strtolower($filter_diff))) . '</strong> category yet.</p>
                            <a href="quiz.php" class="btn btn-primary btn-sm">View all quizzes</a>
                          </div>';
                endif;
                mysqli_stmt_close($stmt_list);
                ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php 
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer(); 
?>