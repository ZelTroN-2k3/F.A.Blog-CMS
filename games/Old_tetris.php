<?php
include "../core.php";

$pagetitle = "Tetris - Arcade";
head();
?>

<style>
    .tetris-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        background-color: #202028;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        max-width: 700px;
        margin: 20px auto;
        color: #fff;
        font-family: 'Courier New', monospace;
    }

    canvas {
        border: 2px solid #333;
        background-color: #000;
        box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }

    .info-panel {
        display: flex;
        flex-direction: column;
        min-width: 150px;
    }

    .info-box {
        background: #333;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
    }

    .info-label { color: #aaa; font-size: 0.9rem; display: block; margin-bottom: 5px; }
    .info-value { font-size: 1.5rem; font-weight: bold; color: #0dcaf0; }

    .btn-controls {
        margin-top: auto;
    }
</style>

<div class="container mt-4 mb-5">
    
    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour Arcade</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="tetris-wrapper">
                
                <canvas id="tetris" width="240" height="400"></canvas>
                
                <div class="info-panel">
                    <div class="info-box">
                        <span class="info-label">SCORE</span>
                        <span class="info-value" id="score">0</span>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-label">LEVEL</span>
                        <span class="info-value" id="level">1</span>
                    </div>

                    <div class="btn-controls d-grid gap-2">
                        <button class="btn btn-primary fw-bold" onclick="playerReset(); update();" id="startBtn">START</button>
                        <div class="text-muted small mt-2">
                            <i class="fas fa-arrow-left"></i> <i class="fas fa-arrow-right"></i> Move<br>
                            <i class="fas fa-arrow-down"></i> Drop<br>
                            <i class="fas fa-arrow-up"></i> Rotate
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('tetris');
const context = canvas.getContext('2d');
const scoreElement = document.getElementById('score');
const levelElement = document.getElementById('level');

context.scale(20, 20); // Echelle x20 pour avoir des blocs de 20px

// --- LOGIQUE TETROMINOS ---
function arenaSweep() {
    let rowCount = 1;
    outer: for (let y = arena.length - 1; y > 0; --y) {
        for (let x = 0; x < arena[y].length; ++x) {
            if (arena[y][x] === 0) {
                continue outer;
            }
        }
        const row = arena.splice(y, 1)[0].fill(0);
        arena.unshift(row);
        ++y;

        player.score += rowCount * 10;
        rowCount *= 2;
    }
    // Level Up
    player.level = Math.floor(player.score / 100) + 1;
    updateScore();
}

function collide(arena, player) {
    const m = player.matrix;
    const o = player.pos;
    for (let y = 0; y < m.length; ++y) {
        for (let x = 0; x < m[y].length; ++x) {
            if (m[y][x] !== 0 &&
               (arena[y + o.y] &&
                arena[y + o.y][x + o.x]) !== 0) {
                return true;
            }
        }
    }
    return false;
}

function createMatrix(w, h) {
    const matrix = [];
    while (h--) {
        matrix.push(new Array(w).fill(0));
    }
    return matrix;
}

function createPiece(type) {
    if (type === 'I') {
        return [
            [0, 1, 0, 0],
            [0, 1, 0, 0],
            [0, 1, 0, 0],
            [0, 1, 0, 0],
        ];
    } else if (type === 'L') {
        return [
            [0, 2, 0],
            [0, 2, 0],
            [0, 2, 2],
        ];
    } else if (type === 'J') {
        return [
            [0, 3, 0],
            [0, 3, 0],
            [3, 3, 0],
        ];
    } else if (type === 'O') {
        return [
            [4, 4],
            [4, 4],
        ];
    } else if (type === 'Z') {
        return [
            [5, 5, 0],
            [0, 5, 5],
            [0, 0, 0],
        ];
    } else if (type === 'S') {
        return [
            [0, 6, 6],
            [6, 6, 0],
            [0, 0, 0],
        ];
    } else if (type === 'T') {
        return [
            [0, 7, 0],
            [7, 7, 7],
            [0, 0, 0],
        ];
    }
}

function drawMatrix(matrix, offset) {
    matrix.forEach((row, y) => {
        row.forEach((value, x) => {
            if (value !== 0) {
                // Couleurs basiques
                const colors = [
                    null,
                    '#FF0D72', // T
                    '#0DC2FF', // I
                    '#0DFF72', // S
                    '#F538FF', // Z
                    '#FF8E0D', // L
                    '#FFE138', // J
                    '#3877FF', // O
                ];
                context.fillStyle = colors[value];
                context.fillRect(x + offset.x, y + offset.y, 1, 1);
                
                // Effet de bordure pour le style
                context.lineWidth = 0.05;
                context.strokeStyle = 'white';
                context.strokeRect(x + offset.x, y + offset.y, 1, 1);
            }
        });
    });
}

function draw() {
    context.fillStyle = '#000';
    context.fillRect(0, 0, canvas.width, canvas.height);
    drawMatrix(arena, {x: 0, y: 0});
    drawMatrix(player.matrix, player.pos);
}

function merge(arena, player) {
    player.matrix.forEach((row, y) => {
        row.forEach((value, x) => {
            if (value !== 0) {
                arena[y + player.pos.y][x + player.pos.x] = value;
            }
        });
    });
}

function rotate(matrix, dir) {
    for (let y = 0; y < matrix.length; ++y) {
        for (let x = 0; x < y; ++x) {
            [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
        }
    }
    if (dir > 0) {
        matrix.forEach(row => row.reverse());
    } else {
        matrix.reverse();
    }
}

function playerDrop() {
    player.pos.y++;
    if (collide(arena, player)) {
        player.pos.y--;
        merge(arena, player);
        playerReset();
        arenaSweep();
        updateScore();
    }
    dropCounter = 0;
}

function playerMove(offset) {
    player.pos.x += offset;
    if (collide(arena, player)) {
        player.pos.x -= offset;
    }
}

function playerReset() {
    const pieces = 'ILJOTSZ';
    player.matrix = createPiece(pieces[pieces.length * Math.random() | 0]);
    player.pos.y = 0;
    player.pos.x = (arena[0].length / 2 | 0) - (player.matrix[0].length / 2 | 0);
    
    // Game Over
    if (collide(arena, player)) {
        arena.forEach(row => row.fill(0));
        player.score = 0;
        player.level = 1;
        updateScore();
        alert("GAME OVER!");
    }
}

function playerRotate(dir) {
    const pos = player.pos.x;
    let offset = 1;
    rotate(player.matrix, dir);
    while (collide(arena, player)) {
        player.pos.x += offset;
        offset = -(offset + (offset > 0 ? 1 : -1));
        if (offset > player.matrix[0].length) {
            rotate(player.matrix, -dir);
            player.pos.x = pos;
            return;
        }
    }
}

let dropCounter = 0;
let dropInterval = 1000;
let lastTime = 0;

function update(time = 0) {
    const deltaTime = time - lastTime;
    
    // Vitesse augmente avec le niveau
    dropInterval = 1000 - (player.level * 50);
    if(dropInterval < 100) dropInterval = 100;

    lastTime = time;
    dropCounter += deltaTime;
    if (dropCounter > dropInterval) {
        playerDrop();
    }
    draw();
    requestAnimationFrame(update);
}

function updateScore() {
    scoreElement.innerText = player.score;
    levelElement.innerText = player.level;
}

const arena = createMatrix(12, 20);

const player = {
    pos: {x: 0, y: 0},
    matrix: null,
    score: 0,
    level: 1,
};

document.addEventListener('keydown', event => {
    if (event.keyCode === 37) { // Gauche
        playerMove(-1);
    } else if (event.keyCode === 39) { // Droite
        playerMove(1);
    } else if (event.keyCode === 40) { // Bas
        playerDrop();
    } else if (event.keyCode === 38) { // Haut (Rotate)
        playerRotate(1);
    }
});

// Ne pas lancer auto, attendre clic
playerReset();
draw();
</script>

<?php footer(); ?>