<?php
// 1. INCLURE LE NOYAU D'ABORD
include_once '../core.php'; 

// 2. VÉRIFICATION DE SÉCURITÉ
if (isset($_SESSION['sec-username'])) {
    $uname = $_SESSION['sec-username'];
    $stmt = mysqli_prepare($connect, "SELECT * FROM `users` WHERE username=? AND (role='Admin' OR role='Editor')");
    mysqli_stmt_bind_param($stmt, "s", $uname);
    mysqli_stmt_execute($stmt);
    $suser = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($suser) <= 0) {
        header("Location: " . $settings['site_url']); exit;
    }
    $user = mysqli_fetch_assoc($suser);
} else {
    header("Location: ../login"); exit;
}

// --- NOUVEAU : Récupérer l'ID du Quiz ---
if (!isset($_GET['quiz_id'])) {
    header("Location: quizzes.php"); // Rediriger si aucun quiz n'est sélectionné
    exit;
}
$quiz_id = (int)$_GET['quiz_id'];

// --- Récupérer les infos du Quiz parent ---
$stmt_quiz = mysqli_prepare($connect, "SELECT title FROM quizzes WHERE id = ?");
mysqli_stmt_bind_param($stmt_quiz, "i", $quiz_id);
mysqli_stmt_execute($stmt_quiz);
$result_quiz = mysqli_stmt_get_result($stmt_quiz);
$quiz_data = mysqli_fetch_assoc($result_quiz);
mysqli_stmt_close($stmt_quiz);

if (!$quiz_data) {
    header("Location: quizzes.php");
    exit;
}
$quiz_title = $quiz_data['title'];


// --- Logique de Suppression (Question) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete-id'];

    // 1. Supprimer les options associées
    $stmt_del_opts = mysqli_prepare($connect, "DELETE FROM quiz_options WHERE question_id = ?");
    mysqli_stmt_bind_param($stmt_del_opts, "i", $id);
    mysqli_stmt_execute($stmt_del_opts);
    mysqli_stmt_close($stmt_del_opts);

    // 2. Supprimer la question
    $stmt_del_q = mysqli_prepare($connect, "DELETE FROM quiz_questions WHERE id = ? AND quiz_id = ?");
    mysqli_stmt_bind_param($stmt_del_q, "ii", $id, $quiz_id);
    mysqli_stmt_execute($stmt_del_q);
    mysqli_stmt_close($stmt_del_q);

    header("Location: quiz_questions.php?quiz_id=" . $quiz_id); // Revenir à ce quiz
    exit;
}

// --- Logique de Basculement de Statut (Toggle) ---
$csrf_token = $_SESSION['csrf_token']; 

if (isset($_GET['activate-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['activate-id'];
    $stmt_activate = mysqli_prepare($connect, "UPDATE quiz_questions SET active = 'Yes' WHERE id = ? AND quiz_id = ?");
    mysqli_stmt_bind_param($stmt_activate, "ii", $id, $quiz_id);
    mysqli_stmt_execute($stmt_activate);
    mysqli_stmt_close($stmt_activate);
    header("Location: quiz_questions.php?quiz_id=" . $quiz_id);
    exit;
}

if (isset($_GET['deactivate-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['deactivate-id'];
    $stmt_deactivate = mysqli_prepare($connect, "UPDATE quiz_questions SET active = 'No' WHERE id = ? AND quiz_id = ?");
    mysqli_stmt_bind_param($stmt_deactivate, "ii", $id, $quiz_id);
    mysqli_stmt_execute($stmt_deactivate);
    mysqli_stmt_close($stmt_deactivate);
    header("Location: quiz_questions.php?quiz_id=" . $quiz_id);
    exit;
}

// 3. INCLURE LE HEADER HTML
include 'header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1 class="m-0"><i class="fas fa-list-ol"></i> Questions: <strong><?php echo htmlspecialchars($quiz_title); ?></strong></h1>
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="quizzes.php">Quiz Manager</a></li>
                    <li class="breadcrumb-item active">Manage Questions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <a href="add_question.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Question
                            </a>
                            <a href="quizzes.php" class="btn btn-default btn-sm ml-2">
                                <i class="fas fa-arrow-left"></i> Back to Quizzes
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="quiz-table" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>Question</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Requête MISE À JOUR pour filtrer par quiz_id (Votre logique originale)
                                $stmt_get_q = mysqli_prepare($connect, "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY position_order ASC, id DESC");
                                mysqli_stmt_bind_param($stmt_get_q, "i", $quiz_id);
                                mysqli_stmt_execute($stmt_get_q);
                                $query_questions = mysqli_stmt_get_result($stmt_get_q);

                                while ($row = mysqli_fetch_assoc($query_questions)) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo strip_tags(html_entity_decode($row['question'])); ?></td>
                                        <td class="text-center">
                                            <?php if ($row['active'] == 'Yes') : ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else : ?>
                                                <span class="badge badge-warning">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['active'] == 'Yes') : ?>
                                                <a href="quiz_questions.php?deactivate-id=<?php echo $row['id']; ?>&quiz_id=<?php echo $quiz_id; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-warning btn-sm mr-1" title="Deactivate">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            <?php else : ?>
                                                <a href="quiz_questions.php?activate-id=<?php echo $row['id']; ?>&quiz_id=<?php echo $quiz_id; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-sm mr-1" title="Activate">
                                                    <i class="fas fa-toggle-on"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="edit_question.php?id=<?php echo $row['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-info btn-sm mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="quiz_questions.php?delete-id=<?php echo $row['id']; ?>&quiz_id=<?php echo $quiz_id; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question AND all its answers?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } mysqli_stmt_close($stmt_get_q); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>

<script>
$(function () {
    $('#quiz-table').DataTable({
        "paging": true, "lengthChange": true, "searching": true,
        "ordering": true, "info": true, "autoWidth": false, "responsive": true,
        "order": [[ 0, "desc" ]]
    });
});
</script>