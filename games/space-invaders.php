<?php
include "../core.php";
$pagetitle = "Space Invaders Deluxe";
head();
?>

<style>
    #game-wrapper {
        position: relative;
        width: 800px;
        max-width: 100%;
        margin: 20px auto;
    }
    canvas {
        display: block;
        background: #000;
        border: 4px solid #333;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    #ui-layer {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    .btn-start {
        pointer-events: auto;
        font-family: 'Courier New', monospace;
        font-size: 24px;
        font-weight: bold;
        padding: 15px 40px;
        background: #28a745;
        color: white;
        border: 2px solid #fff;
        border-radius: 50px;
        cursor: pointer;
        text-transform: uppercase;
        box-shadow: 0 0 15px #28a745;
        transition: all 0.2s;
    }
    .btn-start:hover { transform: scale(1.1); }
    .hud {
        display: flex; justify-content: space-between;
        background: #222; color: #fff;
        padding: 10px 20px;
        font-family: 'Courier New', monospace;
        font-weight: bold;
        border-radius: 10px 10px 0 0;
        margin-bottom: -4px; /* Coller au canvas */
        width: 800px; max-width: 100%; margin: 0 auto;
    }
</style>

<div class="container mt-4 mb-5">
    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour Arcade</a>
    </div>

    <div class="hud">
        <span>SCORE: <span id="scoreEl" style="color:#0f0">0</span></span>
        <span>LIVES: <span id="livesEl" style="color:#f00">3</span></span>
    </div>

    <div id="game-wrapper">
        <canvas id="gameCanvas" width="800" height="500"></canvas>
        
        <div id="ui-layer">
            <button id="startBtn" class="btn-start" onclick="startGame()">START GAME</button>
            <div id="msg" style="margin-top:20px; color:white; font-size:20px; font-family:'Courier New'; text-shadow: 2px 2px 0 #000;"></div>
        </div>
    </div>
    
    <div class="text-center mt-2 text-muted small">
        <i class="fas fa-volume-up"></i> Son activé | Flèches pour bouger, Espace pour tirer.
    </div>
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const scoreEl = document.getElementById('scoreEl');
const livesEl = document.getElementById('livesEl');
const startBtn = document.getElementById('startBtn');
const msgEl = document.getElementById('msg');

// --- CHARGEMENT DES ASSETS ---
const sprites = {
    player: new Image(),
    enemy: new Image(),
    bg: new Image()
};
sprites.player.src = 'assets/img/invaders/player.png';
sprites.enemy.src = 'assets/img/invaders/alien.png';
sprites.bg.src = 'assets/img/invaders/bg.jpg';

const sounds = {
    shoot: new Audio('assets/sound/invaders/shoot.mp3'),
    explosion: new Audio('assets/sound/invaders/explosion.mp3'),
    music: new Audio('assets/sound/invaders/music.mp3')
};
sounds.music.loop = true;
sounds.music.volume = 0.5;
sounds.shoot.volume = 0.4;

// --- CONFIGURATION ---
let gameActive = false;
let animationId;
let score = 0;
let lives = 3;

const player = { x: 375, y: 450, w: 50, h: 50, speed: 5, dx: 0 };
let bullets = [];
let enemies = [];
let enemyDirection = 1;
let enemySpeed = 1; // Vitesse de base plus lente pour commencer

// Clavier
const keys = { Right: false, Left: false, Space: false };

document.addEventListener('keydown', e => {
    if(e.key === 'ArrowRight') keys.Right = true;
    if(e.key === 'ArrowLeft') keys.Left = true;
    if(e.code === 'Space') {
        if(!keys.Space && gameActive) { shoot(); keys.Space = true; }
        e.preventDefault();
    }
});
document.addEventListener('keyup', e => {
    if(e.key === 'ArrowRight') keys.Right = false;
    if(e.key === 'ArrowLeft') keys.Left = false;
    if(e.code === 'Space') keys.Space = false;
});

// --- LOGIQUE JEU ---

function initEnemies() {
    enemies = [];
    enemySpeed = 1.5; // Reset vitesse
    for(let c=0; c<8; c++) {
        for(let r=0; r<3; r++) {
            enemies.push({
                x: 50 + (c * 70),
                y: 50 + (r * 60),
                w: 40, h: 40,
                alive: true
            });
        }
    }
}

function shoot() {
    bullets.push({ x: player.x + player.w/2 - 2, y: player.y, w: 4, h: 10 });
    // Jouer le son (clone pour permettre tir rapide)
    if(sounds.shoot.readyState >= 2) sounds.shoot.cloneNode().play();
}

function update() {
    if(!gameActive) return;

    // Joueur
    if(keys.Right && player.x < canvas.width - player.w) player.x += player.speed;
    if(keys.Left && player.x > 0) player.x -= player.speed;

    // Balles
    bullets.forEach((b, i) => {
        b.y -= 7;
        if(b.y < 0) bullets.splice(i, 1);
    });

    // Ennemis
    let hitWall = false;
    enemies.forEach(e => {
        if(e.alive) {
            e.x += enemySpeed * enemyDirection;
            if(e.x + e.w > canvas.width || e.x < 0) hitWall = true;
        }
    });

    if(hitWall) {
        enemyDirection *= -1;
        enemies.forEach(e => e.y += 20);
        enemySpeed += 0.2; // Accélère à chaque descente
    }

    // Collisions
    bullets.forEach((b, bi) => {
        enemies.forEach((e, ei) => {
            if(e.alive && b.x > e.x && b.x < e.x + e.w && b.y > e.y && b.y < e.y + e.h) {
                e.alive = false;
                bullets.splice(bi, 1);
                score += 10;
                scoreEl.innerText = score;
                if(sounds.explosion.readyState >= 2) sounds.explosion.cloneNode().play();
                
                // Vérifier Victoire
                if(enemies.filter(en => en.alive).length === 0) {
                    levelUp();
                }
            }
        });
    });

    // Game Over (Ennemis touchent le bas)
    enemies.forEach(e => {
        if(e.alive && e.y + e.h >= player.y) gameOver();
    });
}

function levelUp() {
    // Relance une vague plus rapide
    initEnemies();
    enemySpeed += 1;
}

function draw() {
    // 1. Fond (Image ou Noir)
    if(sprites.bg.complete && sprites.bg.naturalWidth !== 0) {
        ctx.drawImage(sprites.bg, 0, 0, canvas.width, canvas.height);
    } else {
        ctx.fillStyle = '#000'; ctx.fillRect(0,0,canvas.width, canvas.height);
    }

    // 2. Joueur (Sprite ou Carré Vert)
    if(sprites.player.complete && sprites.player.naturalWidth !== 0) {
        ctx.drawImage(sprites.player, player.x, player.y, player.w, player.h);
    } else {
        ctx.fillStyle = '#0f0'; ctx.fillRect(player.x, player.y, player.w, player.h);
    }

    // 3. Ennemis (Sprite ou Carré Rouge)
    enemies.forEach(e => {
        if(e.alive) {
            if(sprites.enemy.complete && sprites.enemy.naturalWidth !== 0) {
                ctx.drawImage(sprites.enemy, e.x, e.y, e.w, e.h);
            } else {
                ctx.fillStyle = '#f00'; ctx.fillRect(e.x, e.y, e.w, e.h);
            }
        }
    });

    // 4. Balles
    ctx.fillStyle = '#ff0';
    bullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));
}

function loop() {
    if(!gameActive) return;
    update();
    draw();
    animationId = requestAnimationFrame(loop);
}

function startGame() {
    score = 0; scoreEl.innerText = score;
    lives = 3; livesEl.innerText = lives;
    bullets = [];
    player.x = 375;
    
    initEnemies();
    
    gameActive = true;
    startBtn.style.display = 'none';
    msgEl.innerText = "";
    
    // Lancer Musique (nécessite interaction utilisateur)
    sounds.music.currentTime = 0;
    sounds.music.play().catch(e => console.log("Audio autoplay bloqué"));
    
    loop();
}

function gameOver() {
    gameActive = false;
    cancelAnimationFrame(animationId);
    sounds.music.pause();
    startBtn.innerText = "REJOUER";
    startBtn.style.display = 'block';
    msgEl.innerHTML = "GAME OVER<br>Score Final : " + score;
}

// Dessin initial
ctx.fillStyle = '#000'; ctx.fillRect(0,0,canvas.width, canvas.height);
</script>

<?php footer(); ?>