<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use MyEspacio\Framework\Csrf\StoredTokenValidator;
use MyEspacio\Framework\Csrf\Token;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestHandler
{
    private ?string $responseType = null;

    public function __construct(
        private readonly StoredTokenValidator $storedTokenValidator,
        private readonly TemplateRenderer $templateRenderer
    ) {
    }

    public function validateRequest(Request $request): bool
    {
        /**
         * If the response type is text/html and does not contain the token, send a full application response
         * with a destination parameter set as the requested tab
         * If the response type is application/json we can skip this and send only raw data
         */
        $this->responseType = $request->headers->get('Accept');
        if ($this->responseType === 'application/json') {
            return false;
        }

        $layoutToken = $request->headers->get('X-Layout');
        if (!$this->storedTokenValidator->validate('layout', new Token($layoutToken ?? ''))) {
            return true;
        }
        return false;
    }

    public function showRoot(Request $request, array $vars): Response
    {
        $injector = include(ROOT_DIR . '/src/Dependencies.php');
        $controller = $injector->make('Personly\FrontPage\Presentation\RootPageController');
        return $controller->show($request, $vars);
    }

    public function sendResponse(array $data = [], string $template = ''): Response
    {
        if ($this->responseType === 'application/json' || $template == '') {
            return new JsonResponse($data);
        }
        $content = $this->templateRenderer->render($template, $data);
        return new Response($content);
    }

    public function setResponseType(string $responseType): void
    {
        $this->responseType = $responseType;
    }

    public function getResponseType(): ?string
    {
        return $this->responseType;
    }
}
