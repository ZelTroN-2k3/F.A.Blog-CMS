<?php
include "core.php";
head();

// 1. Vérifier si l'utilisateur est connecté
if ($logged == 'No') {
    echo '<meta http-equiv="refresh" content="0;url=login">';
    exit;
}

$user_id = $rowu['id'];
$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// 2. Vérifier si l'ID du commentaire est valide
if ($comment_id == 0) {
    echo '<meta http-equiv="refresh" content="0;url=my-comments.php">';
    exit;
}

// 3. Récupérer le commentaire ET les infos de l'article (JOIN)
// --- CORRECTION ICI : Ajout du LEFT JOIN pour avoir le slug et le titre ---
$query = "
    SELECT c.*, p.title AS post_title, p.slug AS post_slug 
    FROM comments c 
    LEFT JOIN posts p ON c.post_id = p.id 
    WHERE c.id = ? AND c.user_id = ? AND c.guest = 'No' 
    LIMIT 1
";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$comment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 4. Si le commentaire n'existe pas ou n'appartient pas à l'utilisateur
if (!$comment) {
    echo '<meta http-equiv="refresh" content="0;url=my-comments.php">';
    exit;
}

// 5. Gérer la soumission du formulaire
if (isset($_POST['save_comment'])) {
    validate_csrf_token();
    
    $new_comment_content = $_POST['comment_content'];
    
    if (strlen($new_comment_content) < 2) {
        $message = '<div class="alert alert-danger">Your comment is too short.</div>';
    } else {
        // Mettre à jour le commentaire
        $stmt_update = mysqli_prepare($connect, "UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt_update, "sii", $new_comment_content, $comment_id, $user_id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
        
        $message = '<div class="alert alert-success">Comment updated! Redirecting...</div>';
        echo '<meta http-equiv="refresh" content="2;url=my-comments.php">';
    }
}

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>

<div class="col-md-8 mb-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><i class="fa fa-edit"></i> Edit my comment</div>
        <div class="card-body">
            
            <?php echo $message; ?>

            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group mb-4">
                    <label for="comment_content">Your comment:</label>
                    <textarea name="comment_content" id="comment_content" rows="6" class="form-control" required><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                    
                    <small class="form-text text-muted mt-2">
                        Original article: 
                        <a href="post?name=<?php echo htmlspecialchars($comment['post_slug']); ?>#comment-<?php echo $comment['id']; ?>" target="_blank">
                            <?php echo htmlspecialchars($comment['post_title']); ?>
                        </a>
                    </small>
                </div>
                
                <input type="submit" name="save_comment" class="btn btn-primary col-12" value="Save changes" />
            </form>

        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>