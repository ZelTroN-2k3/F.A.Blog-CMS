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

// --- Logique de Suppression (VOTRE LOGIQUE ORIGINALE) ---
if (isset($_GET['delete-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['delete-id'];

    // 1. Trouver toutes les questions
    $q_ids_stmt = mysqli_prepare($connect, "SELECT id FROM quiz_questions WHERE quiz_id = ?");
    mysqli_stmt_bind_param($q_ids_stmt, "i", $id);
    mysqli_stmt_execute($q_ids_stmt);
    $q_ids_result = mysqli_stmt_get_result($q_ids_stmt);
    $question_ids = [];
    while ($row = mysqli_fetch_assoc($q_ids_result)) {
        $question_ids[] = $row['id'];
    }
    mysqli_stmt_close($q_ids_stmt);

    // 2. Supprimer les options de ces questions
    if (!empty($question_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($question_ids), '?'));
        $types = str_repeat('i', count($question_ids));
        
        $del_opt_stmt = mysqli_prepare($connect, "DELETE FROM quiz_options WHERE question_id IN ($ids_placeholder)");
        mysqli_stmt_bind_param($del_opt_stmt, $types, ...$question_ids);
        mysqli_stmt_execute($del_opt_stmt);
        mysqli_stmt_close($del_opt_stmt);
    }
    
    // 3. Supprimer les questions
    $del_q_stmt = mysqli_prepare($connect, "DELETE FROM quiz_questions WHERE quiz_id = ?");
    mysqli_stmt_bind_param($del_q_stmt, "i", $id);
    mysqli_stmt_execute($del_q_stmt);
    mysqli_stmt_close($del_q_stmt);
    
    // 4. Supprimer le quiz lui-même
    $del_quiz_stmt = mysqli_prepare($connect, "DELETE FROM quizzes WHERE id = ?");
    mysqli_stmt_bind_param($del_quiz_stmt, "i", $id);
    mysqli_stmt_execute($del_quiz_stmt);
    mysqli_stmt_close($del_quiz_stmt);

    header("Location: quizzes.php");
    exit;
}

// --- Logique de Basculement de Statut ---
$csrf_token = $_SESSION['csrf_token'];

if (isset($_GET['activate-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['activate-id'];
    $stmt_activate = mysqli_prepare($connect, "UPDATE quizzes SET active = 'Yes' WHERE id = ?");
    mysqli_stmt_bind_param($stmt_activate, "i", $id);
    mysqli_stmt_execute($stmt_activate);
    mysqli_stmt_close($stmt_activate);
    header("Location: quizzes.php");
    exit;
}

if (isset($_GET['deactivate-id'])) {
    validate_csrf_token_get();
    $id = (int)$_GET['deactivate-id'];
    $stmt_deactivate = mysqli_prepare($connect, "UPDATE quizzes SET active = 'No' WHERE id = ?");
    mysqli_stmt_bind_param($stmt_deactivate, "i", $id);
    mysqli_stmt_execute($stmt_deactivate);
    mysqli_stmt_close($stmt_deactivate);
    header("Location: quizzes.php");
    exit;
}

include 'header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-check-double"></i> Manage Quizzes</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Quizzes</li>
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
                            <a href="add_quiz.php" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Create New Quiz
                            </a>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <table id="quiz-table" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th style="width: 100px;" class="text-center">Image</th>
                                    <th>Title</th>
                                    <th class="text-center">Difficulty</th>
                                    <th class="text-center">Questions</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_quizzes = mysqli_query($connect, "
                                    SELECT q.*, COUNT(qq.id) AS question_count
                                    FROM quizzes q
                                    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
                                    GROUP BY q.id
                                    ORDER BY q.id DESC
                                ");
                                while ($row = mysqli_fetch_assoc($query_quizzes)) {
                                    
                                    $badge_class = 'badge-info';
                                    if ($row['difficulty'] == 'FACILE') $badge_class = 'badge-success';
                                    if ($row['difficulty'] == 'DIFFICILE') $badge_class = 'badge-warning';
                                    if ($row['difficulty'] == 'EXPERT') $badge_class = 'badge-danger';
                                ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td class="text-center">
                                            <?php if(!empty($row['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($row['image']); ?>" width="80" height="50" style="object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-image"></i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                        <td class="text-center"><span class="badge <?php echo $badge_class; ?>"><?php echo $row['difficulty']; ?></span></td>
                                        <td class="text-center">
                                            <span class="badge badge-light border"><?php echo $row['question_count']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['active'] == 'Yes') : ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="quiz_questions.php?quiz_id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm mr-1" title="Manage Questions">
                                                <i class="fas fa-list"></i>
                                            </a>

                                            <?php if ($row['active'] == 'Yes') : ?>
                                                <a href="quizzes.php?deactivate-id=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-warning btn-sm mr-1" title="Deactivate"><i class="fas fa-eye-slash"></i></a>
                                            <?php else : ?>
                                                <a href="quizzes.php?activate-id=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-sm mr-1" title="Activate"><i class="fas fa-eye"></i></a>
                                            <?php endif; ?>

                                            <a href="edit_quiz.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm mr-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="quizzes.php?delete-id=<?php echo $row['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this Quiz AND all its associated questions and answers?');" title="Delete">
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

<?php include 'footer.php'; ?>

<script>
$(function () {
    $('#quiz-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [1, 6] } 
        ]
    });
});
</script>