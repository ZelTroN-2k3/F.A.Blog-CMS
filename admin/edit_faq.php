<?php
include "header.php";

// Validation ID
if (!isset($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=faq.php">'; exit;
}
$id = (int)$_GET['id'];

// Fetch info
$stmt = mysqli_prepare($connect, "SELECT * FROM faqs WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="alert alert-danger m-3">Question not found.</div>';
    include "footer.php"; exit;
}

// Traitement
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $question = $_POST['question'];
    $answer   = $_POST['answer'];
    $active   = $_POST['active'];
    $order    = (int)$_POST['position_order'];

    if (empty($question)) {
        echo '<div class="alert alert-danger m-3">Question cannot be empty.</div>';
    } else {
        $stmt_up = mysqli_prepare($connect, "UPDATE faqs SET question=?, answer=?, active=?, position_order=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_up, "sssii", $question, $answer, $active, $order, $id);
        
        if(mysqli_stmt_execute($stmt_up)) {
            echo '<div class="alert alert-success m-3">FAQ updated successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=faq.php">';
            exit;
        }
        mysqli_stmt_close($stmt_up);
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Question</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="faq.php">FAQ</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                            <h3 class="card-title">Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="question" class="form-control form-control-lg" value="<?php echo htmlspecialchars($row['question']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Answer</label>
                                <textarea name="answer" id="summernote" class="form-control" rows="5" required><?php echo html_entity_decode($row['answer']); ?></textarea>
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
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($row['active']=='Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($row['active']=='No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="position_order" class="form-control" value="<?php echo (int)$row['position_order']; ?>">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Question
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