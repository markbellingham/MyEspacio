<?php

declare(strict_types=1);

namespace Tests\Php\Photos\Presentation;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Domain\Repository\PhotoFaveRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Presentation\PhotoFaveController;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PhotoFaveControllerTest extends TestCase
{
    #[DataProvider('addDataProvider')]
    public function testAdd(
        bool $validated,
        int $sessionInvocationCount,
        ?User $sessionUser,
        ?UuidInterface $uuid,
        int $photoRepositoryInvocationCount,
        ?Photo $photo,
        PhotoFave $photoFave,
        int $photoFaveRepositoryInvocationCount,
        bool $photoFaveSaveResult,
        Request $request,
        DataSet $pathParameters,
        ResponseData $responseData,
        Response $expectedResponse,
    ): void {
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validated);
        $session->expects($this->exactly($sessionInvocationCount))
            ->method('get')
            ->with('user')
            ->willReturn($sessionUser);
        $photoRepository->expects($this->exactly($photoRepositoryInvocationCount))
            ->method('fetchByUuid')
            ->with($uuid)
            ->willReturn($photo);
        $photoFaveRepository->expects($this->exactly($photoFaveRepositoryInvocationCount))
            ->method('save')
            ->with($photoFave)
            ->willReturn($photoFaveSaveResult);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoFaveController(
            $photoRepository,
            $photoFaveRepository,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->add($request, $pathParameters);

        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
    }

    /** @return array<string, array<string, mixed>> */
    public static function addDataProvider(): array
    {
        return [
            'invalid_request' => [
                'validated' => false,
                'sessionInvocationCount' => 0,
                'sessionUser' => null,
                'uuid' => null,
                'photoRepositoryInvocationCount' => 0,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'photoFaveSaveResult' => false,
                'request' => new Request(),
                'pathParameters' => new DataSet(),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_METHOD_NOT_ALLOWED,
                    translationKey: 'general.method_not_allowed',
                ),
                'expectedResponse' => new Response('', Response::HTTP_METHOD_NOT_ALLOWED),
            ],
            'invalid_uuid' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => null,
                'photoRepositoryInvocationCount' => 0,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'photoFaveSaveResult' => false,
                'request' => new Request(),
                'pathParameters' => new DataSet(),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid',
                ),
                'expectedResponse' => new Response('The photo was not found.', Response::HTTP_BAD_REQUEST),
            ],
            'photo_not_found' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'photoFaveSaveResult' => false,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'photos.not_found',
                ),
                'expectedResponse' => new Response('The photo was not found.', Response::HTTP_NOT_FOUND),
            ],
            'fave_not_saved' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('a5b3c2d1-e4f5-4a6b-8c7d-9e0f1a2b3c4d'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('a5b3c2d1-e4f5-4a6b-8c7d-9e0f1a2b3c4d'),
                    'Sunset in Santorini',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('a5b3c2d1-e4f5-4a6b-8c7d-9e0f1a2b3c4d'),
                        'Sunset in Santorini',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'photoFaveSaveResult' => false,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => 'a5b3c2d1-e4f5-4a6b-8c7d-9e0f1a2b3c4d',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                    translationKey: 'general.server_error',
                    translationVariables: ['photo_title' => 'Sunset in Santorini'],
                ),
                'expectedResponse' => new Response('Sorry, there was an error. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR),
            ],
            'everything_ok' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                    'Hiking the Andes',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'photoFaveSaveResult' => true,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_saved',
                    translationVariables: ['photo_title' => 'Hiking the Andes'],
                ),
                'expectedResponse' => new Response('The photo was saved as a favourite.', Response::HTTP_OK),
            ],
            'everything_ok_different_photo' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
                    'Northern Lights',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
                        'Northern Lights',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'photoFaveSaveResult' => true,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => 'b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_saved',
                    translationVariables: ['photo_title' => 'Northern Lights'],
                ),
                'expectedResponse' => new Response('The photo was saved as a favourite.', Response::HTTP_OK),
            ],
            'anonymous_user' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => null,
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                    'Hiking the Andes',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: UserRepository::getAnonymousUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'photoFaveSaveResult' => true,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_saved',
                    translationVariables: ['photo_title' => 'Hiking the Andes'],
                ),
                'expectedResponse' => new Response('The photo was saved as a favourite.', Response::HTTP_OK),
            ],
        ];
    }

    #[DataProvider('removeDataProvider')]
    public function testRemove(
        bool $validated,
        int $sessionInvocationCount,
        ?User $sessionUser,
        ?UuidInterface $uuid,
        int $photoRepositoryInvocationCount,
        ?Photo $photo,
        PhotoFave $photoFave,
        int $photoFaveRepositoryInvocationCount,
        Request $request,
        DataSet $pathParameters,
        ResponseData $responseData,
        Response $expectedResponse,
    ): void {
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validated);
        $session->expects($this->exactly($sessionInvocationCount))
            ->method('get')
            ->with('user')
            ->willReturn($sessionUser);
        $photoRepository->expects($this->exactly($photoRepositoryInvocationCount))
            ->method('fetchByUuid')
            ->with($uuid)
            ->willReturn($photo);
        $photoFaveRepository->expects($this->exactly($photoFaveRepositoryInvocationCount))
            ->method('delete')
            ->with($photoFave);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponse);

        $controller = new PhotoFaveController(
            $photoRepository,
            $photoFaveRepository,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->remove($request, $pathParameters);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function removeDataProvider(): array
    {
        return [
            'invalid_request' => [
                'validated' => false,
                'sessionInvocationCount' => 0,
                'sessionUser' => null,
                'uuid' => null,
                'photoRepositoryInvocationCount' => 0,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'request' => new Request(),
                'pathParameters' => new DataSet(),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_METHOD_NOT_ALLOWED,
                    translationKey: 'general.method_not_allowed',
                ),
                'expectedResponse' => new Response('', Response::HTTP_METHOD_NOT_ALLOWED),
            ],
            'invalid_uuid' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => null,
                'photoRepositoryInvocationCount' => 0,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'request' => new Request(),
                'pathParameters' => new DataSet(),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid',
                ),
                'expectedResponse' => new Response('The photo was not found.', Response::HTTP_BAD_REQUEST),
            ],
            'photo_not_found' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => null,
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 0,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'photos.not_found',
                ),
                'expectedResponse' => new Response('The photo was not found.', Response::HTTP_NOT_FOUND),
            ],
            'everything_ok' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                    'Hiking the Andes',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_removed',
                    translationVariables: ['photo_title' => 'Hiking the Andes'],
                ),
                'expectedResponse' => new Response('The photo was removed from your favourites.', Response::HTTP_OK),
            ],
            'everything_ok_different_photo' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => self::createSessionUser(),
                'uuid' => Uuid::fromString('c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f'),
                    'Eiffel Tower',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f'),
                        'Eiffel Tower',
                    ),
                    user: self::createSessionUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => 'c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_removed',
                    translationVariables: ['photo_title' => 'Eiffel Tower'],
                ),
                'expectedResponse' => new Response('The photo was removed from your favourites.', Response::HTTP_OK),
            ],
            'anonymous_user' => [
                'validated' => true,
                'sessionInvocationCount' => 1,
                'sessionUser' => null,
                'uuid' => Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'photoRepositoryInvocationCount' => 1,
                'photo' => self::createPhoto(
                    Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                    'Hiking the Andes',
                ),
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(
                        Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                        'Hiking the Andes',
                    ),
                    user: UserRepository::getAnonymousUser(),
                ),
                'photoFaveRepositoryInvocationCount' => 1,
                'request' => new Request(),
                'pathParameters' => new DataSet([
                    'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
                ]),
                'responseData' => new ResponseData(
                    statusCode: Response::HTTP_OK,
                    translationKey: 'photos.fave_removed',
                    translationVariables: ['photo_title' => 'Hiking the Andes'],
                ),
                'expectedResponse' => new Response('The photo was saved as a favourite.', Response::HTTP_OK),
            ],
        ];
    }

    public function testRemoveWithException(): void
    {
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoFaveRepository = $this->createMock(PhotoFaveRepositoryInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $session->expects($this->once())
            ->method('get')
            ->willReturn(self::createSessionUser());
        $photoRepository->expects($this->once())
            ->method('fetchByUuid')
            ->willReturn(self::createPhoto(
                Uuid::fromString('041b71c7-b94f-42e1-bfda-fa58d3349603'),
                'Hiking the Andes'
            ));

        $photoFaveRepository->expects($this->once())
            ->method('delete')
            ->willThrowException(new PDOException('Test exception'));
        $expectedResponse = new Response(
            'Sorry, there was an error. Please try again later.',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                translationKey: 'general.server_error',
            ))
            ->willReturn($expectedResponse);

        $controller = new PhotoFaveController(
            $photoRepository,
            $photoFaveRepository,
            $requestHandler,
            $session,
        );
        $actualResponse = $controller->remove(new Request(), new DataSet([
            'uuid' => '041b71c7-b94f-42e1-bfda-fa58d3349603',
        ]));

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
    }

    private static function createSessionUser(): User
    {
        return new User(
            email: 'test@my-domain.tld',
            uuid: Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'),
            name: 'test user',
            id: 1,
        );
    }

    private static function createPhoto(
        UuidInterface $uuid,
        string $title,
    ): Photo {
        return new Photo(
            country: new Country(
                id: 11,
                name: 'Argentina',
                twoCharCode: 'AR',
                threeCharCode: 'ARG'
            ),
            geoCoordinates: new GeoCoordinates(
                id: 794,
                photoUuid: $uuid,
                latitude: 30254062,
                longitude: 120138974,
                accuracy: 14
            ),
            dimensions: new Dimensions(
                width: 1920,
                height: 1080,
            ),
            relevance: new Relevance(
                cScore: 4,
                pScore: 5
            ),
            uuid: $uuid,
            dateTaken: new DateTimeImmutable('2012-10-21'),
            description: 'Hiking the Andes',
            directory: 'Argentina',
            filename: 'P1234567.JPG',
            id: 1234567,
            title: $title,
            town: 'Mendoza',
            commentCount: 3,
            faveCount: 7
        );
    }
}
