<?php

declare(strict_types=1);

namespace Tests\Home\Presentation;

use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\Home\Presentation\RootPageController;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RootPageControllerTest extends TestCase
{
    public function testShow(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $photoSearch = $this->createMock(PhotoSearchInterface::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $request = new Request();
        $vars = [];

        $photoSearch->expects($this->once())
            ->method('search')
            ->willReturn(new PhotoCollection([]));

        $expectedUser = new User(
            email: 'mail@example.tld',
            uuid: Uuid::fromString('ebea9038-1b92-4af2-8f12-74fdc76dd92b'),
            name: 'Anonymous'
        );

        $userRepository->expects($this->once())
            ->method('getAnonymousUser')
            ->willReturn($expectedUser);

        $session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $controller = new RootPageController(
            $photoSearch,
            $session,
            $templateRendererFactory,
            $userRepository
        );

        $response = $controller->show($request, $vars);

        $this->assertInstanceOf(Response::class, $response);
    }
}
