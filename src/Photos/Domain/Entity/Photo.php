<?php

/** @noinspection PhpLackOfCohesionInspection */

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

final class Photo extends Model
{
    public function __construct(
        private readonly Country $country,
        private readonly GeoCoordinates $geoCoordinates,
        private readonly Dimensions $dimensions,
        private readonly Relevance $relevance,
        private readonly ?DateTimeImmutable $dateTaken = null,
        private readonly ?string $description = '',
        private readonly ?string $directory = '',
        private readonly string $filename = '',
        private readonly int $id = 0,
        private readonly string $title = '',
        private readonly string $town = '',
        private readonly ?int $commentCount = 0,
        private readonly ?int $faveCount = 0,
        private readonly string $uuid = ''
    ) {
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getGeoCoordinates(): GeoCoordinates
    {
        return $this->geoCoordinates;
    }

    public function getDimensions(): Dimensions
    {
        return $this->dimensions;
    }

    public function getRelevance(): Relevance
    {
        return $this->relevance;
    }

    public function getDateTaken(): ?DateTimeImmutable
    {
        return $this->dateTaken;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTown(): string
    {
        return $this->town;
    }

    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    public function getFaveCount(): ?int
    {
        return $this->faveCount;
    }

    public function jsonSerialize(): array
    {
        $array = get_object_vars($this);
        $array['dateTaken'] = $this->dateTaken?->format(DateTimeInterface::ATOM);
        return $array;
    }

    public static function createFromDataSet(DataSet $data): Photo
    {
        return new Photo(
            country: Country::createFromDataSet($data),
            geoCoordinates: GeoCoordinates::createFromDataSet($data),
            dimensions: Dimensions::createFromDataSet($data),
            relevance: Relevance::createFromDataSet($data),
            dateTaken: $data->dateTimeNull('date_taken'),
            description: $data->string('description'),
            directory: $data->string('directory'),
            filename: $data->string('filename'),
            id: $data->int('photo_id'),
            title: $data->string('title'),
            town: $data->string('town'),
            commentCount: $data->int('comment_count'),
            faveCount: $data->int('fave_count'),
            uuid: $data->string('uu_id')
        );
    }
}
