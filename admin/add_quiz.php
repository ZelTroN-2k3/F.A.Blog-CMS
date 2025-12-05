<?php
include_once '../core.php'; 
include 'header.php'; // Sécurité incluse dans header ou via le bloc précédent

$message = '';
if (isset($_POST['add_quiz'])) {
    validate_csrf_token();

    $title = $_POST['title'];
    $purifier = get_purifier();
    $description = $purifier->purify($_POST['description']);
    $difficulty = $_POST['difficulty'];
    $active = $_POST['active'];
    
    $image_url = ''; 

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $temp_file = $_FILES['image']['tmp_name'];
        
        // --- VOTRE BLOC DE VERIFICATION DOSSIER ---
        $upload_dir_full_path = __DIR__ . '/../uploads/quiz'; 
        if (!is_dir($upload_dir_full_path)) {
            if (!mkdir($upload_dir_full_path, 0755, true)) {
                $message = '<div class="alert alert-danger">Critical error: Unable to create the upload folder. Check permissions.</div>';
            }
        }

        if (empty($message)) {
            $output_file_base = 'uploads/quiz/quiz_' . time() . '_' . rand(100,999);
            $upload_dir = __DIR__ . '/../'; 
            
            // Votre fonction d'optimisation
            $optimized_image_path = optimize_and_save_image($temp_file, $upload_dir . $output_file_base);

            if ($optimized_image_path) {
                $image_url = str_replace($upload_dir, '', $optimized_image_path);
            } else {
                $message = '<div class="alert alert-danger">Error during image optimization.</div>';
            }
        } 
    }

    if (empty($message)) { 
        if (empty($title)) {
            $message = '<div class="alert alert-danger">Title cannot be empty.</div>';
        } else {
            $stmt_insert = mysqli_prepare($connect, "INSERT INTO quizzes (title, description, image, difficulty, active) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $title, $description, $image_url, $difficulty, $active);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                echo '<div class="alert alert-success m-3">Quiz created successfully! Redirecting...</div>';
                echo '<meta http-equiv="refresh" content="1; url=quizzes.php">';
                exit;
            } else {
                $message = '<div class="alert alert-danger">Error creating quiz.</div>';
            }
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Create New Quiz</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="quizzes.php">Quizzes</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
            
            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Quiz Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Quiz Title</label>
                                <input type="text" class="form-control form-control-lg" name="title" placeholder="Enter title" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea id="summernote" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Cover Image</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Choose file...</label>
                                </div>
                                <small class="text-muted">Image will be optimized automatically.</small>
                            </div>
                            <div class="mt-2 text-center" id="preview-container" style="display:none;">
                                <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="active">
                                    <option value="Yes" selected>Published</option>
                                    <option value="No">Draft</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Difficulty</label>
                                <select class="form-control" name="difficulty">
                                    <option value="FACILE">Easy</option>
                                    <option value="NORMAL" selected>Normal</option>
                                    <option value="DIFFICILE">Hard</option>
                                    <option value="EXPERT">Expert</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_quiz" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Quiz
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
    // Preview Image
    $('#image').on('change', function() {
        var fileName = $(this).val().split('\\\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#preview-container').slideDown();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>