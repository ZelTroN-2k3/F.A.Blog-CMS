<?php
include "header.php";

// Vérifier l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=comments.php">';
    exit;
}

$id = (int)$_GET['id'];

// --- LOGIQUE : Mise à jour (UPDATE) ---
if (isset($_POST['submit'])) {
    validate_csrf_token();
    
    $approved = $_POST['approved'];
    $comment_content = $_POST['comment']; // Récupérer le contenu modifié

    // Requête préparée pour mettre à jour le statut ET le contenu
    $stmt = mysqli_prepare($connect, "UPDATE comments SET approved=?, comment=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $approved, $comment_content, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo '<div class="alert alert-success">Commentaire mis à jour avec succès.</div>';
        echo '<meta http-equiv="refresh" content="1; url=comments.php">';
    } else {
        echo '<div class="alert alert-danger">Erreur lors de la mise à jour.</div>';
    }
    mysqli_stmt_close($stmt);
}

// --- LOGIQUE : Récupération des données ET Sécurité ---
// On récupère le commentaire ET l'auteur de l'article lié (posts.author_id)
$query = "
    SELECT c.*, p.author_id as post_author_id 
    FROM comments c
    LEFT JOIN posts p ON c.post_id = p.id
    WHERE c.id = ?
";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo '<meta http-equiv="refresh" content="0; url=comments.php">';
    exit;
}

// --- SÉCURITÉ AJOUTÉE ---
// Si je suis Éditeur, je ne peux modérer que les commentaires de MES articles.
if ($user['role'] == 'Editor' && $row['post_author_id'] != $user['id']) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Access Denied. You can only moderate comments on your own posts.
          </div></div></div>';
    include "footer.php";
    exit;
}
// --- FIN SÉCURITÉ ---

// Récupération des infos de l'auteur
$author_name = $row['user_id'];
$avatar = 'assets/img/avatar.png';

if ($row['guest'] == 'Yes') {
    $author_name = $row['user_id'] . ' (Guest)';
} else {
    $stmt_user = mysqli_prepare($connect, "SELECT username, avatar FROM `users` WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt_user, "i", $row['user_id']);
    mysqli_stmt_execute($stmt_user);
    $res_user = mysqli_stmt_get_result($stmt_user);
    if ($user = mysqli_fetch_assoc($res_user)) {
        $avatar = $user['avatar'];
        $author_name = $user['username'];
    }
    mysqli_stmt_close($stmt_user);
}

// Correction Avatar (Google vs Local)
$avatar_path = $avatar;
if (strpos($avatar, 'http') !== 0) {
    $avatar_path = '../' . $avatar;
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Edit Comment</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="comments.php">Comments</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Edit details</h3>
            </div>
            
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group text-center">
                                <label>Author</label><br>
                                <img src="<?php echo htmlspecialchars($avatar_path); ?>" class="img-circle elevation-2 mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                                <input type="text" class="form-control text-center" value="<?php echo htmlspecialchars($author_name); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="approved">
                                    <option value="Yes" <?php if ($row['approved'] == "Yes") echo 'selected'; ?>>Approved (Visible)</option>
                                    <option value="No" <?php if ($row['approved'] == "No") echo 'selected'; ?>>Pending (Hidden)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" value="<?php echo $row['created_at']; ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Comment Content</label>
                                <textarea name="comment" class="form-control" rows="10" required><?php echo htmlspecialchars($row['comment']); ?></textarea>
                                <small class="text-muted">You can modify the user's text here (e.g. remove bad words).</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="comments.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>