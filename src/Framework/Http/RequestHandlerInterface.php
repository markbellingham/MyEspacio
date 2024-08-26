<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use MyEspacio\Framework\Localisation\TranslationIdentifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestHandlerInterface
{
    public function validate(Request $request): bool;

    /**
     * @param Request $request
     * @param array<string, mixed> $vars
     * @return Response
     */
    public function showRoot(Request $request, array $vars): Response;

    /**
     * @param array<string, mixed> $data
     * @param string $template
     * @param int $statusCode
     * @return Response
     */
    public function sendResponse(array $data = [], string $template = '', int $statusCode = Response::HTTP_OK): Response;

    public function setResponseType(string $responseType): void;

    public function getResponseType(): ?string;

    public function getTranslationIdentifier(Request $request, string $languageFile): TranslationIdentifier;
}
