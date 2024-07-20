<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain;

use MyEspacio\Framework\Model;

final class PhotoAlbum extends Model
{
    public function __construct(
        private readonly int $photoId,
        private readonly string $title,
        private ?int $albumId = null,
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

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
