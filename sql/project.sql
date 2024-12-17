CREATE DATABASE project;

USE project;

CREATE TABLE IF NOT EXISTS icons
(
    icon_id INT(2)       NOT NULL PRIMARY KEY AUTO_INCREMENT,
    icon    VARCHAR(255) NOT NULL,
    name    VARCHAR(255) NOT NULL,
    colour  VARCHAR(255)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO icons (icon, name)
VALUES ('<i class="bi bi-phone-vibrate"></i>', 'Mobile'),
       ('<i class="bi bi-keyboard"></i>', 'Keyboard'),
       ('<i class="bi bi-mouse2-fill"></i>', 'Mouse'),
       ('<i class="bi bi-headphones"></i>', 'Headphones'),
       ('<i class="bi bi-laptop"></i>', 'Laptop'),
       ('<i class="bi bi-code-slash"></i>', 'Code'),
       ('<i class="bi bi-file-earmark-word"></i>', 'File'),
       ('<i class="bi bi-folder2-open"></i>', 'Folder'),
       ('<i class="bi bi-bug"></i>', 'Bug'),
       ('<i class="bi bi-battery-half"></i>', 'Battery'),
       ('<i class="bi bi-wifi"></i>', 'Wifi'),
       ('<i class="bi bi-lock"></i>', 'Padlock'),
       ('<i class="bi bi-envelope-at"></i>', 'Email'),
       ('<i class="bi bi-cpu"></i>', 'CPU'),
       ('<i class="bi bi-pc-display-horizontal"></i>', 'Desktop Computer');

CREATE TABLE IF NOT EXISTS users(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    uuid VARCHAR(40) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(255) UNIQUE,
    passcode_route VARCHAR(5),
    login_attempts INT NOT NULL DEFAULT 0,
    login_date DATETIME,
    magiC_link VARCHAR(255),
    phone_code VARCHAR(6)
);

INSERT INTO users (name, uuid, email, phone)
VALUES ('Anonymous', '95c7cdac-6a6f-44ca-a28f-fc62ef61405d', NULL, NULL),
       ('Mark Bellingham', '27e2cd2b-a5f6-4510-8d24-c19f498d9bb1', 'mail@markbellingham.uk', '+44 7462 030896');

CREATE TABLE IF NOT EXISTS sections
(
    id      INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(255) NOT NULL,
    visible TINYINT(1)   NOT NULL DEFAULT 0
);

INSERT INTO sections (name, visible)
VALUES ('home', 1),
       ('music', 1),
       ('pictures', 1),
       ('blog', 1),
       ('games', 1),
       ('contact', 1);

CREATE TABLE IF NOT EXISTS extra
(
    id      INT(2)       NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(255) NOT NULL,
    setting VARCHAR(255) NOT NULL
);

INSERT INTO extra (name, setting)
VALUES ('LastFM refresh', CURRENT_DATE());
