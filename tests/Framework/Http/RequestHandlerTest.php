<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use MyEspacio\Framework\Csrf\StoredTokenValidator;
use MyEspacio\Framework\Csrf\StoredTokenValidatorInterface;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerTest extends TestCase
{
    /** @var TranslationIdentifierFactory|MockObject */
    private TranslationIdentifierFactory|MockObject $translationIdentifierFactory;

    private TemplateRendererFactoryInterface $templateRendererFactory;

    private RequestHandler|MockObject $requestHandler;

    protected function setUp(): void
    {
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);
        $this->templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $this->translationIdentifierFactory = $this->createMock(TranslationIdentifierFactory::class);
        $this->requestHandler = new RequestHandler(
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
    }

    public function testSetResponseType(): void
    {
        $this->requestHandler->setResponseType('text/html');
        $this->assertEquals('text/html', $this->requestHandler->getResponseType());
    }

    public function testValidateRequestJsonResponse(): void
    {
        $request = new Request();
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');
        $this->assertTrue($this->requestHandler->validate($request));
    }

    public function testValidateRequestHtmlResponseNoToken(): void
    {
        $request = new Request();
        $request->attributes->set('language', 'en');
        $storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(true);
        $requestHandler = new RequestHandler(
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
        $this->assertTrue($requestHandler->validate($request));
    }

    public function testValidateRequestHtmlResponseWithToken(): void
    {
        $request = new Request();
        $request->attributes->set('language', 'en');
        $storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(false);
        $requestHandler = new RequestHandler(
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
        $this->assertFalse($requestHandler->validate($request));
    }

    #[Group('database')]
    public function testShowRoot(): void
    {
        $request = new Request();
        $request->request->set('language', 'en');
        $vars = ['var1' => 'value1', 'var2' => 'value2'];
        $result = $this->requestHandler->showRoot($request, $vars);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testSendResponseJson(): void
    {
        $this->requestHandler->setResponseType('application/json');
        $response = $this->requestHandler->sendResponse(['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"key":"value"}', $response->getContent());
    }

    public function testSendResponseHtml(): void
    {
        $request = new Request();
        $request->attributes->set('language', 'en');
        $storedTokenValidator = $this->createMock(StoredTokenValidator::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(false);
        $requestHandler = new RequestHandler(
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
        $requestHandler->validate($request);
        $response = $requestHandler->sendResponse(['key' => 'value'], 'template');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('', $response->getContent());
    }

    public function testGetTranslationIdentifier(): void
    {
        $this->translationIdentifierFactory->expects($this->once())
            ->method('create')
            ->willReturn(new TranslationIdentifier('en', 'messages', new LanguagesDirectory(ROOT_DIR)));

        $request = new Request();
        $request->attributes->set('language', 'en');

        $result = $this->requestHandler->getTranslationIdentifier($request, 'messages');

        $this->assertInstanceOf(TranslationIdentifier::class, $result);
    }
}
