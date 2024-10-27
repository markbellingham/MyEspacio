<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Application\PhotoBuilder;
use MyEspacio\Photos\Domain\Entity\Photo;

/**
 * @template-extends ModelCollection<int, Photo>
 */
final class PhotoCollection extends ModelCollection
{
    public function requiredKeys(): array
    {
        return [
            'country_id',
            'country_name',
            'two_char_code',
            'three_char_code',
            'geo_id',
            'latitude',
            'longitude',
            'accuracy',
            'width',
            'height',
            'date_taken',
            'description',
            'directory',
            'filename',
            'photo_id',
            'title',
            'town',
            'comment_count',
            'fave_count',
            'uuid'
        ];
    }

    public function current(): Photo
    {
        $data = $this->currentDataSet();

        return (new PhotoBuilder($data))->build();
    }
}
