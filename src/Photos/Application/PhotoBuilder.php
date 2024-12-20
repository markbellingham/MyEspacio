<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;

final readonly class PhotoBuilder
{
    public function __construct(
        private DataSet $dataSet
    ) {
    }

    public function build(): Photo
    {
        return new Photo(
            country: $this->getCountry(),
            geoCoordinates: $this->getGeoCoordinates(),
            dimensions: $this->getDimensions(),
            relevance: $this->getRelevance(),
            dateTaken: $this->dataSet->dateTimeNull('date_taken'),
            description: $this->dataSet->string('description'),
            directory: $this->dataSet->string('directory'),
            filename: $this->dataSet->string('filename'),
            id: $this->dataSet->int('photo_id'),
            title: $this->dataSet->string('title'),
            town: $this->dataSet->string('town'),
            commentCount: $this->dataSet->int('comment_count'),
            faveCount: $this->dataSet->int('fave_count'),
            uuid: $this->dataSet->uuidNull('uuid')
        );
    }

    private function getCountry(): Country
    {
        return new Country(
            id: $this->dataSet->int('country_id'),
            name: $this->dataSet->string('country_name'),
            twoCharCode: $this->dataSet->string('two_char_code'),
            threeCharCode: $this->dataSet->string('three_char_code')
        );
    }

    private function getGeoCoordinates(): GeoCoordinates
    {
        return new GeoCoordinates(
            id: $this->dataSet->int('geo_id'),
            photoUuid: $this->dataSet->uuidNull('photo_uuid'),
            latitude: $this->dataSet->int('latitude'),
            longitude: $this->dataSet->int('longitude'),
            accuracy: $this->dataSet->int('accuracy')
        );
    }

    private function getDimensions(): Dimensions
    {
        return new Dimensions(
            width: $this->dataSet->int('width'),
            height: $this->dataSet->int('height')
        );
    }

    private function getRelevance(): Relevance
    {
        return new Relevance(
            cScore: $this->dataSet->int('cscore'),
            pScore: $this->dataSet->int('pscore')
        );
    }
}
