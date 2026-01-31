<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class ResponseData
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $translationVariables
     */
    public function __construct(
        private array $data = [],
        private readonly int $statusCode = Response::HTTP_OK,
        private readonly ?string $template = null,
        private readonly ?string $translationKey = null,
        private readonly array $translationVariables = []
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

    /** @return array<string, string> */
    public function getTranslationVariables(): array
    {
        return $this->translationVariables;
    }

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}
