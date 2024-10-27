<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class GeoCoordinates extends Model
{
    public function __construct(
        private readonly int $id,
        private readonly string $photoUuid,
        private readonly int $latitude,
        private readonly int $longitude,
        private readonly int $accuracy,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPhotoUuid(): string
    {
        return $this->photoUuid;
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

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function createFromDataSet(DataSet $data): GeoCoordinates
    {
        return new GeoCoordinates(
            id: $data->int('geo_id'),
            photoUuid: $data->string('photo_uuid'),
            latitude: $data->int('latitude'),
            longitude: $data->int('longitude'),
            accuracy: $data->int('accuracy')
        );
    }
}
