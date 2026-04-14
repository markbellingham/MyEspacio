<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Sanitization;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final readonly class SafeHtmlSanitizer implements SafeHtmlSanitizerInterface
{
    public function __construct(
        private HtmlSanitizerInterface $contactMeSanitizer,
        private HtmlSanitizerInterface $commentSanitizer,
    ) {
    }

    public function sanitizeContactMeMessage(string $message): string
    {
        return $this->contactMeSanitizer->sanitize($message);
    }

    public function sanitizeComment(string $message): string
    {
        return $this->commentSanitizer->sanitize($message);
    }
}
