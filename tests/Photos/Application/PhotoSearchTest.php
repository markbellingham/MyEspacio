<?php

declare(strict_types=1);

namespace Photos\Application;

use MyEspacio\Photos\Application\PhotoSearch;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoSearchTest extends TestCase
{
    private PhotoAlbum $album;
    private PhotoCollection $photos;

    protected function setUp(): void
    {
        $this->album = new PhotoAlbum(
            title: 'Valparaiso',
            albumId: 5,
            uuid: Uuid::fromString('7d12c8f5-8d3f-4c49-9c3a-5f2e7f0f0ea9'),
            description: null,
            country: new Country(
                id: 1,
                name: 'Chile',
                twoCharCode: 'CL',
                threeCharCode: 'CHL'
            ),
            photos: new PhotoCollection([])
        );

        $this->photos = new PhotoCollection([
            [
                'country_id' => '45',
                'country_name' => 'Chile',
                'two_char_code' => 'CL',
                'three_char_code' => 'CHL',
                'geo_id' => '2559',
                'photo_uuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                'latitude' => '-33438084',
                'longitude' => '-33438084',
                'accuracy' =>  '16',
                'width' => '456',
                'height' => '123',
                'date_taken' => "2012-10-21",
                'description' => "Note the spurs...",
                'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                'filename' => "P1070237.JPG",
                'photo_id' => '2689',
                'title' => "Getting ready to dance",
                'town' => "Valparaiso",
                'comment_count' => '1',
                'fave_count' => '1'
            ]
        ]);
    }

    public function testSearchWithAlbumAndNoSearchTerms(): void
    {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with('Valparaiso')
            ->willReturn($this->album);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchAlbumPhotos')
            ->with($this->album)
            ->willReturn($this->photos);

        $expected = clone $this->album;
        $expected->setPhotos($this->photos);

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);

        $result = $photoSearch->search('Valparaiso', null);
        $this->assertEquals($expected, $result);
    }

    public function testSearchWithAlbumAndSearchTerms(): void
    {
        $searchTerms = ['dance'];
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with('Valparaiso')
            ->willReturn($this->album);

        $photoAlbumRepository->expects($this->once())
            ->method('searchAlbumPhotos')
            ->with($this->album, $searchTerms)
            ->willReturn($this->photos);

        $expected = clone $this->album;
        $expected->setPhotos($this->photos);

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);

        $result = $photoSearch->search('Valparaiso', 'dance');
        $this->assertEquals($expected, $result);
    }

    public function testSearchWithNoAlbumButWithSearchTerms(): void
    {
        $searchTerms = ['dance'];
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        // Setup mocks for no album but with search terms
        $photoAlbumRepository->expects($this->never())
            ->method('fetchByName');

        $photoRepository->expects($this->once())
            ->method('search')
            ->with($searchTerms)
            ->willReturn($this->photos);

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);

        $result = $photoSearch->search(null, 'dance');
        $this->assertEquals($this->photos, $result);
    }

    public function testSearchWithNoAlbumAndNoSearchTerms(): void
    {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->never())
            ->method('fetchByName');

        $photoRepository->expects($this->once())
            ->method('randomSelection')
            ->willReturn($this->photos);

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);

        $result = $photoSearch->search(null, null);
        $this->assertEquals($this->photos, $result);
    }

    public function testSearchWithMostPopularKeyword(): void
    {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->willReturn(null);

        $photoRepository->expects($this->once())
            ->method('topPhotos')
            ->willReturn($this->photos);

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);

        $result = $photoSearch->search('most-popular', null);
        $this->assertEquals($this->photos, $result);
    }
}
