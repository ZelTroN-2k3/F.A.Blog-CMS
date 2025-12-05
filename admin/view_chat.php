<?php
include "header.php";

// --- SÉCURITÉ : Seul l'Admin peut lire les conversations privées ---
if ($user['role'] != 'Admin') {
    echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can read user conversations.</div>';
    echo '<meta http-equiv="refresh" content="2; url=dashboard.php">';
    exit;
}
// ------------------------------------------------------------------

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<meta http-equiv="refresh" content="0; url=chats.php">'; exit;
}
$conv_id = (int)$_GET['id'];

// Récupérer les infos de la conversation
$stmt = mysqli_prepare($connect, "
    SELECT c.*, 
           u1.username as u1_name, u1.avatar as u1_avatar,
           u2.username as u2_name, u2.avatar as u2_avatar
    FROM chat_conversations c
    LEFT JOIN users u1 ON c.user_1 = u1.id
    LEFT JOIN users u2 ON c.user_2 = u2.id
    WHERE c.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $conv_id);
mysqli_stmt_execute($stmt);
$conv = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$conv) { echo '<div class="alert alert-danger m-3">Conversation not found.</div>'; include "footer.php"; exit; }

// Récupérer les messages
$msgs_q = mysqli_query($connect, "SELECT * FROM chat_messages WHERE conversation_id = '$conv_id' ORDER BY created_at ASC");
?>

<style>
    .chat-bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 15px;
        margin-bottom: 10px;
        position: relative;
    }
    .chat-left { background: #f1f0f0; margin-right: auto; border-bottom-left-radius: 2px; }
    .chat-right { background: #dcf8c6; margin-left: auto; border-bottom-right-radius: 2px; }
    .chat-meta { font-size: 0.75rem; color: #888; margin-top: 5px; display: block; text-align: right; }
    .chat-sender { font-weight: bold; font-size: 0.8rem; color: #555; display: block; margin-bottom: 3px; }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Conversation #<?php echo $conv_id; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="chats.php">Chats</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Between 
                    <b><?php echo htmlspecialchars($conv['u1_name']); ?></b> 
                    and 
                    <b><?php echo htmlspecialchars($conv['u2_name']); ?></b>
                </h3>
                <div class="card-tools">
                    <a href="chats.php" class="btn btn-tool"><i class="fas fa-times"></i></a>
                </div>
            </div>
            <div class="card-body" style="background: #ffffff; height: 600px; overflow-y: auto;">
                <?php
                if (mysqli_num_rows($msgs_q) > 0) {
                    while ($msg = mysqli_fetch_assoc($msgs_q)) {
                        // Déterminer qui parle
                        if ($msg['sender_id'] == $conv['user_1']) {
                            $side = 'chat-left';
                            $sender_name = $conv['u1_name'];
                        } else {
                            $side = 'chat-right'; 
                            $sender_name = $conv['u2_name'];
                        }
                        
                        // --- GESTION AFFICHAGE CONTENU (Texte ou Image) ---
                        $content_display = '';
                        
                        // Vérifie si la colonne 'type' existe (compatibilité)
                        $type = isset($msg['type']) ? $msg['type'] : 'text';
                        
                        // Si le message est une image
                        if ($type == 'image') {
                            // Nettoyage robuste du chemin
                            $clean_path = str_replace('../', '', $msg['message']);
                            $img_src = '../' . $clean_path;
                            
                            $content_display = '
                                <a href="' . htmlspecialchars($img_src) . '" target="_blank">
                                    <img src="' . htmlspecialchars($img_src) . '" 
                                         alt="Sent Image" 
                                         style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ccc;"
                                         onerror="this.src=\'../assets/img/no-image.png\';">
                                </a>';
                        } else {
                            $content_display = nl2br(htmlspecialchars($msg['message']));
                        }
                        // ---------------------------------------------------
                        
                        echo '
                        <div class="chat-bubble ' . $side . '">
                            <span class="chat-sender">' . htmlspecialchars($sender_name) . '</span>
                            ' . $content_display . '
                            <span class="chat-meta">' . date('d/m H:i', strtotime($msg['created_at'])) . '</span>
                        </div>';
                    }
                } else {
                    echo '<p class="text-center text-muted">No messages found.</p>';
                }
                ?>
            </div>
            <div class="card-footer text-center">
                <a href="chats.php?delete_id=<?php echo $conv_id; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-danger" onclick="return confirm('Delete this entire conversation?');">
                    <i class="fas fa-trash"></i> Delete Conversation
                </a>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>