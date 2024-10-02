<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final readonly class ResponseData
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $translationVariables
     */
    public function __construct(
        private array $data = [],
        private int $statusCode = Response::HTTP_OK,
        private ?string $template = null,
        private ?string $translationKey = null,
        private array $translationVariables = []
    ) {
        if (in_array($this->statusCode, array_keys(Response::$statusTexts)) === false) {
            throw new InvalidArgumentException('Invalid HTTP status code ' . $this->statusCode);
        }
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getTranslationKey(): ?string
    {
        return $this->translationKey;
    }

    /** @return array<string, mixed> */
    public function getTranslationVariables(): array
    {
        return $this->translationVariables;
    }
}
