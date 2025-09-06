<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Http;

use Exception;
use FastRoute\Dispatcher;
use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\Http\RequestDispatcher;
use MyEspacio\Framework\Routing\RouterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestDispatcherTest extends TestCase
{
    /** @param array{0:int, 1:string, 2:array<string,string>} $routeInfo */
    #[DataProvider('dispatchRouteFoundDataProvider')]
    public function testDispatchRouteFound(
        string $requestMethod,
        string $pathInfo,
        array $routeInfo,
        string $controllerName,
        Request $request,
        int $status,
        string $content,
    ): void {
        $dispatcher = $this->createMock(Dispatcher::class);
        $router = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $controller = new class ($content, $status) extends BaseController {
            public function __construct(
                private readonly string $content,
                private readonly int $status
            ) {
            }

            /** @param array<string,string> $args */
            public function __call(string $method, array $args): Response
            {
                return new Response($this->content, $this->status);
            }
        };

        $router->expects($this->once())
            ->method('createDispatcher')
            ->willReturn($dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($requestMethod, $pathInfo)
            ->willReturn($routeInfo);
        $container->expects($this->once())
            ->method('get')
            ->with($controllerName)
            ->willReturn($controller);

        $requestDispatcher = new RequestDispatcher($router, $container);
        $actualResponse = $requestDispatcher->dispatch($request);

        $this->assertSame($status, $actualResponse->getStatusCode());
        $this->assertSame($content, $actualResponse->getContent());
    }

    /** @return array<string, array<int, mixed>> */
    public static function dispatchRouteFoundDataProvider(): array
    {
        return [
            'home page' => [
                'GET',
                '/',
                [Dispatcher::FOUND, 'MyEspacio\Home\Presentation\HomePageController#show', []],
                'MyEspacio\Home\Presentation\HomePageController',
                Request::create('/', 'GET'),
                200,
                'Home page content',
            ],
            'photo with parameters' => [
                'GET',
                '/photo/abc123',
                [Dispatcher::FOUND, 'MyEspacio\Photos\Presentation\PhotoController#singlePhoto', ['uuid' => 'abc123']],
                'MyEspacio\Photos\Presentation\PhotoController',
                Request::create('/photo/abc123', 'GET'),
                200,
                'Photo content',
            ],
            'contact form submission' => [
                'POST',
                '/contact/send',
                [Dispatcher::FOUND, 'MyEspacio\Contact\Presentation\ContactController#sendMessage', []],
                'MyEspacio\Contact\Presentation\ContactController',
                Request::create('/contact/send', 'POST'),
                201,
                'Message sent',
            ],
        ];
    }

    public function testDispatchMethodNotAllowed(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $router = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $request = Request::create('/', 'POST');
        $routeInfo = [Dispatcher::METHOD_NOT_ALLOWED, [], []];

        $router->expects($this->once())
            ->method('createDispatcher')
            ->willReturn($dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('POST', '/')
            ->willReturn($routeInfo);
        $container->expects($this->never())
            ->method('get');

        $requestDispatcher = new RequestDispatcher($router, $container);
        $actualResponse = $requestDispatcher->dispatch($request);

        $this->assertSame(405, $actualResponse->getStatusCode());
        $this->assertSame('Method Not Allowed', $actualResponse->getContent());
    }

    public function testDispatchRouteNotFound(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $router = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $request = Request::create('/nonexistent', 'GET');
        $routeInfo = [Dispatcher::NOT_FOUND];

        $router->expects($this->once())
            ->method('createDispatcher')
            ->willReturn($dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('GET', '/nonexistent')
            ->willReturn($routeInfo);
        $container->expects($this->never())
            ->method('get');

        $requestDispatcher = new RequestDispatcher($router, $container);
        $actualResponse = $requestDispatcher->dispatch($request);

        $this->assertSame(404, $actualResponse->getStatusCode());
        $this->assertSame('Not Found', $actualResponse->getContent());
    }

    public function testDispatchThrowsExceptionWhenControllerDoesNotReturnResponse(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $router = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        // Controller that returns non-Response object
        $controller = new class extends BaseController {
            /** @param array<string,string> $args */
            public function __call(string $method, array $args): string
            {
                return 'Not a Response object';
            }
        };

        $routeInfo = [Dispatcher::FOUND, 'TestController#badMethod', []];
        $request = Request::create('/', 'GET');

        $router->expects($this->once())
            ->method('createDispatcher')
            ->willReturn($dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('GET', '/')
            ->willReturn($routeInfo);
        $container->expects($this->once())
            ->method('get')
            ->with('TestController')
            ->willReturn($controller);

        $requestDispatcher = new RequestDispatcher($router, $container);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Controller methods must return a Response object');

        $requestDispatcher->dispatch($request);
    }
}
