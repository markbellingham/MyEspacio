<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql\Queries;

final class QueryService
{
    public const string PHOTO_PROPERTIES = 'SELECT photos.id AS photo_id,
        photos.date_taken,
        photos.description,
        photos.directory,
        photos.filename,
        photos.title,
        photos.town,
        photos.height,
        photos.width,
        photos.uuid AS photo_uuid,
        photos.id AS photo_id,
        countries.id AS country_id,
        countries.name AS country_name,
        countries.two_char_code,
        countries.three_char_code,
        geo.id AS geo_id,
        geo.accuracy,
        geo.latitude,
        geo.longitude,
        (SELECT COUNT(DISTINCT photo_comments.id) 
            FROM pictures.photo_comments 
            WHERE photo_comments.photo_id = photos.id) AS comment_count,
        (SELECT COUNT(DISTINCT photo_faves.photo_id) 
            FROM pictures.photo_faves 
            WHERE photo_faves.photo_id = photos.id) AS fave_count
    FROM pictures.photos
    LEFT JOIN pictures.countries ON countries.Id = photos.country
    LEFT JOIN pictures.geo ON photos.id = geo.photo_id
    LEFT JOIN pictures.photo_comments ON photos.id = photo_comments.photo_id
    LEFT JOIN pictures.photo_faves ON photos.id = photo_faves.photo_id
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id';

    public const string PHOTO_MATCH_PROPERTIES = 'SELECT 
        photos.id AS photo_id,
        photos.date_taken,
        photos.description,
        photos.directory,
        photos.filename,
        photos.title,
        photos.town,
        photos.height,
        photos.width,
        photos.uuid AS photo_uuid,
        countries.id AS country_id,
        countries.name AS country_name,
        countries.two_char_code,
        countries.three_char_code,
        geo.id AS geo_id,
        geo.accuracy,
        geo.latitude,
        geo.longitude,
        IFNULL(cmt.cmt_count, 0) AS comment_count, 
        IFNULL(fv.fave_count, 0) AS fave_count,
        MATCH(photos.title, photos.description, photos.town) AGAINST(:searchTerms IN BOOLEAN MODE) AS pscore,
        MATCH(countries.name) AGAINST(:searchTerms IN BOOLEAN MODE) AS cscore
    FROM pictures.photos
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id
    LEFT JOIN pictures.countries ON countries.id = photos.country
    LEFT JOIN pictures.geo ON photos.id = geo.photo_id
    LEFT JOIN (
        SELECT photo_id, COUNT(photo_id) AS cmt_count
        FROM pictures.photo_comments
        GROUP BY photo_id
    ) AS cmt ON cmt.photo_id = photos.id
    LEFT JOIN (
        SELECT photo_id, COUNT(photo_id) AS fave_count
        FROM pictures.photo_faves
        GROUP BY photo_id
    ) AS fv ON fv.photo_id = photos.id';

    /**
     * @param array<int, string> $searchTerms
     * @return array<int, string>
     */
    public static function prepare(array $searchTerms): array
    {
        $cleanedStrings = array_map(fn($str) => (string) preg_replace('/[^a-zA-Z0-9]/', '', $str), $searchTerms);
        $filteredStrings = array_filter($cleanedStrings, fn($term) => strlen($term) >= 3);
        return array_values(array_map(fn($str) => $str . '*', $filteredStrings));
    }
}
