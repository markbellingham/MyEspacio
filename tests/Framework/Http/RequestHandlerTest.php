<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use MyEspacio\Framework\Csrf\StoredTokenValidatorInterface;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactoryInterface;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestHandlerTest extends TestCase
{
    private LanguageReader $languageReader;
    private TranslationIdentifierFactoryInterface $translationIdentifierFactory;
    private TemplateRendererFactoryInterface $templateRendererFactory;
    private RequestHandler $requestHandler;

    protected function setUp(): void
    {
        $this->languageReader = $this->createMock(LanguageReader::class);
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);
        $this->templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $this->translationIdentifierFactory = $this->createMock(TranslationIdentifierFactoryInterface::class);
        $this->requestHandler = new RequestHandler(
            $this->languageReader,
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
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
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(true);
        $requestHandler = new RequestHandler(
            $this->languageReader,
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
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);
        $storedTokenValidator->expects($this->once())
            ->method('validate')
            ->willreturn(false);
        $requestHandler = new RequestHandler(
            $this->languageReader,
            $storedTokenValidator,
            $this->templateRendererFactory,
            $this->translationIdentifierFactory
        );
        $this->assertFalse($requestHandler->validate($request));
    }

    /**
     * @param string $language
     * @param string $requestedFormat
     * @param string|null $translatedText
     * @param ResponseData $responseData
     * @param class-string $expectedResponseClass
     * @param int $expectedStatusCode
     * @param string $expectedResponseContent
     * @return void
     * @throws Exception
     */
    #[DataProvider('sendResponseDataProvider')]
    public function testSendResponse(
        string $language,
        string $requestedFormat,
        ?string $translatedText,
        ResponseData $responseData,
        string $expectedResponseClass,
        int $expectedStatusCode,
        string $expectedResponseContent
    ): void {
        $request = new Request();
        $request->attributes->set('language', $language);
        $request->headers->set('Accept', $requestedFormat);

        $languageReader = $this->createMock(LanguageReader::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);

        if ($responseData->getTemplate()) {
            $templateRenderer = $this->createMock(TemplateRenderer::class);
            $templateRenderer->expects($this->once())
                ->method('render')
                ->with($responseData->getTemplate(), $responseData->getData())
                ->willReturn($expectedResponseContent);
            $templateRendererFactory->expects($this->once())
                ->method('create')
                ->with($language)
                ->willReturn($templateRenderer);
        }

        if ($responseData->getTranslationKey()) {
            $languageReader->expects($this->once())
                ->method('getTranslationText')
                ->willReturn($translatedText);
        }

        $requestHandler = new RequestHandler(
            $languageReader,
            $storedTokenValidator,
            $templateRendererFactory,
            $this->translationIdentifierFactory
        );

        $requestHandler->validate($request);
        $response = $requestHandler->sendResponse($responseData);

        $this->assertInstanceOf($expectedResponseClass, $response);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $this->assertEquals($requestedFormat, $response->headers->get('Content-Type'));
        $this->assertEquals($expectedResponseContent, $response->getContent());

        if ($responseData->getTranslationKey()) {
            $actualResponseContent = json_decode((string) $response->getContent(), true);
            $this->assertIsArray($actualResponseContent);
            $this->assertArrayHasKey('message', $actualResponseContent);
            $this->assertEquals($translatedText, $actualResponseContent['message']);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public static function sendResponseDataProvider(): array
    {
        return [
            'simple_json' => [
                'en',
                'application/json',
                null,
                new ResponseData(
                    data: ['key' => 'value'],
                    statusCode: Response::HTTP_OK
                ),
                JsonResponse::class,
                200,
                '{"key":"value"}'
            ],
            'spanish_json' => [
                'es',
                'application/json',
                'Ya has iniciado sesión',
                new ResponseData(
                    statusCode: Response::HTTP_CONFLICT,
                    translationKey: 'login.logged_in'
                ),
                JsonResponse::class,
                409,
                '{"message":"Ya has iniciado sesi\u00f3n"}'
            ],
            'html' => [
                'en',
                '',
                null,
                new ResponseData(
                    template: 'pictures.search'
                ),
                Response::class,
                200,
                '<div class="my-class">Some content</div>'
            ],
            'csv' => [
                'en',
                'text/csv',
                null,
                new ResponseData(
                    data: ['key' => 'value'],
                    statusCode: Response::HTTP_OK
                ),
                Response::class,
                200,
                ''
            ],
            'xml' => [
                'en',
                'application/xml',
                null,
                new ResponseData(
                    data: ['key' => 'value'],
                    statusCode: Response::HTTP_OK
                ),
                Response::class,
                200,
                ''
            ]
        ];
    }

    public function testGetTranslationIdentifier(): void
    {
        $languageReader = $this->createMock(LanguageReader::class);
        $storedTokenValidator = $this->createMock(StoredTokenValidatorInterface::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $translationIdentifierFactory = $this->createMock(TranslationIdentifierFactoryInterface::class);

        $translationIdentifierFactory->expects($this->once())
            ->method('create')
            ->willReturn(new TranslationIdentifier('en', 'messages', new LanguagesDirectory(ROOT_DIR)));

        $requestHandler = new RequestHandler(
            $languageReader,
            $storedTokenValidator,
            $templateRendererFactory,
            $translationIdentifierFactory
        );

        $result = $requestHandler->getTranslationIdentifier('messages');

        $this->assertInstanceOf(TranslationIdentifier::class, $result);
    }
}
