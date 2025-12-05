<?php
include "core.php"; // Connexion BDD + Paramètres
head(); // En-tête du site

// 1. Gestion de la Sidebar GAUCHE
if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}

// Récupérer les questions actives
$q_faq = mysqli_query($connect, "SELECT * FROM faqs WHERE active='Yes' ORDER BY position_order ASC, id DESC");
?>

<div class="col-md-8 mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary"><i class="fas fa-question-circle"></i> FAQ</h1>
                <p class="lead text-muted">Find answers to your most common questions here.</p>
            </div>

            <?php if (mysqli_num_rows($q_faq) > 0): ?>
                
                <div class="accordion shadow-sm" id="faqAccordion">
                    <?php 
                    $i = 0;
                    while ($row = mysqli_fetch_assoc($q_faq)): 
                        $i++;
                        $collapseId = "collapse_" . $row['id'];
                        $headingId  = "heading_" . $row['id'];
                        
                        // Le premier élément est ouvert par défaut
                        $showClass   = ($i == 1) ? "show" : "";
                        $buttonClass = ($i == 1) ? "" : "collapsed";
                    ?>
                    
                    <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                            <button class="accordion-button <?php echo $buttonClass; ?> fw-bold py-3" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" 
                                    aria-expanded="<?php echo ($i == 1 ? 'true' : 'false'); ?>" aria-controls="<?php echo $collapseId; ?>">
                                <span class="me-2 text-primary">Q.</span> <?php echo htmlspecialchars($row['question']); ?>
                            </button>
                        </h2>
                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $showClass; ?>" 
                             aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light text-dark" style="line-height: 1.6;">
                                <?php echo html_entity_decode($row['answer']); ?>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No questions have been added yet.
                </div>
            <?php endif; ?>

            <div class="alert alert-light border mt-5 text-center">
                <h5 class="alert-heading"><i class="far fa-life-ring text-danger"></i> Can't find your answer?</h5>
                <p class="mb-3">Our team is here to help you.</p>
                <a href="contact.php" class="btn btn-outline-primary px-4 rounded-pill">Contact Support</a>
            </div>

        </div>
    </div>
</div>

<style>
/* Petit ajustement pour l'accordéon */
.accordion-button:not(.collapsed) {
    color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}
</style>

<?php
// 3. Gestion de la Sidebar DROITE
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}

footer(); // Pied de page
?>