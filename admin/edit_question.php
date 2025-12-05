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
// --- FIN SÉCURITÉ ---

// --- Vérification de l'ID de la Question ET du Quiz ---
if ((!isset($_GET['id']) && !isset($_POST['question_id'])) || (!isset($_GET['quiz_id']) && !isset($_POST['quiz_id']))) {
    header("Location: quizzes.php");
    exit;
}
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['question_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : (int)$_POST['quiz_id'];
// --- FIN ---

$message = '';
$question_data = null;
$options_data = [];

// --- Logique de Traitement (POST) ---
if (isset($_POST['edit_question'])) {
    validate_csrf_token(); 

    $question = $_POST['question'];
    
    $purifier = get_purifier();
    $explanation = $purifier->purify($_POST['explanation']); 
    
    $active = $_POST['active'];
    $options = $_POST['options'] ?? [];
    $correct_option_index = $_POST['is_correct'] ?? -1; 

    if (empty($question)) {
        $message = '<div class="alert alert-danger">The question cannot be empty.</div>';
    } elseif (count($options) < 2) {
        $message = '<div class="alert alert-danger">You must add at least two answer options.</div>';
    } elseif ($correct_option_index == -1) {
        $message = '<div class="alert alert-danger">Please mark one answer as correct.</div>';
    } else {
        
        // REQUÊTE MISE À JOUR : On enlève 'difficulty'
        $stmt_update_q = mysqli_prepare($connect, "UPDATE quiz_questions SET question = ?, explanation = ?, active = ? WHERE id = ? AND quiz_id = ?");
        mysqli_stmt_bind_param($stmt_update_q, "sssii", $question, $explanation, $active, $question_id, $quiz_id);
        
        if (mysqli_stmt_execute($stmt_update_q)) {
            mysqli_stmt_close($stmt_update_q);

            // 2. Supprimer les anciennes options
            $stmt_delete_opts = mysqli_prepare($connect, "DELETE FROM quiz_options WHERE question_id = ?");
            mysqli_stmt_bind_param($stmt_delete_opts, "i", $question_id);
            mysqli_stmt_execute($stmt_delete_opts);
            mysqli_stmt_close($stmt_delete_opts);

            // 3. Ré-insérer les nouvelles options
            $stmt_insert_opt = mysqli_prepare($connect, "INSERT INTO quiz_options (question_id, title, is_correct) VALUES (?, ?, ?)");
            $all_options_inserted = true;
            foreach ($options as $index => $title) {
                if (empty(trim($title))) continue; 
                $is_correct = ($index == $correct_option_index) ? 'Yes' : 'No';
                $option_title = $title;
                mysqli_stmt_bind_param($stmt_insert_opt, "iss", $question_id, $option_title, $is_correct);
                if (!mysqli_stmt_execute($stmt_insert_opt)) {
                    $all_options_inserted = false;
                }
            }
            mysqli_stmt_close($stmt_insert_opt);

            if ($all_options_inserted) {
                // REDIRECTION MISE À JOUR
                header("Location: quiz_questions.php?quiz_id=" . $quiz_id);
                exit;
            } else {
                $message = '<div class="alert alert-danger">Error updating options.</div>';
            }

        } else {
            $message = '<div class="alert alert-danger">Error updating the question.</div>';
        }
    }
}

// --- Logique d'Affichage (GET) ---
$stmt_get_q = mysqli_prepare($connect, "SELECT * FROM quiz_questions WHERE id = ? AND quiz_id = ?");
mysqli_stmt_bind_param($stmt_get_q, "ii", $question_id, $quiz_id);
mysqli_stmt_execute($stmt_get_q);
$result_q = mysqli_stmt_get_result($stmt_get_q);
$question_data = mysqli_fetch_assoc($result_q);
mysqli_stmt_close($stmt_get_q);

if (!$question_data) {
    include 'header.php';
    echo '<section class="content"><div class="alert alert-danger">Question not found or does not belong to this quiz.</div></section>';
    include 'footer.php';
    exit;
}

// Récupérer les options associées
$stmt_get_opts = mysqli_prepare($connect, "SELECT * FROM quiz_options WHERE question_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt_get_opts, "i", $question_id);
mysqli_stmt_execute($stmt_get_opts);
$result_opts = mysqli_stmt_get_result($stmt_get_opts);
while ($row = mysqli_fetch_assoc($result_opts)) {
    $options_data[] = $row;
}
mysqli_stmt_close($stmt_get_opts);

// 3. INCLURE LE HEADER HTML
include 'header.php'; 
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Question</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="quizzes.php">Quiz Manager</a></li>
                    <li class="breadcrumb-item"><a href="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">Questions</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Editing: <?php echo htmlspecialchars($question_data['question']); ?></h3>
                    </div>
                    
                    <?php echo $message; ?>

                    <form method="POST" action="edit_question.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                        <input type="hidden" name="edit_question" value="1">
                        
                        <div class="card-body">
                            <div class="form-group">
                                <label for="question">Question</label>
                                <input type="text" class="form-control" id="question" name="question" value="<?php echo htmlspecialchars($question_data['question']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Answer Options</label>
                                <div id="options-container"></div>
                                <button type="button" id="add-option-btn" class="btn btn-sm btn-success mt-2">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label for="explanation">Explanation (Shown after answering)</label>
                                <textarea id="summernote" name="explanation" class="form-control" style="height: 200px;"><?php echo htmlspecialchars($question_data['explanation']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="active">Status</label>
                                <select class="form-control" id="active" name="active">
                                    <option value="Yes" <?php if ($question_data['active'] == 'Yes') echo 'selected'; ?>>Published (Active)</option>
                                    <option value="No" <?php if ($question_data['active'] == 'No') echo 'selected'; ?>>Draft (Inactive)</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php'; 
?>

<script>
const existingOptions = <?php echo json_encode($options_data); ?>;

$(document).ready(function() {
    $('#summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    let optionIndex = 0; 

    function addOption(title = '', is_correct = false) {
        const isChecked = is_correct ? 'checked' : '';
        const optionHtml = `
            <div class="input-group mb-2" id="option-group-${optionIndex}">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <input type="radio" name="is_correct" value="${optionIndex}" required title="Mark as correct answer" ${isChecked}>
                    </span>
                </div>
                <input type="text" name="options[${optionIndex}]" class="form-control" placeholder="Answer text..." value="${escapeHTML(title)}" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-option-btn" data-index="${optionIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#options-container').append(optionHtml);
        optionIndex++;
    }

    function escapeHTML(str) {
        return str.replace(/[&<>"']/g, function(match) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
        });
    }

    $('#add-option-btn').click(function() { addOption('', false); });

    $(document).on('click', '.remove-option-btn', function() {
        const index = $(this).data('index');
        $('#option-group-' + index).remove();
    });

    if (existingOptions.length > 0) {
        existingOptions.forEach(opt => {
            addOption(opt.title, opt.is_correct === 'Yes');
        });
    } else {
        addOption('', false);
        addOption('', false);
    }
});
</script>