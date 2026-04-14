<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Sanitization;

interface SafeHtmlSanitizerInterface
{
    public function sanitizeContactMeMessage(string $message): string;

    public function sanitizeComment(string $message): string;
}
