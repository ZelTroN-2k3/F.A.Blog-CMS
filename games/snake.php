<?php
include "../core.php";
$pagetitle = "Snake Deluxe";
head();
?>

<style>
    .game-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #2c3e50;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        max-width: 650px;
        margin: 20px auto;
    }
    
    canvas {
        background-color: #000;
        border: 4px solid #ecf0f1;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        display: block;
        border-radius: 4px;
    }
    
    .hud {
        width: 100%;
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        background: #34495e;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-family: 'Courier New', monospace;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .btn-start {
        margin-top: 15px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 10px 30px;
        border-radius: 50px;
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour Arcade</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="game-wrapper">
                
                <div class="hud">
                    <span><i class="fas fa-apple-alt text-danger"></i> SCORE: <span id="score" class="text-warning">0</span></span>
                    <span><i class="fas fa-trophy text-warning"></i> BEST: <span id="highscore">0</span></span>
                </div>

                <canvas id="gameCanvas" width="600" height="400"></canvas> <button id="startBtn" class="btn btn-success btn-lg btn-start shadow" onclick="startGame()">
                    <i class="fas fa-play me-2"></i> JOUER
                </button>
                
                <div class="text-white-50 mt-3 small text-center">
                    Utilisez les flèches <span class="badge bg-light text-dark">←</span> <span class="badge bg-light text-dark">↑</span> <span class="badge bg-light text-dark">↓</span> <span class="badge bg-light text-dark">→</span> pour diriger le serpent.
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

// --- ASSETS ---
const assets = {
    head: new Image(),
    body: new Image(),
    apple: new Image(),
    bg: new Image(),
    soundEat: new Audio('assets/sound/snake/eat.mp3'),
    soundDie: new Audio('assets/sound/snake/die.mp3')
};
assets.head.src = 'assets/img/snake/snake_head.png';
assets.body.src = 'assets/img/snake/snake_body.png';
assets.apple.src = 'assets/img/snake/apple.png';
assets.bg.src = 'assets/img/snake/grass.jpg';

// --- CONFIGURATION ---
const gridSize = 25; // Plus gros pour les sprites
const tileCountX = canvas.width / gridSize;
const tileCountY = canvas.height / gridSize;

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
let speed = 10; // FPS cible

// --- MOTEUR ---

function gameLoop() {
    // Déplacement
    playerX += velocityX;
    playerY += velocityY;

    // Traversée des murs (Mode "Sans fin")
    if (playerX < 0) playerX = tileCountX - 1;
    if (playerX > tileCountX - 1) playerX = 0;
    if (playerY < 0) playerY = tileCountY - 1;
    if (playerY > tileCountY - 1) playerY = 0;

    // DESSIN FOND
    if(assets.bg.complete && assets.bg.naturalWidth !== 0) {
        // Pattern répété pour l'herbe
        const ptrn = ctx.createPattern(assets.bg, 'repeat');
        ctx.fillStyle = ptrn;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else {
        ctx.fillStyle = "#27ae60"; // Vert herbe fallback
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // DESSIN POMME
    if(assets.apple.complete && assets.apple.naturalWidth !== 0) {
        ctx.drawImage(assets.apple, appleX * gridSize, appleY * gridSize, gridSize, gridSize);
    } else {
        ctx.fillStyle = "red";
        ctx.beginPath();
        ctx.arc(appleX * gridSize + gridSize/2, appleY * gridSize + gridSize/2, gridSize/2 -2, 0, Math.PI*2);
        ctx.fill();
    }

    // DESSIN SERPENT (Corps)
    for (let i = 0; i < snake.length; i++) {
        let partX = snake[i].x * gridSize;
        let partY = snake[i].y * gridSize;

        // Collision queue (Game Over)
        if (snake[i].x === playerX && snake[i].y === playerY && tailLength > 5) {
            gameOver();
            return;
        }

        if(assets.body.complete && assets.body.naturalWidth !== 0) {
            ctx.drawImage(assets.body, partX, partY, gridSize, gridSize);
        } else {
            ctx.fillStyle = "#f1c40f"; // Jaune fallback
            ctx.fillRect(partX, partY, gridSize-2, gridSize-2);
        }
    }

    // DESSIN TÊTE (Avec rotation)
    let headX = playerX * gridSize;
    let headY = playerY * gridSize;
    
    ctx.save(); // Sauvegarder contexte
    ctx.translate(headX + gridSize/2, headY + gridSize/2); // Aller au centre de la case
    
    // Calcul de l'angle
    let angle = 0;
    if(velocityX === 1) angle = 0;          // Droite
    if(velocityX === -1) angle = Math.PI;   // Gauche
    if(velocityY === 1) angle = Math.PI/2;  // Bas
    if(velocityY === -1) angle = -Math.PI/2;// Haut
    
    ctx.rotate(angle); // Tourner

    if(assets.head.complete && assets.head.naturalWidth !== 0) {
        // Dessiner centré
        ctx.drawImage(assets.head, -gridSize/2, -gridSize/2, gridSize, gridSize);
    } else {
        ctx.fillStyle = "#e67e22"; // Orange fallback
        ctx.fillRect(-gridSize/2, -gridSize/2, gridSize-2, gridSize-2);
    }
    ctx.restore(); // Rétablir contexte

    // LOGIQUE QUEUE
    snake.push({ x: playerX, y: playerY });
    while (snake.length > tailLength) {
        snake.shift();
    }

    // MANGER POMME
    if (appleX === playerX && appleY === playerY) {
        tailLength++;
        score += 10;
        scoreEl.innerText = score;
        
        // Son
        if(assets.soundEat.readyState >= 2) assets.soundEat.cloneNode().play().catch(() => {});

        // Accélération légère
        if(score % 50 === 0) { 
            speed++; 
            clearInterval(gameInterval); 
            gameInterval = setInterval(gameLoop, 1000 / speed); 
        }

        placeApple();
    }
}

function placeApple() {
    appleX = Math.floor(Math.random() * tileCountX);
    appleY = Math.floor(Math.random() * tileCountY);
    // Vérif anti-spawn sur serpent
    for(let part of snake) {
        if(part.x === appleX && part.y === appleY) { placeApple(); return; }
    }
}

// Contrôles
document.addEventListener("keydown", e => {
    if(!isRunning) return;
    switch (e.keyCode) {
        case 37: if (velocityX !== 1) { velocityX = -1; velocityY = 0; } break; // Gauche
        case 38: if (velocityY !== 1) { velocityX = 0; velocityY = -1; } break; // Haut
        case 39: if (velocityX !== -1) { velocityX = 1; velocityY = 0; } break; // Droite
        case 40: if (velocityY !== -1) { velocityX = 0; velocityY = 1; } break; // Bas
    }
});

function startGame() {
    if(isRunning) return;
    
    // Reset
    playerX = 10; playerY = 10;
    velocityX = 1; velocityY = 0; // Commence en bougeant à droite
    snake = [];
    tailLength = 5;
    score = 0;
    speed = 10;
    scoreEl.innerText = score;
    placeApple();
    
    isRunning = true;
    startBtn.classList.add("disabled");
    startBtn.innerHTML = "EN COURS...";
    
    // Précharger son (pour mobile)
    assets.soundEat.load();
    assets.soundDie.load();

    gameInterval = setInterval(gameLoop, 1000 / speed);
}

/*function gameOver() {
    clearInterval(gameInterval);
    isRunning = false;
    
    if(assets.soundDie.readyState >= 2) assets.soundDie.play().catch(() => {});

    if(score > highScore) {
        highScore = score;
        localStorage.setItem('snake_highscore', highScore);
        highscoreEl.innerText = highScore;
    }
    
    alert("PERDU ! Score: " + score);
    startBtn.classList.remove("disabled");
    startBtn.innerHTML = '<i class="fas fa-redo me-2"></i> REJOUER';
}*/

function gameOver() {
    clearInterval(gameInterval);
    isRunning = false;
    
    if(assets.soundDie.readyState >= 2) assets.soundDie.play().catch(() => {});

    // --- DEBUT MODIFICATION GAMIFICATION ---
    // Envoi du score au serveur
    const formData = new FormData();
    formData.append('game', 'snake'); // Identifiant du jeu
    formData.append('score', score);

    fetch('../ajax_submit_score.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            let msg = "Score sauvegardé !";
            // Si le joueur a gagné des badges
            if(data.new_badges.length > 0) {
                msg += "\n🏆 NOUVEAU BADGE DÉBLOQUÉ : " + data.new_badges.join(', ');
            }
            alert("PERDU ! Score: " + score + "\n" + msg);
        } else if (data.message === 'Login required') {
            alert("PERDU ! Score: " + score + "\n(Connectez-vous pour sauvegarder votre score)");
        } else {
            alert("PERDU ! Score: " + score);
        }
    })
    .catch(error => console.error('Error:', error));
    // --- FIN MODIFICATION ---

    // Gestion HighScore Local (votre ancien code)
    if(score > highScore) {
        highScore = score;
        localStorage.setItem('snake_highscore', highScore);
        highscoreEl.innerText = highScore;
    }
    
    startBtn.classList.remove("disabled");
    startBtn.innerHTML = '<i class="fas fa-redo me-2"></i> REJOUER';
}

// Ecran noir initial
ctx.fillStyle = "#2c3e50";
ctx.fillRect(0, 0, canvas.width, canvas.height);
ctx.font = "20px Arial";
ctx.fillStyle = "white";
ctx.textAlign = "center";
ctx.fillText("Appuyez sur JOUER", canvas.width/2, canvas.height/2);

</script>

<?php footer(); ?>