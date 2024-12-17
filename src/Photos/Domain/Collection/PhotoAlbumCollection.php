<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Collection;

use MyEspacio\Framework\ModelCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;

/**
 * @template-extends ModelCollection<int, PhotoAlbum>
 */
final class PhotoAlbumCollection extends ModelCollection
{
    public function requiredKeys(): array
    {
        return [
            'album_id',
            'uuid',
            'description',
            'title',
            'country_id',
            'country_name',
            'three_char_code',
            'two_char_code'
        ];
    }

    public function current(): PhotoAlbum
    {
        $data = $this->currentDataSet();

        return new PhotoAlbum(
            title: $data->string('title'),
            albumId: $data->int('album_id'),
            uuid: $data->string('uuid'),
            description: $data->string('description'),
            country: Country::createFromDataSet($data)
        );
    }
}
