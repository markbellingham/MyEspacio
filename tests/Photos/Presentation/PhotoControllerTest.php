<?php

declare(strict_types=1);

namespace Tests\Photos\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Presentation\PhotoController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PhotoControllerTest extends TestCase
{
    public function testPhotoGridShowRoot(): void
    {
        $request = new Request();
        $vars = ['searchPhotos' => 'sunset'];

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('showRoot')
            ->with($request, $vars)
            ->willReturn(new Response());
        $photoSearch = $this->createMock(PhotoSearchInterface::class);

        $controller = new PhotoController($photoSearch, $requestHandler);
        $result = $controller->photoGrid($request, $vars);
        $this->assertInstanceOf(Response::class, $result);
    }

    /** @dataProvider photoGridDataProvider */
    public function testPhotoGrid(
        ?string $acceptHeader,
        string $searchTerms,
        ResponseData $responseData,
        Response $expectedResponseClass,
        PhotoAlbum|PhotoCollection $expectedPhotoClass,
        string $expectedResponseClassName,
        string $expectedResponseData
    ): void {
        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);
        $vars = ['searchPhotos' => $searchTerms];

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with($responseData)
            ->willReturn($expectedResponseClass);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $photoSearch->expects($this->once())
            ->method('search')
            ->with($searchTerms)
            ->willReturn($expectedPhotoClass);

        $controller = new PhotoController($photoSearch, $requestHandler);

        $actualResponse = $controller->photoGrid($request, $vars);
        $this->assertInstanceOf($expectedResponseClassName, $actualResponse);

        $this->assertEquals($expectedResponseData, $actualResponse->getContent());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function photoGridDataProvider(): array
    {
        return [
            'test_1' => [
                'application/json',
                'sunset',
                new ResponseData(
                    data: [
                        'photos' => new PhotoCollection([])
                    ],
                    template: 'photos/PhotoGrid.html.twig'
                ),
                new JsonResponse(
                    [
                        'photos' => new PhotoCollection([])
                    ],
                    Response::HTTP_OK
                ),
                new PhotoCollection([]),
                JsonResponse::class,
                '{"photos":[]}'
            ],
            'test_2' => [
                null,
                'sunset',
                new ResponseData(
                    data: [
                        'photos' => new PhotoCollection([])
                    ],
                    template: 'photos/PhotoGrid.html.twig'
                ),
                new Response(
                    '<div class="my-class">Some Content</div>',
                    Response::HTTP_OK
                ),
                new PhotoCollection([]),
                Response::class,
                '<div class="my-class">Some Content</div>'
            ],
            'test_3' => [
                'application/json',
                'sunset',
                new ResponseData(
                    data: [
                        'photos' => new PhotoAlbum(
                            title: 'Singapore',
                            albumId: 51,
                            country: new Country(
                                id: 199,
                                name: 'Singapore',
                                twoCharCode: 'SG',
                                threeCharCode: 'SGP'
                            ),
                            photos: new PhotoCollection([])
                        )
                    ],
                    template: 'photos/PhotoGrid.html.twig'
                ),
                new JsonResponse(
                    [
                        'photos' => new PhotoAlbum(
                            title: 'Singapore',
                            albumId: 51,
                            country: new Country(
                                id: 199,
                                name: 'Singapore',
                                twoCharCode: 'SG',
                                threeCharCode: 'SGP'
                            ),
                            photos: new PhotoCollection([])
                        )
                    ],
                    Response::HTTP_OK
                ),
                new PhotoAlbum(
                    title: 'Singapore',
                    albumId: 51,
                    country: new Country(
                        id: 199,
                        name: 'Singapore',
                        twoCharCode: 'SG',
                        threeCharCode: 'SGP'
                    ),
                    photos: new PhotoCollection([])
                ),
                JsonResponse::class,
                '{"photos":{"title":"Singapore","description":null,"country":{"name":"Singapore","twoCharCode":"SG","threeCharCode":"SGP"},"photos":[]}}'
            ]
        ];
    }
}
