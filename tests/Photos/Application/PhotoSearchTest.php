<?php

declare(strict_types=1);

namespace Tests\Photos\Application;

use MyEspacio\Photos\Application\PhotoSearch;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class PhotoSearchTest extends TestCase
{
    public function testReturnAlbumPhotos(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'Singapore',
            albumId: 51,
            description: '',
            country: new Country(
                id: 199,
                name: 'Singapore',
                twoCharCode: 'SG',
                threeCharCode: 'SGP'
            )
        );
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with('singapore')
            ->willReturn($photoAlbum);
        $photoAlbumRepository->expects($this->once())
            ->method('fetchAlbumPhotos')
            ->with($photoAlbum)
            ->willReturn(new PhotoCollection([]));

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);
        $result = $photoSearch->search('singapore');

        $this->assertInstanceOf(PhotoAlbum::class, $result);
        $this->assertInstanceOf(PhotoCollection::class, $result->getPhotos());
        $this->assertCount(0, $result->getPhotos());
    }

    /**
     * @dataProvider searchAlbumPhotosDataProvider
     * @param array<int, mixed> $cleanedSearchTerms
     * @throws Exception
     */
    public function testSearchAlbumPhotos(
        string $albumName,
        PhotoAlbum $album,
        string $function,
        array $cleanedSearchTerms,
        string $inputSearchTerms,
        int $photoCount
    ): void {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with($albumName)
            ->willReturn($album);
        $photoAlbumRepository->expects($this->once())
            ->method($function)
            ->with($album, $cleanedSearchTerms)
            ->willReturn(new PhotoCollection([]));

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);
        $result = $photoSearch->search($inputSearchTerms);

        $this->assertInstanceOf(PhotoAlbum::class, $result);
        $this->assertInstanceOf(PhotoCollection::class, $result->getPhotos());
        $this->assertCount($photoCount, $result->getPhotos());
    }

    /** @return array<string, array<int, mixed>> */
    public static function searchAlbumPhotosDataProvider(): array
    {
        return [
            'single_searchTerm' => [
                'singapore',
                new PhotoAlbum(
                    title: 'Singapore',
                    albumId: 51,
                    description: '',
                    country: new Country(
                        id: 199,
                        name: 'Singapore',
                        twoCharCode: 'SG',
                        threeCharCode: 'SGP'
                    )
                ),
                'searchAlbumPhotos',
                ['sunset'],
                '%20singapore%20/%20sunset%20',
                0
            ],
            'two_search_terms' => [
                'lijiang',
                new PhotoAlbum(
                    title: 'Lijiang',
                    albumId: 21,
                    description: '',
                    country: new Country(
                        id: 46,
                        name: 'China',
                        twoCharCode: 'CN',
                        threeCharCode: 'CHN'
                    )
                ),
                'searchAlbumPhotos',
                ["temple","dragon's"],
                " lijiang/temple/dragon%27s",
                0
            ]
        ];
    }

    /**
     * @dataProvider searchNoAlbumDataProvider
     * @param array<int, string> $searchValue
     * @throws Exception
     */
    public function testSearchNoAlbum(
        string $param0,
        string $method,
        array $searchValue,
        string $inputValue
    ): void {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with($param0)
            ->willReturn(null);

        $expectation = $photoRepository->expects($this->once())
            ->method($method)
            ->willReturn(new PhotoCollection([]));
        if ($method === 'search') {
            $expectation->with($searchValue);
        }

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);
        $result = $photoSearch->search($inputValue);

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function searchNoAlbumDataProvider(): array
    {
        return [
            'test_1' => [
                'most-popular',
                'topPhotos',
                [],
                'most-popular'
            ],
            'test_2' => [
                'sunset',
                'search',
                ['sunset'],
                'sunset'
            ],
            'test_3' => [
                'sunset',
                'search',
                ['sunset','beach'],
                'sunset/beach'
            ],
            'test_4' => [
                '',
                'randomSelection',
                [],
                ''
            ]
        ];
    }

    public function testSearchMyFavourites(): void
    {
        $photoAlbumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);

        $photoAlbumRepository->expects($this->once())
            ->method('fetchByName')
            ->with('my-favourites')
            ->willReturn(null);
        $photoAlbumRepository->expects($this->once())
            ->method('fetchMyFavourites')
            ->willReturn(new PhotoAlbum(
                title: 'My Favourites',
                albumId: 2,
                description: '',
                country: null,
                photos: new PhotoCollection([])
            ));

        $photoSearch = new PhotoSearch($photoAlbumRepository, $photoRepository);
        $result = $photoSearch->search('my-favourites');

        $this->assertInstanceOf(PhotoAlbum::class, $result);
        $this->assertInstanceOf(PhotoCollection::class, $result->getPhotos());
    }
}
