(function () {
    const grid = document.querySelector('.grid');
    drawBoard();
    const squares = document.querySelectorAll('.grid div');
    const resultDisplay = document.querySelector('#result');
    const gameOverDisplay = document.getElementById('game-over-div');
    const defeatMessage = document.querySelector('#defeat-message');
    const congratsMessage = document.querySelector('#congrats-message');
    const levelDisplay = document.querySelector('#level');
    const topScores = JSON.parse(localStorage.getItem('spaceinvaders')) || [];
    let width = 15;
    let currentShooterIndex = 202;
    let currentInvaderIndex = 0;
    let alienInvaders = [];
    let alienInvadersTakenDown = [];
    let result = 0;
    let direction = 1;
    let invaderId;
    let gameInPlay = false;
    let gameEnded = true;
    let level = 1;
    showTopScores();

    function drawBoard() {
        for (let i = 0; i < 255; i++) {
            const square = document.createElement('div');
            grid.appendChild(square);
        }
    }

    // define the alien invaders
    function resetAlienInvaders() {
        alienInvaders = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
            15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
            30, 31, 32, 33, 34, 35, 36, 37, 38, 39
        ];
    }

    // draw the alien invaders
    function drawGameStart() {
        if (gameEnded) {
            congratsMessage.innerHTML = '';
            currentInvaderIndex = 0;
            direction = 1;
            resetAlienInvaders();
            alienInvadersTakenDown = [];
            alienInvaders.forEach(invader => {
                squares[currentInvaderIndex + invader].classList.add('invader')
            });
            gameEnded = false;
        }
        levelDisplay.innerHTML = level.toString();
        const speed = (Math.log10(Math.pow(level, -0.5)) + 1) * 1000;
        invaderId = setInterval(moveInvaders, speed);
    }

    function keyPressEvents(e) {
        switch (e.key) {
            case 'ArrowLeft':
            case 'ArrowRight':
                moveShooter(e.key);
                break;
            case ' ':
                shoot();
                break;
        }
    }

    // draw the shooter
    squares[currentShooterIndex].classList.add('shooter');

    // move the shooter along a line
    function moveShooter(key) {
        squares[currentShooterIndex].classList.remove('shooter');
        switch (key) {
            case 'ArrowLeft':
                if (currentShooterIndex % width !== 0) currentShooterIndex -= 1;
                break;
            case 'ArrowRight':
                if (currentShooterIndex % width < width - 1) currentShooterIndex += 1;
                break;
        }
        squares[currentShooterIndex].classList.add('shooter');
    }

    document.addEventListener('keyup', keyPressEvents);

    // move the alien invaders
    function moveInvaders() {
        const leftEdge = alienInvaders[0] % width === 0;
        const rightEdge = alienInvaders[alienInvaders.length - 1] % width === width - 1;
        if ((leftEdge && direction === -1) || (rightEdge && direction === 1)) {
            direction = width;
        } else if (direction === width) {
            if (leftEdge) direction = 1;
            else direction = -1;
        }
        for (let i = 0; i < alienInvaders.length; i++) {
            squares[alienInvaders[i]].classList.remove('invader');
        }
        for (let i = 0; i < alienInvaders.length; i++) {
            alienInvaders[i] += direction;
        }
        for (let i = 0; i < alienInvaders.length; i++) {
            if (!alienInvadersTakenDown.includes(i)) {
                squares[alienInvaders[i]].classList.add('invader');
            }
        }

        // decide if game is over
        if (squares[currentShooterIndex].classList.contains('invader', 'shooter')) {
            defeatMessage.innerHTML = "You've Been Hit!"
            squares[currentShooterIndex].classList.add('boom');
            showGameOverModal();
        }

        for (let i = 0; i < alienInvaders.length; i++) {
            if (alienInvaders[i] > (squares.length - (width - 1))) {
                defeatMessage.innerHTML = "The Aliens Have Taken Over!"
                showGameOverModal();
                break;
            }
        }

        // decide a win
        if (alienInvadersTakenDown.length === alienInvaders.length) {
            clearInterval(invaderId);
            level++;
            gameEnded = true;
            drawGameStart();
        }
    }

    function showGameOverModal() {
        const date = getDate();
        const scoreInfo = {date: date, score: result, level: level};
        topScores.push(scoreInfo);
        const index = showTopScores(scoreInfo);
        clearInterval(invaderId);
        gameEnded = true;
        gameInPlay = false;
        congratsMessage.innerHTML = index > -1 ? `Congrats #${index + 1} score!` : '';
        gameOverDisplay.style.display = 'block';
    }

    function getDate() {
        let date = new Date();
        date = date.toISOString().split('T')[0];
        date = date.split('-');
        date = `${date[2]}-${date[1]}-${date[0]}`;
        return date;
    }

    function showTopScores(scoreInfo) {
        topScores.sort((a, b) => b.score - a.score);
        const topFive = topScores.filter((item, index) => index < 5);
        localStorage.spaceinvaders = JSON.stringify(topFive);
        const index = topFive.findIndex(s => s === scoreInfo);
        let markup = '';
        topFive.forEach(s => {
            markup += `
            <tr>
                <td class="scores" nowrap="nowrap">${s.date}</td><td class="scores">${s.level}</td><td class="scores">${s.score}</td>
            </tr>
            `;
        });
        document.querySelector('#top-scores').innerHTML = markup;
        return index;
    }

    // shoot at aliens
    function shoot() {
        let laserId;
        let currentLaserIndex = currentShooterIndex;
        laserId = setInterval(moveLaser, 100);

        // move the laser from the shooter to the alien invader
        function moveLaser() {
            squares[currentLaserIndex].classList.remove('laser');
            currentLaserIndex -= width;
            if (currentLaserIndex > 0) {
                squares[currentLaserIndex].classList.add('laser');
                if (squares[currentLaserIndex].classList.contains('invader')) {
                    squares[currentLaserIndex].classList.remove('laser');
                    squares[currentLaserIndex].classList.remove('invader');
                    squares[currentLaserIndex].classList.add('boom');
                    setTimeout(() => squares[currentLaserIndex].classList.remove('boom'), 250);
                    clearInterval(laserId);
                    const alienTakeDown = alienInvaders.indexOf(currentLaserIndex);
                    alienInvadersTakenDown.push(alienTakeDown);
                    result += 10 + level - 1;
                    resultDisplay.textContent = result.toString();
                }
            } else {
                clearInterval(laserId);
            }
        }
    }

    const startBtn = document.getElementById('start-btn');
    startBtn.addEventListener('click', function () {
        this.blur();
        if (invaderId) {
            clearInterval(invaderId);
            invaderId = null;
            gameInPlay = false;
        } else {
            if (gameEnded) {
                squares.forEach(square => {
                    square.classList.remove('invader');
                });
                result = 0;
                resultDisplay.textContent = result.toString();
                resetAlienInvaders();
            }
            drawGameStart();
            gameInPlay = true;
        }
    });

    // close any open modals
    const closeBtn = document.querySelectorAll('.close-btn');
    closeBtn.forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.modal').forEach(el => {
                el.style.display = 'none';
            });
        });
    });
}());