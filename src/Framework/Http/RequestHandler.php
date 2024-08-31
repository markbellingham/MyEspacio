<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use MyEspacio\Framework\Csrf\StoredTokenValidatorInterface;
use MyEspacio\Framework\Csrf\Token;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestHandler implements RequestHandlerInterface
{
    private ?string $responseType = null;
    private TemplateRenderer $templateRenderer;

    public function __construct(
        private readonly StoredTokenValidatorInterface $storedTokenValidator,
        private readonly TwigTemplateRendererFactory $templateRendererFactory,
        private readonly TranslationIdentifierFactory $translationIdentifierFactory
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

    public function sendResponse(
        array $data = [],
        string $template = '',
        int $statusCode = Response::HTTP_OK
    ): Response {
        if ($this->responseType === 'application/json' || $template == '') {
            return new JsonResponse($data, $statusCode);
        }
        $content = $this->templateRenderer->render($template, $data);
        return new Response($content, $statusCode);
    }

    public function setResponseType(string $responseType): void
    {
        $this->responseType = $responseType;
    }

    public function getResponseType(): ?string
    {
        return $this->responseType;
    }

    public function getTranslationIdentifier(Request $request, string $languageFile): TranslationIdentifier
    {
        return $this->translationIdentifierFactory->create(
            language: $request->attributes->get('language'),
            filename: $languageFile
        );
    }
}
