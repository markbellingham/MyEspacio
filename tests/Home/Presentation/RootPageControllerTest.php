<?php

declare(strict_types=1);

namespace Tests\Home\Presentation;

use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\Home\Presentation\RootPageController;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RootPageControllerTest extends TestCase
{
    public function testShow(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $request = new Request();
        $vars = [];

        $expectedUser = new User(
            email: 'mail@example.tld',
            uuid: 'ebea9038-1b92-4af2-8f12-74fdc76dd92b',
            name: 'Anonymous'
        );

        $userRepository->expects($this->once())
            ->method('getAnonymousUser')
            ->willReturn($expectedUser);

        $session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $controller = new RootPageController($session, $templateRendererFactory, $userRepository);

        $response = $controller->show($request, $vars);

        $this->assertInstanceOf(Response::class, $response);
    }
}
