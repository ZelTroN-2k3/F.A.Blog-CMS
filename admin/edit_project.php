<?php
include "header.php";

// 1. Vérification ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=projects.php">'; exit;
}
$id = (int)$_GET['id'];

// 2. Récupération des données
$stmt = mysqli_prepare($connect, "SELECT * FROM `projects` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<div class="alert alert-danger m-3">Project not found.</div>'; include "footer.php"; exit;
}

// --- SÉCURITÉ : Vérification Auteur/Admin ---
if ($user['role'] != 'Admin' && $row['author_id'] != $user['id']) {
    echo '<div class="alert alert-danger m-3">Access Denied. You can only edit your own projects.</div>';
    include "footer.php"; exit;
}

// 3. Traitement du Formulaire (UPDATE)
if (isset($_POST['edit_project'])) {
    validate_csrf_token();
    
    // 1. Basics
    $title = $_POST['title'];
    // Si le slug est modifié manuellement, on le prend, sinon on garde l'ancien (ou on régénère si vide)
    $slug = !empty($_POST['slug']) ? generateSeoURL($_POST['slug'], 0) : $row['slug'];
    
    $pitch = $_POST['pitch'];
    $difficulty = $_POST['difficulty'];
    $duration = $_POST['duration'];
    $active = $_POST['active'];
    $cat_id = (int)$_POST['project_category_id'];

    // 2. Team
    $team = $_POST['team_credits'];
    
    // 3. Things
    $hardware = $_POST['hardware_parts'];
    $software = $_POST['software_apps'];
    
    // 4. Story
    $story = $_POST['story'];
    
    // 5. Attachments
    $schematics = $_POST['schematics_link'];
    $code = $_POST['code_link'];
    
    // Image Cover (Gestion identique à edit_post)
    $image = $row['image']; // Par défaut, l'ancienne
    if (!empty($_POST['selected_image'])) { $image = $_POST['selected_image']; } // Bibliothèque
    
    // Upload Manuel
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/projects/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_name = "project_" . uniqid() . "." . $ext;
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (function_exists('optimize_and_save_image')) {
                $optimized_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $target_dir . "project_" . uniqid());
                if ($optimized_path) {
                    $image = str_replace("../", "", $optimized_path);
                }
            } else {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_name)){
                    $image = "uploads/projects/" . $new_name;
                }
            }
        }
    }

    // Requête UPDATE
    $stmt = mysqli_prepare($connect, "UPDATE projects SET project_category_id=?, title=?, slug=?, pitch=?, image=?, difficulty=?, duration=?, team_credits=?, hardware_parts=?, software_apps=?, story=?, schematics_link=?, code_link=?, active=? WHERE id=?");
    
    mysqli_stmt_bind_param($stmt, "isssssssssssssi", $cat_id, $title, $slug, $pitch, $image, $difficulty, $duration, $team, $hardware, $software, $story, $schematics, $code, $active, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo '<div class="alert alert-success m-3">Project updated successfully!</div>';
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
            <div class="col-sm-6 d-flex align-items-center">
                <h1 class="m-0 mr-3"><i class="fas fa-edit"></i> Edit Project</h1>
                <?php if($row['active'] == 'Yes'): ?>
                    <a href="../project?name=<?php echo htmlspecialchars($row['slug']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt"></i> View on Site
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="projects.php">Projects</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                        <li class="nav-item"><a class="nav-link active" id="tab-basics-link" data-toggle="pill" href="#tab-basics" role="tab">1. Basics</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-team-link" data-toggle="pill" href="#tab-team" role="tab">2. Team</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-things-link" data-toggle="pill" href="#tab-things" role="tab">3. Things</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-story-link" data-toggle="pill" href="#tab-story" role="tab">4. Story</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-att-link" data-toggle="pill" href="#tab-att" role="tab">5. Attachments</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        
                        <div class="tab-pane fade show active" id="tab-basics">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Project Title</label>
                                        <input type="text" name="title" class="form-control form-control-lg" required value="<?php echo htmlspecialchars($row['title']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Project Category</label>
                                        <select name="project_category_id" class="form-control">
                                            <option value="0">Uncategorized</option>
                                            <?php
                                            $qc = mysqli_query($connect, "SELECT * FROM project_categories ORDER BY category ASC");
                                            while($rc = mysqli_fetch_assoc($qc)){
                                                $sel = ($row['project_category_id'] == $rc['id']) ? 'selected' : '';
                                                echo '<option value="'.$rc['id'].'" '.$sel.'>'.htmlspecialchars($rc['category']).'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>                                    
                                    <div class="form-group">
                                        <label>Pitch (One sentence summary)</label>
                                        <textarea name="pitch" class="form-control" rows="2"><?php echo htmlspecialchars($row['pitch']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Slug (URL)</label>
                                        <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($row['slug']); ?>">
                                        <small class="text-muted">Leave as is unless you want to change the URL.</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Difficulty</label>
                                            <select name="difficulty" class="form-control">
                                                <option value="Easy" <?php if($row['difficulty']=='Easy') echo 'selected'; ?>>Easy</option>
                                                <option value="Intermediate" <?php if($row['difficulty']=='Intermediate') echo 'selected'; ?>>Intermediate</option>
                                                <option value="Advanced" <?php if($row['difficulty']=='Advanced') echo 'selected'; ?>>Advanced</option>
                                                <option value="Expert" <?php if($row['difficulty']=='Expert') echo 'selected'; ?>>Expert</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Approx. Duration</label>
                                            <input type="text" name="duration" class="form-control" value="<?php echo htmlspecialchars($row['duration']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Cover Image</label>
                                    <div class="mb-2 text-center">
                                        <?php 
                                        $img_src = !empty($row['image']) ? '../' . $row['image'] : '../assets/img/project-no-image.png';
                                        // Nettoyage si double ../
                                        if (strpos($row['image'], '../') === 0) $img_src = $row['image'];
                                        ?>
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" id="preview_image_box" class="img-fluid rounded border" style="max-height:150px;" onerror="this.src='../assets/img/project-no-image.png';">
                                    </div>
                                    
                                    <div class="custom-file text-left mb-2">
                                        <input type="file" name="image" class="custom-file-input" id="postImage">
                                        <label class="custom-file-label" for="postImage">Change File</label>
                                    </div>
                                    
                                    <div class="text-center text-muted mb-2 small">- OR -</div>

                                    <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">Select from Library</button>
                                    <input type="hidden" name="selected_image" id="selected_image_input">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-team">
                            <div class="form-group">
                                <label>Contributors / Team Members</label>
                                <textarea name="team_credits" id="summernote_team" class="form-control"><?php echo html_entity_decode($row['team_credits']); ?></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-things">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hardware Components (BOM)</label>
                                        <textarea name="hardware_parts" id="summernote_hw" class="form-control"><?php echo html_entity_decode($row['hardware_parts']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Software & Apps</label>
                                        <textarea name="software_apps" id="summernote_sw" class="form-control"><?php echo html_entity_decode($row['software_apps']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-story">
                            <div class="form-group">
                                <label>The Full Story</label>
                                <textarea name="story" id="summernote" class="form-control" style="height: 400px;"><?php echo html_entity_decode($row['story']); ?></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-att">
                            <div class="form-group">
                                <label>Schematics / Circuit URL</label>
                                <input type="url" name="schematics_link" class="form-control" value="<?php echo htmlspecialchars($row['schematics_link']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Code / GitHub URL</label>
                                <input type="url" name="code_link" class="form-control" value="<?php echo htmlspecialchars($row['code_link']); ?>">
                            </div>
                            
                            <hr>
                            <div class="form-group">
                                <label>Publication Status</label>
                                <select name="active" class="form-control custom-select" style="max-width: 200px;">
                                    <option value="Draft" <?php if($row['active']=='Draft') echo 'selected'; ?>>Draft</option>
                                    <option value="Yes" <?php if($row['active']=='Yes') echo 'selected'; ?>>Public</option>
                                    <option value="No" <?php if($row['active']=='No') echo 'selected'; ?>>Private</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="edit_project" class="btn btn-primary btn-lg mt-3"><i class="fas fa-save"></i> Update Project</button>
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

<script>
$(document).ready(function() {
    // Initialiser Summernote
    $('#summernote').summernote({height: 300});
    $('#summernote_team').summernote({height: 150, toolbar: [['style',['bold','italic','ul','ol','link']]]});
    $('#summernote_hw').summernote({height: 200, toolbar: [['style',['bold','ul','ol']]]});
    $('#summernote_sw').summernote({height: 200, toolbar: [['style',['bold','ul','ol']]]});

    // Gestion Bibliothèque
    $('#filesModal').on('show.bs.modal', function() {
        if($('#files-gallery-content').html().indexOf('Loading') !== -1) {
            $.get('ajax_load_files.php', function(data) { $('#files-gallery-content').html(data); });
        }
    });

    // Aperçu Upload
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
});
function selectFile(dbValue, fullPath) {
    $('#selected_image_input').val(dbValue);
    $('#preview_image_box').attr('src', fullPath);
    $('#filesModal').modal('hide');
}
</script>

<?php include "footer.php"; ?>