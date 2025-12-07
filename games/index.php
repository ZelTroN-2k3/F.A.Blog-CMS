<?php
// 1. On remonte d'un cran pour trouver le moteur du CMS
include "../core.php";

// SEO
$pagetitle = "Arcade Room";
$description = "Play retro games directly in your browser. Relax with our collection of classic arcade games.";

head();
?>

<style>
    .arcade-header {
        background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: 0 0 50% 50% / 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .game-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
        height: 100%;
    }
    
    .game-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    
    .game-icon-box {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: white;
    }
    
    .btn-play {
        border-radius: 50px;
        padding: 8px 25px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
    }
    
    .btn-play:hover {
        transform: scale(1.05);
    }
</style>

<div class="arcade-header text-center">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="fas fa-gamepad"></i> Arcade Room</h1>
        <p class="lead mb-0">Relax with our collection of retro games.</p>
    </div>
</div>

<div class="container mb-5">
    
    <div class="mb-4">
        <a href="../index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Site</a>
    </div>

    <div class="row g-4">
        
        <div class="col-md-6 col-lg-4">
            <div class="card game-card shadow-sm">
                <div class="game-icon-box bg-dark">
                    <i class="fas fa-rocket text-success"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h4 class="card-title fw-bold">Space Invaders</h4>
                    <p class="card-text text-muted small flex-grow-1">
                        DDefend the Earth against the alien invasion! A timeless classic.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark border me-2">Shooter</span>
                        <span class="badge bg-light text-dark border">Retro</span>
                    </div>
                    <div class="d-grid mt-4">
                        <a href="space-invaders.php" class="btn btn-success btn-play shadow-sm">
                            <i class="fas fa-play me-2"></i> PLAY
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card game-card shadow-sm">
                <div class="game-icon-box bg-secondary">
                    <i class="fas fa-square"></i> </div>
                <div class="card-body text-center d-flex flex-column">
                    <h4 class="card-title fw-bold">Snake</h4>
                    <p class="card-text text-muted small flex-grow-1">
                        Eat the apples, grow longer, but don't bite your tail!
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark border me-2">Reflection</span>
                        <span class="badge bg-light text-dark border">Classic</span>
                    </div>
                    <div class="d-grid mt-4">
                        <a href="snake.php" class="btn btn-warning btn-play shadow-sm text-dark">
                            <i class="fas fa-play me-2"></i> PLAY
                        </a>
                    </div>
                </div>
            </div>
        </div>

                <div class="col-md-6 col-lg-4">
            <div class="card game-card shadow-sm">
                <div class="game-icon-box bg-primary">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h4 class="card-title fw-bold">Tetris</h4>
                    <p class="card-text text-muted small flex-grow-1">
                        Stack the blocks and complete lines. The ultimate puzzle.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark border me-2">Puzzle</span>
                    </div>
                    <div class="d-grid mt-4">
                        <a href="tetris.php" class="btn btn-primary btn-play shadow-sm">
                            <i class="fas fa-play me-2"></i> PLAY
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card game-card shadow-sm" style="opacity: 0.7;">
                <div class="game-icon-box bg-primary">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h4 class="card-title fw-bold">Demo</h4>
                    <p class="card-text text-muted small flex-grow-1">
                        Game currently under construction.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark border me-2">Demo</span>
                    </div>
                    <div class="d-grid mt-4">
                        <button class="btn btn-secondary btn-play disabled">
                            <i class="fas fa-clock me-2"></i> COMING SOON
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
// On remonte au footer du core
// Note: comme footer() est une fonction définie dans core.php, on l'appelle simplement.
footer();
?>