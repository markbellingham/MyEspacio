<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class PhotoAlbum extends Model
{
    public function __construct(
        private readonly int $photoId,
        private string $title = 'Unassigned',
        private ?int $albumId = 0,
        private ?string $description = null,
    ) {
    }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setAlbumId(int $albumId): void
    {
        $this->albumId = $albumId;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public static function createFromDataSet(DataSet $data): PhotoAlbum
    {
        return new PhotoAlbum(
            photoId: $data->int('photo_id'),
            title: $data->string('title'),
            albumId: $data->int('album_id'),
            description: $data->string('description')
        );
    }
}
