CREATE TABLE IF NOT EXISTS albums_temp
(
    id           INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    artist       VARCHAR(255),
    album_artist VARCHAR(255),
    title        VARCHAR(255),
    genre        VARCHAR(255),
    year         VARCHAR(255),
    image        VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS tracks_temp
(
    id          INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    track_no    VARCHAR(255),
    track_name  VARCHAR(255),
    album_title VARCHAR(255),
    artist      VARCHAR(255),
    duration    VARCHAR(255),
    filename    VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS genres_temp
(
    id    INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    genre VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS artist_temp
(
    id     INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    artist VARCHAR(255)
);

DELETE
FROM genres_temp
WHERE genre IS NULL
   OR genre = ''
   OR genre = ' ';
DELETE
FROM artist_temp
WHERE artist IS NULL
   OR artist = ''
   OR artist = ' ';
DELETE
FROM albums_temp
WHERE artist IS NULL
   OR artist = ''
   OR artist = ' ';
DELETE
FROM tracks_temp
WHERE artist IS NULL
   OR artist = ''
   OR artist = ' ';


UPDATE albums_temp al JOIN artist_temp at ON al.artist = at.artist
SET al.artist = at.id;
UPDATE albums_temp al JOIN artist_temp at ON al.album_artist = at.artist
SET al.album_artist = at.id;
UPDATE albums_temp al JOIN tracks_temp tr ON al.title = tr.album_title
SET tr.album_title = al.id;
UPDATE albums_temp al JOIN genres_temp gt ON al.genre = gt.genre
SET al.genre = gt.id;
UPDATE tracks_temp t JOIN artist_temp a ON t.artist = a.artist
SET t.artist = a.id;
UPDATE albums_temp
SET album_artist = artist
WHERE album_artist IS NULL
   OR album_artist = ''
   OR album_artist = ' ';
UPDATE tracks_temp
SET track_no = SUBSTRING_INDEX(track_no, '/', 1);
UPDATE albums_temp
SET genre = NULL
WHERE genre = '';

INSERT INTO genres (genre_id, genre)
SELECT id, genre
FROM genres_temp;
INSERT INTO artists (artist_id, artist)
SELECT id, artist
FROM artist_temp;
INSERT INTO albums (album_id, album_artist, title, year, image, genre_id, artist_id)
SELECT id, album_artist, title, year, image, genre, artist
FROM albums_temp;
INSERT INTO albums (album_artist, title, year, image, genre_id, artist_id)
VALUES (228, "Say It Ain't So", 1995, "/home/mark/Music/Weezer/Say It Ain't So/folder.jpg", 2, 228);

INSERT INTO tracks (track_id, track_no, track_name, duration, filename, album_id, artist_id)
SELECT id, track_no, track_name, duration, filename, album_title, artist
FROM tracks_temp;