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
     * @param array<string, mixed> $translationVariables
     */
    public function sendResponse(
        array $data = [],
        int $statusCode = Response::HTTP_OK,
        string $template = '',
        string $translationKey = '',
        array $translationVariables = []
    ): Response;

    public function getTranslationIdentifier(string $languageFile): TranslationIdentifier;
}
