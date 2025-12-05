<?php
include "header.php";

// Vérification ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (empty($id)) {
    echo '<meta http-equiv="refresh" content="0; url=messages.php">'; exit;
}

// Marquer comme LU
$stmt_update = mysqli_prepare($connect, "UPDATE `messages` SET viewed='Yes' WHERE id=?");
mysqli_stmt_bind_param($stmt_update, "i", $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

// Récupérer le message
$stmt_select = mysqli_prepare($connect, "SELECT * FROM `messages` WHERE id=?");
mysqli_stmt_bind_param($stmt_select, "i", $id);
mysqli_stmt_execute($stmt_select);
$runq = mysqli_stmt_get_result($stmt_select);
$row = mysqli_fetch_assoc($runq);
mysqli_stmt_close($stmt_select);

if (!$row) {
    echo '<div class="content-header"><div class="container-fluid"><div class="alert alert-danger">Message not found.</div></div></div>';
    include "footer.php"; exit;
}

// --- 1. LOGIQUE BADGE UTILISATEUR CONNECTÉ (Pour le "Hello...") ---
$my_role_badge = 'badge-secondary';
if ($user['role'] == 'Admin') $my_role_badge = 'badge-danger';
if ($user['role'] == 'Editor') $my_role_badge = 'badge-success';
// ------------------------------------------------------------------

// --- 2. LOGIQUE AVATAR & RÔLE EXPÉDITEUR ---
$sender_avatar = '../assets/img/avatar.png';
$sender_role_html = '';

$stmt_user = mysqli_prepare($connect, "SELECT avatar, role FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_user, "s", $row['email']);
mysqli_stmt_execute($stmt_user);
$res_user = mysqli_stmt_get_result($stmt_user);

if ($u = mysqli_fetch_assoc($res_user)) {
    // Avatar Expéditeur
    if (!empty($u['avatar'])) {
        $clean_path = str_replace('../', '', $u['avatar']);
        if (strpos($clean_path, 'http') === 0) {
            $sender_avatar = $clean_path;
        } elseif (file_exists('../' . $clean_path)) {
            $sender_avatar = '../' . $clean_path;
        }
    }

    // Badge Rôle Expéditeur
    $role_badge_class = 'badge-secondary';
    if ($u['role'] == 'Admin') $role_badge_class = 'badge-danger';
    if ($u['role'] == 'Editor') $role_badge_class = 'badge-success';
    if ($u['role'] == 'User')   $role_badge_class = 'badge-info';
    
    $sender_role_html = '<div class="text-center mb-2"><span class="badge ' . $role_badge_class . '">' . $u['role'] . '</span></div>';
}
mysqli_stmt_close($stmt_user);
// -------------------------------------------------------------------
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-envelope-open-text"></i> Read Message</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="messages.php">Messages</a></li>
                    <li class="breadcrumb-item active">Read</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-lg-9 col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Message Body</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mailbox-read-info">
                            <h5>Contact Form Submission</h5>
                            <h6>From: <?php echo htmlspecialchars($row['email']); ?>
                                <span class="mailbox-read-time float-right"><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></span>
                            </h6>
                        </div>
                        
                        <div class="mailbox-read-message mt-4">
                            <p class="lead" style="font-size: 1.1rem;">
                                Hello <span class="badge <?php echo $my_role_badge; ?>"><?php echo htmlspecialchars($user['role']); ?></span>,
                            </p>
                            
                            <div class="p-3 bg-light border rounded" style="font-family: sans-serif; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-12">
                
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Sender Details</h3>
                    </div>
                    <div class="card-body box-profile">
                        
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle mb-3"
                                 src="<?php echo htmlspecialchars($sender_avatar); ?>"
                                 alt="User profile picture"
                                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #adb5bd;">
                        </div>

                        <h3 class="profile-username text-center"><?php echo htmlspecialchars($row['name']); ?></h3>
                        
                        <?php echo $sender_role_html; ?>
                        
                        <p class="text-muted text-center"><?php echo htmlspecialchars($row['email']); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Date</b> <a class="float-right"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Time</b> <a class="float-right"><?php echo date('H:i', strtotime($row['created_at'])); ?></a>
                            </li>
                        </ul>

                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-reply"></i> Reply via Email
                        </a>
                        
                        <a href="messages.php?delete-id=<?php echo $row['id']; ?>&token=<?php echo isset($csrf_token) ? $csrf_token : ''; ?>" class="btn btn-danger btn-block mt-2" onclick="return confirm('Delete this message?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        
                        <a href="messages.php" class="btn btn-default btn-block mt-2">
                            <i class="fas fa-arrow-left"></i> Back to Inbox
                        </a>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
</section>

<?php include "footer.php"; ?>