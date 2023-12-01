CREATE TABLE IF NOT EXISTS flickr (
    id INT,
    name VARCHAR(255),
    description TEXT,
    count_views INT,
    count_faves INT,
    count_comments INT,
    date_taken DATETIME,
    count_tags INT,
    count_notes INT,
    rotation INT,
    date_imported DATETIME,
    geo JSON,
    photo_groups JSON,
    albums JSON,
    tags JSON,
    people JSON,
    notes JSON,
    comments JSON
);

CREATE TABLE IF NOT EXISTS geo (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    photo_id INT NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    accuracy INT NOT NULL,
    CONSTRAINT fk_geo_photoid FOREIGN KEY (photo_id) REFERENCES photos (id)
);

INSERT INTO geo (latitude, longitude, accuracy, photo_id)
SELECT REPLACE(JSON_EXTRACT(geo, '$[0].latitude'), '"', '') / 1000000,
       REPLACE(JSON_EXTRACT(geo, '$[0].longitude'), '"', '') / 1000000,
       REPLACE(JSON_EXTRACT(geo, '$[0].accuracy'), '"', ''),
       photos_id
FROM flickr
WHERE photos_id IS NOT NULL AND geo IS NOT NULL AND geo != '[]';

CREATE TABLE IF NOT EXISTS albums (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL
);

INSERT INTO albums (title)
SELECT DISTINCT JSON_EXTRACT(albums, '$[0].title') AS album
FROM flickr
WHERE albums IS NOT NULL AND albums != '[]'
ORDER BY album;

DELETE
FROM anon_photo_faves
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

DELETE
FROM geo
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

DELETE
FROM photo_album
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

DELETE
FROM photo_comments
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

DELETE
FROM photo_faves
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

DELETE
FROM photo_tags
WHERE photo_id IN(
    SELECT id FROM photos WHERE country = 142
);

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM photos WHERE country = 142;
SET FOREIGN_KEY_CHECKS = 1;