<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use Ramsey\Uuid\UuidInterface;

final class PhotoAlbum extends Model
{
    public function __construct(
        private string $title = 'Unassigned',
        private ?int $albumId = null,
        private ?UuidInterface $uuid = null,
        private ?string $description = null,
        private ?Country $country = null,
        private ?PhotoCollection $photos = null
    ) {
        if ($this->photos === null) {
            $this->photos = new PhotoCollection([]);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setAlbumId(int $albumId): void
    {
        $this->albumId = $albumId;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    public function getPhotos(): PhotoCollection
    {
        return $this->photos;
    }

    public function setPhotos(PhotoCollection $photos): void
    {
        $this->photos = $photos;
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'album_uuid' => $this->uuid->toString(),
            'country' => $this->country?->jsonSerialize(),
            'photos' => $this->photos->jsonSerialize()
        ];
    }

    public static function createFromDataSet(DataSet $data): PhotoAlbum
    {
        $country = null;
        if ($data->value('country_id') !== null) {
            $country = new Country(
                id: $data->int('country_id'),
                name: $data->string('country_name'),
                twoCharCode: $data->string('two_char_code'),
                threeCharCode: $data->string('three_char_code')
            );
        }
        return new PhotoAlbum(
            title: $data->string('title'),
            albumId: $data->int('album_id'),
            uuid: $data->uuidNull('album_uuid'),
            description: $data->string('description'),
            country: $country
        );
    }
}
