CREATE DATABASE music;

USE music;

CREATE TABLE IF NOT EXISTS artists
(
    artist_id INT(5)       NOT NULL PRIMARY KEY AUTO_INCREMENT,
    artist    VARCHAR(255) NOT NULL,
    top50     INT(2),
    playcount INT(5)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS genres
(
    genre_id INT(5)       NOT NULL PRIMARY KEY AUTO_INCREMENT,
    genre    VARCHAR(255) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS albums
(
    album_id     INT(7) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    album_artist INT(7),
    title        VARCHAR(255),
    YEAR         VARCHAR(255),
    image        VARCHAR(255),
    genre_id     INT(5),
    artist_id    INT(5),
    top50        INT(2),
    playcount    INT(5),
    CONSTRAINT fk_albums_genreid FOREIGN KEY (genre_id) REFERENCES genres (genre_id),
    CONSTRAINT fk_albums_artistid FOREIGN KEY (artist_id) REFERENCES artists (artist_id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS tracks
(
    track_id   INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    track_no   CHAR(5),
    track_name VARCHAR(255),
    duration   INT(5),
    filename   VARCHAR(255),
    album_id   INT(7),
    artist_id  INT(5),
    top50      INT(2),
    playcount  INT(5),
    CONSTRAINT fk_tracks_albumid FOREIGN KEY (album_id) REFERENCES albums (album_id),
    CONSTRAINT fk_tracks_artistid FOREIGN KEY (artist_id) REFERENCES artists (artist_id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
