<?php
include "header.php";

// --- SÉCURITÉ : Seul l'Admin peut gérer les conversations ---
if ($user['role'] != 'Admin') {
    echo '<div class="alert alert-danger m-3">Access Denied. Only Admins can manage conversations.</div>';
    echo '<meta http-equiv="refresh" content="2; url=dashboard.php">';
    exit;
}
// ------------------------------------------------------------

// --- SUPPRESSION D'UNE CONVERSATION ---
if (isset($_GET['delete_id'])) {
    validate_csrf_token_get(); // Sécurité CSRF
    $id = (int)$_GET['delete_id'];
    
    // 1. SUPPRESSION DES IMAGES PHYSIQUES (NOUVEAU BLOC)
    // On récupère tous les messages de cette conversation qui sont des images
    $stmt_files = mysqli_prepare($connect, "SELECT message FROM chat_messages WHERE conversation_id = ? AND type = 'image'");
    mysqli_stmt_bind_param($stmt_files, "i", $id);
    mysqli_stmt_execute($stmt_files);
    $res_files = mysqli_stmt_get_result($stmt_files);
    
    while ($file = mysqli_fetch_assoc($res_files)) {
        // Le chemin en base est "uploads/chat/..."
        // Depuis le dossier admin, on doit remonter d'un cran avec "../"
        $file_path = '../' . $file['message'];
        
        if (file_exists($file_path) && is_file($file_path)) {
            @unlink($file_path); // Supprime le fichier du serveur
        }
    }
    mysqli_stmt_close($stmt_files);

    // 2. Supprimer les messages de la BDD (Comme avant)
    $stmt_msg = mysqli_prepare($connect, "DELETE FROM chat_messages WHERE conversation_id = ?");
    mysqli_stmt_bind_param($stmt_msg, "i", $id);
    mysqli_stmt_execute($stmt_msg);
    mysqli_stmt_close($stmt_msg);

    // 3. Supprimer la conversation elle-même (Comme avant)
    $stmt_conv = mysqli_prepare($connect, "DELETE FROM chat_conversations WHERE id = ?");
    mysqli_stmt_bind_param($stmt_conv, "i", $id);
    
    if(mysqli_stmt_execute($stmt_conv)) {
        echo '<meta http-equiv="refresh" content="0; url=chats.php">';
        exit;
    }
    mysqli_stmt_close($stmt_conv);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-comments"></i> Conversations Manager</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Chats</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">All Conversations</h3>
            </div>
            <div class="card-body">
                <table id="dt-chats" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Last Update</th>
                            <th>Participant A</th>
                            <th>Participant B</th>
                            <th>Messages</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Requête pour récupérer les conversations et les noms des deux utilisateurs
                        $sql = "
                            SELECT c.id, c.updated_at, 
                                   u1.username as user1_name, u1.avatar as user1_avatar,
                                   u2.username as user2_name, u2.avatar as user2_avatar,
                                   (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id) as msg_count
                            FROM chat_conversations c
                            LEFT JOIN users u1 ON c.user_1 = u1.id
                            LEFT JOIN users u2 ON c.user_2 = u2.id
                            ORDER BY c.updated_at DESC
                        ";
                        $run = mysqli_query($connect, $sql);
                        
                        while ($row = mysqli_fetch_assoc($run)) {
                            $u1_img = !empty($row['user1_avatar']) ? '../'.$row['user1_avatar'] : '../assets/img/avatar.png';
                            $u2_img = !empty($row['user2_avatar']) ? '../'.$row['user2_avatar'] : '../assets/img/avatar.png';
                            
                            echo '
                            <tr>
                                <td>' . $row['id'] . '</td>
                                <td>' . date('d M Y - H:i', strtotime($row['updated_at'])) . '</td>
                                
                                <td>
                                    <img src="' . $u1_img . '" width="30" height="30" class="rounded-circle mr-2">
                                    ' . htmlspecialchars($row['user1_name']) . '
                                </td>
                                
                                <td>
                                    <img src="' . $u2_img . '" width="30" height="30" class="rounded-circle mr-2">
                                    ' . htmlspecialchars($row['user2_name']) . '
                                </td>
                                
                                <td><span class="badge badge-info">' . $row['msg_count'] . '</span></td>
                                
                                <td>
                                    <a href="view_chat.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary" title="Read Chat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete_id=' . $row['id'] . '&token=' . $csrf_token . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this entire conversation?\');" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(document).ready(function() {
    $('#dt-chats').DataTable({
        "responsive": true, "autoWidth": false, "ordering": false // On laisse l'ordre SQL par défaut (date)
    });
});
</script>