<?php
include "header.php";

// Validation ID
if (!isset($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=polls.php">'; exit;
}
$id = (int)$_GET['id'];

// Fetch Poll
$q = mysqli_query($connect, "SELECT * FROM polls WHERE id=$id");
$poll = mysqli_fetch_assoc($q);
if(!$poll) { echo "Poll not found"; exit; }

// --- LOGIQUE : SUPPRESSION OPTION ---
if (isset($_GET['delete_option'])) {
    validate_csrf_token_get();
    $opt_id = (int)$_GET['delete_option'];
    
    // Vérifier appartenance
    $check = mysqli_query($connect, "SELECT id FROM poll_options WHERE id=$opt_id AND poll_id=$id");
    if(mysqli_num_rows($check) > 0){
        mysqli_query($connect, "DELETE FROM poll_options WHERE id=$opt_id");
    }
    echo '<meta http-equiv="refresh" content="0; url=edit_poll.php?id='.$id.'">';
    exit;
}

// --- LOGIQUE : MISE A JOUR ---
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $question = $_POST['question'];
    $active   = $_POST['active'];
    
    // 1. Update Poll
    $stmt = mysqli_prepare($connect, "UPDATE polls SET question=?, active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $question, $active, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 2. Update Existing Options
    if(isset($_POST['existing_options'])){
        $stmt_up = mysqli_prepare($connect, "UPDATE poll_options SET title=? WHERE id=?");
        foreach($_POST['existing_options'] as $oid => $oval){
            $oval = trim($oval);
            if(!empty($oval)){
                mysqli_stmt_bind_param($stmt_up, "si", $oval, $oid);
                mysqli_stmt_execute($stmt_up);
            }
        }
        mysqli_stmt_close($stmt_up);
    }

    // 3. Insert New Options
    if(isset($_POST['new_options'])){
        $stmt_new = mysqli_prepare($connect, "INSERT INTO poll_options (poll_id, title, votes) VALUES (?, ?, 0)");
        foreach($_POST['new_options'] as $nval){
            $nval = trim($nval);
            if(!empty($nval)){
                mysqli_stmt_bind_param($stmt_new, "is", $id, $nval);
                mysqli_stmt_execute($stmt_new);
            }
        }
        mysqli_stmt_close($stmt_new);
    }

    echo '<div class="alert alert-success m-3">Poll updated! Redirecting...</div>';
    echo '<meta http-equiv="refresh" content="1; url=polls.php">';
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Edit Poll</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="polls.php">Polls</a></li>
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
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Question & Options</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="question" class="form-control form-control-lg" value="<?php echo htmlspecialchars($poll['question']); ?>" required>
                            </div>
                            
                            <hr>
                            
                            <label>Existing Options</label>
                            <?php
                            $q_opt = mysqli_query($connect, "SELECT * FROM poll_options WHERE poll_id=$id ORDER BY id ASC");
                            while($opt = mysqli_fetch_assoc($q_opt)){
                                echo '
                                <div class="input-group mb-2">
                                    <input type="text" name="existing_options['.$opt['id'].']" class="form-control" value="'.htmlspecialchars($opt['title']).'" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light">Votes: '.$opt['votes'].'</span>
                                        <a href="?id='.$id.'&delete_option='.$opt['id'].'&token='.$csrf_token.'" class="btn btn-danger" onclick="return confirm(\'Delete this option?\')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>';
                            }
                            ?>

                            <label class="mt-3">Add New Options</label>
                            <div id="new-options-container"></div>
                            
                            <button type="button" class="btn btn-success btn-sm mt-2" onclick="addNewOption()">
                                <i class="fas fa-plus"></i> Add New Option field
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option value="Yes" <?php if($poll['active']=='Yes') echo 'selected'; ?>>Active</option>
                                    <option value="No" <?php if($poll['active']=='No') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Note: Deleting options does not remove the votes from the global count immediately, but removes the choice for future voters.
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Poll
                            </button>
                            <a href="polls.php" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
function addNewOption() {
    var html = `
    <div class="input-group mb-2">
        <div class="input-group-prepend">
            <span class="input-group-text text-success"><i class="fas fa-plus"></i></span>
        </div>
        <input type="text" name="new_options[]" class="form-control" placeholder="New Option" required>
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" onclick="this.closest('.input-group').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>`;
    $('#new-options-container').append(html);
}
</script>