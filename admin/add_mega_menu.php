<?php
include "header.php";

if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    // Récupération des données
    $name = $_POST['name'];
    $trigger_text = $_POST['trigger_text'];
    $trigger_icon = $_POST['trigger_icon'];
    $trigger_link = $_POST['trigger_link'];
    $active = $_POST['active'];
    $order = (int)$_POST['position_order'];
    
    // Colonne 1
    $col1_title = $_POST['col_1_title'];
    $col1_content = $_POST['col_1_content']; // HTML (Summernote)
    
    // Colonne 2
    $col2_title = $_POST['col_2_title'];
    $col2_type = $_POST['col_2_type'];
    $col2_content = $_POST['col_2_content'];
    
    // Colonne 3
    $col3_title = $_POST['col_3_title'];
    $col3_type = $_POST['col_3_type'];
    $col3_content = $_POST['col_3_content'];
    
    $stmt = mysqli_prepare($connect, "INSERT INTO mega_menus 
    (name, trigger_text, trigger_icon, trigger_link, active, position_order,
     col_1_title, col_1_content,
     col_2_title, col_2_type, col_2_content,
     col_3_title, col_3_type, col_3_content) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    mysqli_stmt_bind_param($stmt, "sssssissssssss", 
        $name, $trigger_text, $trigger_icon, $trigger_link, $active, $order,
        $col1_title, $col1_content,
        $col2_title, $col2_type, $col2_content,
        $col3_title, $col3_type, $col3_content
    );
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<meta http-equiv="refresh" content="0; url=mega_menus.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger">Error: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><i class="fas fa-plus-square"></i> Add New Mega Menu</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Main Settings</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Internal Name</label>
                                    <input type="text" name="name" class="form-control" required placeholder="ex: Blog Menu">
                                </div>
                                <div class="col-md-4">
                                    <label>Menu Label (Public)</label>
                                    <input type="text" name="trigger_text" class="form-control" required placeholder="ex: Blog">
                                </div>
                                <div class="col-md-4">
                                    <label>Icon (FontAwesome)</label>
                                    <input type="text" name="trigger_icon" class="form-control" value="fa-bars">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>Main Link</label>
                                    <input type="text" name="trigger_link" class="form-control" value="#">
                                </div>
                                <div class="col-md-2">
                                    <label>Order</label>
                                    <input type="number" name="position_order" class="form-control" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label>Active</label>
                                    <select name="active" class="form-control">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Column 1 (Left)</h3></div>
                        <div class="card-body">
                            <label>Title</label>
                            <input type="text" name="col_1_title" class="form-control mb-2" value="Explore">
                            <label>Content (Custom HTML)</label>
                            <textarea name="col_1_content" class="summernote"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-warning">
                        <div class="card-header"><h3 class="card-title">Column 2 (Center)</h3></div>
                        <div class="card-body">
                            <label>Title</label>
                            <input type="text" name="col_2_title" class="form-control mb-2" value="Categories">
                            <label>Content Type</label>
                            <select name="col_2_type" class="form-control mb-2" onchange="toggleEditor(this, '#col2_edit')">
                                <option value="categories">Auto: Categories List</option>
                                <option value="custom">Custom HTML</option>
                                <option value="none">Hide Column</option>
                            </select>
                            <div id="col2_edit" style="display:none;">
                                <label>Custom Content</label>
                                <textarea name="col_2_content" class="summernote"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title">Column 3 (Right)</h3></div>
                        <div class="card-body">
                            <label>Title</label>
                            <input type="text" name="col_3_title" class="form-control mb-2" value="Newest">
                            <label>Content Type</label>
                            <select name="col_3_type" class="form-control mb-2" onchange="toggleEditor(this, '#col3_edit')">
                                <option value="latest_posts">Auto: Latest Posts (w/ Images)</option>
                                <option value="custom">Custom HTML</option>
                                <option value="none">Hide Column</option>
                            </select>
                            <div id="col3_edit" style="display:none;">
                                <label>Custom Content</label>
                                <textarea name="col_3_content" class="summernote"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 mb-4">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block">Create Mega Menu</button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('.summernote').summernote({ height: 150 });
});

function toggleEditor(select, targetId) {
    if(select.value == 'custom') {
        $(targetId).slideDown();
    } else {
        $(targetId).slideUp();
    }
}
</script>