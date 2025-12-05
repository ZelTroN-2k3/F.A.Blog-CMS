<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>

<div class="col-md-8 mb-4">
    <div class="card shadow-sm border-0">
        
        <div class="card-header bg-primary text-white p-3">
            <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> About</h4>
        </div>

        <div class="card-body p-4">
            
            <div class="mb-4">
                <p class="lead text-dark">
                    No formal introduction, no frills: just a blog to share my experiments.
                </p>
                <p>
                    I do not claim to hold the truth, I simply try to <em>"do things myself"</em>. 
                    The result is sometimes great, sometimes... <span class="text-muted">a bit less (maybe often?)</span>.
                </p>
            </div>
            
            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-question-circle me-2"></i> Why do it yourself?</h5>
                <p>
                    To meet specific needs at a lower cost, to innovate, or simply for the pride of having done it. 
                    On paper, it's viable, but what about in practice? That's what we'll see here.
                </p>
                <div class="alert alert-light border-start border-primary border-4">
                    <i class="fas fa-info-circle text-primary me-2"></i> 
                    <strong>The themes:</strong> This blog explores new technologies 
                    (home automation, electronics, computing, embedded systems) and DIY in a broad sense.
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-sitemap me-2"></i> How does it work?</h5>
                    <p>The site is organized around two main axes:</p>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold"><i class="far fa-lightbulb text-warning me-2"></i> The Idea Lab</h6>
                        <p class="small mb-0 text-muted">Concepts, reflections, feasible or not.</p>
                    </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold"><i class="fas fa-tools text-secondary me-2"></i> The Practice</h6>
                        <p class="small mb-0 text-muted">Concrete roadmaps and completed projects.</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-project-diagram me-2"></i> Project Structure</h5>
                <p>Each completed project follows a rigorous structure to be reproducible:</p>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0"><i class="fas fa-pencil-ruler text-primary me-3" style="width:20px;"></i> <strong>Study and Design</strong></li>
                    <li class="list-group-item px-0"><i class="fas fa-chalkboard-teacher text-primary me-3" style="width:20px;"></i> <strong>Explanation of Concepts</strong> (popularized in my own way)</li>
                    <li class="list-group-item px-0"><i class="fas fa-coins text-primary me-3" style="width:20px;"></i> <strong>Detailed Implementation</strong> (including costs)</li>
                    <li class="list-group-item px-0"><i class="fas fa-bug text-danger me-3" style="width:20px;"></i> <strong>Feedback</strong> (opinions and mistakes made)</li>
                    <li class="list-group-item px-0"><i class="fas fa-balance-scale text-success me-3" style="width:20px;"></i> <strong>Verdict</strong> : quality/price ratio and comparison.</li>
                </ul>
            </div>

            <div class="bg-primary text-white p-4 rounded-3 mt-5 text-center shadow-sm">
                <p class="mb-0 fst-italic">
                    <i class="fab fa-opensource fa-2x mb-2 d-block"></i>
                    Everything here is <strong>« Open Source »</strong> : I try to share as many resources as possible and remain open to your comments and feedback!
                </p>
            </div>

        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>