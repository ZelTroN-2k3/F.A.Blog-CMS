<?php
// 1. Intégration au CMS
include "../core.php";

// Titre de la page pour le SEO
$pagetitle = "Space Invaders - Arcade";
head();
?>

<style>
    #game-container {
        background-color: #000;
        margin: 20px auto;
        display: block;
        border: 4px solid #333;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        position: relative;
        overflow: hidden;
    }
    canvas {
        display: block;
        margin: 0 auto;
        background: #000;
    }
    #ui-layer {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .game-message {
        color: #0f0;
        font-family: 'Courier New', Courier, monospace;
        font-size: 30px;
        font-weight: bold;
        text-align: center;
        background: rgba(0,0,0,0.8);
        padding: 20px;
        border: 2px solid #0f0;
        border-radius: 10px;
        pointer-events: auto;
        cursor: pointer;
    }
    .score-board {
        position: absolute;
        top: 10px;
        left: 15px;
        color: #fff;
        font-family: 'Courier New', Courier, monospace;
        font-size: 20px;
        z-index: 10;
    }
    .controls-hint {
        text-align: center;
        margin-top: 10px;
        color: #666;
        font-size: 0.9rem;
    }
    .key-badge {
        display: inline-block;
        padding: 2px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #f4f4f4;
        color: #333;
        font-weight: bold;
        margin: 0 2px;
    }
</style>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-rocket text-success me-2"></i> Space Invaders</h5>
                    <a href="../index.php" class="btn btn-sm btn-outline-light">Quitter</a>
                </div>
                <div class="card-body bg-light text-center">
                    
                    <div style="position:relative; width: 800px; max-width: 100%; margin: 0 auto;">
                        <div class="score-board">SCORE: <span id="scoreEl">0</span></div>
                        <canvas id="gameCanvas" width="800" height="500"></canvas>
                        
                        <div id="ui-layer">
                            <div class="game-message" id="startBtn" onclick="startGame()">
                                CLICK TO START<br>
                                <small style="font-size:16px; color:#fff;">Sauvez la galaxie !</small>
                            </div>
                        </div>
                    </div>

                    <div class="controls-hint">
                        Contrôles : <span class="key-badge">←</span> <span class="key-badge">→</span> pour bouger, <span class="key-badge">ESPACE</span> pour tirer.
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const scoreEl = document.getElementById('scoreEl');
const startBtn = document.getElementById('startBtn');
const uiLayer = document.getElementById('ui-layer');

// --- VARIABLES ---
let animationId;
let score = 0;
let gameActive = false;

// Joueur
const player = {
    x: canvas.width / 2 - 20,
    y: canvas.height - 40,
    width: 40,
    height: 20,
    color: '#00ff00',
    speed: 5,
    dx: 0
};

// Balles
let bullets = [];
const bulletSpeed = 7;

// Ennemis
let enemies = [];
const enemyRows = 4;
const enemyCols = 8;
const enemyWidth = 40;
const enemyHeight = 30;
const enemyPadding = 20;
const enemyOffsetTop = 50;
const enemyOffsetLeft = 50;
let enemyDirection = 1; // 1 = droite, -1 = gauche
let enemySpeed = 1; // Vitesse initiale

// Clavier
let rightPressed = false;
let leftPressed = false;
let spacePressed = false;

// --- ECOUTEURS CLAVIER ---
document.addEventListener('keydown', (e) => {
    if(e.key === 'Right' || e.key === 'ArrowRight') rightPressed = true;
    if(e.key === 'Left' || e.key === 'ArrowLeft') leftPressed = true;
    if(e.key === ' ' || e.code === 'Space') {
        if(!spacePressed && gameActive) {
            shoot();
            spacePressed = true; 
        }
        // Empêcher le scroll avec espace
        e.preventDefault();
    }
});
document.addEventListener('keyup', (e) => {
    if(e.key === 'Right' || e.key === 'ArrowRight') rightPressed = false;
    if(e.key === 'Left' || e.key === 'ArrowLeft') leftPressed = false;
    if(e.key === ' ' || e.code === 'Space') spacePressed = false;
});

// --- FONCTIONS DU JEU ---

function initGame() {
    score = 0;
    scoreEl.innerText = score;
    bullets = [];
    enemies = [];
    enemySpeed = 1;
    player.x = canvas.width / 2 - 20;
    
    // Créer les ennemis
    for(let c=0; c<enemyCols; c++) {
        for(let r=0; r<enemyRows; r++) {
            let enemyX = (c * (enemyWidth + enemyPadding)) + enemyOffsetLeft;
            let enemyY = (r * (enemyHeight + enemyPadding)) + enemyOffsetTop;
            enemies.push({ x: enemyX, y: enemyY, alive: true });
        }
    }
}

function shoot() {
    bullets.push({
        x: player.x + player.width / 2 - 2.5,
        y: player.y,
        width: 5,
        height: 15,
        color: '#ff0'
    });
}

function drawPlayer() {
    ctx.fillStyle = player.color;
    // Forme simple de vaisseau (triangle + base)
    ctx.beginPath();
    ctx.moveTo(player.x + player.width/2, player.y);
    ctx.lineTo(player.x + player.width, player.y + player.height);
    ctx.lineTo(player.x, player.y + player.height);
    ctx.fill();
}

function drawEnemies() {
    enemies.forEach(enemy => {
        if(enemy.alive) {
            ctx.fillStyle = '#ff0000';
            // Dessin simple d'alien (carré avec yeux)
            ctx.fillRect(enemy.x, enemy.y, enemyWidth, enemyHeight);
            
            // Yeux
            ctx.fillStyle = '#000';
            ctx.fillRect(enemy.x + 10, enemy.y + 10, 5, 5);
            ctx.fillRect(enemy.x + 25, enemy.y + 10, 5, 5);
        }
    });
}

function drawBullets() {
    ctx.fillStyle = '#ffff00';
    bullets.forEach(bullet => {
        ctx.fillRect(bullet.x, bullet.y, bullet.width, bullet.height);
    });
}

function update() {
    if(!gameActive) return;

    // Déplacement Joueur
    if(rightPressed && player.x < canvas.width - player.width) {
        player.x += player.speed;
    }
    else if(leftPressed && player.x > 0) {
        player.x -= player.speed;
    }

    // Déplacement Balles
    bullets.forEach((bullet, index) => {
        bullet.y -= bulletSpeed;
        // Supprimer si hors écran
        if(bullet.y < 0) {
            bullets.splice(index, 1);
        }
    });

    // Déplacement Ennemis
    let moveDown = false;
    // Vérifier les bords
    enemies.forEach(enemy => {
        if(enemy.alive) {
            if(enemy.x + enemyWidth > canvas.width || enemy.x < 0) {
                enemyDirection *= -1; // Inverser direction
                moveDown = true;
            }
        }
    });

    if(moveDown) {
        // Accélérer un peu quand ils descendent
        enemySpeed += 0.2; 
        enemies.forEach(enemy => {
            enemy.y += 20; // Descendre
            // Si un ennemi touche le bas -> Game Over
            if(enemy.y + enemyHeight >= player.y) {
                gameOver();
            }
        });
    } else {
        // Avancer
        enemies.forEach(enemy => {
            enemy.x += enemySpeed * enemyDirection;
        });
    }

    // Collision Balle <-> Ennemi
    bullets.forEach((bullet, bIndex) => {
        enemies.forEach((enemy, eIndex) => {
            if(enemy.alive) {
                if(bullet.x > enemy.x && 
                   bullet.x < enemy.x + enemyWidth && 
                   bullet.y > enemy.y && 
                   bullet.y < enemy.y + enemyHeight) {
                       
                    enemy.alive = false;
                    bullets.splice(bIndex, 1);
                    score += 10;
                    scoreEl.innerText = score;
                    
                    // Vérifier victoire
                    if(enemies.filter(e => e.alive).length === 0) {
                        gameWin();
                    }
                }
            }
        });
    });
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height); // Effacer
    drawPlayer();
    drawEnemies();
    drawBullets();
}

function gameLoop() {
    if(!gameActive) return;
    update();
    draw();
    animationId = requestAnimationFrame(gameLoop);
}

function startGame() {
    initGame();
    gameActive = true;
    startBtn.style.display = 'none'; // Cacher le bouton
    gameLoop();
}

function gameOver() {
    gameActive = false;
    cancelAnimationFrame(animationId);
    startBtn.innerHTML = "GAME OVER<br><small style='color:red'>Score: " + score + "</small><br><span style='font-size:16px; color:#fff'>Click to restart</span>";
    startBtn.style.display = 'block';
}

function gameWin() {
    gameActive = false;
    cancelAnimationFrame(animationId);
    startBtn.innerHTML = "YOU WIN!<br><small style='color:#0f0'>Score: " + score + "</small><br><span style='font-size:16px; color:#fff'>Click to play again</span>";
    startBtn.style.display = 'block';
}
</script>

<?php footer(); ?>