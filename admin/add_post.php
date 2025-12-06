<?php
include "header.php";

if (isset($_POST['add'])) {
    validate_csrf_token();

    $title      = $_POST['title'];
    
    // --- GESTION SLUG ---
    if (!empty($_POST['slug'])) {
        $slug = generateSeoURL($_POST['slug'], 0);
    } else {
        $slug = generateSeoURL($title, 0);
    }

    $active     = $_POST['active']; 
    $featured       = $_POST['featured'];
    $category_id    = $_POST['category_id'];
    $content        = $_POST['content'];
    $publish_at     = $_POST['publish_at']; 
    $meta_title       = !empty($_POST['meta_title']) ? $_POST['meta_title'] : $title;
    $meta_description = $_POST['meta_description'];
    $download_link = $_POST['download_link'];
    $github_link   = $_POST['github_link'];
    $author_id = $user['id'];
    $author    = $uname;

    // Gestion Image
    $image = isset($_POST['selected_image']) ? $_POST['selected_image'] : '';
    if (@$_FILES['image']['name'] != '') {
        $target_dir    = "uploads/posts/";
        $target_file   = $target_dir . basename($_FILES["image"]["name"]);
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false && $_FILES["image"]["size"] < 10000000) {
            $string = "0123456789wsderfgtyhjuk";
            $new_string = str_shuffle($string);
            $destination_path_no_ext = "../uploads/posts/image_" . $new_string;
            $optimized_full_path = optimize_and_save_image($_FILES["image"]["tmp_name"], $destination_path_no_ext);
            
            if ($optimized_full_path) {
                $image = ltrim($optimized_full_path, './'); 
                $image = str_replace('../', '', $image);
            }
        }
    }
    
    // Insertion
    $stmt = mysqli_prepare($connect, "INSERT INTO `posts` (category_id, title, slug, meta_title, meta_description, author_id, image, content, active, featured, download_link, github_link, publish_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "issssisssssss", $category_id, $title, $slug, $meta_title, $meta_description, $author_id, $image, $content, $active, $featured, $download_link, $github_link, $publish_at);
    if (mysqli_stmt_execute($stmt)) {
        $post_id = mysqli_insert_id($connect);
        log_activity($user['id'], "Create Post", "Created post: " . $title . " (ID: $post_id)");

        // Tags
        if ($post_id && !empty($_POST['tags'])) {
            $tags_json = $_POST['tags'];
            $tags_array = json_decode($tags_json);
            if (is_array($tags_array)) {
                $stmt_tag_find = mysqli_prepare($connect, "SELECT id FROM tags WHERE slug = ? LIMIT 1");
                $stmt_tag_insert = mysqli_prepare($connect, "INSERT INTO tags (name, slug) VALUES (?, ?)");
                $stmt_post_tag_insert = mysqli_prepare($connect, "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($tags_array as $tag_obj) {
                    $tag_name = $tag_obj->value;
                    $tag_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tag_name), '-'));
                    if (empty($tag_slug)) continue;
                    mysqli_stmt_bind_param($stmt_tag_find, "s", $tag_slug);
                    mysqli_stmt_execute($stmt_tag_find);
                    $result_tag = mysqli_stmt_get_result($stmt_tag_find);
                    if ($row_tag = mysqli_fetch_assoc($result_tag)) {
                        $tag_id = $row_tag['id'];
                    } else {
                        mysqli_stmt_bind_param($stmt_tag_insert, "ss", $tag_name, $tag_slug);
                        mysqli_stmt_execute($stmt_tag_insert);
                        $tag_id = mysqli_insert_id($connect);
                    }
                    mysqli_stmt_bind_param($stmt_post_tag_insert, "ii", $post_id, $tag_id);
                    @mysqli_stmt_execute($stmt_post_tag_insert);
                }
                mysqli_stmt_close($stmt_tag_find); mysqli_stmt_close($stmt_tag_insert); mysqli_stmt_close($stmt_post_tag_insert);
            }
        }
        // Newsletter (Code existant conservé)
        if ($post_id && $active == 'Yes') { /* ... votre code newsletter ... */ }
    }
    mysqli_stmt_close($stmt);
    echo '<meta http-equiv="refresh" content="0;url=posts.php">';
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-edit"></i> Add Post</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="posts.php">Posts</a></li>
                    <li class="breadcrumb-item active">Add Post</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form name="post_form" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                
                <div class="col-lg-9 col-md-12">
                    
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input class="form-control form-control-lg" name="title" id="title" value="" type="text" oninput="countText()" placeholder="Enter post title" required>
                                <small class="text-muted"><i>Current: <span id="characters">0</span></i></small>
                            </div>
                            
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="form-control" id="summernote" rows="15" name="content" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card card-purple card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-search"></i> SEO Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>URL Slug (Friendly URL)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">/post?name=</span></div>
                                    <input class="form-control" name="slug" type="text" placeholder="Auto-generated from title if empty">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Meta Title</label>
                                <input class="form-control" name="meta_title" type="text" placeholder="Custom title for search engines (Optional)">
                            </div>
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea class="form-control" name="meta_description" rows="3" placeholder="Description for search results (Optional)"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-link"></i> Attachments & Links</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Download link</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-file-archive"></i></span></div>
                                            <input class="form-control" name="download_link" type="url" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>GitHub link</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-github"></i></span></div>
                                            <input class="form-control" name="github_link" type="url" placeholder="https://github.com/...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="col-lg-3 col-md-12">
                    
                    <div class="card card-success card-outline">
                        <div class="card-header"><h3 class="card-title">Publish</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control" required>
                                    <option value="Draft" selected>Draft</option>
                                    <option value="Yes">Published</option>
                                    <option value="No">Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Publication Date</label>
                                <input type="datetime-local" class="form-control" name="publish_at" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                             <div class="form-group">
                                <label>Featured Post?</label>
                                <select name="featured" class="form-control" required>
                                    <option value="Yes">Yes</option>
                                    <option value="No" selected>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <input type="submit" name="add" class="btn btn-primary btn-block" value="Publish Post" />
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
                                    echo '<option value="' . $rw['id'] . '">' . $rw['category'] . '</option>';
                                }
                                ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tags</label>
                                <input name="tags" class="form-control" value="" placeholder="Add tags...">
                                <div class="mt-3">
                                    <label class="small text-muted mb-1"><i class="fas fa-tags"></i> Existing Tags (Click to add):</label>
                                    <div class="d-flex flex-wrap" style="max-height: 150px; overflow-y: auto; gap: 5px;">
                                        <?php
                                        $q_tags = mysqli_query($connect, "SELECT name FROM tags ORDER BY name ASC");
                                        if (mysqli_num_rows($q_tags) > 0) {
                                            while ($tag_item = mysqli_fetch_assoc($q_tags)) {
                                                echo '<span class="badge badge-secondary existing-tag" style="cursor:pointer; opacity: 0.6; font-weight: normal;" onclick="addTagFromList(this)">' . htmlspecialchars($tag_item['name']) . '</span>';
                                            }
                                        } else { echo '<small class="text-muted">No tags created yet.</small>'; }
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
                                <img src="assets/img/no-image.png" id="preview_image_box" class="img-fluid rounded shadow-sm" style="max-height: 150px; border: 1px solid #ddd;">
                                <small id="default_image_msg" class="d-block mt-2 text-muted" style="font-style: italic;">This default image will be used if no file is uploaded.</small>
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
	tagify = new Tagify(input, {
		duplicate: false, delimiters: ",", addTagOnBlur: true,
        whitelist: [
            <?php 
            $q_tags_js = mysqli_query($connect, "SELECT name FROM tags");
            $tags_js_arr = [];
            while($t = mysqli_fetch_assoc($q_tags_js)) { $tags_js_arr[] = $t['name']; }
            echo '"' . implode('","', $tags_js_arr) . '"';
            ?>
        ],
        dropdown: { enabled: 1 }
	});

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
            reader.onload = function(e) { 
                $('#preview_image_box').attr('src', e.target.result);
                $('#default_image_msg').slideUp(); 
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});

function addTagFromList(element) {
    var tagName = element.innerText;
    if (tagify) {
        tagify.addTags([tagName]);
        element.style.opacity = "1"; element.classList.remove("badge-secondary"); element.classList.add("badge-primary");
    }
}

function selectFile(dbValue, fullPath) {
    document.getElementById('selected_image_input').value = dbValue;
    document.getElementById('preview_image_box').src = fullPath;
    document.getElementById('postImage').value = "";
    $('.custom-file-label').html('Choose file');
    $('#default_image_msg').slideUp(); 
    $('#filesModal').modal('hide');
}
</script>
<?php include "footer.php"; ?>