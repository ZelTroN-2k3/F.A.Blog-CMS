<?php
include "core.php";
head();

// Obtenir l'instance de HTML Purifier
$purifier = get_purifier();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<style>
    /* Petit effet de zoom au survol de la carte */
    .gallery-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    /* Curseur loupe pour indiquer qu'on peut cliquer */
    .gallery-card img {
        cursor: zoom-in;
    }
</style>

<div class="col-md-12 mb-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><i class="fa fa-images"></i> Gallery</div>
        <div class="card-body">

            <nav class="mb-3">
                <div class="nav nav-pills nav-fill" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-all-tab" data-bs-toggle="tab" data-bs-target="#nav-all" type="button" role="tab" aria-controls="nav-all" aria-selected="true">
                        <i class="fas fa-border-all"></i> All
                    </button>
                    <?php
                    $runalb = mysqli_query($connect, "SELECT * FROM `albums` ORDER BY id DESC");
                    while ($rowalb = mysqli_fetch_assoc($runalb)) {
                        echo '<button class="nav-link" id="nav-' . $rowalb['id'] . '-tab" data-bs-toggle="tab" data-bs-target="#nav-' . $rowalb['id'] . '" type="button" role="tab" aria-controls="nav-' . $rowalb['id'] . '" aria-selected="false">
                                  <i class="fas fa-folder"></i> ' . htmlspecialchars($rowalb['title']) . '
                              </button>';
                    }
                    ?>
                </div>
            </nav>
            
            <div class="tab-content" id="nav-tabContent">
                
                <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                    <br />
                    <div class="row">
                        <?php
                        $stmt_all = mysqli_prepare($connect, "SELECT * FROM `gallery` WHERE active='Yes' ORDER BY id DESC");
                        mysqli_stmt_execute($stmt_all);
                        $run = mysqli_stmt_get_result($stmt_all);
                        $count = mysqli_num_rows($run);
                        
                        if ($count <= 0) {
                            echo '<div class="alert alert-info">There are no added images.</div>';
                        } else {
                            while ($row = mysqli_fetch_assoc($run)) {
                                // Préparation de la description pour la lightbox (on nettoie le HTML complexe pour l'attribut data)
                                $clean_desc = htmlspecialchars(strip_tags(html_entity_decode($row['description'])));
                                $img_src = htmlspecialchars($row['image']);
                                $img_title = htmlspecialchars($row['title']);
                                
                                echo '
                                <div class="col-md-4 mb-4">
                                    <div class="card shadow-sm h-100 gallery-card">
                                        <a href="' . $img_src . '" class="glightbox" data-gallery="gallery-all" data-title="' . $img_title . '" data-description="' . $clean_desc . '">
                                            <img src="' . $img_src . '" alt="' . $img_title . '" style="width: 100%; height: 200px; object-fit: cover;" class="card-img-top">
                                        </a>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-primary text-truncate">' . $img_title . '</h6>
                                            <div class="mt-auto">
                                                <a href="' . $img_src . '" class="btn btn-sm btn-outline-primary col-12 glightbox" data-gallery="gallery-all" data-title="' . $img_title . '" data-description="' . $clean_desc . '">
                                                    <i class="fas fa-search-plus"></i> Zoom
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ';
                            }
                        }
                        mysqli_stmt_close($stmt_all);
                        ?>
                    </div>
                </div>
                
                <?php
                $runalb = mysqli_query($connect, "SELECT * FROM `albums` ORDER BY id DESC");
                while ($rowalb = mysqli_fetch_assoc($runalb)) {
                    $album_id = $rowalb['id'];
                    
                    echo '<div class="tab-pane fade" id="nav-' . $album_id . '" role="tabpanel" aria-labelledby="nav-' . $album_id . '-tab">
                              <br />
                              <div class="row">';

                    $stmt_album = mysqli_prepare($connect, "SELECT * FROM `gallery` WHERE active='Yes' AND album_id=? ORDER BY id DESC");
                    mysqli_stmt_bind_param($stmt_album, "i", $album_id);
                    mysqli_stmt_execute($stmt_album);
                    $run = mysqli_stmt_get_result($stmt_album);
                    $count = mysqli_num_rows($run);
                    
                    if ($count <= 0) {
                        echo '<div class="alert alert-info">There are no images in this album.</div>';
                    } else {
                        while ($row = mysqli_fetch_assoc($run)) {
                            $clean_desc = htmlspecialchars(strip_tags(html_entity_decode($row['description'])));
                            $img_src = htmlspecialchars($row['image']);
                            $img_title = htmlspecialchars($row['title']);
                            
                            // Notez le data-gallery="gallery-ALBUMID" pour isoler la navigation par album
                            echo '
                                <div class="col-md-4 mb-4">
                                    <div class="card shadow-sm h-100 gallery-card">
                                        <a href="' . $img_src . '" class="glightbox" data-gallery="gallery-' . $album_id . '" data-title="' . $img_title . '" data-description="' . $clean_desc . '">
                                            <img src="' . $img_src . '" alt="' . $img_title . '" style="width: 100%; height: 200px; object-fit: cover;" class="card-img-top">
                                        </a>

                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-primary text-truncate">' . $img_title . '</h6>
                                            <div class="mt-auto">
                                                <a href="' . $img_src . '" class="btn btn-sm btn-outline-primary col-12 glightbox" data-gallery="gallery-' . $album_id . '" data-title="' . $img_title . '" data-description="' . $clean_desc . '">
                                                    <i class="fas fa-search-plus"></i> Zoom
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    }
                    mysqli_stmt_close($stmt_album);

                    echo '</div></div>';
                }
                ?>
                
            </div> </div> </div> </div>

<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    // Configuration de la Lightbox
    const lightbox = GLightbox({
        touchNavigation: true,
        loop: true,
        zoomable: true,
        draggable: true,
        openEffect: 'zoom', // Effet d'ouverture (zoom, fade, none)
        closeEffect: 'zoom',
        slideEffect: 'slide' // Effet de transition entre images
    });
</script>

<?php
// La fonction footer() contient la balise </body> et </html>
footer();
?>