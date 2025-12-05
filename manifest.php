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
            <h4 class="mb-0"><i class="fas fa-scroll me-2"></i> Manifesto</h4>
        </div>

        <div class="card-body p-4">
            
            <div class="mb-4">
                <h2 class="h4 fw-bold text-dark mb-3">Welcome to F.A Blog: Beyond Words, A Vision.</h2>
                <p>
                    Welcome to <strong>F.A Blog</strong>. If you have arrived here, it is likely no coincidence. You might be looking for an answer, inspiration, or simply a fresh perspective on the topics that drive us.
                </p>
                <p>
                    F.A Blog is not just another web address added to your bookmarks. It is a project born from a deep conviction: that sharing knowledge and experiences is the most powerful engine for progress. In a digital world often saturated with superficial information and ephemeral content, I wanted to create a space where we take our time. Time to analyze, time to understand, and above all, time to exchange.
                </p>
            </div>
            
            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-bullseye me-2"></i> What is the mission of F.A Blog?</h5>
                <p>
                    The goal here is twofold. On one hand, it is about deciphering our core topics with rigor and authenticity. On the other hand, F.A Blog aims to be a ground for exploration. I do not claim to hold the absolute truth, but I commit to offering you total intellectual honesty in every line written.
                </p>
                <div class="alert alert-light border-start border-primary border-4">
                    <i class="fas fa-check-circle text-primary me-2"></i> 
                    <strong>Quality over Quantity:</strong> Each article published on this blog is the result of careful reflection, designed to bring you concrete added value—whether to solve a problem, spark your curiosity, or give you the keys to take action in your own projects.
                </div>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-users me-2"></i> A community above all</h5>
                <p>
                    But a blog without readers is just a monologue. F.A Blog is also, and above all, <strong>you</strong>. Your presence, your comments, and your feedback are the very essence of this project.
                </p>
                <p>
                    I envision this space as an open, benevolent, and stimulating discussion lounge. Therefore, I invite you not to remain passive behind your screen: interact, challenge the ideas proposed, and share your own experiences. It is often from this friction of ideas that innovation is born.
                </p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-heart me-2"></i> Why stay?</h5>
                <p>
                    By following F.A Blog, you are not simply subscribing to a stream of articles. You are joining a movement. The desire to go a little further, to dig a little deeper. Whether you are here to learn, to be entertained, or to find the inspiration needed for your next big step, you are in the right place.
                </p>
            </div>

            <div class="bg-light p-4 rounded-3 mt-5 shadow-sm border">
                <p class="mb-0 fst-italic text-center">
                    Thank you for being part of this adventure. Make yourself comfortable, browse the archives, or discover the latest post. The story of F.A Blog is just beginning, and I am delighted to write it with you.
                </p>
                <p class="text-end mt-3 mb-0 fw-bold text-primary">
                    — The F.A Blog Team
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