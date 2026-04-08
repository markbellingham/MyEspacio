<?php

declare(strict_types=1);

namespace Tests\Php\Photos\Presentation;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoCommentRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoFaveRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Presentation\PhotoController;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PhotoControllerTest extends TestCase
{
    #[DataProvider('photoGridDataOnlyDataProvider')]
    public function testPhotoGridDataOnly(
        ?string $albumName,
        ?string $searchTerms,
        PhotoAlbum|PhotoCollection $searchResult,
        ResponseData $responseData,
        Request $request,
        DataSet $pathParams,
    ): void {
        $expectedResponse = new JsonResponse();

        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $photoSearch->expects($this->once())
            ->method('search')
            ->with($albumName, $searchTerms)
            ->willReturn($searchResult);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);
        $albumRepository->expects($this->never())
            ->method('fetchAll');
        $session->expects($this->never())
            ->method('get');

        $controller = new PhotoController(
            $albumRepository,
            $this->createMock(PhotoCommentRepositoryInterface::class),
            $this->createMock(PhotoFaveRepositoryInterface::class),
            $this->createMock(PhotoRepositoryInterface::class),
            $photoSearch,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->photoGrid($request, $pathParams);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame(get_class($expectedResponse), get_class($actualResponse));
    }

    /** @return array<string, array<string, mixed>> */
    public static function photoGridDataOnlyDataProvider(): array
    {
        return [
            'photo_album_search' => [
                'albumName' => 'tulum',
                'searchTerms' => 'mayan',
                'searchResult' => self::createPhotoAlbum(id: 1),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => 'mayan',
                    ],
                    template: 'photos/partials/album-grid.html.twig',
                ),
                'request' => new Request(
                    ['search' => 'mayan'],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet(['album' => 'tulum',]),
            ],
            'photo_collection_search' => [
                'albumName' => null,
                'searchTerms' => 'pyramid',
                'searchResult' => new PhotoCollection([]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photos' => new PhotoCollection([]),
                        'search' => 'pyramid',
                    ],
                    template: 'photos/partials/photo-grid.html.twig',
                ),
                'request' => new Request(
                    ['search' => 'pyramid'],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet(['album' => '']),
            ],
            'photo_collection_no_search' => [
                'albumName' => null,
                'searchTerms' => null,
                'searchResult' => new PhotoCollection([]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                    ],
                    template: 'photos/partials/photo-grid.html.twig',
                ),
                'request' => new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet(['album' => '']),
            ],
            'invalid_album' => [
                'albumName' => '{"invalid":"data"}',
                'searchTerms' => null,
                'searchResult' => new PhotoCollection([]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => '{"invalid":"data"}',
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                    ],
                    template: 'photos/partials/photo-grid.html.twig',
                ),
                'request' => new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet([
                    'album' => ['invalid' => 'data']
                ]),
            ],
        ];
    }

    /**
     * @param array<bool, int> $validated
     */
    #[DataProvider('photoGridHtmlResponseDataProvider')]
    public function testPhotoGridHtmlResponse(
        array $validated,
        ?string $albumName,
        ?string $searchTerms,
        PhotoAlbum|PhotoCollection $searchResult,
        Request $request,
        DataSet $pathParams,
        ResponseData $responseData,
    ): void {
        $expectedResponse = new Response();
        [$valid, $invocationCount] = $validated;

        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn($valid);
        $photoSearch->expects($this->once())
            ->method('search')
            ->with($albumName, $searchTerms)
            ->willReturn($searchResult);
        $albumRepository->expects($this->exactly($invocationCount))
            ->method('fetchAll')
            ->willReturn(new PhotoAlbumCollection([]));
        $session->expects($this->exactly($invocationCount))
            ->method('get')
            ->willReturn(self::createUser());
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoController(
            $albumRepository,
            $this->createMock(PhotoCommentRepositoryInterface::class),
            $this->createMock(PhotoFaveRepositoryInterface::class),
            $this->createMock(PhotoRepositoryInterface::class),
            $photoSearch,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->photoGrid($request, $pathParams);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame(get_class($expectedResponse), get_class($actualResponse));
    }

    /** @return array<string, array<string, mixed>> */
    public static function photoGridHtmlResponseDataProvider(): array
    {
        return [
            'photo_album_search_from_website' => [
                'validated' => [true, 0],
                'albumName' => 'tulum',
                'searchTerms' => 'mayan',
                'searchResult' => self::createPhotoAlbum(id: 1),
                'request' => new Request(['search' => 'mayan']),
                'pathParams' => new DataSet(['album' => 'tulum']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => 'mayan',
                    ],
                    template: 'photos/partials/album-grid.html.twig',
                ),
            ],
            'photo_album_no_search_from_website' => [
                'validated' => [true, 0],
                'albumName' => 'tulum',
                'searchTerms' => null,
                'searchResult' => self::createPhotoAlbum(id: 1),
                'request' => new Request(),
                'pathParams' => new DataSet(['album' => 'tulum']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => null,
                    ],
                    template: 'photos/partials/album-grid.html.twig',
                ),
            ],
            'invalid_album_from_website' => [
                'validated' => [true, 0],
                'albumName' => '{"invalid":"data"}',
                'searchTerms' => null,
                'searchResult' => new PhotoCollection([]),
                'request' => new Request(),
                'pathParams' => new DataSet(['album' => ['invalid' => 'data']]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => '{"invalid":"data"}',
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                    ],
                    template: 'photos/partials/photo-grid.html.twig',
                ),
            ],
            'photo_search_from_website' => [
                'validated' => [true, 0],
                'albumName' => null,
                'searchTerms' => 'pyramid',
                'searchResult' => new PhotoCollection([]),
                'request' => new Request(['search' => 'pyramid']),
                'pathParams' => new DataSet(),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photos' => new PhotoCollection([]),
                        'search' => 'pyramid',
                    ],
                    template: 'photos/partials/photo-grid.html.twig',
                ),
            ],
            'photo_album_search_from_url' => [
                'validated' => [false, 1],
                'albumName' => 'tulum',
                'searchTerms' => 'temple',
                'searchResult' => self::createPhotoAlbum(id: 1),
                'request' => new Request(['search' => 'temple']),
                'pathParams' => new DataSet(['album' => 'tulum']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => 'temple',
                        'albums' => new PhotoAlbumCollection([]),
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photo-album.html.twig',
                ),
            ],
            'photo_album_no_search_from_url' => [
                'validated' => [false, 1],
                'albumName' => 'tulum',
                'searchTerms' => null,
                'searchResult' => self::createPhotoAlbum(id: 1),
                'request' => new Request(),
                'pathParams' => new DataSet(['album' => 'tulum']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => null,
                        'albums' => new PhotoAlbumCollection([]),
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photo-album.html.twig',
                ),
            ],
            'photo_search_from_url' => [
                'validated' => [false, 1],
                'albumName' => null,
                'searchTerms' => 'temple',
                'searchResult' => new PhotoCollection([]),
                'request' => new Request(['search' => 'temple']),
                'pathParams' => new DataSet(['album' => '']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photos' => new PhotoCollection([]),
                        'search' => 'temple',
                        'albums' => new PhotoAlbumCollection([]),
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photos.html.twig',
                ),
            ],
            'photos_no_search_from_url' => [
                'validated' => [false, 1],
                'albumName' => null,
                'searchTerms' => null,
                'searchResult' => new PhotoCollection([]),
                'request' => new Request(),
                'pathParams' => new DataSet(['album' => '']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                        'albums' => new PhotoAlbumCollection([]),
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photos.html.twig',
                ),
            ],
            'invalid_album_from_url' => [
                'validated' => [false, 1],
                'albumName' => 'not-exist',
                'searchTerms' => null,
                'searchResult' => new PhotoCollection([]),
                'request' => new Request(),
                'pathParams' => new DataSet(['album' => 'not-exist']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'not-exist',
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                        'albums' => new PhotoAlbumCollection([]),
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photos.html.twig',
                ),
            ],
        ];
    }

    #[DataProvider('singlePhotoDataOnlyDataProvider')]
    public function testSinglePhotoDataOnly(
        string $uuid,
        Photo $photo,
        Request $request,
        DataSet $pathParams,
        ResponseData $responseData,
    ): void {
        $expectedResponse = new JsonResponse();

        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(true);
        $photoRepository->expects($this->once())
            ->method('fetchByUuid')
            ->with($uuid)
            ->willReturn($photo);
        $commentRepository->expects($this->once())
            ->method('fetchForPhoto')
            ->with($photo)
            ->willReturn(new PhotoCommentCollection([]));
        $session->expects($this->never())
            ->method('get');
        $albumRepository->expects($this->never())
            ->method('fetchAll');
        $photoFaveRepository->expects($this->never())
            ->method('isUserFave');
        $photoSearch->expects($this->never())
            ->method('search');
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoController(
            $albumRepository,
            $commentRepository,
            $photoFaveRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->singlePhoto($request, $pathParams);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame(get_class($expectedResponse), get_class($actualResponse));
    }

    /** @return array<string, array<string, mixed>> */
    public static function singlePhotoDataOnlyDataProvider(): array
    {
        return [
            'photo_only' => [
                'uuid' => 'd24024ea-bce2-4d3a-bfa1-3959d5d9d5ce',
                'photo' => self::createPhoto(id: 1),
                'request' => new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet(['uuid' => 'd24024ea-bce2-4d3a-bfa1-3959d5d9d5ce']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'comments' => new PhotoCommentCollection([]),
                        'photo' => self::createPhoto(id: 1),
                    ],
                    template: 'photos/partials/single-photo.html.twig',
                ),
            ],
            'album_context' => [
                'uuid' => 'd24024ea-bce2-4d3a-bfa1-3959d5d9d5ce',
                'photo' => self::createPhoto(id: 1),
                'request' => new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json'],
                ),
                'pathParams' => new DataSet([
                    'album' => 'tulum',
                    'uuid' => 'd24024ea-bce2-4d3a-bfa1-3959d5d9d5ce'
                ]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => 'tulum',
                        'comments' => new PhotoCommentCollection([]),
                        'photo' => self::createPhoto(id: 1),
                    ],
                    template: 'photos/partials/single-photo.html.twig',
                ),
            ],
        ];
    }

    /**
     * @param array<bool, int> $validated
     */
    #[DataProvider('singlePhotoHtmlDataProvider')]
    public function testSinglePhotoHtml(
        string $uuid,
        ?string $albumName,
        ?string $searchTerms,
        array $validated,
        Photo $photo,
        ?User $loggedInUser,
        User $user,
        bool $isUserFave,
        PhotoAlbum|PhotoCollection $searchResults,
        Request $request,
        DataSet $pathParams,
        ResponseData $responseData,
    ): void {
        $expectedResponse = new Response();
        [$valid, $invocationCount] = $validated;

        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($valid);
        $photoRepository->expects($this->once())
            ->method('fetchByUuid')
            ->with($uuid)
            ->willReturn($photo);
        $commentRepository->expects($this->once())
            ->method('fetchForPhoto')
            ->with($photo)
            ->willReturn(new PhotoCommentCollection([]));
        $session->expects($this->exactly($invocationCount))
            ->method('get')
            ->with('user')
            ->willReturn($loggedInUser);
        $albumRepository->expects($this->exactly($invocationCount))
            ->method('fetchAll')
            ->willReturn(new PhotoAlbumCollection([]));
        $photoFaveRepository->expects($this->exactly($invocationCount))
            ->method('isUserFave')
            ->with($photo, $user)
            ->willReturn($isUserFave);
        $photoSearch->expects($this->exactly($invocationCount))
            ->method('search')
            ->with($albumName, $searchTerms)
            ->willReturn($searchResults);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoController(
            $albumRepository,
            $commentRepository,
            $photoFaveRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->singlePhoto($request, $pathParams);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame(get_class($expectedResponse), get_class($actualResponse));
    }

    /** @return array<string, array<string, mixed>> */
    public static function singlePhotoHtmlDataProvider(): array
    {
        return [
            'photo_only_ajax_request' => [
                'uuid' => 'd0bc8050-7f58-4825-b079-87a14aa5e633',
                'albumName' => null,
                'searchTerms' => null,
                'validated' => [true, 0],
                'photo' => self::createPhoto(id: 1),
                'loggedInUser' => self::createUser(),
                'user' => self::createUser(),
                'isUserFave' => true,
                'searchResults' => new PhotoCollection([]),
                'request' => new Request(),
                'pathParams' => new DataSet([
                    'album' => null,
                    'uuid' => 'd0bc8050-7f58-4825-b079-87a14aa5e633',
                ]),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'comments' => new PhotoCommentCollection([]),
                        'photo' => self::createPhoto(id: 1),
                    ],
                    template: 'photos/partials/single-photo.html.twig',
                ),
            ],
            'photo_only_url_request' => [
                'uuid' => '921b69d5-5e51-4030-9452-07fbd7dcffeb',
                'albumName' => null,
                'searchTerms' => null,
                'validated' => [false, 1],
                'photo' => self::createPhoto(id: 1),
                'loggedInUser' => self::createUser(),
                'user' => self::createUser(),
                'isUserFave' => true,
                'searchResults' => new PhotoCollection([]),
                'request' => new Request(),
                'pathParams' => new DataSet([
                    'album' => null,
                    'uuid' => '921b69d5-5e51-4030-9452-07fbd7dcffeb',
                ]),
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'albumName' => null,
                        'comments' => new PhotoCommentCollection([]),
                        'faveText' => 'photo.fave_text',
                        'isUserFave' => true,
                        'photo' => self::createPhoto(id: 1),
                        'photos' => new PhotoCollection([]),
                        'search' => null,
                        'user' => self::createUser(),
                    ],
                    template: 'photos/photos.html.twig',
                ),
            ],
            'photo_album_url_request' => [
                'uuid' => 'cf59497a-f8f6-4407-b80d-f3421ca7a420',
                'albumName' => 'tulum',
                'searchTerms' => null,
                'validated' => [false, 1],
                'photo' => self::createPhoto(id: 1),
                'loggedInUser' => null,
                'user' => UserRepository::getAnonymousUser(),
                'isUserFave' => false,
                'searchResults' => self::createPhotoAlbum(id: 1),
                'request' => new Request(),
                'pathParams' => new DataSet([
                    'album' => 'tulum',
                    'uuid' => 'cf59497a-f8f6-4407-b80d-f3421ca7a420',
                ]),
                'responseData' => new ResponseData(
                    data: [
                        'albums' => new PhotoAlbumCollection([]),
                        'albumName' => 'tulum',
                        'comments' => new PhotoCommentCollection([]),
                        'faveText' => 'photo.fave_text',
                        'isUserFave' => false,
                        'photo' => self::createPhoto(id: 1),
                        'photos' => self::createPhotoAlbum(id: 1),
                        'search' => null,
                        'user' => UserRepository::getAnonymousUser(),
                    ],
                    template: 'photos/photo-album.html.twig',
                ),
            ],
        ];
    }

    #[DataProvider('singlePhotoEarlyReturnsDataProvider')]
    public function testSinglePhotoEarlyReturns(
        Request $request,
        DataSet $pathParams,
        ResponseData $responseData,
        Response $expectedResponse,
        bool $valid,
        ?string $uuid = null,
    ): void {
        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($valid);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoController(
            $albumRepository,
            $commentRepository,
            $photoFaveRepository,
            $photoRepository,
            $photoSearch,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->singlePhoto($request, $pathParams);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame(get_class($expectedResponse), get_class($actualResponse));
    }

    /** @return array<string, array<string, mixed>> */
    public static function singlePhotoEarlyReturnsDataProvider(): array
    {
        return [
            'invalid_uuid_json' => [
                'request' => new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
                'pathParams' => new DataSet(['uuid' => 'bad_uuid']),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid',
                ),
                'expectedResponse' => new JsonResponse('The photo was not found.', 400),
                'valid' => true,
            ],
            'invalid_uuid_html' => [
                'request' => new Request(),
                'pathParams' => new DataSet(['uuid' => 'bad_uuid']),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid',
                ),
                'expectedResponse' => new Response('The photo was not found.', 400),
                'valid' => false,
            ],
            'photo_not_found_json' => [
                'request' => new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
                'pathParams' => new DataSet(['uuid' => 'f613bc97-543f-442d-b87f-55c39abcba9d']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photo' => null,
                    ],
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'photos.not_found',
                ),
                'expectedResponse' => new JsonResponse('The photo was not found.', 404),
                'valid' => true,
            ],
            'photo_not_found_html' => [
                'request' => new Request(),
                'pathParams' => new DataSet(['uuid' => 'f613bc97-543f-442d-b87f-55c39abcba9d']),
                'responseData' => new ResponseData(
                    data: [
                        'albumName' => null,
                        'photo' => null,
                    ],
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'photos.not_found',
                ),
                'expectedResponse' => new Response('The photo was not found.', 404),
                'valid' => false,
            ],
        ];
    }

//    /** @param class-string $expectedClass */
//    #[DataProvider('singlePhotoDataProvider')]
//    public function testSinglePhoto(
//        bool $validRequest,
//        Request $request,
//        DataSet $pathParams,
//        string $uuid,
//        ?Photo $repositoryResult,
//        int $commentRepositoryInvocationCount,
//        int $templateItemsRepositoryInvocations,
//        PhotoAlbum|PhotoCollection $photoSearchResult,
//        bool $isUserFave,
//        ResponseData $responseData,
//        string $expectedClass,
//        Response $expectedResponse,
//        string $expectedResponseContent,
//        string $expectedContentType,
//    ): void {
//        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
//        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
//        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
//        $photoSearch = $this->createMock(PhotoSearchInterface::class);
//        $requestHandler = $this->createMock(RequestHandlerInterface::class);
//        $faveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
//        $session = $this->createMock(SessionInterface::class);
//
//        $requestHandler->expects($this->once())
//            ->method('validate')
//            ->with($request)
//            ->willReturn($validRequest);
//        $photoRepository->expects($this->once())
//            ->method('fetchByUuid')
//            ->with($uuid)
//            ->willReturn($repositoryResult);
//        $photoSearch->expects($this->exactly($templateItemsRepositoryInvocations))
//            ->method('search')
//            ->willReturn($photoSearchResult);
//        $albumRepository->expects($this->exactly($templateItemsRepositoryInvocations))
//            ->method('fetchAll')
//            ->willReturn(new PhotoAlbumCollection([]));
//        $faveRepository->expects($this->exactly($templateItemsRepositoryInvocations))
//            ->method('isUserFave')
//            ->willReturn($isUserFave);
//        $commentRepository->expects($this->exactly($commentRepositoryInvocationCount))
//            ->method('fetchForPhoto')
//            ->with($repositoryResult)
//            ->willReturn(new PhotoCommentCollection([]));
//        $session->expects($this->once())
//            ->method('get')
//            ->with('user')
//            ->willReturn(self::createUser());
//        $requestHandler->expects($this->once())
//            ->method('sendResponse')
//            ->with($responseData)
//            ->willReturn($expectedResponse);
//
//        $controller = new PhotoController(
//            $albumRepository,
//            $commentRepository,
//            $faveRepository,
//            $photoRepository,
//            $photoSearch,
//            $requestHandler,
//            $session,
//        );
//        $actualResult = $controller->singlePhoto($request, $pathParams);
//
//        $this->assertInstanceOf($expectedClass, $actualResult);
//        $this->assertEquals($expectedResponseContent, $actualResult->getContent());
//        $this->assertStringContainsString(
//            $expectedContentType,
//            (string) $actualResult->headers->get('Content-Type')
//        );
//    }
//
//    /** @return array<string, array<string, mixed>> */
//    public static function singlePhotoDataProvider(): array
//    {
//        return [
//            'json_found' => [
//                'validRequest' => true,
//                'request' => new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
//                'pathParams' => new DataSet(['uuid' => '38a0a218-9a5c-4bb9-ab30-aae6ca3ffc61']),
//                'uuid' => '38a0a218-9a5c-4bb9-ab30-aae6ca3ffc61',
//                'repositoryResult' => self::createPhoto(5689),
//                'commentRepositoryInvocationCount' => 1,
//                'templateItemsRepositoryInvocations' => 0,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [
//                        'comments' => new PhotoCommentCollection([]),
//                        'photo' => self::createPhoto(5689),
//                    ],
//                    template: 'photos/partials/single-photo.html.twig'
//                ),
//                'expectedClass' => JsonResponse::class,
//                'expectedResponse' => new JsonResponse([
//                    'photo' => null
//                ]),
//                'expectedResponseContent' => '{"photo":null}',
//                'expectedContentType' => 'application/json',
//            ],
//            'json_not_found' => [
//                'validRequest' => true,
//                'request' => new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'application/json']),
//                'pathParams' => new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => null,
//                'commentRepositoryInvocationCount' => 0,
//                'templateItemsRepositoryInvocations' => 0,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [],
//                    statusCode: 404,
//                    template: null,
//                    translationKey: 'photos.not_found',
//                    translationVariables: [],
//                ),
//                'expectedClass' => JsonResponse::class,
//                'expectedResponse' => new JsonResponse(['photo' => null]),
//                'expectedResponseContent' => '{"photo":null}',
//                'expectedContentType' => 'application/json',
//            ],
//            'html_photo_found' => [
//                'validRequest' => true,
//                'request' => new Request(),
//                'pathParams' => new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => self::createPhoto(1234),
//                'commentRepositoryInvocationCount' => 1,
//                'templateItemsRepositoryInvocations' => 0,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [
//                        'comments' => new PhotoCommentCollection([]),
//                        'photo' => self::createPhoto(1234),
//                    ],
//                    template: 'photos/partials/single-photo.html.twig'
//                ),
//                'expectedClass' => Response::class,
//                'expectedResponse' => new Response(
//                    '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                    Response::HTTP_OK,
//                    ['Content-Type' => 'text/html']
//                ),
//                'expectedResponseContent' => '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                'expectedContentType' => 'text/html',
//            ],
//            'html_full_page_with_album' => [
//                'validRequest' => false,
//                'request' => new Request(),
//                'pathParams' => new DataSet(['album' => 'Mexico', 'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => self::createPhoto(4321),
//                'commentRepositoryInvocationCount' => 1,
//                'templateItemsRepositoryInvocations' => 1,
//                'photoSearchResult' => self::createPhotoAlbum(1234),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [
//                        'albumName' => 'Mexico',
//                        'albums' => new PhotoAlbumCollection([]),
//                        'comments' => new PhotoCommentCollection([]),
//                        'faveText' => 'photo.fave_text',
//                        'isUserFave' => false,
//                        'photo' => self::createPhoto(4321),
//                        'photos' => self::createPhotoAlbum(1234),
//                        'search' => '',
//                    ],
//                    template: 'photos/photo-album.html.twig'
//                ),
//                'expectedClass' => Response::class,
//                'expectedResponse' => new Response(
//                    '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                    Response::HTTP_OK,
//                    ['Content-Type' => 'text/html']
//                ),
//                'expectedResponseContent' => '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                'expectedContentType' => 'text/html',
//            ],
//            'html_full_page_no_album' => [
//                'validRequest' => false,
//                'request' => new Request(),
//                'pathParams' => new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => self::createPhoto(1122),
//                'commentRepositoryInvocationCount' => 1,
//                'templateItemsRepositoryInvocations' => 1,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => true,
//                'responseData' => new ResponseData(
//                    data: [
//                        'albumName' => null,
//                        'albums' => new PhotoAlbumCollection([]),
//                        'comments' => new PhotoCommentCollection([]),
//                        'faveText' => 'photo.fave_text',
//                        'isUserFave' => true,
//                        'photo' => self::createPhoto(1122),
//                        'photos' => new PhotoCollection([]),
//                        'search' => '',
//                    ],
//                    template: 'photos/photos.html.twig'
//                ),
//                'expectedClass' => Response::class,
//                'expectedResponse' => new Response(
//                    '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                    Response::HTTP_OK,
//                    ['Content-Type' => 'text/html']
//                ),
//                'expectedResponseContent' => '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                'expectedContentType' => 'text/html',
//            ],
//            'html_full_page_with_search' => [
//                'validRequest' => false,
//                'request' => new Request(['search' => 'test']),
//                'pathParams' => new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => self::createPhoto(1122),
//                'commentRepositoryInvocationCount' => 1,
//                'templateItemsRepositoryInvocations' => 1,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [
//                        'albumName' => null,
//                        'albums' => new PhotoAlbumCollection([]),
//                        'comments' => new PhotoCommentCollection([]),
//                        'faveText' => 'photo.fave_text',
//                        'isUserFave' => false,
//                        'photo' => self::createPhoto(1122),
//                        'photos' => new PhotoCollection([]),
//                        'search' => 'test',
//                    ],
//                    template: 'photos/photos.html.twig'
//                ),
//                'expectedClass' => Response::class,
//                'expectedResponse' => new Response(
//                    '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                    Response::HTTP_OK,
//                    ['Content-Type' => 'text/html']
//                ),
//                'expectedResponseContent' => '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                'expectedContentType' => 'text/html',
//            ],
//            'html_not_found' => [
//                'validRequest' => true,
//                'request' => new Request(),
//                'pathParams' => new DataSet(['uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44']),
//                'uuid' => 'ac3fcbc7-2c69-4181-8f87-b6de6f6aeb44',
//                'repositoryResult' => null,
//                'commentRepositoryInvocationCount' => 0,
//                'templateItemsRepositoryInvocations' => 0,
//                'photoSearchResult' => new PhotoCollection([]),
//                'isUserFave' => false,
//                'responseData' => new ResponseData(
//                    data: [],
//                    statusCode: 404,
//                    template: null,
//                    translationKey: 'photos.not_found',
//                    translationVariables: [],
//                ),
//                'expectedClass' => Response::class,
//                'expectedResponse' => new Response(
//                    '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                    Response::HTTP_OK,
//                    ['Content-Type' => 'text/html']
//                ),
//                'expectedResponseContent' => '<!DOCTYPE html><html lang="en-GB"><body><div>Hello World</div></body></html>',
//                'expectedContentType' => 'text/html',
//            ],
//        ];
//    }
//
//    public function testSinglePhotoBadUuid(): void
//    {
//        $request = new Request();
//        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
//        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
//        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
//        $photoSearch = $this->createMock(PhotoSearchInterface::class);
//        $requestHandler = $this->createMock(RequestHandlerInterface::class);
//        $faveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
//        $session = $this->createMock(SessionInterface::class);
//
//        $photoRepository->expects($this->never())
//            ->method('fetchByUuid');
//        $requestHandler->expects($this->once())
//            ->method('validate')
//            ->with($request)
//            ->willReturn(true);
//        $requestHandler->expects($this->once())
//            ->method('sendResponse')
//            ->with(new ResponseData(
//                data: [],
//                statusCode: Response::HTTP_BAD_REQUEST,
//                translationKey: 'photos.invalid_uuid'
//            ))
//            ->willReturn(new Response('Invalid UUID', Response::HTTP_BAD_REQUEST));
//
//        $photoRepository->expects($this->never())
//            ->method('fetchByUuid');
//        $commentRepository->expects($this->never())
//            ->method('fetchForPhoto');
//        $albumRepository->expects($this->never())
//            ->method('fetchAll');
//        $faveRepository->expects($this->never())
//            ->method('isUserFave');
//
//        $controller = new PhotoController(
//            $albumRepository,
//            $commentRepository,
//            $faveRepository,
//            $photoRepository,
//            $photoSearch,
//            $requestHandler,
//            $session,
//        );
//        $actualResult = $controller->singlePhoto(
//            $request,
//            new DataSet(['uuid' => ['bad' => 'data']])
//        );
//
//        $this->assertSame(Response::class, get_class($actualResult));
//        $this->assertEquals('Invalid UUID', $actualResult->getContent());
//        $this->assertSame(400, $actualResult->getStatusCode());
//    }
//
//    public function testSinglePhotoHtmlNoToken(): void
//    {
//        $request = new Request(['uuid' => 'b8cf4379-62f4-4f98-a57e-9811d1a7d07d']);
//        $expectedResult = '<html lang="en"><body>Some content</body></html>';
//
//        $albumRepository = $this->createMock(PhotoAlbumRepositoryInterface::class);
//        $commentRepository = $this->createMock(PhotoCommentRepositoryInterface::class);
//        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
//        $photoSearch = $this->createMock(PhotoSearchInterface::class);
//        $requestHandler = $this->createMock(RequestHandlerInterface::class);
//        $faveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
//        $session = $this->createMock(SessionInterface::class);
//
//        $photoRepository->expects($this->once())
//            ->method('fetchByUuid')
//            ->willReturn(null);
//        $requestHandler->expects($this->once())
//            ->method('validate')
//            ->with($request)
//            ->willReturn(false);
//        $requestHandler->expects($this->once())
//            ->method('sendResponse')
//            ->with(new ResponseData(
//                data: [],
//                statusCode: 404,
//                template: null,
//                translationKey: 'photos.not_found',
//                translationVariables: [],
//            ))
//            ->willReturn(new Response(
//                $expectedResult,
//                Response::HTTP_OK,
//                ['Content-Type' => 'text/html']
//            ));
//
//        $photoSearch->expects($this->never())
//            ->method('search');
//        $albumRepository->expects($this->never())
//            ->method('fetchAll');
//        $faveRepository->expects($this->never())
//            ->method('isUserFave');
//
//        $controller = new PhotoController(
//            $albumRepository,
//            $commentRepository,
//            $faveRepository,
//            $photoRepository,
//            $photoSearch,
//            $requestHandler,
//            $session,
//        );
//        $actualResult = $controller->singlePhoto(
//            $request,
//            new DataSet(['uuid' => 'b8cf4379-62f4-4f98-a57e-9811d1a7d07d'])
//        );
//
//        $this->assertInstanceOf(Response::class, $actualResult);
//        $this->assertEquals($expectedResult, $actualResult->getContent());
//        $this->assertStringContainsString('text/html', (string) $actualResult->headers->get('Content-Type'));
//    }

    private static function createPhoto(?int $id = null): Photo
    {
        return Photo::createFromDataSet(new DataSet([
            'country_id' => '45',
            'country_name' => 'Chile',
            'two_char_code' => 'CL',
            'three_char_code' => 'CHL',
            'geo_id' => '2559',
            'photo_id' => $id,
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
        ]));
    }

    private static function createPhotoAlbum(?int $id = null): PhotoAlbum
    {
        return new PhotoAlbum(
            title: 'Tulum',
            albumId: $id,
            uuid: Uuid::fromString('120f05ed-fda7-4a3b-8a4a-bbf9bb6f8211'),
            description: '',
            country: new Country(
                id: 142,
                name: 'Mexico',
                twoCharCode: 'MX',
                threeCharCode: 'MEX'
            )
        );
    }

    private static function createUser(): User
    {
        return new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: PasscodeRoute::Email,
            id: 7
        );
    }
}
