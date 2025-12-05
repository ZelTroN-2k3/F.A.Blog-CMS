<?php
include "header.php";

// 1. Vérification de l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=mega_menus.php">';
    exit;
}
$id = (int)$_GET['id'];

// 2. Traitement du Formulaire (UPDATE)
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    // Récupération des champs
    $name = $_POST['name'];
    $trigger_text = $_POST['trigger_text'];
    $trigger_icon = $_POST['trigger_icon'];
    $trigger_link = $_POST['trigger_link'];
    $active = $_POST['active'];
    $order = (int)$_POST['position_order'];
    
    // Colonnes
    $col1_title = $_POST['col_1_title'];
    $col1_content = $_POST['col_1_content'];
    
    $col2_title = $_POST['col_2_title'];
    $col2_type = $_POST['col_2_type'];
    $col2_content = $_POST['col_2_content'];
    
    $col3_title = $_POST['col_3_title'];
    $col3_type = $_POST['col_3_type'];
    $col3_content = $_POST['col_3_content'];
    
    // Requête UPDATE
    $stmt = mysqli_prepare($connect, "UPDATE mega_menus SET 
        name=?, trigger_text=?, trigger_icon=?, trigger_link=?, active=?, position_order=?,
        col_1_title=?, col_1_content=?,
        col_2_title=?, col_2_type=?, col_2_content=?,
        col_3_title=?, col_3_type=?, col_3_content=?
        WHERE id=?");
        
    mysqli_stmt_bind_param($stmt, "sssssissssssssi", 
        $name, $trigger_text, $trigger_icon, $trigger_link, $active, $order,
        $col1_title, $col1_content,
        $col2_title, $col2_type, $col2_content,
        $col3_title, $col3_type, $col3_content,
        $id
    );
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<meta http-equiv="refresh" content="0; url=mega_menus.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger">Error updating menu: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}

// 3. Récupération des données actuelles
$stmt_get = mysqli_prepare($connect, "SELECT * FROM mega_menus WHERE id=?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$res = mysqli_stmt_get_result($stmt_get);
$menu = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt_get);

if(!$menu) {
    echo '<div class="alert alert-warning">Menu not found.</div>';
    include "footer.php";
    exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><i class="fas fa-edit"></i> Edit Mega Menu: <?php echo htmlspecialchars($menu['name']); ?></h1>
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
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($menu['name']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label>Menu Label (Public)</label>
                                    <input type="text" name="trigger_text" class="form-control" required value="<?php echo htmlspecialchars($menu['trigger_text']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label>Icon (FontAwesome)</label>
                                    <input type="text" name="trigger_icon" class="form-control" value="<?php echo htmlspecialchars($menu['trigger_icon']); ?>">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>Main Link</label>
                                    <input type="text" name="trigger_link" class="form-control" value="<?php echo htmlspecialchars($menu['trigger_link']); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label>Order</label>
                                    <input type="number" name="position_order" class="form-control" value="<?php echo (int)$menu['position_order']; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label>Active</label>
                                    <select name="active" class="form-control">
                                        <option value="Yes" <?php if($menu['active']=='Yes') echo 'selected'; ?>>Yes</option>
                                        <option value="No" <?php if($menu['active']=='No') echo 'selected'; ?>>No</option>
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
                            <input type="text" name="col_1_title" class="form-control mb-2" value="<?php echo htmlspecialchars($menu['col_1_title']); ?>">
                            <label>Content (Custom HTML)</label>
                            <textarea name="col_1_content" class="summernote"><?php echo htmlspecialchars($menu['col_1_content']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-warning">
                        <div class="card-header"><h3 class="card-title">Column 2 (Center)</h3></div>
                        <div class="card-body">
                            <label>Title</label>
                            <input type="text" name="col_2_title" class="form-control mb-2" value="<?php echo htmlspecialchars($menu['col_2_title']); ?>">
                            <label>Content Type</label>
                            <select name="col_2_type" class="form-control mb-2" onchange="toggleEditor(this, '#col2_edit')">
                                <option value="categories" <?php if($menu['col_2_type']=='categories') echo 'selected'; ?>>Auto: Categories List</option>
                                <option value="custom" <?php if($menu['col_2_type']=='custom') echo 'selected'; ?>>Custom HTML</option>
                                <option value="none" <?php if($menu['col_2_type']=='none') echo 'selected'; ?>>Hide Column</option>
                            </select>
                            <div id="col2_edit" style="<?php echo ($menu['col_2_type']=='custom' ? '' : 'display:none;'); ?>">
                                <label>Custom Content</label>
                                <textarea name="col_2_content" class="summernote"><?php echo htmlspecialchars($menu['col_2_content']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title">Column 3 (Right)</h3></div>
                        <div class="card-body">
                            <label>Title</label>
                            <input type="text" name="col_3_title" class="form-control mb-2" value="<?php echo htmlspecialchars($menu['col_3_title']); ?>">
                            <label>Content Type</label>
                            <select name="col_3_type" class="form-control mb-2" onchange="toggleEditor(this, '#col3_edit')">
                                <option value="latest_posts" <?php if($menu['col_3_type']=='latest_posts') echo 'selected'; ?>>Auto: Latest Posts (w/ Images)</option>
                                <option value="custom" <?php if($menu['col_3_type']=='custom') echo 'selected'; ?>>Custom HTML</option>
                                <option value="none" <?php if($menu['col_3_type']=='none') echo 'selected'; ?>>Hide Column</option>
                            </select>
                            <div id="col3_edit" style="<?php echo ($menu['col_3_type']=='custom' ? '' : 'display:none;'); ?>">
                                <label>Custom Content</label>
                                <textarea name="col_3_content" class="summernote"><?php echo htmlspecialchars($menu['col_3_content']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 mb-4">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block">Update Mega Menu</button>
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