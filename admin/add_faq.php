<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $question = $_POST['question'];
    $answer   = $_POST['answer'];
    $active   = $_POST['active'];
    $order    = (int)$_POST['position_order'];

    if (empty($question)) {
        echo '<div class="alert alert-danger m-3">Question is required.</div>';
    } else {
        $stmt = mysqli_prepare($connect, "INSERT INTO faqs (question, answer, active, position_order) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssi", $question, $answer, $active, $order);
        
        if(mysqli_stmt_execute($stmt)) {
            echo '<div class="alert alert-success m-3">FAQ added successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=faq.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error adding FAQ.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Question</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="faq.php">FAQ</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Question & Answer</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="question" class="form-control form-control-lg" placeholder="e.g. How do I reset my password?" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Answer</label>
                                <textarea name="answer" id="summernote" class="form-control" rows="5" required></textarea>
                                <small class="text-muted">You can use formatting here.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="position_order" class="form-control" value="0">
                                <small class="text-muted">Lower numbers appear first.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Question
                            </button>
                            <a href="faq.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>