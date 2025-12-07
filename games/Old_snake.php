<?php
include "../core.php";

$pagetitle = "Snake - Arcade";
head();
?>

<style>
    .game-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #222;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        max-width: 600px;
        margin: 20px auto;
    }
    
    canvas {
        background-color: #000;
        border: 2px solid #444;
        box-shadow: inset 0 0 20px rgba(0,255,0,0.1);
        display: block;
    }
    
    .score-panel {
        font-family: 'Courier New', Courier, monospace;
        color: #0f0;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        width: 100%;
        display: flex;
        justify-content: space-between;
    }
    
    .btn-start {
        margin-top: 15px;
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour Arcade</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="game-wrapper">
                
                <div class="score-panel">
                    <span>SCORE: <span id="score">0</span></span>
                    <span>HIGHSCORE: <span id="highscore">0</span></span>
                </div>

                <canvas id="gameCanvas" width="400" height="400"></canvas>
                
                <button id="startBtn" class="btn btn-success btn-lg btn-start px-5" onclick="startGame()">
                    <i class="fas fa-play me-2"></i> JOUER
                </button>
                
                <div class="text-white-50 mt-2 small">
                    Utilisez les flèches <i class="fas fa-arrows-alt"></i> pour diriger le serpent.
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");
const scoreEl = document.getElementById("score");
const highscoreEl = document.getElementById("highscore");
const startBtn = document.getElementById("startBtn");

// --- CONFIGURATION ---
const gridSize = 20; // Taille d'une case
const tileCount = canvas.width / gridSize; // Nombre de cases (20x20)
let speed = 7; // Vitesse initiale (images par seconde)

let score = 0;
let highScore = localStorage.getItem('snake_highscore') || 0;
highscoreEl.innerText = highScore;

let velocityX = 0;
let velocityY = 0;
let playerX = 10;
let playerY = 10;

let snake = [];
let tailLength = 5;

let appleX = 5;
let appleY = 5;

let gameInterval;
let isRunning = false;

// --- BOUCLE DE JEU ---
function gameLoop() {
    // Déplacement
    playerX += velocityX;
    playerY += velocityY;

    // Traversée des murs (Pacman style)
    if (playerX < 0) playerX = tileCount - 1;
    if (playerX > tileCount - 1) playerX = 0;
    if (playerY < 0) playerY = tileCount - 1;
    if (playerY > tileCount - 1) playerY = 0;

    // Fond noir
    ctx.fillStyle = "black";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Dessiner le Serpent
    ctx.fillStyle = "lime";
    for (let i = 0; i < snake.length; i++) {
        // Ombre légère pour effet rétro
        ctx.shadowBlur = 5;
        ctx.shadowColor = "lime";
        ctx.fillRect(snake[i].x * gridSize, snake[i].y * gridSize, gridSize - 2, gridSize - 2);
        ctx.shadowBlur = 0;

        // Collision avec soi-même
        if (snake[i].x === playerX && snake[i].y === playerY && tailLength > 5) {
            gameOver();
            return; // Arrêt boucle
        }
    }

    // Gestion de la queue
    snake.push({ x: playerX, y: playerY });
    while (snake.length > tailLength) {
        snake.shift();
    }

    // Dessiner la Pomme
    ctx.fillStyle = "red";
    ctx.shadowBlur = 10;
    ctx.shadowColor = "red";
    ctx.fillRect(appleX * gridSize, appleY * gridSize, gridSize - 2, gridSize - 2);
    ctx.shadowBlur = 0;

    // Manger la Pomme
    if (appleX === playerX && appleY === playerY) {
        tailLength++;
        score += 10;
        scoreEl.innerText = score;
        
        // Accélération légère tous les 50 points
        if(score % 50 === 0) { speed += 1; clearInterval(gameInterval); gameInterval = setInterval(gameLoop, 1000 / speed); }

        placeApple();
    }
}

// Placer une pomme au hasard
function placeApple() {
    appleX = Math.floor(Math.random() * tileCount);
    appleY = Math.floor(Math.random() * tileCount);
    
    // Vérifier que la pomme n'est pas SUR le serpent
    for(let part of snake) {
        if(part.x === appleX && part.y === appleY) {
            placeApple(); // Réessayer
            return;
        }
    }
}

// Contrôles Clavier
document.addEventListener("keydown", keyDownEvent);

function keyDownEvent(e) {
    if(!isRunning) return; // Ne pas bouger si pas commencé

    switch (e.keyCode) {
        case 37: // Gauche
            if (velocityX !== 1) { velocityX = -1; velocityY = 0; }
            break;
        case 38: // Haut
            if (velocityY !== 1) { velocityX = 0; velocityY = -1; }
            break;
        case 39: // Droite
            if (velocityX !== -1) { velocityX = 1; velocityY = 0; }
            break;
        case 40: // Bas
            if (velocityY !== -1) { velocityX = 0; velocityY = 1; }
            break;
    }
}

function startGame() {
    if(isRunning) return;
    
    // Reset
    playerX = 10; playerY = 10;
    velocityX = 0; velocityY = 0;
    snake = [];
    tailLength = 5;
    score = 0;
    speed = 7;
    scoreEl.innerText = score;
    placeApple();
    
    isRunning = true;
    startBtn.classList.add("disabled");
    startBtn.innerHTML = "JEU EN COURS...";
    
    gameInterval = setInterval(gameLoop, 1000 / speed);
}

function gameOver() {
    clearInterval(gameInterval);
    isRunning = false;
    
    // Gestion HighScore
    if(score > highScore) {
        highScore = score;
        localStorage.setItem('snake_highscore', highScore);
        highscoreEl.innerText = highScore;
        alert("NOUVEAU RECORD ! Score: " + score);
    } else {
        alert("PERDU ! Score: " + score);
    }
    
    startBtn.classList.remove("disabled");
    startBtn.innerHTML = '<i class="fas fa-redo me-2"></i> REJOUER';
}

// Dessin initial (écran noir)
ctx.fillStyle = "black";
ctx.fillRect(0, 0, canvas.width, canvas.height);
</script>

<?php footer(); ?>