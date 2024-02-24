<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Auryn\Injector;
use MyEspacio\Framework\Csrf\StoredTokenValidator;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerTest extends TestCase
{
    private StoredTokenValidator $storedTokenValidator;
    private TemplateRenderer $templateRenderer;
    private RequestHandler $requestHandler;

    protected function setUp(): void
    {
        $this->storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $this->templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->requestHandler = new RequestHandler($this->storedTokenValidator, $this->templateRenderer);
    }

    public function testSetResponseType()
    {
        $this->requestHandler->setResponseType('text/html');
        $this->assertEquals('text/html', $this->requestHandler->getResponseType());
    }

    public function testValidateRequestJsonResponse()
    {
        $request = Request::createFromGlobals();
        $request->headers->set('Accept', 'application/json');
        $this->assertFalse($this->requestHandler->validateRequest($request));
    }

    public function testValidateRequestHtmlResponseNoToken()
    {
        $request = Request::createFromGlobals();
        $storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(false);
        $requestHandler = new RequestHandler($storedTokenValidator, $this->templateRenderer);
        $this->assertTrue($requestHandler->validateRequest($request));
    }

    public function testValidateRequestHtmlResponseWithToken()
    {
        $request = Request::createFromGlobals();
        $storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(true);
        $requestHandler = new RequestHandler($storedTokenValidator, $this->templateRenderer);
        $this->assertFalse($requestHandler->validateRequest($request));
    }

//    public function testShowRoot()
//    {
//        $request = $this->createMock(Request::class);
//        $vars = ['var1' => 'value1', 'var2' => 'value2']; // adjust this to your needs
//
//        $controller = $this->createMock(RootPageController::class);
//        $controller->method('show')->willReturn(new Response());
//
//        $injector = $this->createMock(Injector::class);
//        $injector->method('make')->willReturn($controller);
//
//        $result = $controller->showRoot($request, $vars);
//
//        $this->assertInstanceOf(Response::class, $result);
//    }

    public function testSendResponseJson(): void
    {
        $this->requestHandler->setResponseType('application/json');
        $response = $this->requestHandler->sendResponse(['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"key":"value"}', $response->getContent());
    }

    public function testSendResponseHtml(): void
    {
        $this->requestHandler->setResponseType('text/html');

        $this->templateRenderer->expects($this->once())
            ->method('render')
            ->with('template', ['key' => 'value'])
            ->willReturn('Rendered Template');

        $response = $this->requestHandler->sendResponse(['key' => 'value'], 'template');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Rendered Template', $response->getContent());
    }
}
