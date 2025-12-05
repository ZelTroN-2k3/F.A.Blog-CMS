<?php
include_once '../core.php'; 
include 'header.php';

if (!isset($_GET['quiz_id']) && !isset($_POST['quiz_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=quizzes.php">'; exit;
}
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : (int)$_POST['quiz_id'];
$message = '';

if (isset($_POST['add_question'])) {
    validate_csrf_token(); 

    $question = $_POST['question'];
    $purifier = get_purifier();
    $explanation = $purifier->purify($_POST['explanation']); 
    $active = $_POST['active'];
    $options = $_POST['options'] ?? [];
    $correct_option_index = $_POST['is_correct'] ?? -1; 

    if (empty($question)) {
        $message = '<div class="alert alert-danger">Question cannot be empty.</div>';
    } elseif (count($options) < 2) {
        $message = '<div class="alert alert-danger">At least 2 options required.</div>';
    } elseif ($correct_option_index == -1) {
        $message = '<div class="alert alert-danger">Mark one answer as correct.</div>';
    } else {
        // Insertion Question (Sans 'Points' car absent de votre BDD originale)
        $stmt_question = mysqli_prepare($connect, "INSERT INTO quiz_questions (quiz_id, question, explanation, active, position_order) VALUES (?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt_question, "isss", $quiz_id, $question, $explanation, $active);
        
        if (mysqli_stmt_execute($stmt_question)) {
            $question_id = mysqli_insert_id($connect); 
            mysqli_stmt_close($stmt_question);

            // Insertion Options
            $stmt_option = mysqli_prepare($connect, "INSERT INTO quiz_options (question_id, title, is_correct) VALUES (?, ?, ?)");
            foreach ($options as $index => $title) {
                if (empty(trim($title))) continue; 
                $is_correct = ($index == $correct_option_index) ? 'Yes' : 'No';
                mysqli_stmt_bind_param($stmt_option, "iss", $question_id, $title, $is_correct);
                mysqli_stmt_execute($stmt_option);
            }
            mysqli_stmt_close($stmt_option);

            echo '<div class="alert alert-success m-3">Question added! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=quiz_questions.php?quiz_id='.$quiz_id.'">';
            exit;
        } else {
            $message = '<div class="alert alert-danger">Error inserting question.</div>';
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus"></i> Add Question</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">Questions</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $message; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="add_question" value="1">

            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Question & Explanation</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question Text</label>
                                <input type="text" class="form-control form-control-lg" name="question" placeholder="Enter question..." required>
                            </div>
                            
                            <div class="form-group">
                                <label>Explanation (Optional)</label>
                                <textarea id="summernote" name="explanation" class="form-control" style="height: 150px;"></textarea>
                                <small class="text-muted">Shown after the user answers.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Answers</h3>
                            <button type="button" id="add-option-btn" class="btn btn-success btn-sm ml-auto"><i class="fas fa-plus"></i> Add Option</button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info"><i class="fas fa-check-circle"></i> Select the radio button for the correct answer.</div>
                            <div id="options-container">
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="active">
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Question
                            </button>
                            <a href="quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    let optionIndex = 0;
    function addOption() {
        const optionHtml = `
            <div class="input-group mb-2" id="option-group-${optionIndex}">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <input type="radio" name="is_correct" value="${optionIndex}" required>
                    </div>
                </div>
                <input type="text" name="options[${optionIndex}]" class="form-control" placeholder="Answer text..." required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-option-btn" data-index="${optionIndex}"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
        $('#options-container').append(optionHtml);
        optionIndex++;
    }
    $('#add-option-btn').click(addOption);
    $(document).on('click', '.remove-option-btn', function() {
        $('#option-group-' + $(this).data('index')).remove();
    });

    addOption(); addOption(); // 2 par défaut
    $('input[name="is_correct"][value="0"]').prop('checked', true);
});
</script>