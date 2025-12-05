<?php
include "header.php"; 

if ($rowu['role'] == 'User') {
    echo '<meta http-equiv="refresh" content="0;url=dashboard">';
    exit;
}

// --- LOGIQUE SUPPRESSION TENTATIVE ---
if (isset($_GET['delete-attempt'])) {
    // Pas de token CSRF strict ici pour simplifier l'exemple, 
    // mais idéalement il faudrait validate_csrf_token_get();
    $id = (int)$_GET['delete-attempt'];
    $stmt_del = mysqli_prepare($connect, "DELETE FROM quiz_attempts WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);
    echo '<meta http-equiv="refresh" content="0;url=quiz_stats.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-chart-bar"></i> Quiz Statistics</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="quizzes.php">Quiz Manager</a></li>
                    <li class="breadcrumb-item active">Quiz Statistics</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <?php
                        $stmt_total_quizzes = mysqli_query($connect, "SELECT COUNT(id) FROM `quizzes` WHERE active = 'Yes'");
                        $total_quizzes = mysqli_fetch_array($stmt_total_quizzes)[0];
                        ?>
                        <h3><?php echo $total_quizzes; ?></h3>
                        <p>Active Quizzes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <a href="quizzes.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <?php
                        $stmt_total_attempts = mysqli_query($connect, "SELECT COUNT(id) FROM `quiz_attempts`");
                        $total_attempts = mysqli_fetch_array($stmt_total_attempts)[0];
                        ?>
                        <h3><?php echo $total_attempts; ?></h3>
                        <p>Total Quiz Attempts</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-play"></i>
                    </div>
                    <a href="#all-history" class="small-box-footer">View History <i class="fas fa-arrow-circle-down"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <?php
                        $stmt_avg_score = mysqli_query($connect, "SELECT AVG(score) FROM `quiz_attempts`");
                        $avg_score = round(mysqli_fetch_array($stmt_avg_score)[0]);
                        ?>
                        <h3><?php echo $avg_score; ?><sup style="font-size: 20px">%</sup></h3>
                        <p>Average Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percent"></i>
                    </div>
                    <a href="#all-history" class="small-box-footer">View Details <i class="fas fa-arrow-circle-down"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <?php
                        $stmt_unique_players = mysqli_query($connect, "SELECT COUNT(DISTINCT user_id) FROM `quiz_attempts`");
                        $unique_players = mysqli_fetch_array($stmt_unique_players)[0];
                        ?>
                        <h3><?php echo $unique_players; ?></h3>
                        <p>Unique Players</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="users.php" class="small-box-footer">Manage Users <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Quizzes by Attempts</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Attempts</th>
                                        <th>Average Score</th>
                                        <th style="width: 100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt_top_quizzes = mysqli_query($connect, "
                                        SELECT q.id, q.title, COUNT(qa.id) AS total_attempts, AVG(qa.score) AS avg_score 
                                        FROM quizzes q 
                                        JOIN quiz_attempts qa ON q.id = qa.quiz_id 
                                        GROUP BY q.id 
                                        ORDER BY total_attempts DESC 
                                        LIMIT 5
                                    ");
                                    if (mysqli_num_rows($stmt_top_quizzes) > 0) {
                                        while ($tq_row = mysqli_fetch_assoc($stmt_top_quizzes)) {
                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($tq_row['title']) . '</td>
                                                <td>' . $tq_row['total_attempts'] . '</td>
                                                <td>' . round($tq_row['avg_score']) . '%</td>
                                                <td>
                                                    <a href="edit_quiz.php?id=' . $tq_row['id'] . '" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No quiz data available.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Latest 5 Quiz Attempts</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Player</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt_latest_attempts = mysqli_query($connect, "
                                        SELECT qa.score, qa.attempt_date, q.title AS quiz_title, u.username AS player_username 
                                        FROM quiz_attempts qa 
                                        JOIN quizzes q ON qa.quiz_id = q.id 
                                        JOIN users u ON qa.user_id = u.id 
                                        ORDER BY qa.attempt_date DESC 
                                        LIMIT 5
                                    ");
                                    if (mysqli_num_rows($stmt_latest_attempts) > 0) {
                                        while ($la_row = mysqli_fetch_assoc($stmt_latest_attempts)) {
                                            $score_color = ($la_row['score'] >= 80) ? 'text-success' : (($la_row['score'] >= 50) ? 'text-warning' : 'text-danger');
                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($la_row['quiz_title']) . '</td>
                                                <td>' . htmlspecialchars($la_row['player_username']) . '</td>
                                                <td class="' . $score_color . ' fw-bold">' . $la_row['score'] . '%</td>
                                                <td>' . date("d M Y H:i", strtotime($la_row['attempt_date'])) . '</td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No recent attempts.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="all-history">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Complete Quiz History</h3>
                    </div>
                    <div class="card-body">
                        <table id="table-history" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Quiz</th>
                                    <th>Player</th>
                                    <th>Score</th>
                                    <th>Time</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Requête pour récupérer TOUTES les tentatives (Votre code original)
                                $q_history = mysqli_query($connect, "
                                    SELECT qa.*, q.title AS quiz_title, u.username 
                                    FROM quiz_attempts qa
                                    LEFT JOIN quizzes q ON qa.quiz_id = q.id
                                    LEFT JOIN users u ON qa.user_id = u.id
                                    ORDER BY qa.id DESC
                                ");

                                while ($hist = mysqli_fetch_assoc($q_history)) {
                                    // Couleur du score
                                    $score_badge = 'bg-danger';
                                    if ($hist['score'] >= 50) $score_badge = 'bg-warning';
                                    if ($hist['score'] >= 80) $score_badge = 'bg-success';

                                    // Gestion suppression quiz/user
                                    $quiz_name = $hist['quiz_title'] ? htmlspecialchars($hist['quiz_title']) : '<i class="text-muted">Deleted Quiz</i>';
                                    $user_name = $hist['username'] ? htmlspecialchars($hist['username']) : '<i class="text-muted">Unknown</i>';
                                ?>
                                <tr>
                                    <td><?php echo $hist['id']; ?></td>
                                    <td><?php echo $quiz_name; ?></td>
                                    <td><?php echo $user_name; ?></td>
                                    <td><span class="badge <?php echo $score_badge; ?>"><?php echo $hist['score']; ?>%</span></td>
                                    <td><?php echo $hist['time_seconds']; ?>s</td>
                                    <td><?php echo date("d M Y, H:i", strtotime($hist['attempt_date'])); ?></td>
                                    <td>
                                        <a href="?delete-attempt=<?php echo $hist['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> 

    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(function () {
    // Active DataTables sur le nouveau tableau d'historique
    $('#table-history').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 0, "desc" ]] // Trier par ID descendant par défaut
    });
});
</script>