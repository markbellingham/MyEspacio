SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE photo_album;
TRUNCATE photo_comments;
TRUNCATE photo_faves;
TRUNCATE anon_photo_faves;
TRUNCATE photo_tags;
TRUNCATE albums;
TRUNCATE photos;
TRUNCATE geo;
SET FOREIGN_KEY_CHECKS = 1;

SELECT a.title, p.title
FROM photo_comments pc
LEFT JOIN photos p ON pc.photo_id = p.id
LEFT JOIN photo_album pa ON p.id = pa.album_id
LEFT JOIN albums a ON pa.album_id = a.album_id
GROUP BY p.title;
