CREATE TABLE IF NOT EXISTS games
(
    id   INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL
);

INSERT INTO games (name, code)
VALUES ('Minesweeper', 'minesweeper'),
       ('Space Invaders', 'spaceinvaders'),
       ('Tetris', 'tetris');