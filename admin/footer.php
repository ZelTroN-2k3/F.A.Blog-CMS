</div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            Admin v. <?php echo $admin_version; ?>
        </div>
        <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="<?php echo $settings['site_url']; ?>"><?php echo htmlspecialchars($settings['sitename']); ?></a>.</strong> All rights reserved.
    </footer>
</div>
<script src="assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/adminlte/dist/js/adminlte.min.js"></script>
<script src="assets/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="assets/adminlte/plugins/summernote/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="assets/adminlte/plugins/chart.js/Chart.min.js"></script>
<script>
    // Activation de Summernote (éditeur de texte)
    $(document).ready(function() {
        if ($('#summernote').length) {
            $('#summernote').summernote({
                height: 350,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview']],
                    ['help', ['help']]
                ]
            });
        }
    });

    // Activation de DataTables (CETTE FOIS-CI, LE BLOC EST RETIRÉ)
    // Nous nous fions uniquement aux scripts présents dans chaque page (ex: users.php)
    
    // Script pour le titre du post (comptage)
    function countText() {
        let text = document.post_form.title.value;
        document.getElementById('characters').innerText = text.length;
    }
</script>
</body>
</html>