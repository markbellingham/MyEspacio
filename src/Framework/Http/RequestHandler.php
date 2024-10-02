<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use MyEspacio\Framework\Csrf\StoredTokenValidatorInterface;
use MyEspacio\Framework\Csrf\Token;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactoryInterface;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestHandler implements RequestHandlerInterface
{
    private string $language = 'en';
    private ?string $responseType = null;
    private TemplateRenderer $templateRenderer;

    public function __construct(
        private readonly LanguageReader $languageReader,
        private readonly StoredTokenValidatorInterface $storedTokenValidator,
        private readonly TemplateRendererFactoryInterface $templateRendererFactory,
        private readonly TranslationIdentifierFactoryInterface $translationIdentifierFactory
    ) {
    }

    public function validate(Request $request): bool
    {
        $this->templateRenderer = $this->templateRendererFactory->create($request->attributes->get('language') ?? 'en');

        /**
         * If the response type is text/html and does not contain the token, send a full application response
         * with a destination parameter set as the requested tab
         * If the response type is application/json we can skip this and send only raw data
         */
        $this->responseType = $request->headers->get('Accept');
        $this->language = $request->attributes->get('language') ?? 'en';
        if ($this->responseType === 'application/json') {
            return true;
        }

        $layoutToken = $request->headers->get('X-Layout');
        $token = new Token($layoutToken ?? '');
        if ($this->storedTokenValidator->validate('layout', $token) === false) {
            return false;
        }
        return true;
    }

    public function showRoot(Request $request, array $vars): Response
    {
        $injector = include(ROOT_DIR . '/src/Dependencies.php');
        $controller = $injector->make('MyEspacio\Home\Presentation\RootPageController');
        return $controller->show($request, $vars);
    }

    public function sendResponse(ResponseData $responseData): Response
    {
        if ($responseData->getTemplate()) {
            $content = $this->templateRenderer->render($responseData->getTemplate(), $responseData->getData());
            return new Response($content, $responseData->getStatusCode());
        }
        if ($responseData->getTranslationKey()) {
            $data['message'] = $this->languageReader->getTranslationText(
                $this->getTranslationIdentifier('messages'),
                $responseData->getTranslationKey()
            );
        }
        switch ($this->responseType) {
            case 'text/csv':
                return new Response(
                    content: $this->formatToCsv($responseData->getData()),
                    status: $responseData->getStatusCode(),
                    headers: ['Content-Type' => 'text/csv']
                );
            case 'application/xml':
                return new Response(
                    content: $this->formatToXml($responseData->getData()),
                    status: $responseData->getStatusCode(),
                    headers: ['Content-Type' => 'application/xml']
                );
            case 'application/json':
            default:
                return new JsonResponse($responseData->getData(), $responseData->getStatusCode());
        }
    }

    public function getTranslationIdentifier(string $languageFile): TranslationIdentifier
    {
        return $this->translationIdentifierFactory->create(
            language: $this->language,
            filename: $languageFile
        );
    }

    /** @param array<string, mixed> $data */
    private function formatToCsv(array $data): string
    {
        return '';
    }

    /** @param array<string, mixed> $data */
    private function formatToXml(array $data): string
    {
        return '';
    }
}
