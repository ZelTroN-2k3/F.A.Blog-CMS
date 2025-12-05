<?php
// -------------------------------------------------------------------------
// newsletter.php
// Interface de gestion de la newsletter (Envoi, Liste, Ajout)
// -------------------------------------------------------------------------

// 1. Charger la logique (Doit être avant le header pour gérer l'export CSV)
include "includes/newsletter_logic.php";

// 2. Charger le header (Affichage du menu, CSS, etc.)
include "header.php"; 
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="far fa-envelope"></i> Newsletter</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Newsletter</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php echo $display_message; // Affichage des alertes succès/erreur ?>

        <div class="row">
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Send mass message</h3>
                    </div>        
                    <form action="newsletter.php" method="post">
                    <div class="card-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label>Modèle d'email</label>
                            <select class="form-control" name="template" id="template-select" required>
                                <option value="simple" <?php if($form_data['template'] == 'simple') echo 'selected'; ?>>Simple (Par défaut)</option>
                                <option value="featured_post" <?php if($form_data['template'] == 'featured_post') echo 'selected'; ?>>Article à la Une</option>
                                <option value="promo" <?php if($form_data['template'] == 'promo') echo 'selected'; ?>>Promotionnel (Avec bouton)</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="featured-post-group" style="display:none; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <label>Choisir un article à mettre en avant</label>
                            <select class="form-control" name="featured_post_id">
                                <option value="0">-- Aucun --</option>
                                <?php foreach ($latest_posts as $post): ?>
                                    <option value="<?php echo $post['id']; ?>" <?php echo ($form_data['featured_post_id'] == $post['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Le titre, l'image et l'extrait seront ajoutés au-dessus de votre contenu.</small>
                        </div>
                        
                        <div id="promo-group" style="display:none; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <div class="form-group">
                                <label>Texte du Bouton (Optionnel)</label>
                                <input class="form-control" name="promo_btn_text" placeholder="Ex: Voir l'offre" value="<?php echo htmlspecialchars($form_data['promo_btn_text']); ?>">
                            </div>
                            <div class="form-group">
                                <label>URL du Bouton (Optionnel)</label>
                                <input class="form-control" name="promo_btn_url" type="url" placeholder="https://..." value="<?php echo htmlspecialchars($form_data['promo_btn_url']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Sujet (Titre de l'email)</label>
                            <input class="form-control" name="title" value="<?php echo htmlspecialchars($form_data['title']); ?>" type="text" required>
                        </div>
                        <div class="form-group">
                            <label>Contenu Principal</label>
                            <textarea class="form-control" id="summernote" name="content" required><?php echo htmlspecialchars($form_data['content']); ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="submit" name="send_mass_message" class="btn btn-primary" value="Envoyer à tous" onclick="return confirm('Êtes-vous sûr de vouloir envoyer cet email à tous les abonnés ?');" />
                        <input type="submit" name="preview_message" class="btn btn-secondary" value="Aperçu" />
                    </div>
                    </form>
                </div>
                
                <?php if (!empty($preview_html)): ?>
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Aperçu de l'Email</h3>
                    </div>
                    <div class="card-body p-0">
                        <iframe srcdoc="<?php echo htmlspecialchars($preview_html); ?>" style="width: 100%; height: 600px; border: 0;"></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-5">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Add Subscriber</h3>
                    </div>
                    <form action="newsletter.php" method="post">
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input class="form-control" name="email" type="email" placeholder="Enter email" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <input type="submit" name="add_subscriber" class="btn btn-success" value="Add" />
                        </div>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Subscribers List</h3>
                        <div class="card-tools">
                            <a href="?export=csv" class="btn btn-success btn-sm"><i class="fas fa-file-csv"></i> Export CSV</a>
                        </div>
                    </div>        
                    
                    <form action="newsletter.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="card-body">
                            <table class="table table-bordered table-hover" id="dt-basic" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width: 10px;"><input type="checkbox" id="select-all"></th>
                                        <th>E-Mail</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($subscribers_list as $row): ?>
                                    <tr>
                                        <td><input type="checkbox" name="subscriber_ids[]" value="<?php echo $row['id']; ?>"></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <button type="submit" name="action" value="unsubscribe_from_list" 
                                                    onclick="this.form.unsubscribe_id.value='<?php echo $row['id']; ?>'; this.form.email_for_message.value='<?php echo htmlspecialchars($row['email']); ?>'; return confirm('Are you sure?');" 
                                                    class="btn btn-danger btn-sm">
                                                <i class="fas fa-bell-slash"></i> Unsubscribe
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <input type="hidden" name="unsubscribe_id" value="">
                            <input type="hidden" name="email_for_message" value="">
                        </div>
                        <div class="card-footer">
                            <select name="bulk_action" class="form-control" style="width: 200px; display: inline-block;">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" name="apply_bulk_action" class="btn btn-primary" onclick="return confirm('Are you sure you want to delete selected subscribers?');">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
$(document).ready(function() {
    // 1. Activation de DataTables
    var table = $('#dt-basic').DataTable({
        "responsive": true,
        "lengthChange": false, 
        "autoWidth": false,
        "order": [[ 1, "asc" ]], 
        "columnDefs": [ { "orderable": false, "targets": 0 }, { "orderable": false, "targets": 2 } ]
    });
    
    // 2. Logique "Select All"
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // 3. Logique Template (Champs conditionnels)
    function toggleTemplateFields() {
        var type = $('#template-select').val();
        
        if (type === 'featured_post') {
            $('#featured-post-group').slideDown();
            $('#promo-group').slideUp();
        } else if (type === 'promo') {
            $('#featured-post-group').slideUp();
            $('#promo-group').slideDown();
        } else {
            $('#featured-post-group').slideUp();
            $('#promo-group').slideUp();
        }
    }
    
    $('#template-select').on('change', toggleTemplateFields);
    toggleTemplateFields(); // Init au chargement
});
</script>

<?php include "footer.php"; ?>