CREATE DATABASE IF NOT EXISTS project;

USE project;

CREATE TABLE IF NOT EXISTS icons(
    icon_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    icon VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS sections (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    visible TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    tag VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS users(
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20) UNIQUE,
    passcode_route VARCHAR(5),
    login_attempts INT NOT NULL DEFAULT 0,
    login_date DATETIME,
    magic_link VARCHAR(255),
    phone_code VARCHAR(6),
    uuid BINARY(16) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE DATABASE IF NOT EXISTS pictures;
USE pictures;

CREATE TABLE IF NOT EXISTS countries (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    two_char_code CHAR(2) UNIQUE,
    three_char_code CHAR(3) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS albums (
    album_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL UNIQUE,
    country_id INT UNSIGNED,
    description TEXT,
    uuid BINARY(16) NOT NULL UNIQUE,
    CONSTRAINT fk_albums_countryid FOREIGN KEY (country_id) REFERENCES countries (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS photos (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    flickr_title VARCHAR(255),
    description TEXT,
    town VARCHAR(255) NOT NULL,
    country_id INT UNSIGNED,
    filename VARCHAR(255),
    directory VARCHAR(255),
    width INT,
    height INT,
    date_taken DATETIME,
    geo_id INT UNSIGNED,
    uuid BINARY(16) NOT NULL UNIQUE,
    CONSTRAINT fk_photos_countryid FOREIGN KEY (country_id) REFERENCES countries (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS anon_photo_faves (
    user_id INT UNSIGNED NOT NULL,
    photo_id INT UNSIGNED NOT NULL,
    CONSTRAINT fk_anonphotofaves_userid FOREIGN KEY (user_id) REFERENCES project.users (id),
    CONSTRAINT fk_anonphotofaves_photoid FOREIGN KEY (photo_id) REFERENCES photos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS photo_album (
    photo_id INT UNSIGNED NOT NULL,
    album_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (photo_id, album_id),
    CONSTRAINT fk_photoalbum_photoid FOREIGN KEY (photo_id) REFERENCES photos (id),
    CONSTRAINT fk_photoalbum_albumid FOREIGN KEY (album_id) REFERENCES albums (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS photo_comments (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    photo_id INT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created DATETIME NOT NULL,
    verified TINYINT(1) UNSIGNED DEFAULT 1,
    title VARCHAR(255),
    CONSTRAINT fk_photocomments_userid FOREIGN KEY (user_id) REFERENCES project.users (id),
    CONSTRAINT fk_photocomments_photoid FOREIGN KEY (photo_id) REFERENCES photos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS photo_faves (
    user_id INT UNSIGNED NOT NULL,
    photo_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, photo_id),
    CONSTRAINT fk_photofaves_userid FOREIGN KEY (user_id) REFERENCES project.users (id),
    CONSTRAINT fk_photofaves_photoid FOREIGN KEY (photo_id) REFERENCES photos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS photo_tags (
    photo_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (photo_id, tag_id),
    CONSTRAINT fk_phototags_photoid FOREIGN KEY (photo_id) REFERENCES photos (id),
    CONSTRAINT fk_phototags_tagid FOREIGN KEY (tag_id) REFERENCES project.tags (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS geo (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    photo_id INT UNSIGNED NOT NULL,
    latitude INT NOT NULL,
    longitude INT NOT NULL,
    accuracy INT NOT NULL,
    CONSTRAINT fk_geo_photoid FOREIGN KEY (photo_id) REFERENCES photos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

GRANT SELECT, INSERT, UPDATE, DELETE ON project.* TO 'webserver'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON pictures.* TO 'webserver'@'%';
FLUSH PRIVILEGES;
