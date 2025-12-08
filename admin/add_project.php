<?php
include "header.php";

if (isset($_POST['add_project'])) {
    validate_csrf_token();
    
    // Basics
    $title = $_POST['title'];
    $slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug'], 0) : generateSeoURL($title, 0);
    $pitch = $_POST['pitch'];
    $difficulty = $_POST['difficulty'];
    $duration = $_POST['duration'];
    $active = $_POST['active'];
    $featured = $_POST['featured'];
    $project_category_id = (int)$_POST['project_category_id'];
    $author_id = $user['id'];
    
    // Team & Story
    $team = $_POST['team_credits'];
    $story = $_POST['story'];
    
    // Things (JSON)
    $hardware = isset($_POST['hardware']) ? json_encode($_POST['hardware']) : '[]';
    $software = isset($_POST['software']) ? json_encode($_POST['software']) : '[]';
    $tools    = isset($_POST['tools']) ? json_encode($_POST['tools']) : '[]';
    
    // Attachments
    $schematics = $_POST['schematics_link'];
    $code = $_POST['code_link'];
    $files = $_POST['files_link'];

    // --- SHOP (NOUVEAU) ---
    $is_product = $_POST['is_product'];
    $price = !empty($_POST['price']) ? $_POST['price'] : 0.00;
    $stock_status = $_POST['stock_status'];
    $buy_link = $_POST['buy_link'];
    
    // Image
    $image = isset($_POST['selected_image']) ? $_POST['selected_image'] : '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/projects/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $new_name = "project_" . uniqid() . "." . $ext;
            if (function_exists('optimize_and_save_image')) {
                $opt = optimize_and_save_image($_FILES["image"]["tmp_name"], $target_dir . "project_" . uniqid());
                if ($opt) $image = str_replace("../", "", $opt);
            } else {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_name)){
                    $image = "uploads/projects/" . $new_name;
                }
            }
        }
    }

    // INSERTION (Mise à jour avec les nouveaux champs Shop)
    // Structure: 19 champs String/Int/Decimal
    $stmt = mysqli_prepare($connect, "INSERT INTO projects (
        author_id, project_category_id, title, slug, pitch, image, 
        difficulty, duration, team_credits, hardware_parts, software_apps, hand_tools, 
        story, schematics_link, code_link, files_link, 
        active, featured, is_product, price, stock_status, buy_link, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    // Types: i (int), d (double/decimal), s (string)
    // author(i), cat(i), price(d), le reste(s)
    // Total La chaîne de types contient exactement 22 caractères (2 int, 17 string, 1 double, 2 string)
    mysqli_stmt_bind_param($stmt, "iisssssssssssssssssdss", 
        $author_id, $project_category_id, $title, $slug, $pitch, $image, 
        $difficulty, $duration, $team, $hardware, $software, $tools, 
        $story, $schematics, $code, $files, 
        $active, $featured, $is_product, $price, $stock_status, $buy_link
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo '<div class="alert alert-success m-3">Project created successfully!</div>';
        echo '<meta http-equiv="refresh" content="1; url=projects.php">';
        exit;
    } else {
        echo '<div class="alert alert-danger m-3">Error: ' . mysqli_error($connect) . '</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> Add Project</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="projects.php">Projects</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="project-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-basics">1. Basics</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-team">2. Team</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-things">3. Things</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-story">4. Story</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-att">5. Attachments</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-shop"><i class="fas fa-shopping-cart"></i> 6. Shop</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        
                        <div class="tab-pane fade show active" id="tab-basics">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Project Title</label>
                                        <input type="text" name="title" class="form-control form-control-lg" required placeholder="Ex: Smart Mirror v2">
                                    </div>
                                    <div class="form-group">
                                        <label>Pitch (Short Description)</label>
                                        <textarea name="pitch" class="form-control" rows="2" placeholder="Summary for cards..."></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Difficulty</label>
                                            <select name="difficulty" class="form-control">
                                                <option value="Easy">Easy</option>
                                                <option value="Intermediate" selected>Intermediate</option>
                                                <option value="Advanced">Advanced</option>
                                                <option value="Expert">Expert</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Duration</label>
                                            <input type="text" name="duration" class="form-control" placeholder="Ex: 4 hours">
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label>Category</label>
                                        <select name="project_category_id" class="form-control">
                                            <option value="0">Uncategorized</option>
                                            <?php
                                            $qc = mysqli_query($connect, "SELECT * FROM project_categories ORDER BY category ASC");
                                            while($rc = mysqli_fetch_assoc($qc)){ echo '<option value="'.$rc['id'].'">'.htmlspecialchars($rc['category']).'</option>'; }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Slug (URL) - Optional</label>
                                        <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Cover Image</label>
                                    <div class="mb-2 text-center">
                                        <img src="../assets/img/project-no-image.png" id="preview_image_box" class="img-fluid rounded border" style="max-height:150px;">
                                    </div>
                                    <div class="custom-file text-left mb-2">
                                        <input type="file" name="image" class="custom-file-input" id="postImage">
                                        <label class="custom-file-label" for="postImage">Upload File</label>
                                    </div>
                                    <div class="text-center text-muted mb-2 small">- OR -</div>
                                    <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">Select from Library</button>
                                    <input type="hidden" name="selected_image" id="selected_image_input">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-team">
                            <div class="form-group">
                                <label>Contributors</label>
                                <textarea name="team_credits" id="summernote_team" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-things">
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header"><h5 class="card-title">Hardware</h5><button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('hardware-container', 'hardware')">+ Add</button></div>
                                <div class="card-body bg-light" id="hardware-container"></div>
                            </div>
                            <div class="card card-outline card-info mb-3">
                                <div class="card-header"><h5 class="card-title">Software</h5><button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('software-container', 'software')">+ Add</button></div>
                                <div class="card-body bg-light" id="software-container"></div>
                            </div>
                            <div class="card card-outline card-warning mb-3">
                                <div class="card-header"><h5 class="card-title">Tools</h5><button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('tools-container', 'tools')">+ Add</button></div>
                                <div class="card-body bg-light" id="tools-container"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-story">
                            <div class="form-group">
                                <label>The Full Story</label>
                                <textarea name="story" id="summernote" class="form-control" style="height: 400px;"></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-att">
                            <div class="form-group"><label>Schematics URL</label><input type="url" name="schematics_link" class="form-control"></div>
                            <div class="form-group"><label>Code / GitHub URL</label><input type="url" name="code_link" class="form-control"></div>
                            <div class="form-group"><label>Other Files URL</label><input type="url" name="files_link" class="form-control"></div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Publication Status</label>
                                        <select name="active" class="form-control custom-select">
                                            <option value="Draft">Draft</option>
                                            <option value="Yes">Public</option>
                                            <option value="No">Private</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Featured Project?</label>
                                        <select name="featured" class="form-control custom-select">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes (Show in Slider)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-shop">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-tags"></i> Sell this project</h5>
                                <p>Turn this project into a product. You can sell a kit, the 3D files, or the finished product.</p>
                            </div>
                            
                            <div class="form-group">
                                <label>Is this project for sale?</label>
                                <select name="is_product" class="form-control custom-select" id="isProductSelect">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes, enable shop features</option>
                                </select>
                            </div>

                            <div id="shopOptions" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Price ($)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Stock Status</label>
                                            <select name="stock_status" class="form-control">
                                                <option value="In Stock">In Stock</option>
                                                <option value="Low Stock">Low Stock</option>
                                                <option value="Out of Stock">Out of Stock</option>
                                                <option value="Pre-order">Pre-order</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Payment Link (PayPal, Stripe, etc.)</label>
                                    <input type="url" name="buy_link" class="form-control" placeholder="https://paypal.me/...">
                                    <small class="text-muted">Users will be redirected here when they click "Buy Now".</small>
                                </div>
                            </div>
                            
                            <hr>
                            <button type="submit" name="add_project" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Save Project</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="filesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Select Image</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
      <div class="modal-body" id="files-gallery-content">Loading...</div>
    </div>
  </div>
</div>

<script src="js/bom_manager.js"></script>
<script>
$(document).ready(function() {
    $('#summernote').summernote({height: 300});
    $('#summernote_team').summernote({height: 150, toolbar: [['style',['bold','italic','link']]]});

    $('#filesModal').on('show.bs.modal', function() {
        if($('#files-gallery-content').html().indexOf('Loading') !== -1) {
            $.get('ajax_load_files.php', function(data) { $('#files-gallery-content').html(data); });
        }
    });

    $("#postImage").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        $('#selected_image_input').val('');
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#preview_image_box').attr('src', e.target.result); }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Afficher/Cacher options Shop
    $('#isProductSelect').change(function() {
        if($(this).val() == 'Yes') { $('#shopOptions').slideDown(); } else { $('#shopOptions').slideUp(); }
    });
});

function selectFile(dbValue, fullPath) {
    $('#selected_image_input').val(dbValue);
    $('#preview_image_box').attr('src', fullPath);
    $('#filesModal').modal('hide');
}
</script>
<?php include "footer.php"; ?>