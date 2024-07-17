<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain;

use MyEspacio\Framework\Model;

final class GeoCoordinates extends Model
{
    public function __construct(
        private readonly int $id,
        private readonly int $photoId,
        private readonly int $latitude,
        private readonly int $longitude,
        private readonly int $accuracy,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public function getLatitude(): int
    {
        return $this->latitude;
    }

    public function getLongitude(): int
    {
        return $this->longitude;
    }

    public function getAccuracy(): int
    {
        return $this->accuracy;
    }
}
