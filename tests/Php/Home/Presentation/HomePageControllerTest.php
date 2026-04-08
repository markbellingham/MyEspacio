<?php

declare(strict_types=1);

namespace Tests\Php\Home\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Home\Presentation\HomePageController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class HomePageControllerTest extends TestCase
{
    public function testShow(): void
    {
        $request = new Request();
        $expectedResponse = new Response('<div>Some Html</div>');

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(false);
        $session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                data: ['user' => null],
                template: 'home/FrontPage.html.twig',
            ))
            ->willReturn($expectedResponse);

        $controller = new HomePageController(
            $requestHandler,
            $session,
        );

        $actualResponse = $controller->show($request);

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
    }

    public function testHomeAlias(): void
    {
        $request = new Request();
        $expectedResponse = new Response("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        <meta http-equiv=\"refresh\" content=\"0;url='/'\" />

        <title>Redirecting to /</title>
    </head>
    <body>
        Redirecting to <a href=\"/\">/</a>.
    </body>
</html>", Response::HTTP_FOUND);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $requestHandler->expects($this->never())
            ->method('validate');
        $session->expects($this->never())
            ->method('get');
        $requestHandler->expects($this->never())
            ->method('sendResponse');

        $controller = new HomePageController(
            $requestHandler,
            $session,
        );

        $actualResponse = $controller->homeAlias();

        $this->assertSame($expectedResponse->getContent(), $actualResponse->getContent());
        $this->assertSame($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
    }
}
