(function () {
    const grid = document.querySelector('.grid');
    const gameOverDisplay = document.getElementById('game-over-div');
    const closeBtn = document.querySelectorAll('.close-btn');
    const topScores = JSON.parse(localStorage.getItem('minesweeper')) || [];
    showTopTimes();
    let width = 10;
    let bombAmount = 20;
    let squares = [];
    let isGameOver = false;
    let flags = 0;
    let startTime = null;
    let timer;

    document.getElementById('new-game').addEventListener('click', function () {
        document.querySelectorAll('.modal').forEach(el => {
            el.style.display = 'none';
        });
        createBoard();
        startTime = Math.floor(Date.now() / 1000); //Get the starting time (right now) in seconds
        timeCounter(startTime, true);
    });

    document.getElementById('number-of-bombs').onchange = function () {
        bombAmount = parseInt(this.value);
        resetGame();
    };

    // create board
    function createBoard() {
        resetGame();
        // get shuffled game array with random bombs
        const bombsArray = Array(bombAmount).fill('bomb');
        const emptyArray = Array(width * width - bombAmount).fill('valid');
        const gameArray = emptyArray.concat(bombsArray);
        const shuffledArray = shuffle(gameArray);

        for (let i = 0; i < width * width; i++) {
            const square = document.createElement('div');
            square.setAttribute('id', i.toString());
            square.classList.add(shuffledArray[i]);
            grid.appendChild(square);
            squares.push(square);

            // normal click;
            square.addEventListener('click', function (e) {
                click(square);
            });

            // ctrl and left click
            square.oncontextmenu = function (e) {
                e.preventDefault();
                addFlag(square);
            }
        }

        // add numbers
        for (let i = 0; i < squares.length; i++) {
            let total = 0;
            const isLeftEdge = i % width === 0;
            const isRightEdge = i % width === width - 1;

            if (squares[i].classList.contains('valid')) {
                if (i > 0 && !isLeftEdge && squares[i - 1].classList.contains('bomb')) total++;
                if (i > 9 && !isRightEdge && squares[i + 1 - width].classList.contains('bomb')) total++;
                if (i > 10 && squares[i - width].classList.contains('bomb')) total++;
                if (i > 11 && !isLeftEdge && squares[i - 1 - width].classList.contains('bomb')) total++;
                if (i < 98 && !isRightEdge && squares[i + 1].classList.contains('bomb')) total++;
                if (i < 90 && !isLeftEdge && squares[i - 1 + width].classList.contains('bomb')) total++;
                if (i < 88 && !isRightEdge && squares[i + 1 + width].classList.contains('bomb')) total++;
                if (i < 90 && squares[i + width].classList.contains('bomb')) total++;
                squares[i].setAttribute('data', total);
            }
        }
    }

    function resetGame() {
        isGameOver = false;
        clearTimeout(timer);
        grid.innerHTML = '';
        squares = [];
        flags = 0;
        document.getElementById('no-of-flags').innerText = flags.toString();
    }

// add Flag with right click
    function addFlag(square) {
        if (isGameOver) return;
        if (!square.classList.contains('checked')) {
            if (!square.classList.contains('flag')) {
                square.classList.add('flag');
                square.innerHTML = '<i class="fas fa-flag"></i>';
                flags++;
                checkForWin();
            } else {
                square.classList.remove('flag');
                square.innerHTML = '';
                flags--;
            }
        }
        document.getElementById('no-of-flags').innerText = flags.toString();
    }

    // click on square actions
    function click(square) {
        if (square.classList.contains('checked') || square.classList.contains('flag') || isGameOver) return;
        if (square.classList.contains('bomb')) {
            gameOver(square);
        } else {
            let total = parseInt(square.getAttribute('data'));
            if (total !== 0) {
                square.classList.add('checked');
                square.innerHTML = total;
                return;
            }
            checkSquare(square, parseInt(square.id));
        }
        square.classList.add('checked');
    }

    // check neighbouring squares once square is clicked
    function checkSquare(square, currentId) {
        const isLeftEdge = currentId % width === 0;
        const isRightEdge = currentId % width === width - 1;

        setTimeout(() => {
            if (currentId > 0 && !isLeftEdge) {
                const newId = parseInt(currentId) - 1;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId > 9 && !isRightEdge) {
                const newId = parseInt(currentId) + 1 - width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId > 10) {
                const newId = parseInt(currentId) - width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId > 11 && !isLeftEdge) {
                const newId = parseInt(currentId) - 1 - width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId < 98 && !isRightEdge) {
                const newId = parseInt(currentId) + 1;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId < 90 && !isLeftEdge) {
                const newId = parseInt(currentId) - 1 + width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId < 88 && !isRightEdge) {
                const newId = parseInt(currentId) + 1 + width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
            if (currentId < 90) {
                const newId = parseInt(currentId) + width;
                const newSquare = document.getElementById(newId.toString());
                click(newSquare);
            }
        }, 10);
    }

    function gameOver(square) {
        clearTimeout(timer);
        document.getElementById('congrats-message').innerHTML = "BOOM! You Lose!";
        gameOverDisplay.style.display = 'block';
        isGameOver = true;

        // show ALL the bombs
        squares.forEach(square => {
            if (square.classList.contains('bomb')) {
                square.innerHTML = '<i class="fas fa-bomb"></i>';
                square.style.color = 'black';
                square.style.backgroundColor = 'red';
            }
        })
    }

    function checkForWin() {
        let matches = 0;
        for (let i = 0; i < squares.length; i++) {
            if (squares[i].classList.contains('flag') && squares[i].classList.contains('bomb')) {
                matches++;
            }
        }
        if (flags === matches && matches === bombAmount) {
            clearTimeout(timer);
            document.getElementById('congrats-message').innerHTML = "Congrats! You Win!";
            const scoreInfo = {
                'date': getDate(),
                'bombs': bombAmount,
                'time': document.getElementById('timer-display').innerHTML
            };
            topScores.push(scoreInfo);
            const position = showTopTimes(scoreInfo);
            document.querySelector('#congrats-message').innerHTML = position > -1 ? `Congrats! #${position + 1} score!` : '';
            gameOverDisplay.style.display = 'block';
            isGameOver = true;
        }
    }

    function getDate() {
        let date = new Date();
        date = date.toISOString().split('T')[0];
        date = date.split('-');
        date = `${date[2]}-${date[1]}-${date[0]}`;
        return date;
    }

    function showTopTimes(scoreInfo) {
        topScores.sort((a, b) => new Date('1970/1/1 ' + a.time) - new Date('1970/1/1 ' + b.time) || b.bombs - a.bombs);
        const topFive = topScores.filter((item, index) => index < 5);
        const index = topFive.findIndex(s => s === scoreInfo);
        localStorage.minesweeper = JSON.stringify(topFive);
        let markup = '';
        topFive.forEach(s => {
            markup += `<tr>
                <td class="scores" nowrap="nowrap">${s.date}</td><td class="scores">${s.bombs}</td><td class="scores">${s.time}</td>
            </tr>`;
        });
        document.querySelector('#top-times-tbl').innerHTML = markup;
        return index;
    }

// close any open modals
    closeBtn.forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.modal').forEach(el => {
                el.style.display = 'none';
            });
        });
    });

    function shuffle(a) {
        for (let i = a.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [a[i], a[j]] = [a[j], a[i]];
        }
        return a;
    }

    function timeCounter(startTime, start) {
        const now = Math.floor(Date.now() / 1000); // get the time now
        const diff = now - startTime; // diff in seconds between now and start
        let m = Math.floor(diff / 60); // get minutes value (quotient of diff)
        let s = Math.floor(diff % 60); // get seconds value (remainder of diff)
        m = checkTime(m); // add a leading zero if it's single digit
        s = checkTime(s); // add a leading zero if it's single digit
        document.getElementById("timer-display").innerHTML = m + ":" + s; // update the element where the timer will appear
        if (start) {
            timer = setTimeout(function () {
                timeCounter(startTime, true);
            }, 1000); // set a timeout to update the timer
        }
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }  // add zero in front of numbers < 10
        return i;
    }
}());