<?php
include_once '../core.php'; 
include 'header.php';

if (!isset($_GET['id']) && !isset($_POST['quiz_id'])) {
    echo '<meta http-equiv="refresh" content="0; url=quizzes.php">'; exit;
}
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['quiz_id'];
$message = '';

// --- Traitement Formulaire ---
if (isset($_POST['edit_quiz'])) {
    validate_csrf_token();

    $title = $_POST['title'];
    $purifier = get_purifier();
    $description = $purifier->purify($_POST['description']);
    $difficulty = $_POST['difficulty'];
    $active = $_POST['active'];
    $image_url = $_POST['current_image']; 

    // Gestion Upload Nouvelle Image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir_full_path = __DIR__ . '/../uploads/quiz';
        if (!is_dir($upload_dir_full_path)) {
            if (!mkdir($upload_dir_full_path, 0755, true)) {
                $message = '<div class="alert alert-danger">Error: Unable to create folder.</div>';
            }
        }

        if (empty($message)) {
            $temp_file = $_FILES['image']['tmp_name'];
            $output_file_base = 'uploads/quiz/quiz_' . $quiz_id . '_' . time(); 
            $upload_dir = __DIR__ . '/../'; 
            
            $optimized_image_path = optimize_and_save_image($temp_file, $upload_dir . $output_file_base);

            if ($optimized_image_path) {
                $image_url = str_replace($upload_dir, '', $optimized_image_path);
            } else {
                $message = '<div class="alert alert-danger">Error optimizing image.</div>';
            }
        } 
    }

    if (empty($message)) {
        if (empty($title)) {
            $message = '<div class="alert alert-danger">Title required.</div>';
        } else {
            $stmt_update = mysqli_prepare($connect, "UPDATE quizzes SET title = ?, description = ?, image = ?, difficulty = ?, active = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_update, "sssssi", $title, $description, $image_url, $difficulty, $active, $quiz_id);
            
            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                echo '<div class="alert alert-success m-3">Quiz updated! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="1; url=quizzes.php">';
                exit;
            } else {
                $message = '<div class="alert alert-danger">Error updating quiz.</div>';
            }
        }
    }
}

// --- Récupération des infos ---
$stmt_get = mysqli_prepare($connect, "SELECT * FROM quizzes WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $quiz_id);
mysqli_stmt_execute($stmt_get);
$quiz_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get));
mysqli_stmt_close($stmt_get);

if (!$quiz_data) {
    echo '<div class="alert alert-danger m-3">Quiz not found.</div>';
    include 'footer.php'; exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Quiz</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="quizzes.php">Quizzes</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php echo $message; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($quiz_data['image']); ?>">

            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control form-control-lg" name="title" value="<?php echo htmlspecialchars($quiz_data['title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea id="summernote" name="description" class="form-control"><?php echo html_entity_decode($quiz_data['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Image</h3>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($quiz_data['image'])): ?>
                                <div class="text-center mb-3">
                                    <img src="../<?php echo htmlspecialchars($quiz_data['image']); ?>" class="img-fluid rounded" style="max-height: 150px;">
                                    <br><small class="text-muted">Current Image</small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Replace Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image">
                                    <label class="custom-file-label" for="image">Choose file...</label>
                                </div>
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
                                    <option value="Yes" <?php if ($quiz_data['active'] == 'Yes') echo 'selected'; ?>>Published</option>
                                    <option value="No" <?php if ($quiz_data['active'] == 'No') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="d-block">Difficulty</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <?php $diff = $quiz_data['difficulty']; ?>
                                    
                                    <label class="btn btn-outline-success <?php echo ($diff == 'FACILE') ? 'active' : ''; ?>">
                                        <input type="radio" name="difficulty" value="FACILE" autocomplete="off" <?php echo ($diff == 'FACILE') ? 'checked' : ''; ?>> Easy
                                    </label>
                                    
                                    <label class="btn btn-outline-info <?php echo ($diff == 'NORMAL') ? 'active' : ''; ?>">
                                        <input type="radio" name="difficulty" value="NORMAL" autocomplete="off" <?php echo ($diff == 'NORMAL') ? 'checked' : ''; ?>> Normal
                                    </label>
                                    
                                    <label class="btn btn-outline-warning <?php echo ($diff == 'DIFFICILE') ? 'active' : ''; ?>">
                                        <input type="radio" name="difficulty" value="DIFFICILE" autocomplete="off" <?php echo ($diff == 'DIFFICILE') ? 'checked' : ''; ?>> Hard
                                    </label>
                                    
                                    <label class="btn btn-outline-danger <?php echo ($diff == 'EXPERT') ? 'active' : ''; ?>">
                                        <input type="radio" name="difficulty" value="EXPERT" autocomplete="off" <?php echo ($diff == 'EXPERT') ? 'checked' : ''; ?>> Expert
                                    </label>
                                </div>
                            </div>
                            </div>
                        <div class="card-footer">
                            <button type="submit" name="edit_quiz" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Quiz
                            </button>
                            <a href="quizzes.php" class="btn btn-default btn-block">Cancel</a>
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
    $('#image').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>