<?php

declare(strict_types=1);

namespace Tests\Php\Photos\Presentation;

use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Presentation\PhotoController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PhotoControllerTest extends TestCase
{
    /** @param class-string $expectedResponseClassName */
    #[DataProvider('photoGridDataProvider')]
    public function testPhotoGrid(
        Request $request,
        bool $validated,
        DataSet $vars,
        ?string $album,
        ?string $searchTerms,
        ResponseData $responseData,
        Response $expectedResponse,
        PhotoAlbum|PhotoCollection $expectedSearchResult,
        string $expectedResponseClassName,
        string $expectedResponseData
    ): void {
        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validated);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);
        $photoSearch->expects($this->once())
            ->method('search')
            ->with($album, $searchTerms)
            ->willReturn($expectedSearchResult);
        $albumRepository->expects($this->once())
            ->method('fetchAll')
            ->willReturn(new PhotoAlbumCollection([]));

        $controller = new PhotoController(
            $albumRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler
        );

        $actualResponse = $controller->photoGrid($request, $vars);
        $this->assertInstanceOf($expectedResponseClassName, $actualResponse);

        $this->assertEquals($expectedResponseData, $actualResponse->getContent());
    }

    /** @return array<string, array<string, mixed>> */
    public static function photoGridDataProvider(): array
    {
        return [
            'json_photo_collection_search' => [
                'request' => new Request(['search' => 'sunset'], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
                'validated' => true,
                'vars' => new DataSet(),
                'album' => null,
                'searchTerms' => 'sunset',
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoCollection([])
                    ],
                    template: 'photos/photos.html.twig'
                ),
                'expectedResponse' => new JsonResponse(
                    [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoCollection([])
                    ],
                    Response::HTTP_OK
                ),
                'expectedSearchResult' => new PhotoCollection([]),
                'expectedResponseClassName' => JsonResponse::class,
                'expectedResponseData' => '{"albums":[],"photos":[]}',
            ],
            'json_photo_album' => [
                'request' => new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
                'validated' => true,
                'vars' => new DataSet(['album' => 'mexico']),
                'album' => 'mexico',
                'searchTerms' => null,
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoAlbum()
                    ],
                    template: 'photos/photo-album.html.twig'
                ),
                'expectedResponse' => new JsonResponse(
                    [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoAlbum(),
                    ]
                ),
                'expectedSearchResult' => new PhotoAlbum(),
                'expectedResponseClassName' => JsonResponse::class,
                'expectedResponseData' => '{"albums":[],"photos":{"title":"Unassigned","description":null,"album_uuid":null,"country":null,"photos":[]}}',
            ],
            'html_photo_collection_full_search' => [
                'request' => new Request(['search' => 'sunset']),
                'validated' => false,
                'vars' => new DataSet(),
                'album' => null,
                'searchTerms' => 'sunset',
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoCollection([]),
                    ],
                    template: 'photos/photos.html.twig'
                ),
                'expectedResponse' => new Response('<div class="my-class">Some Content</div>'),
                'expectedSearchResult' => new PhotoCollection([]),
                'expectedResponseClassName' => Response::class,
                'expectedResponseData' => '<div class="my-class">Some Content</div>',
            ],
            'html_photo_album_full' => [
                'request' => new Request(),
                'validated' => false,
                'vars' => new DataSet(['album' => 'mexico']),
                'album' => 'mexico',
                'searchTerms' => null,
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoAlbum(),
                    ],
                    template: 'photos/photo-album.html.twig'
                ),
                'expectedResponse' => new Response('<div class="my-class">Some Content</div>'),
                'expectedSearchResult' => new PhotoAlbum(),
                'expectedResponseClassName' => Response::class,
                'expectedResponseData' => '<div class="my-class">Some Content</div>',
            ],
            'invalid_album_name' => [
                'request' => new Request(),
                'validated' => false,
                'vars' => new DataSet(['album' => ['invalid' => 'data']]),
                'album' => '{"invalid":"data"}',
                'searchTerms' => null,
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'photos' => new PhotoCollection([]),
                    ],
                    template: 'photos/photos.html.twig'
                ),
                'expectedResponse' => new Response('<div class="my-class">Some Content</div>'),
                'expectedSearchResult' => new PhotoCollection([]),
                'expectedResponseClassName' => Response::class,
                'expectedResponseData' => '<div class="my-class">Some Content</div>',
            ]
        ];
    }

    /** @param class-string $expectedClass */
    #[DataProvider('singlePhotoDataProvider')]
    public function testSinglePhoto(
        Request $request,
        DataSet $vars,
        string $uuid,
        ?Photo $repositoryResult,
        string $expectedClass,
        Response $expectedResponse,
        string $expectedResult,
        string $expectedContentType
    ): void {
        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $photoRepository->expects($this->once())
            ->method('fetchByUuid')
            ->with($uuid)
            ->willReturn($repositoryResult);
        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                data: [
                    'photo' => $repositoryResult
                ],
                template: 'photos/partials/single-photo.html.twig'
            ))
            ->willReturn($expectedResponse);

        $controller = new PhotoController(
            $albumRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler
        );
        $actualResult = $controller->singlePhoto($request, $vars);

        $this->assertInstanceOf($expectedClass, $actualResult);
        $this->assertEquals($expectedResult, $actualResult->getContent());
        $this->assertStringContainsString($expectedContentType, (string) $actualResult->headers->get('Content-Type'));
    }

    /** @return array<string, array<int, mixed>> */
    public static function singlePhotoDataProvider(): array
    {
        return [
            'json_found' => [
                new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    [
                        'HTTP_ACCEPT' => 'application/json'
                    ]
                ),
                new DataSet(['uuid' => '38a0a218-9a5c-4bb9-ab30-aae6ca3ffc61']),
                '38a0a218-9a5c-4bb9-ab30-aae6ca3ffc61',
                Photo::createFromDataSet(new DataSet([
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'photo_id' => '2689',
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'latitude' => '-33438084',
                    'longitude' => '-33438084',
                    'accuracy' =>  '16',
                    'width' => '456',
                    'height' => '123',
                    'cscore' => '4',
                    'pscore' => '5',
                    'date_taken' => "2012-10-21",
                    'description' => "Note the spurs...",
                    'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                    'filename' => "P1070237.JPG",
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1',
                    'uuid' => '4b9d0175-6d47-4460-b48b-6385db446a30'
                ])),
                JsonResponse::class,
                new JsonResponse(['photo' => null]),
                '{"photo":null}',
                'application/json'
            ],
            'json_not_found' => [
                new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    [
                        'HTTP_ACCEPT' => 'application/json'
                    ]
                ),
                new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
                'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
                null,
                JsonResponse::class,
                new JsonResponse(['photo' => null]),
                '{"photo":null}',
                'application/json'
            ],
            'html_found' => [
                new Request(),
                new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
                'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
                Photo::createFromDataSet(new DataSet([
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'photo_id' => '2689',
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'latitude' => '-33438084',
                    'longitude' => '-33438084',
                    'accuracy' =>  '16',
                    'width' => '456',
                    'height' => '123',
                    'cscore' => '4',
                    'pscore' => '5',
                    'date_taken' => "2012-10-21",
                    'description' => "Note the spurs...",
                    'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                    'filename' => "P1070237.JPG",
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1',
                    'uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd'
                ])),
                Response::class,
                new Response(
                    '<!DOCTYPE html><html lang="en-GB"><head><title>Test</title></head><body><div>Hello World</div></body></html>',
                    Response::HTTP_OK,
                    ['Content-Type' => 'text/html']
                ),
                '<!DOCTYPE html><html lang="en-GB"><head><title>Test</title></head><body><div>Hello World</div></body></html>',
                'text/html'
            ],
            'html_not_found' => [
                new Request(),
                new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
                'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
                null,
                Response::class,
                new Response(
                    '<!DOCTYPE html><html lang="en-GB"><head><title>Test</title></head><body><div>Hello World</div></body></html>',
                    Response::HTTP_OK,
                    ['Content-Type' => 'text/html']
                ),
                '<!DOCTYPE html><html lang="en-GB"><head><title>Test</title></head><body><div>Hello World</div></body></html>',
                'text/html'
            ]
        ];
    }

    public function testSinglePhotoBadUuid(): void
    {
        $request = new Request();
        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $photoRepository->expects($this->never())
            ->method('fetchByUuid');
        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                data: [],
                statusCode: Response::HTTP_BAD_REQUEST,
                translationKey: 'photos.invalid_uuid'
            ))
            ->willReturn(new Response('Invalid UUID', Response::HTTP_BAD_REQUEST));

        $controller = new PhotoController(
            $albumRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler
        );
        $actualResult = $controller->singlePhoto(
            $request,
            new DataSet(['uuid' => ['bad' => 'data']])
        );

        $this->assertSame(Response::class, get_class($actualResult));
        $this->assertEquals('Invalid UUID', $actualResult->getContent());
        $this->assertSame(400, $actualResult->getStatusCode());
    }

    public function testSinglePhotoHtmlNoToken(): void
    {
        $request = new Request(['uuid' => 'b8cf4379-62f4-4f98-a57e-9811d1a7d07d']);
        $expectedResult = '<html lang="en"><body>Some content</body></html>';

        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $photoRepository->expects($this->once())
            ->method('fetchByUuid')
            ->willReturn(null);
        $photoSearch->expects($this->once())
            ->method('search')
            ->willReturn(new PhotoCollection([]));
        $albumRepository->expects($this->once())
            ->method('fetchAll')
            ->willReturn(new PhotoAlbumCollection([]));
        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                data: [
                    'photo' => null,
                    'albums' => new PhotoAlbumCollection([]),
                    'photos' => new PhotoCollection([]),
                ],
                template: 'photos/photos.html.twig'
            ))
            ->willReturn(new Response(
                $expectedResult,
                Response::HTTP_OK,
                ['Content-Type' => 'text/html']
            ));

        $controller = new PhotoController(
            $albumRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler
        );
        $actualResult = $controller->singlePhoto(
            $request,
            new DataSet(['uuid' => 'b8cf4379-62f4-4f98-a57e-9811d1a7d07d'])
        );

        $this->assertInstanceOf(Response::class, $actualResult);
        $this->assertEquals($expectedResult, $actualResult->getContent());
        $this->assertStringContainsString('text/html', (string) $actualResult->headers->get('Content-Type'));
    }
}
