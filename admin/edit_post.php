<?php
include "header.php";

// 1. Vérification de l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) { echo '<meta http-equiv="refresh" content="0; url=posts.php">'; exit; }
$id = (int)$_GET['id'];

// 2. Récupération
$stmt = mysqli_prepare($connect, "SELECT * FROM `posts` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) { echo '<div class="alert alert-danger">Post not found.</div>'; exit; }

if ($user['role'] == 'Editor' && $row['author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="alert alert-danger">Access Denied.</div></div>'; exit;
}

// 3. Tags
$tags_value = '';
$stmt_get_tags = mysqli_prepare($connect, "SELECT t.name FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = ?");
mysqli_stmt_bind_param($stmt_get_tags, "i", $id);
mysqli_stmt_execute($stmt_get_tags);
$result_tags = mysqli_stmt_get_result($stmt_get_tags);
$existing_tags_array = [];
while ($row_tag = mysqli_fetch_assoc($result_tags)) { $existing_tags_array[] = $row_tag['name']; }
mysqli_stmt_close($stmt_get_tags);
$tags_value = implode(',', $existing_tags_array);

// 4. Traitement
if (isset($_POST['submit'])) {
    validate_csrf_token();

    $title = $_POST['title'];
    if (!empty($_POST['slug'])) { $slug = generateSeoURL($_POST['slug'], 0); } else { $slug = generateSeoURL($title, 0); }
    $active = $_POST['active']; 
    $featured = $_POST['featured'];
    $category_id = $_POST['category_id'];
    $content = $_POST['content'];
    $publish_at = $_POST['publish_at'];
    $meta_title = !empty($_POST['meta_title']) ? $_POST['meta_title'] : $title;
    $meta_description = $_POST['meta_description'];    
    $download_link = $_POST['download_link'];
    $github_link = $_POST['github_link'];

    $image = $row['image']; 
    if (!empty($_POST['selected_image'])) { $image = $_POST['selected_image']; }
    if (@$_FILES['image']['name'] != '') {
        $target_dir = "uploads/posts/";
        $string = "0123456789wsderfgtyhjuk"; $new_string = str_shuffle($string);
        $dest = "../uploads/posts/image_" . $new_string;
        $opt_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $dest);
        if ($opt_path) { $image = str_replace("../", "", $opt_path); } // CORRECTION : utilisation de opt_path
    }

    $stmt = mysqli_prepare($connect, "UPDATE posts SET title=?, slug=?, meta_title=?, meta_description=?, image=?, active=?, featured=?, category_id=?, content=?, download_link=?, github_link=?, publish_at=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssissssi", $title, $slug, $meta_title, $meta_description, $image, $active, $featured, $category_id, $content, $download_link, $github_link, $publish_at, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Tags Logic (Identique à add_post)
    $post_id = $id;
    $new_tag_slugs = []; 
    if (!empty($_POST['tags'])) {
        $tags_json = $_POST['tags']; $tags_array = json_decode($tags_json);
        if (is_array($tags_array)) {
            $stmt_tag_find = mysqli_prepare($connect, "SELECT id FROM tags WHERE slug = ? LIMIT 1");
            $stmt_tag_insert = mysqli_prepare($connect, "INSERT INTO tags (name, slug) VALUES (?, ?)");
            $stmt_check_link = mysqli_prepare($connect, "SELECT id FROM post_tags WHERE post_id = ? AND tag_id = ?");
            $stmt_post_tag_insert = mysqli_prepare($connect, "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($tags_array as $tag_obj) {
                $tag_name = $tag_obj->value; $tag_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tag_name), '-'));
                if (empty($tag_slug)) continue; $new_tag_slugs[] = $tag_slug; 
                mysqli_stmt_bind_param($stmt_tag_find, "s", $tag_slug); mysqli_stmt_execute($stmt_tag_find);
                $result_tag = mysqli_stmt_get_result($stmt_tag_find);
                if ($row_tag_found = mysqli_fetch_assoc($result_tag)) { $tag_id = $row_tag_found['id']; } 
                else { mysqli_stmt_bind_param($stmt_tag_insert, "ss", $tag_name, $tag_slug); mysqli_stmt_execute($stmt_tag_insert); $tag_id = mysqli_insert_id($connect); }
                mysqli_stmt_bind_param($stmt_check_link, "ii", $post_id, $tag_id); mysqli_stmt_execute($stmt_check_link); mysqli_stmt_store_result($stmt_check_link);
                if (mysqli_stmt_num_rows($stmt_check_link) == 0) { mysqli_stmt_bind_param($stmt_post_tag_insert, "ii", $post_id, $tag_id); mysqli_stmt_execute($stmt_post_tag_insert); }
            }
            mysqli_stmt_close($stmt_tag_find); mysqli_stmt_close($stmt_tag_insert); mysqli_stmt_close($stmt_check_link); mysqli_stmt_close($stmt_post_tag_insert);
        }
    }
    // Nettoyage anciens tags
    if (!empty($existing_tags_array)) {
        $stmt_get_tag_id = mysqli_prepare($connect, "SELECT id, slug FROM tags WHERE name = ?");
        $stmt_unlink = mysqli_prepare($connect, "DELETE FROM post_tags WHERE post_id = ? AND tag_id = ?");
        foreach ($existing_tags_array as $old_name) {
            mysqli_stmt_bind_param($stmt_get_tag_id, "s", $old_name); mysqli_stmt_execute($stmt_get_tag_id);
            $res_old = mysqli_stmt_get_result($stmt_get_tag_id);
            if ($r_old = mysqli_fetch_assoc($res_old)) {
                if (!in_array($r_old['slug'], $new_tag_slugs)) {
                    mysqli_stmt_bind_param($stmt_unlink, "ii", $post_id, $r_old['id']); mysqli_stmt_execute($stmt_unlink);
                }
            }
        }
        mysqli_stmt_close($stmt_get_tag_id); mysqli_stmt_close($stmt_unlink);
    }

    echo '<meta http-equiv="refresh" content="0;url=posts.php">'; exit;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6 d-flex align-items-center">
                <h1 class="m-0 mr-3"><i class="fas fa-edit"></i> Edit Post</h1>
                <?php if($row['active'] == 'Yes'): ?>
                    <a href="../post?name=<?php echo htmlspecialchars($row['slug']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt"></i> View on Site
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="posts.php">Posts</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form name="edit_post_form" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <div class="col-lg-9 col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title">Content</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input class="form-control form-control-lg" name="title" id="title" type="text" value="<?php echo htmlspecialchars($row['title']); ?>" oninput="countText()" required>
                                <small class="text-muted"><i>Characters: <span id="characters"><?php echo strlen($row['title']); ?></span></i></small>
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="form-control" id="summernote" name="content" rows="15" required><?php echo html_entity_decode($row['content']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card card-purple card-outline">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-search"></i> SEO Settings</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>URL Slug</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">/post?name=</span></div>
                                    <input class="form-control" name="slug" type="text" value="<?php echo htmlspecialchars($row['slug']); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input class="form-control" name="meta_title" type="text" value="<?php echo htmlspecialchars($row['meta_title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea class="form-control" name="meta_description" rows="3"><?php echo htmlspecialchars($row['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-link"></i> Attachments</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <input class="form-control" name="download_link" value="<?php echo htmlspecialchars($row['download_link']); ?>" placeholder="Download Link">
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control" name="github_link" value="<?php echo htmlspecialchars($row['github_link']); ?>" placeholder="GitHub Link">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header"><h3 class="card-title">Publishing</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control" required>
                                    <option value="Draft" <?php if ($row['active'] == "Draft") echo 'selected'; ?>>Draft</option>
                                    <option value="Yes" <?php if ($row['active'] == "Yes") echo 'selected'; ?>>Published</option>
                                    <option value="No" <?php if ($row['active'] == "No") echo 'selected'; ?>>Inactive</option>
                                    <option value="Pending" <?php if ($row['active'] == "Pending") echo 'selected'; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="datetime-local" class="form-control" name="publish_at" value="<?php echo date('Y-m-d\TH:i', strtotime($row['publish_at'])); ?>" required>
                            </div>
                             <div class="form-group">
                                <label>Featured</label>
                                <select name="featured" class="form-control" required>
                                    <option value="Yes" <?php if ($row['featured'] == "Yes") echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($row['featured'] == "No") echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <input type="submit" class="btn btn-primary btn-block" name="submit" value="Update Post" />
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Organization</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                <?php
                                $crun = mysqli_query($connect, "SELECT * FROM `categories`");
                                while ($rw = mysqli_fetch_assoc($crun)) {
                                    $selected = ($row['category_id'] == $rw['id']) ? "selected" : "";
                                    echo '<option value="' . $rw['id'] . '" ' . $selected . '>' . $rw['category'] . '</option>';
                                }
                                ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tags</label>
                                <input name="tags" class="form-control" value="<?php echo htmlspecialchars($tags_value); ?>">
                                <div class="mt-3">
                                    <label class="small text-muted mb-1">Existing Tags:</label>
                                    <div class="d-flex flex-wrap" style="max-height: 150px; overflow-y: auto; gap: 5px;">
                                        <?php
                                        $q_tags = mysqli_query($connect, "SELECT name FROM tags ORDER BY name ASC");
                                        while ($tag_item = mysqli_fetch_assoc($q_tags)) {
                                            echo '<span class="badge badge-secondary existing-tag" style="cursor:pointer; opacity: 0.6; font-weight: normal;" onclick="addTagFromList(this)">' . htmlspecialchars($tag_item['name']) . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title">Featured Image</h3></div>
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <?php 
                                $current_img = (!empty($row['image'])) ? '../'.$row['image'] : '../assets/img/no-image.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($current_img); ?>" id="preview_image_box" class="img-fluid rounded shadow-sm" style="max-height: 150px; border: 1px solid #ddd;">
                            </div>
                            <div class="custom-file text-left mb-2">
                                <input type="file" name="image" class="custom-file-input" id="postImage">
                                <label class="custom-file-label" for="postImage">Upload New</label>
                            </div>
                            <div class="text-center text-muted mb-2 small">- OR -</div>
                            <button type="button" class="btn btn-outline-primary btn-block btn-sm" data-toggle="modal" data-target="#filesModal">
                                <i class="fas fa-images"></i> Select from Library
                            </button>
                            <input type="hidden" name="selected_image" id="selected_image_input" value="">
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
      <div class="modal-header">
        <h5 class="modal-title">Select an Image</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="files-gallery-content"><div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div></div>
    </div>
  </div>
</div>

<script>
var tagify;
$(document).ready(function() {
    var input = document.querySelector('input[name=tags]');
    tagify = new Tagify(input, { duplicate: false, delimiters: ",", addTagOnBlur: true, whitelist: [<?php $q_tags_js = mysqli_query($connect, "SELECT name FROM tags"); $tags_js_arr = []; while($t = mysqli_fetch_assoc($q_tags_js)) { $tags_js_arr[] = $t['name']; } echo '"' . implode('","', $tags_js_arr) . '"'; ?>], dropdown: { enabled: 1 } });

    $('#filesModal').on('show.bs.modal', function (e) {
        if($('#files-gallery-content').html().indexOf('fa-spinner') !== -1) {
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
});
function addTagFromList(element) {
    var tagName = element.innerText; if (tagify) { tagify.addTags([tagName]); element.style.opacity = "1"; element.classList.remove("badge-secondary"); element.classList.add("badge-primary"); }
}
function selectFile(dbValue, fullPath) {
    document.getElementById('selected_image_input').value = dbValue;
    document.getElementById('preview_image_box').src = fullPath;
    document.getElementById('postImage').value = "";
    $('.custom-file-label').html('Choose file');
    $('#filesModal').modal('hide');
}
</script>

<?php include "footer.php"; ?>