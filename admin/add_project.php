<?php
include "header.php";

if (isset($_POST['add_project'])) {
    validate_csrf_token();
    
    // 1. Basics
    $title = $_POST['title'];
    $slug = generateSeoURL($title, 0);
    $pitch = $_POST['pitch'];
    $difficulty = $_POST['difficulty'];
    $duration = $_POST['duration'];
    $active = $_POST['active'];
    $featured = $_POST['featured'];
    $cat_id = (int)$_POST['project_category_id'];

    // 2. Team
    $team = $_POST['team_credits'];
    
    // 3. Things (Conversion Array -> JSON)
    // On vérifie si c'est un tableau, sinon on met un tableau JSON vide
    $hardware = isset($_POST['hardware']) ? json_encode($_POST['hardware']) : '[]';
    $software = isset($_POST['software']) ? json_encode($_POST['software']) : '[]';
    $tools    = isset($_POST['tools']) ? json_encode($_POST['tools']) : '[]';
    
    // 4. Story
    $story = $_POST['story'];
    
    // 5. Attachments
    $schematics = $_POST['schematics_link'];
    $code = $_POST['code_link'];
    
    // Image Cover
    $image = '';
    if (!empty($_POST['selected_image'])) { $image = $_POST['selected_image']; }
    // (Ajoutez ici votre bloc de fallback upload d'image standard si vous le souhaitez, 
    // pour l'instant on utilise la bibliothèque pour simplifier le code)
    
    $author_id = $user['id'];

    // Mise à jour de la requête INSERT avec 'featured'
    $stmt = mysqli_prepare($connect, "INSERT INTO projects (author_id, title, slug, pitch, image, difficulty, duration, team_credits, hardware_parts, software_apps, hand_tools, story, schematics_link, code_link, active, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    // Ajout d'un 's' dans le type (15 paramètres au total : i + 14 s)
    mysqli_stmt_bind_param($stmt, "issssssssssssssss", $author_id, $title, $slug, $pitch, $image, $difficulty, $duration, $team, $hardware, $software, $tools, $story, $schematics, $code, $active, $featured);    
    
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
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle"></i> New Project</h1></div>
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
                                        <input type="text" name="title" class="form-control form-control-lg" required placeholder="Ex: Smart Home Assistant">
                                    </div>
                                    <div class="form-group">
                                        <label>Project Category</label>
                                        <select name="project_category_id" class="form-control">
                                            <option value="0">Uncategorized</option>
                                            <?php
                                            $qc = mysqli_query($connect, "SELECT * FROM project_categories ORDER BY category ASC");
                                            while($rc = mysqli_fetch_assoc($qc)){
                                                echo '<option value="'.$rc['id'].'">'.htmlspecialchars($rc['category']).'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>                                    
                                    <div class="form-group">
                                        <label>Pitch (One sentence summary)</label>
                                        <textarea name="pitch" class="form-control" rows="2" placeholder="What is this project about?"></textarea>
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
                                            <label>Approx. Duration</label>
                                            <input type="text" name="duration" class="form-control" placeholder="Ex: 2 hours">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Cover Image</label>
                                    <div class="mb-2 text-center">
                                        <img src="../assets/img/project-no-image.png" id="preview_image_box" class="img-fluid rounded border" style="max-height:150px;">
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">Select Cover Image</button>
                                    <input type="hidden" name="selected_image" id="selected_image_input">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-team">
                            <div class="form-group">
                                <label>Contributors / Team Members</label>
                                <textarea name="team_credits" id="summernote_team" class="form-control"></textarea>
                                <small class="text-muted">List people who helped you.</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-things">
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-microchip"></i> Hardware components</h5>
                                    <button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('hardware-container', 'hardware')">+ Add Component</button>
                                </div>
                                <div class="card-body bg-light" id="hardware-container">
                                    </div>
                            </div>

                            <div class="card card-outline card-info mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-code"></i> Software apps & online services</h5>
                                    <button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('software-container', 'software')">+ Add App</button>
                                </div>
                                <div class="card-body bg-light" id="software-container"></div>
                            </div>

                            <div class="card card-outline card-warning mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-tools"></i> Hand tools & fabrication machines</h5>
                                    <button type="button" class="btn btn-sm btn-primary float-right" onclick="addBomRow('tools-container', 'tools')">+ Add Tool</button>
                                </div>
                                <div class="card-body bg-light" id="tools-container"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-story">
                            <div class="form-group">
                                <label>The Full Story (How you built it)</label>
                                <textarea name="story" id="summernote" class="form-control" style="height: 400px;"></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-att">
                            <div class="form-group">
                                <label>Schematics / Circuit URL</label>
                                <input type="url" name="schematics_link" class="form-control" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Code / GitHub URL</label>
                                <input type="url" name="code_link" class="form-control" placeholder="https://github.com/...">
                            </div>
                            
                            <hr>
                            <div class="form-group">
                                <label>Publication Status</label>
                                <select name="active" class="form-control custom-select" style="max-width: 200px;">
                                    <option value="Draft">Draft</option>
                                    <option value="Yes">Public</option>
                                    <option value="No">Private</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Featured Project?</label>
                                <select name="featured" class="form-control custom-select" style="max-width: 200px;">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes (Show in Slider)</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="add_project" class="btn btn-success btn-lg mt-3"><i class="fas fa-check"></i> Publish Project</button>
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
    // Initialiser les Summernote pour chaque zone
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
});
function selectFile(dbValue, fullPath) {
    $('#selected_image_input').val(dbValue);
    $('#preview_image_box').attr('src', fullPath);
    $('#filesModal').modal('hide');
}
</script>
<script src="js/bom_manager.js"></script>
<?php include "footer.php"; ?>