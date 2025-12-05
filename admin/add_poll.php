<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $question = $_POST['question'];
    $active   = $_POST['active'];
    $options  = $_POST['options']; // Tableau des options
    
    if (empty($question)) {
        echo '<div class="alert alert-danger m-3">Question is required.</div>';
    } else {
        // 1. Créer le sondage
        $stmt = mysqli_prepare($connect, "INSERT INTO polls (question, active, created_at) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "ss", $question, $active);
        
        if(mysqli_stmt_execute($stmt)) {
            $poll_id = mysqli_insert_id($connect);
            mysqli_stmt_close($stmt);
            
            // 2. Insérer les options
            if(!empty($options)){
                $stmt_opt = mysqli_prepare($connect, "INSERT INTO poll_options (poll_id, title, votes) VALUES (?, ?, 0)");
                foreach($options as $opt_text){
                    $opt_text = trim($opt_text);
                    if(!empty($opt_text)){
                        mysqli_stmt_bind_param($stmt_opt, "is", $poll_id, $opt_text);
                        mysqli_stmt_execute($stmt_opt);
                    }
                }
                mysqli_stmt_close($stmt_opt);
            }
            
            echo '<div class="alert alert-success m-3">Poll created successfully! Redirecting...</div>';
            echo '<meta http-equiv="refresh" content="1; url=polls.php">';
            exit;
        } else {
            echo '<div class="alert alert-danger m-3">Error creating poll.</div>';
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Poll</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="polls.php">Polls</a></li>
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
                <div class="col-lg-8 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Poll Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Question</label>
                                <input type="text" name="question" class="form-control form-control-lg" placeholder="e.g. What is your favorite color?" required>
                            </div>
                            
                            <hr>
                            
                            <label>Options</label>
                            <div id="options-container">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">1</span>
                                    </div>
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">2</span>
                                    </div>
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-sm mt-2" onclick="addOption()">
                                <i class="fas fa-plus"></i> Add Option
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
                                    <option value="Yes" selected>Active</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tip: Once created, you can add this poll to your sidebar via the <strong>Widgets</strong> manager.
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Save Poll
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
function addOption() {
    var container = document.getElementById("options-container");
    var count = container.getElementsByClassName("input-group").length + 1;
    
    var html = `
    <div class="input-group mb-2">
        <div class="input-group-prepend">
            <span class="input-group-text">${count}</span>
        </div>
        <input type="text" name="options[]" class="form-control" placeholder="Option ${count}" required>
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="this.closest('.input-group').remove(); reorderIndices();">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>`;
    
    $(container).append(html);
}

function reorderIndices() {
    $('#options-container .input-group').each(function(index) {
        $(this).find('.input-group-text').text(index + 1);
        $(this).find('input').attr('placeholder', 'Option ' + (index + 1));
    });
}
</script>